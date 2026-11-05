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
 * Настройки local_kabperson: гейт Chatwoot, iframe, источники.
 *
 * @package    local_kabperson
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_kabperson', get_string('pluginname', 'local_kabperson'));
    $ADMIN->add('localplugins', $settings);

    $p = 'local_kabperson';

    $settings->add(new admin_setting_heading($p . '/gateheading',
        get_string('settings_gate', $p), get_string('settings_gate_desc', $p)));
    $settings->add(new admin_setting_configtext($p . '/chatwootbase',
        get_string('chatwootbase', $p), get_string('chatwootbase_desc', $p), '', PARAM_URL));
    $settings->add(new admin_setting_configtext($p . '/chatwootaccount',
        get_string('chatwootaccount', $p), get_string('chatwootaccount_desc', $p), '', PARAM_INT));
    $settings->add(new admin_setting_configpasswordunmask($p . '/chatwoottoken',
        get_string('chatwoottoken', $p), get_string('chatwoottoken_desc', $p), ''));
    $settings->add(new admin_setting_configtext($p . '/frameancestors',
        get_string('frameancestors', $p), get_string('frameancestors_desc', $p), '', PARAM_RAW_TRIMMED));

    $settings->add(new admin_setting_heading($p . '/sourcesheading',
        get_string('settings_sources', $p), get_string('settings_sources_desc', $p)));
    $settings->add(new admin_setting_configcheckbox($p . '/enablekabshop',
        get_string('enablekabshop', $p), get_string('enablekabshop_desc', $p), 1));

    $settings->add(new admin_setting_heading($p . '/wpheading',
        get_string('settings_wp', $p), get_string('settings_wp_desc', $p)));
    $settings->add(new admin_setting_configcheckbox($p . '/enablewp',
        get_string('enablewp', $p), get_string('enablewp_desc', $p), 1));
    $settings->add(new admin_setting_configtext($p . '/wpbase',
        get_string('wpbase', $p), get_string('wpbase_desc', $p), '', PARAM_URL));
    $settings->add(new admin_setting_configpasswordunmask($p . '/wptoken',
        get_string('wptoken', $p), get_string('wptoken_desc', $p), ''));
}
