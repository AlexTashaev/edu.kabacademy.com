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
 * Заказы витрины для администратора: все покупки, поиск, фильтры, выгрузка, незавершённые оплаты.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\orders;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$tab       = optional_param('tab', 'orders', PARAM_ALPHA);
$q         = optional_param('q', '', PARAM_RAW_TRIMMED);
$productid = optional_param('productid', 0, PARAM_INT);
$fromstr   = optional_param('from', '', PARAM_RAW_TRIMMED);
$tostr     = optional_param('to', '', PARAM_RAW_TRIMMED);
$page      = optional_param('page', 0, PARAM_INT);
$download  = optional_param('download', '', PARAM_ALPHA);

admin_externalpage_setup('local_kabshop_orders');
require_capability('local/kabshop:manage', context_system::instance());

$p = 'local_kabshop';
$todate = function (string $s, bool $endofday) {
    $t = $s !== '' ? strtotime($s . ' 00:00:00') : 0;
    return $t ? ($endofday ? $t + DAYSECS : $t) : 0;
};
$filters = ['q' => $q, 'productid' => $productid, 'from' => $todate($fromstr, false), 'to' => $todate($tostr, true)];
$baseparams = ['tab' => $tab, 'q' => $q, 'productid' => $productid, 'from' => $fromstr, 'to' => $tostr];
$baseurl = new moodle_url('/local/kabshop/orders.php', $baseparams);

$fmt = get_string('strftimedatetimeshort', 'langconfig');
$row = function (stdClass $r) use ($fmt) {
    return [
        $r->id,
        userdate($r->timecreated, $fmt),
        fullname($r),
        $r->email,
        format_string($r->productname ?? ('#' . $r->productid)),
        orders::type($r),
        \core_payment\helper::get_cost_as_string((float) $r->amount, $r->currency),
        orders::method($r),
        (string) ($r->txnid ?? ''),
        (string) ($r->serviceref ?? ''),
    ];
};
$columns = ['order', 'orderdate', 'buyer', 'email', 'subproduct', 'ordertype', 'subamount', 'paymethod', 'txn', 'serviceref'];

// Выгрузка в CSV — со всеми фильтрами, без постраничности.
if ($download === 'csv') {
    [$rows] = orders::search($filters, 0, 0);
    $head = array_map(fn($c) => get_string('col_' . $c, $p), $columns);
    \core\dataformat::download_data('orders_' . date('Ymd'), 'csv', $head, new ArrayIterator($rows),
        fn($r) => $row($r));
    exit;
}

echo $OUTPUT->header();

$tabs = [
    new tabobject('orders', new moodle_url('/local/kabshop/orders.php', ['tab' => 'orders']), get_string('orders', $p)),
    new tabobject('attempts', new moodle_url('/local/kabshop/orders.php', ['tab' => 'attempts']), get_string('attempts', $p)),
];
echo $OUTPUT->tabtree($tabs, $tab);

if ($tab === 'attempts') {
    echo html_writer::tag('p', get_string('attempts_help', $p), ['class' => 'text-muted']);
    $list = orders::attempts();
    if (!$list) {
        echo $OUTPUT->notification(get_string('noattempts', $p), 'info');
    } else {
        $t = new html_table();
        $t->head = [get_string('col_serviceref', $p), get_string('col_orderdate', $p), get_string('col_buyer', $p),
            get_string('col_email', $p), get_string('col_subamount', $p), get_string('col_paymethod', $p),
            get_string('substatus', $p)];
        $t->attributes['class'] = 'generaltable';
        foreach ($list as $a) {
            $t->data[] = [$a->id, userdate($a->timecreated, $fmt), fullname($a), s($a->email),
                \core_payment\helper::get_cost_as_string((float) $a->amount, $a->currency),
                get_string('method_' . $a->method, 'paygw_kbb1'), get_string('attempt_' . $a->status, $p)];
        }
        echo html_writer::table($t);
    }
    echo $OUTPUT->footer();
    exit;
}

// Фильтры.
$products = [0 => get_string('allproducts', $p)] +
    $DB->get_records_menu('local_kabshop_product', null, 'name', 'id, name');
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $baseurl->out_omit_querystring(),
    'class' => 'd-flex flex-wrap gap-2 align-items-end mb-3']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'tab', 'value' => 'orders']);
echo html_writer::div(html_writer::label(get_string('search'), 'kq', true, ['class' => 'd-block small']) .
    html_writer::empty_tag('input', ['type' => 'text', 'name' => 'q', 'id' => 'kq', 'value' => $q,
        'class' => 'form-control', 'placeholder' => get_string('searchhint', $p)]));
echo html_writer::div(html_writer::label(get_string('col_subproduct', $p), 'kp', true, ['class' => 'd-block small']) .
    html_writer::select($products, 'productid', $productid, false, ['id' => 'kp', 'class' => 'form-select']));
echo html_writer::div(html_writer::label(get_string('datefrom', $p), 'kf', true, ['class' => 'd-block small']) .
    html_writer::empty_tag('input', ['type' => 'date', 'name' => 'from', 'id' => 'kf', 'value' => $fromstr, 'class' => 'form-control']));
echo html_writer::div(html_writer::label(get_string('dateto', $p), 'kt', true, ['class' => 'd-block small']) .
    html_writer::empty_tag('input', ['type' => 'date', 'name' => 'to', 'id' => 'kt', 'value' => $tostr, 'class' => 'form-control']));
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('filter'), 'class' => 'btn btn-primary']);
echo html_writer::link(new moodle_url($baseurl, ['download' => 'csv']), get_string('exportcsv', $p),
    ['class' => 'btn btn-secondary']);
echo html_writer::end_tag('form');

[$rows, $total] = orders::search($filters, $page);
if (!$rows) {
    echo $OUTPUT->notification(get_string('noorders', $p), 'info');
} else {
    $sum = [];
    foreach (orders::search($filters + ['paidonly' => 1], 0, 0)[0] as $r) {
        $sum[$r->currency] = ($sum[$r->currency] ?? 0) + (float) $r->amount;
    }
    $sumstr = implode(' + ', array_map(fn($c, $v) => \core_payment\helper::get_cost_as_string($v, $c),
        array_keys($sum), $sum));
    echo html_writer::tag('p', get_string('orderstotal', $p, (object) ['count' => $total, 'sum' => $sumstr ?: '0']));

    $t = new html_table();
    $t->head = array_map(fn($c) => get_string('col_' . $c, $p), $columns);
    $t->attributes['class'] = 'generaltable';
    foreach ($rows as $r) {
        $cells = $row($r);
        $cells[2] = html_writer::link(new moodle_url('/user/profile.php', ['id' => $r->userid]), s($cells[2]));
        $cells[3] = s($cells[3]);
        $t->data[] = $cells;
    }
    echo html_writer::table($t);
    echo $OUTPUT->paging_bar($total, $page, orders::PERPAGE, $baseurl);
}

echo $OUTPUT->footer();
