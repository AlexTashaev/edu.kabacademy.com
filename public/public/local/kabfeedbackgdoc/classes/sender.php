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
     * Whether a feedback cmid is in the configured watch list.
     *
     * @param int $cmid
     * @param string $configured comma-separated cmids; empty = all
     * @return bool
     */
    public static function cmid_is_watched(int $cmid, string $configured): bool {
        $configured = trim($configured);
        if ($configured === '') {
            return true;
        }
        $ids = array_filter(array_map('intval', preg_split('/[\s,;]+/', $configured)));
        return in_array($cmid, $ids, true);
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
                'url'      => (new \moodle_url('/user/view.php', ['id' => $user->id, 'course' => $course->id]))->out(false),
            ] : null,
            'responseurl' => $responseurl->out(false),
            'answers'     => $answers,
        ];
    }

    /**
     * POST the payload to the configured Apps Script web app.
     *
     * Apps Script answers a POST with a 302 to script.googleusercontent.com;
     * Moodle's curl wrapper follows redirects by default, and the redirected
     * GET returns whatever doPost() produced.
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
            'CURLOPT_FOLLOWLOCATION' => 1,
            'CURLOPT_MAXREDIRS'      => 5,
            'CURLOPT_CONNECTTIMEOUT' => 10,
            'CURLOPT_TIMEOUT'        => 30,
        ]);
        $code = (int)($curl->get_info()['http_code'] ?? 0);
        $body = is_string($body) ? $body : '';
        if ($curl->get_errno()) {
            return ['ok' => false, 'code' => $code, 'body' => 'curl error: ' . $curl->error];
        }

        $ok = ($code >= 200 && $code < 300);
        $decoded = json_decode($body, true);
        if (is_array($decoded) && array_key_exists('ok', $decoded)) {
            $ok = $ok && (bool)$decoded['ok'];
        }
        return ['ok' => $ok, 'code' => $code, 'body' => mb_substr(trim($body), 0, 300)];
    }
}
