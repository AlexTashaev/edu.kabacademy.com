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
 * «Мои заказы» покупателя.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\orders;

require_once(__DIR__ . '/../../config.php');

require_login(null, false);
if (isguestuser()) {
    redirect(get_login_url());
}
$page = optional_param('page', 0, PARAM_INT);
$url = new moodle_url('/local/kabshop/myorders.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('myorders', 'local_kabshop'));
$PAGE->set_heading(get_string('myorders', 'local_kabshop'));

$p = 'local_kabshop';
echo $OUTPUT->header();
echo html_writer::div(html_writer::link(new moodle_url('/local/kabshop/subscriptions.php'),
    get_string('mysubscriptions', $p)), 'mb-3');

[$rows, $total] = orders::search(['userid' => $USER->id], $page);
if (!$rows) {
    echo $OUTPUT->notification(get_string('noorders', $p), 'info');
} else {
    $fmt = get_string('strftimedate', 'langconfig');
    $t = new html_table();
    $t->head = [get_string('col_order', $p), get_string('col_orderdate', $p), get_string('col_subproduct', $p),
        get_string('col_subamount', $p), get_string('col_paymethod', $p), get_string('col_txn', $p)];
    $t->attributes['class'] = 'generaltable';
    foreach ($rows as $r) {
        $name = $r->productname !== null
            ? html_writer::link(new moodle_url('/local/kabshop/product.php', ['id' => $r->productid]), format_string($r->productname))
            : '#' . $r->productid;
        $t->data[] = [$r->id, userdate($r->timecreated, $fmt), $name,
            empty($r->paymentid) ? orders::type($r)
                : \core_payment\helper::get_cost_as_string((float) $r->amount, $r->currency) .
                    ($r->paymentarea === 'renewal' ? ' · ' . orders::type($r) : ''),
            orders::method($r), s((string) ($r->txnid ?? ''))];
    }
    echo html_writer::table($t);
    echo $OUTPUT->paging_bar($total, $page, orders::PERPAGE, $url);
}
echo html_writer::tag('p', get_string('mail_receipt', $p), ['class' => 'text-muted']);
echo $OUTPUT->footer();
