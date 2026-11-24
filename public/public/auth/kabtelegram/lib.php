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
 * Callbacks.
 *
 * @package    auth_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * "Sign in with Telegram" in Preferences → User account, for one's own settings only.
 *
 * @param navigation_node $navigation the user settings node
 * @param stdClass $user whose settings these are
 * @param context_user $usercontext
 * @param stdClass $course
 * @param context_course $coursecontext
 */
function auth_kabtelegram_extend_navigation_user_settings(navigation_node $navigation, stdClass $user, $usercontext,
        stdClass $course, $coursecontext) {
    global $USER, $PAGE;

    if (!$PAGE->url || !$PAGE->url->compare(new moodle_url('/user/preferences.php'), URL_MATCH_BASE)) {
        return;
    }
    if ((int)$user->id !== (int)$USER->id || isguestuser() || !is_enabled_auth('kabtelegram')) {
        return;
    }
    if (!\auth_kabtelegram\manager::is_configured()) {
        return;
    }
    $usernode = $navigation->find('useraccount', navigation_node::TYPE_CONTAINER);
    if (!$usernode) {
        return;
    }
    $usernode->add_node(navigation_node::create(get_string('linktitle', 'auth_kabtelegram'),
        new moodle_url('/auth/kabtelegram/link.php'), navigation_node::TYPE_SETTING, null, 'auth_kabtelegram_link'));
}
