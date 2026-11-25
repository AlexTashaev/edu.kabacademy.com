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
 * Funnel statistics: clicks by day and the per-course cut ("wp kab-te-stats" of WordPress).
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_kabtelegram\stats;

$days = optional_param('days', 14, PARAM_INT);
$days = max(1, min(400, $days));
$courseid = optional_param('courseid', 0, PARAM_INT);
$since = optional_param('since', '', PARAM_RAW_TRIMMED);

admin_externalpage_setup('local_kabtelegram_stats', '', ['days' => $days, 'courseid' => $courseid, 'since' => $since]);
require_capability('local/kabtelegram:viewstats', context_system::instance());

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('statsheading', 'local_kabtelegram'));

// Filter form.
$sinceinput = preg_match('/^\d{4}-\d{2}-\d{2}$/', $since) ? $since : '';
echo html_writer::start_tag('form', ['method' => 'get', 'action' => (new moodle_url('/local/kabtelegram/stats.php'))->out(false),
    'class' => 'row g-2 align-items-end mb-4']);
echo html_writer::div(html_writer::label(get_string('statsdays', 'local_kabtelegram'), 'kabtg-days', true, ['class' => 'form-label'])
    . html_writer::empty_tag('input', ['type' => 'number', 'name' => 'days', 'id' => 'kabtg-days', 'value' => $days,
        'min' => 1, 'max' => 400, 'class' => 'form-control']), 'col-auto');
echo html_writer::div(html_writer::label(get_string('statscourse', 'local_kabtelegram'), 'kabtg-course', true, ['class' => 'form-label'])
    . html_writer::empty_tag('input', ['type' => 'number', 'name' => 'courseid', 'id' => 'kabtg-course',
        'value' => $courseid ?: '', 'min' => 0, 'class' => 'form-control']), 'col-auto');
echo html_writer::div(html_writer::label(get_string('statssince', 'local_kabtelegram'), 'kabtg-since', true, ['class' => 'form-label'])
    . html_writer::empty_tag('input', ['type' => 'date', 'name' => 'since', 'id' => 'kabtg-since', 'value' => $sinceinput,
        'class' => 'form-control']), 'col-auto');
echo html_writer::div(html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('statsshow', 'local_kabtelegram'),
    'class' => 'btn btn-primary']), 'col-auto');
echo html_writer::end_tag('form');

// Per-course cut.
if ($courseid > 0 && $sinceinput !== '') {
    $tz = core_date::get_server_timezone_object();
    $sincets = (new DateTime($sinceinput . ' 00:00:00', $tz))->getTimestamp();
    $course = $DB->get_record('course', ['id' => $courseid], 'id, fullname');
    echo $OUTPUT->heading(get_string('statscohort', 'local_kabtelegram', (object)[
        'course' => $course ? format_string($course->fullname) : '#' . $courseid,
        'since' => $sinceinput,
    ]), 3);
    $cohort = stats::cohort($courseid, $sincets);
    $table = new html_table();
    $table->head = array_map(function($k) {
        return get_string('col_' . $k, 'local_kabtelegram');
    }, array_keys($cohort));
    $table->data = [array_values($cohort)];
    echo html_writer::table($table);
}

// By day.
[$rows, $columns] = stats::daily($days);
echo $OUTPUT->heading(get_string('statsdaily', 'local_kabtelegram', $days), 3);
if (!$columns) {
    echo $OUTPUT->notification(get_string('statsnone', 'local_kabtelegram'), 'info');
} else {
    $table = new html_table();
    $table->head = array_merge([get_string('statsday', 'local_kabtelegram')], $columns);
    foreach (array_reverse($rows, true) as $day => $row) {
        $line = [$day];
        foreach ($columns as $col) {
            $line[] = isset($row[$col]) ? (int)$row[$col] : '';
        }
        $table->data[] = $line;
    }
    echo html_writer::table($table);
}
echo html_writer::tag('p', get_string('statsnote', 'local_kabtelegram'), ['class' => 'text-muted small']);

echo $OUTPUT->footer();
