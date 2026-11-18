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
$string['settingsintro'] = 'Public course signup page: an account is created from a name and an e-mail address, the person is enrolled and receives a login link by e-mail. Link for landing pages: <code>/local/kabsignup/index.php?id=COURSEID&amp;camp=SLUG</code> (the campaign slug is optional). UTM parameters are passed through to the thank-you page.';

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
$string['campaigns'] = 'Campaign thank-you pages';
$string['campaigns_desc'] = 'One line per campaign: slug, then | and the thank-you page URL. The slug is passed in the signup link as <code>camp</code> and takes precedence over the course page. Example: <code>autumn26|https://kabacademy.com/ty-autumn26/</code>';
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
