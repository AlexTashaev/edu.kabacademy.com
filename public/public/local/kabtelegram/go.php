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
 * Funnel links — what the WordPress buttons did, without WordPress:
 *
 *   go.php?to=bot[&src=…]      the SendPulse bot; a logged-in person gets a token,
 *                              and the webhook fills their contact variables;
 *   go.php?to=channel[&src=…]  the course channel (the click is counted);
 *   go.php?to=lesson[&url=<address on this site>][&pres=<persona>][&src=…]
 *                              into a lesson after login, with the persona label
 *                              (old kab_te_sso / kab_te_lesson_go links via the WP router).
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_kabtelegram\manager;
use local_kabtelegram\stats;
use local_kabtelegram\token;

$to = optional_param('to', 'bot', PARAM_ALPHA);
if (!in_array($to, ['bot', 'channel', 'lesson'], true)) {
    $to = 'bot';
}
$place = manager::clean_place(optional_param('src', '', PARAM_RAW_TRIMMED));
$config = manager::config();
$loggedin = isloggedin() && !isguestuser();

/**
 * Leave with a 302 that nobody caches: a bot link carries a personal token.
 *
 * @param string $url
 */
function local_kabtelegram_go(string $url): void {
    \core\session\manager::write_close();
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Referrer-Policy: no-referrer');
    header('Location: ' . $url, true, 302);
    exit;
}

$home = (new moodle_url('/'))->out(false);

if ($to === 'lesson') {
    $PAGE->set_url(new moodle_url('/local/kabtelegram/go.php', ['to' => 'lesson']));
    $PAGE->set_context(context_system::instance());
    if (!$loggedin) {
        // After login (password, link by e-mail, Telegram) Moodle brings the person back here.
        $SESSION->wantsurl = $FULLME;
        redirect(get_login_url());
    }
    $url = trim(optional_param('url', '', PARAM_RAW_TRIMMED));
    // Only this site: "/path" or "<wwwroot>/path". "//host" and backslashes would leave it in some browsers.
    $local = (strpos($url, '/') === 0 && strpos($url, '//') !== 0) || strpos($url, $CFG->wwwroot . '/') === 0;
    if (!$local || strpos($url, '\\') !== false || clean_param($url, PARAM_LOCALURL) === '') {
        $url = $config->defaultlesson !== '' ? $config->defaultlesson : '/my/';
    }
    manager::save_persona((int)$USER->id, optional_param('pres', '', PARAM_RAW_TRIMMED));
    stats::track('lesson_click', 'session', $place, (int)$USER->id);
    local_kabtelegram_go((new moodle_url($url))->out(false));
}

if ($to === 'channel') {
    stats::track('channel_click', $loggedin ? 'session' : 'anonymous', $place, $loggedin ? (int)$USER->id : 0);
    local_kabtelegram_go($config->channelurl !== '' ? $config->channelurl : $home);
}

// The bot.
$tokenstr = '';
if ($loggedin && $config->botusername !== '' && $config->flowid !== '') {
    $tokenstr = token::issue((int)$USER->id);
}
stats::track('bot_click', $loggedin ? 'session' : 'anonymous', $place, $loggedin ? (int)$USER->id : 0);
$link = token::bot_link($config, $tokenstr);
local_kabtelegram_go($link !== '' ? $link : $home);
