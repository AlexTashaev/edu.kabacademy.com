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
 * Предпросмотр письма «оплата прошла» для покупки — для админа (на dev письма выключены).
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\catalog;

require_once(__DIR__ . '/../../config.php');

$purchaseid = required_param('purchase', PARAM_INT);
require_login();
$context = context_system::instance();
require_capability('local/kabshop:manage', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/kabshop/mailpreview.php', ['purchase' => $purchaseid]));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('mailpreview', 'local_kabshop'));
$PAGE->set_heading(get_string('mailpreview', 'local_kabshop'));

$purchase = $DB->get_record('local_kabshop_purchase', ['id' => $purchaseid], '*', MUST_EXIST);
$payment = $DB->get_record('payments', ['id' => $purchase->paymentid], '*', MUST_EXIST);
$product = catalog::get_product($purchase->productid);
$user = core_user::get_user($purchase->userid, '*', MUST_EXIST);
$sub = $DB->get_record_select('local_kabshop_sub', 'productid = ? AND userid = ? AND (firstpaymentid = ? OR lastpaymentid = ?)',
    [$product->id, $user->id, $payment->id, $payment->id], '*', IGNORE_MULTIPLE) ?: null;

[$subject, , $html] = \local_kabshop\mailer::build_paid($product, $user, $purchaseid, $payment, $sub);

echo $OUTPUT->header();
echo html_writer::tag('p', html_writer::tag('strong', get_string('mailpreview_to', 'local_kabshop')) . ' ' . s($user->email));
echo html_writer::tag('p', html_writer::tag('strong', get_string('mailpreview_subject', 'local_kabshop')) . ' ' . s($subject));
echo html_writer::div($html, 'border rounded p-3 bg-white');
echo $OUTPUT->footer();
