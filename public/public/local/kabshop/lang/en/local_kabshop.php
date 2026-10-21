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
$string['accessforever'] = 'open, no end date';
$string['accessnone'] = 'not enrolled';
$string['accessuntil'] = 'open until {$a}';
$string['accountrequired'] = 'Choose a payment account for a paid product.';
$string['addcoupon'] = 'Add coupon';
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
$string['chargenow'] = 'Charge now';
$string['chargenow_confirm'] = 'Yes, charge {$a} to the buyer\'s saved card now';
$string['chargenow_failed'] = 'Charge failed: {$a}';
$string['chargenow_ok'] = 'Charged. Subscription extended, the buyer got an email.';
$string['chargenow_unavailable'] = 'This subscription cannot be charged from here: it is not active or the gateway charges itself.';
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
$string['coupon_alreadyused'] = 'You have already used this code.';
$string['coupon_expired'] = 'This code has expired or is disabled.';
$string['coupon_freedone'] = 'Coupon applied, order placed. Details are in the email.';
$string['coupon_freenote'] = 'Coupon {$a}: 100% off';
$string['coupon_notforproduct'] = 'This code does not apply to this product.';
$string['coupon_notfound'] = 'No such code.';
$string['coupon_usedup'] = 'This code is no longer valid: usage limit reached.';
$string['couponapplied'] = 'Code {$a->code}: {$a->off} off (full price {$a->was}).';
$string['couponapply'] = 'Apply';
$string['couponcode'] = 'Coupon code';
$string['couponcode_help'] = 'Letters, digits, "-" and "_", no spaces. Case does not matter.';
$string['couponcode_invalid'] = 'Letters, digits, "-" and "_" only.';
$string['couponcode_taken'] = 'This code already exists.';
$string['couponfrom'] = 'Valid from';
$string['couponkind'] = 'Discount';
$string['couponkind_fixed'] = 'Fixed amount';
$string['couponkind_percent'] = 'Percent';
$string['couponlabel'] = 'Have a coupon code?';
$string['couponmaxuses'] = 'Usage limit';
$string['couponmaxuses_help'] = 'Total number of uses. 0 means no limit.';
$string['couponnolimit'] = 'no time limit';
$string['couponnote'] = 'Note';
$string['couponperuser'] = 'Once per person';
$string['couponperuser_short'] = 'once per person';
$string['couponproducts'] = 'Products';
$string['couponproducts_help'] = 'Empty means all paid products. Not for amount-of-choice products or external links. For subscriptions the discount applies to the first payment only.';
$string['couponremove'] = 'Remove coupon';
$string['coupons'] = 'Storefront coupons';
$string['couponto'] = 'Valid until';
$string['couponto_invalid'] = 'The end must be after the start.';
$string['couponuses'] = 'Used';
$string['couponvalidity'] = 'Validity';
$string['couponvalue'] = 'Discount size';
$string['couponvalue_invalid'] = 'Above 0; a percent cannot exceed 100.';
$string['courseaccess'] = 'Course access';
$string['courseid'] = 'Moodle course ID';
$string['courseid_help'] = 'Course the buyer is enrolled into. 0 — no course (donation, service).';
$string['coursenotfound'] = 'Course not found';
$string['customamount'] = 'Amount of choice';
$string['customamount_help'] = 'The buyer chooses the amount. The price field is the minimum.';
$string['datefrom'] = 'From';
$string['dateto'] = 'To';
$string['description'] = 'Description';
$string['editcoupon'] = 'Edit coupon';
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
$string['grant'] = 'Grant manually';
$string['grant_help'] = 'Give someone a product without paying on the site: bank transfer, gift, fixing an error. Creates an order noting who granted it and why.';
$string['granted'] = 'Granted. Order #{$a}.';
$string['grantedby'] = 'Granted by';
$string['grantemail'] = 'Buyer email';
$string['grantemail_help'] = 'An account with this email must already exist.';
$string['grantmail'] = 'Send the buyer an "access granted" email';
$string['grantmanyusers'] = 'Several accounts use this email.';
$string['grantnote'] = 'Reason';
$string['grantnote_help'] = 'For example: "bank transfer 12.10", "gift", "payment error, ticket 123". Shown on the order.';
$string['grantnouser'] = 'No account with this email.';
$string['grantsubmit'] = 'Grant';
$string['granttimeend'] = 'Access until';
$string['granttimeend_help'] = 'Off means the product period or no end date.';
$string['granttimeend_past'] = 'This date has passed.';
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
$string['mail_granted_body'] = 'You now have access to "{$a->product}".';
$string['mail_granted_subject'] = 'Access granted: {$a->product}';
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
$string['mailresend'] = 'Resend email';
$string['mailresent'] = 'Email sent to {$a}.';
$string['manage'] = 'Storefront';
$string['managesub'] = 'Manage subscription';
$string['more'] = 'Details';
$string['myorders'] = 'My orders';
$string['mysubscriptions'] = 'My subscriptions';
$string['noattempts'] = 'No unfinished payments.';
$string['nocoupons'] = 'No coupons yet.';
$string['noorders'] = 'No orders.';
$string['nopayment'] = 'Payment is not set up for this product yet.';
$string['noproducts'] = 'No products in this section.';
$string['nosubscriptions'] = 'No recurring subscriptions.';
$string['orderno'] = 'Order #{$a}';
$string['orderrevoked'] = 'Access for this order was revoked on {$a}.';
$string['orders'] = 'Storefront orders';
$string['orderstotal'] = 'Orders: {$a->count}, paid: {$a->sum}';
$string['ordertype_coupon'] = '100% coupon';
$string['ordertype_free'] = 'Free';
$string['ordertype_manual'] = 'Manual';
$string['ordertype_paid'] = 'Payment';
$string['ordertype_renewal'] = 'Automatic payment';
$string['ordertype_revoked'] = 'revoked';
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
$string['revoke'] = 'Revoke access';
$string['revoked'] = 'Access revoked: unenrolled from the course, subscription for this product stopped.';
$string['revokednote'] = 'Revoked by {$a->who}: {$a->note}';
$string['revokefailed'] = 'Access is already revoked.';
$string['revokenote'] = 'Reason (e.g. refund via accounting)';
$string['searchhint'] = 'Order number, name or email';
$string['section'] = 'Section';
$string['sortorder'] = 'Order';
$string['subactive'] = 'Subscription active. Next charge on {$a}.';
$string['subamount'] = 'Amount';
$string['subcancel'] = 'Cancel';
$string['subcancel_admin'] = 'Stop auto-renewal';
$string['subcancelfailed'] = 'Could not cancel: it may be cancelled already.';
$string['subcancelled'] = 'Subscription cancelled. Access stays until the end of the paid period.';
$string['subcancelnote'] = 'After cancelling nothing more is charged; access stays until the end of the paid period.';
$string['subdue'] = 'due, will be charged on the next task run (hourly at :17)';
$string['subedit'] = 'Adjust subscription';
$string['subedit_help'] = 'Next payment date is when the task charges next. Course access moves to that date + 3 days. The amount applies to all following charges.';
$string['subfails'] = 'Failures';
$string['subgatewayown'] = 'the gateway charges itself';
$string['subhistory'] = 'Subscription payments';
$string['subnext'] = 'Next charge';
$string['subnext_help'] = 'The task charges hourly at :17, starting from this time.';
$string['subno'] = 'Subscription #{$a}';
$string['subpaiduntil'] = 'paid until {$a}';
$string['subproduct'] = 'Product';
$string['subresetfails'] = 'Reset failure counter';
$string['subscriptions'] = 'Subscriptions';
$string['substatus'] = 'Status';
$string['substatus_active'] = 'Active';
$string['substatus_cancelled'] = 'Cancelled';
$string['substatus_failed'] = 'Stopped: charge failed';
$string['summary'] = 'Short description';
$string['task_charge'] = 'Storefront subscription charges';
$string['thankyou'] = 'Thank you! Payment received.';
