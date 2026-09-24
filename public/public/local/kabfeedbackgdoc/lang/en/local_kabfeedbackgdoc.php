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
$string['taskname'] = 'Send feedback response to Google Doc';
$string['privacy:metadata:webhook'] = 'Feedback responses (course, activity, respondent name and e-mail, answers) are sent to the configured Google Apps Script web app so that teachers can read them in a Google Doc.';
$string['privacy:metadata:webhook:answers'] = 'The answers given in the feedback activity.';
$string['privacy:metadata:webhook:email'] = 'The e-mail address of the respondent.';
$string['privacy:metadata:webhook:fullname'] = 'The full name of the respondent.';
