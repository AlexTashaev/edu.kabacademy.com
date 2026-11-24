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
 * @package    auth_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Sign in with Telegram';
$string['auth_kabtelegramdescription'] = 'Adds a "Sign in with Telegram" button (Telegram Login) to the login page. Only existing accounts with a linked Telegram can sign in this way: the plugin creates no accounts and never changes passwords or users\' authentication methods. Users link Telegram themselves in Preferences → User account → Sign in with Telegram. An unknown Telegram is offered to sign in by e-mail link or password and link it.<br><br>Needed: the login bot token (below) and the bot\'s domain in BotFather (<code>/setdomain</code>) set to <code>{$a}</code>. No button is shown on the login page until the token is set.';
$string['loginbutton'] = 'Sign in with Telegram';
$string['logintitle'] = 'Sign in with Telegram';
$string['loggingin'] = 'Signing you in… If nothing happens, press the button.';
$string['notfinished'] = 'Telegram sign-in was not finished: Telegram returned no confirmation. Try again or sign in by e-mail link.';
$string['needjs'] = 'Signing in with Telegram needs JavaScript enabled in the browser.';
$string['backtologin'] = 'Back to login page';
$string['backtolink'] = 'Back';
$string['stateinvalid'] = 'Telegram sign-in failed: the page is outdated or was opened in another browser. Start again from the login page.';
$string['resultinvalid'] = 'Telegram did not confirm the sign-in, or the confirmation is too old. Try again.';
$string['loginrefused'] = 'This account cannot be signed in with Telegram right now. Sign in by e-mail link or password.';
$string['notenabled'] = 'Sign in with Telegram is not enabled on this site.';
$string['linktitle'] = 'Sign in with Telegram';
$string['linkdone'] = 'Telegram {$a} is linked. You can now sign in with Telegram.';
$string['linktaken'] = 'This Telegram is already linked to another account on this site. Sign in to that account or contact support.';
$string['unlinkdone'] = 'Telegram is unlinked. It can no longer be used to sign in.';
$string['notlinkedtitle'] = 'Telegram is not linked yet';
$string['notlinkedintro'] = 'Telegram {$a} is not linked to any account on this site. Sign in the usual way, by e-mail link or password, and you will be offered to link this Telegram. After that you can sign in with Telegram too.';
$string['notlinkedemail'] = 'Sign in by e-mail link';
$string['notlinkedpassword'] = 'Sign in with password';
$string['confirmlink'] = 'Link Telegram {$a->telegram} to the account {$a->account}? This Telegram will then sign in to the account.';
$string['confirmreplace'] = 'Another Telegram is linked to the account now, {$a}. It will be unlinked.';
$string['confirmlinkyes'] = 'Link';
$string['confirmlinkno'] = 'Do not link';
$string['linkedstatus'] = 'Telegram {$a->telegram} is linked to your account (since {$a->since}). You can sign in with it using the "Sign in with Telegram" button.';
$string['lastlogin'] = 'Last sign-in with Telegram: {$a}.';
$string['lastloginnever'] = 'Never signed in with Telegram yet.';
$string['unlink'] = 'Unlink Telegram';
$string['relink'] = 'Link another Telegram';
$string['notlinkedstatus'] = 'Telegram is not linked. Link it to sign in with the "Sign in with Telegram" button, without a password or an e-mail.';
$string['linkbutton'] = 'Link Telegram';
$string['bottoken'] = 'Bot token';
$string['bottoken_desc'] = 'The login bot token from BotFather, like <code>123456789:AA…</code>. It checks the signature of Telegram\'s answers; the number before the colon is the bot id.';
$string['maxage'] = 'Telegram answer lifetime (minutes)';
$string['maxage_desc'] = 'A Telegram answer older than this is refused. At most one day (1440).';
$string['allowedauths'] = 'Who may sign in with Telegram';
$string['allowedauths_desc'] = 'Only accounts with one of these authentication methods sign in with Telegram. Suspended, unconfirmed and deleted accounts never do.';
$string['privacy:metadata:links'] = 'Telegram linked to the account: which Telegram, when it was linked and when it was last used to sign in.';
$string['privacy:metadata:links:userid'] = 'The account on this site.';
$string['privacy:metadata:links:tgid'] = 'Telegram user id.';
$string['privacy:metadata:links:tgusername'] = 'Telegram username.';
$string['privacy:metadata:links:tgname'] = 'First and last name from Telegram.';
$string['privacy:metadata:links:source'] = 'Where the link comes from: moved from the old site or made by the user.';
$string['privacy:metadata:links:timecreated'] = 'When Telegram was linked.';
$string['privacy:metadata:links:timelastlogin'] = 'When Telegram was last used to sign in.';
$string['privacy:metadata:telegram'] = 'To sign in, the user\'s browser opens a Telegram page (oauth.telegram.org) where the user confirms the sign-in.';
$string['privacy:metadata:telegram:origin'] = 'Address of this site, where Telegram returns the confirmation.';
$string['privacy:path'] = 'Sign in with Telegram';
