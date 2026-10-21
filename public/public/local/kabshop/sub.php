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
 * Подписка для администратора: история платежей, ближайший платёж и его настройка.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\catalog;
use local_kabshop\subscriptions;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$id = required_param('id', PARAM_INT);
$do = optional_param('do', '', PARAM_ALPHA);
$url = new moodle_url('/local/kabshop/sub.php', ['id' => $id]);
admin_externalpage_setup('local_kabshop_manage', '', null, $url);
require_capability('local/kabshop:manage', context_system::instance());
$p = 'local_kabshop';

$sub = subscriptions::get($id);
$product = catalog::get_product($sub->productid, IGNORE_MISSING);
$user = core_user::get_user($sub->userid);

// «Списать сейчас» — настоящее списание с сохранённой карты; только POST с подтверждением.
if ($do === 'chargenow' && data_submitted() && confirm_sesskey() && optional_param('confirm', 0, PARAM_BOOL)) {
    if ($sub->status !== 'active' || !subscriptions::charger($sub->gateway)) {
        redirect($url, get_string('chargenow_unavailable', $p), null, \core\output\notification::NOTIFY_WARNING);
    }
    $ok = subscriptions::charge_one($sub);
    $sub = subscriptions::get($id);
    redirect($url, $ok ? get_string('chargenow_ok', $p) : get_string('chargenow_failed', $p, s((string) $sub->lasterror)),
        null, $ok ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_ERROR);
}

$form = new \local_kabshop\form\sub_form($url);
$form->set_data(['id' => $sub->id, 'nextcharge' => $sub->nextcharge, 'amount' => $sub->amount, 'status' => $sub->status]);
if ($data = $form->get_data()) {
    $upd = (object) ['id' => $sub->id, 'nextcharge' => (int) $data->nextcharge, 'amount' => round((float) $data->amount, 2),
        'status' => $data->status, 'timemodified' => time()];
    if (!empty($data->resetfails)) {
        $upd->failcount = 0;
        $upd->lastattempt = 0;
        $upd->lasterror = null;
    }
    $DB->update_record('local_kabshop_sub', $upd);
    // Доступ к курсу подписки — до нового ближайшего платежа + запас.
    if ($product && $product->courseid && $data->status === 'active') {
        $instance = $DB->get_record('enrol', ['courseid' => $product->courseid, 'enrol' => 'manual'], '*', IGNORE_MULTIPLE);
        if ($instance && $DB->record_exists('user_enrolments', ['enrolid' => $instance->id, 'userid' => $sub->userid])) {
            $DB->set_field('user_enrolments', 'timeend', (int) $data->nextcharge + subscriptions::GRACE,
                ['enrolid' => $instance->id, 'userid' => $sub->userid]);
        }
    }
    redirect($url, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$PAGE->set_title(get_string('subno', $p, $sub->id));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('subno', $p, $sub->id));

$fmt = get_string('strftimedatetime', 'langconfig');
$dl = [
    get_string('col_buyer', $p) => $user ? html_writer::link(new moodle_url('/user/profile.php', ['id' => $user->id]),
        fullname($user)) . ' · ' . s($user->email) : $sub->userid,
    get_string('col_subproduct', $p) => $product ? format_string($product->name) : '#' . $sub->productid,
    get_string('subamount', $p) => \core_payment\helper::get_cost_as_string((float) $sub->amount, $sub->currency) . ' '
        . subscriptions::period_label((int) $sub->months),
    get_string('substatus', $p) => get_string('substatus_' . $sub->status, $p),
    get_string('subnext', $p) => html_writer::tag('strong', userdate($sub->nextcharge, $fmt)) .
        ($sub->status === 'active' && $sub->nextcharge <= time() ? ' · ' . get_string('subdue', $p) : ''),
    get_string('col_paymethod', $p) => s($sub->gateway) . (subscriptions::charger($sub->gateway) ? '' : ' · ' . get_string('subgatewayown', $p)),
    get_string('subfails', $p) => $sub->failcount ? $sub->failcount . ' · ' . s((string) $sub->lasterror) .
        ($sub->lastattempt ? ' · ' . userdate($sub->lastattempt, $fmt) : '') : '—',
];
$html = '';
foreach ($dl as $k => $v) {
    $html .= html_writer::tag('dt', $k, ['class' => 'col-sm-3']) . html_writer::tag('dd', $v, ['class' => 'col-sm-9']);
}
echo html_writer::tag('dl', $html, ['class' => 'row']);

// История платежей подписки.
echo $OUTPUT->heading(get_string('subhistory', $p), 4);
$hist = $DB->get_records_select('local_kabshop_purchase', 'productid = ? AND userid = ? AND paymentid > 0',
    [$sub->productid, $sub->userid], 'id DESC');
$t = new html_table();
$t->head = [get_string('col_order', $p), get_string('col_orderdate', $p), get_string('col_ordertype', $p), get_string('col_subamount', $p)];
$t->attributes['class'] = 'generaltable';
foreach ($hist as $h) {
    $area = $DB->get_field('payments', 'paymentarea', ['id' => $h->paymentid]);
    $t->data[] = [html_writer::link(new moodle_url('/local/kabshop/order.php', ['id' => $h->id]), $h->id),
        userdate($h->timecreated, $fmt),
        get_string($area === 'renewal' ? 'ordertype_renewal' : 'ordertype_paid', $p),
        \core_payment\helper::get_cost_as_string((float) $h->amount, $h->currency)];
}
echo html_writer::table($t);

echo $OUTPUT->heading(get_string('subedit', $p), 4);
echo html_writer::tag('p', get_string('subedit_help', $p), ['class' => 'text-muted']);
$form->display();

if ($sub->status === 'active' && subscriptions::charger($sub->gateway)) {
    echo $OUTPUT->heading(get_string('chargenow', $p), 4);
    echo html_writer::start_tag('form', ['method' => 'post', 'action' => $url->out(false)]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'do', 'value' => 'chargenow']);
    echo html_writer::div(html_writer::checkbox('confirm', 1, false, ' ' . get_string('chargenow_confirm', $p,
        \core_payment\helper::get_cost_as_string((float) $sub->amount, $sub->currency)), ['required' => 'required']), 'mb-2');
    echo html_writer::tag('button', get_string('chargenow', $p), ['type' => 'submit', 'class' => 'btn btn-warning']);
    echo html_writer::end_tag('form');
}
echo html_writer::div(html_writer::link(new moodle_url('/local/kabshop/manage.php'), '← ' . get_string('manage', $p)), 'mt-3');
echo $OUTPUT->footer();
