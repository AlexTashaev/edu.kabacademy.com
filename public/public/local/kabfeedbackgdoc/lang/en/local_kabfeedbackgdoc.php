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
 * English strings.
 *
 * @package    local_kabfeedbackgdoc
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Feedback → Google Sheet (KAB)';
$string['courseids'] = 'Courses (ids)';
$string['courseids_desc'] = 'Comma-separated course ids whose feedback activities are all forwarded. Empty = any course. Combine with the name pattern so new weekly forms are picked up automatically.';
$string['namepattern'] = 'Activity name pattern';
$string['namepattern_desc'] = 'Case-insensitive regular expression the activity name must match, e.g. <code>вопрос</code>. Empty = any name.';
$string['enabled'] = 'Enabled';
$string['enabled_desc'] = 'Forward new feedback responses to the Google Apps Script web app.';
$string['webhookurl'] = 'Apps Script web app URL';
$string['webhookurl_desc'] = 'Deployment URL of the Apps Script that writes to the Google Sheet (https://script.google.com/macros/s/.../exec). Empty = nothing is sent.';
$string['secret'] = 'Shared secret';
$string['secret_desc'] = 'Must match the SECRET constant in the Apps Script. Empty = no check.';
$string['cmids'] = 'Feedback activities (cmids)';
$string['cmids_desc'] = 'Comma-separated course module ids of the feedback activities to forward (the "id" in the activity URL). Empty = no restriction by activity. All three filters (courses, name pattern, cmids) are applied together.';
$string['defaulttarget'] = 'Questions table (default)';
$string['defaulttarget_desc'] = 'Link to the Google Sheet (or its id) that receives the forms matching the three filters above, in the "ask the teacher" layout. Empty = the table set in the script itself (SPREADSHEET_ID). The table title must contain <code>(Moodle)</code> and the account the script is deployed as must be able to edit it.';
$string['routes'] = 'Forms with a table of their own';
$string['routes_desc'] = 'One form per line: <code>cmid = link to the Google Sheet</code>, e.g. <code>12951 = https://docs.google.com/spreadsheets/d/…/edit</code>. Optionally a tab name after <code>|</code>. Such a form is forwarded whatever the filters say, one column per form item; the script creates the columns. The table title must contain <code>(Moodle)</code>. Headers may be renamed, columns moved and added: the script finds a column by the note on its header.';
$string['statuslink'] = 'Which form goes where, the delivery queue and "Send all responses again" are on the <a href="{$a}">status page</a>.';
$string['statuspage'] = 'Feedback → Google Sheet: what goes where';
$string['statusintro'] = 'Feedback activities of the courses where forwarding is set up. Greyed out: activities that do not reach any table.';
$string['status_disabled'] = 'Forwarding is switched off in the settings: new responses do not reach the tables.';
$string['status_nowebhook'] = 'The Apps Script web app URL is not set: new responses do not reach the tables.';
$string['col_form'] = 'Form';
$string['col_target'] = 'Where responses go';
$string['col_responses'] = 'Responses';
$string['col_last'] = 'Last response';
$string['col_result'] = 'What the script answered';
$string['target_default'] = 'Questions table';
$string['target_builtin'] = '(set in the script)';
$string['target_own'] = 'Own table';
$string['target_none'] = 'not forwarded';
$string['resend'] = 'Send all responses again';
$string['resendconfirm'] = 'Send all responses of "{$a}" to the table? Rows the table already has will not be duplicated.';
$string['resendqueued'] = '"{$a->name}" is queued, responses: {$a->count}. The table will fill up within a few minutes.';
$string['ping'] = 'Check the connection to the script';
$string['pingok'] = 'The script answers, version {$a}.';
$string['pingfail'] = 'The script does not answer as expected: {$a}';
$string['queue'] = 'Delivery queue';
$string['queuestate'] = 'Tasks in the queue: {$a->pending}, failing (waiting for a retry): {$a->failing}.';
$string['recent'] = 'Recent deliveries';
$string['recentnote'] = 'A failed delivery is not a lost response: the task retries by itself, and the script never writes a row the table already has. "try 2" or "try 3" means Google did not answer the first time, which happens. The one sign of trouble is failing tasks that stay in the queue for more than an hour.';
$string['fail'] = 'Failed';
$string['taskname'] = 'Send feedback response to Google Sheet';
$string['taskresend'] = 'Send all responses of a form to Google Sheet again';
$string['taskarchive'] = 'Move the responses of closed forms to the "Архив" tab';
$string['closes'] = 'closes {$a}';
$string['closedwaiting'] = 'closed {$a}, the responses move to "Архив" within the hour';
$string['archived'] = 'closed {$a}, the responses are in "Архив"';
$string['privacy:metadata:webhook'] = 'Feedback responses (course, activity, respondent name and e-mail, answers) are sent to the configured Google Apps Script web app so that staff can read them in a Google Sheet.';
$string['privacy:metadata:webhook:answers'] = 'The answers given in the feedback activity.';
$string['privacy:metadata:webhook:email'] = 'The e-mail address of the respondent.';
$string['privacy:metadata:webhook:fullname'] = 'The full name of the respondent.';
