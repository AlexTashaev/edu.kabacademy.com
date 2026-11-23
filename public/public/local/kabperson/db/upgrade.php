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
 * Апгрейд local_kabperson.
 *
 * @package    local_kabperson
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Шаги апгрейда.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_kabperson_upgrade($oldversion) {
    if ($oldversion < 2026112400) {
        // 0.3.1: ключ Dashboard App для анонимного view.php. Сайт без CLI — ключ заводит
        // сам апгрейд, чтобы не писать настройку руками; в Chatwoot его вписывают после.
        if (trim((string) get_config('local_kabperson', 'viewkey')) === '') {
            set_config('viewkey', random_string(40), 'local_kabperson');
        }
        upgrade_plugin_savepoint(true, 2026112400, 'local', 'kabperson');
    }
    return true;
}
