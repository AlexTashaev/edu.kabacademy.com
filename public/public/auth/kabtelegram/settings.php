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
 * @package    auth_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'auth_kabtelegram/pluginname',
        '',
        new lang_string('auth_kabtelegramdescription', 'auth_kabtelegram', \auth_kabtelegram\manager::origin())
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'auth_kabtelegram/bottoken',
        new lang_string('bottoken', 'auth_kabtelegram'),
        new lang_string('bottoken_desc', 'auth_kabtelegram'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'auth_kabtelegram/maxage',
        new lang_string('maxage', 'auth_kabtelegram'),
        new lang_string('maxage_desc', 'auth_kabtelegram'),
        60,
        PARAM_INT,
        5
    ));

    $authoptions = [];
    foreach (array_keys(core_component::get_plugin_list('auth')) as $authname) {
        if ($authname === 'nologin' || $authname === 'kabtelegram') {
            continue;
        }
        $authoptions[$authname] = get_string_manager()->string_exists('pluginname', 'auth_' . $authname)
            ? get_string('pluginname', 'auth_' . $authname)
            : $authname;
    }
    $settings->add(new admin_setting_configmultiselect(
        'auth_kabtelegram/allowedauths',
        new lang_string('allowedauths', 'auth_kabtelegram'),
        new lang_string('allowedauths_desc', 'auth_kabtelegram'),
        ['manual', 'email'],
        $authoptions
    ));
}
