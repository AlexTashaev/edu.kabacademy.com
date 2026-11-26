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

namespace local_kabtelegram;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

/**
 * SendPulse chatbot API: OAuth token (cached 50 minutes) and contact variables.
 * The same two calls WordPress made.
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sendpulse {

    /** @var \stdClass plugin settings */
    protected $config;

    /** @var string last error, for the log */
    public $error = '';

    /**
     * Constructor.
     *
     * @param \stdClass|null $config
     */
    public function __construct(?\stdClass $config = null) {
        $this->config = $config ?? manager::config();
    }

    /**
     * Are the API keys set?
     *
     * @return bool
     */
    public function configured(): bool {
        return $this->config->spclientid !== '' && $this->config->spclientsecret !== '';
    }

    /**
     * OAuth access token.
     *
     * @param bool $fresh ignore the cached one
     * @return string '' on failure ($this->error says why)
     */
    public function access_token(bool $fresh = false): string {
        if (!$this->configured()) {
            $this->error = 'SendPulse API keys are not set';
            return '';
        }
        $cache = \cache::make('local_kabtelegram', 'sptoken');
        // The key depends on the client id: new keys in the settings — new token.
        $key = 'tok' . substr(sha1($this->config->apibase . '|' . $this->config->spclientid), 0, 16);
        if (!$fresh) {
            $cached = $cache->get($key);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        }
        [$code, $body] = $this->post('/oauth/access_token', [
            'grant_type' => 'client_credentials',
            'client_id' => $this->config->spclientid,
            'client_secret' => $this->config->spclientsecret,
        ]);
        $json = json_decode($body, true);
        if ($code !== 200 || empty($json['access_token']) || !is_string($json['access_token'])) {
            $this->error = 'SendPulse auth failed: HTTP ' . $code;
            return '';
        }
        $cache->set($key, $json['access_token']);
        return $json['access_token'];
    }

    /**
     * Write the variables into a Telegram contact, one call per variable (the API
     * has no bulk call). An expired token is renewed once.
     *
     * @param string $contactid SendPulse contact id
     * @param array $variables name => value
     * @return bool all written
     */
    public function set_variables(string $contactid, array $variables): bool {
        $token = $this->access_token();
        if ($token === '') {
            return false;
        }
        $ok = true;
        $errors = [];
        foreach ($variables as $name => $value) {
            $payload = ['contact_id' => $contactid, 'variable_name' => (string)$name, 'variable_value' => (string)$value];
            [$code] = $this->post('/telegram/contacts/setVariable', $payload, $token);
            if ($code === 401) {
                $token = $this->access_token(true);
                if ($token === '') {
                    return false;
                }
                [$code] = $this->post('/telegram/contacts/setVariable', $payload, $token);
            }
            if ($code < 200 || $code >= 300) {
                $ok = false;
                $errors[] = $name . ':' . $code;
            }
        }
        if (!$ok) {
            $this->error = 'setVariable failed: ' . implode(', ', $errors);
        }
        return $ok;
    }

    /**
     * POST JSON to the API.
     *
     * @param string $path
     * @param array $payload
     * @param string $bearer access token or ''
     * @return array [int HTTP code (0 — no answer), string body]
     */
    protected function post(string $path, array $payload, string $bearer = ''): array {
        $curl = new \curl();
        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        if ($bearer !== '') {
            $headers[] = 'Authorization: Bearer ' . $bearer;
        }
        $curl->setHeader($headers);
        $body = $curl->post($this->config->apibase . $path, json_encode($payload, JSON_UNESCAPED_UNICODE), [
            'CURLOPT_CONNECTTIMEOUT' => 4,
            'CURLOPT_TIMEOUT' => 8,
            'CURLOPT_FOLLOWLOCATION' => false,
        ]);
        $info = $curl->get_info();
        $code = (int)($info['http_code'] ?? 0);
        if ($curl->get_errno()) {
            $code = 0;
        }
        return [$code, is_string($body) ? $body : ''];
    }
}
