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

namespace local_kabsignup\task;

use local_kabsignup\capi;
use local_kabsignup\dupcheck;
use local_kabsignup\geo;
use local_kabsignup\manager;

/**
 * Background work after a signup, off the request: for a newly created
 * account — city/country by IP and the duplicate-account alert; for every
 * first enrolment of a campaign with the pixel on — the Facebook
 * CompleteRegistration event (Conversions API).
 *
 * Custom data: logid, userid, courseid, ip, newuser (absent in tasks queued by
 * 1.1–1.2: then true), capi (event from capi::payload() or null).
 *
 * Each step marks the log row (flags) before or right after it runs, so a
 * retried task never sends an alert or an event twice.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class after_signup extends \core\task\adhoc_task {

    /**
     * Run.
     */
    public function execute() {
        global $DB;
        $data = $this->get_custom_data();
        $logid = (int)($data->logid ?? 0);
        $userid = (int)($data->userid ?? 0);
        $newuser = !isset($data->newuser) || !empty($data->newuser);
        $user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0],
            'id, email, firstname, lastname, phone1, phone2, auth, suspended, city, country');
        if (!$user) {
            return;
        }
        $done = $logid ? (string)$DB->get_field('local_kabsignup_log', 'flags', ['id' => $logid]) : '';

        if ($newuser && strpos($done, 'geo') === false) {
            try {
                if (geo::fill($userid, (string)($data->ip ?? ''))) {
                    manager::add_flags($logid, ['geo']);
                }
            } catch (\Throwable $e) {
                mtrace('local_kabsignup: geo lookup failed: ' . $e->getMessage());
            }
        }

        if ($newuser && strpos($done, 'dup') === false) {
            $matches = dupcheck::find($user);
            if ($matches) {
                // Flag first: a retry after a mail failure must not send twice.
                manager::add_flags($logid, ['dup:' . implode(',', dupcheck::signals($matches))]);
                $course = $DB->get_record('course', ['id' => (int)($data->courseid ?? 0)], 'id, fullname');
                dupcheck::alert($user, $matches, manager::config()->dupalertto, $course ?: null);
            }
        }

        if (!empty($data->capi) && strpos($done, 'capi') === false) {
            $event = json_decode(json_encode($data->capi), true);
            try {
                $ok = capi::send($event, manager::config());
            } catch (\Throwable $e) {
                mtrace('local_kabsignup: Conversions API failed: ' . $e->getMessage());
                $ok = false;
            }
            // Not retried: a late registration event is worth little, a duplicate one costs.
            manager::add_flags($logid, [$ok ? 'capi' : 'capi:err']);
        }
    }
}
