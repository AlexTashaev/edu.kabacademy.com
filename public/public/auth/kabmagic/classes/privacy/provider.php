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

namespace auth_kabmagic\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider: the plugin keeps a log of login link requests per user.
 *
 * @package    auth_kabmagic
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
        $collection->add_database_table('auth_kabmagic_tokens', [
            'userid'      => 'privacy:metadata:tokens:userid',
            'emailhash'   => 'privacy:metadata:tokens:emailhash',
            'wantsurl'    => 'privacy:metadata:tokens:wantsurl',
            'timecreated' => 'privacy:metadata:tokens:timecreated',
            'timeused'    => 'privacy:metadata:tokens:timeused',
            'ip'          => 'privacy:metadata:tokens:ip',
        ], 'privacy:metadata:tokens');
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
                  JOIN {auth_kabmagic_tokens} t ON t.userid = ctx.instanceid
                 WHERE ctx.contextlevel = :userlevel AND t.userid = :userid";
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
        $userlist->add_from_sql('userid', 'SELECT userid FROM {auth_kabmagic_tokens} WHERE userid = :userid',
            ['userid' => $context->instanceid]);
    }

    /**
     * Export the request log.
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
            foreach ($DB->get_records('auth_kabmagic_tokens', ['userid' => $user->id], 'timecreated ASC') as $record) {
                $rows[] = (object)[
                    'timecreated' => transform::datetime($record->timecreated),
                    'timeused'    => $record->timeused ? transform::datetime($record->timeused) : null,
                    'ip'          => $record->ip,
                    'wantsurl'    => $record->wantsurl,
                ];
            }
            writer::with_context($context)->export_data(
                [get_string('privacy:path', 'auth_kabmagic')],
                (object)['requests' => $rows]
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
            $DB->delete_records('auth_kabmagic_tokens', ['userid' => $context->instanceid]);
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
                $DB->delete_records('auth_kabmagic_tokens', ['userid' => $user->id]);
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
            $DB->delete_records('auth_kabmagic_tokens', ['userid' => $context->instanceid]);
        }
    }
}
