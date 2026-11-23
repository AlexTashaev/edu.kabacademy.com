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
 * Строки local_kabdarkmode (английский).
 *
 * @package    local_kabdarkmode
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['appearance'] = 'Appearance';
$string['auto_hint'] = 'dark from {$a->from} to {$a->to}';
$string['auto_never'] = 'dark hours are not set';
$string['mode_auto'] = 'Auto';
$string['mode_dark'] = 'Dark';
$string['mode_light'] = 'Light';
$string['pluginname'] = 'Dark mode';
$string['privacy:metadata'] = 'The plugin stores no personal data on the server: the chosen appearance is kept only in the browser of the device (cookie kabdm_mode).';
$string['setting_darkfrom'] = 'Auto: dark from';
$string['setting_darkfrom_desc'] = 'In Auto mode the dark appearance starts at this hour by the clock of the visitor\'s device.';
$string['setting_darkto'] = 'Auto: dark until';
$string['setting_darkto_desc'] = 'In Auto mode the light appearance returns at this hour. Equal to "dark from" — Auto always stays light.';
$string['setting_defaultmode'] = 'Default mode';
$string['setting_defaultmode_desc'] = 'Appearance for visitors who have not chosen one yet. The choice is remembered per device (browser).';
$string['setting_enabled'] = 'Enabled';
$string['setting_enabled_desc'] = 'Show the appearance switch in the header. Turned off — everyone gets the light appearance.';
$string['toggle_label'] = 'Appearance: {$a}';
