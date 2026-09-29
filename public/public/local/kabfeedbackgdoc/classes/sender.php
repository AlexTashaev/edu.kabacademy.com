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

namespace local_kabfeedbackgdoc;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/feedback/lib.php');
require_once($CFG->libdir . '/filelib.php');

/**
 * Decides where a feedback response goes, builds its JSON payload and delivers it.
 *
 * @package    local_kabfeedbackgdoc
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sender {

    /** Layout of the teachers' questions table (the default target). */
    public const LAYOUT_QUESTIONS = 'questions';

    /** Layout of a form with its own table: one column per form item. */
    public const LAYOUT_GENERIC = 'generic';

    /** Responses per request when a whole form is re-sent. */
    public const BATCH_SIZE = 50;

    /** How many times a delivery is asked for before the task gives up until cron retries it. */
    public const TRIES = 3;

    /**
     * Parse a comma/space separated list of ints.
     *
     * @param string $configured
     * @return int[]
     */
    protected static function id_list(string $configured): array {
        $configured = trim($configured);
        if ($configured === '') {
            return [];
        }
        return array_values(array_filter(array_map('intval', preg_split('/[\s,;]+/', $configured))));
    }

    /**
     * Spreadsheet id from a Google Sheets URL or from a bare id.
     *
     * @param string $value
     * @return string '' if the value is neither
     */
    public static function spreadsheet_id(string $value): string {
        $value = trim($value);
        if (preg_match('~/spreadsheets/d/([A-Za-z0-9_-]{20,})~', $value, $m)) {
            return $m[1];
        }
        if (preg_match('~^[A-Za-z0-9_-]{20,}$~', $value)) {
            return $value;
        }
        return '';
    }

    /**
     * Link to a spreadsheet.
     *
     * @param string $spreadsheetid
     * @return string
     */
    public static function spreadsheet_url(string $spreadsheetid): string {
        return 'https://docs.google.com/spreadsheets/d/' . $spreadsheetid . '/edit';
    }

    /**
     * Parse the "routes" setting: forms that have a table of their own.
     *
     * One route per line: "cmid = spreadsheet URL or id", optionally followed by
     * "| tab name". Empty lines and lines starting with # are skipped.
     *
     * @param string $configured
     * @return array[] cmid => ['spreadsheet' => id, 'sheet' => tab name or '']
     */
    public static function routes(string $configured): array {
        $routes = [];
        foreach (preg_split('/\R/u', $configured) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $cmid = (int)trim($parts[0]);
            $right = explode('|', $parts[1], 2);
            $spreadsheet = self::spreadsheet_id($right[0]);
            if ($cmid <= 0 || $spreadsheet === '') {
                continue;
            }
            $routes[$cmid] = ['spreadsheet' => $spreadsheet, 'sheet' => trim($right[1] ?? '')];
        }
        return $routes;
    }

    /**
     * Whether a feedback activity passes the three filters of the default table.
     *
     * All filters are AND-ed; an empty filter matches everything:
     *  - cmids: explicit course module ids;
     *  - courseids: every feedback in these courses;
     *  - namepattern: case-insensitive regex against the activity name (e.g. "вопрос").
     *
     * @param int $cmid
     * @param int $courseid
     * @param string $name activity name
     * @param \stdClass $config plugin config
     * @return bool
     */
    public static function matches_filters(int $cmid, int $courseid, string $name, \stdClass $config): bool {
        $cmids = self::id_list((string)($config->cmids ?? ''));
        if (!empty($cmids) && !in_array($cmid, $cmids, true)) {
            return false;
        }
        $courseids = self::id_list((string)($config->courseids ?? ''));
        if (!empty($courseids) && !in_array($courseid, $courseids, true)) {
            return false;
        }
        $pattern = trim((string)($config->namepattern ?? ''));
        if ($pattern !== '') {
            $re = '/' . str_replace('/', '\/', $pattern) . '/iu';
            if (@preg_match($re, $name) !== 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * Where the responses of a feedback activity go.
     *
     * A form listed in "routes" goes to its own table (generic layout) whatever
     * the filters say; any other form that passes the filters goes to the default
     * questions table.
     *
     * @param int $cmid
     * @param int $courseid
     * @param string $name activity name
     * @param \stdClass $config plugin config
     * @return array|null ['spreadsheet' => id or '' (the one built into the script), 'sheet' => string,
     *                     'layout' => string]; null = not forwarded
     */
    public static function target_for_cm(int $cmid, int $courseid, string $name, \stdClass $config): ?array {
        $routes = self::routes((string)($config->routes ?? ''));
        if (isset($routes[$cmid])) {
            return $routes[$cmid] + ['layout' => self::LAYOUT_GENERIC];
        }
        if (!self::matches_filters($cmid, $courseid, $name, $config)) {
            return null;
        }
        return [
            'spreadsheet' => self::spreadsheet_id((string)($config->defaulttarget ?? '')),
            'sheet'       => '',
            'layout'      => self::LAYOUT_QUESTIONS,
        ];
    }

    /**
     * Where the responses of a feedback activity go, by cmid only.
     *
     * @param int $cmid
     * @param \stdClass $config plugin config
     * @return array|null see target_for_cm()
     */
    public static function target_for(int $cmid, \stdClass $config): ?array {
        $cm = get_coursemodule_from_id('feedback', $cmid);
        if (!$cm) {
            return null;
        }
        return self::target_for_cm((int)$cm->id, (int)$cm->course, (string)$cm->name, $config);
    }

    /**
     * Whether a feedback activity is forwarded at all.
     *
     * @param int $cmid
     * @param \stdClass $config plugin config
     * @return bool
     */
    public static function is_watched(int $cmid, \stdClass $config): bool {
        return self::target_for($cmid, $config) !== null;
    }

    /**
     * Build the payload for one feedback_completed record.
     *
     * @param int $completedid
     * @return array|null null if the response/activity no longer exists or is not forwarded any more
     */
    public static function build_payload(int $completedid): ?array {
        global $DB, $CFG;

        $completed = $DB->get_record('feedback_completed', ['id' => $completedid]);
        if (!$completed) {
            return null;
        }
        $feedback = $DB->get_record('feedback', ['id' => $completed->feedback]);
        if (!$feedback) {
            return null;
        }
        $cm = get_coursemodule_from_instance('feedback', $feedback->id, $feedback->course);
        if (!$cm) {
            return null;
        }
        $target = self::target_for_cm((int)$cm->id, (int)$cm->course, (string)$cm->name,
            get_config('local_kabfeedbackgdoc'));
        if ($target === null) {
            return null;
        }
        $course = get_course($feedback->course);
        $modcontext = \context_module::instance($cm->id);

        $anonymous = ((int)$completed->anonymous_response === FEEDBACK_ANONYMOUS_YES);
        $user = null;
        if (!$anonymous && $completed->userid) {
            $user = $DB->get_record('user', ['id' => $completed->userid]);
        }

        $answers = [];
        $items = $DB->get_records('feedback_item', ['feedback' => $feedback->id], 'position ASC');
        $values = $DB->get_records('feedback_value', ['completed' => $completed->id], '', 'item, value');
        foreach ($items as $item) {
            if (!$item->hasvalue) {
                continue; // Labels, page breaks, captcha.
            }
            $raw = isset($values[$item->id]) ? (string)$values[$item->id]->value : '';
            $printval = $raw;
            try {
                $itemobj = feedback_get_item_class($item->typ);
                $printval = (string)$itemobj->get_printval($item, (object)['value' => $raw]);
            } catch (\Throwable $e) {
                // Fall back to the raw value.
                $printval = $raw;
            }
            if (!in_array($item->typ, ['textarea', 'textfield'], true)) {
                // Option labels may carry markup; free-text answers are raw and kept as typed.
                $printval = str_replace(['<br>', '<br />', '<br/>'], "\n", $printval);
                $printval = html_entity_decode(strip_tags($printval), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
            $printval = trim($printval);
            $answers[] = [
                'itemid' => (int)$item->id,
                'name'   => format_string($item->name, true, ['context' => $modcontext]),
                'type'   => $item->typ,
                'value'  => $printval,
            ];
        }

        $responseurl = new \moodle_url('/mod/feedback/show_entries.php', [
            'id'            => $cm->id,
            'userid'        => $completed->userid,
            'showcompleted' => $completed->id,
        ]);

        return [
            'event'       => 'feedback_response',
            'site'        => $CFG->wwwroot,
            'completedid' => (int)$completed->id,
            'timestamp'   => (int)$completed->timemodified,
            'datetime'    => userdate($completed->timemodified, '%d.%m.%Y %H:%M', \core_date::get_server_timezone()),
            'target'      => $target,
            'course'      => [
                'id'        => (int)$course->id,
                'shortname' => $course->shortname,
                'fullname'  => format_string($course->fullname, true, ['context' => \context_course::instance($course->id)]),
            ],
            'feedback'    => [
                'id'   => (int)$feedback->id,
                'cmid' => (int)$cm->id,
                'name' => format_string($feedback->name, true, ['context' => $modcontext]),
                'url'  => (new \moodle_url('/mod/feedback/view.php', ['id' => $cm->id]))->out(false),
            ],
            'anonymous'   => $anonymous,
            'user'        => $user ? [
                'id'       => (int)$user->id,
                'fullname' => fullname($user),
                'email'    => $user->email,
                'city'     => (string)$user->city,
                'groups'   => self::user_group_names((int)$course->id, (int)$user->id),
                'url'      => (new \moodle_url('/user/view.php', ['id' => $user->id, 'course' => $course->id]))->out(false),
            ] : null,
            'responseurl' => $responseurl->out(false),
            'answers'     => $answers,
        ];
    }

    /**
     * Names of the course groups the user belongs to (the "Номер группы" column).
     *
     * @param int $courseid
     * @param int $userid
     * @return string[]
     */
    public static function user_group_names(int $courseid, int $userid): array {
        global $DB;
        $sql = "SELECT g.id, g.name
                  FROM {groups} g
                  JOIN {groups_members} gm ON gm.groupid = g.id
                 WHERE g.courseid = :courseid AND gm.userid = :userid
              ORDER BY g.name";
        $names = [];
        foreach ($DB->get_records_sql($sql, ['courseid' => $courseid, 'userid' => $userid]) as $g) {
            $names[] = format_string($g->name, true, ['context' => \context_course::instance($courseid)]);
        }
        return $names;
    }

    /**
     * Ids of all responses of a feedback activity, oldest first.
     *
     * @param int $feedbackid
     * @return int[]
     */
    public static function completed_ids(int $feedbackid): array {
        global $DB;
        return array_map('intval', array_keys(
            $DB->get_records('feedback_completed', ['feedback' => $feedbackid], 'timemodified ASC, id ASC', 'id')
        ));
    }

    /**
     * Deliver one response.
     *
     * @param array $payload from build_payload()
     * @return array ['ok' => bool, 'code' => int, 'body' => string, 'json' => array|null, 'tries' => int]
     */
    public static function post(array $payload): array {
        return self::deliver($payload, 90);
    }

    /**
     * Deliver several responses with one request; the script appends them in the given order.
     *
     * @param array[] $payloads from build_payload()
     * @return array ['ok' => bool, 'code' => int, 'body' => string, 'json' => array|null, 'tries' => int]
     */
    public static function post_batch(array $payloads): array {
        global $CFG;
        return self::deliver([
            'event'     => 'feedback_batch',
            'site'      => $CFG->wwwroot,
            'responses' => array_values($payloads),
        ], 240);
    }

    /**
     * Ask the script who it is: GET returns {"ok":true,"ping":…,"version":…}.
     *
     * @return array ['ok' => bool, 'code' => int, 'body' => string, 'json' => array|null]
     */
    public static function ping(): array {
        return self::request(null, 90);
    }

    /**
     * POST to the script, asking again when the answer got lost on the way.
     *
     * Google answers a share of the requests (about one in eight, measured from this
     * server) 8 to 30 seconds late and from the wrong place: the redirect chain ends
     * with the ping of doGet() or with a 404 of the content host, although doPost()
     * has run and the rows are written. Asking again is safe, the script skips the
     * rows it already has. What the script itself refused ("error" in its answer) is
     * not asked again here: the task fails and cron retries it later.
     *
     * @param array $payload
     * @param int $timeout seconds per request
     * @return array ['ok' => bool, 'code' => int, 'body' => string, 'json' => array|null, 'tries' => int]
     */
    protected static function deliver(array $payload, int $timeout): array {
        $tries = 0;
        do {
            $tries++;
            $result = self::request($payload, $timeout);
            $refused = is_array($result['json']) && array_key_exists('error', $result['json']);
        } while (!$result['ok'] && !$refused && empty($result['unconfigured']) && $tries < self::TRIES);

        $result['tries'] = $tries;
        if ($tries > 1) {
            $result['body'] .= " (try {$tries})";
        }
        return $result;
    }

    /**
     * Talk to the configured Apps Script web app, once.
     *
     * Apps Script answers a POST with a 302 to script.googleusercontent.com,
     * which only accepts GET. Moodle's curl wrapper, when it has to emulate
     * redirects (open_basedir), re-issues the POST and gets a 405, so we do
     * not follow at all: POST once, then GET the Location ourselves.
     *
     * Only a JSON answer with "ok": true and the number of rows written counts as
     * delivered. A broken script or a deployment that lost its "Anyone" access
     * answers with an HTML page and HTTP 200, and a redirect chain that ends at the
     * web app itself answers with the ping of doGet() - neither is a delivery.
     *
     * @param array|null $payload null = GET (ping)
     * @param int $timeout seconds
     * @return array ['ok' => bool, 'code' => int, 'body' => string, 'json' => array|null]
     */
    protected static function request(?array $payload, int $timeout): array {
        $config = get_config('local_kabfeedbackgdoc');
        $url = trim((string)($config->webhookurl ?? ''));
        if ($url === '') {
            return ['ok' => false, 'code' => 0, 'body' => 'webhookurl not configured', 'json' => null,
                'unconfigured' => true];
        }
        $options = [
            'CURLOPT_FOLLOWLOCATION' => 0,
            'CURLOPT_CONNECTTIMEOUT' => 10,
            'CURLOPT_TIMEOUT'        => $timeout,
        ];

        $curl = new \curl();
        if ($payload === null) {
            $curl->setHeader(['Accept: application/json']);
            $body = $curl->get($url, [], $options);
        } else {
            if (!empty($config->secret)) {
                $payload['secret'] = $config->secret;
            }
            $curl->setHeader(['Content-Type: application/json', 'Accept: application/json']);
            $body = $curl->post($url, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $options);
        }

        for ($hop = 0; $hop <= 5; $hop++) {
            $info = $curl->get_info();
            $code = (int)($info['http_code'] ?? 0);
            $body = is_string($body) ? $body : '';
            if ($curl->get_errno()) {
                return ['ok' => false, 'code' => $code, 'body' => 'curl error: ' . $curl->error, 'json' => null];
            }
            if ($code < 300 || $code >= 400 || $hop === 5) {
                break;
            }
            $location = (string)($info['redirect_url'] ?? '');
            if ($location === '') {
                $raw = $curl->get_raw_response();
                $raw = is_array($raw) ? implode("\n", $raw) : (string)$raw;
                if (preg_match('/^Location:\s*(\S+)/mi', $raw, $m)) {
                    $location = $m[1];
                }
            }
            if ($location === '') {
                break;
            }
            $curl = new \curl();
            $curl->setHeader(['Accept: application/json']);
            $body = $curl->get($location, [], $options);
        }

        $decoded = json_decode($body, true);
        $json = is_array($decoded) ? $decoded : null;
        $ok = ($code >= 200 && $code < 300) && $json !== null && !empty($json['ok']);
        if ($ok && $payload !== null && !array_key_exists('written', $json)) {
            $ok = false;
            $body = 'not the answer of doPost(): ' . $body;
        } else if ($json === null) {
            $body = 'not JSON: ' . trim(preg_replace('/\s+/', ' ', strip_tags($body)));
        }
        return ['ok' => $ok, 'code' => $code, 'body' => mb_substr(trim($body), 0, 300), 'json' => $json];
    }
}
