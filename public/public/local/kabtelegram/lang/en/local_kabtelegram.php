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
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['botheading'] = 'Bot';
$string['botusername'] = 'Bot username';
$string['botusername_desc'] = 'The SendPulse Telegram bot, without @. Empty — the bot link leads to the home page and /kab/tgbot.php keeps sending people to WordPress.';
$string['cachedef_sptoken'] = 'SendPulse API access token';
$string['channelbottoken'] = 'Bot token for the member count';
$string['channelbottoken_desc'] = 'Any bot of the BotFather; for a public channel it need not be an admin. Empty — the member count is not taken.';
$string['channelchat'] = 'Channel for the member count';
$string['channelchat_desc'] = '@name of the channel (or its numeric id). Counted once an hour.';
$string['channelheading'] = 'Course channel';
$string['channelurl'] = 'Channel link';
$string['channelurl_desc'] = 'Where go.php?to=channel leads (https://t.me/… or an invite link).';
$string['col_bot_click'] = 'Went to the bot';
$string['col_bot_linked'] = 'Linked the bot';
$string['col_channel_click'] = 'Went to the channel';
$string['col_lesson_click'] = 'Went to the lesson';
$string['col_people'] = 'Enrolled';
$string['defaultlesson'] = 'Default lesson';
$string['defaultlesson_desc'] = 'Address on this site for go.php?to=lesson without url= (old links from WordPress order e-mails). Empty — "My courses".';
$string['errorsync'] = 'Could not write the variables into SendPulse contact {$a}';
$string['extravars'] = 'Extra variables';
$string['extravars_desc'] = 'One per line: <code>sendpulse_variable = key</code>. Keys: userid, firstname, lastname, email, phone, country, city, course, courseid, orderid, quizscore, persona, campaign. Always sent: kab_first_name, kab_last_name, kab_email, kab_phone, kab_country, kab_city, kab_order_id, kab_course_name. The variable must exist in the bot.';
$string['flowid'] = 'SendPulse flow ID';
$string['flowid_desc'] = 'The flow started by the deep link tg.pulse.is/&lt;bot&gt;?start=&lt;flow&gt;&amp;token=&lt;token&gt;. Empty — logged-in people get a plain t.me link without data.';
$string['otherheading'] = 'Lesson and statistics';
$string['pluginname'] = 'Telegram funnel';
$string['privacy:metadata:contact'] = 'Who started the bot from our link: the SendPulse contact and the Telegram id.';
$string['privacy:metadata:contact:contactid'] = 'SendPulse contact id';
$string['privacy:metadata:contact:telegramid'] = 'Telegram user id';
$string['privacy:metadata:contact:timecreated'] = 'When the bot was started';
$string['privacy:metadata:contact:timesynced'] = 'When the data was last written into the contact';
$string['privacy:metadata:contact:userid'] = 'The user';
$string['privacy:metadata:event'] = 'Clicks to the bot, the channel and the lesson.';
$string['privacy:metadata:event:event'] = 'What was clicked';
$string['privacy:metadata:event:place'] = 'Where the link was';
$string['privacy:metadata:event:timecreated'] = 'When';
$string['privacy:metadata:event:userid'] = 'The user';
$string['privacy:metadata:sendpulse'] = 'When the bot is started from our link, the data goes into the SendPulse contact.';
$string['privacy:metadata:sendpulse:city'] = 'City';
$string['privacy:metadata:sendpulse:country'] = 'Country';
$string['privacy:metadata:sendpulse:course'] = 'Course';
$string['privacy:metadata:sendpulse:email'] = 'E-mail';
$string['privacy:metadata:sendpulse:firstname'] = 'First name';
$string['privacy:metadata:sendpulse:lastname'] = 'Last name';
$string['privacy:metadata:sendpulse:phone'] = 'Phone';
$string['privacy:metadata:sendpulse:quizscore'] = 'Quiz score from the signup link';
$string['privacy:metadata:token'] = 'The token of the bot link.';
$string['privacy:metadata:token:timecreated'] = 'When it was created';
$string['privacy:metadata:token:timeexpires'] = 'When it expires';
$string['privacy:metadata:token:token'] = 'The token';
$string['privacy:metadata:token:userid'] = 'The user';
$string['retentiondays'] = 'Keep statistics, days';
$string['retentiondays_desc'] = 'Older clicks are deleted every night. 0 — keep forever.';
$string['settingsintro'] = '<p>The funnel of the old WordPress site (kab-telegram-enrollment) inside Moodle.</p>
<p>Button links: bot <code>{$a->bot}</code>, channel <code>{$a->channel}</code>, lesson <code>{$a->lesson}</code>.
The lounges link to <code>/kab/tgbot.php</code>, which comes here once the bot is set.</p>
<p>Webhook for SendPulse (events new_subscriber and incoming_message): <code>{$a->webhook}</code></p>';
$string['spclientid'] = 'SendPulse API ID';
$string['spclientid_desc'] = 'SendPulse → Account settings → API.';
$string['spclientsecret'] = 'SendPulse API Secret';
$string['spclientsecret_desc'] = '';
$string['spheading'] = 'SendPulse API and webhook';
$string['statscohort'] = 'Enrolled in "{$a->course}" since {$a->since}';
$string['statscourse'] = 'Course id';
$string['statsdaily'] = 'By day, last {$a} days';
$string['statsday'] = 'Day';
$string['statsdays'] = 'Days';
$string['statsheading'] = 'Telegram funnel — statistics';
$string['statsnone'] = 'No clicks in this period yet.';
$string['statsnote'] = 'session — logged-in person, anonymous — not logged in (no data reaches the bot). channel_members — the last member count of the day. History before the move — "wp kab-te-stats" on the old site.';
$string['statsshow'] = 'Show';
$string['statssince'] = 'Enrolled since';
$string['taskchannelcount'] = 'Telegram funnel: channel member count';
$string['taskcleanup'] = 'Telegram funnel: cleanup';
$string['tasksynccontact'] = 'Telegram funnel: write data into a SendPulse contact';
$string['tokendays'] = 'Token lifetime, days';
$string['tokendays_desc'] = 'How long the bot link of a person stays valid after the last click (WordPress: 7).';
$string['webhooknosecret'] = '(no secret — the webhook answers 403; set one below)';
$string['webhooksecret'] = 'Webhook secret';
$string['webhooksecret_desc'] = 'Part of the webhook address (?key=) or the X-Webhook-Secret header. Created on installation. Changed — change the address in SendPulse too.';
