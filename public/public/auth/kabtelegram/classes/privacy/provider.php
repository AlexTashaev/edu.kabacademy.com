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

namespace auth_kabtelegram\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider: the plugin keeps one Telegram link per user.
 *
 * @package    auth_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    /** @var string */
    const TABLE = 'auth_kabtelegram_links';

    /**
     * Describe the stored data.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(self::TABLE, [
            'userid'        => 'privacy:metadata:links:userid',
            'tgid'          => 'privacy:metadata:links:tgid',
            'tgusername'    => 'privacy:metadata:links:tgusername',
            'tgname'        => 'privacy:metadata:links:tgname',
            'source'        => 'privacy:metadata:links:source',
            'timecreated'   => 'privacy:metadata:links:timecreated',
            'timelastlogin' => 'privacy:metadata:links:timelastlogin',
        ], 'privacy:metadata:links');
        $collection->add_external_location_link('telegram', [
            'origin' => 'privacy:metadata:telegram:origin',
        ], 'privacy:metadata:telegram');
        return $collection;
    }

    /**
     * The user context of a user with a link.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {" . self::TABLE . "} l ON l.userid = ctx.instanceid
                 WHERE ctx.contextlevel = :userlevel AND l.userid = :userid";
        $contextlist->add_from_sql($sql, ['userlevel' => CONTEXT_USER, 'userid' => $userid]);
        return $contextlist;
    }

    /**
     * Users with a link in a user context (only its owner).
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }
        $userlist->add_from_sql('userid', 'SELECT userid FROM {' . self::TABLE . '} WHERE userid = :userid',
            ['userid' => $context->instanceid]);
    }

    /**
     * Export the link.
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
            $link = $DB->get_record(self::TABLE, ['userid' => $user->id]);
            if (!$link) {
                continue;
            }
            writer::with_context($context)->export_data(
                [get_string('privacy:path', 'auth_kabtelegram')],
                (object)[
                    'tgid' => $link->tgid,
                    'tgusername' => $link->tgusername,
                    'tgname' => $link->tgname,
                    'source' => $link->source,
                    'timecreated' => transform::datetime($link->timecreated),
                    'timelastlogin' => $link->timelastlogin ? transform::datetime($link->timelastlogin) : null,
                ]
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
            $DB->delete_records(self::TABLE, ['userid' => $context->instanceid]);
        }
    }

    /**
     * Delete one user's link.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $user = $contextlist->get_user();
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_USER && $context->instanceid == $user->id) {
                $DB->delete_records(self::TABLE, ['userid' => $user->id]);
            }
        }
    }

    /**
     * Delete the links of the listed users in a user context.
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
            $DB->delete_records(self::TABLE, ['userid' => $context->instanceid]);
        }
    }
}
