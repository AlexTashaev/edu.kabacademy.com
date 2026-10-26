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

namespace local_kabsignup\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider: the plugin keeps a per-user log of signup attempts.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Describe the stored data.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_kabsignup_log', [
            'courseid'    => 'privacy:metadata:log:courseid',
            'userid'      => 'privacy:metadata:log:userid',
            'emailhash'   => 'privacy:metadata:log:emailhash',
            'newuser'     => 'privacy:metadata:log:newuser',
            'campaign'    => 'privacy:metadata:log:campaign',
            'status'      => 'privacy:metadata:log:status',
            'ip'          => 'privacy:metadata:log:ip',
            'timecreated' => 'privacy:metadata:log:timecreated',
        ], 'privacy:metadata:log');
        return $collection;
    }

    /**
     * The user context of everybody who has rows.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {local_kabsignup_log} l ON l.userid = ctx.instanceid
                 WHERE ctx.contextlevel = :userlevel AND l.userid = :userid";
        $contextlist->add_from_sql($sql, ['userlevel' => CONTEXT_USER, 'userid' => $userid]);
        return $contextlist;
    }

    /**
     * Users with rows in a user context (only its owner).
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_kabsignup_log} WHERE userid = :userid',
            ['userid' => $context->instanceid]);
    }

    /**
     * Export the signup log.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $user = $contextlist->get_user();
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_USER || $context->instanceid != $user->id) {
                continue;
            }
            $rows = [];
            foreach ($DB->get_records('local_kabsignup_log', ['userid' => $user->id], 'timecreated ASC') as $record) {
                $rows[] = (object)[
                    'courseid'    => $record->courseid,
                    'campaign'    => $record->campaign,
                    'status'      => $record->status,
                    'newuser'     => transform::yesno($record->newuser),
                    'ip'          => $record->ip,
                    'timecreated' => transform::datetime($record->timecreated),
                ];
            }
            writer::with_context($context)->export_data(
                [get_string('privacy:path', 'local_kabsignup')],
                (object)['signups' => $rows]
            );
        }
    }

    /**
     * Delete everything in a user context.
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context->contextlevel == CONTEXT_USER) {
            $DB->delete_records('local_kabsignup_log', ['userid' => $context->instanceid]);
        }
    }

    /**
     * Delete one user's rows.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $user = $contextlist->get_user();
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_USER && $context->instanceid == $user->id) {
                $DB->delete_records('local_kabsignup_log', ['userid' => $user->id]);
            }
        }
    }

    /**
     * Delete the rows of the listed users in a user context.
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }
        if (in_array($context->instanceid, $userlist->get_userids())) {
            $DB->delete_records('local_kabsignup_log', ['userid' => $context->instanceid]);
        }
    }
}
