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
 * Signup log for marketing: counts by campaign and status, CSV with the ad
 * parameters and quiz answers. No e-mail, phone or IP in the export.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');

use local_kabsignup\attribution;

admin_externalpage_setup('local_kabsignup_requests');
require_capability('local/kabsignup:viewreports', context_system::instance());
$p = 'local_kabsignup';

$tz = core_date::get_user_timezone_object();
$parsedate = function(string $value, string $default) use ($tz): int {
    $date = DateTime::createFromFormat('!Y-m-d', $value, $tz) ?: DateTime::createFromFormat('!Y-m-d', $default, $tz);
    return $date->getTimestamp();
};
$fmt = function(int $time) use ($tz): string {
    return (new DateTime('@' . $time))->setTimezone($tz)->format('Y-m-d');
};
$today = (new DateTime('now', $tz))->format('Y-m-d');
$monthago = (new DateTime('-30 days', $tz))->format('Y-m-d');
$fromstr = optional_param('from', $monthago, PARAM_RAW_TRIMMED);
$tostr = optional_param('to', $today, PARAM_RAW_TRIMMED);
$from = $parsedate($fromstr, $monthago);
$to = $parsedate($tostr, $today) + DAYSECS;
$camp = core_text::strtolower(optional_param('camp', '', PARAM_ALPHANUMEXT));
$download = optional_param('download', 0, PARAM_BOOL);

$where = 'timecreated >= :from AND timecreated < :to';
$params = ['from' => $from, 'to' => $to];
if ($camp !== '') {
    $where .= ' AND campaign = :camp';
    $params['camp'] = $camp;
}

if ($download) {
    $csv = new csv_export_writer();
    $csv->set_filename('kabsignup_' . $fmt($from) . '_' . $fmt($to - 1) . ($camp !== '' ? '_' . $camp : ''));
    $keys = array_values(array_diff(attribution::KEYS, ['quiz_score', 'qa', 'pres']));
    $csv->add_data(array_merge(['id', 'time', 'courseid', 'campaign', 'status', 'newuser', 'userid', 'persona',
        'quiz_score', 'qa'], $keys, ['flags']));
    $rs = $DB->get_recordset_select('local_kabsignup_log', $where, $params, 'id ASC',
        'id, timecreated, courseid, campaign, status, newuser, userid, persona, quizscore, attribution, flags');
    foreach ($rs as $r) {
        $attr = json_decode((string)$r->attribution, true) ?: [];
        $row = [$r->id, userdate($r->timecreated, '%Y-%m-%d %H:%M:%S'), $r->courseid, $r->campaign, $r->status,
            $r->newuser, $r->userid, $r->persona, $r->quizscore, $attr['qa'] ?? ''];
        foreach ($keys as $key) {
            $row[] = $attr[$key] ?? '';
        }
        $row[] = $r->flags;
        $csv->add_data($row);
    }
    $rs->close();
    $csv->download_file();
    exit;
}

$pageurl = new moodle_url('/local/kabsignup/requests.php', ['from' => $fromstr, 'to' => $tostr, 'camp' => $camp]);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('requests', $p));
echo html_writer::tag('p', get_string('requests_intro', $p));

// Filter.
$campaigns = $DB->get_fieldset_sql("SELECT DISTINCT campaign FROM {local_kabsignup_log} WHERE campaign <> '' ORDER BY campaign");
$options = html_writer::tag('option', get_string('requests_allcampaigns', $p), ['value' => '']);
foreach ($campaigns as $c) {
    $options .= html_writer::tag('option', s($c), ['value' => $c] + ($c === $camp ? ['selected' => 'selected'] : []));
}
$filter = html_writer::label(get_string('requests_from', $p), 'ks-from', true, ['class' => 'me-1'])
    . html_writer::empty_tag('input', ['type' => 'date', 'name' => 'from', 'id' => 'ks-from',
        'value' => $fmt($from), 'class' => 'form-control d-inline-block w-auto me-3'])
    . html_writer::label(get_string('requests_to', $p), 'ks-to', true, ['class' => 'me-1'])
    . html_writer::empty_tag('input', ['type' => 'date', 'name' => 'to', 'id' => 'ks-to',
        'value' => $fmt($to - 1), 'class' => 'form-control d-inline-block w-auto me-3'])
    . html_writer::label(get_string('requests_campaign', $p), 'ks-camp', true, ['class' => 'me-1'])
    . html_writer::tag('select', $options, ['name' => 'camp', 'id' => 'ks-camp',
        'class' => 'form-select d-inline-block w-auto me-3'])
    . html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('requests_show', $p),
        'class' => 'btn btn-secondary']);
echo html_writer::tag('form', $filter, ['method' => 'get', 'action' => $pageurl->out_omit_querystring(),
    'class' => 'mb-3']);

// Counts by campaign and status.
$sql = "SELECT campaign, status, COUNT(1) AS n, SUM(newuser) AS newusers
          FROM {local_kabsignup_log}
         WHERE $where
      GROUP BY campaign, status
      ORDER BY campaign, status";
$table = new html_table();
$table->head = [get_string('requests_campaign', $p), get_string('requests_status', $p),
    get_string('requests_count', $p), get_string('requests_newusers', $p)];
$table->attributes['class'] = 'generaltable ks-requests';
$rs = $DB->get_recordset_sql($sql, $params);
foreach ($rs as $r) {
    $table->data[] = [$r->campaign !== '' ? s($r->campaign) : '—', s($r->status), (int)$r->n, (int)$r->newusers];
}
$rs->close();
if ($table->data) {
    echo html_writer::table($table);
} else {
    echo $OUTPUT->notification(get_string('requests_none', $p), \core\output\notification::NOTIFY_INFO, false);
}

echo $OUTPUT->single_button(new moodle_url($pageurl, ['download' => 1]), get_string('requests_download', $p), 'get');
echo html_writer::tag('p', get_string('requests_statuses', $p), ['class' => 'text-muted mt-3']);
echo $OUTPUT->footer();
