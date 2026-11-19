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

namespace local_kabwporders\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy API: реестр хранит оплаты человека с почтой и именем на момент оплаты.
 *
 * По запросу на удаление финансовую строку не стираем (сумма и дата нужны учёту), а обезличиваем:
 * отвязываем от пользователя и убираем почту и имя.
 *
 * @package    local_kabwporders
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Что храним.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_kabwporders_order', [
            'userid'     => 'privacy:metadata:order:userid',
            'payeremail' => 'privacy:metadata:order:payeremail',
            'payername'  => 'privacy:metadata:order:payername',
            'amount'     => 'privacy:metadata:order:amount',
            'timepaid'   => 'privacy:metadata:order:timepaid',
            'txnid'      => 'privacy:metadata:order:txnid',
        ], 'privacy:metadata:order');
        return $collection;
    }

    /**
     * Данные лежат в системном контексте.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;
        $contextlist = new contextlist();
        if ($DB->record_exists('local_kabwporders_order', ['userid' => $userid])) {
            $contextlist->add_system_context();
        }
        return $contextlist;
    }

    /**
     * Пользователи с оплатами.
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        if ($userlist->get_context() instanceof \context_system) {
            $userlist->add_from_sql('userid',
                'SELECT DISTINCT userid FROM {local_kabwporders_order} WHERE userid > 0', []);
        }
    }

    /**
     * Выгрузка оплат человека.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_system) {
                continue;
            }
            $orders = $DB->get_records('local_kabwporders_order',
                ['userid' => $contextlist->get_user()->id], 'wporderid');
            if (!$orders) {
                continue;
            }
            $data = [];
            foreach (\local_kabwporders\api::with_items($orders) as $o) {
                $data[] = (object) [
                    'order'    => $o->wporderid,
                    'paid'     => transform::datetime($o->timepaid ?: $o->timecreated),
                    'email'    => $o->payeremail,
                    'name'     => $o->payername,
                    'products' => \local_kabwporders\api::products($o),
                    'amount'   => $o->amount . ' ' . $o->currency,
                    'refunded' => $o->refunded,
                    'status'   => $o->status,
                    'txnid'    => $o->txnid,
                ];
            }
            writer::with_context($context)->export_data(
                [get_string('pluginname', 'local_kabwporders')], (object) ['orders' => $data]);
        }
    }

    /**
     * Обезличить все строки контекста.
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if ($context instanceof \context_system) {
            self::anonymise('userid > 0', []);
        }
    }

    /**
     * Обезличить строки одного человека.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof \context_system) {
                self::anonymise('userid = :userid', ['userid' => $contextlist->get_user()->id]);
            }
        }
    }

    /**
     * Обезличить строки нескольких людей.
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        if (!$userlist->get_context() instanceof \context_system || !$userlist->get_userids()) {
            return;
        }
        [$insql, $params] = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        self::anonymise("userid $insql", $params);
    }

    /**
     * Отвязать строки от человека и убрать почту и имя; сумма, дата и номер заказа остаются.
     *
     * @param string $where
     * @param array $params
     */
    protected static function anonymise(string $where, array $params) {
        global $DB;
        $DB->execute("UPDATE {local_kabwporders_order}
                         SET userid = 0, wpuserid = 0, payeremail = '', payername = ''
                       WHERE $where", $params);
    }
}
