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
 * Управление витриной: разделы и товары.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\catalog;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_kabshop_manage');
$context = context_system::instance();
require_capability('local/kabshop:manage', $context);

$PAGE->set_title(get_string('manage', 'local_kabshop'));
$PAGE->set_heading(get_string('manage', 'local_kabshop'));

echo $OUTPUT->header();

$p = 'local_kabshop';
echo html_writer::div(
    html_writer::link(new moodle_url('/local/kabshop/edit_section.php'), get_string('addsection', $p),
        ['class' => 'btn btn-secondary me-2']) .
    html_writer::link(new moodle_url('/local/kabshop/edit_product.php'), get_string('addproduct', $p),
        ['class' => 'btn btn-primary me-2']) .
    html_writer::link(new moodle_url('/local/kabshop/orders.php'), get_string('orders', $p),
        ['class' => 'btn btn-secondary me-2']) .
    html_writer::link(new moodle_url('/local/kabshop/index.php'), get_string('catalog', $p),
        ['class' => 'btn btn-link']),
    'mb-4');

foreach (catalog::get_sections(true) as $s) {
    $title = format_string($s->name) . ($s->visible ? '' : ' (' . get_string('hidden', 'local_kabshop') . ')');
    echo $OUTPUT->heading(html_writer::link(new moodle_url('/local/kabshop/edit_section.php', ['id' => $s->id]),
        $title), 3);
    if (!$s->products) {
        echo html_writer::tag('p', get_string('noproducts', $p), ['class' => 'text-muted']);
        continue;
    }
    $table = new html_table();
    $table->head = ['#', get_string('name'), get_string('kind', $p), get_string('price', $p),
        get_string('courseid', $p), get_string('paymentaccount', 'payment'), get_string('visible')];
    $table->attributes['class'] = 'generaltable';
    foreach ($s->products as $pr) {
        $table->data[] = [
            $pr->sortorder,
            html_writer::link(new moodle_url('/local/kabshop/edit_product.php', ['id' => $pr->id]),
                format_string($pr->name)) . ' ' .
            html_writer::link(new moodle_url('/local/kabshop/product.php', ['id' => $pr->id]), '↗',
                ['title' => get_string('view')]),
            get_string('kind_' . $pr->kind, $p),
            catalog::price_string($pr),
            $pr->courseid ? html_writer::link(new moodle_url('/course/view.php', ['id' => $pr->courseid]),
                $pr->courseid) : '—',
            $pr->accountid ?: '—',
            $pr->visible ? get_string('yes') : get_string('no'),
        ];
    }
    echo html_writer::table($table);
}

// Подписки: что и когда спишется, где ошибки.
$subs = $DB->get_records('local_kabshop_sub', null, 'timemodified DESC', '*', 0, 100);
echo $OUTPUT->heading(get_string('subscriptions', $p), 3);
if (!$subs) {
    echo html_writer::tag('p', get_string('nosubscriptions', $p), ['class' => 'text-muted']);
} else {
    $fmt = get_string('strftimedatetimeshort', 'langconfig');
    $table = new html_table();
    $table->head = ['#', get_string('user'), get_string('subproduct', $p), get_string('subamount', $p),
        get_string('substatus', $p), get_string('subnext', $p), get_string('subfails', $p)];
    $table->attributes['class'] = 'generaltable';
    foreach ($subs as $sub) {
        $u = core_user::get_user($sub->userid);
        $pr = catalog::get_product($sub->productid, IGNORE_MISSING);
        $table->data[] = [
            html_writer::link(new moodle_url('/local/kabshop/sub.php', ['id' => $sub->id]), $sub->id),
            $u ? html_writer::link(new moodle_url('/user/profile.php', ['id' => $u->id]), fullname($u)) : $sub->userid,
            $pr ? format_string($pr->name) : $sub->productid,
            \core_payment\helper::get_cost_as_string((float) $sub->amount, $sub->currency) . ' '
                . \local_kabshop\subscriptions::period_label((int) $sub->months) . ' · ' . s($sub->gateway),
            get_string('substatus_' . $sub->status, $p),
            userdate($sub->nextcharge, $fmt),
            $sub->failcount ? $sub->failcount . ': ' . s((string) $sub->lasterror) : '—',
        ];
    }
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
