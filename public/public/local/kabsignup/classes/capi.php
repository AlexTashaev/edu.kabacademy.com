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
 * Server-side CompleteRegistration for the Facebook pixel (Conversions API),
 * the port of the WordPress theme's send_facebook_conversion_event().
 *
 * The browser event fires on the thank-you page with eventID = the eid the
 * page receives; the server event carries the same event_id, so Meta counts
 * one registration. Only a first enrolment counts (a repeat signup gets the
 * "already enrolled" page and no event, as on WordPress).
 *
 * The payload is built during the request (IP and browser of the visitor,
 * hashes of e-mail and phone — no plain address is stored) and sent by the
 * after_signup ad-hoc task, so Facebook never holds up the form.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class capi {

    /** @var string Graph API host. */
    const ENDPOINT = 'https://graph.facebook.com/';

    /**
     * Is the event configured at all?
     *
     * @param \stdClass $config
     * @return bool
     */
    public static function enabled(\stdClass $config): bool {
        return $config->capipixel !== '' && $config->capitoken !== '';
    }

    /**
     * The event to send later, or null when the campaign does not want it or
     * the pixel is not configured.
     *
     * @param \stdClass|null $campaign
     * @param \stdClass $config
     * @param string $eventid
     * @param string $email
     * @param string $phone normalised phone, may be ''
     * @param array $attr ad parameters of the link
     * @param string $ip
     * @param string $sourceurl page of the signup
     * @return array|null
     */
    public static function payload(?\stdClass $campaign, \stdClass $config, string $eventid, string $email,
            string $phone, array $attr, string $ip, string $sourceurl): ?array {
        if (!$campaign || empty($campaign->capi) || !self::enabled($config) || $eventid === '') {
            return null;
        }
        $userdata = ['em' => [hash('sha256', manager::normalise_email($email))]];
        $digits = preg_replace('/\D/', '', $phone);
        if ($digits !== '') {
            $userdata['ph'] = [hash('sha256', $digits)];
        }
        $fbc = attribution::fbc($attr);
        if ($fbc !== '') {
            $userdata['fbc'] = $fbc;
        }
        if ($ip !== '') {
            $userdata['client_ip_address'] = $ip;
        }
        $ua = \core_text::substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 512);
        if ($ua !== '') {
            $userdata['client_user_agent'] = $ua;
        }
        return [
            'event_name' => 'CompleteRegistration',
            'event_time' => time(),
            'event_id' => $eventid,
            'action_source' => 'website',
            'event_source_url' => $sourceurl,
            'user_data' => $userdata,
        ];
    }

    /**
     * Send one event.
     *
     * @param array $event from payload()
     * @param \stdClass $config
     * @return bool accepted by Facebook
     */
    public static function send(array $event, \stdClass $config): bool {
        global $CFG;
        if (!self::enabled($config)) {
            return false;
        }
        require_once($CFG->libdir . '/filelib.php');
        $body = ['data' => [$event], 'access_token' => $config->capitoken];
        if ($config->capitestcode !== '') {
            $body['test_event_code'] = $config->capitestcode;
        }
        $url = self::ENDPOINT . rawurlencode($config->capiversion) . '/' . rawurlencode($config->capipixel) . '/events';
        $curl = new \curl();
        $curl->setHeader(['Content-Type: application/json']);
        $response = $curl->post($url, json_encode($body), ['CURLOPT_TIMEOUT' => 10, 'CURLOPT_CONNECTTIMEOUT' => 5]);
        $code = (int)($curl->get_info()['http_code'] ?? 0);
        if ($code !== 200) {
            // The token must not reach the cron log.
            mtrace('local_kabsignup: Conversions API answered ' . $code . ': '
                . \core_text::substr(str_replace($config->capitoken, '***', (string)$response), 0, 300));
            return false;
        }
        return true;
    }
}
