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

use core_payment\helper;

/**
 * Автосписание по подписке витрины: токен карты (EMV) или vault_token (PayPal) из первой
 * оплаты → /emv/charge или /paypal/charge. Как scheduled_subscription_payment в woocommerce-bb.
 *
 * Вызывает local_kabshop\subscriptions::charge_one().
 *
 * @package    paygw_kbb1
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recurring {

    /**
     * Списать очередной платёж подписки.
     *
     * @param \stdClass $sub local_kabshop_sub
     * @param \stdClass $product local_kabshop_product
     * @param \stdClass $user
     * @return array ['paymentid' => int] при успехе или ['error' => string]
     */
    public static function charge(\stdClass $sub, \stdClass $product, \stdClass $user): array {
        global $CFG, $DB;
        require_once($CFG->libdir . '/filelib.php');

        $first = $DB->get_record('paygw_kbb1', ['paymentid' => $sub->firstpaymentid], '*', IGNORE_MULTIPLE);
        if (!$first || (string) $first->token === '') {
            return ['error' => 'no saved card token for the first payment'];
        }
        $gw = $DB->get_record('payment_gateways', ['accountid' => $sub->accountid, 'gateway' => 'kbb1']);
        $account = $DB->get_record('payment_accounts', ['id' => $sub->accountid]);
        if (!$gw || !$gw->enabled || !$account || !$account->enabled) {
            return ['error' => 'payment account or gateway disabled'];
        }
        $config = (object) json_decode((string) $gw->config, true);

        $now = time();
        $rec = (object) [
            'paymentid'    => 0,
            'component'    => 'local_kabshop',
            'paymentarea'  => 'renewal',
            'itemid'       => $sub->id,
            'userid'       => $sub->userid,
            'accountid'    => $sub->accountid,
            'amount'       => $sub->amount,
            'currency'     => $sub->currency,
            'method'       => $first->method,
            'userkey'      => \core\uuid::generate(),
            'goodkey'      => random_string(48),
            'status'       => 'pending',
            'returnurl'    => '',
            'timecreated'  => $now,
            'timemodified' => $now,
        ];
        $rec->id = $DB->insert_record('paygw_kbb1', $rec);

        $payload = api::build_payload($config, $rec, $user, format_string($product->name));
        $payload['Token'] = $first->token;
        $payload['IsRecurring'] = true;
        if ($first->method === 'emv') {
            $payload['ApprovalNo'] = (string) $first->authno;
        }

        $res = api::charge($config, $first->method, $payload);
        if (!empty($res['error'])) {
            $DB->update_record('paygw_kbb1', (object) ['id' => $rec->id, 'status' => 'error',
                'timemodified' => time()]);
            return ['error' => $res['error']];
        }

        $paymentid = helper::save_payment($sub->accountid, 'local_kabshop', 'renewal', $sub->id,
            $sub->userid, (float) $sub->amount, $sub->currency, 'kbb1');
        $DB->update_record('paygw_kbb1', (object) [
            'id' => $rec->id, 'paymentid' => $paymentid, 'status' => 'paid', 'txnid' => $res['txnid'],
            'token' => $first->token, 'authno' => $first->authno, 'timemodified' => time(),
        ]);
        return ['paymentid' => $paymentid];
    }
}
