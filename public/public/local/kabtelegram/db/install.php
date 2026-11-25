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
 * Installation of local_kabtelegram.
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The webhook secret is created here: without it the webhook answers 403, and
 * the full webhook address with the secret is shown on the settings page.
 *
 * @return bool
 */
function xmldb_local_kabtelegram_install() {
    if (trim((string) get_config('local_kabtelegram', 'webhooksecret')) === '') {
        set_config('webhooksecret', random_string(32), 'local_kabtelegram');
    }
    return true;
}
