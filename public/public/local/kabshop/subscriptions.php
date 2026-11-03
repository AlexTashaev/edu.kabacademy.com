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
 * «Мои подписки»: список и отмена автосписания.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\catalog;
use local_kabshop\subscriptions;

require_once(__DIR__ . '/../../config.php');

require_login(null, false);
if (isguestuser()) {
    redirect(get_login_url());
}

$url = new moodle_url('/local/kabshop/subscriptions.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('mysubscriptions', 'local_kabshop'));
$PAGE->set_heading(get_string('mysubscriptions', 'local_kabshop'));

$cancel = optional_param('cancel', 0, PARAM_INT);
if ($cancel && confirm_sesskey()) {
    $ok = subscriptions::cancel($cancel, $USER->id);
    redirect($url, get_string($ok ? 'subcancelled' : 'subcancelfailed', 'local_kabshop'), null,
        $ok ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_WARNING);
}

echo $OUTPUT->header();

$subs = subscriptions::for_user($USER->id);
if (!$subs) {
    echo $OUTPUT->notification(get_string('nosubscriptions', 'local_kabshop'), 'info');
    echo $OUTPUT->footer();
    exit;
}

$p = 'local_kabshop';
$table = new html_table();
$table->head = [get_string('subproduct', $p), get_string('subamount', $p), get_string('substatus', $p),
    get_string('subnext', $p), ''];
$table->attributes['class'] = 'generaltable';
$fmt = get_string('strftimedate', 'langconfig');
foreach ($subs as $sub) {
    $product = catalog::get_product($sub->productid, IGNORE_MISSING);
    $name = $product
        ? html_writer::link(new moodle_url('/local/kabshop/product.php', ['id' => $product->id]), format_string($product->name))
        : '—';
    $action = '';
    if ($sub->status === 'active') {
        $action = $OUTPUT->single_button(new moodle_url($url, ['cancel' => $sub->id, 'sesskey' => sesskey()]),
            get_string('subcancel', $p), 'post');
    }
    $next = $sub->status === 'active' ? userdate($sub->nextcharge, $fmt)
        : get_string('subpaiduntil', $p, userdate($sub->nextcharge, $fmt));
    $table->data[] = [
        $name,
        \core_payment\helper::get_cost_as_string((float) $sub->amount, $sub->currency) . ' '
            . subscriptions::period_label((int) $sub->months),
        get_string('substatus_' . $sub->status, $p),
        $next,
        $action,
    ];
}
echo html_writer::table($table);
echo html_writer::tag('p', get_string('subcancelnote', $p), ['class' => 'text-muted']);
echo $OUTPUT->footer();
