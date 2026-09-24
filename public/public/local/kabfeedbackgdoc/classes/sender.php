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
 * Builds the JSON payload for a feedback response and delivers it.
 *
 * @package    local_kabfeedbackgdoc
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sender {

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
     * Whether a feedback cmid is in the configured watch list (cmids setting only).
     *
     * @param int $cmid
     * @param string $configured comma-separated cmids; empty = all
     * @return bool
     */
    public static function cmid_is_watched(int $cmid, string $configured): bool {
        $ids = self::id_list($configured);
        return empty($ids) || in_array($cmid, $ids, true);
    }

    /**
     * Whether a feedback activity should be forwarded, given all three filters.
     *
     * All filters are AND-ed; an empty filter matches everything:
     *  - cmids: explicit course module ids;
     *  - courseids: every feedback in these courses;
     *  - namepattern: case-insensitive regex against the activity name (e.g. "вопрос").
     *
     * @param int $cmid
     * @param \stdClass $config plugin config
     * @return bool
     */
    public static function is_watched(int $cmid, \stdClass $config): bool {
        if (!self::cmid_is_watched($cmid, (string)($config->cmids ?? ''))) {
            return false;
        }
        $courseids = self::id_list((string)($config->courseids ?? ''));
        $pattern = trim((string)($config->namepattern ?? ''));
        if (empty($courseids) && $pattern === '') {
            return true;
        }
        $cm = get_coursemodule_from_id('feedback', $cmid);
        if (!$cm) {
            return false;
        }
        if (!empty($courseids) && !in_array((int)$cm->course, $courseids, true)) {
            return false;
        }
        if ($pattern !== '') {
            $re = '/' . str_replace('/', '\/', $pattern) . '/iu';
            if (@preg_match($re, $cm->name) !== 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * Build the payload for one feedback_completed record.
     *
     * @param int $completedid
     * @return array|null null if the response/activity no longer exists
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
     * POST the payload to the configured Apps Script web app.
     *
     * Apps Script answers a POST with a 302 to script.googleusercontent.com,
     * which only accepts GET. Moodle's curl wrapper, when it has to emulate
     * redirects (open_basedir), re-issues the POST and gets a 405, so we do
     * not follow at all: POST once, then GET the Location ourselves.
     *
     * @param array $payload
     * @return array ['ok' => bool, 'code' => int, 'body' => string]
     */
    public static function post(array $payload): array {
        $config = get_config('local_kabfeedbackgdoc');
        $url = trim((string)($config->webhookurl ?? ''));
        if ($url === '') {
            return ['ok' => false, 'code' => 0, 'body' => 'webhookurl not configured'];
        }
        if (!empty($config->secret)) {
            $payload['secret'] = $config->secret;
        }

        $curl = new \curl();
        $curl->setHeader(['Content-Type: application/json', 'Accept: application/json']);
        $body = $curl->post($url, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), [
            'CURLOPT_FOLLOWLOCATION' => 0,
            'CURLOPT_CONNECTTIMEOUT' => 10,
            'CURLOPT_TIMEOUT'        => 30,
        ]);
        $info = $curl->get_info();
        $code = (int)($info['http_code'] ?? 0);
        $body = is_string($body) ? $body : '';
        if ($curl->get_errno()) {
            return ['ok' => false, 'code' => $code, 'body' => 'curl error: ' . $curl->error];
        }

        for ($hop = 0; $hop < 5 && $code >= 300 && $code < 400; $hop++) {
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
            $body = $curl->get($location, [], [
                'CURLOPT_FOLLOWLOCATION' => 0,
                'CURLOPT_CONNECTTIMEOUT' => 10,
                'CURLOPT_TIMEOUT'        => 30,
            ]);
            $info = $curl->get_info();
            $code = (int)($info['http_code'] ?? 0);
            $body = is_string($body) ? $body : '';
            if ($curl->get_errno()) {
                return ['ok' => false, 'code' => $code, 'body' => 'curl error: ' . $curl->error];
            }
        }

        $ok = ($code >= 200 && $code < 300);
        $decoded = json_decode($body, true);
        if (is_array($decoded) && array_key_exists('ok', $decoded)) {
            $ok = $ok && (bool)$decoded['ok'];
        }
        return ['ok' => $ok, 'code' => $code, 'body' => mb_substr(trim($body), 0, 300)];
    }
}
