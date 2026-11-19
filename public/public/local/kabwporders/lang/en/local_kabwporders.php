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
 * Strings for local_kabwporders, English.
 *
 * @package    local_kabwporders
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Old site payments (WordPress)';
$string['registry'] = 'Old site payments';
$string['registry_help'] = 'Paid orders of kabacademy.com before the move to Moodle: who paid, when, for what and how much. Read-only — the data is loaded by import.';
$string['import'] = 'Import old site payments';
$string['import_help'] = 'Upload the three WordPress export files: orders, items and products (.tsv or .tsv.gz). The import replaces the registry as a whole, so repeating it creates no duplicates. Moodle users are neither created nor changed.';
$string['importfiles'] = 'Export files';
$string['importfiles_help'] = 'orders.tsv, items.tsv and products.tsv as written by the export script. Gzipped files are accepted: orders.tsv.gz. Uploaded files are removed from the server after the import.';
$string['importrun'] = 'Import';
$string['importdone'] = 'Import finished. Compare the numbers with the export script summary.';
$string['importfailed'] = 'Import failed, the registry is unchanged: {$a}';
$string['importdir_found'] = 'The server directory set in config.php holds a complete export.';
$string['importdir_run'] = 'Import from the server directory';
$string['empty'] = 'The registry is empty — nothing has been imported yet.';
$string['notfound'] = 'Nothing found.';
$string['found'] = 'Orders found: {$a}';
$string['searchhint'] = 'Order number, email, name, transaction number';
$string['exportcsv'] = 'Export to CSV';
$string['allyears'] = 'All years';
$string['allcurrencies'] = 'All currencies';
$string['allstatuses'] = 'All statuses';

$string['col_order'] = 'Order no.';
$string['col_date'] = 'Paid on';
$string['col_payer'] = 'Payer';
$string['col_email'] = 'Email';
$string['col_products'] = 'Paid for';
$string['col_amount'] = 'Amount';
$string['col_refunded'] = 'Refunded';
$string['col_status'] = 'Status';
$string['col_gateway'] = 'Payment method';
$string['col_txn'] = 'Transaction number';
$string['col_currency'] = 'Currency';
$string['col_year'] = 'Year';

$string['tot_orders'] = 'Orders';
$string['tot_amount'] = 'Amount';
$string['tot_refunds'] = 'Orders with a refund';
$string['tot_refunded'] = 'Refunded amount';
$string['tot_people'] = 'Linked to a Moodle user: {$a->linked}. Not linked: {$a->unlinked}. Last import: {$a->date}.';

$string['status_completed'] = 'Completed';
$string['status_processing'] = 'Processing';
$string['status_refunded'] = 'Refunded';
$string['status_cancelled'] = 'Paid, later cancelled';
$string['status_failed'] = 'Paid, later marked failed';
$string['status_pending'] = 'Paid, status pending';
$string['status_onhold'] = 'Paid, on hold';

$string['res_orders'] = 'Orders in the registry';
$string['res_items'] = 'Line items';
$string['res_itemswithcourse'] = 'Line items with a Moodle course';
$string['res_orderswithoutitems'] = 'Orders without line items';
$string['res_itemorphans'] = 'Line items without an order (skipped)';
$string['res_bylink'] = 'User found by the WordPress link';
$string['res_byemail'] = 'User found by email';
$string['res_unmatched'] = 'Orders without a Moodle user';
$string['res_unmatchedemails'] = 'Email addresses without a Moodle user';
$string['res_linkemaildiffers'] = 'Linked, but the Moodle email differs';

$string['err_nofile'] = 'file {$a} is missing';
$string['err_empty'] = 'orders.tsv has no orders';
$string['err_header'] = 'the header of {$a} does not match the expected one';
$string['err_columns'] = '{$a} has a row with a wrong number of columns';

$string['kabwporders:view'] = 'View old site payments';
$string['kabwporders:import'] = 'Import old site payments';

$string['privacy:metadata:order'] = 'Paid orders of the old kabacademy.com site';
$string['privacy:metadata:order:userid'] = 'The user the order is linked to';
$string['privacy:metadata:order:payeremail'] = 'Payer email at the time of payment';
$string['privacy:metadata:order:payername'] = 'Payer name at the time of payment';
$string['privacy:metadata:order:amount'] = 'Order amount and currency';
$string['privacy:metadata:order:timepaid'] = 'Payment date';
$string['privacy:metadata:order:txnid'] = 'Transaction number at the payment gateway';
