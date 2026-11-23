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
 * Настройки «Тёмной темы».
 *
 * @package    local_kabdarkmode
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_kabdarkmode', get_string('pluginname', 'local_kabdarkmode'));

    $settings->add(new admin_setting_configcheckbox(
        'local_kabdarkmode/enabled',
        get_string('setting_enabled', 'local_kabdarkmode'),
        get_string('setting_enabled_desc', 'local_kabdarkmode'),
        1
    ));

    $modes = [];
    foreach (\local_kabdarkmode\ui::MODES as $mode) {
        $modes[$mode] = get_string('mode_' . $mode, 'local_kabdarkmode');
    }
    $settings->add(new admin_setting_configselect(
        'local_kabdarkmode/defaultmode',
        get_string('setting_defaultmode', 'local_kabdarkmode'),
        get_string('setting_defaultmode_desc', 'local_kabdarkmode'),
        'light',
        $modes
    ));

    $hours = [];
    for ($h = 0; $h < 24; $h++) {
        $hours[$h] = sprintf('%d:00', $h);
    }
    $settings->add(new admin_setting_configselect(
        'local_kabdarkmode/darkfrom',
        get_string('setting_darkfrom', 'local_kabdarkmode'),
        get_string('setting_darkfrom_desc', 'local_kabdarkmode'),
        20,
        $hours
    ));
    $settings->add(new admin_setting_configselect(
        'local_kabdarkmode/darkto',
        get_string('setting_darkto', 'local_kabdarkmode'),
        get_string('setting_darkto_desc', 'local_kabdarkmode'),
        7,
        $hours
    ));

    $ADMIN->add('localplugins', $settings);
}
