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

/**
 * Возврат из external_payments после оплаты.
 *
 * Успех засчитывается только при верном goodkey: он есть лишь в адресе успеха, который
 * сервис отдаёт браузеру после оплаты. В woocommerce-bb такой проверки нет — там заказ
 * отмечается оплаченным по одному параметру action=good.
 *
 * @package    paygw_kbb1
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_payment\helper;

require_once(__DIR__ . '/../../../config.php');

$id = required_param('id', PARAM_INT);
$r  = required_param('r', PARAM_ALPHA);
$k  = optional_param('k', '', PARAM_ALPHANUM);

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/payment/gateway/kbb1/return.php', ['id' => $id, 'r' => $r]);

$rec = $DB->get_record('paygw_kbb1', ['id' => $id], '*', MUST_EXIST);
$back = $rec->returnurl ? new moodle_url($rec->returnurl) : new moodle_url('/');

if ($r !== 'good') {
    if ($rec->status === 'pending') {
        $DB->update_record('paygw_kbb1', (object) ['id' => $rec->id,
            'status' => $r === 'cancel' ? 'cancel' : 'error', 'timemodified' => time()]);
    }
    redirect($back, get_string($r === 'cancel' ? 'paymentcancelled' : 'paymentfailed', 'paygw_kbb1'),
        null, \core\output\notification::NOTIFY_WARNING);
}

if ($k === '' || !hash_equals($rec->goodkey, $k)) {
    throw new moodle_exception('invalidreturn', 'paygw_kbb1');
}

// Повторный заход на тот же адрес (обновили страницу) не должен выдать товар дважды.
$lock = \core\lock\lock_config::get_lock_factory('paygw_kbb1')->get_lock('payment_' . $rec->id, 10);
if (!$lock) {
    throw new moodle_exception('locktimeout');
}
try {
    $rec = $DB->get_record('paygw_kbb1', ['id' => $id], '*', MUST_EXIST);
    if ($rec->status !== 'paid') {
        $txnid  = optional_param('transaction_id', optional_param('ApprovalNo', '', PARAM_TEXT), PARAM_TEXT);
        $token  = optional_param('token', optional_param('vault_token', '', PARAM_TEXT), PARAM_TEXT);
        $authno = optional_param('authNo', '', PARAM_TEXT);

        $paymentid = helper::save_payment($rec->accountid, $rec->component, $rec->paymentarea, $rec->itemid,
            $rec->userid, (float) $rec->amount, $rec->currency, 'kbb1');
        $DB->update_record('paygw_kbb1', (object) [
            'id' => $rec->id, 'paymentid' => $paymentid, 'status' => 'paid',
            'txnid' => $txnid, 'token' => $token, 'authno' => $authno, 'timemodified' => time(),
        ]);
        helper::deliver_order($rec->component, $rec->paymentarea, $rec->itemid, $paymentid, $rec->userid);
    }
} finally {
    $lock->release();
}

redirect(helper::get_success_url($rec->component, $rec->paymentarea, $rec->itemid),
    get_string('paymentsuccessful', 'paygw_kbb1'), null, \core\output\notification::NOTIFY_SUCCESS);
