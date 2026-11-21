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

namespace local_kabwporders;

/**
 * Чтение реестра старых оплат. В реестр пишет только импорт.
 *
 * @package    local_kabwporders
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    /** Строк на странице реестра. */
    const PERPAGE = 50;

    /** Статусы, при которых деньги остались у нас (как в карточке человека). */
    const PAID_STATUSES = ['completed', 'processing'];

    /**
     * Поиск по реестру.
     *
     * @param array $filters q (номер заказа, почта, имя, номер транзакции), year, currency, status, userid
     * @param int $page
     * @param int $perpage 0 — без постраничности (выгрузка)
     * @return array [строки с полем items, всего]
     */
    public static function search(array $filters, int $page = 0, int $perpage = self::PERPAGE): array {
        global $DB;

        $where = ['1 = 1'];
        $params = [];
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            if (ctype_digit($q)) {
                $where[] = '(o.wporderid = :qid OR o.txnid = :qtxn)';
                $params['qid'] = (int) $q;
                $params['qtxn'] = $q;
            } else {
                $like = '%' . $DB->sql_like_escape($q) . '%';
                $where[] = '(' . $DB->sql_like('o.payeremail', ':q1', false) . ' OR ' .
                    $DB->sql_like('o.payername', ':q2', false) . ' OR ' .
                    $DB->sql_like('o.txnid', ':q3', false) . ')';
                $params += ['q1' => $like, 'q2' => $like, 'q3' => $like];
            }
        }
        $year = (int) ($filters['year'] ?? 0);
        if ($year > 0) {
            $where[] = 'o.timecreated >= :yfrom AND o.timecreated < :yto';
            $params['yfrom'] = gmmktime(0, 0, 0, 1, 1, $year);
            $params['yto'] = gmmktime(0, 0, 0, 1, 1, $year + 1);
        }
        foreach (['currency', 'status'] as $field) {
            if (!empty($filters[$field])) {
                $where[] = "o.$field = :$field";
                $params[$field] = (string) $filters[$field];
            }
        }
        if (!empty($filters['userid'])) {
            $where[] = 'o.userid = :userid';
            $params['userid'] = (int) $filters['userid'];
        }
        $wheresql = implode(' AND ', $where);

        $total = $DB->count_records_sql("SELECT COUNT(1) FROM {local_kabwporders_order} o WHERE $wheresql", $params);
        $rows = $DB->get_records_sql(
            "SELECT o.* FROM {local_kabwporders_order} o WHERE $wheresql ORDER BY o.wporderid DESC",
            $params, $perpage ? $page * $perpage : 0, $perpage);
        return [self::with_items($rows), $total];
    }

    /**
     * Добавляет к заказам их позиции (поле items).
     *
     * @param \stdClass[] $orders
     * @return \stdClass[]
     */
    public static function with_items(array $orders): array {
        global $DB;

        $byorder = [];
        foreach ($orders as $o) {
            $o->items = [];
            $byorder[$o->wporderid] = $o;
        }
        foreach (array_chunk(array_keys($byorder), 1000) as $chunk) {
            $items = $DB->get_records_list('local_kabwporders_item', 'wporderid', $chunk, 'wporderid, wpitemid');
            foreach ($items as $i) {
                $byorder[$i->wporderid]->items[] = $i;
            }
        }
        return $orders;
    }

    /**
     * Названия товаров заказа одной строкой.
     *
     * @param \stdClass $order заказ с полем items
     * @return string
     */
    public static function products(\stdClass $order): string {
        $names = [];
        foreach ($order->items ?? [] as $i) {
            $names[] = $i->productname . ($i->qty > 1 ? ' × ' . $i->qty : '');
        }
        return implode('; ', $names);
    }

    /**
     * Итоги реестра для сверки с WordPress.
     *
     * @return array ['currencies' => [валюта => объект], 'linked' => int, 'unlinked' => int, 'lastimport' => int]
     */
    public static function totals(): array {
        global $DB;

        $currencies = $DB->get_records_sql(
            "SELECT currency, COUNT(1) AS orders, SUM(amount) AS amount,
                    SUM(CASE WHEN refunded > 0 THEN 1 ELSE 0 END) AS refunds, SUM(refunded) AS refunded
               FROM {local_kabwporders_order}
           GROUP BY currency
           ORDER BY currency");
        return [
            'currencies' => $currencies,
            'linked'     => $DB->count_records_select('local_kabwporders_order', 'userid > 0'),
            'unlinked'   => $DB->count_records('local_kabwporders_order', ['userid' => 0]),
            'lastimport' => (int) get_config('local_kabwporders', 'lastimport'),
        ];
    }

    /**
     * Строки реестра одного человека с позициями, новые сверху.
     *
     * Ищем по userid; заказы без человека (гостевые) — по почте плательщика.
     *
     * @param int $userid user.id (0 — неизвестен)
     * @param string $email email
     * @return \stdClass[] заказы с полем items
     */
    public static function records_for_user(int $userid, string $email = ''): array {
        global $DB;

        $email = \core_text::strtolower(trim($email));
        $where = [];
        $params = [];
        if ($userid > 0) {
            $where[] = 'userid = :userid';
            $params['userid'] = $userid;
        }
        if ($email !== '') {
            $where[] = '(userid = 0 AND payeremail = :email)';
            $params['email'] = $email;
        }
        if (!$where) {
            return [];
        }
        $rows = $DB->get_records_select('local_kabwporders_order', implode(' OR ', $where), $params,
            'timecreated DESC, wporderid DESC');
        return self::with_items($rows);
    }

    /**
     * Старые оплаты человека — в том же виде, в каком карточка local_kabperson получает их из WordPress.
     *
     * @param int $userid user.id (0 — неизвестен)
     * @param string $email email
     * @return array заказы, новые сверху
     */
    public static function for_user(int $userid, string $email = ''): array {
        $out = [];
        foreach (self::records_for_user($userid, $email) as $o) {
            $product = self::products($o);
            $isdonation = (bool) preg_match('/пожертвован|взнос в фонд|donat/iu', $product);
            $out[] = [
                'source'   => 'kabacademy_wp',
                'id'       => (int) $o->wporderid,
                'number'   => (string) $o->wporderid,
                'date'     => date('c', (int) ($o->timepaid ?: $o->timecreated)),
                'product'  => $product,
                'kind'     => $isdonation ? 'donation' : 'course',
                'type'     => 'paid',
                'amount'   => round((float) $o->amount, 2),
                'refunded' => round((float) $o->refunded, 2),
                'currency' => (string) $o->currency,
                'status'   => (string) $o->status,
                'gateway'  => (string) ($o->gatewaytitle !== '' ? $o->gatewaytitle : $o->gateway),
            ];
        }
        return $out;
    }
}
