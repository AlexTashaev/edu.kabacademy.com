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
 * Sign in by the link from the e-mail.
 *
 * Two steps. The GET from the e-mail only checks that the token is live and
 * shows a self-submitting form: mail scanners and link previews open links,
 * and a one-time token must not die in their hands. The POST (with sesskey)
 * consumes the token, signs the user in and redirects.
 *
 * @package    auth_kabmagic
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/login/lib.php');

$token = required_param('t', PARAM_ALPHANUMEXT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

// The token stays out of the page URL (links, logs, referrers).
$PAGE->set_url(new moodle_url('/auth/kabmagic/login.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('logintitle', 'auth_kabmagic'));
$PAGE->set_heading(format_string($SITE->fullname));
$PAGE->set_cacheable(false);
@header('Referrer-Policy: no-referrer');

if (!is_enabled_auth('kabmagic')) {
    throw new moodle_exception('notenabled', 'auth_kabmagic');
}

/**
 * Print a message and a button to ask for a new link, then stop.
 *
 * @param string $message
 */
function auth_kabmagic_fail(string $message): void {
    global $OUTPUT;
    echo $OUTPUT->header();
    echo $OUTPUT->notification($message, \core\output\notification::NOTIFY_ERROR);
    echo html_writer::div(
        $OUTPUT->single_button(new moodle_url('/auth/kabmagic/request.php'), get_string('requestnew', 'auth_kabmagic'), 'get'),
        'text-center'
    );
    echo $OUTPUT->footer();
    exit;
}

$record = \auth_kabmagic\manager::peek($token);
if ($record === null) {
    auth_kabmagic_fail(get_string('linkinvalid', 'auth_kabmagic'));
}

if (isloggedin() && !isguestuser()) {
    if ($USER->id == $record->userid) {
        // Already signed in as this very user: just go where the link wanted to.
        \auth_kabmagic\manager::consume($token);
        redirect(new moodle_url(!empty($record->wantsurl) ? $record->wantsurl : '/my/'));
    }
    // Somebody else is signed in here: same treatment as on the standard login page.
    echo $OUTPUT->header();
    $logout = new single_button(new moodle_url('/login/logout.php', ['sesskey' => sesskey(), 'loginpage' => 1]),
        get_string('logout'), 'post');
    $continue = new single_button(new moodle_url('/'), get_string('cancel'), 'get');
    echo $OUTPUT->confirm(get_string('alreadyloggedin', 'error', fullname($USER)), $logout, $continue);
    echo $OUTPUT->footer();
    exit;
}

if ($confirm) {
    if (!confirm_sesskey()) {
        // No session between the two steps: cookies are off, or the link was opened twice.
        auth_kabmagic_fail(get_string('cookiesnotenabled'));
    }
    $result = \auth_kabmagic\manager::consume($token);
    if ($result === null) {
        auth_kabmagic_fail(get_string('linkinvalid', 'auth_kabmagic'));
    }
    $user = $result['user'];

    complete_user_login($user);
    \core\session\manager::apply_concurrent_login_limit($user->id, session_id());
    if (login_is_lockedout($user)) {
        // The mailbox is exactly what Moodle's own unlock e-mail verifies.
        login_unlock_account($user);
    }
    if (empty($CFG->nolastloggedin)) {
        set_moodle_cookie($user->username);
    }
    $SESSION->wantsurl = $result['wantsurl'] !== '' ? $result['wantsurl'] : $CFG->wwwroot . '/my/';
    redirect(core_login_get_return_url());
}

// Step 1: the GET from the e-mail. Nothing is consumed yet.
echo $OUTPUT->header();
$form = html_writer::start_tag('form', [
    'method' => 'post',
    'action' => $PAGE->url->out(false),
    'id' => 'auth_kabmagic_confirm',
    'class' => 'text-center',
]);
$form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 't', 'value' => $token]);
$form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'confirm', 'value' => 1]);
$form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
$form .= html_writer::tag('p', get_string('loggingin', 'auth_kabmagic'));
$form .= html_writer::empty_tag('input', [
    'type' => 'submit',
    'class' => 'btn btn-primary',
    'value' => get_string('loginconfirm', 'auth_kabmagic'),
]);
$form .= html_writer::end_tag('form');
echo $OUTPUT->box($form, 'generalbox boxwidthnormal boxaligncenter');
echo html_writer::script("document.getElementById('auth_kabmagic_confirm').submit();");
echo $OUTPUT->footer();
