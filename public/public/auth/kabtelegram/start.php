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
 * Send the browser to Telegram: sign in (mode=login, the button on the login
 * page) or link Telegram to the signed-in account (mode=link, from link.php).
 *
 * @package    auth_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$mode = optional_param('mode', 'login', PARAM_ALPHA);
$wantsurl = optional_param('wantsurl', '', PARAM_LOCALURL);
if ($wantsurl === '' && !empty($SESSION->wantsurl)) {
    $wantsurl = clean_param($SESSION->wantsurl, PARAM_LOCALURL);
}

$PAGE->set_url(new moodle_url('/auth/kabtelegram/start.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');
$PAGE->set_cacheable(false);

if (!is_enabled_auth('kabtelegram') || !\auth_kabtelegram\manager::is_configured()) {
    throw new moodle_exception('notenabled', 'auth_kabtelegram');
}

if ($mode === 'link') {
    require_login(null, false);
    if (isguestuser()) {
        throw new require_login_exception('guest');
    }
    require_sesskey();
    $wantsurl = (new moodle_url('/auth/kabtelegram/link.php'))->out(false);
} else if (isloggedin() && !isguestuser()) {
    // Already signed in: nothing to do with Telegram here.
    redirect(new moodle_url($wantsurl !== '' ? $wantsurl : '/my/'));
}

redirect(\auth_kabtelegram\manager::start($mode, $wantsurl));
