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
 * Is a new address deliverable? Clearout instant verification, as the
 * WordPress checkout did with the Clearout plugin.
 *
 * Only addresses that are not in Moodle yet are checked (an existing account
 * proves nothing new and would only spend credits). Rejected are "invalid" and
 * disposable addresses; "unknown", catch-all and any failure of the service
 * let the signup through — the form must never depend on Clearout.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class emailcheck {

    /** @var string Instant verification endpoint. */
    const ENDPOINT = 'https://api.clearout.io/v2/email_verify/instant';

    /** @var array|null Result of the last check in this request: ['email' => …, 'result' => ok|reject|skip]. */
    protected static $last = null;

    /**
     * Check an address.
     *
     * @param string $email
     * @param \stdClass $config
     * @return string 'off' (no token), 'ok', 'reject' or 'skip' (service failed)
     */
    public static function check(string $email, \stdClass $config): string {
        global $CFG;
        if ($config->clearouttoken === '') {
            return 'off';
        }
        $email = manager::normalise_email($email);
        if (self::$last !== null && self::$last['email'] === $email) {
            return self::$last['result'];
        }
        require_once($CFG->libdir . '/filelib.php');
        $result = 'skip';
        try {
            $curl = new \curl();
            $curl->setHeader([
                'Content-Type: application/json',
                'Authorization: Bearer:' . str_replace(' ', '', $config->clearouttoken),
            ]);
            $timeout = $config->clearouttimeout;
            $response = $curl->post(self::ENDPOINT, json_encode(['email' => $email, 'timeout' => $timeout * 1000]),
                ['CURLOPT_TIMEOUT' => $timeout + 2, 'CURLOPT_CONNECTTIMEOUT' => 3]);
            $code = (int)($curl->get_info()['http_code'] ?? 0);
            $data = json_decode((string)$response, true);
            if ($code === 200 && is_array($data) && isset($data['data']['status'])) {
                $status = (string)$data['data']['status'];
                $disposable = (string)($data['data']['disposable'] ?? '');
                $result = ($status === 'invalid' || $disposable === 'yes') ? 'reject' : 'ok';
            }
        } catch (\Throwable $e) {
            $result = 'skip';
        }
        self::$last = ['email' => $email, 'result' => $result];
        return $result;
    }

    /**
     * Log flag for the last check of this address: co:ok, co:skip, or nothing.
     *
     * @param string $email
     * @return string[]
     */
    public static function flags(string $email): array {
        $email = manager::normalise_email($email);
        if (self::$last === null || self::$last['email'] !== $email) {
            return [];
        }
        return in_array(self::$last['result'], ['ok', 'skip'], true) ? ['co:' . self::$last['result']] : [];
    }
}
