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
 * Status page: which feedback activity goes to which table, the delivery queue,
 * and "send all responses again" per form.
 *
 * @package    local_kabfeedbackgdoc
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabfeedbackgdoc\sender;
use local_kabfeedbackgdoc\task\resend_form;

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_kabfeedbackgdoc_status');

$component = 'local_kabfeedbackgdoc';
$config = get_config($component);
$pageurl = new moodle_url('/local/kabfeedbackgdoc/index.php');
$action = optional_param('action', '', PARAM_ALPHA);

if ($action === 'resend') {
    require_sesskey();
    $cmid = required_param('cmid', PARAM_INT);
    $cm = get_coursemodule_from_id('feedback', $cmid, 0, false, MUST_EXIST);
    if (sender::target_for_cm((int)$cm->id, (int)$cm->course, (string)$cm->name, $config) === null) {
        redirect($pageurl, get_string('target_none', $component), null, \core\output\notification::NOTIFY_ERROR);
    }
    $task = new resend_form();
    $task->set_custom_data(['cmid' => (int)$cm->id]);
    \core\task\manager::queue_adhoc_task($task, true);
    redirect($pageurl, get_string('resendqueued', $component, (object)[
        'name'  => format_string($cm->name),
        'count' => count(sender::completed_ids((int)$cm->instance)),
    ]), null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($action === 'ping') {
    require_sesskey();
    $result = sender::ping();
    if ($result['ok']) {
        redirect($pageurl, get_string('pingok', $component, s((string)($result['json']['version'] ?? '?'))),
            null, \core\output\notification::NOTIFY_SUCCESS);
    }
    redirect($pageurl, get_string('pingfail', $component, s("HTTP {$result['code']} {$result['body']}")),
        null, \core\output\notification::NOTIFY_ERROR);
}

$forms = sender::forwarded_forms($config);
$archived = sender::archived_cmids($config);
$dateformat = get_string('strftimedatetimeshort', 'langconfig');

// Forms that are forwarded, and their neighbours in the same courses: a form that
// is NOT forwarded is what one usually comes here to find.
$courses = [];
foreach ($forms as $form) {
    if ($form->target !== null) {
        $courses[$form->courseid] = true;
    }
}
foreach (preg_split('/[\s,;]+/', (string)($config->courseids ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $courseid) {
    $courses[(int)$courseid] = true;
}

$table = new html_table();
$table->attributes['class'] = 'generaltable';
$table->head = [
    get_string('course'),
    get_string('col_form', $component),
    'cmid',
    get_string('col_target', $component),
    get_string('col_responses', $component),
    get_string('col_last', $component),
    '',
];
foreach ($forms as $form) {
    if (empty($courses[$form->courseid])) {
        continue;
    }
    $target = $form->target;
    if ($target === null) {
        $where = html_writer::span(get_string('target_none', $component), 'text-muted');
        $button = '';
    } else {
        if ($target['layout'] === sender::LAYOUT_GENERIC) {
            $label = get_string('target_own', $component);
        } else {
            $label = get_string('target_default', $component);
        }
        if ($target['spreadsheet'] !== '') {
            $where = html_writer::link(sender::spreadsheet_url($target['spreadsheet']), $label, ['target' => '_blank']);
        } else {
            $where = $label . ' ' . html_writer::span(get_string('target_builtin', $component), 'text-muted');
        }
        if ($target['sheet'] !== '') {
            $where .= ' · ' . s($target['sheet']);
        }
        if ($target['layout'] === sender::LAYOUT_QUESTIONS && ($closed = sender::closed_at($form))) {
            // After the form stops taking answers, and after the archive hour of that day, its rows
            // leave the time tabs for the archive (hourly task).
            if (time() < $closed) {
                $where .= ' · ' . get_string('closes', $component, userdate($closed, $dateformat));
            } else if (in_array((int)$form->cmid, $archived, true)) {
                $where .= ' · ' . get_string('archived', $component, userdate($closed, $dateformat));
            } else {
                $where .= ' · ' . get_string('closedwaiting', $component, (object)[
                    'closed'  => userdate($closed, $dateformat),
                    'archive' => userdate(sender::archive_at($closed, $config), $dateformat),
                ]);
            }
        }
        $resend = new single_button(
            new moodle_url($pageurl, ['action' => 'resend', 'cmid' => $form->cmid]),
            get_string('resend', $component), 'post');
        $resend->add_confirm_action(get_string('resendconfirm', $component, format_string($form->name)));
        $button = $form->responses ? $OUTPUT->render($resend) : '';
    }
    $row = new html_table_row([
        html_writer::link(new moodle_url('/course/view.php', ['id' => $form->courseid]),
            $form->courseid . ' · ' . format_string($form->shortname)),
        html_writer::link(new moodle_url('/mod/feedback/view.php', ['id' => $form->cmid]), format_string($form->name)),
        $form->cmid,
        $where,
        $form->responses,
        $form->lastresponse ? userdate($form->lastresponse, $dateformat) : '',
        $button,
    ]);
    if ($target === null) {
        $row->attributes['class'] = 'dimmed_text';
    }
    $table->data[] = $row;
}

$pending = $DB->count_records('task_adhoc', ['component' => $component]);
$failing = $DB->count_records_select('task_adhoc', 'component = ? AND faildelay > 0', [$component]);
$logs = $DB->get_records('task_log', ['component' => $component], 'id DESC', 'id, timestart, result, output', 0, 15);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('statuspage', $component));

if (empty($config->enabled)) {
    echo $OUTPUT->notification(get_string('status_disabled', $component), \core\output\notification::NOTIFY_WARNING);
} else if (empty($config->webhookurl)) {
    echo $OUTPUT->notification(get_string('status_nowebhook', $component), \core\output\notification::NOTIFY_WARNING);
}

echo html_writer::tag('p', get_string('statusintro', $component));
echo html_writer::table($table);

echo $OUTPUT->single_button(new moodle_url($pageurl, ['action' => 'ping']), get_string('ping', $component), 'post');
echo $OUTPUT->single_button(new moodle_url('/admin/settings.php', ['section' => $component]),
    get_string('settings'), 'get');

echo $OUTPUT->heading(get_string('queue', $component), 3);
echo $OUTPUT->notification(
    get_string('queuestate', $component, (object)['pending' => $pending, 'failing' => $failing]),
    $failing ? \core\output\notification::NOTIFY_ERROR : \core\output\notification::NOTIFY_INFO,
    false
);

if ($logs) {
    echo $OUTPUT->heading(get_string('recent', $component), 3);
    $recent = new html_table();
    $recent->attributes['class'] = 'generaltable';
    $recent->head = [get_string('time'), get_string('status'), get_string('col_result', $component)];
    foreach ($logs as $log) {
        // Of the whole cron output of the task, keep what the plugin itself said.
        preg_match_all('/^local_kabfeedbackgdoc: .*$/mu', (string)$log->output, $m);
        $recent->data[] = [
            userdate((int)$log->timestart, get_string('strftimedatetimeshort', 'langconfig')),
            $log->result ? get_string('fail', $component) : get_string('ok'),
            html_writer::tag('small', nl2br(s(implode("\n", $m[0])))),
        ];
    }
    echo html_writer::table($recent);
    echo html_writer::tag('p', get_string('recentnote', $component), ['class' => 'text-muted']);
}

echo $OUTPUT->footer();
