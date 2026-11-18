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
 * Купоны витрины: список и правка.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\coupons;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$id = optional_param('id', -1, PARAM_INT);

admin_externalpage_setup('local_kabshop_coupons');
require_capability('local/kabshop:manage', context_system::instance());
$p = 'local_kabshop';
$listurl = new moodle_url('/local/kabshop/coupons.php');

// Форма: id = 0 — новый купон, id > 0 — правка.
if ($id >= 0) {
    $PAGE->set_url(new moodle_url($listurl, ['id' => $id]));
    $emails = $id ? coupons::emails($id) : [];
    $sentcount = count(array_filter($emails, fn($r) => $r->timesent > 0));
    $form = new \local_kabshop\form\coupon_form($PAGE->url, ['emailstatus' => $emails
        ? get_string('couponemails_status', $p, (object) ['sent' => $sentcount, 'total' => count($emails)]) : '']);
    if ($id) {
        $c = $DB->get_record('local_kabshop_coupon', ['id' => $id], '*', MUST_EXIST);
        $c->productids = array_filter(array_map('intval', explode(',', (string) $c->productids)));
        $c->emails = implode("\n", array_keys($emails));
        $c->emailonly = $emails ? $c->emailonly : 1;
        $form->set_data($c);
    } else {
        $form->set_data(['id' => 0]);
    }
    if ($form->is_cancelled()) {
        redirect($listurl);
    } else if ($data = $form->get_data()) {
        $rec = (object) [
            'code' => coupons::normalize($data->code),
            'kind' => $data->kind,
            'value' => round((float) $data->value, 2),
            'currency' => $data->kind === 'fixed' ? $data->currency : null,
            'productids' => implode(',', array_map('intval', (array) ($data->productids ?? []))),
            'validfrom' => (int) ($data->validfrom ?? 0),
            'validto' => (int) ($data->validto ?? 0),
            'maxuses' => max(0, (int) $data->maxuses),
            'peruser' => (int) $data->peruser,
            'note' => trim((string) $data->note),
            'enabled' => (int) $data->enabled,
            'timemodified' => time(),
        ];
        [$list] = coupons::parse_emails((string) ($data->emails ?? ''));
        $rec->emailonly = $list ? (int) $data->emailonly : 0;
        if ($data->id) {
            $rec->id = $data->id;
            $DB->update_record('local_kabshop_coupon', $rec);
        } else {
            $rec->uses = 0;
            $rec->timecreated = time();
            $rec->id = $DB->insert_record('local_kabshop_coupon', $rec);
        }
        $pending = coupons::save_emails($rec->id, $list);
        $msg = get_string('changessaved');
        if ($pending && !empty($data->sendmail)) {
            coupons::queue_mail($rec->id);
            $msg .= ' ' . get_string('couponemails_queued', $p, $pending);
        }
        redirect($listurl, $msg, null, \core\output\notification::NOTIFY_SUCCESS);
    }
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string($id ? 'editcoupon' : 'addcoupon', $p));
    $form->display();
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
echo html_writer::div(html_writer::link(new moodle_url($listurl, ['id' => 0]), get_string('addcoupon', $p),
    ['class' => 'btn btn-primary']), 'mb-3');

$list = $DB->get_records('local_kabshop_coupon', null, 'timecreated DESC');
if (!$list) {
    echo $OUTPUT->notification(get_string('nocoupons', $p), 'info');
} else {
    $products = $DB->get_records_menu('local_kabshop_product', null, '', 'id, name');
    $emailstats = $DB->get_records_sql('SELECT couponid, COUNT(1) AS total, SUM(CASE WHEN timesent > 0 THEN 1 ELSE 0 END) AS sent
        FROM {local_kabshop_coupon_email} GROUP BY couponid');
    $fmt = get_string('strftimedatetimeshort', 'langconfig');
    $t = new html_table();
    $t->head = [get_string('couponcode', $p), get_string('couponkind', $p), get_string('couponproducts', $p),
        get_string('couponvalidity', $p), get_string('couponemails_col', $p), get_string('couponuses', $p),
        get_string('couponnote', $p), get_string('enabled', 'admin')];
    $t->attributes['class'] = 'generaltable';
    foreach ($list as $c) {
        $ids = array_filter(array_map('intval', explode(',', (string) $c->productids)));
        $names = $ids ? implode(', ', array_map(fn($i) => format_string($products[$i] ?? ('#' . $i)), $ids))
            : get_string('allproducts', $p);
        $valid = (!$c->validfrom && !$c->validto) ? get_string('couponnolimit', $p)
            : ($c->validfrom ? userdate($c->validfrom, $fmt) : '…') . ' — ' . ($c->validto ? userdate($c->validto, $fmt) : '…');
        $es = $emailstats[$c->id] ?? null;
        $emailcell = $es ? get_string('couponemails_short', $p, (object) ['total' => $es->total, 'sent' => (int) $es->sent])
            . ($c->emailonly ? ' · ' . get_string('couponemailonly_short', $p) : '') : '—';
        $uses = $c->uses . ($c->maxuses ? ' / ' . $c->maxuses : '');
        $t->data[] = [
            html_writer::link(new moodle_url($listurl, ['id' => $c->id]), s($c->code)),
            coupons::label($c) . ($c->peruser ? ' · ' . get_string('couponperuser_short', $p) : ''),
            $names,
            $valid,
            $emailcell,
            $c->uses ? html_writer::link(new moodle_url('/local/kabshop/orders.php', ['couponid' => $c->id]), $uses,
                ['title' => get_string('couponuses_link', $p)]) : $uses,
            s((string) $c->note),
            $c->enabled ? get_string('yes') : get_string('no'),
        ];
    }
    echo html_writer::table($t);
}
echo $OUTPUT->footer();
