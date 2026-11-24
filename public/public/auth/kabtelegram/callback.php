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
 * Back from Telegram.
 *
 * Two steps. Telegram returns the browser to callback.php?state=…#tgAuthResult=…;
 * the fragment never reaches the server, so the GET page reads it with a
 * script, wipes it from the address bar and posts it here with the state and
 * sesskey. The POST checks the state of this browser's round trip, the
 * Telegram signature and its age, then signs in (mode=login) or links
 * (mode=link).
 *
 * @package    auth_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/login/lib.php');

use auth_kabtelegram\manager;

$state = optional_param('state', '', PARAM_ALPHANUM);

// Neither the state nor the Telegram answer goes into the page URL (links, logs, referrers).
$PAGE->set_url(new moodle_url('/auth/kabtelegram/callback.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('logintitle', 'auth_kabtelegram'));
$PAGE->set_heading(format_string($SITE->fullname));
$PAGE->set_cacheable(false);
@header('Referrer-Policy: no-referrer');

if (!is_enabled_auth('kabtelegram') || !manager::is_configured()) {
    throw new moodle_exception('notenabled', 'auth_kabtelegram');
}

/**
 * Print a message and a way back, then stop.
 *
 * @param string $message
 * @param moodle_url|null $back where the button leads (the login page by default)
 * @param string $backlabel
 */
function auth_kabtelegram_fail(string $message, ?moodle_url $back = null, string $backlabel = ''): void {
    global $OUTPUT;
    echo $OUTPUT->header();
    echo $OUTPUT->notification($message, \core\output\notification::NOTIFY_ERROR);
    echo html_writer::div(
        $OUTPUT->single_button($back ?? new moodle_url('/login/index.php'),
            $backlabel !== '' ? $backlabel : get_string('backtologin', 'auth_kabtelegram'), 'get'),
        'text-center'
    );
    echo $OUTPUT->footer();
    exit;
}

if (data_submitted() === false) {
    // Step 1: the GET from Telegram. The answer is in the fragment; post it.
    echo $OUTPUT->header();
    $form = html_writer::start_tag('form', [
        'method' => 'post',
        'action' => $PAGE->url->out(false),
        'id' => 'auth_kabtelegram_result',
        'class' => 'text-center',
        'hidden' => 'hidden',
    ]);
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'state', 'value' => $state]);
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'tgauth', 'value' => '']);
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    $form .= html_writer::tag('p', get_string('loggingin', 'auth_kabtelegram'));
    $form .= html_writer::empty_tag('input', [
        'type' => 'submit',
        'class' => 'btn btn-primary',
        'value' => get_string('continue'),
    ]);
    $form .= html_writer::end_tag('form');
    $missing = html_writer::div(
        $OUTPUT->notification(get_string('notfinished', 'auth_kabtelegram'), \core\output\notification::NOTIFY_WARNING)
        . html_writer::div($OUTPUT->single_button(new moodle_url('/login/index.php'),
            get_string('backtologin', 'auth_kabtelegram'), 'get'), 'text-center'),
        '', ['id' => 'auth_kabtelegram_missing', 'hidden' => 'hidden']
    );
    echo $OUTPUT->box($form . $missing, 'generalbox boxwidthnormal boxaligncenter');
    echo html_writer::tag('noscript', $OUTPUT->notification(get_string('needjs', 'auth_kabtelegram'),
        \core\output\notification::NOTIFY_WARNING));
    echo html_writer::script("
(function() {
    var re = /[#?&]tgAuthResult=([A-Za-z0-9_=+\\/-]*)/;
    var m = (window.location.hash || '').match(re) || (window.location.search || '').match(re);
    if (window.history && window.history.replaceState) {
        window.history.replaceState(null, '', window.location.pathname);
    }
    var form = document.getElementById('auth_kabtelegram_result');
    if (m && m[1]) {
        form.elements['tgauth'].value = m[1];
        form.hidden = false;
        form.submit();
    } else {
        document.getElementById('auth_kabtelegram_missing').hidden = false;
    }
})();
");
    echo $OUTPUT->footer();
    exit;
}

// Step 2: the POST with the answer.
if (!confirm_sesskey()) {
    // No session between Telegram and here: cookies are off, or the page was opened twice.
    auth_kabtelegram_fail(get_string('cookiesnotenabled'));
}
$saved = manager::take_state($state);
if ($saved === null) {
    auth_kabtelegram_fail(get_string('stateinvalid', 'auth_kabtelegram'));
}
$islink = $saved['mode'] === 'link';
$linkpage = new moodle_url('/auth/kabtelegram/link.php');

$fields = manager::decode_result(optional_param('tgauth', '', PARAM_RAW_TRIMMED));
$tg = $fields === null ? null : manager::verify($fields);
if ($tg === null) {
    auth_kabtelegram_fail(get_string('resultinvalid', 'auth_kabtelegram'),
        $islink ? $linkpage : null, $islink ? get_string('backtolink', 'auth_kabtelegram') : '');
}

if ($islink) {
    // Link Telegram to the account that started the round trip.
    if (!isloggedin() || isguestuser() || (int)$USER->id !== (int)$saved['userid']) {
        auth_kabtelegram_fail(get_string('stateinvalid', 'auth_kabtelegram'));
    }
    $result = manager::link((int)$USER->id, $tg, 'user');
    if ($result === 'taken') {
        redirect($linkpage, get_string('linktaken', 'auth_kabtelegram'), null, \core\output\notification::NOTIFY_ERROR);
    }
    manager::clear_pending();
    redirect($linkpage, get_string('linkdone', 'auth_kabtelegram', s(manager::display_name($tg['name'], $tg['username']))),
        null, \core\output\notification::NOTIFY_SUCCESS);
}

// Sign in.
$wantsurl = (string)$saved['wantsurl'];
if (isloggedin() && !isguestuser()) {
    $link = manager::get_link_by_tgid($tg['id']);
    if ($link && (int)$link->userid === (int)$USER->id) {
        redirect(new moodle_url($wantsurl !== '' ? $wantsurl : '/my/'));
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

$result = manager::accept_login($tg);
if ($result['status'] === 'notlinked') {
    // Never create an account: remember the verified Telegram and offer to sign in some other way and link it.
    manager::set_pending($tg, $wantsurl);
    redirect($linkpage);
}
if ($result['status'] !== 'ok') {
    auth_kabtelegram_fail(get_string('loginrefused', 'auth_kabtelegram'));
}

$user = $result['user'];
complete_user_login($user);
\core\session\manager::apply_concurrent_login_limit($user->id, session_id());
if (empty($CFG->nolastloggedin)) {
    set_moodle_cookie($user->username);
}
$SESSION->wantsurl = $wantsurl !== '' ? $wantsurl : $CFG->wwwroot . '/my/';
redirect(core_login_get_return_url());
