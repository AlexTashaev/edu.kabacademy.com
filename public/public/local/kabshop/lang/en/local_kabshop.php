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
 * Language strings for local_kabshop.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$string['accountrequired'] = 'Choose a payment account for a paid product.';
$string['addproduct'] = 'Add product';
$string['addsection'] = 'Add section';
$string['allproducts'] = 'All products';
$string['amountrange'] = 'Enter an amount from {$a->min} to {$a->max}.';
$string['attempt_cancel'] = 'Cancelled by buyer';
$string['attempt_error'] = 'Payment error';
$string['attempt_pending'] = 'Went to pay, did not return';
$string['attempts'] = 'Unfinished payments';
$string['attempts_help'] = 'Card / PayPal payment attempts that did not complete, for "I paid but have no access" requests. The service number is what accounting sees.';
$string['backtocatalog'] = 'Back to catalogue';
$string['buy'] = 'Buy';
$string['catalog'] = 'Courses';
$string['changeamount'] = 'Change amount';
$string['chooseamount'] = 'Choose an amount';
$string['col_buyer'] = 'Buyer';
$string['col_email'] = 'Email';
$string['col_order'] = 'Order #';
$string['col_orderdate'] = 'Date';
$string['col_ordertype'] = 'Type';
$string['col_paymethod'] = 'Payment method';
$string['col_serviceref'] = 'Payment service #';
$string['col_subamount'] = 'Amount';
$string['col_subproduct'] = 'Product';
$string['col_txn'] = 'Transaction';
$string['courseid'] = 'Moodle course ID';
$string['courseid_help'] = 'Course the buyer is enrolled into. 0 — no course (donation, service).';
$string['coursenotfound'] = 'Course not found';
$string['customamount'] = 'Amount of choice';
$string['customamount_help'] = 'The buyer chooses the amount. The price field is the minimum.';
$string['datefrom'] = 'From';
$string['dateto'] = 'To';
$string['description'] = 'Description';
$string['editproduct'] = 'Edit product';
$string['editsection'] = 'Edit section';
$string['emptycatalog'] = 'The catalogue is empty for now.';
$string['enrolfree'] = 'Enrol for free';
$string['enrolled'] = 'You are enrolled';
$string['enrolperiod'] = 'Access period';
$string['enrolperiod_help'] = 'How long access to the course lasts after purchase. Disabled — unlimited.';
$string['exportcsv'] = 'Export CSV';
$string['externalurl'] = 'External link';
$string['free'] = 'Free';
$string['gotocourse'] = 'Go to course';
$string['gotoexternal'] = 'Open';
$string['hidden'] = 'hidden';
$string['image'] = 'Image';
$string['image_help'] = '16:9. If empty, the course image is used.';
$string['invalidamount'] = 'Invalid amount';
$string['invalidprice'] = 'Price cannot be negative';
$string['kabshop:manage'] = 'Manage the storefront';
$string['kind'] = 'Type';
$string['kind_course'] = 'Course';
$string['kind_donation'] = 'Donation';
$string['kind_external'] = 'External link';
$string['kind_service'] = 'Service';
$string['loginandbuy'] = 'Log in and buy';
$string['loginanddonate'] = 'Log in and donate';
$string['loginandenrol'] = 'Log in and enrol';
$string['mail_course'] = 'You are enrolled in "{$a->course}":';
$string['mail_course_extended'] = 'Your access to "{$a->course}" is extended:';
$string['mail_failed_final'] = 'We could not charge {$a->amount} for "{$a->product}" after several attempts, so the subscription is stopped. To resume, subscribe again: {$a->producturl}';
$string['mail_failed_final_subject'] = 'Subscription stopped: {$a->product}';
$string['mail_failed_retry'] = 'We could not charge {$a->amount} for "{$a->product}". We will try again in a day. If your card changed or lacks funds, please check it, or subscribe again: {$a->producturl}';
$string['mail_failed_subject'] = 'Payment failed: {$a->product}';
$string['mail_hello'] = 'Hello {$a->firstname},';
$string['mail_next'] = 'Next charge of {$a->subamount} on {$a->next}. You can cancel in "My subscriptions":';
$string['mail_order'] = 'Order number: {$a->order}';
$string['mail_paid_body'] = 'Your payment of {$a->amount} for "{$a->product}" was successful.';
$string['mail_paid_subject'] = 'Payment received: {$a->product}';
$string['mail_receipt'] = 'The receipt will be sent separately by our accounting department.';
$string['mail_renewal_body'] = 'Your automatic payment of {$a->amount} for "{$a->product}" was successful.';
$string['mail_renewal_subject'] = 'Automatic payment received: {$a->product}';
$string['mail_signature'] = 'Best regards, {$a->site}';
$string['mail_txn'] = 'Transaction number: {$a->txn}';
$string['mailpreview'] = 'Payment email';
$string['mailpreview_subject'] = 'Subject:';
$string['mailpreview_to'] = 'To:';
$string['manage'] = 'Storefront';
$string['managesub'] = 'Manage subscription';
$string['more'] = 'Details';
$string['myorders'] = 'My orders';
$string['mysubscriptions'] = 'My subscriptions';
$string['noattempts'] = 'No unfinished payments.';
$string['noorders'] = 'No orders.';
$string['nopayment'] = 'Payment is not set up for this product yet.';
$string['noproducts'] = 'No products in this section.';
$string['nosubscriptions'] = 'No recurring subscriptions.';
$string['orders'] = 'Storefront orders';
$string['orderstotal'] = 'Orders: {$a->count}, paid: {$a->sum}';
$string['ordertype_free'] = 'Free';
$string['ordertype_paid'] = 'Payment';
$string['ordertype_renewal'] = 'Automatic payment';
$string['otheramount'] = 'Or your own amount';
$string['payamount'] = 'Pay {$a}';
$string['per1'] = 'per month';
$string['per12'] = 'per year';
$string['per3'] = 'every 3 months';
$string['pluginname'] = 'Kabacademy storefront';
$string['presets'] = 'Suggested amounts';
$string['presets_help'] = 'Comma-separated, e.g. 10, 25, 50, 100. Amounts below the price are skipped.';
$string['price'] = 'Price';
$string['price_help'] = '0 — free.';
$string['pricefrom'] = 'from {$a}';
$string['pricelabel'] = 'Price label';
$string['pricelabel_help'] = 'Shown after the price, e.g. "per month".';
$string['privacy:metadata'] = 'The storefront stores purchases: product, user, payment, amount, time.';
$string['productnotfound'] = 'Product not found';
$string['recur_1'] = 'Monthly';
$string['recur_12'] = 'Yearly';
$string['recur_3'] = 'Every 3 months';
$string['recur_none'] = 'One-off';
$string['recurfree'] = 'Recurring charges need a paid product.';
$string['recurring'] = 'Recurrence';
$string['recurring_help'] = 'Charges the saved card automatically: the first payment is normal, then it repeats every period. Course access lasts until the next charge plus 3 days. Works with the Card / PayPal gateway.';
$string['searchhint'] = 'Order number, name or email';
$string['section'] = 'Section';
$string['sortorder'] = 'Order';
$string['subactive'] = 'Subscription active. Next charge on {$a}.';
$string['subamount'] = 'Amount';
$string['subcancel'] = 'Cancel';
$string['subcancelfailed'] = 'Could not cancel: it may be cancelled already.';
$string['subcancelled'] = 'Subscription cancelled. Access stays until the end of the paid period.';
$string['subcancelnote'] = 'After cancelling nothing more is charged; access stays until the end of the paid period.';
$string['subfails'] = 'Failures';
$string['subnext'] = 'Next charge';
$string['subpaiduntil'] = 'paid until {$a}';
$string['subproduct'] = 'Product';
$string['subscriptions'] = 'Subscriptions';
$string['substatus'] = 'Status';
$string['substatus_active'] = 'Active';
$string['substatus_cancelled'] = 'Cancelled';
$string['substatus_failed'] = 'Stopped: charge failed';
$string['summary'] = 'Short description';
$string['task_charge'] = 'Storefront subscription charges';
$string['thankyou'] = 'Thank you! Payment received.';
