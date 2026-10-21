<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_kabshop;

/**
 * Заказы витрины: выборка покупок с платежами, покупателями и данными шлюза.
 * Номер заказа = id покупки (local_kabshop_purchase) — тот, что в письме покупателю.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class orders {

    /** Строк на странице. */
    const PERPAGE = 100;

    /**
     * Есть ли таблица шлюза kbb1 (номер в сервисе оплаты, транзакция, способ).
     *
     * @return bool
     */
    protected static function has_kbb1(): bool {
        global $DB;
        static $has = null;
        return $has ?? ($has = $DB->get_manager()->table_exists('paygw_kbb1'));
    }

    /**
     * Заказы по фильтрам.
     *
     * @param array $f q (имя/email/номер), productid, from, to (timestamp), userid
     * @param int $page
     * @param int $perpage 0 — без ограничения (выгрузка)
     * @return array [записи, всего]
     */
    public static function search(array $f, int $page = 0, int $perpage = self::PERPAGE): array {
        global $DB;
        $where = ['1 = 1'];
        $params = [];
        if (!empty($f['userid'])) {
            $where[] = 'pu.userid = :userid';
            $params['userid'] = $f['userid'];
        }
        if (!empty($f['productid'])) {
            $where[] = 'pu.productid = :productid';
            $params['productid'] = $f['productid'];
        }
        if (!empty($f['from'])) {
            $where[] = 'pu.timecreated >= :from';
            $params['from'] = $f['from'];
        }
        if (!empty($f['to'])) {
            $where[] = 'pu.timecreated < :to';
            $params['to'] = $f['to'];
        }
        if (!empty($f['paidonly'])) {
            $where[] = 'pu.paymentid > 0';
        }
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            if (ctype_digit($q)) {
                $where[] = 'pu.id = :qid';
                $params['qid'] = (int) $q;
            } else {
                $like = [];
                foreach (['u.email', 'u.firstname', 'u.lastname'] as $i => $col) {
                    $like[] = $DB->sql_like($col, ':q' . $i, false, false);
                    $params['q' . $i] = '%' . $DB->sql_like_escape($q) . '%';
                }
                $where[] = '(' . implode(' OR ', $like) . ')';
            }
        }

        $names = \core_user\fields::for_name()->get_sql('u');
        $kbb1sel = self::has_kbb1() ? ', k.id AS serviceref, k.method, k.txnid' : ', NULL AS serviceref, NULL AS method, NULL AS txnid';
        $kbb1join = self::has_kbb1() ? 'LEFT JOIN {paygw_kbb1} k ON k.paymentid = pu.paymentid AND pu.paymentid > 0' : '';
        $from = "FROM {local_kabshop_purchase} pu
                 JOIN {user} u ON u.id = pu.userid
            LEFT JOIN {local_kabshop_product} pr ON pr.id = pu.productid
            LEFT JOIN {payments} pm ON pm.id = pu.paymentid
            LEFT JOIN {local_kabshop_coupon} cp ON cp.id = pu.couponid
                 $kbb1join
                WHERE " . implode(' AND ', $where);

        $total = $DB->count_records_sql("SELECT COUNT(1) $from", $params);
        $rows = $DB->get_records_sql(
            "SELECT pu.id, pu.timecreated, pu.amount, pu.currency, pu.paymentid, pu.productid, pu.userid,
                    pu.status, pu.grantedby, pu.couponid, pu.amountoff, cp.code AS couponcode,
                    pr.name AS productname, u.email {$names->selects},
                    pm.gateway, pm.paymentarea $kbb1sel
             $from
             ORDER BY pu.id DESC", $params, $perpage ? $page * $perpage : 0, $perpage);
        return [$rows, $total];
    }

    /**
     * Вид заказа: оплата / автоплатёж / бесплатно.
     *
     * @param \stdClass $r
     * @return string
     */
    public static function type(\stdClass $r): string {
        $p = 'local_kabshop';
        if (!empty($r->grantedby)) {
            $t = get_string('ordertype_manual', $p);
        } else if (empty($r->paymentid)) {
            $t = get_string(!empty($r->couponid) ? 'ordertype_coupon' : 'ordertype_free', $p);
        } else {
            $t = get_string($r->paymentarea === 'renewal' ? 'ordertype_renewal' : 'ordertype_paid', $p);
        }
        if (!empty($r->couponcode)) {
            $t .= ' · ' . $r->couponcode;
        }
        if (($r->status ?? 'ok') === 'revoked') {
            $t .= ' · ' . get_string('ordertype_revoked', $p);
        }
        return $t;
    }

    /**
     * Способ оплаты для показа.
     *
     * @param \stdClass $r
     * @return string
     */
    public static function method(\stdClass $r): string {
        if (empty($r->gateway)) {
            return '—';
        }
        if ($r->gateway === 'kbb1' && !empty($r->method)) {
            return get_string('method_' . $r->method, 'paygw_kbb1');
        }
        return get_string('pluginname', 'paygw_' . $r->gateway);
    }

    /**
     * Незавершённые попытки оплаты через kbb1 (отменены, ошибка, ждут возврата).
     *
     * @param int $limit
     * @return array
     */
    public static function attempts(int $limit = 200): array {
        global $DB;
        if (!self::has_kbb1()) {
            return [];
        }
        $names = \core_user\fields::for_name()->get_sql('u');
        return $DB->get_records_sql(
            "SELECT k.id, k.component, k.paymentarea, k.itemid, k.userid, k.method, k.status, k.amount, k.currency,
                    k.timecreated, k.timemodified, u.email {$names->selects}
               FROM {paygw_kbb1} k
               JOIN {user} u ON u.id = k.userid
              WHERE k.status <> 'paid'
           ORDER BY k.id DESC", [], 0, $limit);
    }
}
