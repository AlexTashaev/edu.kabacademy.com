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
 * Ad campaigns (table local_kabsignup_campaign): the look of the signup page
 * and the thank-you address of each camp= slug.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class campaigns {

    /** @var string[] Colour keys of the theme JSON, in form order. */
    const COLOURS = ['bg', 'card', 'ink', 'muted', 'accent', 'button', 'buttontext'];

    /** @var string[] Text keys of the theme JSON. */
    const THEMETEXT = ['fontheading', 'fontbody', 'fontcss', 'logo'];

    /**
     * A visible campaign by slug.
     *
     * @param string $slug
     * @return \stdClass|null
     */
    public static function get_visible(string $slug): ?\stdClass {
        global $DB;
        if ($slug === '') {
            return null;
        }
        $record = $DB->get_record('local_kabsignup_campaign', ['slug' => $slug, 'visible' => 1]);
        return $record ?: null;
    }

    /**
     * Any campaign by id.
     *
     * @param int $id
     * @return \stdClass|null
     */
    public static function get(int $id): ?\stdClass {
        global $DB;
        $record = $DB->get_record('local_kabsignup_campaign', ['id' => $id]);
        return $record ?: null;
    }

    /**
     * All campaigns, newest first.
     *
     * @return \stdClass[]
     */
    public static function all(): array {
        global $DB;
        return $DB->get_records('local_kabsignup_campaign', null, 'timemodified DESC, id DESC');
    }

    /**
     * The theme of a campaign, cleaned: only valid colours, font names and https URLs survive.
     *
     * @param \stdClass|null $campaign
     * @return array key => value (only keys that are set)
     */
    public static function theme(?\stdClass $campaign): array {
        if (!$campaign || empty($campaign->theme)) {
            return [];
        }
        $raw = json_decode((string)$campaign->theme, true);
        return is_array($raw) ? self::clean_theme($raw) : [];
    }

    /**
     * Clean theme values from the form or the database.
     *
     * @param array $raw
     * @return array
     */
    public static function clean_theme(array $raw): array {
        $out = [];
        foreach (self::COLOURS as $key) {
            $value = trim((string)($raw[$key] ?? ''));
            if (self::is_colour($value)) {
                $out[$key] = strtolower($value);
            }
        }
        foreach (['fontheading', 'fontbody'] as $key) {
            $value = trim((string)($raw[$key] ?? ''));
            if (self::is_font($value)) {
                $out[$key] = $value;
            }
        }
        foreach (['fontcss', 'logo'] as $key) {
            $value = trim((string)($raw[$key] ?? ''));
            if (self::is_https($value) || ($value !== '' && $value[0] === '/' && strpos($value, '//') !== 0)) {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    /**
     * #rgb or #rrggbb.
     *
     * @param string $value
     * @return bool
     */
    public static function is_colour(string $value): bool {
        return (bool)preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value);
    }

    /**
     * A CSS font-family list: letters, digits, spaces, commas, hyphens, quotes.
     *
     * @param string $value
     * @return bool
     */
    public static function is_font(string $value): bool {
        return $value !== '' && strlen($value) <= 200 && (bool)preg_match('/^[\p{L}0-9 ,\'"\-]+$/u', $value);
    }

    /**
     * An absolute https URL.
     *
     * @param string $value
     * @return bool
     */
    public static function is_https(string $value): bool {
        return strpos($value, 'https://') === 0 && filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Insert or update a campaign from the edit form.
     *
     * @param \stdClass $data form data
     * @return int id
     */
    public static function save(\stdClass $data): int {
        global $DB;
        $theme = [];
        foreach (array_merge(self::COLOURS, self::THEMETEXT) as $key) {
            $theme[$key] = $data->{'theme_' . $key} ?? '';
        }
        $record = (object)[
            'slug' => \core_text::strtolower(trim($data->slug)),
            'name' => trim((string)($data->name ?? '')),
            'courseid' => (int)($data->courseid ?? 0),
            'visible' => empty($data->visible) ? 0 : 1,
            'capi' => empty($data->capi) ? 0 : 1,
            'thankyouurl' => trim((string)($data->thankyouurl ?? '')),
            'thankyoufallback' => trim((string)($data->thankyoufallback ?? '')),
            'title' => trim((string)($data->title ?? '')),
            'intro' => trim((string)($data->intro ?? '')),
            'cardtitle' => trim((string)($data->cardtitle ?? '')),
            'cardnote' => trim((string)($data->cardnote ?? '')),
            'cardimage' => trim((string)($data->cardimage ?? '')),
            'pricelabel' => trim((string)($data->pricelabel ?? '')),
            'buttonlabel' => trim((string)($data->buttonlabel ?? '')),
            'belowbutton' => trim((string)($data->belowbutton ?? '')),
            'theme' => json_encode(self::clean_theme($theme), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'timemodified' => time(),
        ];
        if (!empty($data->id)) {
            $record->id = (int)$data->id;
            $DB->update_record('local_kabsignup_campaign', $record);
            return $record->id;
        }
        $record->timecreated = time();
        return (int)$DB->insert_record('local_kabsignup_campaign', $record);
    }

    /**
     * Form defaults for an existing campaign.
     *
     * @param \stdClass $campaign
     * @return \stdClass
     */
    public static function to_form(\stdClass $campaign): \stdClass {
        $data = clone $campaign;
        $theme = json_decode((string)$campaign->theme, true) ?: [];
        foreach (array_merge(self::COLOURS, self::THEMETEXT) as $key) {
            $data->{'theme_' . $key} = $theme[$key] ?? '';
        }
        unset($data->theme);
        return $data;
    }

    /**
     * Delete a campaign. Old log rows keep the slug.
     *
     * @param int $id
     */
    public static function delete(int $id): void {
        global $DB;
        $DB->delete_records('local_kabsignup_campaign', ['id' => $id]);
    }

    /**
     * The link a landing should use for this campaign.
     *
     * @param \stdClass $campaign
     * @return \moodle_url
     */
    public static function signup_url(\stdClass $campaign): \moodle_url {
        $params = [];
        if ((int)$campaign->courseid > 0) {
            $params['id'] = (int)$campaign->courseid;
        }
        $params['camp'] = $campaign->slug;
        return new \moodle_url('/local/kabsignup/index.php', $params);
    }

    /**
     * Thank-you base address of a campaign for this persona: the template with
     * {pres} filled in, the fallback when the persona is unknown, or '' when the
     * campaign gives no address (then the course and default pages apply).
     *
     * @param \stdClass $campaign
     * @param string $persona checked slug or ''
     * @return string
     */
    public static function thankyou_base(\stdClass $campaign, string $persona): string {
        $template = manager::clean_target(str_replace('{pres}', 'x', (string)$campaign->thankyouurl)) !== ''
            ? trim((string)$campaign->thankyouurl) : '';
        if ($template === '') {
            return manager::clean_target((string)$campaign->thankyoufallback);
        }
        if (strpos($template, '{pres}') === false) {
            return $template;
        }
        if ($persona !== '') {
            return str_replace('{pres}', rawurlencode($persona), $template);
        }
        return manager::clean_target((string)$campaign->thankyoufallback);
    }
}
