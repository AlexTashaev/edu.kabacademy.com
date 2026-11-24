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
 * "Sign in with Telegram" in the user's own settings.
 *
 * Signed in: the link status, link / unlink, and the confirmation for a
 * Telegram that was verified before the user signed in some other way.
 * Not signed in, with such a Telegram waiting: why it did not sign in and
 * how to sign in some other way to link it.
 *
 * @package    auth_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use auth_kabtelegram\manager;

$action = optional_param('action', '', PARAM_ALPHA);

$pageurl = new moodle_url('/auth/kabtelegram/link.php');
$PAGE->set_url($pageurl);
$PAGE->set_cacheable(false);
$PAGE->set_title(get_string('linktitle', 'auth_kabtelegram'));

if (!is_enabled_auth('kabtelegram')) {
    throw new moodle_exception('notenabled', 'auth_kabtelegram');
}

$pending = manager::get_pending();

if (!isloggedin() || isguestuser()) {
    if ($pending === null) {
        require_login(null, false); // Off to the login page; back here afterwards.
    }
    // A verified Telegram that is linked to nobody. Never create an account: offer to sign in and link.
    $PAGE->set_context(context_system::instance());
    $PAGE->set_heading(format_string($SITE->fullname));
    $PAGE->set_pagelayout('login');
    $SESSION->wantsurl = $pageurl->out(false);
    $tgname = manager::display_name($pending['tg']['name'], $pending['tg']['username']);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('notlinkedtitle', 'auth_kabtelegram'), 2, 'text-center mb-3');
    echo $OUTPUT->box(get_string('notlinkedintro', 'auth_kabtelegram', s($tgname)), 'generalbox boxwidthnormal boxaligncenter');
    $buttons = '';
    if (is_enabled_auth('kabmagic')) {
        $buttons .= $OUTPUT->single_button(
            new moodle_url('/auth/kabmagic/request.php', ['wantsurl' => $pageurl->out(false)]),
            get_string('notlinkedemail', 'auth_kabtelegram'), 'get', ['type' => single_button::BUTTON_PRIMARY]);
    }
    $buttons .= $OUTPUT->single_button(new moodle_url('/login/index.php'),
        get_string('notlinkedpassword', 'auth_kabtelegram'), 'get');
    echo html_writer::div($buttons, 'd-flex flex-column align-items-center gap-2');
    echo $OUTPUT->footer();
    exit;
}

// Signed in (require_login() also runs the site policy and MFA checks).
require_login(null, false);
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_pagelayout('admin');
$PAGE->add_body_class('limitedwidth');
$PAGE->set_heading(fullname($USER));
$PAGE->navbar->add(get_string('preferences'), new moodle_url('/user/preferences.php'));
$PAGE->navbar->add(get_string('linktitle', 'auth_kabtelegram'));

$doneurl = function() use ($pending): moodle_url {
    return new moodle_url(!empty($pending['wantsurl']) ? $pending['wantsurl'] : '/my/');
};

if ($action !== '' && data_submitted()) {
    require_sesskey();
    if ($action === 'confirm' && $pending !== null) {
        $result = manager::link((int)$USER->id, $pending['tg'], 'user');
        manager::clear_pending();
        if ($result === 'taken') {
            redirect($pageurl, get_string('linktaken', 'auth_kabtelegram'), null, \core\output\notification::NOTIFY_ERROR);
        }
        redirect($doneurl(), get_string('linkdone', 'auth_kabtelegram',
            s(manager::display_name($pending['tg']['name'], $pending['tg']['username']))),
            null, \core\output\notification::NOTIFY_SUCCESS);
    }
    if ($action === 'skip') {
        manager::clear_pending();
        redirect($doneurl());
    }
    if ($action === 'unlink') {
        manager::unlink((int)$USER->id);
        redirect($pageurl, get_string('unlinkdone', 'auth_kabtelegram'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
    redirect($pageurl);
}

$link = manager::get_link_by_userid((int)$USER->id);
if ($pending !== null && $link && (int)$link->tgid === (int)$pending['tg']['id']) {
    manager::clear_pending(); // That very Telegram is linked already.
    $pending = null;
}

/**
 * A POST button to this page with sesskey.
 *
 * @param string $action
 * @param string $label
 * @param bool $primary
 * @return string
 */
function auth_kabtelegram_action_button(string $action, string $label, bool $primary = false): string {
    global $OUTPUT, $PAGE;
    return $OUTPUT->single_button(new moodle_url($PAGE->url, ['action' => $action]), $label, 'post',
        ['type' => $primary ? single_button::BUTTON_PRIMARY : single_button::BUTTON_SECONDARY]);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('linktitle', 'auth_kabtelegram'));

if ($pending !== null) {
    $a = new stdClass();
    $a->telegram = s(manager::display_name($pending['tg']['name'], $pending['tg']['username']));
    $a->account = s(fullname($USER) . ' (' . $USER->email . ')');
    $body = html_writer::tag('p', get_string('confirmlink', 'auth_kabtelegram', $a));
    if ($link) {
        $body .= html_writer::tag('p', get_string('confirmreplace', 'auth_kabtelegram',
            s(manager::display_name($link->tgname, $link->tgusername))));
    }
    $body .= html_writer::div(
        auth_kabtelegram_action_button('confirm', get_string('confirmlinkyes', 'auth_kabtelegram'), true)
        . auth_kabtelegram_action_button('skip', get_string('confirmlinkno', 'auth_kabtelegram')),
        'd-flex flex-wrap gap-2'
    );
    echo $OUTPUT->box($body, 'generalbox');
}

if ($link) {
    $a = new stdClass();
    $a->telegram = s(manager::display_name($link->tgname, $link->tgusername));
    $a->since = userdate((int)$link->timecreated, get_string('strftimedate', 'langconfig'));
    $body = html_writer::tag('p', get_string('linkedstatus', 'auth_kabtelegram', $a));
    $body .= html_writer::tag('p', (int)$link->timelastlogin > 0
        ? get_string('lastlogin', 'auth_kabtelegram', userdate((int)$link->timelastlogin))
        : get_string('lastloginnever', 'auth_kabtelegram'), ['class' => 'text-muted']);
    $buttons = auth_kabtelegram_action_button('unlink', get_string('unlink', 'auth_kabtelegram'));
    if (manager::is_configured()) {
        $buttons .= $OUTPUT->single_button(new moodle_url('/auth/kabtelegram/start.php',
            ['mode' => 'link', 'sesskey' => sesskey()]), get_string('relink', 'auth_kabtelegram'), 'get');
    }
    $body .= html_writer::div($buttons, 'd-flex flex-wrap gap-2');
    echo $OUTPUT->box($body, 'generalbox');
} else if ($pending === null) {
    $body = html_writer::tag('p', get_string('notlinkedstatus', 'auth_kabtelegram'));
    if (manager::is_configured()) {
        $body .= $OUTPUT->single_button(new moodle_url('/auth/kabtelegram/start.php',
            ['mode' => 'link', 'sesskey' => sesskey()]), get_string('linkbutton', 'auth_kabtelegram'), 'get',
            ['type' => single_button::BUTTON_PRIMARY]);
    }
    echo $OUTPUT->box($body, 'generalbox');
}

echo $OUTPUT->footer();
