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
     * @param array $f q (имя/email/номер/промокод), productid, couponid, from, to (timestamp), userid
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
        if (!empty($f['couponid'])) {
            $where[] = 'pu.couponid = :couponid';
            $params['couponid'] = $f['couponid'];
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
            // Деньги, которые остались у нас: без возвратов и отзывов.
            $where[] = "pu.paymentid > 0 AND pu.status = 'ok'";
        }
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            if (ctype_digit($q)) {
                $where[] = 'pu.id = :qid';
                $params['qid'] = (int) $q;
            } else {
                $like = [];
                foreach (['u.email', 'u.firstname', 'u.lastname', 'cp.code'] as $i => $col) {
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
        } else if (($r->status ?? 'ok') === 'refunded') {
            $t .= ' · ' . get_string('ordertype_refunded', $p);
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
     * Незавершённые попытки оплаты: карта / PayPal (kbb1: отменена, ошибка, не вернулся) и
     * Robokassa (счёт выставлен, оплата не подтверждена). Строки импорта подписок WordPress
     * (kbb1 'imported') — не попытки, их нет. Новые сверху.
     *
     * @param int $limit
     * @return array записи: gateway, ref (номер в сервисе оплаты), status, productid, productname, …
     */
    public static function attempts(int $limit = 200): array {
        global $DB;
        $names = \core_user\fields::for_name()->get_sql('u');
        $list = [];
        if (self::has_kbb1()) {
            foreach ($DB->get_records_sql(
                    "SELECT k.id, k.paymentarea, k.itemid, k.userid, k.method, k.status, k.amount, k.currency,
                            k.timecreated, u.email {$names->selects}
                       FROM {paygw_kbb1} k
                       JOIN {user} u ON u.id = k.userid
                      WHERE k.component = 'local_kabshop' AND k.status NOT IN ('paid', 'imported')
                   ORDER BY k.id DESC", $names->params, 0, $limit) as $r) {
                $r->gateway = 'kbb1';
                $r->ref = $r->id;
                $list[] = $r;
            }
        }
        if ($DB->get_manager()->table_exists('paygw_robokassa')) {
            foreach ($DB->get_records_sql(
                    "SELECT r.id, pm.paymentarea, pm.itemid, pm.userid, pm.amount, pm.currency,
                            r.timecreated, u.email {$names->selects}
                       FROM {paygw_robokassa} r
                       JOIN {payments} pm ON pm.id = r.paymentid
                       JOIN {user} u ON u.id = pm.userid
                      WHERE r.success = 0 AND pm.component = 'local_kabshop'
                   ORDER BY r.id DESC", $names->params, 0, $limit) as $r) {
                $r->gateway = 'robokassa';
                $r->ref = $r->id;
                $r->method = '';
                $r->status = 'unpaid';
                $list[] = $r;
            }
        }
        usort($list, fn($a, $b) => (int) $b->timecreated <=> (int) $a->timecreated);
        $list = array_slice($list, 0, $limit);
        foreach ($list as $r) {
            $r->productid = self::attempt_productid((string) $r->paymentarea, (int) $r->itemid);
            $r->productname = $r->productid
                ? $DB->get_field('local_kabshop_product', 'name', ['id' => $r->productid]) : null;
        }
        return $list;
    }

    /**
     * Товар попытки оплаты по зоне платежа: custom — намерение, renewal — подписка, иначе — сам товар.
     *
     * @param string $area
     * @param int $itemid
     * @return int 0 — не найден
     */
    public static function attempt_productid(string $area, int $itemid): int {
        global $DB;
        if ($area === 'custom') {
            return (int) $DB->get_field('local_kabshop_intent', 'productid', ['id' => $itemid]);
        }
        if ($area === 'renewal') {
            return (int) $DB->get_field('local_kabshop_sub', 'productid', ['id' => $itemid]);
        }
        return in_array($area, ['product', 'productalt'], true) ? $itemid : 0;
    }
}
