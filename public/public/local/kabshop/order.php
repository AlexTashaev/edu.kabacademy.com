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
 * Заказ для администратора: подробности и действия (отзыв доступа, письмо, подписка).
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\catalog;
use local_kabshop\orders;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$id = required_param('id', PARAM_INT);
$do = optional_param('do', '', PARAM_ALPHA);

$url = new moodle_url('/local/kabshop/order.php', ['id' => $id]);
admin_externalpage_setup('local_kabshop_orders', '', null, $url);
require_capability('local/kabshop:manage', context_system::instance());
$p = 'local_kabshop';

$purchase = $DB->get_record('local_kabshop_purchase', ['id' => $id], '*', MUST_EXIST);
$product = catalog::get_product($purchase->productid, IGNORE_MISSING);
$user = core_user::get_user($purchase->userid);
$payment = $purchase->paymentid ? $DB->get_record('payments', ['id' => $purchase->paymentid]) : null;
$sub = ($product && $payment) ? $DB->get_record_select('local_kabshop_sub',
    'productid = ? AND userid = ? AND (firstpaymentid = ? OR lastpaymentid = ?)',
    [$product->id, $user->id, $payment->id, $payment->id], '*', IGNORE_MULTIPLE) : null;
if (!$sub && $product) {
    $sub = \local_kabshop\subscriptions::get_active($product->id, $user->id);
}

// Действия — только POST с sesskey.
if ($do && data_submitted() && confirm_sesskey()) {
    if ($do === 'revoke') {
        $ok = catalog::revoke($purchase->id, $USER->id, optional_param('note', '', PARAM_TEXT));
        redirect($url, get_string($ok ? 'revoked' : 'revokefailed', $p), null,
            $ok ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_WARNING);
    }
    if ($do === 'refund') {
        $ok = catalog::refund($purchase->id, $USER->id, optional_param('note', '', PARAM_TEXT),
            (bool) optional_param('unenrol', 0, PARAM_BOOL));
        redirect($url, get_string($ok ? 'refunded' : 'refundfailed', $p), null,
            $ok ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_WARNING);
    }
    if ($do === 'transfer') {
        // Куда переносим — по адресу: живой аккаунт, адрес ровно у одного.
        $email = core_text::strtolower(trim(optional_param('email', '', PARAM_RAW_TRIMMED)));
        $targets = $email === '' ? [] : $DB->get_records_select('user',
            'deleted = 0 AND ' . $DB->sql_equal('email', ':email', false), ['email' => $email], 'id', 'id, email');
        if (count($targets) !== 1) {
            redirect($url, get_string(count($targets) ? 'transfermany' : 'transfernouser', $p, s($email)), null,
                \core\output\notification::NOTIFY_WARNING);
        }
        $target = reset($targets);
        if ((int) $target->id === (int) $purchase->userid || isguestuser($target)) {
            redirect($url, get_string('transferbaduser', $p), null, \core\output\notification::NOTIFY_WARNING);
        }
        $n = catalog::transfer($purchase->id, (int) $target->id, $USER->id);
        redirect($url, get_string('transferred', $p, (object) ['count' => $n, 'email' => s($target->email)]), null,
            \core\output\notification::NOTIFY_SUCCESS);
    }
    if ($do === 'resend' && $product && $user) {
        if ($payment) {
            \local_kabshop\mailer::paid($product, $user->id, $purchase->id, $payment, $sub ?: null);
        } else {
            \local_kabshop\mailer::granted($product, $user->id, $purchase->id);
        }
        redirect($url, get_string('mailresent', $p, s($user->email)), null, \core\output\notification::NOTIFY_SUCCESS);
    }
    if ($do === 'cancelsub' && $sub && $sub->status === 'active') {
        \local_kabshop\subscriptions::cancel($sub->id, $sub->userid);
        redirect($url, get_string('subcancelled', $p), null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

$PAGE->set_title(get_string('orderno', $p, $purchase->id));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('orderno', $p, $purchase->id));
if ($purchase->status === 'revoked') {
    echo $OUTPUT->notification(get_string('orderrevoked', $p, userdate($purchase->timerevoked)), 'warning');
} else if ($purchase->status === 'refunded') {
    echo $OUTPUT->notification(get_string('orderrefunded', $p, userdate($purchase->timerevoked)), 'warning');
}

[$rows] = orders::search(['q' => (string) $purchase->id], 0, 1);
$r = reset($rows);
$fmt = get_string('strftimedatetime', 'langconfig');
$dl = [
    get_string('col_orderdate', $p) => userdate($purchase->timecreated, $fmt),
    get_string('col_buyer', $p) => $user ? html_writer::link(new moodle_url('/user/profile.php', ['id' => $user->id]),
        fullname($user)) . ' · ' . s($user->email) . ' · ' .
        html_writer::link(new moodle_url('/local/kabshop/orders.php', ['userid' => $user->id]),
            get_string('buyerordersn', $p, $DB->count_records('local_kabshop_purchase', ['userid' => $user->id])))
        : $purchase->userid,
    get_string('col_subproduct', $p) => $product ? html_writer::link(new moodle_url('/local/kabshop/product.php',
        ['id' => $product->id]), format_string($product->name)) : '#' . $purchase->productid,
    get_string('col_ordertype', $p) => $r ? orders::type($r) : '',
    get_string('col_subamount', $p) => \core_payment\helper::get_cost_as_string((float) $purchase->amount, $purchase->currency),
];
if ($purchase->couponid && ($c = $DB->get_record('local_kabshop_coupon', ['id' => $purchase->couponid]))) {
    $dl[get_string('couponcode', $p)] = s($c->code) . ' · −' .
        \core_payment\helper::get_cost_as_string((float) $purchase->amountoff, $purchase->currency);
}
if (!empty($purchase->taxtype)) {
    $dl[get_string('taxpayer', $p)] = catalog::tax_label($purchase->taxtype, $purchase->taxid);
}
if ($r) {
    $dl[get_string('col_paymethod', $p)] = orders::method($r);
    if ($r->txnid) {
        $dl[get_string('col_txn', $p)] = s($r->txnid);
    }
    if ($r->serviceref) {
        $dl[get_string('col_serviceref', $p)] = s($r->serviceref);
    }
}
if ($purchase->grantedby) {
    $dl[get_string('grantedby', $p)] = fullname(core_user::get_user($purchase->grantedby));
}
if ($purchase->note) {
    $dl[get_string('grantnote', $p)] = s($purchase->note);
}
if ($product && $product->courseid && $user) {
    $ue = $DB->get_record_sql("SELECT ue.* FROM {user_enrolments} ue JOIN {enrol} e ON e.id = ue.enrolid
        WHERE e.courseid = ? AND e.enrol = 'manual' AND ue.userid = ?", [$product->courseid, $user->id], IGNORE_MULTIPLE);
    $dl[get_string('courseaccess', $p)] = $ue
        ? ($ue->timeend ? get_string('accessuntil', $p, userdate($ue->timeend, $fmt)) : get_string('accessforever', $p))
        : get_string('accessnone', $p);
}
if ($sub) {
    $dl[get_string('subscriptions', $p)] = html_writer::link(new moodle_url('/local/kabshop/sub.php', ['id' => $sub->id]),
        '#' . $sub->id) . ' · ' . get_string('substatus_' . $sub->status, $p) . ' · ' .
        get_string('subnext', $p) . ': ' . userdate($sub->nextcharge, $fmt);
}
$html = '';
foreach ($dl as $k => $v) {
    $html .= html_writer::tag('dt', $k, ['class' => 'col-sm-3']) . html_writer::tag('dd', $v, ['class' => 'col-sm-9']);
}
echo html_writer::tag('dl', $html, ['class' => 'row']);

// Действия.
$btn = function (string $do, string $label, string $class, string $extra = '') use ($url) {
    return html_writer::start_tag('form', ['method' => 'post', 'action' => $url->out(false), 'class' => 'd-inline-block me-2 mb-2'])
        . html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()])
        . html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'do', 'value' => $do])
        . $extra
        . html_writer::tag('button', $label, ['type' => 'submit', 'class' => 'btn ' . $class])
        . html_writer::end_tag('form');
};
echo html_writer::start_div('mt-3');
echo $btn('resend', get_string('mailresend', $p), 'btn-secondary');
if ($sub && $sub->status === 'active') {
    echo $btn('cancelsub', get_string('subcancel_admin', $p), 'btn-outline-secondary');
}
echo html_writer::end_div();

// Возврат денег: сами деньги возвращают в кабинете шлюза, здесь — отметка, подписка и доступ.
if ($purchase->status === 'ok' && $purchase->paymentid > 0) {
    $unenrol = '';
    if ($product && $product->courseid) {
        $unenrol = html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'unenrol', 'value' => 0])
            . html_writer::tag('label', html_writer::empty_tag('input', ['type' => 'checkbox', 'name' => 'unenrol',
                'value' => 1, 'checked' => 'checked', 'class' => 'form-check-input me-1']) .
                get_string('refundunenrol', $p), ['class' => 'form-check-label me-3']);
    }
    $note = html_writer::empty_tag('input', ['type' => 'text', 'name' => 'note', 'required' => 'required',
        'class' => 'form-control d-inline-block w-auto me-2', 'placeholder' => get_string('refundnote', $p)]);
    echo html_writer::tag('h3', get_string('refundtitle', $p), ['class' => 'h5 mt-4']);
    echo html_writer::tag('p', get_string('refundhelp', $p), ['class' => 'text-muted small mb-2']);
    echo $btn('refund', get_string('refund', $p), 'btn-warning', $note . $unenrol);
}

// Отозвать доступ без возврата денег (или после возврата, если доступ тогда оставили).
if ($purchase->status === 'ok' || ($purchase->status === 'refunded' && $product && $product->courseid)) {
    $note = html_writer::empty_tag('input', ['type' => 'text', 'name' => 'note', 'class' => 'form-control d-inline-block w-auto me-2',
        'placeholder' => get_string('revokenote', $p), 'required' => 'required']);
    echo html_writer::tag('h3', get_string('revoketitle', $p), ['class' => 'h5 mt-4']);
    echo $btn('revoke', get_string('revoke', $p), 'btn-danger', $note);
}

// Перенос на другой аккаунт (дубль): заказ, подписка с её оплатами, доступ к курсу.
$email = html_writer::empty_tag('input', ['type' => 'email', 'name' => 'email', 'required' => 'required',
    'class' => 'form-control d-inline-block w-auto me-2', 'placeholder' => get_string('transferemail', $p)]);
echo html_writer::tag('h3', get_string('transfertitle', $p), ['class' => 'h5 mt-4']);
echo html_writer::tag('p', get_string('transferhelp', $p), ['class' => 'text-muted small mb-2']);
echo $btn('transfer', get_string('transfer', $p), 'btn-outline-primary', $email);
echo html_writer::div(html_writer::link(new moodle_url('/local/kabshop/orders.php'), '← ' . get_string('orders', $p)), 'mt-3');
echo $OUTPUT->footer();
