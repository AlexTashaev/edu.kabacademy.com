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

namespace auth_kabtelegram;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');

/**
 * Telegram Login: the round trip through oauth.telegram.org, the signature
 * check and the Telegram <-> Moodle account links.
 *
 * Isolation: nothing here reads or writes passwords, changes a user's auth
 * method or creates accounts. The class only decides whether a verified
 * Telegram account is linked to an existing Moodle account that may be
 * signed in, and keeps the links table.
 *
 * Protocol (https://core.telegram.org/widgets/login, "Checking authorization"):
 * the browser goes to oauth.telegram.org/auth?bot_id&origin&return_to and comes
 * back to return_to#tgAuthResult=<base64url JSON>. The JSON fields except
 * "hash", as key=value lines sorted by key and joined by "\n", are signed with
 * HMAC-SHA256 under the key SHA256(bot token).
 *
 * @package    auth_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /** @var string Links table. */
    const TABLE = 'auth_kabtelegram_links';

    /** @var string Telegram's login endpoint. */
    const OAUTH_URL = 'https://oauth.telegram.org/auth';

    /** @var int The round trip (start -> callback) must fit into this many seconds. */
    const STATE_LIFETIME = 1800;

    /** @var int A verified but unlinked Telegram waits this long for the user to sign in and link it. */
    const PENDING_LIFETIME = 1800;

    /** @var int Tolerated clock difference with Telegram, seconds. */
    const CLOCK_SKEW = 300;

    /** @var int Upper bound of the maxage setting, minutes (one day). */
    const MAXAGE_LIMIT = 1440;

    /** @var string Auth methods allowed by default. */
    const DEFAULT_AUTHS = 'manual,email';

    /** @var string[] Fields Telegram may send; anything else is refused. */
    const FIELDS = ['id', 'first_name', 'last_name', 'username', 'photo_url', 'auth_date', 'hash'];

    /**
     * Plugin settings with defaults applied.
     *
     * @return \stdClass bottoken, botid (int, 0 when the token is not set), maxage (minutes), allowedauths (array)
     */
    public static function config(): \stdClass {
        $config = get_config('auth_kabtelegram');
        $out = new \stdClass();
        $out->bottoken = trim((string)($config->bottoken ?? ''));
        $out->botid = 0;
        if (preg_match('/^(\d{1,20}):[A-Za-z0-9_-]{20,}$/', $out->bottoken, $m)) {
            $out->botid = (int)$m[1];
        }
        $maxage = (int)($config->maxage ?? 0);
        $out->maxage = $maxage > 0 ? min($maxage, self::MAXAGE_LIMIT) : 60;
        $auths = trim((string)($config->allowedauths ?? ''));
        if ($auths === '') {
            $auths = self::DEFAULT_AUTHS;
        }
        $out->allowedauths = array_values(array_filter(array_map('trim', explode(',', $auths)), function($auth) {
            return $auth !== '' && $auth !== 'nologin';
        }));
        return $out;
    }

    /**
     * Is a usable bot token set?
     *
     * @return bool
     */
    public static function is_configured(): bool {
        return self::config()->botid > 0;
    }

    /**
     * Scheme and host of the site, the "origin" Telegram checks against the bot's domain.
     *
     * @return string
     */
    public static function origin(): string {
        global $CFG;
        $parts = parse_url($CFG->wwwroot);
        $origin = $parts['scheme'] . '://' . $parts['host'];
        if (!empty($parts['port'])) {
            $origin .= ':' . $parts['port'];
        }
        return $origin;
    }

    /**
     * Where to go after the login: an absolute URL inside wwwroot, or '' when
     * the requested one is not ours or is a picture/style/script.
     *
     * @param string $wantsurl
     * @return string
     */
    public static function clean_wantsurl(string $wantsurl): string {
        global $CFG;
        $wantsurl = clean_param($wantsurl, PARAM_LOCALURL);
        if ($wantsurl === '') {
            return '';
        }
        $out = (new \moodle_url($wantsurl))->out(false);
        if ($out !== $CFG->wwwroot && strpos($out, $CFG->wwwroot . '/') !== 0) {
            return '';
        }
        if (class_exists('\auth_kabmagic\manager') && method_exists('\auth_kabmagic\manager', 'is_subresource_url')
                && \auth_kabmagic\manager::is_subresource_url($out)) {
            return '';
        }
        return \core_text::substr($out, 0, 2000);
    }

    /**
     * Begin the round trip: remember a one-time state in the session and
     * return the Telegram URL to send the browser to.
     *
     * @param string $mode 'login' or 'link'
     * @param string $wantsurl where to go afterwards
     * @return \moodle_url
     */
    public static function start(string $mode, string $wantsurl): \moodle_url {
        global $SESSION, $USER;
        $config = self::config();
        $state = bin2hex(random_bytes(16));
        $SESSION->auth_kabtelegram_state = [
            'state' => $state,
            'mode' => $mode === 'link' ? 'link' : 'login',
            'userid' => $mode === 'link' ? (int)$USER->id : 0,
            'wantsurl' => self::clean_wantsurl($wantsurl),
            'time' => time(),
        ];
        $returnto = new \moodle_url('/auth/kabtelegram/callback.php', ['state' => $state]);
        $params = [
            'bot_id' => $config->botid,
            'origin' => self::origin(),
            'return_to' => $returnto->out(false),
        ];
        $lang = substr(current_language(), 0, 2);
        if (preg_match('/^[a-z]{2}$/', $lang)) {
            $params['lang'] = $lang;
        }
        return new \moodle_url(self::OAUTH_URL, $params);
    }

    /**
     * Take the state of the round trip out of the session if it matches: the
     * state works once, whatever the outcome.
     *
     * @param string $state from the callback
     * @return array|null ['mode', 'userid', 'wantsurl', 'time'] or null
     */
    public static function take_state(string $state): ?array {
        global $SESSION;
        $saved = $SESSION->auth_kabtelegram_state ?? null;
        unset($SESSION->auth_kabtelegram_state);
        if (!is_array($saved) || empty($saved['state']) || $state === '' || !hash_equals((string)$saved['state'], $state)) {
            return null;
        }
        if ((int)$saved['time'] + self::STATE_LIFETIME < time()) {
            return null;
        }
        return $saved;
    }

    /**
     * Decode tgAuthResult (base64 or base64url JSON) into its fields.
     *
     * @param string $encoded
     * @return array|null field => string value, or null when it is not a Telegram result
     */
    public static function decode_result(string $encoded): ?array {
        $encoded = trim($encoded);
        if ($encoded === '' || strlen($encoded) > 4096 || !preg_match('/^[A-Za-z0-9_\-+\/=]+$/', $encoded)) {
            return null;
        }
        $b64 = strtr($encoded, '-_', '+/');
        $b64 = rtrim($b64, '=');
        $b64 .= str_repeat('=', (4 - strlen($b64) % 4) % 4);
        $json = base64_decode($b64, true);
        if ($json === false) {
            return null;
        }
        $data = json_decode($json, true, 4, JSON_BIGINT_AS_STRING);
        if (!is_array($data)) {
            return null;
        }
        $out = [];
        foreach ($data as $key => $value) {
            if (!is_string($key) || !in_array($key, self::FIELDS, true)) {
                return null;
            }
            if (is_int($value) || is_string($value)) {
                $out[$key] = (string)$value;
            } else {
                return null; // Telegram sends only numbers and strings; floats, arrays, booleans are not its.
            }
        }
        return $out;
    }

    /**
     * The data-check-string Telegram signs.
     *
     * @param array $fields all fields but hash
     * @return string
     */
    public static function data_check_string(array $fields): string {
        unset($fields['hash']);
        ksort($fields, SORT_STRING);
        $lines = [];
        foreach ($fields as $key => $value) {
            $lines[] = $key . '=' . $value;
        }
        return implode("\n", $lines);
    }

    /**
     * Signature for the fields under a bot token (lower-case hex).
     *
     * @param array $fields
     * @param string $bottoken
     * @return string
     */
    public static function sign(array $fields, string $bottoken): string {
        return hash_hmac('sha256', self::data_check_string($fields), hash('sha256', $bottoken, true));
    }

    /**
     * Check a Telegram result: signature, freshness, shape.
     *
     * @param array $fields from decode_result()
     * @param int|null $now for tests
     * @return array|null ['id' => int, 'username', 'name', 'auth_date' => int] or null
     */
    public static function verify(array $fields, ?int $now = null): ?array {
        $config = self::config();
        $now = $now ?? time();
        if ($config->botid <= 0 || empty($fields['hash']) || !isset($fields['id'], $fields['auth_date'])) {
            return null;
        }
        if (!preg_match('/^[0-9a-f]{64}$/', strtolower($fields['hash']))) {
            return null;
        }
        if (!hash_equals(self::sign($fields, $config->bottoken), strtolower($fields['hash']))) {
            return null;
        }
        if (!preg_match('/^[1-9]\d{0,18}$/', $fields['id']) || !preg_match('/^\d{1,12}$/', $fields['auth_date'])) {
            return null;
        }
        $authdate = (int)$fields['auth_date'];
        if ($authdate > $now + self::CLOCK_SKEW || $authdate < $now - $config->maxage * 60) {
            return null;
        }
        $name = trim(($fields['first_name'] ?? '') . ' ' . ($fields['last_name'] ?? ''));
        return [
            'id' => (int)$fields['id'],
            'username' => \core_text::substr(clean_param($fields['username'] ?? '', PARAM_ALPHANUMEXT), 0, 64),
            'name' => \core_text::substr(clean_param($name, PARAM_TEXT), 0, 255),
            'auth_date' => $authdate,
        ];
    }

    /**
     * The account, if it may be signed in by Telegram right now.
     *
     * Conditions: exists and not deleted, not suspended, confirmed, local
     * (not MNet), not guest, auth method from the plugin setting and enabled.
     *
     * @param int $userid
     * @return \stdClass|null full user record as complete_user_login() expects it
     */
    public static function eligible_user(int $userid): ?\stdClass {
        global $CFG;
        if ($userid <= 0) {
            return null;
        }
        $config = self::config();
        $user = get_complete_user_data('id', $userid, null, false);
        if (!$user || !empty($user->deleted) || !empty($user->suspended) || empty($user->confirmed)) {
            return null;
        }
        if ($user->mnethostid != $CFG->mnet_localhost_id || isguestuser($user)) {
            return null;
        }
        if (!in_array($user->auth, $config->allowedauths, true) || !is_enabled_auth($user->auth)) {
            return null;
        }
        return $user;
    }

    /**
     * Link of a Telegram account.
     *
     * @param int $tgid
     * @return \stdClass|null
     */
    public static function get_link_by_tgid(int $tgid): ?\stdClass {
        global $DB;
        return $DB->get_record(self::TABLE, ['tgid' => $tgid]) ?: null;
    }

    /**
     * Link of a Moodle account.
     *
     * @param int $userid
     * @return \stdClass|null
     */
    public static function get_link_by_userid(int $userid): ?\stdClass {
        global $DB;
        return $DB->get_record(self::TABLE, ['userid' => $userid]) ?: null;
    }

    /**
     * Accept a verified Telegram result for a login: the linked account if it
     * may sign in and the result is newer than the last one accepted for it.
     *
     * The link row is updated (last auth_date, username, name, time) before
     * the caller signs the user in, so the same result is refused next time.
     *
     * @param array $tg from verify()
     * @return array ['status' => 'ok'|'notlinked'|'refused', 'user' => stdClass|null]
     */
    public static function accept_login(array $tg): array {
        $lock = \core\lock\lock_config::get_lock_factory('auth_kabtelegram')->get_lock('tg_' . $tg['id'], 5);
        if (!$lock) {
            return ['status' => 'refused', 'user' => null];
        }
        try {
            return self::accept_login_locked($tg);
        } finally {
            $lock->release();
        }
    }

    /**
     * accept_login() under the per-Telegram lock: two requests with the same
     * answer cannot both pass the lastauthdate check.
     *
     * @param array $tg from verify()
     * @return array
     */
    private static function accept_login_locked(array $tg): array {
        global $DB;
        $link = self::get_link_by_tgid($tg['id']);
        if (!$link) {
            return ['status' => 'notlinked', 'user' => null];
        }
        if ($tg['auth_date'] <= (int)$link->lastauthdate) {
            return ['status' => 'refused', 'user' => null]; // A replay of an answer already used.
        }
        $now = time();
        $update = (object)[
            'id' => $link->id,
            'lastauthdate' => $tg['auth_date'],
            'tgusername' => $tg['username'],
            'tgname' => $tg['name'],
            'timemodified' => $now,
        ];
        $DB->update_record(self::TABLE, $update);
        $user = self::eligible_user((int)$link->userid);
        if ($user === null) {
            return ['status' => 'refused', 'user' => null];
        }
        $DB->set_field(self::TABLE, 'timelastlogin', $now, ['id' => $link->id]);
        return ['status' => 'ok', 'user' => $user];
    }

    /**
     * Link a verified Telegram account to a Moodle account (replacing the
     * account's previous Telegram, if any).
     *
     * @param int $userid
     * @param array $tg from verify() (or the pending copy of it)
     * @param string $source 'user' or 'wp'
     * @return string 'linked' | 'same' (already linked to this account) | 'taken' (linked to another account)
     */
    public static function link(int $userid, array $tg, string $source = 'user'): string {
        global $DB;
        $existing = self::get_link_by_tgid((int)$tg['id']);
        if ($existing && (int)$existing->userid !== $userid) {
            return 'taken';
        }
        $now = time();
        if ($existing) {
            $existing->tgusername = $tg['username'];
            $existing->tgname = $tg['name'];
            $existing->lastauthdate = max((int)$existing->lastauthdate, (int)$tg['auth_date']);
            $existing->timemodified = $now;
            $DB->update_record(self::TABLE, $existing);
            return 'same';
        }
        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records(self::TABLE, ['userid' => $userid]);
        $DB->insert_record(self::TABLE, (object)[
            'userid' => $userid,
            'tgid' => (int)$tg['id'],
            'tgusername' => $tg['username'],
            'tgname' => $tg['name'],
            'source' => $source,
            'lastauthdate' => (int)$tg['auth_date'],
            'timecreated' => $now,
            'timemodified' => $now,
            'timelastlogin' => 0,
        ]);
        $transaction->allow_commit();
        return 'linked';
    }

    /**
     * Remove a Moodle account's Telegram link.
     *
     * @param int $userid
     */
    public static function unlink(int $userid): void {
        global $DB;
        $DB->delete_records(self::TABLE, ['userid' => $userid]);
    }

    /**
     * Remember a verified Telegram that is not linked yet, for the link page
     * the user lands on after signing in some other way.
     *
     * @param array $tg from verify()
     * @param string $wantsurl where the user was going
     */
    public static function set_pending(array $tg, string $wantsurl): void {
        global $SESSION;
        $SESSION->auth_kabtelegram_pending = [
            'tg' => $tg,
            'wantsurl' => self::clean_wantsurl($wantsurl),
            'time' => time(),
        ];
    }

    /**
     * The Telegram waiting to be linked, if it is still fresh.
     *
     * @return array|null ['tg' => array, 'wantsurl' => string, 'time' => int]
     */
    public static function get_pending(): ?array {
        global $SESSION;
        $pending = $SESSION->auth_kabtelegram_pending ?? null;
        if (!is_array($pending) || empty($pending['tg']['id']) || (int)$pending['time'] + self::PENDING_LIFETIME < time()) {
            unset($SESSION->auth_kabtelegram_pending);
            return null;
        }
        return $pending;
    }

    /**
     * Forget the Telegram waiting to be linked.
     */
    public static function clear_pending(): void {
        global $SESSION;
        unset($SESSION->auth_kabtelegram_pending);
    }

    /**
     * How to show a linked Telegram: "Name (@username)".
     *
     * @param string $name
     * @param string $username
     * @return string plain text
     */
    public static function display_name(string $name, string $username): string {
        $name = trim($name);
        $username = trim($username);
        if ($username !== '') {
            return $name !== '' ? $name . ' (@' . $username . ')' : '@' . $username;
        }
        return $name !== '' ? $name : 'Telegram';
    }
}
