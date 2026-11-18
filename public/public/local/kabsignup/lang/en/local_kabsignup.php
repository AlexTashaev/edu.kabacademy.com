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
 * English strings.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Course signup';
$string['settingsintro'] = 'Public course signup page: an account is created from a name and an e-mail address, the person is enrolled and receives an e-mail. Link for landing pages: <code>/local/kabsignup/index.php?id=COURSEID&amp;camp=SLUG</code> plus the ad and quiz parameters. The look of the page and the thank-you address are set in <a href="{$a->campaigns}">campaigns</a>; the log and the export are on the <a href="{$a->requests}">signup requests</a> page.';

$string['signuptitle'] = 'Course signup';
$string['signupheading'] = 'Sign up for "{$a}"';
$string['signupintro'] = 'Enter your name, e-mail and phone and we will enrol you in the course. Already studied with us? Use the same address and the enrolment is added to your account. Your login details will come by e-mail.';
$string['firstnamelabel'] = 'First name';
$string['lastnamelabel'] = 'Last name (optional)';
$string['emaillabel'] = 'E-mail';
$string['phonelabel'] = 'Phone';
$string['submit'] = 'Sign up';
$string['emailtoolong'] = 'The address is too long.';
$string['invalidphone'] = 'Please check the number: a phone number with the country code is needed, e.g. +7 999 123-45-67.';

$string['notavailable'] = 'Signup for this course is not open at the moment.';
$string['throttled'] = 'Too many attempts from this address or device. If you have already submitted the form, the e-mail with your login link should be in your mailbox — check the Spam folder too. Please try again a bit later.';
$string['existstext'] = 'An account with this address already exists. Log in and the enrolment will be added automatically.';
$string['loginbutton'] = 'Log in';
$string['magicbutton'] = 'Log in by e-mail link';
$string['prereqintro'] = 'This course is a continuation: signup is open to those who studied in {$a}. Enter the e-mail you studied under.';
$string['noprereqheading'] = 'Signup for continuing students only';
$string['noprereqtext'] = 'Signup for "{$a->course}" is open only to those who studied in {$a->required}. We found no such enrolment for this address. If you studied with us, enter the e-mail you were enrolled under or log in. Do not remember the address? Contact support.';
$string['noprereqtextloggedin'] = 'Signup for "{$a->course}" is open only to those who studied in {$a->required}. Your account ({$a->email}) has no such enrolment. If you studied under another address, contact support and we will check.';
$string['tryagain'] = 'Use another address';

$string['thanksheading'] = 'You are enrolled!';
$string['thankstext'] = 'You are enrolled in "{$a}". We have sent a login link to your e-mail — open it to start learning. No e-mail? Check the Spam folder.';
$string['thankstextloggedin'] = 'You are enrolled in "{$a}" and already logged in — you can start right away. We have e-mailed you the login and password for next time.';
$string['alreadyheading'] = 'You are already enrolled';
$string['alreadytext'] = 'You are already enrolled in "{$a}" — no need to sign up again.';
$string['alreadytextguest'] = 'You are already enrolled in "{$a}" — no need to sign up again. We have sent a login link to your e-mail. No e-mail? Check the Spam folder.';
$string['thankstextgeneric'] = 'We have sent a login link to your e-mail — open it to start learning.';
$string['gotocourse'] = 'Go to the course';

$string['emailsubject'] = 'You are enrolled in "{$a->coursename}"';
$string['emailtext'] = 'Hello {$a->firstname},

You are enrolled in "{$a->coursename}" at {$a->sitename}.

To start learning, log in by this link:
{$a->link}

The link is valid for {$a->minutes} min and works once. If it has expired, request a new one on the login page: {$a->requesturl}

If you did not sign up for the course, just ignore this e-mail.';
$string['emailhtml'] = '<p>Hello {$a->firstname},</p>
<p>You are enrolled in "{$a->coursename}" at {$a->sitename}.</p>
<p>To start learning, log in by this link:<br><a href="{$a->link}">{$a->link}</a></p>
<p>The link is valid for {$a->minutes} min and works once.<br>If it has expired, request a new one on the <a href="{$a->requesturl}">login page</a>.</p>
<p>If you did not sign up for the course, just ignore this e-mail.</p>';
$string['emailnewtext'] = 'Hello {$a->firstname},

You are enrolled in "{$a->coursename}" at {$a->sitename}. You are already logged in — you can start right away:
{$a->courseurl}

Your login details for next time:
Username: {$a->username}
Password: {$a->password}
Login page: {$a->loginurl}

Keep this e-mail. You can change the password in your profile. If you forget it, you can log in by an e-mail link, no password needed: {$a->requesturl}

If you did not sign up for the course, just ignore this e-mail.';
$string['emailnewhtml'] = '<p>Hello {$a->firstname},</p>
<p>You are enrolled in "{$a->coursename}" at {$a->sitename}. You are already logged in — <a href="{$a->courseurl}">you can start right away</a>.</p>
<p>Your login details for next time:<br>
Username: <b>{$a->username}</b><br>
Password: <b>{$a->password}</b><br>
Login page: <a href="{$a->loginurl}">{$a->loginurl}</a></p>
<p>Keep this e-mail. You can change the password in your profile. If you forget it, you can log in <a href="{$a->requesturl}">by an e-mail link</a>, no password needed.</p>
<p>If you did not sign up for the course, just ignore this e-mail.</p>';
$string['emailtextnolink'] = 'Hello {$a->firstname},

You are enrolled in "{$a->coursename}" at {$a->sitename}.

To start learning, open the login page:
{$a->loginurl}

If you do not remember your password, use the "Forgot password?" link on the login page.

If you did not sign up for the course, just ignore this e-mail.';
$string['emailhtmlnolink'] = '<p>Hello {$a->firstname},</p>
<p>You are enrolled in "{$a->coursename}" at {$a->sitename}.</p>
<p>To start learning, open the <a href="{$a->loginurl}">login page</a>. If you do not remember your password, use the "Forgot password?" link there.</p>
<p>If you did not sign up for the course, just ignore this e-mail.</p>';

$string['courses'] = 'Courses open for signup';
$string['courses_desc'] = 'One line per course: the course ID, optionally followed by | and the thank-you page URL for that course. Signup works only for the listed courses. Example: <code>236|https://kabacademy.com/thankyou-autumn/</code>';
$string['campaigns'] = 'Signup campaigns';
$string['prerequisites'] = 'Signup conditions';
$string['prerequisites_desc'] = 'Courses that can be signed up for only after another course (part 2 only for part-1 students). One line per course: the course ID, then | and the required course IDs separated by commas; an enrolment (active or suspended) in any of them is enough. Example: <code>240|236</code> or <code>240|236,210</code> (part 1 of two streams). No account is created for somebody who does not meet the condition. Part-1 students who should keep the right to part 2 must be suspended, not unenrolled: unenrolling deletes the enrolment.';
$string['defaultthankyou'] = 'Default thank-you page';
$string['defaultthankyou_desc'] = 'Where to go after a signup when neither the campaign nor the course has its own page. An absolute https URL or a local path. Empty: the plugin\'s built-in page.';
$string['existingpolicy'] = 'When the e-mail is already taken';
$string['existingpolicy_desc'] = '"Enrol right away": the existing account is enrolled and e-mailed a login link (no auto-login). "Ask to log in": a "you already have an account" page with login buttons is shown; after logging in the enrolment is added automatically.';
$string['policyenrol'] = 'Enrol right away';
$string['policylogin'] = 'Ask to log in';
$string['phonerequired'] = 'Phone is required';
$string['phonerequired_desc'] = 'The form is not sent without a phone. The phone goes to the "Phone" profile field (phone1) of a new account and is used in the duplicate check.';
$string['ipinfotoken'] = 'ipinfo.io token';
$string['ipinfotoken_desc'] = 'For the city and country of a new account by IP, as on WordPress. The token can be taken from the ipinfo pool in the WP theme options. Without a token the requests are anonymous, with a small ipinfo quota. When the site has a GeoLite2 file (geoip2file), it is used first.';
$string['dupalertto'] = 'Duplicate account alerts';
$string['dupalertto_desc'] = 'When a new account looks like an existing one (same e-mail apart from dots, a domain typo, the same phone), an e-mail with links to both profiles goes here. "One account" rule: only an admin merges, by hand. Empty: no alerts.';
$string['ratelimit'] = 'Signups per address';
$string['ratelimit_desc'] = 'How many signups are accepted per e-mail address within the window below. Per IP address the cap is ten times higher. Extra attempts are asked to wait.';
$string['ratewindow'] = 'Rate limit window (minutes)';
$string['ratewindow_desc'] = 'Over how many minutes the limit above is counted.';
$string['retentiondays'] = 'Log retention (days)';
$string['retentiondays_desc'] = 'After how many days the cleanup task deletes signup log rows. 0 keeps them forever.';

$string['taskcleanup'] = 'Course signup log cleanup';

$string['dupalertsubject'] = '[KAB] Possible duplicate account: {$a}';
$string['dupalertintro'] = 'A course signup created a new account that looks like an existing one.';
$string['dupalertcourse'] = 'Course: {$a}';
$string['dupalertnew'] = 'New account:';
$string['dupalertmatches'] = 'Similar accounts:';
$string['dupalertfooter'] = '"One account" rule: duplicates are merged by hand only, after a check.
Sent automatically (Moodle, local_kabsignup).';
$string['dupsignal_email'] = 'same e-mail apart from dots';
$string['dupsignal_typo'] = 'looks like a typo in the e-mail domain';
$string['dupsignal_local'] = 'same mailbox name on another domain (weak signal)';
$string['dupsignal_phone'] = 'same phone';

$string['privacy:metadata:log'] = 'Signup log: who signed up, for which course, when, from which IP address and campaign. The entered address is stored only as a hash.';
$string['privacy:metadata:log:courseid'] = 'The course signed up for.';
$string['privacy:metadata:log:userid'] = 'The user that was created or matched by the address.';
$string['privacy:metadata:log:emailhash'] = 'Hash of the entered e-mail address, for rate limiting.';
$string['privacy:metadata:log:newuser'] = 'Whether this signup created the account.';
$string['privacy:metadata:log:campaign'] = 'The ad campaign slug from the link.';
$string['privacy:metadata:log:status'] = 'Outcome of the signup.';
$string['privacy:metadata:log:flags'] = 'Results of the background processing: whether the city was found, whether similar accounts were found.';
$string['privacy:metadata:ipinfo'] = 'For a new account the IP address is sent to ipinfo.io to find the city and country.';
$string['privacy:metadata:ipinfo:ip'] = 'IP address the signup was sent from.';
$string['privacy:metadata:log:ip'] = 'IP address the signup was sent from.';
$string['privacy:metadata:log:timecreated'] = 'When the signup was sent.';
$string['privacy:path'] = 'Course signups';

$string['existsheading'] = 'You already have an account';
$string['consenttext'] = 'I have read and accept the {$a}';
$string['consentlink'] = 'terms and conditions';
$string['consentrequired'] = 'Please accept the terms.';
$string['undeliverable'] = 'Please check the address: e-mail cannot be delivered to it.';
$string['previewnotice'] = 'Preview: this is what a guest sees. Submitting the form here signs nobody up.';
$string['previewsubmitted'] = 'Preview: the form passed validation, but nothing was submitted.';

$string['termsurl'] = 'Terms page';
$string['termsurl_desc'] = 'Where the link in the required "I accept the terms" box leads. Empty — the storefront offer (local_kabshop) when installed; "-" — no box.';
$string['personas'] = 'Personas';
$string['personas_desc'] = 'Allowed values of the <code>pres</code> parameter, comma-separated (the persona of the ad). A listed persona is written into the profile field below — it opens that persona\'s lounge in the course — and replaces <code>{pres}</code> in the campaign thank-you address. Empty — personas are not used.';
$string['personafield'] = 'Profile field for the persona';
$string['personafield_desc'] = 'Short name of the custom profile field the persona goes to (default <code>kab_persona</code>). Overwritten on existing accounts too, as WordPress did. No such field — nothing is written.';
$string['capiheading'] = 'Facebook registration event (Conversions API)';
$string['capiheading_desc'] = 'After a first enrolment the server sends CompleteRegistration with the same event_id the thank-you page receives (<code>eid</code>), so Meta merges the browser and the server event. A repeat signup sends nothing. Switched on per campaign.';
$string['capipixel'] = 'Pixel ID';
$string['capipixel_desc'] = 'The pixel the campaigns optimise for. Empty — no event.';
$string['capitoken'] = 'Conversions API token';
$string['capitoken_desc'] = 'Access token from Events Manager.';
$string['capiversion'] = 'Graph API version';
$string['capiversion_desc'] = 'For example <code>v23.0</code>. Change it when Meta deprecates the version.';
$string['capitestcode'] = 'Test event code';
$string['capitestcode_desc'] = 'For testing only: events with the code show in Events Manager → Test Events and do not count. Clear it afterwards.';
$string['clearoutheading'] = 'Address check (Clearout)';
$string['clearoutheading_desc'] = 'A new address (not yet in Moodle) is checked when the form is submitted. Only invalid and disposable addresses are refused; "unknown" and any failure of the service let the signup through.';
$string['clearouttoken'] = 'Clearout token';
$string['clearouttoken_desc'] = 'Clearout API token. Empty — no check.';
$string['clearouttimeout'] = 'Check timeout (seconds)';
$string['clearouttimeout_desc'] = 'How long to wait for Clearout; no answer — the address is accepted.';

$string['kabsignup:manage'] = 'Manage signup campaigns';
$string['kabsignup:viewreports'] = 'View and download signup requests';

$string['campaigns_intro'] = 'A campaign sets the look of the signup page (colours, logo, course card, button text) and the thank-you page. Link from a landing: the address in the "Landing link" column plus the ad and quiz parameters.';
$string['campaigns_none'] = 'No campaigns yet.';
$string['campaigns_paramsnote'] = 'Landings and the quiz append utm_*, utm_id, pres, click ids (fbclid, gclid, yclid…), quiz_score and qa — all of it goes to the signup log. The thank-you page gets camp, utm_*, pres and eid.';
$string['campaign_add'] = 'New campaign';
$string['campaign_edit'] = 'Campaign "{$a}"';
$string['campaign_deleted'] = 'Campaign deleted.';
$string['campaign_deleteconfirm'] = 'Delete the campaign "{$a}"? Links with this slug keep signing people up, but without the look and the thank-you page of the campaign. The log does not change.';
$string['campaign_hidden'] = 'off';
$string['campaign_preview'] = 'Preview';
$string['campaign_link'] = 'Landing link';
$string['campaign_general'] = 'Campaign';
$string['campaign_slug'] = 'Slug';
$string['campaign_slug_help'] = 'Value of the <code>camp</code> link parameter: latin letters, digits, hyphen and underscore, e.g. <code>spring27</code>. It goes to the signup log and to the SendGrid categories of the e-mails.';
$string['campaign_slug_invalid'] = 'Latin letters, digits, hyphen and underscore only.';
$string['campaign_slug_taken'] = 'This slug is taken.';
$string['campaign_name'] = 'Name';
$string['campaign_course'] = 'Course';
$string['campaign_course_help'] = 'The course a link without <code>id</code> signs up for. Listed are the courses of the "Courses open for signup" setting.';
$string['campaign_nocourse'] = '— from the link only —';
$string['campaign_visible'] = 'On';
$string['campaign_capi'] = 'Facebook registration event';
$string['campaign_capi_help'] = 'Send CompleteRegistration from the server for a first enrolment (needs the pixel and the token in the plugin settings).';
$string['campaign_thanks'] = 'Thank-you page';
$string['campaign_thankyouurl'] = 'Thank-you address';
$string['campaign_thankyouurl_help'] = 'Absolute https address or a local path. <code>{pres}</code> is replaced by the persona of the ad: <code>https://kabacademy.com/lp/spring27/thanks/{pres}/</code>. Empty — the course, default or built-in page.';
$string['campaign_thankyoufallback'] = 'Address without persona';
$string['campaign_thankyoufallback_help'] = 'Where to go when the address has <code>{pres}</code> but the link has no persona or an unlisted one.';
$string['campaign_badurl'] = 'An https://… address or a local /… path, please.';
$string['campaign_nopres'] = '{pres} is not filled in here.';
$string['campaign_page'] = 'Signup page';
$string['campaign_title'] = 'Heading';
$string['campaign_intro'] = 'Text above the form (HTML)';
$string['campaign_cardtitle'] = 'Card: course name';
$string['campaign_cardnote'] = 'Card: note';
$string['campaign_cardimage'] = 'Card: image address';
$string['campaign_pricelabel'] = 'Card: price';
$string['campaign_buttonlabel'] = 'Button text';
$string['campaign_belowbutton'] = 'Text below the button';
$string['campaign_theme'] = 'Look';
$string['campaign_theme_desc'] = 'Colours as #rrggbb. An empty field keeps the site\'s own look.';
$string['campaign_colour_bg'] = 'Page background';
$string['campaign_colour_card'] = 'Form card background';
$string['campaign_colour_ink'] = 'Text';
$string['campaign_colour_muted'] = 'Secondary text';
$string['campaign_colour_accent'] = 'Links and accent';
$string['campaign_colour_button'] = 'Button';
$string['campaign_colour_buttontext'] = 'Button text';
$string['campaign_fontheading'] = 'Heading font (font-family)';
$string['campaign_fontbody'] = 'Text font (font-family)';
$string['campaign_fontcss'] = 'Font stylesheet (CSS address)';
$string['campaign_fontcss_help'] = 'A stylesheet with @font-face, e.g. Google Fonts or a file on edu (<code>/kab/…</code>). Fonts from kabacademy.com will not do: browsers refuse fonts from another site without CORS permission.';
$string['campaign_logo'] = 'Logo (image address)';
$string['campaign_badcolour'] = 'A colour as #rrggbb or #rgb.';
$string['campaign_badfont'] = 'Font names separated by commas only.';

$string['requests'] = 'Signup requests';
$string['requests_intro'] = 'Signup log by campaign. The CSV export has the ad parameters, quiz score and answers and the persona; no e-mail, phone or IP.';
$string['requests_from'] = 'From';
$string['requests_to'] = 'to';
$string['requests_campaign'] = 'Campaign';
$string['requests_allcampaigns'] = 'all';
$string['requests_show'] = 'Show';
$string['requests_status'] = 'Status';
$string['requests_count'] = 'Requests';
$string['requests_newusers'] = 'New accounts';
$string['requests_none'] = 'No requests in this period.';
$string['requests_download'] = 'Download CSV';
$string['requests_statuses'] = 'Statuses: created — new account; enrolled — existing account enrolled; loggedin — signed up while logged in; already — enrolled before; exists — asked to log in; noprereq — not enrolled in part 1; badmail — address refused by the check.';

$string['privacy:metadata:log:attribution'] = 'Ad and quiz parameters of the link (utm, persona, click ids, quiz score and answers).';
$string['privacy:metadata:log:quizscore'] = 'Quiz score.';
$string['privacy:metadata:log:persona'] = 'Persona of the ad.';
$string['privacy:metadata:log:eventid'] = 'Random id of the pixel registration event.';
$string['privacy:metadata:log:productid'] = 'Storefront product when the request is a purchase.';
$string['privacy:metadata:facebook'] = 'A first enrolment through a campaign with the event on sends a registration event to Facebook (Conversions API).';
$string['privacy:metadata:facebook:emailhash'] = 'SHA-256 hash of the e-mail address.';
$string['privacy:metadata:facebook:phonehash'] = 'SHA-256 hash of the phone number.';
$string['privacy:metadata:facebook:ip'] = 'IP address of the request.';
$string['privacy:metadata:facebook:useragent'] = 'Browser user agent.';
$string['privacy:metadata:facebook:fbclid'] = 'Ad click id.';
$string['privacy:metadata:clearout'] = 'A new e-mail address is checked by the Clearout service.';
$string['privacy:metadata:clearout:email'] = 'E-mail address.';
