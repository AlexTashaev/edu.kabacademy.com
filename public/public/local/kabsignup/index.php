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
 * Public course signup page.
 *
 * ?id=<courseid>&camp=<campaign slug>&utm_*=... — the course must be listed in
 * the plugin's "courses open for signup" setting. A guest fills in a name and
 * an e-mail: a free address gets a new account (and is logged in right away),
 * a taken one is enrolled as the existing account (or asked to log in,
 * depending on the setting). A logged-in visitor is enrolled without the form.
 * Everybody ends up on the thank-you page of the campaign/course.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$courseid = required_param('id', PARAM_INT);
$campaign = core_text::strtolower(core_text::substr(optional_param('camp', '', PARAM_ALPHANUMEXT), 0, 64));
$utm = \local_kabsignup\manager::utm_params();

$config = \local_kabsignup\manager::config();

$pageparams = ['id' => $courseid];
if ($campaign !== '') {
    $pageparams['camp'] = $campaign;
}
$pageparams += $utm;
$pageurl = new moodle_url('/local/kabsignup/index.php', $pageparams);

$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('login');
$PAGE->set_cacheable(false);

$course = null;
if (isset($config->courses[$courseid])) {
    $course = $DB->get_record('course', ['id' => $courseid]);
}
if (!$course) {
    // Not whitelisted (or gone): a neutral answer that does not leak course names.
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title(get_string('signuptitle', 'local_kabsignup'));
    $PAGE->set_heading(format_string($SITE->fullname));
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('notavailable', 'local_kabsignup'), \core\output\notification::NOTIFY_INFO);
    echo $OUTPUT->footer();
    exit;
}

// set_course() also picks up the course's forced theme (kabacademy on 236/238).
$PAGE->set_course($course);
$coursecontext = context_course::instance($course->id);
$coursename = format_string($course->fullname, true, ['context' => $coursecontext]);
$PAGE->set_title(get_string('signuptitle', 'local_kabsignup'));
$PAGE->set_heading($coursename);

if (isloggedin() && !isguestuser()) {
    if (is_siteadmin() || has_capability('moodle/course:update', $coursecontext)) {
        // Admins and the course's teachers preview the flow without enrolling themselves.
        redirect(\local_kabsignup\manager::thankyou_url($course, $campaign, $utm));
    }
    $status = \local_kabsignup\manager::enrol($USER, $course);
    \local_kabsignup\manager::log($course->id, (int)$USER->id,
        \local_kabsignup\manager::email_hash((string)$USER->email),
        0, $campaign, $status === 'already' ? 'already' : 'loggedin', (string)getremoteaddr());
    redirect(\local_kabsignup\manager::thankyou_url($course, $campaign, $utm));
}

// Honeypot: bots fill the invisible field; people never see it.
if (data_submitted() && optional_param('kab_website', '', PARAM_RAW) !== '') {
    redirect(\local_kabsignup\manager::thankyou_url($course, $campaign, $utm));
}

$form = new \local_kabsignup\form\signup_form($pageurl);

if ($data = $form->get_data()) {
    $result = \local_kabsignup\manager::signup($course, $data->firstname, (string)($data->lastname ?? ''),
        $data->email, $campaign, (string)getremoteaddr());

    if ($result['status'] === 'throttled') {
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('throttled', 'local_kabsignup'), \core\output\notification::NOTIFY_WARNING);
        if (is_enabled_auth('kabmagic')) {
            $requesturl = new moodle_url('/auth/kabmagic/request.php',
                ['wantsurl' => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false)]);
            echo html_writer::div(
                $OUTPUT->single_button($requesturl, get_string('magicbutton', 'local_kabsignup'), 'get'),
                'text-center'
            );
        }
        echo $OUTPUT->footer();
        exit;
    }

    if ($result['status'] === 'exists') {
        // "Ask to log in" mode: after the login Moodle returns here and the
        // logged-in branch above finishes the enrolment.
        $SESSION->wantsurl = $pageurl->out(false);
        echo $OUTPUT->header();
        echo $OUTPUT->box(get_string('existstext', 'local_kabsignup'), 'generalbox boxwidthnormal boxaligncenter');
        $buttons = $OUTPUT->single_button(new moodle_url('/login/index.php'),
            get_string('loginbutton', 'local_kabsignup'), 'get');
        if (is_enabled_auth('kabmagic')) {
            $buttons .= ' ' . $OUTPUT->single_button(
                new moodle_url('/auth/kabmagic/request.php', ['wantsurl' => $pageurl->out(false)]),
                get_string('magicbutton', 'local_kabsignup'), 'get');
        }
        echo html_writer::div($buttons, 'text-center');
        echo $OUTPUT->footer();
        exit;
    }

    if ($result['status'] === 'created') {
        // A brand-new account may be entered right away: there is nothing in it
        // to take over, and the login link still reaches the mailbox. Existing
        // accounts are never signed in here — address ownership is unproven.
        $newuser = get_complete_user_data('id', $result['user']->id, null, true);
        complete_user_login($newuser);
        \core\session\manager::apply_concurrent_login_limit($newuser->id, session_id());
    }
    redirect(\local_kabsignup\manager::thankyou_url($course, $campaign, $utm));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('signupheading', 'local_kabsignup', $coursename), 2, 'text-center mb-3');
echo $OUTPUT->box(get_string('signupintro', 'local_kabsignup'), 'generalbox boxwidthnormal boxaligncenter');
$form->display();
echo $OUTPUT->footer();
