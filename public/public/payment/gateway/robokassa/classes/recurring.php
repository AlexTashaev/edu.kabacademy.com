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
     * Сайт, с которого можно списывать. Магазин Robokassa у прода и dev общий, а dev — копия БД прода
     * с боевыми настройками шлюза: с другого адреса автосписание не идёт никогда.
     */
    const LIVE_WWWROOT = 'https://edu.kabacademy.com';

    /**
     * Метка в paygw_robokassa.recurrent у первой оплаты подписки кассы: pay.php отправил её с
     * Recurring=true. Отрицательная — задача плагина recurrent_payments берёт только recurrent > 0.
     */
    const KAB_PARENT = -1;

    /**
     * Запросить очередной платёж подписки.
     *
     * @param \stdClass $sub local_kabshop_sub
     * @param \stdClass $product local_kabshop_product
     * @param \stdClass $user
     * @return array ['pending' => int paymentid] — запрос принят; ['error' => string] — нет (с 'stop'
     *     и 'nomail' — повторять бессмысленно, покупателю не писать: это вопрос к администратору)
     */
    public static function charge(\stdClass $sub, \stdClass $product, \stdClass $user): array {
        global $CFG, $DB;
        require_once($CFG->libdir . '/filelib.php');

        if (rtrim((string) $CFG->wwwroot, '/') !== self::LIVE_WWWROOT) {
            // Не неудача покупателя: касса пропускает подписку, не трогая счётчик (переехал адрес
            // сайта — поменять LIVE_WWWROOT).
            return ['error' => 'Robokassa recurring: only on ' . self::LIVE_WWWROOT, 'skip' => true];
        }
        if ($sub->currency !== 'RUB') {
            return ['error' => 'Robokassa recurring: only RUB subscriptions', 'stop' => true, 'nomail' => true];
        }
        // Повторять можно только платёж, который Robokassa подтвердила (success = 1, не тестовый) и
        // который pay.php отправил с Recurring=true (метка KAB_PARENT). Подписки, оформленные до
        // 24.11.2026 или оплаченные не в рублях, этой метки не имеют — их продлевают вручную.
        $first = $DB->get_records('paygw_robokassa', ['paymentid' => $sub->firstpaymentid, 'success' => 1], 'id', '*', 0, 1);
        $first = $first ? reset($first) : null;
        if (!$first) {
            return ['error' => 'Robokassa recurring: first payment ' . $sub->firstpaymentid . ' is not confirmed',
                'stop' => true, 'nomail' => true];
        }
        if ((int) $first->recurrent !== self::KAB_PARENT) {
            return ['error' => 'Robokassa recurring: first payment ' . $sub->firstpaymentid . ' was not recurring',
                'stop' => true, 'nomail' => true];
        }
        $config = self::config((int) $sub->accountid);
        if (is_string($config)) {
            return ['error' => $config, 'nomail' => true];
        }
        if (!empty($config->istestmode)) {
            return ['error' => 'Robokassa recurring: not available in test mode', 'nomail' => true];
        }
        if (empty($config->merchant_login) || empty($config->password1)) {
            return ['error' => 'Robokassa recurring: merchant login or password #1 is not set', 'nomail' => true];
        }
        $apiurl = self::apiurl($config);
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

    /**
     * Чем кончился запрошенный платёж, подтверждения которого нет: спросить Robokassa (OpStateExt).
     *
     * @param \stdClass $sub local_kabshop_sub
     * @param int $paymentid номер счёта (InvoiceID)
     * @return string 'paid' — проведён; 'wait' — ещё в работе; 'failed' — отменён, возвращён или не
     *     найден; 'unknown' — Robokassa не ответила
     */
    public static function pending_state(\stdClass $sub, int $paymentid): string {
        global $CFG, $DB;
        require_once($CFG->libdir . '/filelib.php');
        $tx = $DB->get_records('paygw_robokassa', ['paymentid' => $paymentid], 'id', '*', 0, 1);
        $tx = $tx ? reset($tx) : null;
        if ($tx && in_array((int) $tx->success, [1, 3], true)) {
            return 'paid'; // Колбэк дошёл, но продление не записалось — выдача идемпотентна.
        }
        $config = self::config((int) $sub->accountid);
        if (is_string($config) || empty($config->merchant_login) || empty($config->password2)) {
            return 'unknown';
        }
        $crypto = !empty($config->crypto) ? (string) $config->crypto : 'md5';
        $signature = strtoupper(hash($crypto, "{$config->merchant_login}:{$paymentid}:{$config->password2}"));
        $url = 'https://' . self::apiurl($config) . '/Merchant/WebService/Service.asmx/OpStateExt?' . http_build_query([
            'MerchantLogin' => $config->merchant_login,
            'InvoiceID' => $paymentid,
            'Signature' => $signature,
        ], '', '&');
        $curl = new \curl();
        $xml = $curl->get($url, [], [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_TIMEOUT' => 30,
            'CURLOPT_CONNECTTIMEOUT' => 10,
            'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            'CURLOPT_SSLVERSION' => CURL_SSLVERSION_TLSv1_2,
        ]);
        if (!empty($curl->errno) || trim((string) $xml) === '') {
            return 'unknown';
        }
        $response = xmlize($xml, 1, 'UTF-8', false);
        $result = $response['OperationStateResponse']['#']['Result'][0]['#']['Code'][0]['#'] ?? null;
        if ($result === null) {
            return 'unknown';
        }
        if ((string) $result === '3') {
            return 'failed'; // Счёта с таким номером у магазина нет.
        }
        if ((string) $result !== '0') {
            return 'unknown';
        }
        $state = (string) ($response['OperationStateResponse']['#']['State'][0]['#']['Code'][0]['#'] ?? '');
        if ($state === '100') {
            return 'paid';
        }
        if ($state === '5') {
            // Счёт создан, денег нет. Повторный платёж проходит за секунды — двое суток в этом
            // состоянии значит отказ банка, который Robokassa так и не закрыла.
            $created = (int) $DB->get_field('payments', 'timecreated', ['id' => $paymentid]);
            if ($created && time() - $created > 2 * DAYSECS) {
                return 'failed';
            }
        }
        if (in_array($state, ['5', '50', '80'], true)) {
            return 'wait'; // Создан, деньги получены и зачисляются, приостановлен.
        }
        if (in_array($state, ['10', '60'], true)) {
            return 'failed'; // Отменён без оплаты, деньги возвращены.
        }
        return 'unknown';
    }

    /**
     * Отметить счёт оплаченным после выдачи по сверке (подтверждение не дошло): поздний колбэк
     * по нему ответит OK и второй раз не выдаст.
     *
     * @param int $paymentid
     */
    public static function mark_paid(int $paymentid): void {
        global $DB;
        $DB->execute('UPDATE {paygw_robokassa} SET success = 1 WHERE paymentid = ? AND success = 0', [$paymentid]);
    }

    /**
     * Настройки шлюза счёта подписки.
     *
     * @param int $accountid
     * @return \stdClass|string настройки или текст ошибки
     */
    protected static function config(int $accountid) {
        global $DB;
        $gw = $DB->get_record('payment_gateways', ['accountid' => $accountid, 'gateway' => 'robokassa']);
        $account = $DB->get_record('payment_accounts', ['id' => $accountid]);
        if (!$gw || !$gw->enabled || !$account || !$account->enabled) {
            return 'payment account or gateway disabled';
        }
        return (object) json_decode((string) $gw->config, true);
    }

    /**
     * Адрес API: Россия или Казахстан.
     *
     * @param \stdClass $config
     * @return string
     */
    protected static function apiurl(\stdClass $config): string {
        return (isset($config->origin) && $config->origin == 2) ? 'auth.robokassa.kz' : 'auth.robokassa.ru';
    }
}
