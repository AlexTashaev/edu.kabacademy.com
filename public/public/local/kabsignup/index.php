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
 * ?id=<courseid>&camp=<campaign slug>&<ad and quiz parameters> — the course must
 * be listed in the plugin's "courses open for signup" setting (without id the
 * campaign's course is used). A guest fills in a name, an e-mail and a phone:
 * a free address gets a new account (and is logged in right away), a taken one
 * is enrolled as the existing account (or asked to log in, depending on the
 * setting). A logged-in visitor is enrolled without the form. Everybody ends up
 * on the thank-you page of the campaign/course; somebody already enrolled — on
 * the plugin's "you are already enrolled" page.
 *
 * A campaign (local_kabsignup_campaign, camp=) gives the page its colours, logo,
 * course card and button text, and the thank-you address. preview=1 lets a
 * campaign manager see the page as a guest does, without signing up.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_kabsignup\attribution;
use local_kabsignup\campaigns;
use local_kabsignup\manager;
use local_kabsignup\persona;
use local_kabsignup\skin;

$config = manager::config();
$campaign = core_text::strtolower(core_text::substr(optional_param('camp', '', PARAM_ALPHANUMEXT), 0, 64));
$campaignrec = campaigns::get_visible($campaign);
$courseid = optional_param('id', 0, PARAM_INT);
if ($courseid <= 0 && $campaignrec) {
    $courseid = (int)$campaignrec->courseid;
}
$attr = attribution::from_request();
$persona = persona::slug($attr['pres'] ?? '', $config);
$preview = optional_param('preview', 0, PARAM_BOOL)
    && has_capability('local/kabsignup:manage', context_system::instance());

$pageparams = ['id' => $courseid];
if ($campaign !== '') {
    $pageparams['camp'] = $campaign;
}
$pageparams += $attr;
if ($preview) {
    $pageparams['preview'] = 1;
}
$pageurl = new moodle_url('/local/kabsignup/index.php', $pageparams);
$sourceurl = (new moodle_url('/local/kabsignup/index.php',
    array_filter(['id' => $courseid, 'camp' => $campaign])))->out(false);
$context = ['attr' => $attr, 'campaignrec' => $campaignrec, 'persona' => $persona, 'sourceurl' => $sourceurl];

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
    skin::setup($PAGE, $campaignrec);
    echo $OUTPUT->header();
    echo skin::style($campaignrec);
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

// Signup conditions: part 2 of a course only for those enrolled in part 1.
$prereqnames = manager::prerequisite_names((int)$course->id, $config);

/**
 * "Signup is open only to part-1 students" page, then exit.
 *
 * @param bool $loggedin
 */
$noprereqpage = function(bool $loggedin) use ($OUTPUT, $PAGE, $USER, $coursename, $prereqnames, $pageurl,
        $campaignrec) {
    global $SESSION;
    $a = (object)['course' => $coursename, 'required' => $prereqnames,
        'email' => $loggedin ? s($USER->email) : ''];
    skin::setup($PAGE, $campaignrec);
    echo $OUTPUT->header();
    echo skin::style($campaignrec);
    echo skin::heading($campaignrec, get_string('noprereqheading', 'local_kabsignup'));
    echo $OUTPUT->box(get_string($loggedin ? 'noprereqtextloggedin' : 'noprereqtext', 'local_kabsignup', $a),
        'generalbox boxwidthnormal boxaligncenter');
    if (!$loggedin) {
        // Studied under this address but the form still said no? Logging in
        // brings them back here, and the logged-in branch re-checks.
        $SESSION->wantsurl = $pageurl->out(false);
        $buttons = $OUTPUT->single_button($pageurl, get_string('tryagain', 'local_kabsignup'), 'get');
        if (is_enabled_auth('kabmagic')) {
            $buttons .= ' ' . $OUTPUT->single_button(
                new moodle_url('/auth/kabmagic/request.php', ['wantsurl' => $pageurl->out(false)]),
                get_string('magicbutton', 'local_kabsignup'), 'get');
        }
        echo html_writer::div($buttons, 'text-center');
    }
    echo $OUTPUT->footer();
    exit;
};

if (!$preview && isloggedin() && !isguestuser()) {
    if (is_siteadmin() || has_capability('moodle/course:update', $coursecontext)) {
        // Admins and the course's teachers preview the flow without enrolling themselves.
        redirect(manager::thankyou_url($course, $campaign, $attr, '', false, $campaignrec, $persona));
    }
    $result = manager::signup_loggedin($USER, $course, $campaign, (string)getremoteaddr(), $context);
    if ($result['status'] === 'noprereq') {
        $noprereqpage(true);
    }
    redirect(manager::thankyou_url($course, $campaign, $attr, $result['eventid'], $result['status'] === 'already',
        $campaignrec, $persona));
}

// Honeypot: bots fill the invisible field; people never see it.
if (data_submitted() && optional_param('kab_website', '', PARAM_RAW) !== '') {
    redirect(manager::thankyou_url($course, $campaign, $attr, '', false, $campaignrec, $persona));
}

$form = new \local_kabsignup\form\signup_form($pageurl, [
    'phonerequired' => $config->phonerequired,
    'termsurl' => $config->termsurl,
    'privacyurl' => $config->privacyurl,
    'buttonlabel' => $campaignrec ? (string)$campaignrec->buttonlabel : '',
    'belowbutton' => $campaignrec ? (string)$campaignrec->belowbutton : '',
    'country' => skin::country(),
    'courseid' => (int)$course->id,
    'campaign' => $campaign,
]);
skin::setup($PAGE, $campaignrec, true);

$data = $form->get_data();
if ($data && $preview) {
    // A manager's preview never signs anybody up.
    $data = null;
    $previewsubmitted = true;
}

if ($data) {
    $result = manager::signup($course, $data->firstname, (string)($data->lastname ?? ''),
        $data->email, (string)($data->phone ?? ''), $campaign, (string)getremoteaddr(), $context);

    if ($result['status'] === 'throttled') {
        echo $OUTPUT->header();
        echo skin::style($campaignrec);
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

    if ($result['status'] === 'noprereq') {
        $noprereqpage(false);
    }

    if ($result['status'] === 'exists') {
        // "Ask to log in" mode: after the login Moodle returns here and the
        // logged-in branch above finishes the enrolment.
        $SESSION->wantsurl = $pageurl->out(false);
        echo $OUTPUT->header();
        echo skin::style($campaignrec);
        echo skin::heading($campaignrec, get_string('existsheading', 'local_kabsignup'));
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
        manager::autologin((int)$result['user']->id);
    }
    redirect(manager::thankyou_url($course, $campaign, $attr, $result['eventid'], $result['status'] === 'already',
        $campaignrec, $persona));
}

echo $OUTPUT->header();
echo skin::style($campaignrec);
if ($preview) {
    echo $OUTPUT->notification(get_string(empty($previewsubmitted) ? 'previewnotice' : 'previewsubmitted',
        'local_kabsignup'), \core\output\notification::NOTIFY_INFO, false);
}
echo skin::intro($campaignrec, get_string('signupheading', 'local_kabsignup', $coursename),
    get_string('signupintro', 'local_kabsignup'));
if ($prereqnames !== '') {
    echo $OUTPUT->notification(get_string('prereqintro', 'local_kabsignup', $prereqnames),
        \core\output\notification::NOTIFY_INFO, false);
}
$form->display();
echo $OUTPUT->footer();
