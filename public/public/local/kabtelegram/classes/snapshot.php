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

namespace local_kabtelegram;

/**
 * What the SendPulse contact learns about the person: the same set WordPress
 * collected from the order (name, e-mail, phone, country, city, order, course,
 * quiz score), taken from the Moodle profile, the signup log of
 * local_kabsignup and the purchases of local_kabshop. Both plugins are optional.
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class snapshot {

    /** Keys of a snapshot, in the order they are documented. */
    const KEYS = ['userid', 'firstname', 'lastname', 'email', 'phone', 'country', 'city',
        'course', 'courseid', 'orderid', 'quizscore', 'persona', 'campaign'];

    /** SendPulse variables WordPress always sent => snapshot key. */
    const BASEVARS = [
        'kab_first_name'  => 'firstname',
        'kab_last_name'   => 'lastname',
        'kab_email'       => 'email',
        'kab_phone'       => 'phone',
        'kab_country'     => 'country',
        'kab_city'        => 'city',
        'kab_order_id'    => 'orderid',
        'kab_course_name' => 'course',
    ];

    /**
     * Collect the snapshot for a user.
     *
     * @param \stdClass $user a user record (id, names, email, phone1, phone2, country, city)
     * @return array key => string|int
     */
    public static function build(\stdClass $user): array {
        global $DB;
        $userid = (int)$user->id;
        $phone = trim((string)($user->phone1 ?? ''));
        if ($phone === '') {
            $phone = trim((string)($user->phone2 ?? ''));
        }
        $data = [
            'userid'    => $userid,
            'firstname' => (string)($user->firstname ?? ''),
            'lastname'  => (string)($user->lastname ?? ''),
            'email'     => (string)($user->email ?? ''),
            'phone'     => $phone,
            'country'   => (string)($user->country ?? ''),
            'city'      => (string)($user->city ?? ''),
            'course'    => '',
            'courseid'  => 0,
            'orderid'   => '',
            'quizscore' => '',
            'persona'   => '',
            'campaign'  => '',
        ];
        $dbman = $DB->get_manager();

        // Course: the latest active enrolment (signup, purchase and manual enrolment all end up there).
        $enrol = $DB->get_records_sql(
            "SELECT ue.id, c.id AS courseid, c.fullname
               FROM {user_enrolments} ue
               JOIN {enrol} e ON e.id = ue.enrolid AND e.status = 0
               JOIN {course} c ON c.id = e.courseid
              WHERE ue.userid = :userid AND ue.status = 0 AND c.id <> :siteid
           ORDER BY ue.timecreated DESC, ue.id DESC",
            ['userid' => $userid, 'siteid' => SITEID], 0, 1);
        if ($enrol) {
            $row = reset($enrol);
            $data['courseid'] = (int)$row->courseid;
            $data['course'] = format_string($row->fullname, true,
                ['context' => \context_course::instance($row->courseid, IGNORE_MISSING) ?: \context_system::instance()]);
        }

        // Order: the storefront purchase that gave this course.
        if ($data['courseid'] && $dbman->table_exists('local_kabshop_purchase')) {
            $purchase = $DB->get_records_sql(
                "SELECT pu.id
                   FROM {local_kabshop_purchase} pu
                   JOIN {local_kabshop_product} pr ON pr.id = pu.productid
                  WHERE pu.userid = :userid AND pu.status = 'ok' AND pr.courseid = :courseid
               ORDER BY pu.timecreated DESC, pu.id DESC",
                ['userid' => $userid, 'courseid' => $data['courseid']], 0, 1);
            if ($purchase) {
                $data['orderid'] = (string)reset($purchase)->id;
            }
        }

        // Quiz score, campaign and persona of the ad: the signup log.
        if ($dbman->table_exists('local_kabsignup_log')) {
            $score = $DB->get_records_select('local_kabsignup_log', 'userid = :userid AND quizscore IS NOT NULL',
                ['userid' => $userid], 'timecreated DESC, id DESC', 'id, quizscore', 0, 1);
            if ($score) {
                $data['quizscore'] = (string)(int)reset($score)->quizscore;
            }
            $camp = $DB->get_records_select('local_kabsignup_log', "userid = :userid AND campaign <> ''",
                ['userid' => $userid], 'timecreated DESC, id DESC', 'id, campaign', 0, 1);
            if ($camp) {
                $data['campaign'] = (string)reset($camp)->campaign;
            }
        }

        // Persona: the profile field the lounges are restricted by.
        [, $field] = manager::personas();
        if ($field !== '') {
            $persona = $DB->get_field_sql(
                "SELECT d.data
                   FROM {user_info_data} d
                   JOIN {user_info_field} f ON f.id = d.fieldid
                  WHERE f.shortname = :field AND d.userid = :userid",
                ['field' => $field, 'userid' => $userid]);
            $data['persona'] = ($persona === false) ? '' : (string)$persona;
        }
        return $data;
    }

    /**
     * Contact variables for SendPulse: the WordPress set plus the extra mapping.
     * Empty values are left out, as WordPress did.
     *
     * @param array $data snapshot
     * @param array $extravars variable => snapshot key
     * @return array variable => string
     */
    public static function variables(array $data, array $extravars): array {
        $out = [];
        foreach (self::BASEVARS + $extravars as $var => $key) {
            $value = isset($data[$key]) ? trim((string)$data[$key]) : '';
            if ($value !== '' && !($key === 'courseid' && $value === '0')) {
                $out[$var] = $value;
            }
        }
        return $out;
    }
}
