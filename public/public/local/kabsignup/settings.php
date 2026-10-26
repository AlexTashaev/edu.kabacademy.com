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
 * Admin settings.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_kabsignup', new lang_string('pluginname', 'local_kabsignup'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_heading(
        'local_kabsignup/intro',
        '',
        new lang_string('settingsintro', 'local_kabsignup')
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_kabsignup/courses',
        new lang_string('courses', 'local_kabsignup'),
        new lang_string('courses_desc', 'local_kabsignup'),
        '',
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_kabsignup/campaigns',
        new lang_string('campaigns', 'local_kabsignup'),
        new lang_string('campaigns_desc', 'local_kabsignup'),
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
}
