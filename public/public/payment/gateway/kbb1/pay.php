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
 * Старт оплаты: выбор способа (карта / PayPal), создание сессии в external_payments,
 * переход на страницу оплаты.
 *
 * @package    paygw_kbb1
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_payment\helper;
use paygw_kbb1\api;
use paygw_kbb1\gateway;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/filelib.php');

require_login();
require_sesskey();

$component   = required_param('component', PARAM_COMPONENT);
$paymentarea = required_param('paymentarea', PARAM_AREA);
$itemid      = required_param('itemid', PARAM_INT);
$description = required_param('description', PARAM_TEXT);
$method      = optional_param('method', '', PARAM_ALPHA);
$returnurl   = optional_param('returnurl', '', PARAM_LOCALURL);

$params = ['component' => $component, 'paymentarea' => $paymentarea, 'itemid' => $itemid,
    'description' => $description, 'sesskey' => sesskey()];
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/payment/gateway/kbb1/pay.php', $params);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'paygw_kbb1'));
$PAGE->set_heading(get_string('choosemethod', 'paygw_kbb1'));

$config   = (object) helper::get_gateway_configuration($component, $paymentarea, $itemid, 'kbb1');
$payable  = helper::get_payable($component, $paymentarea, $itemid);
$currency = $payable->get_currency();
$cost     = helper::get_rounded_cost($payable->get_amount(), $currency, helper::get_gateway_surcharge('kbb1'));

if (!in_array($currency, gateway::get_supported_currencies(), true)) {
    throw new moodle_exception('unsupportedcurrency', 'paygw_kbb1', '', $currency);
}
$methods = gateway::methods($config);
if (!$methods) {
    throw new moodle_exception('nomethod', 'paygw_kbb1');
}
// Куда вернуть при отмене: страница, с которой пришли (только адреса этого сайта).
if ($returnurl === '' && !empty($_SERVER['HTTP_REFERER'])) {
    $ref = clean_param($_SERVER['HTTP_REFERER'], PARAM_LOCALURL);
    $returnurl = $ref ?: '';
}

// Два способа и способ не выбран — показываем выбор.
if ($method === '' && count($methods) > 1) {
    echo $OUTPUT->header();
    echo html_writer::tag('p', s($description) . ' — ' . helper::get_cost_as_string($cost, $currency));
    $buttons = '';
    foreach ($methods as $m) {
        $url = new moodle_url('/payment/gateway/kbb1/pay.php', $params + ['method' => $m, 'returnurl' => $returnurl]);
        $buttons .= html_writer::link($url, get_string('method_' . $m, 'paygw_kbb1'),
            ['class' => 'btn btn-primary btn-lg me-2 mb-2']);
    }
    echo html_writer::div($buttons, 'mt-3');
    if ($returnurl) {
        echo html_writer::div(html_writer::link($returnurl, get_string('cancel')), 'mt-3');
    }
    echo $OUTPUT->footer();
    exit;
}
$method = $method ?: $methods[0];
if (!in_array($method, $methods, true)) {
    throw new moodle_exception('nomethod', 'paygw_kbb1');
}

$now = time();
$rec = (object) [
    'paymentid'    => 0,
    'component'    => $component,
    'paymentarea'  => $paymentarea,
    'itemid'       => $itemid,
    'userid'       => $USER->id,
    'accountid'    => $payable->get_account_id(),
    'amount'       => $cost,
    'currency'     => $currency,
    'method'       => $method,
    'userkey'      => \core\uuid::generate(),
    'goodkey'      => random_string(48),
    'status'       => 'pending',
    'returnurl'    => $returnurl,
    'timecreated'  => $now,
    'timemodified' => $now,
];
$rec->id = $DB->insert_record('paygw_kbb1', $rec);

try {
    $url = api::create_session($config, $method, api::build_payload($config, $rec, $USER, $description));
} catch (moodle_exception $e) {
    $DB->update_record('paygw_kbb1', (object) ['id' => $rec->id, 'status' => 'error', 'timemodified' => time()]);
    throw $e;
}
redirect($url);
