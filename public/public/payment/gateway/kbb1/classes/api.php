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

namespace paygw_kbb1;

/**
 * Клиент external_payments. Поля запроса — как в woocommerce-bb 1.2.0 (build_payload),
 * чтобы сервис и Priority получали то же, что от WordPress.
 *
 * @package    paygw_kbb1
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    /**
     * Адрес метода сервиса: {apiurl}{apipath}/{emv|paypal}/{action}.
     *
     * @param \stdClass $config
     * @param string $method
     * @param string $action new|charge
     * @return string
     */
    public static function endpoint(\stdClass $config, string $method, string $action): string {
        $base = rtrim(trim((string) $config->apiurl), '/');
        $path = '/' . trim(trim((string) ($config->apipath ?? '/woocommerce')), '/');
        return $base . ($path === '/' ? '' : $path) . '/' . $method . '/' . $action;
    }

    /**
     * Сколько платежей разрешить: настройка «сумма;платежей» — если сумма больше порога,
     * до N платежей (не больше 10), иначе 1. Как installments_config в woocommerce-bb.
     *
     * @param \stdClass $config
     * @param float $amount
     * @return int
     */
    public static function max_payments(\stdClass $config, float $amount): int {
        $inst = trim((string) ($config->installments ?? ''));
        if ($inst === '' || strpos($inst, ';') === false) {
            return 1;
        }
        [$limit, $n] = array_map('trim', explode(';', $inst, 2));
        $n = (int) $n;
        if ($n < 1) {
            return 1;
        }
        return $amount > (float) $limit ? min($n, 10) : 1;
    }

    /**
     * Язык страницы оплаты по языку пользователя Moodle.
     *
     * @return string HE|RU|EN
     */
    public static function language(): string {
        $lang = current_language();
        if (strpos($lang, 'he') === 0) {
            return 'HE';
        }
        if (strpos($lang, 'ru') === 0) {
            return 'RU';
        }
        return 'EN';
    }

    /**
     * Пожертвование ли это: для товаров витрины local_kabshop — вид товара «donation».
     * Сервис ставит по нему признак пожертвования (VAT=Y), как WordPress по категории donation.
     *
     * @param \stdClass $rec запись paygw_kbb1
     * @return bool
     */
    public static function is_donation(\stdClass $rec): bool {
        global $DB;
        if ($rec->component !== 'local_kabshop' || !class_exists('\local_kabshop\catalog')) {
            return false;
        }
        $productid = $rec->itemid;
        if ($rec->paymentarea === 'custom') {
            $productid = (int) $DB->get_field('local_kabshop_intent', 'productid', ['id' => $rec->itemid]);
        }
        return $DB->get_field('local_kabshop_product', 'kind', ['id' => $productid]) === 'donation';
    }

    /**
     * Тело запроса на создание платёжной сессии.
     *
     * @param \stdClass $config
     * @param \stdClass $rec запись paygw_kbb1
     * @param \stdClass $user
     * @param string $description
     * @return array
     */
    public static function build_payload(\stdClass $config, \stdClass $rec, \stdClass $user, string $description): array {
        $ret = function (string $r, array $extra = []) use ($rec) {
            return (new \moodle_url('/payment/gateway/kbb1/return.php', ['id' => $rec->id, 'r' => $r] + $extra))->out(false);
        };
        $donation = self::is_donation($rec);
        $plugin = \core_plugin_manager::instance()->get_plugin_info('paygw_kbb1');
        return [
            'UserKey'       => $rec->userkey,
            // Секрет goodkey — только в адресе успеха: сервис отправляет туда браузер лишь
            // после оплаты, поэтому подделать «успех», не заплатив, нельзя.
            'GoodURL'       => $ret('good', ['k' => $rec->goodkey]),
            'ErrorURL'      => $ret('error'),
            'CancelURL'     => $ret('cancel'),
            'Name'          => fullname($user),
            'Price'         => (float) $rec->amount,
            'Currency'      => $rec->currency,
            'Email'         => $user->email,
            'Phone'         => trim((string) ($user->phone1 ?: $user->phone2)) ?: '0000000000',
            'Street'        => '',
            'City'          => '',
            'Country'       => (string) $user->country,
            'Details'       => \core_text::substr(trim($description), 0, 250),
            'SKU'           => (string) $config->sku,
            'MaxPayments'   => self::max_payments($config, (float) $rec->amount),
            'VAT'           => $donation ? 'Y' : 'N',
            'TaxType'       => '',
            'TaxId'         => '',
            'Installments'  => 1,
            'Language'      => self::language(),
            // Номер платежа Moodle. Префикс подставляет сервис по токену этого сайта —
            // поэтому Moodle нужен СВОЙ токен, не токен WordPress.
            'Reference'     => (string) $rec->id,
            'IsVisual'      => false,
            'PluginVersion' => 'moodle-paygw_kbb1/' . ($plugin->release ?? ''),
        ];
    }

    /**
     * Создать платёжную сессию и получить адрес страницы оплаты.
     *
     * @param \stdClass $config
     * @param string $method emv|paypal
     * @param array $payload
     * @return string адрес, куда отправить покупателя
     * @throws \moodle_exception
     */
    public static function create_session(\stdClass $config, string $method, array $payload): string {
        $curl = new \curl();
        $curl->setHeader([
            'Content-Type: application/json',
            'Authorization: Bearer ' . trim((string) $config->apitoken),
        ]);
        $body = $curl->post(self::endpoint($config, $method, 'new'), json_encode($payload), [
            'CURLOPT_TIMEOUT' => 30,
            'CURLOPT_SSL_VERIFYPEER' => true,
        ]);
        $info = $curl->get_info();
        $code = (int) ($info['http_code'] ?? 0);
        $data = json_decode((string) $body, true);
        if ($curl->get_errno() || $code !== 200 || empty($data['url'])) {
            $msg = $data['error'] ?? ($curl->error ?: ('HTTP ' . $code));
            debugging('paygw_kbb1: create_session ' . $method . ' failed: ' . $msg, DEBUG_DEVELOPER);
            throw new \moodle_exception('gatewayerror', 'paygw_kbb1', '', s($msg));
        }
        return $data['url'];
    }
}
