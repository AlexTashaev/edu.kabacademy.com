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
 * Bot deep-link tokens. The link "tg.pulse.is/<bot>?start=<flow>&token=<token>"
 * makes SendPulse store the token as the contact variable "token"; the webhook
 * then finds the person by it. One token per person: reused while it is alive
 * and prolonged on every click (as WordPress did with the profile token),
 * replaced once it has expired so that an old copied link dies.
 *
 * Unlike WordPress no data is stored with the token: the webhook reads the
 * person's current data (snapshot::build) when the contact arrives.
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class token {

    /** Token format: "m" + Moodle user id + "_" + random part. WordPress used "<order>_…" and "u<wp user>_…". */
    const PATTERN = '/^m\d{1,10}_[A-Za-z0-9]{16}$/';

    /**
     * Token for a user.
     *
     * @param int $userid
     * @param int|null $now
     * @return string
     */
    public static function issue(int $userid, ?int $now = null): string {
        global $DB;
        $now = $now ?? time();
        $expires = $now + manager::config()->tokendays * DAYSECS;

        $row = $DB->get_record('local_kabtelegram_token', ['userid' => $userid]);
        if ($row && (int)$row->timeexpires > $now) {
            $DB->update_record('local_kabtelegram_token', (object)[
                'id' => $row->id,
                'timemodified' => $now,
                'timeexpires' => $expires,
            ]);
            return $row->token;
        }

        $token = 'm' . $userid . '_' . random_string(16);
        $record = (object)[
            'userid' => $userid,
            'token' => $token,
            'timecreated' => $now,
            'timemodified' => $now,
            'timeexpires' => $expires,
        ];
        if ($row) {
            $record->id = $row->id;
            $DB->update_record('local_kabtelegram_token', $record);
        } else {
            try {
                $DB->insert_record('local_kabtelegram_token', $record);
            } catch (\dml_write_exception $e) {
                // A double click: the other request has just created the token.
                return (string)$DB->get_field('local_kabtelegram_token', 'token', ['userid' => $userid], MUST_EXIST);
            }
        }
        return $token;
    }

    /**
     * Owner of a live token.
     *
     * @param string $token
     * @param int|null $now
     * @return int user id, 0 when the token is unknown or expired
     */
    public static function find(string $token, ?int $now = null): int {
        global $DB;
        if (!preg_match(self::PATTERN, $token)) {
            return 0;
        }
        $row = $DB->get_record('local_kabtelegram_token', ['token' => $token], 'id, userid, timeexpires');
        if (!$row || (int)$row->timeexpires <= ($now ?? time())) {
            return 0;
        }
        return (int)$row->userid;
    }

    /**
     * Deep link into the bot. With a token and a flow: tg.pulse.is (the token
     * reaches the contact variables), otherwise plain t.me.
     *
     * @param \stdClass $config
     * @param string $token may be ''
     * @return string '' when no bot is configured
     */
    public static function bot_link(\stdClass $config, string $token): string {
        if ($config->botusername === '') {
            return '';
        }
        if ($token !== '' && $config->flowid !== '') {
            return 'https://tg.pulse.is/' . rawurlencode($config->botusername) . '?start=' . rawurlencode($config->flowid)
                . '&token=' . rawurlencode($token);
        }
        return 'https://t.me/' . rawurlencode($config->botusername);
    }
}
