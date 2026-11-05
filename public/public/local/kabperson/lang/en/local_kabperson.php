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
 * EN strings for local_kabperson.
 *
 * @package    local_kabperson
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'KAB Person Card';
$string['kabperson:view'] = 'View person card (PII of any user)';
$string['eventcardviewed'] = 'Person card viewed';

// Settings.
$string['settings_gate'] = 'Operator page gate (view.php)';
$string['settings_gate_desc'] = 'view.php is opened by the Chatwoot Dashboard App without a Moodle login. It only serves emails that have a chat contact with a conversation (checked via the Chatwoot API below), rate-limited per IP. Not configured = the anonymous page is closed (fail-closed). Logged-in users with the local/kabperson:view capability bypass the gate.';
$string['chatwootbase'] = 'Chatwoot base URL';
$string['chatwootbase_desc'] = 'Public Chatwoot URL, e.g. https://support.edu.kabacademy.com. The token is sent as the api-access-token header (hyphenated — the edge passes it through).';
$string['chatwootaccount'] = 'Chatwoot account id';
$string['chatwootaccount_desc'] = 'Numeric account id from the Chatwoot URL.';
$string['chatwoottoken'] = 'Chatwoot API token';
$string['chatwoottoken_desc'] = 'Access token of an agent/bot with read access to contacts. Stored in Moodle config, not in git.';
$string['frameancestors'] = 'Allowed iframe parents (frame-ancestors)';
$string['frameancestors_desc'] = 'Space-separated origins allowed to embed view.php in an iframe (Chatwoot Dashboard App), e.g. https://support.edu.kabacademy.com. The page also accepts the contact via postMessage only from these origins. Empty = only Moodle itself may embed the page; postMessage is off.';
$string['settings_sources'] = 'Sources';
$string['settings_sources_desc'] = 'Moodle itself is always on. A source that fails or does not answer within 4 s is marked "error" in the card; the other blocks are still shown. kabbalah.info and books.kab.co.il are stage 5 and are "off" for now.';
$string['settings_wp'] = 'Old kabacademy WP (WooCommerce orders)';
$string['settings_wp_desc'] = 'The payments block is read from the site\'s /wp-json/kab/v1/person (mu-plugin kab-person-card.php 0.3+, blocks=payments mode). Needed while part 1 and current payments live in WooCommerce; after WP is closed the source switches to the order archive.';
$string['enablewp'] = 'Read kabacademy WP orders';
$string['enablewp_desc'] = 'Without a URL and token the source is off.';
$string['wpbase'] = 'WP URL';
$string['wpbase_desc'] = 'E.g. https://kabacademy.com (on dev-edu — the dev copy of the site).';
$string['wptoken'] = 'X-KAB-Token';
$string['wptoken_desc'] = 'Value of KAB_PERSON_CARD_TOKEN_EDU from the site\'s wp-config (the Moodle card\'s own token, separate from the bot\'s). Not in git.';
$string['enablekabshop'] = 'Read local_kabshop (academy payments)';
$string['enablekabshop_desc'] = 'Purchases, subscriptions and unfinished payments from the Moodle shop. Turn off if the shop schema changes and the block starts failing.';

// View page.
$string['viewtitle'] = 'Person card';
$string['emaillabel'] = 'Email';
$string['show'] = 'Show';
$string['refresh'] = 'Refresh';
$string['jsonlink'] = 'JSON';
$string['notfound'] = 'Not in the common base (no Moodle user with this email).';
$string['sec_identity'] = 'Person';
$string['sec_learning'] = 'Learning';
$string['sec_payments'] = 'Academy payments';
$string['col_course'] = 'Course';
$string['col_role'] = 'Role';
$string['col_enrolstatus'] = 'Enrolment';
$string['col_progress'] = 'Progress';
$string['col_lastaccess'] = 'Last access';
$string['col_badges'] = 'Badges';
$string['col_date'] = 'Date';
$string['col_product'] = 'Product';
$string['col_kind'] = 'Kind';
$string['col_amount'] = 'Amount';
$string['col_status'] = 'Status';
$string['col_gateway'] = 'Gateway';
$string['cohorts'] = 'Cohorts';
$string['sitebadges'] = 'Site badges';
$string['orders'] = 'Orders';
$string['subscriptions'] = 'Subscriptions';
$string['unfinished'] = 'Unfinished payments';
$string['donortotal'] = 'Donations total';
$string['nextcharge'] = 'Next charge';
$string['failcount'] = 'Failed attempts';
$string['lasterror'] = 'Last error';
$string['registered'] = 'Registered';
$string['lastlogin'] = 'Last login';
$string['telegram'] = 'Telegram linked';
$string['duplicates'] = 'Duplicate signals';
$string['statusactive'] = 'active';
$string['statussuspended'] = 'suspended';
$string['statusdeleted'] = 'deleted';
$string['nothing'] = '—';
$string['generatedat'] = 'Card built at';
$string['fromcache'] = 'from cache (up to 10 min old)';
$string['notinmoodle'] = 'not in Moodle';
$string['identitydetails'] = 'More about the person';
$string['sum_courses'] = 'Courses';
$string['sum_passed'] = 'completed';
$string['sum_teaching'] = 'teaches {$a}';
$string['sum_current'] = 'Studying now';
$string['sum_lastseen'] = 'Last on edu';
$string['sum_payments'] = 'Payments';
$string['sum_paid'] = 'paid';
$string['sum_pending'] = 'pending';
$string['sum_failed'] = 'failed';
$string['sum_since'] = 'With us since';
$string['sum_ids'] = 'WP / Moodle';
$string['donorbadge'] = 'donor: {$a}';
$string['morecourses'] = '{$a} more courses';
$string['moreorders'] = '{$a} more payments';
$string['failedorders'] = 'Failed / cancelled / refunded ({$a})';
$string['ago_today'] = 'today';
$string['ago_yesterday'] = 'yesterday';
$string['ago_days'] = '{$a} days ago';
$string['ago_months'] = '{$a} months ago';
$string['ago_years'] = '{$a} years ago';
$string['waitingcontact'] = 'Waiting for the contact from Chatwoot…';
$string['nocontactemail'] = 'The Chatwoot contact has no email (neither primary nor "other email from chat") — nothing to open the card by.';
$string['unverified'] = '⚠ The address was given in chat and is not confirmed by a site login. Do not discuss courses or payments — ask the person to log in on the site and write from there.';
$string['wponly'] = 'This person exists in the old kabacademy WP:';
$string['col_source'] = 'Source';
$string['src_kabshop'] = 'Moodle shop';
$string['src_kabacademy_wp'] = 'kabacademy WP';
$string['nopaid'] = 'No payments.';
$string['freerecords'] = 'Free enrolments ({$a})';
$string['refundednote'] = 'refunded {$a}';
$string['sig_same_name'] = 'same name in Moodle: id {$a}';
$string['sig_duplicate_email'] = 'same email on several Moodle accounts: id {$a}';
$string['sig_moodle_email_differs'] = 'the Moodle account has another email — found via the WP link';
$string['sig_wp_email_differs'] = 'the WP account has another email';
$string['sig_wp_linked_to_moodle'] = 'the WP account is linked to another Moodle account: id {$a}';
$string['sig_wp_no_moodle_link'] = 'the WP account is not linked to Moodle';
$string['sig_wp_order_email_mismatch'] = 'a WP order was paid with another email';

// Gate errors.
$string['err_notconfigured'] = 'Chatwoot access is not configured';
$string['err_ratelimited'] = 'Too many requests';
$string['err_bademail'] = 'A valid email is required';
$string['err_notacontact'] = 'No chat contact for this email';
$string['err_chatwoot'] = 'Cannot verify contact (Chatwoot unavailable)';

// Privacy.
$string['privacy:metadata'] = 'The KAB Person Card plugin stores no personal data of its own: it only reads Moodle, local_kabshop and the old kabacademy WP and keeps a short-lived cache.';
$string['privacy:metadata:chatwoot'] = 'To gate the operator page, the looked-up email is sent to the Chatwoot support system to check that this email belongs to a chat contact.';
$string['privacy:metadata:chatwoot:email'] = 'The email address being looked up.';
$string['privacy:metadata:kabacademy_wp'] = 'For the payments block, the looked-up email and the Moodle user id are sent to the old kabacademy.com site (WordPress) to find their WooCommerce orders.';
$string['privacy:metadata:kabacademy_wp:email'] = 'The email address being looked up.';
$string['privacy:metadata:kabacademy_wp:moodleuserid'] = 'The Moodle user id (lookup via the account link if the WP email differs).';

// Cache definitions.
$string['cachedef_person'] = 'Built person cards (10 minutes per email)';
$string['cachedef_gate'] = 'PII gate verdicts (email is a chat contact)';
$string['cachedef_ratelimit'] = 'Per-IP rate limit counters for view.php';
