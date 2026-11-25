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

namespace local_kabtelegram\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider: bot token, the link to the SendPulse contact, clicks; the
 * person's data goes to SendPulse when the bot is started from our link.
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    /** Tables with a userid column. */
    const TABLES = ['local_kabtelegram_token', 'local_kabtelegram_contact', 'local_kabtelegram_event'];

    /**
     * Describe the stored data.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_kabtelegram_token', [
            'userid' => 'privacy:metadata:token:userid',
            'token' => 'privacy:metadata:token:token',
            'timecreated' => 'privacy:metadata:token:timecreated',
            'timeexpires' => 'privacy:metadata:token:timeexpires',
        ], 'privacy:metadata:token');
        $collection->add_database_table('local_kabtelegram_contact', [
            'userid' => 'privacy:metadata:contact:userid',
            'contactid' => 'privacy:metadata:contact:contactid',
            'telegramid' => 'privacy:metadata:contact:telegramid',
            'timecreated' => 'privacy:metadata:contact:timecreated',
            'timesynced' => 'privacy:metadata:contact:timesynced',
        ], 'privacy:metadata:contact');
        $collection->add_database_table('local_kabtelegram_event', [
            'userid' => 'privacy:metadata:event:userid',
            'event' => 'privacy:metadata:event:event',
            'place' => 'privacy:metadata:event:place',
            'timecreated' => 'privacy:metadata:event:timecreated',
        ], 'privacy:metadata:event');
        $collection->add_external_location_link('sendpulse', [
            'firstname' => 'privacy:metadata:sendpulse:firstname',
            'lastname' => 'privacy:metadata:sendpulse:lastname',
            'email' => 'privacy:metadata:sendpulse:email',
            'phone' => 'privacy:metadata:sendpulse:phone',
            'country' => 'privacy:metadata:sendpulse:country',
            'city' => 'privacy:metadata:sendpulse:city',
            'course' => 'privacy:metadata:sendpulse:course',
            'quizscore' => 'privacy:metadata:sendpulse:quizscore',
        ], 'privacy:metadata:sendpulse');
        return $collection;
    }

    /**
     * The user context of a person with rows.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        foreach (self::TABLES as $table) {
            $contextlist->add_from_sql("SELECT ctx.id
                                          FROM {context} ctx
                                          JOIN {{$table}} t ON t.userid = ctx.instanceid
                                         WHERE ctx.contextlevel = :userlevel AND t.userid = :userid",
                ['userlevel' => CONTEXT_USER, 'userid' => $userid]);
        }
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
        foreach (self::TABLES as $table) {
            $userlist->add_from_sql('userid', "SELECT userid FROM {{$table}} WHERE userid = :userid",
                ['userid' => $context->instanceid]);
        }
    }

    /**
     * Export.
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
            $subcontext = [get_string('pluginname', 'local_kabtelegram')];
            $token = $DB->get_record('local_kabtelegram_token', ['userid' => $user->id]);
            $contacts = array_values(array_map(function($c) {
                return [
                    'contactid' => $c->contactid,
                    'telegramid' => $c->telegramid,
                    'timecreated' => transform::datetime($c->timecreated),
                    'timesynced' => $c->timesynced ? transform::datetime($c->timesynced) : '',
                ];
            }, $DB->get_records('local_kabtelegram_contact', ['userid' => $user->id], 'id')));
            $events = array_values(array_map(function($e) {
                return [
                    'event' => $e->event,
                    'place' => $e->place,
                    'timecreated' => transform::datetime($e->timecreated),
                ];
            }, $DB->get_records('local_kabtelegram_event', ['userid' => $user->id], 'id')));
            writer::with_context($context)->export_data($subcontext, (object)[
                'token' => $token ? [
                    'timecreated' => transform::datetime($token->timecreated),
                    'timeexpires' => transform::datetime($token->timeexpires),
                ] : null,
                'contacts' => $contacts,
                'events' => $events,
            ]);
        }
    }

    /**
     * Delete everything in a user context.
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if ($context->contextlevel == CONTEXT_USER) {
            self::delete_user((int)$context->instanceid);
        }
    }

    /**
     * Delete one user's rows.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        $userid = (int)$contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_USER && $context->instanceid == $userid) {
                self::delete_user($userid);
            }
        }
    }

    /**
     * Delete the rows of the users in a user context.
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }
        if (in_array((int)$context->instanceid, array_map('intval', $userlist->get_userids()), true)) {
            self::delete_user((int)$context->instanceid);
        }
    }

    /**
     * Remove a user's rows from every table.
     *
     * @param int $userid
     */
    protected static function delete_user(int $userid) {
        global $DB;
        foreach (self::TABLES as $table) {
            $DB->delete_records($table, ['userid' => $userid]);
        }
    }
}
