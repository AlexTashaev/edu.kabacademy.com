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

namespace paygw_kbb1\privacy;

use core_payment\privacy\paygw_provider;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\writer;

/**
 * Privacy: что хранится и что уходит в external_payments.
 *
 * @package    paygw_kbb1
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\data_provider, paygw_provider {

    /**
     * Описание данных.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('paygw_kbb1', [
            'userid' => 'privacy:metadata:paygw_kbb1:userid',
            'amount' => 'privacy:metadata:paygw_kbb1:amount',
            'txnid' => 'privacy:metadata:paygw_kbb1:txnid',
            'token' => 'privacy:metadata:paygw_kbb1:token',
        ], 'privacy:metadata:paygw_kbb1');
        $collection->add_external_location_link('external_payments', [
            'fullname' => 'privacy:metadata:external:fullname',
            'email' => 'privacy:metadata:external:email',
            'phone' => 'privacy:metadata:external:phone',
            'country' => 'privacy:metadata:external:country',
        ], 'privacy:metadata:external');
        return $collection;
    }

    /**
     * Экспорт данных о платеже.
     *
     * @param \context $context
     * @param array $subcontext
     * @param \stdClass $payment
     */
    public static function export_payment_data(\context $context, array $subcontext, \stdClass $payment) {
        global $DB;
        $rec = $DB->get_record('paygw_kbb1', ['paymentid' => $payment->id]);
        if ($rec) {
            writer::with_context($context)->export_data(
                array_merge($subcontext, [get_string('gatewayname', 'paygw_kbb1')]),
                (object) ['method' => $rec->method, 'txnid' => $rec->txnid, 'amount' => $rec->amount,
                    'currency' => $rec->currency, 'status' => $rec->status]
            );
        }
    }

    /**
     * Удаление данных платежей.
     *
     * @param string $paymentsql
     * @param array $paymentparams
     */
    public static function delete_data_for_payment_sql(string $paymentsql, array $paymentparams) {
        global $DB;
        $DB->delete_records_select('paygw_kbb1', "paymentid IN ($paymentsql)", $paymentparams);
    }
}
