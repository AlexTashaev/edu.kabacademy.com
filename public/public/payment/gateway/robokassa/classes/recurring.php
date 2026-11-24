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

namespace paygw_robokassa;

use core_payment\helper;

/**
 * KAB patch (24.11.2026): автосписание по подписке витрины local_kabshop через повторный платёж
 * Robokassa — /Merchant/Recurring с PreviousInvoiceID первой оплаты (её pay.php отправил с
 * Recurring=true). Так же продлевал рублёвые подписки WooCommerce плагин Robokassa на WordPress
 * (46 продлений с 10.2024).
 *
 * Robokassa отвечает «OK<InvoiceID>» — запрос принят, а результат присылает на ResultURL. Поэтому
 * charge() возвращает ['pending' => paymentid]: подписку продлит колбэк (callback.php →
 * deliver_order('renewal')), а не подтверждённое к следующему запуску списание касса посчитает
 * неудачей (local_kabshop\subscriptions::charge_one).
 *
 * Собственное автосписание плагина (настройка recurrent, задача recurrent_payments) должно быть
 * выключено: у него свой график, и отмена подписки в кассе его не остановит.
 *
 * Вызывает local_kabshop\subscriptions::charge_one().
 *
 * @package    paygw_robokassa
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recurring {

    /**
     * Запросить очередной платёж подписки.
     *
     * @param \stdClass $sub local_kabshop_sub
     * @param \stdClass $product local_kabshop_product
     * @param \stdClass $user
     * @return array ['pending' => int paymentid] — запрос принят; ['error' => string] — нет
     */
    public static function charge(\stdClass $sub, \stdClass $product, \stdClass $user): array {
        global $CFG, $DB;
        require_once($CFG->libdir . '/filelib.php');

        if ($sub->currency !== 'RUB') {
            return ['error' => 'Robokassa recurring: only RUB subscriptions'];
        }
        // Повторять можно только платёж, который Robokassa подтвердила (success = 1, не тестовый).
        $first = $DB->get_records('paygw_robokassa', ['paymentid' => $sub->firstpaymentid, 'success' => 1], 'id', '*', 0, 1);
        if (!$first) {
            return ['error' => 'Robokassa recurring: first payment ' . $sub->firstpaymentid . ' is not confirmed'];
        }
        $gw = $DB->get_record('payment_gateways', ['accountid' => $sub->accountid, 'gateway' => 'robokassa']);
        $account = $DB->get_record('payment_accounts', ['id' => $sub->accountid]);
        if (!$gw || !$gw->enabled || !$account || !$account->enabled) {
            return ['error' => 'payment account or gateway disabled'];
        }
        $config = (object) json_decode((string) $gw->config, true);
        if (!empty($config->istestmode)) {
            return ['error' => 'Robokassa recurring: not available in test mode'];
        }
        if (empty($config->merchant_login) || empty($config->password1)) {
            return ['error' => 'Robokassa recurring: merchant login or password #1 is not set'];
        }
        $apiurl = (isset($config->origin) && $config->origin == 2) ? 'auth.robokassa.kz' : 'auth.robokassa.ru';
        $crypto = !empty($config->crypto) ? (string) $config->crypto : 'md5';

        $amount = number_format((float) $sub->amount, 2, '.', '');
        $name = \core_text::substr(format_string($product->name, true,
            ['context' => \context_system::instance(), 'escape' => false]), 0, 100);

        // Номер нового счёта — id платежа Moodle, как у первой оплаты (pay.php: InvId = paymentid).
        $paymentid = helper::save_payment($sub->accountid, 'local_kabshop', 'renewal', $sub->id,
            $sub->userid, (float) $amount, 'RUB', 'robokassa');
        $tx = (object) [
            'paymentid'   => $paymentid,
            'invoiceid'   => (string) $sub->firstpaymentid,
            'courseid'    => !empty($product->courseid) ? (int) $product->courseid : null,
            'timecreated' => time(),
        ];
        $tx->id = $DB->insert_record('paygw_robokassa', $tx);

        // Чек 54-ФЗ — как у первой оплаты (pay.php). Подпись и тело — как у рабочего плагина
        // WordPress: в подписи Receipt в URL-кодировке, в теле — он же (http_build_query кодирует
        // ещё раз, Robokassa декодирует один раз).
        $receipt = urlencode(json_encode([
            'sno' => $config->sno ?? null,
            'items' => [[
                'name' => $name,
                'quantity' => 1,
                'sum' => (float) $amount,
                'tax' => $config->tax ?? 'none',
            ]],
        ], JSON_UNESCAPED_UNICODE));
        $signature = hash($crypto, "{$config->merchant_login}:{$amount}:{$paymentid}:{$receipt}:{$config->password1}");
        $data = [
            'MerchantLogin'     => $config->merchant_login,
            'InvoiceID'         => $paymentid,
            'PreviousInvoiceID' => (int) $sub->firstpaymentid,
            'Description'       => $name,
            'SignatureValue'    => $signature,
            'OutSum'            => $amount,
            'Receipt'           => $receipt,
        ];

        $curl = new \curl();
        $response = $curl->post('https://' . $apiurl . '/Merchant/Recurring', http_build_query($data, '', '&'), [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_TIMEOUT' => 30,
            'CURLOPT_CONNECTTIMEOUT' => 10,
            'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            'CURLOPT_SSLVERSION' => CURL_SSLVERSION_TLSv1_2,
        ]);
        if (!empty($curl->errno)) {
            return ['error' => 'Robokassa recurring: connection error ' . $curl->error];
        }
        if (trim((string) $response) === 'OK' . $paymentid) {
            return ['pending' => $paymentid];
        }
        // Счёт остаётся неподтверждённым (success = 0), как брошенная оплата; неудачу ведёт касса.
        $text = \core_text::substr(trim(strip_tags((string) $response)), 0, 150);
        return ['error' => 'Robokassa recurring refused: ' . ($text !== '' ? $text : 'empty response')];
    }
}
