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
$string['signupintro'] = 'Enter your name and e-mail and we will enrol you in the course. Already studied with us? Use the same address and the enrolment is added to your account. No password needed: we e-mail you a login link.';
$string['firstnamelabel'] = 'First name';
$string['lastnamelabel'] = 'Last name (optional)';
$string['emaillabel'] = 'E-mail';
$string['submit'] = 'Sign up';
$string['emailtoolong'] = 'The address is too long.';

$string['notavailable'] = 'Signup for this course is not open at the moment.';
$string['throttled'] = 'Too many attempts from this address or device. If you have already submitted the form, the e-mail with your login link should be in your mailbox — check the Spam folder too. Please try again a bit later.';
$string['existstext'] = 'An account with this address already exists. Log in and the enrolment will be added automatically.';
$string['loginbutton'] = 'Log in';
$string['magicbutton'] = 'Log in by e-mail link';

$string['thanksheading'] = 'You are enrolled!';
$string['thankstext'] = 'You are enrolled in "{$a}". We have sent a login link to your e-mail — open it to start learning. No e-mail? Check the Spam folder.';
$string['thankstextloggedin'] = 'You are enrolled in "{$a}" and already logged in — you can start right away.';
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
$string['defaultthankyou'] = 'Default thank-you page';
$string['defaultthankyou_desc'] = 'Where to go after a signup when neither the campaign nor the course has its own page. An absolute https URL or a local path. Empty: the plugin\'s built-in page.';
$string['existingpolicy'] = 'When the e-mail is already taken';
$string['existingpolicy_desc'] = '"Enrol right away": the existing account is enrolled and e-mailed a login link (no auto-login). "Ask to log in": a "you already have an account" page with login buttons is shown; after logging in the enrolment is added automatically.';
$string['policyenrol'] = 'Enrol right away';
$string['policylogin'] = 'Ask to log in';
$string['ratelimit'] = 'Signups per address';
$string['ratelimit_desc'] = 'How many signups are accepted per e-mail address within the window below. Per IP address the cap is ten times higher. Extra attempts are asked to wait.';
$string['ratewindow'] = 'Rate limit window (minutes)';
$string['ratewindow_desc'] = 'Over how many minutes the limit above is counted.';
$string['retentiondays'] = 'Log retention (days)';
$string['retentiondays_desc'] = 'After how many days the cleanup task deletes signup log rows. 0 keeps them forever.';

$string['taskcleanup'] = 'Course signup log cleanup';

$string['privacy:metadata:log'] = 'Signup log: who signed up, for which course, when, from which IP address and campaign. The entered address is stored only as a hash.';
$string['privacy:metadata:log:courseid'] = 'The course signed up for.';
$string['privacy:metadata:log:userid'] = 'The user that was created or matched by the address.';
$string['privacy:metadata:log:emailhash'] = 'Hash of the entered e-mail address, for rate limiting.';
$string['privacy:metadata:log:newuser'] = 'Whether this signup created the account.';
$string['privacy:metadata:log:campaign'] = 'The ad campaign slug from the link.';
$string['privacy:metadata:log:status'] = 'Outcome of the signup.';
$string['privacy:metadata:log:ip'] = 'IP address the signup was sent from.';
$string['privacy:metadata:log:timecreated'] = 'When the signup was sent.';
$string['privacy:path'] = 'Course signups';
