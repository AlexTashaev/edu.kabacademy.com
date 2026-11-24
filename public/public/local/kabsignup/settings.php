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
 * Admin settings and the pages of the plugin in the admin tree.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('localplugins', new admin_externalpage(
    'local_kabsignup_campaigns',
    new lang_string('campaigns', 'local_kabsignup'),
    new moodle_url('/local/kabsignup/campaigns.php'),
    'local/kabsignup:manage'
));
$ADMIN->add('localplugins', new admin_externalpage(
    'local_kabsignup_requests',
    new lang_string('requests', 'local_kabsignup'),
    new moodle_url('/local/kabsignup/requests.php'),
    'local/kabsignup:viewreports'
));

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_kabsignup', new lang_string('pluginname', 'local_kabsignup'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_heading(
        'local_kabsignup/intro',
        '',
        new lang_string('settingsintro', 'local_kabsignup', (object)[
            'campaigns' => (new moodle_url('/local/kabsignup/campaigns.php'))->out(),
            'requests' => (new moodle_url('/local/kabsignup/requests.php'))->out(),
        ])
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_kabsignup/courses',
        new lang_string('courses', 'local_kabsignup'),
        new lang_string('courses_desc', 'local_kabsignup'),
        '',
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_kabsignup/prerequisites',
        new lang_string('prerequisites', 'local_kabsignup'),
        new lang_string('prerequisites_desc', 'local_kabsignup'),
        '',
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configtext(
        'local_kabsignup/defaultthankyou',
        new lang_string('defaultthankyou', 'local_kabsignup'),
        new lang_string('defaultthankyou_desc', 'local_kabsignup'),
        '',
        PARAM_RAW_TRIMMED
    ));

    $settings->add(new admin_setting_configselect(
        'local_kabsignup/existingpolicy',
        new lang_string('existingpolicy', 'local_kabsignup'),
        new lang_string('existingpolicy_desc', 'local_kabsignup'),
        'enrol',
        [
            'enrol' => new lang_string('policyenrol', 'local_kabsignup'),
            'login' => new lang_string('policylogin', 'local_kabsignup'),
        ]
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_kabsignup/phonerequired',
        new lang_string('phonerequired', 'local_kabsignup'),
        new lang_string('phonerequired_desc', 'local_kabsignup'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_kabsignup/termsurl',
        new lang_string('termsurl', 'local_kabsignup'),
        new lang_string('termsurl_desc', 'local_kabsignup'),
        '',
        PARAM_RAW_TRIMMED
    ));

    $settings->add(new admin_setting_configtext(
        'local_kabsignup/personas',
        new lang_string('personas', 'local_kabsignup'),
        new lang_string('personas_desc', 'local_kabsignup'),
        \local_kabsignup\manager::DEFAULT_PERSONAS,
        PARAM_RAW_TRIMMED
    ));

    $settings->add(new admin_setting_configtext(
        'local_kabsignup/personafield',
        new lang_string('personafield', 'local_kabsignup'),
        new lang_string('personafield_desc', 'local_kabsignup'),
        'kab_persona',
        PARAM_ALPHANUMEXT
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_kabsignup/ipinfotoken',
        new lang_string('ipinfotoken', 'local_kabsignup'),
        new lang_string('ipinfotoken_desc', 'local_kabsignup'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_kabsignup/dupalertto',
        new lang_string('dupalertto', 'local_kabsignup'),
        new lang_string('dupalertto_desc', 'local_kabsignup'),
        \local_kabsignup\dupcheck::DEFAULT_TO,
        PARAM_RAW_TRIMMED
    ));

    $settings->add(new admin_setting_configtext(
        'local_kabsignup/ratelimit',
        new lang_string('ratelimit', 'local_kabsignup'),
        new lang_string('ratelimit_desc', 'local_kabsignup'),
        5,
        PARAM_INT,
        5
    ));

    $settings->add(new admin_setting_configtext(
        'local_kabsignup/ratewindow',
        new lang_string('ratewindow', 'local_kabsignup'),
        new lang_string('ratewindow_desc', 'local_kabsignup'),
        60,
        PARAM_INT,
        5
    ));

    $settings->add(new admin_setting_configtext(
        'local_kabsignup/retentiondays',
        new lang_string('retentiondays', 'local_kabsignup'),
        new lang_string('retentiondays_desc', 'local_kabsignup'),
        365,
        PARAM_INT,
        5
    ));

    $settings->add(new admin_setting_heading(
        'local_kabsignup/capiheading',
        new lang_string('capiheading', 'local_kabsignup'),
        new lang_string('capiheading_desc', 'local_kabsignup')
    ));

    $settings->add(new admin_setting_configtext(
        'local_kabsignup/capipixel',
        new lang_string('capipixel', 'local_kabsignup'),
        new lang_string('capipixel_desc', 'local_kabsignup'),
        '',
        PARAM_ALPHANUM
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_kabsignup/capitoken',
        new lang_string('capitoken', 'local_kabsignup'),
        new lang_string('capitoken_desc', 'local_kabsignup'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_kabsignup/capiversion',
        new lang_string('capiversion', 'local_kabsignup'),
        new lang_string('capiversion_desc', 'local_kabsignup'),
        'v23.0',
        PARAM_RAW_TRIMMED
    ));

    $settings->add(new admin_setting_configtext(
        'local_kabsignup/capitestcode',
        new lang_string('capitestcode', 'local_kabsignup'),
        new lang_string('capitestcode_desc', 'local_kabsignup'),
        '',
        PARAM_ALPHANUMEXT
    ));

    $settings->add(new admin_setting_heading(
        'local_kabsignup/clearoutheading',
        new lang_string('clearoutheading', 'local_kabsignup'),
        new lang_string('clearoutheading_desc', 'local_kabsignup')
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_kabsignup/clearouttoken',
        new lang_string('clearouttoken', 'local_kabsignup'),
        new lang_string('clearouttoken_desc', 'local_kabsignup'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_kabsignup/clearouttimeout',
        new lang_string('clearouttimeout', 'local_kabsignup'),
        new lang_string('clearouttimeout_desc', 'local_kabsignup'),
        4,
        PARAM_INT,
        5
    ));
}
