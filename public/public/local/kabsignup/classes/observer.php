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

namespace local_kabsignup;

/**
 * Event observers.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /**
     * A real login proves the mailbox: the password of an account created here and the login
     * links both arrive only by e-mail. The plugin's own autologin right after the form does not.
     *
     * @param \core\event\user_loggedin $event
     */
    public static function user_loggedin(\core\event\user_loggedin $event): void {
        if (manager::$autologin) {
            return;
        }
        $userid = (int)$event->objectid;
        try {
            if ($userid > 0 && get_user_preferences(manager::PREF_UNVERIFIED, 0, $userid)) {
                unset_user_preference(manager::PREF_UNVERIFIED, $userid);
            }
        } catch (\Throwable $e) {
            // Never break a login over this.
            debugging('local_kabsignup: could not clear ' . manager::PREF_UNVERIFIED . ': ' . $e->getMessage(),
                DEBUG_DEVELOPER);
        }
    }
}
