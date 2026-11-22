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
 * Ad and quiz parameters a signup link carries.
 *
 * The keys are those the landings and the quiz pass on (repo kabacademy.com,
 * landings/…/src/carry.ts PASS_PARAMS: 15 ad keys + oppref + 3 legacy video-quiz
 * keys) plus quiz_score and qa that the quiz adds itself. They ride along the
 * signup page (form action), land in the log and partly on the thank-you page.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attribution {

    /** @var string[] Every key accepted from the link, in the order of the CSV export. */
    const KEYS = [
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'utm_id', 'pres',
        'fbclid', 'gclid', 'gad_source', 'ttclid', 'yclid', 'ymclid', '_openstat', 'msclkid', 'oppref',
        'quiz_vid', 'quiz_level', 'quiz_count', 'quiz_score', 'qa',
    ];

    /** @var string[] Keys passed on to the thank-you page (click ids and quiz answers stay in the log). */
    const THANKYOU_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'pres'];

    /**
     * The parameters of the current request (GET or POST), cleaned; empty ones dropped.
     *
     * @return array name => value
     */
    public static function from_request(): array {
        $out = [];
        foreach (self::KEYS as $name) {
            $value = self::clean($name, (string)optional_param($name, '', PARAM_RAW));
            if ($value !== '') {
                $out[$name] = $value;
            }
        }
        return $out;
    }

    /**
     * One value, cleaned for its key.
     *
     * @param string $name
     * @param string $value
     * @return string '' when nothing usable is left
     */
    public static function clean(string $name, string $value): string {
        $value = trim(clean_param($value, PARAM_NOTAGS));
        switch ($name) {
            case 'quiz_score':
                return preg_match('/^\d{1,4}$/', $value) ? $value : '';
            case 'qa':
                // Answer indexes, comma-separated; an unanswered question is an empty item.
                return \core_text::substr(preg_replace('/[^0-9,]/', '', $value), 0, 100);
            case 'pres':
                return \core_text::substr(\core_text::strtolower(clean_param($value, PARAM_ALPHANUMEXT)), 0, 32);
            default:
                return \core_text::substr($value, 0, 255);
        }
    }

    /**
     * The part of the parameters that goes to the thank-you page.
     *
     * @param array $attr from from_request()
     * @return array
     */
    public static function for_thankyou(array $attr): array {
        return array_intersect_key($attr, array_flip(self::THANKYOU_KEYS));
    }

    /**
     * Quiz score as a number, or null.
     *
     * @param array $attr
     * @return int|null
     */
    public static function quiz_score(array $attr): ?int {
        return isset($attr['quiz_score']) ? (int)$attr['quiz_score'] : null;
    }

    /**
     * The fbc value for the Conversions API, built from the fbclid of the link
     * (the _fbc cookie lives on the landing's domain, edu cannot see it).
     *
     * @param array $attr
     * @return string '' without fbclid
     */
    public static function fbc(array $attr): string {
        if (empty($attr['fbclid'])) {
            return '';
        }
        return 'fb.1.' . round(microtime(true) * 1000) . '.' . $attr['fbclid'];
    }
}
