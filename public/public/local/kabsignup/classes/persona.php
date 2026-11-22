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

namespace local_kabsignup;

defined('MOODLE_INTERNAL') || die();

/**
 * Persona of the ad (pres=…) → profile field kab_persona → which "lounge"
 * (гостиная) the student sees in the course: the lounges are restricted by
 * this field, as on WordPress (kab-telegram-enrollment wrote it there).
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class persona {

    /**
     * The persona of the link when it is on the allowed list, else ''.
     *
     * @param string $pres cleaned pres= value
     * @param \stdClass $config
     * @return string
     */
    public static function slug(string $pres, \stdClass $config): string {
        return ($pres !== '' && in_array($pres, $config->personas, true)) ? $pres : '';
    }

    /**
     * Write the persona into the profile field. Unlike everything else here this
     * also touches existing accounts and overwrites the old value, as WordPress
     * did: the field is not personal data but the choice of lounge, and a
     * returning student would otherwise get the lounge of last year's persona.
     * No such field — nothing happens.
     *
     * @param int $userid
     * @param string $persona checked slug
     * @param \stdClass $config
     * @return bool written
     */
    public static function save(int $userid, string $persona, \stdClass $config): bool {
        global $CFG, $DB;
        if ($userid <= 0 || $persona === '' || $config->personafield === '') {
            return false;
        }
        if (!$DB->record_exists('user_info_field', ['shortname' => $config->personafield])) {
            return false;
        }
        require_once($CFG->dirroot . '/user/profile/lib.php');
        try {
            profile_save_custom_fields($userid, [$config->personafield => $persona]);
            return true;
        } catch (\Throwable $e) {
            debugging('local_kabsignup: persona not saved: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }
}
