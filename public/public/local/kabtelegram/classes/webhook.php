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

/**
 * SendPulse webhook: a contact started the bot from our deep link → link the
 * contact to the person and write the kab_* variables into it.
 *
 * SendPulse posts a list of events ({title, contact, info, …}); the deep link
 * token sits in contact.variables.token (tg.pulse.is puts it there), sometimes
 * also in "/start <token>" or in the tracking data of the message.
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class webhook {

    /**
     * Is the request allowed: the secret in ?key= or in the X-Webhook-Secret header.
     *
     * @param string $given
     * @return bool
     */
    public static function authorised(string $given): bool {
        $secret = manager::config()->webhooksecret;
        return $secret !== '' && $given !== '' && hash_equals($secret, $given);
    }

    /**
     * Events of a webhook body.
     *
     * @param mixed $body decoded JSON
     * @return array|null list of events, null when the body is not a SendPulse webhook
     */
    public static function events($body): ?array {
        if (!is_array($body)) {
            return null;
        }
        if (isset($body['contact']) && is_array($body['contact'])) {
            return [$body];
        }
        if (array_key_exists(0, $body)) {
            return array_values(array_filter($body, 'is_array'));
        }
        return null;
    }

    /**
     * The deep-link token of an event, '' when there is none.
     *
     * @param array $event
     * @return string
     */
    public static function token_of(array $event): string {
        $contact = is_array($event['contact'] ?? null) ? $event['contact'] : [];
        $candidates = [];

        $variables = $contact['variables'] ?? [];
        if (is_array($variables)) {
            if (isset($variables['token']) && is_scalar($variables['token'])) {
                $candidates[] = (string)$variables['token'];
            }
            // Some webhook formats list the variables as [{name, value}].
            foreach ($variables as $var) {
                if (is_array($var) && ($var['name'] ?? '') === 'token' && isset($var['value']) && is_scalar($var['value'])) {
                    $candidates[] = (string)$var['value'];
                }
            }
        }
        foreach ([
            $contact['last_message_data']['message']['tracking_data']['contact_variables']['token'] ?? null,
            $event['info']['message']['channel_data']['message']['tracking_data']['contact_variables']['token'] ?? null,
        ] as $tracked) {
            if (is_scalar($tracked)) {
                $candidates[] = (string)$tracked;
            }
        }
        foreach ([
            $contact['last_message'] ?? null,
            $event['info']['message']['channel_data']['message']['text'] ?? null,
        ] as $text) {
            if (!is_string($text)) {
                continue;
            }
            if (preg_match('#/start\s+(\S+)#', $text, $m)) {
                $candidates[] = $m[1];
            } else {
                $candidates[] = trim($text);
            }
        }
        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);
            if (preg_match(token::PATTERN, $candidate)) {
                return $candidate;
            }
        }
        return '';
    }

    /**
     * Process one event.
     *
     * @param array $event
     * @return string what happened: notoken | unknown | synced | unchanged | failed | nocontact
     */
    public static function process(array $event): string {
        global $DB;
        $contact = is_array($event['contact'] ?? null) ? $event['contact'] : [];
        $contactid = is_scalar($contact['id'] ?? null) ? trim((string)$contact['id']) : '';
        if ($contactid === '' || !preg_match('/^[A-Za-z0-9_-]{1,64}$/', $contactid)) {
            return 'nocontact';
        }
        $tokenstr = self::token_of($event);
        if ($tokenstr === '') {
            return 'notoken';
        }
        $userid = token::find($tokenstr);
        if (!$userid || !$DB->record_exists('user', ['id' => $userid, 'deleted' => 0])) {
            return 'unknown';
        }

        $telegramid = is_scalar($contact['telegram_id'] ?? null) ? preg_replace('/\D/', '', (string)$contact['telegram_id']) : '';
        $link = self::link($userid, $contactid, \core_text::substr($telegramid, 0, 32));
        $result = self::sync($link);
        if ($result === 'failed' && (new sendpulse())->configured()) {
            // SendPulse did not answer or refused: try again from cron (the task retries with backoff).
            $task = new task\sync_contact();
            $task->set_custom_data(['linkid' => (int)$link->id]);
            $task->set_next_run_time(time() + 5 * MINSECS);
            \core\task\manager::queue_adhoc_task($task, true);
        }
        return $result;
    }

    /**
     * Link the person to the contact (once; the first link counts as bot_linked).
     *
     * @param int $userid
     * @param string $contactid
     * @param string $telegramid
     * @return \stdClass link row
     */
    public static function link(int $userid, string $contactid, string $telegramid): \stdClass {
        global $DB;
        $link = $DB->get_record('local_kabtelegram_contact', ['userid' => $userid, 'contactid' => $contactid]);
        if ($link) {
            if ($telegramid !== '' && $link->telegramid !== $telegramid) {
                $link->telegramid = $telegramid;
                $DB->set_field('local_kabtelegram_contact', 'telegramid', $telegramid, ['id' => $link->id]);
            }
            return $link;
        }
        $link = (object)[
            'userid' => $userid,
            'contactid' => $contactid,
            'telegramid' => $telegramid,
            'synchash' => '',
            'timecreated' => time(),
            'timesynced' => 0,
        ];
        try {
            $link->id = $DB->insert_record('local_kabtelegram_contact', $link);
            stats::track('bot_linked', 'webhook', '', $userid);
        } catch (\dml_write_exception $e) {
            // Two events of the same contact at once: the other request has just linked it.
            $link = $DB->get_record('local_kabtelegram_contact', ['userid' => $userid, 'contactid' => $contactid], '*', MUST_EXIST);
        }
        return $link;
    }

    /**
     * Write the person's current data into the contact unless it already has
     * exactly these values (WordPress rewrote every variable on every incoming
     * message of the contact).
     *
     * @param \stdClass $link contact link row
     * @return string synced | unchanged | failed | nouser
     */
    public static function sync(\stdClass $link): string {
        global $DB;
        $user = $DB->get_record('user', ['id' => $link->userid, 'deleted' => 0]);
        if (!$user) {
            return 'nouser';
        }
        $config = manager::config();
        $variables = snapshot::variables(snapshot::build($user), $config->extravars);
        ksort($variables);
        $hash = hash('sha256', json_encode($variables, JSON_UNESCAPED_UNICODE));
        if ($hash === $link->synchash) {
            return 'unchanged';
        }
        $api = new sendpulse($config);
        if ($api->set_variables($link->contactid, $variables)) {
            $DB->update_record('local_kabtelegram_contact', (object)[
                'id' => $link->id,
                'synchash' => $hash,
                'timesynced' => time(),
            ]);
            return 'synced';
        }
        error_log('local_kabtelegram: contact ' . $link->contactid . ' (user ' . $link->userid . '): ' . $api->error);
        return 'failed';
    }
}
