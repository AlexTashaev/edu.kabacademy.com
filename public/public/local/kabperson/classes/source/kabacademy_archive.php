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

namespace local_kabperson\source;

/**
 * Заказы старого WP kabacademy из реестра local_kabwporders (этап 6 спеки: WP выключен).
 *
 * Отдаёт то же, что source\kabacademy_wp::fetch(), но без сети: заказы — через
 * \local_kabwporders\api::for_user(). Отличия от живого WP:
 * — в реестре только оплаченные заказы (плюс немногие оплаченные и позже отменённые),
 *   «ждёт оплаты» и «не прошли» сюда не попадают;
 * — пожертвование узнаётся по названию товара (категорий WooCommerce в реестре нет);
 * — даты регистрации в WP нет; имя и адрес — плательщика из последнего заказа.
 *
 * @package    local_kabperson
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class kabacademy_archive {

    /** Статусы, при которых деньги остались у нас (как в реестре и WP-адаптере). */
    const PAID_STATUSES = ['completed', 'processing'];

    /**
     * Установлен ли реестр.
     *
     * @return bool
     */
    public static function available(): bool {
        global $DB;
        return class_exists('\local_kabwporders\api')
            && $DB->get_manager()->table_exists('local_kabwporders_order');
    }

    /**
     * Заказы по пользователю Moodle и/или email.
     *
     * Moodle по email никого не нашёл ($moodleuserid = 0), а оплаты с этого адреса
     * привязаны к пользователю Moodle — отдаём его id в identity.moodle_user_id
     * (api::build() покажет этого человека с сигналом moodle_email_differs) и берём все
     * его заказы, а не только оплаченные с этого адреса.
     *
     * @param string $email нормализованный email
     * @param int $moodleuserid id пользователя Moodle (0 — неизвестен)
     * @return array как у kabacademy_wp::fetch(): state, found, identity, orders, donor
     */
    public static function fetch(string $email, int $moodleuserid = 0): array {
        global $DB;
        $result = ['state' => 'error', 'found' => false, 'identity' => null, 'orders' => [], 'donor' => []];
        if (!self::available()) {
            return $result;
        }
        try {
            $userid = $moodleuserid;
            if ($userid <= 0 && $email !== '') {
                $userid = (int) $DB->get_field_sql(
                    "SELECT MAX(userid) FROM {local_kabwporders_order} WHERE payeremail = :email",
                    ['email' => $email]);
            }
            $orders = \local_kabwporders\api::for_user($userid, $email);
            // Кто платил: то же условие, что у api::records_for_user(), только нужные поля.
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
            $payers = $where ? $DB->get_records_select('local_kabwporders_order', implode(' OR ', $where), $params,
                'timecreated DESC, wporderid DESC', 'id, wpuserid, payername, payeremail') : [];
        } catch (\Throwable $e) {
            return $result; // Схема реестра поменялась — блок «error», карточка живёт.
        }
        $result['state'] = 'ok';
        if (!$orders) {
            return $result;
        }

        $latest = reset($payers);
        $wpuserid = 0;
        $payeremails = [];
        foreach ($payers as $r) {
            if (!$wpuserid && (int) $r->wpuserid > 0) {
                $wpuserid = (int) $r->wpuserid;
            }
            if ((string) $r->payeremail !== '') {
                $payeremails[(string) $r->payeremail] = true;
            }
        }
        $signals = [];
        if ($email !== '' && $payeremails && (count($payeremails) > 1 || !isset($payeremails[$email]))) {
            $signals[] = 'order_email_mismatch';
        }
        $name = $latest ? trim((string) $latest->payername) : '';
        $result['found'] = true;
        $result['identity'] = [
            'wp_user_id'          => $wpuserid,
            'name'                => $name !== '' ? $name : null,
            'email'               => $latest ? \core_text::strtolower(trim((string) $latest->payeremail)) : '',
            'moodle_user_id'      => max(0, $userid),
            'registered_at'       => null,
            'matched_by'          => $moodleuserid > 0 ? 'moodle_user_id' : 'email',
            'possible_duplicates' => $signals,
        ];

        foreach ($orders as &$o) {
            $o['source'] = 'kabacademy_archive';
            $paid = $o['amount'] > 0 && in_array($o['status'], self::PAID_STATUSES, true);
            if ($o['kind'] === 'donation' && $paid && $o['currency'] !== '') {
                $result['donor'][$o['currency']] = round(($result['donor'][$o['currency']] ?? 0)
                    + $o['amount'] - $o['refunded'], 2);
            }
        }
        unset($o);
        $result['orders'] = $orders;
        return $result;
    }
}
