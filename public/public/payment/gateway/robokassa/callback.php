<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     paygw_robokassa
 * @copyright   2024 Alex Orlov <snickser@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_payment\helper;
use paygw_robokassa\notifications;

require("../../../config.php");
global $CFG, $USER, $DB;
require_once($CFG->libdir . '/filelib.php');

defined('MOODLE_INTERNAL') || die();

$invid     = required_param('InvId', PARAM_INT);
$outsumm   = required_param('OutSum', PARAM_TEXT); // TEXT only!
$signature = required_param('SignatureValue', PARAM_ALPHANUMEXT);

if (!$robokassatx = $DB->get_record('paygw_robokassa', ['paymentid' => $invid])) {
    throw new \moodle_exception('FAIL. Not a valid transaction id');
}
// KAB patch (аудит 24.11.2026): уже подтверждённый счёт (1 — боевой, 3 — тестовый) не выдаём
// второй раз. Robokassa повторяет уведомление, пока не получит OK{InvId}.
$alreadypaid = in_array((int) $robokassatx->success, [1, 3], true);
$nextpay = null;

if (!$payment = $DB->get_record('payments', ['id' => $robokassatx->paymentid])) {
    throw new \moodle_exception('FAIL. Not a valid payment.');
}
$component   = $payment->component;
$paymentarea = $payment->paymentarea;
$itemid      = $payment->itemid;
$paymentid   = $payment->id;
$userid      = $payment->userid;

// Get config.
$config = (object) helper::get_gateway_configuration($component, $paymentarea, $itemid, 'robokassa');

// Check country.
$apiurl = 'auth.robokassa.ru';
if (isset($config->origin) && $config->origin == 2) {
    $apiurl = 'auth.robokassa.kz';
}

// Check test-mode.
if ($config->istestmode) {
    $mrhpass2 = $config->test_password2; // Merchant test_pass2 here.
    $robokassatx->success = 3;
} else {
    $mrhpass2 = $config->password2;      // Merchant pass2 here.
    $robokassatx->success = 1;
}

// Check crypto or set default.
if (isset($config->crypto)) {
    $crypto = $config->crypto;
} else {
    $crypto = 'md5';
}

// Check crc.
// KAB patch: сравнение без утечки по времени и без зависимости от регистра подписи.
$crc = strtoupper(hash($crypto, "$outsumm:$invid:$mrhpass2"));
if (!hash_equals($crc, strtoupper($signature))) {
    throw new \moodle_exception('FAIL. Signature does not match.');
}

// KAB patch: повтор уведомления по уже подтверждённому счёту — сразу OK, без второй выдачи и письма.
if ($alreadypaid) {
    die('OK' . $invid);
}

// KAB patch: два уведомления одновременно — второе ждёт первое и видит, что счёт уже подтверждён.
$cblock = \core\lock\lock_config::get_lock_factory('paygw_robokassa')->get_lock('callback_' . $invid, 30);
if (!$cblock) {
    throw new \moodle_exception('FAIL. Locked.');
}
\core_shutdown_manager::register_function(function() use ($cblock) {
    $cblock->release();
});
$robokassatx = $DB->get_record('paygw_robokassa', ['id' => $robokassatx->id], '*', MUST_EXIST);
if (in_array((int) $robokassatx->success, [1, 3], true)) {
    die('OK' . $invid);
}
$robokassatx->success = $config->istestmode ? 3 : 1;

// KAB patch: сумма из уведомления должна совпасть с выставленной (mdl_payments.amount записан в pay.php).
// Подпись Password2 сама сумму не защищает от чужого счёта с тем же InvId (общий магазин dev/прод).
// Для счёта не в рублях Robokassa присылает сумму в рублях — сверить её не с чем, только пишем в журнал.
if ($payment->currency === 'RUB') {
    if (abs((float) $outsumm - (float) $payment->amount) > 0.01) {
        error_log("paygw_robokassa: InvId $invid amount mismatch: OutSum $outsumm, expected {$payment->amount} RUB — not delivered");
        throw new \moodle_exception('FAIL. Amount mismatch.');
    }
} else {
    error_log("paygw_robokassa: InvId $invid paid OutSum $outsumm RUB for {$payment->amount} {$payment->currency}, amount not checked");
}

// Check invoice.
if ($config->checkinvoice && !$config->istestmode) {
    $mrhlogin = $config->merchant_login;
    $location = 'https://' . $apiurl . '/Merchant/WebService/Service.asmx/OpStateExt';
    $crc = strtoupper(hash($crypto, "$mrhlogin:$invid:$mrhpass2"));
    $location .= "?MerchantLogin=$mrhlogin" .
        "&InvoiceID=$invid" .
        "&Signature=$crc";
    $options = [
       'CURLOPT_RETURNTRANSFER' => true,
       'CURLOPT_TIMEOUT' => 30,
       'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
       'CURLOPT_SSLVERSION' => CURL_SSLVERSION_TLSv1_2,
       ];
    $curl = new curl();
    $xmlresponse = $curl->get($location, $options);
    $response = xmlize($xmlresponse, $whitespace = 1, $encoding = 'UTF-8', false);

    $err = $response['OperationStateResponse']['#']['Result'][0]['#']['Code'][0]['#'];
    if ($err) {
        throw new \moodle_exception('FAIL. Invoice result error.');
    }
    $err = $response['OperationStateResponse']['#']['State'][0]['#']['Code'][0]['#'];
    if ($err !== '100') {
        throw new \moodle_exception('FAIL. Invoice not paid.');
    }
}

// For currency conversion.
$payment->amount = (float)$outsumm;
if ($payment->currency !== 'RUB') {
    $payment->currency = 'RUB';
}

// Update payment.
$payment->timemodified = time();
$DB->update_record('payments', $payment);

// Deliver order.
helper::deliver_order($component, $paymentarea, $itemid, $paymentid, $userid);

// Notify user.
if ($robokassatx->recurrent) {
    $reason = 'Success recurrent';
    $nextpay = userdate($robokassatx->recurrent, "%d %B %Y, %k:%M");
} else {
    $reason = 'Success completed';
}
notifications::notify(
    $userid,
    $payment->amount,
    $payment->currency,
    $paymentid,
    $reason,
    $nextpay
);

// Update paygw.
if (!$DB->update_record('paygw_robokassa', $robokassatx)) {
    throw new \moodle_exception('FAIL. Update db error.');
} else {
    die('OK' . $invid);
}
