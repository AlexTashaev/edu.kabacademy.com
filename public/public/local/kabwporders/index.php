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
 * Реестр оплат старого WordPress: поиск, фильтры, итоги для сверки, выгрузка CSV.
 *
 * @package    local_kabwporders
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabwporders\api;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$q        = optional_param('q', '', PARAM_RAW_TRIMMED);
$year     = optional_param('year', 0, PARAM_INT);
$currency = optional_param('currency', '', PARAM_ALPHA);
$status   = optional_param('status', '', PARAM_ALPHAEXT);
$userid   = optional_param('userid', 0, PARAM_INT);
$page     = optional_param('page', 0, PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);

admin_externalpage_setup('local_kabwporders_index');
require_capability('local/kabwporders:view', context_system::instance());

$p = 'local_kabwporders';
$filters = ['q' => $q, 'year' => $year, 'currency' => $currency, 'status' => $status, 'userid' => $userid];
$baseurl = new moodle_url('/local/kabwporders/index.php', array_filter($filters));

$fmt = get_string('strftimedatetimeshort', 'langconfig');
$statusname = function (string $s) use ($p) {
    $key = 'status_' . str_replace('-', '', $s);
    return get_string_manager()->string_exists($key, $p) ? get_string($key, $p) : $s;
};
$money = fn($amount, string $cur) => format_float((float) $amount, 2) . ' ' . $cur;
$columns = ['order', 'date', 'payer', 'email', 'products', 'amount', 'refunded', 'status', 'gateway', 'txn'];

// Выгрузка в CSV — со всеми фильтрами, без постраничности.
if ($download === 'csv') {
    [$rows] = api::search($filters, 0, 0);
    $head = array_map(fn($c) => get_string('col_' . $c, $p), $columns);
    \core\dataformat::download_data('wp_orders_' . date('Ymd'), 'csv', $head, new ArrayIterator($rows),
        fn($r) => [
            $r->wporderid,
            userdate($r->timepaid ?: $r->timecreated, $fmt),
            $r->payername,
            $r->payeremail,
            api::products($r),
            $money($r->amount, $r->currency),
            $r->refunded > 0 ? $money($r->refunded, $r->currency) : '',
            $statusname($r->status),
            $r->gatewaytitle !== '' ? $r->gatewaytitle : $r->gateway,
            $r->txnid,
        ]);
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('registry', $p));
echo html_writer::tag('p', get_string('registry_help', $p), ['class' => 'text-muted']);

// Итоги — те же числа печатает скрипт выгрузки, по ним реестр сверяется с WordPress.
$totals = api::totals();
if (!$totals['currencies']) {
    echo $OUTPUT->notification(get_string('empty', $p), 'info');
    if (has_capability('local/kabwporders:import', context_system::instance())) {
        echo html_writer::link(new moodle_url('/local/kabwporders/import.php'), get_string('import', $p),
            ['class' => 'btn btn-primary']);
    }
    echo $OUTPUT->footer();
    exit;
}
$t = new html_table();
$t->head = [get_string('col_currency', $p), get_string('tot_orders', $p), get_string('tot_amount', $p),
    get_string('tot_refunds', $p), get_string('tot_refunded', $p)];
$t->attributes['class'] = 'generaltable w-auto';
foreach ($totals['currencies'] as $c) {
    $t->data[] = [s($c->currency), (int) $c->orders, format_float((float) $c->amount, 2), (int) $c->refunds,
        format_float((float) $c->refunded, 2)];
}
echo html_writer::table($t);
echo html_writer::tag('p', get_string('tot_people', $p, (object) [
    'linked'   => $totals['linked'],
    'unlinked' => $totals['unlinked'],
    'date'     => $totals['lastimport'] ? userdate($totals['lastimport'], $fmt) : '—',
]), ['class' => 'small text-muted']);

// Фильтры.
$years = [0 => get_string('allyears', $p)];
for ($y = (int) date('Y'); $y >= 2018; $y--) {
    $years[$y] = $y;
}
$currencies = ['' => get_string('allcurrencies', $p)];
foreach ($totals['currencies'] as $c) {
    $currencies[$c->currency] = $c->currency;
}
$statuses = ['' => get_string('allstatuses', $p)];
foreach (['completed', 'processing', 'refunded', 'cancelled', 'failed', 'pending', 'on-hold'] as $s) {
    $statuses[$s] = $statusname($s);
}
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $baseurl->out_omit_querystring(),
    'class' => 'd-flex flex-wrap gap-2 align-items-end mb-3']);
echo html_writer::div(html_writer::label(get_string('search'), 'kq', true, ['class' => 'd-block small']) .
    html_writer::empty_tag('input', ['type' => 'text', 'name' => 'q', 'id' => 'kq', 'value' => $q,
        'class' => 'form-control', 'size' => 34, 'placeholder' => get_string('searchhint', $p)]));
echo html_writer::div(html_writer::label(get_string('col_year', $p), 'ky', true, ['class' => 'd-block small']) .
    html_writer::select($years, 'year', $year, false, ['id' => 'ky', 'class' => 'form-select']));
echo html_writer::div(html_writer::label(get_string('col_currency', $p), 'kc', true, ['class' => 'd-block small']) .
    html_writer::select($currencies, 'currency', $currency, false, ['id' => 'kc', 'class' => 'form-select']));
echo html_writer::div(html_writer::label(get_string('col_status', $p), 'ks', true, ['class' => 'd-block small']) .
    html_writer::select($statuses, 'status', $status, false, ['id' => 'ks', 'class' => 'form-select']));
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('filter'), 'class' => 'btn btn-primary']);
echo html_writer::link(new moodle_url($baseurl, ['download' => 'csv']), get_string('exportcsv', $p),
    ['class' => 'btn btn-secondary']);
echo html_writer::end_tag('form');

[$rows, $total] = api::search($filters, $page);
if (!$rows) {
    echo $OUTPUT->notification(get_string('notfound', $p), 'info');
    echo $OUTPUT->footer();
    exit;
}

echo html_writer::tag('p', get_string('found', $p, $total), ['class' => 'small text-muted']);
$t = new html_table();
$t->head = array_map(fn($c) => get_string('col_' . $c, $p), $columns);
$t->attributes['class'] = 'generaltable';
foreach ($rows as $r) {
    $payer = s($r->payername);
    if ($r->userid > 0) {
        $payer = html_writer::link(new moodle_url('/user/profile.php', ['id' => $r->userid]),
            $payer !== '' ? $payer : '#' . $r->userid);
    }
    $t->data[] = [
        $r->wporderid,
        userdate($r->timepaid ?: $r->timecreated, $fmt),
        $payer,
        s($r->payeremail),
        s(api::products($r)),
        $money($r->amount, s($r->currency)),
        $r->refunded > 0 ? $money($r->refunded, s($r->currency)) : '',
        s($statusname($r->status)),
        s($r->gatewaytitle !== '' ? $r->gatewaytitle : $r->gateway),
        s($r->txnid),
    ];
}
echo html_writer::table($t);
echo $OUTPUT->paging_bar($total, $page, api::PERPAGE, $baseurl);
echo $OUTPUT->footer();
