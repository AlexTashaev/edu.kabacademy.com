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
 * Пункты «Оплаты WordPress» в администрировании.
 *
 * @package    local_kabwporders
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$syscontext = context_system::instance();
if ($hassiteconfig || has_capability('local/kabwporders:view', $syscontext)) {
    $ADMIN->add('courses', new admin_externalpage(
        'local_kabwporders_index',
        get_string('registry', 'local_kabwporders'),
        new moodle_url('/local/kabwporders/index.php'),
        'local/kabwporders:view'
    ));
}
if ($hassiteconfig || has_capability('local/kabwporders:import', $syscontext)) {
    $ADMIN->add('courses', new admin_externalpage(
        'local_kabwporders_import',
        get_string('import', 'local_kabwporders'),
        new moodle_url('/local/kabwporders/import.php'),
        'local/kabwporders:import'
    ));
}

if ($hassiteconfig) {
    $page = new admin_settingpage('local_kabwporders_settings', get_string('pluginname', 'local_kabwporders'));
    $page->add(new admin_setting_configcheckbox(
        'local_kabwporders/usermenu',
        get_string('setting_usermenu', 'local_kabwporders'),
        get_string('setting_usermenu_desc', 'local_kabwporders'),
        1
    ));
    $ADMIN->add('localplugins', $page);
}
