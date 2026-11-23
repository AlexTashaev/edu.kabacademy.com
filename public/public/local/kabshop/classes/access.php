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
 * Доступ к сервису по оплате на витрине — для внешних сервисов (ИИ-ассистент AI MAK).
 *
 * Какие товары дают доступ — настройка local_kabshop/accessproducts (id через запятую).
 * Доступ есть, если по одному из этих товаров у человека с этим email:
 *  - подписка 'active' и дата следующего списания не прошла больше чем на GRACE назад
 *    (Robokassa списывает сама и о прекращении не сообщает — без этого условия
 *    брошенная подписка давала бы доступ вечно);
 *  - подписка 'cancelled', но оплаченный период ещё идёт;
 *  - ручная выдача (grant.php) не отозвана — бессрочно.
 * Ответ — только «есть/нет» и до какого числа: ни имени, ни оплат наружу не уходит.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class access {

    /**
     * Товары, дающие доступ.
     *
     * @return int[]
     */
    public static function product_ids(): array {
        $raw = (string) get_config('local_kabshop', 'accessproducts');
        $ids = array_filter(array_map('intval', preg_split('/[\s,;]+/', $raw)));
        return array_values(array_unique($ids));
    }

    /**
     * Есть ли доступ у email.
     *
     * @param string $email
     * @param int $now для тестов
     * @return array ['active' => bool, 'until' => int (0 — бессрочно или нет доступа), 'source' => string]
     */
    public static function check(string $email, int $now = 0): array {
        global $DB;
        $now = $now ?: time();
        $none = ['active' => false, 'until' => 0, 'source' => 'none'];

        $email = \core_text::strtolower(trim($email));
        $productids = self::product_ids();
        if ($email === '' || !validate_email($email) || !$productids) {
            return $none;
        }
        $userids = $DB->get_fieldset_select('user', 'id',
            'deleted = 0 AND ' . $DB->sql_equal('email', ':email', false), ['email' => $email]);
        if (!$userids) {
            return $none;
        }
        [$uin, $up] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'u');
        [$pin, $pp] = $DB->get_in_or_equal($productids, SQL_PARAMS_NAMED, 'p');

        if ($DB->record_exists_select('local_kabshop_purchase',
                "userid $uin AND productid $pin AND status = 'ok' AND grantedby > 0", $up + $pp)) {
            return ['active' => true, 'until' => 0, 'source' => 'grant'];
        }

        $best = $none;
        $subs = $DB->get_records_select('local_kabshop_sub',
            "userid $uin AND productid $pin AND status IN ('active', 'cancelled')", $up + $pp);
        foreach ($subs as $sub) {
            $until = (int) $sub->nextcharge;
            $end = $sub->status === 'active' ? $until + subscriptions::GRACE : $until;
            if ($end > $now && $until > $best['until']) {
                $best = ['active' => true, 'until' => $until, 'source' => 'subscription'];
            }
        }
        return $best;
    }
}
