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
$p = 'local_kabshop';
$manageurl = new moodle_url('/local/kabshop/manage.php');

// Видимость и порядок прямо из списка (ссылки с sesskey, как в управлении курсами Moodle).
$action = optional_param('action', '', PARAM_ALPHA);
if ($action !== '') {
    require_sesskey();
    $type = required_param('type', PARAM_ALPHA) === 'section' ? 'section' : 'product';
    $table = 'local_kabshop_' . $type;
    $item = $DB->get_record($table, ['id' => required_param('id', PARAM_INT)], '*', MUST_EXIST);
    if ($action === 'toggle') {
        $DB->update_record($table, (object) ['id' => $item->id, 'visible' => $item->visible ? 0 : 1,
            'timemodified' => time()]);
    } else if ($action === 'move') {
        // Сосед меняется местами, весь список получает порядок 10, 20, 30… — как в форме.
        $siblings = array_values($DB->get_records($table, $type === 'product' ? ['sectionid' => $item->sectionid] : null,
            'sortorder, id', 'id'));
        $ids = array_map(fn($r) => (int) $r->id, $siblings);
        $pos = array_search((int) $item->id, $ids, true);
        $swap = optional_param('dir', 'down', PARAM_ALPHA) === 'up' ? $pos - 1 : $pos + 1;
        if ($pos !== false && isset($ids[$swap])) {
            [$ids[$pos], $ids[$swap]] = [$ids[$swap], $ids[$pos]];
            foreach ($ids as $i => $id) {
                $DB->set_field($table, 'sortorder', ($i + 1) * 10, ['id' => $id]);
            }
        }
    }
    redirect(new moodle_url($manageurl, [], 'kabshop-s' . ($type === 'product' ? $item->sectionid : $item->id)));
}

// Значки действий: глаз, стрелки.
$icon = function (array $params, string $pix, string $title) use ($manageurl): string {
    global $OUTPUT;
    return $OUTPUT->action_icon(new moodle_url($manageurl, $params + ['sesskey' => sesskey()]),
        new pix_icon($pix, $title));
};
$controls = function (string $type, stdClass $item, bool $first, bool $last) use ($icon, $p): string {
    $base = ['type' => $type, 'id' => $item->id];
    return ($first ? '' : $icon($base + ['action' => 'move', 'dir' => 'up'], 't/up', get_string('moveup')))
        . ($last ? '' : $icon($base + ['action' => 'move', 'dir' => 'down'], 't/down', get_string('movedown')))
        . $icon($base + ['action' => 'toggle'], $item->visible ? 't/hide' : 't/show',
            get_string($item->visible ? 'hide' : 'show'));
};

echo $OUTPUT->header();
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

$sections = array_values(catalog::get_sections(true));
echo html_writer::tag('p', get_string('manageorderhelp', $p), ['class' => 'text-muted small']);
foreach ($sections as $si => $s) {
    $title = format_string($s->name) . ($s->visible ? '' : ' (' . get_string('hidden', 'local_kabshop') . ')');
    $cond = catalog::restrictions_summary($s, false);
    echo html_writer::start_div('', ['id' => 'kabshop-s' . $s->id]);
    echo $OUTPUT->heading(html_writer::link(new moodle_url('/local/kabshop/edit_section.php', ['id' => $s->id]),
        $title) . ' ' . $controls('section', $s, $si === 0, $si === count($sections) - 1) .
        $OUTPUT->action_icon(new moodle_url('/local/kabshop/delete.php', ['type' => 'section', 'id' => $s->id]),
        new pix_icon('t/delete', get_string('delete_section', $p))), 3);
    if ($cond !== '') {
        echo html_writer::tag('p', get_string('visibleto', $p, $cond), ['class' => 'small text-muted']);
    }
    if (!$s->products) {
        echo html_writer::tag('p', get_string('noproducts', $p), ['class' => 'text-muted']);
        echo html_writer::end_div();
        continue;
    }
    $table = new html_table();
    $table->head = ['#', get_string('name'), get_string('kind', $p), get_string('price', $p),
        get_string('courseid', $p), get_string('conditions_short', $p), get_string('paymentaccount', 'payment'),
        get_string('visible'), ''];
    $table->attributes['class'] = 'generaltable';
    $n = count($s->products);
    foreach (array_values($s->products) as $i => $pr) {
        $row = new html_table_row([
            $i + 1,
            html_writer::link(new moodle_url('/local/kabshop/edit_product.php', ['id' => $pr->id]),
                format_string($pr->name)) . ' ' .
            html_writer::link(new moodle_url('/local/kabshop/product.php', ['id' => $pr->id]), '↗',
                ['title' => get_string('view')]),
            get_string('kind_' . $pr->kind, $p),
            implode(' · ', array_map([catalog::class, 'price_string'], catalog::variants($pr))),
            $pr->courseid ? html_writer::link(new moodle_url('/course/view.php', ['id' => $pr->courseid]),
                $pr->courseid) : '—',
            catalog::restrictions_summary($pr) ?: '—',
            implode(' · ', array_map(fn($v) => $v->accountid ?: '—', catalog::variants($pr))),
            $pr->visible ? get_string('yes') : get_string('no'),
            $controls('product', $pr, $i === 0, $i === $n - 1) .
            $OUTPUT->action_icon(new moodle_url('/local/kabshop/delete.php', ['type' => 'product', 'id' => $pr->id]),
                new pix_icon('t/delete', get_string('delete_product', $p))),
        ]);
        if (!$pr->visible) {
            $row->attributes['class'] = 'dimmed_text';
        }
        $table->data[] = $row;
    }
    echo html_writer::table($table);
    echo html_writer::end_div();
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
            ($sub->failcount ? $sub->failcount . ': ' . s((string) $sub->lasterror) : '—')
                . (\local_kabshop\subscriptions::pending_paymentid($sub) ? ' · ' . get_string('subpendingshort', $p) : ''),
        ];
    }
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
