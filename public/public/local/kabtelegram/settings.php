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
 * Settings and the statistics page.
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('localplugins', new admin_externalpage(
    'local_kabtelegram_stats',
    new lang_string('statsheading', 'local_kabtelegram'),
    new moodle_url('/local/kabtelegram/stats.php'),
    'local/kabtelegram:viewstats'
));

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_kabtelegram', new lang_string('pluginname', 'local_kabtelegram'));
    $ADMIN->add('localplugins', $settings);

    if ($ADMIN->fulltree) {
        $webhook = \local_kabtelegram\manager::webhook_url();
        $go = (new moodle_url('/local/kabtelegram/go.php'))->out(false);
        $links = (object)[
            'bot' => s($go . '?to=bot&src=thanks'),
            'channel' => s($go . '?to=channel&src=thanks'),
            'lesson' => s($go . '?to=lesson&url=/mod/lesson/view.php?id=…&pres=edik&src=thanks'),
            'webhook' => $webhook !== '' ? s($webhook) : get_string('webhooknosecret', 'local_kabtelegram'),
        ];
        $settings->add(new admin_setting_heading('local_kabtelegram/intro', '',
            get_string('settingsintro', 'local_kabtelegram', $links)));

        // Bot.
        $settings->add(new admin_setting_heading('local_kabtelegram/botheading',
            new lang_string('botheading', 'local_kabtelegram'), ''));
        $settings->add(new admin_setting_configtext('local_kabtelegram/botusername',
            new lang_string('botusername', 'local_kabtelegram'),
            new lang_string('botusername_desc', 'local_kabtelegram'), '', PARAM_TEXT));
        $settings->add(new admin_setting_configtext('local_kabtelegram/flowid',
            new lang_string('flowid', 'local_kabtelegram'),
            new lang_string('flowid_desc', 'local_kabtelegram'), '', PARAM_TEXT));
        $settings->add(new admin_setting_configtext('local_kabtelegram/tokendays',
            new lang_string('tokendays', 'local_kabtelegram'),
            new lang_string('tokendays_desc', 'local_kabtelegram'), '7', PARAM_INT));

        // SendPulse API and webhook.
        $settings->add(new admin_setting_heading('local_kabtelegram/spheading',
            new lang_string('spheading', 'local_kabtelegram'), ''));
        $settings->add(new admin_setting_configtext('local_kabtelegram/spclientid',
            new lang_string('spclientid', 'local_kabtelegram'),
            new lang_string('spclientid_desc', 'local_kabtelegram'), '', PARAM_TEXT));
        $settings->add(new admin_setting_configpasswordunmask('local_kabtelegram/spclientsecret',
            new lang_string('spclientsecret', 'local_kabtelegram'),
            new lang_string('spclientsecret_desc', 'local_kabtelegram'), ''));
        $settings->add(new admin_setting_configpasswordunmask('local_kabtelegram/webhooksecret',
            new lang_string('webhooksecret', 'local_kabtelegram'),
            new lang_string('webhooksecret_desc', 'local_kabtelegram'), ''));
        $settings->add(new admin_setting_configtextarea('local_kabtelegram/extravars',
            new lang_string('extravars', 'local_kabtelegram'),
            new lang_string('extravars_desc', 'local_kabtelegram'),
            \local_kabtelegram\manager::DEFAULT_EXTRAVARS, PARAM_RAW, 60, 3));

        // Channel.
        $settings->add(new admin_setting_heading('local_kabtelegram/channelheading',
            new lang_string('channelheading', 'local_kabtelegram'), ''));
        $settings->add(new admin_setting_configtext('local_kabtelegram/channelurl',
            new lang_string('channelurl', 'local_kabtelegram'),
            new lang_string('channelurl_desc', 'local_kabtelegram'), '', PARAM_URL));
        $settings->add(new admin_setting_configtext('local_kabtelegram/channelchat',
            new lang_string('channelchat', 'local_kabtelegram'),
            new lang_string('channelchat_desc', 'local_kabtelegram'), '', PARAM_TEXT));
        $settings->add(new admin_setting_configpasswordunmask('local_kabtelegram/channelbottoken',
            new lang_string('channelbottoken', 'local_kabtelegram'),
            new lang_string('channelbottoken_desc', 'local_kabtelegram'), ''));

        // Lesson and statistics.
        $settings->add(new admin_setting_heading('local_kabtelegram/otherheading',
            new lang_string('otherheading', 'local_kabtelegram'), ''));
        $settings->add(new admin_setting_configtext('local_kabtelegram/defaultlesson',
            new lang_string('defaultlesson', 'local_kabtelegram'),
            new lang_string('defaultlesson_desc', 'local_kabtelegram'), '', PARAM_RAW_TRIMMED));
        $settings->add(new admin_setting_configtext('local_kabtelegram/retentiondays',
            new lang_string('retentiondays', 'local_kabtelegram'),
            new lang_string('retentiondays_desc', 'local_kabtelegram'), '400', PARAM_INT));
    }
}
