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
 * City and country by IP, the way WordPress fills them (ipinfo.io), or from
 * Moodle's own GeoLite2 file when one is configured.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class geo {

    /** @var int ipinfo.io request timeout, seconds. */
    const TIMEOUT = 5;

    /**
     * Look an address up.
     *
     * @param string $ip
     * @return array|null ['city' => string, 'country' => ISO 3166-1 alpha-2 or ''] or null
     */
    public static function lookup(string $ip): ?array {
        global $CFG;
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return null;
        }
        if (!empty($CFG->geoip2file) && is_readable($CFG->geoip2file)) {
            try {
                $reader = new \GeoIp2\Database\Reader($CFG->geoip2file);
                $record = $reader->city($ip);
                return self::clean((string)($record->city->name ?? ''), (string)($record->country->isoCode ?? ''));
            } catch (\Throwable $e) {
                // Not in the database or a broken file: fall through to ipinfo.io.
                unset($e);
            }
        }
        return self::ipinfo($ip, manager::config()->ipinfotoken);
    }

    /**
     * Ask ipinfo.io (HTTPS). Without a token the anonymous ipinfo quota applies.
     *
     * @param string $ip
     * @param string $token
     * @return array|null
     */
    public static function ipinfo(string $ip, string $token): ?array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');
        $url = 'https://ipinfo.io/' . rawurlencode($ip) . '/json';
        if ($token !== '') {
            $url .= '?token=' . rawurlencode($token);
        }
        $curl = new \curl();
        $body = $curl->get($url, [], ['CURLOPT_TIMEOUT' => self::TIMEOUT, 'CURLOPT_CONNECTTIMEOUT' => 3]);
        $info = $curl->get_info();
        if ($curl->get_errno() || (int)($info['http_code'] ?? 0) !== 200) {
            return null;
        }
        $data = json_decode((string)$body, true);
        if (!is_array($data) || !empty($data['bogon'])) {
            return null;
        }
        return self::clean((string)($data['city'] ?? ''), (string)($data['country'] ?? ''));
    }

    /**
     * Trim to the user table limits; drop a country code Moodle does not know.
     *
     * @param string $city
     * @param string $country
     * @return array|null
     */
    protected static function clean(string $city, string $country): ?array {
        $city = \core_text::substr(trim(clean_param($city, PARAM_NOTAGS)), 0, 120);
        $country = strtoupper(trim($country));
        if ($country !== '' && !array_key_exists($country, get_string_manager()->get_list_of_countries(true))) {
            $country = '';
        }
        if ($city === '' && $country === '') {
            return null;
        }
        return ['city' => $city, 'country' => $country];
    }

    /**
     * Fill the empty city/country of a user from the IP. Written straight to
     * the user table, without a user_updated event: nothing else needs to hear
     * about it, and the Edwiser bridge would otherwise push it back to WordPress.
     *
     * @param int $userid
     * @param string $ip
     * @return bool true when something was written
     */
    public static function fill(int $userid, string $ip): bool {
        global $DB;
        $user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0], 'id, city, country');
        if (!$user || ($user->city !== '' && $user->country !== '')) {
            return false;
        }
        $found = self::lookup($ip);
        if ($found === null) {
            return false;
        }
        $update = ['id' => $user->id];
        if ($user->city === '' && $found['city'] !== '') {
            $update['city'] = $found['city'];
        }
        if ($user->country === '' && $found['country'] !== '') {
            $update['country'] = $found['country'];
        }
        if (count($update) === 1) {
            return false;
        }
        $update['timemodified'] = time();
        $DB->update_record('user', (object)$update);
        return true;
    }
}
