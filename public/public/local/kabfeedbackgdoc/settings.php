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
 * @package    local_kabfeedbackgdoc
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_kabfeedbackgdoc', get_string('pluginname', 'local_kabfeedbackgdoc'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configcheckbox(
        'local_kabfeedbackgdoc/enabled',
        get_string('enabled', 'local_kabfeedbackgdoc'),
        get_string('enabled_desc', 'local_kabfeedbackgdoc'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_kabfeedbackgdoc/webhookurl',
        get_string('webhookurl', 'local_kabfeedbackgdoc'),
        get_string('webhookurl_desc', 'local_kabfeedbackgdoc'),
        '',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_kabfeedbackgdoc/secret',
        get_string('secret', 'local_kabfeedbackgdoc'),
        get_string('secret_desc', 'local_kabfeedbackgdoc'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_kabfeedbackgdoc/cmids',
        get_string('cmids', 'local_kabfeedbackgdoc'),
        get_string('cmids_desc', 'local_kabfeedbackgdoc'),
        '',
        PARAM_TEXT
    ));
}
