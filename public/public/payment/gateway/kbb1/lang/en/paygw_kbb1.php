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
 * Strings for paygw_kbb1.
 *
 * @package    paygw_kbb1
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['apipath'] = 'API path';
$string['apipath_help'] = 'Path before /emv and /paypal. WordPress uses /woocommerce.';
$string['apitoken'] = 'API token';
$string['apitoken_help'] = 'Issued by the payment service; it decides the organization and the reference prefix. Moodle needs its own token, not the WordPress one.';
$string['apiurl'] = 'Service URL';
$string['apiurl_help'] = 'external_payments base URL without a trailing slash, e.g. https://checkout.kbb1.com';
$string['choosemethod'] = 'Choose a payment method';
$string['gatewaydescription'] = 'Credit card or PayPal';
$string['gatewayerror'] = 'The payment service rejected the payment: {$a}';
$string['gatewayname'] = 'Card or PayPal';
$string['installments'] = 'Installments';
$string['installments_help'] = 'Format amount;payments, e.g. 500;5: above the amount allow up to N payments (max 10). Empty means a single payment.';
$string['installments_invalid'] = 'Format: amount;payments, e.g. 500;5';
$string['invalidreturn'] = 'This payment return link is not valid. If you were charged, contact support.';
$string['method_emv'] = 'Credit card';
$string['method_paypal'] = 'PayPal';
$string['nomethod'] = 'No payment method is enabled.';
$string['paymentcancelled'] = 'Payment cancelled.';
$string['paymentfailed'] = 'Payment failed. Try again or choose another method.';
$string['paymentsuccessful'] = 'Payment successful. Thank you!';
$string['pluginname'] = 'Card / PayPal (organization service)';
$string['pluginname_desc'] = 'Credit card (Pelecard EMV) and PayPal via the organization payment service external_payments — the same one WordPress uses. Credentials are set per payment account.';
$string['privacy:metadata'] = 'Stores payment records and sends buyer details to the payment service.';
$string['privacy:metadata:external'] = 'Data sent to the external_payments service.';
$string['privacy:metadata:external:country'] = 'Buyer country.';
$string['privacy:metadata:external:email'] = 'Buyer email.';
$string['privacy:metadata:external:fullname'] = 'Buyer name.';
$string['privacy:metadata:external:phone'] = 'Buyer phone.';
$string['privacy:metadata:paygw_kbb1'] = 'Payments made through the payment service.';
$string['privacy:metadata:paygw_kbb1:amount'] = 'Amount.';
$string['privacy:metadata:paygw_kbb1:token'] = 'Card or PayPal token for recurring charges.';
$string['privacy:metadata:paygw_kbb1:txnid'] = 'Transaction ID.';
$string['privacy:metadata:paygw_kbb1:userid'] = 'Buyer.';
$string['sku'] = 'Default SKU';
$string['sku_help'] = 'Priority code sent to the service. WordPress takes it from the product; here it is one per account.';
$string['unsupportedcurrency'] = 'Currency {$a} is not supported: ILS, USD, EUR only.';
