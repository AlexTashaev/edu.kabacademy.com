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
 * @package    auth_kabmagic
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Email link login';
$string['auth_kabmagicdescription'] = 'Adds a "Log in with an email link" button to the login page. The user enters the e-mail address of an existing account and receives a one-time link that signs them in without a password. The plugin never checks or changes passwords and is never assigned to users as their authentication method: it only signs in existing accounts whose authentication method is listed below.';
$string['loginbutton'] = 'Log in with an email link';
$string['requesttitle'] = 'Log in with an email link';
$string['requestintro'] = 'Enter the e-mail address your account is registered with. We will send you a link that signs you in, no password needed.';
$string['sendlink'] = 'Send me a link';
$string['sent'] = 'If an account with this address exists, an e-mail with a login link has been sent. Check your inbox and the spam folder: the link works once and only for a short time.';
$string['backtologin'] = 'Back to the login page';
$string['logintitle'] = 'Signing in';
$string['loggingin'] = 'Signing you in. If nothing happens, press the button.';
$string['loginconfirm'] = 'Log in';
$string['linkinvalid'] = 'This login link is invalid, has already been used or has expired. Request a new one.';
$string['requestnew'] = 'Request a new link';
$string['notenabled'] = 'Login by e-mail link is not enabled on this site.';
$string['lifetime'] = 'Link lifetime (minutes)';
$string['lifetime_desc'] = 'How long a login link stays valid after it is sent. Each link works once.';
$string['ratelimit'] = 'Requests per address';
$string['ratelimit_desc'] = 'How many links may be requested for one e-mail address within the window below. Further requests are silently ignored. Per IP address the cap is ten times this number.';
$string['ratewindow'] = 'Rate limit window (minutes)';
$string['ratewindow_desc'] = 'The window the limit above is counted over.';
$string['allowedauths'] = 'Accounts that may log in by link';
$string['allowedauths_desc'] = 'Only users whose authentication method is in this list receive links. Other accounts (and suspended, unconfirmed or deleted ones) are ignored; the form always reports the same result.';
$string['taskcleanup'] = 'Delete expired login links';
$string['emailsubject'] = 'Sign in to {$a->sitename}';
$string['emailtext'] = 'Hello {$a->firstname},

To sign in to {$a->sitename}, open this link:
{$a->link}

The link is valid for {$a->minutes} minutes and works once.
If you did not request it, just ignore this message.';
$string['emailhtml'] = '<p>Hello {$a->firstname},</p>
<p>To sign in to {$a->sitename}, open this link:<br><a href="{$a->link}">{$a->link}</a></p>
<p>The link is valid for {$a->minutes} minutes and works once.<br>If you did not request it, just ignore this message.</p>';
$string['privacy:metadata:tokens'] = 'Login link requests: who asked for a link, when, from which IP address, and when the link was used. The link itself is stored only as a hash.';
$string['privacy:metadata:tokens:userid'] = 'The user the link was issued to.';
$string['privacy:metadata:tokens:emailhash'] = 'A hash of the e-mail address entered, used for rate limiting.';
$string['privacy:metadata:tokens:wantsurl'] = 'The page to open after signing in.';
$string['privacy:metadata:tokens:timecreated'] = 'When the link was requested.';
$string['privacy:metadata:tokens:timeused'] = 'When the link was used.';
$string['privacy:metadata:tokens:ip'] = 'The IP address the link was requested from.';
$string['privacy:path'] = 'Login links';
