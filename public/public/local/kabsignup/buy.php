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
 * A newcomer on the storefront: "e-mail → account → the product page" (§5.1 of the landing
 * spec, 1.4.0).
 *
 * ?product=<id>[&cur=<currency>]&camp=<slug>&<ad parameters>. The product must be a visible
 * storefront product that is sold right now and has no signup condition (part 2 of a course
 * is for students of part 1 — they have accounts and log in). A logged-in visitor goes straight
 * to the product page. A guest fills in the same form as on the course signup page:
 *  - a free address → a new account (no enrolment yet), signed in, an e-mail with the login and
 *    the password → the product page, where the purchase, the donation or the free enrolment
 *    (with the storefront's own checks and its donation window) happen as for anybody logged in;
 *  - a taken address → the account is not touched; the standard login link goes to the mailbox
 *    and brings the person back to the product page.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_kabsignup\attribution;
use local_kabsignup\campaigns;
use local_kabsignup\manager;
use local_kabsignup\skin;

$config = manager::config();
$productid = optional_param('product', 0, PARAM_INT);
$currency = optional_param('cur', '', PARAM_ALPHA);
$campaign = core_text::strtolower(core_text::substr(optional_param('camp', '', PARAM_ALPHANUMEXT), 0, 64));
$campaignrec = campaigns::get_visible($campaign);
$attr = attribution::from_request();

$pageparams = ['product' => $productid];
if ($currency !== '') {
    $pageparams['cur'] = $currency;
}
if ($campaign !== '') {
    $pageparams['camp'] = $campaign;
}
$pageparams += $attr;
$pageurl = new moodle_url('/local/kabsignup/buy.php', $pageparams);

$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');
$PAGE->set_cacheable(false);
$PAGE->set_title(get_string('buytitle', 'local_kabsignup'));
$PAGE->set_heading(format_string($SITE->fullname));

$product = manager::shop_product($productid, $currency);
if ($product === null) {
    if (class_exists('\local_kabshop\catalog') && $productid > 0
            && \local_kabshop\catalog::get_product($productid, IGNORE_MISSING)) {
        // Exists but is not for newcomers right now (closed, part 2, hidden): the product page
        // explains why and offers to log in.
        redirect(new moodle_url('/local/kabshop/product.php', ['id' => $productid] + ($currency !== '' ? ['cur' => $currency] : [])));
    }
    skin::setup($PAGE, $campaignrec);
    echo $OUTPUT->header();
    echo skin::style($campaignrec);
    echo $OUTPUT->notification(get_string('buynotavailable', 'local_kabsignup'), \core\output\notification::NOTIFY_INFO);
    echo $OUTPUT->footer();
    exit;
}

$producturl = \local_kabshop\catalog::product_url($product);
if (isloggedin() && !isguestuser()) {
    redirect($producturl);
}

$syscontext = context_system::instance();
$productname = format_string($product->name, true, ['context' => $syscontext]);
$free = \local_kabshop\catalog::is_free($product);
$custom = !empty($product->customamount);
$button = get_string($free ? 'buybuttonfree' : ($custom ? 'buybuttondonate' : 'buybuttonpay'), 'local_kabsignup');

// Honeypot: bots fill the invisible field; people never see it.
if (data_submitted() && optional_param('kab_website', '', PARAM_RAW) !== '') {
    redirect($producturl);
}

$form = new \local_kabsignup\form\signup_form($pageurl, [
    'phonerequired' => $config->phonerequired,
    'termsurl' => $config->termsurl,
    'buttonlabel' => $campaignrec && (string)$campaignrec->buttonlabel !== '' ? (string)$campaignrec->buttonlabel : $button,
    'belowbutton' => $campaignrec ? (string)$campaignrec->belowbutton : '',
    'country' => skin::country(),
    'courseid' => (int)($product->courseid ?? 0),
    'campaign' => $campaign,
]);
skin::setup($PAGE, $campaignrec, true);

if ($data = $form->get_data()) {
    $result = manager::account_for_purchase($product, $data->firstname, (string)($data->lastname ?? ''),
        $data->email, (string)($data->phone ?? ''), $campaign, (string)getremoteaddr(), $attr);

    if ($result['status'] === 'created') {
        manager::autologin((int)$result['user']->id);
        redirect($producturl, get_string($free ? 'buycreatedfree' : 'buycreated', 'local_kabsignup'), null,
            \core\output\notification::NOTIFY_SUCCESS);
    }

    echo $OUTPUT->header();
    echo skin::style($campaignrec);
    if ($result['status'] === 'throttled') {
        echo $OUTPUT->notification(get_string('throttled', 'local_kabsignup'), \core\output\notification::NOTIFY_WARNING);
    } else {
        // Taken address: the same answer whatever happened to the link (its own rate limit and
        // eligibility rules), so the page does not tell who has an account.
        echo skin::heading($campaignrec, get_string('buyexistsheading', 'local_kabsignup'));
        echo $OUTPUT->box(get_string('buyexiststext', 'local_kabsignup', s($data->email)),
            'generalbox boxwidthnormal boxaligncenter');
    }
    echo html_writer::div(html_writer::link($producturl, get_string('buyback', 'local_kabsignup', $productname)),
        'text-center mt-3');
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
echo skin::style($campaignrec);
$price = \local_kabshop\catalog::price_string($product);
echo skin::intro($campaignrec, get_string('buyheading', 'local_kabsignup', $productname),
    get_string($free ? 'buyintrofree' : 'buyintro', 'local_kabsignup', $price));
$form->display();
// Already a student: log in and come back to the product.
$SESSION->wantsurl = $producturl->out(false);
$login = html_writer::link(new moodle_url('/login/index.php'), get_string('buyhaveaccount', 'local_kabsignup'));
echo html_writer::div($login, 'text-center mt-3 ks-have-account');
echo $OUTPUT->footer();
