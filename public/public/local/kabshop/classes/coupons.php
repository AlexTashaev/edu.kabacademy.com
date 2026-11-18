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
 * Купоны: код → скидка в % или фиксированной суммой. Цена со скидкой фиксируется на сервере
 * (local_kabshop_intent, как сумма на выбор) и идёт в оплату в зоне 'custom'. Для подписки
 * скидка — только на первый платёж.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coupons {

    /**
     * Код к виду хранения: без пробелов, верхним регистром.
     *
     * @param string $code
     * @return string
     */
    public static function normalize(string $code): string {
        return \core_text::strtoupper(preg_replace('/\s+/u', '', $code));
    }

    /**
     * Купон по коду.
     *
     * @param string $code
     * @return \stdClass|null
     */
    public static function find(string $code): ?\stdClass {
        global $DB;
        $code = self::normalize($code);
        return $code === '' ? null : ($DB->get_record('local_kabshop_coupon', ['code' => $code]) ?: null);
    }

    /**
     * Почему купон нельзя применить к товару (ключ строки) или null — можно.
     *
     * @param \stdClass $c
     * @param \stdClass $product
     * @param int $userid
     * @return string|null
     */
    public static function check(\stdClass $c, \stdClass $product, int $userid): ?string {
        global $DB;
        $now = time();
        if (!$c->enabled || ($c->validfrom && $now < $c->validfrom) || ($c->validto && $now > $c->validto)) {
            return 'coupon_expired';
        }
        if ($c->maxuses && $c->uses >= $c->maxuses) {
            return 'coupon_usedup';
        }
        if (!self::allowed_for($c, $userid)) {
            return 'coupon_notforyou';
        }
        $ids = array_filter(array_map('intval', explode(',', (string) $c->productids)));
        if (($ids && !in_array((int) $product->id, $ids, true)) || !empty($product->customamount)
                || catalog::is_free($product) || $product->kind === 'external') {
            return 'coupon_notforproduct';
        }
        if ($c->kind === 'fixed' && $c->currency && $c->currency !== $product->currency) {
            return 'coupon_notforproduct';
        }
        // Подписке нужна оплата, чтобы сохранить карту: 100% скидка дала бы бессрочный доступ.
        if (!empty($product->recurmonths) && self::amount_off($c, (float) $product->price) >= (float) $product->price) {
            return 'coupon_notforproduct';
        }
        if ($c->peruser && $DB->record_exists('local_kabshop_coupon_use', ['couponid' => $c->id, 'userid' => $userid])) {
            return 'coupon_alreadyused';
        }
        return null;
    }

    /**
     * Можно ли пользователю применить купон: у купона «только для этих адресов» e-mail аккаунта
     * должен быть в списке. Пустой список — без ограничения.
     *
     * @param \stdClass $c
     * @param int $userid
     * @return bool
     */
    public static function allowed_for(\stdClass $c, int $userid): bool {
        global $DB;
        if (empty($c->emailonly) || !$DB->record_exists('local_kabshop_coupon_email', ['couponid' => $c->id])) {
            return true;
        }
        $email = \core_text::strtolower(trim((string) $DB->get_field('user', 'email', ['id' => $userid])));
        return $email !== '' && $DB->record_exists('local_kabshop_coupon_email', ['couponid' => $c->id, 'email' => $email]);
    }

    /**
     * Разобрать список адресов: через запятую, точку с запятой, пробел или с новой строки.
     *
     * @param string $raw
     * @return array [верные адреса нижним регистром без повторов, неверные]
     */
    public static function parse_emails(string $raw): array {
        $ok = [];
        $bad = [];
        foreach (preg_split('/[\s,;]+/u', $raw, -1, PREG_SPLIT_NO_EMPTY) as $e) {
            $e = \core_text::strtolower(trim($e, " \t\n\r\0\x0B<>\"'"));
            if ($e === '') {
                continue;
            }
            if (\core_text::strlen($e) <= 100 && validate_email($e)) {
                $ok[$e] = $e;
            } else {
                $bad[$e] = $e;
            }
        }
        return [array_values($ok), array_values($bad)];
    }

    /**
     * Адреса купона.
     *
     * @param int $couponid
     * @return array email => запись
     */
    public static function emails(int $couponid): array {
        global $DB;
        $list = [];
        foreach ($DB->get_records('local_kabshop_coupon_email', ['couponid' => $couponid], 'id') as $r) {
            $list[$r->email] = $r;
        }
        return $list;
    }

    /**
     * Сохранить список адресов: новые добавить (письмо им ещё не отправлено), убранные удалить.
     *
     * @param int $couponid
     * @param array $emails верные адреса нижним регистром
     * @return int сколько адресов ещё без письма
     */
    public static function save_emails(int $couponid, array $emails): int {
        global $DB;
        $have = self::emails($couponid);
        foreach (array_diff_key($have, array_flip($emails)) as $r) {
            $DB->delete_records('local_kabshop_coupon_email', ['id' => $r->id]);
        }
        foreach (array_diff($emails, array_keys($have)) as $e) {
            $DB->insert_record('local_kabshop_coupon_email',
                (object) ['couponid' => $couponid, 'email' => $e, 'timesent' => 0, 'timecreated' => time()]);
        }
        return $DB->count_records('local_kabshop_coupon_email', ['couponid' => $couponid, 'timesent' => 0]);
    }

    /**
     * Поставить в очередь письма с кодом тем адресам купона, кому ещё не отправляли.
     *
     * @param int $couponid
     */
    public static function queue_mail(int $couponid): void {
        $task = new task\send_coupon_emails();
        $task->set_custom_data(['couponid' => $couponid]);
        \core\task\manager::queue_adhoc_task($task, true);
    }

    /**
     * Скидка в деньгах для цены товара (не больше цены).
     *
     * @param \stdClass $c
     * @param float $price
     * @return float
     */
    public static function amount_off(\stdClass $c, float $price): float {
        $off = $c->kind === 'percent' ? $price * min(100, max(0, (float) $c->value)) / 100 : (float) $c->value;
        return round(min($price, max(0, $off)), 2);
    }

    /**
     * Зафиксировать цену со скидкой для покупателя.
     *
     * @param \stdClass $c
     * @param \stdClass $product
     * @param int $userid
     * @return \stdClass intent (amount может быть 0 — тогда товар выдаётся без оплаты)
     */
    public static function create_intent(\stdClass $c, \stdClass $product, int $userid): \stdClass {
        global $DB;
        $off = self::amount_off($c, (float) $product->price);
        $intent = (object) [
            'productid' => $product->id,
            'userid' => $userid,
            'amount' => round((float) $product->price - $off, 2),
            'currency' => $product->currency,
            'couponid' => $c->id,
            'amountoff' => $off,
            'timecreated' => time(),
        ];
        $intent->id = $DB->insert_record('local_kabshop_intent', $intent);
        return $intent;
    }

    /**
     * Учесть использование купона в заказе.
     *
     * @param int $couponid
     * @param int $userid
     * @param int $purchaseid
     * @param float $amountoff
     * @param string $currency
     */
    public static function redeem(int $couponid, int $userid, int $purchaseid, float $amountoff, string $currency): void {
        global $DB;
        $DB->insert_record('local_kabshop_coupon_use', (object) [
            'couponid' => $couponid, 'userid' => $userid, 'purchaseid' => $purchaseid,
            'amountoff' => $amountoff, 'currency' => $currency, 'timecreated' => time(),
        ]);
        $DB->execute('UPDATE {local_kabshop_coupon} SET uses = uses + 1 WHERE id = ?', [$couponid]);
    }

    /**
     * Скидка строкой: «−20%» или «−10,00 $».
     *
     * @param \stdClass $c
     * @return string
     */
    public static function label(\stdClass $c): string {
        return $c->kind === 'percent'
            ? '−' . format_float((float) $c->value, -1) . '%'
            : '−' . \core_payment\helper::get_cost_as_string((float) $c->value, (string) ($c->currency ?: 'USD'));
    }
}
