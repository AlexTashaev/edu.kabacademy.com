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

namespace auth_kabmagic;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');

/**
 * Issues, verifies and expires one-time login links.
 *
 * Isolation: nothing here reads or writes passwords or changes a user's auth
 * method. The class only decides whether an existing account may be signed
 * in by a link and hands that account to the login page.
 *
 * @package    auth_kabmagic
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /** @var int Raw token length in bytes (256 bits). */
    const TOKEN_BYTES = 32;

    /** @var int Per-IP cap is this many times the per-address limit, same window. */
    const IP_MULTIPLIER = 10;

    /** @var string SendGrid category of the login e-mails (X-SMTPAPI header). */
    const SMTPAPI_CATEGORY = 'moodle-magic';

    /** @var string Auth methods allowed by default. */
    const DEFAULT_AUTHS = 'manual,email';

    /**
     * Plugin settings with defaults applied.
     *
     * @return \stdClass lifetime (min), ratelimit, ratewindow (min), allowedauths (array)
     */
    public static function config(): \stdClass {
        // A setting that is missing, empty or zero means "use the default": the admin
        // settings page saves empty fields when it is first saved without touching them.
        $config = get_config('auth_kabmagic');
        $out = new \stdClass();
        $out->lifetime = (int)($config->lifetime ?? 0) > 0 ? (int)$config->lifetime : 15;
        $out->ratelimit = (int)($config->ratelimit ?? 0) > 0 ? (int)$config->ratelimit : 3;
        $out->ratewindow = (int)($config->ratewindow ?? 0) > 0 ? (int)$config->ratewindow : 5;
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
     * Lower-cased, trimmed address.
     *
     * @param string $email
     * @return string
     */
    public static function normalise_email(string $email): string {
        return \core_text::strtolower(trim($email));
    }

    /**
     * Hash used for rate limiting; the address itself is not stored.
     *
     * @param string $email
     * @return string
     */
    public static function email_hash(string $email): string {
        return hash('sha256', self::normalise_email($email));
    }

    /**
     * Raw token to its URL form (base64url, no padding).
     *
     * @param string $raw
     * @return string
     */
    public static function encode(string $raw): string {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    /**
     * URL form back to the raw token; null if it is not a token at all.
     *
     * @param string $encoded
     * @return string|null
     */
    public static function decode(string $encoded): ?string {
        $encoded = strtr($encoded, '-_', '+/');
        $encoded .= str_repeat('=', (4 - strlen($encoded) % 4) % 4);
        $raw = base64_decode($encoded, true);
        if ($raw === false || strlen($raw) !== self::TOKEN_BYTES) {
            return null;
        }
        return $raw;
    }

    /**
     * Where to go after the login: an absolute URL inside wwwroot, or '' when
     * the requested one is not ours.
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
        return \core_text::substr($out, 0, 2000);
    }

    /**
     * The account, if it may be signed in by a link right now.
     *
     * Conditions: exists and not deleted, not suspended, confirmed, local
     * (not MNet), auth method from the plugin setting and enabled.
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
     * The eligible account behind an address, or null.
     *
     * Address comparison is case-insensitive. Should several live accounts
     * share the address (allowaccountssameemail), the most recently used wins.
     *
     * @param string $email
     * @return \stdClass|null
     */
    public static function find_user(string $email): ?\stdClass {
        global $DB, $CFG;
        $config = self::config();
        $email = self::normalise_email($email);
        if ($email === '' || !validate_email($email) || empty($config->allowedauths)) {
            return null;
        }
        [$authsql, $authparams] = $DB->get_in_or_equal($config->allowedauths, SQL_PARAMS_NAMED, 'auth');
        $select = $DB->sql_equal('email', ':email', false, false)
            . " AND deleted = 0 AND suspended = 0 AND confirmed = 1 AND mnethostid = :mnethostid AND auth $authsql";
        $params = ['email' => $email, 'mnethostid' => $CFG->mnet_localhost_id] + $authparams;
        $candidates = $DB->get_records_select('user', $select, $params, 'lastaccess DESC, id ASC', 'id', 0, 1);
        if (!$candidates) {
            return null;
        }
        return self::eligible_user((int)reset($candidates)->id);
    }

    /**
     * Has the address or the IP hit its cap within the window?
     *
     * @param string $emailhash
     * @param string $ip
     * @param \stdClass $config
     * @return bool
     */
    public static function throttled(string $emailhash, string $ip, \stdClass $config): bool {
        global $DB;
        $since = time() - $config->ratewindow * 60;
        $byemail = $DB->count_records_select('auth_kabmagic_tokens', 'emailhash = :h AND timecreated > :t',
            ['h' => $emailhash, 't' => $since]);
        if ($byemail >= $config->ratelimit) {
            return true;
        }
        if ($ip !== '') {
            $byip = $DB->count_records_select('auth_kabmagic_tokens', 'ip = :ip AND timecreated > :t',
                ['ip' => $ip, 't' => $since]);
            if ($byip >= $config->ratelimit * self::IP_MULTIPLIER) {
                return true;
            }
        }
        return false;
    }

    /**
     * Handle a request from the form. Silent about the outcome by design: the
     * caller shows the same message whatever happened here.
     *
     * @param string $email address as typed
     * @param string $wantsurl page to open after the login
     * @param string $ip requester's IP
     * @return bool true when a link was actually sent (for tests; never shown to the requester)
     */
    public static function request(string $email, string $wantsurl, string $ip): bool {
        global $DB;
        $config = self::config();
        $ip = \core_text::substr($ip, 0, 45);
        $emailhash = self::email_hash($email);
        if (self::throttled($emailhash, $ip, $config)) {
            return false;
        }

        $record = new \stdClass();
        $record->userid = 0;
        $record->emailhash = $emailhash;
        $record->tokenhash = '';
        $record->wantsurl = null;
        $record->timecreated = time();
        $record->timeused = 0;
        $record->ip = $ip;

        $user = self::find_user($email);
        if ($user === null) {
            // Unknown or ineligible address: remember the attempt for the rate limit, send nothing.
            $DB->insert_record('auth_kabmagic_tokens', $record);
            return false;
        }

        $token = random_bytes(self::TOKEN_BYTES);
        $record->userid = $user->id;
        $record->tokenhash = hash('sha256', $token);
        $record->wantsurl = self::clean_wantsurl($wantsurl);
        $DB->insert_record('auth_kabmagic_tokens', $record);

        $url = new \moodle_url('/auth/kabmagic/login.php', ['t' => self::encode($token)]);
        return self::send_link($user, $url->out(false), $config);
    }

    /**
     * Issue a login link on behalf of another KAB plugin (e.g. the course
     * signup welcome e-mail), without sending anything itself.
     *
     * The account must pass the same eligibility rules as on the request form;
     * the row counts towards the address's rate limit like any issued link.
     *
     * @param \stdClass $user target user (id and email are used)
     * @param string $wantsurl page to open after the login
     * @param string $ip requester's IP, for the log
     * @return string|null the full login URL, or null when the plugin is
     *                     disabled or the account may not be signed in by a link
     */
    public static function issue_link(\stdClass $user, string $wantsurl = '', string $ip = ''): ?string {
        global $DB;
        if (!is_enabled_auth('kabmagic') || self::eligible_user((int)$user->id) === null) {
            return null;
        }
        $token = random_bytes(self::TOKEN_BYTES);
        $record = new \stdClass();
        $record->userid = (int)$user->id;
        $record->emailhash = self::email_hash((string)$user->email);
        $record->tokenhash = hash('sha256', $token);
        $record->wantsurl = self::clean_wantsurl($wantsurl);
        $record->timecreated = time();
        $record->timeused = 0;
        $record->ip = \core_text::substr($ip, 0, 45);
        $DB->insert_record('auth_kabmagic_tokens', $record);
        return (new \moodle_url('/auth/kabmagic/login.php', ['t' => self::encode($token)]))->out(false);
    }

    /**
     * Look a token up without consuming it: the row if it is live, else null.
     *
     * @param string $encoded token from the URL
     * @return \stdClass|null
     */
    public static function peek(string $encoded): ?\stdClass {
        global $DB;
        $raw = self::decode($encoded);
        if ($raw === null) {
            return null;
        }
        $record = $DB->get_record('auth_kabmagic_tokens', ['tokenhash' => hash('sha256', $raw)]);
        if (!$record || $record->tokenhash === '' || (int)$record->timeused !== 0 || (int)$record->userid <= 0) {
            return null;
        }
        if ($record->timecreated + self::config()->lifetime * 60 < time()) {
            return null;
        }
        return $record;
    }

    /**
     * Consume a token: mark it used and return who to sign in and where to go.
     *
     * The row is marked used before the account is re-checked, so a token
     * dies on first use whatever the outcome.
     *
     * @param string $encoded token from the URL
     * @return array|null ['user' => stdClass, 'wantsurl' => string] or null
     */
    public static function consume(string $encoded): ?array {
        global $DB;
        $record = self::peek($encoded);
        if ($record === null) {
            return null;
        }
        $DB->set_field('auth_kabmagic_tokens', 'timeused', time(), ['id' => $record->id, 'timeused' => 0]);
        $user = self::eligible_user((int)$record->userid);
        if ($user === null) {
            return null;
        }
        return ['user' => $user, 'wantsurl' => (string)$record->wantsurl];
    }

    /**
     * Send the e-mail with the link, in the user's language, through Moodle's
     * own mail (email_to_user), tagged for SendGrid statistics.
     *
     * @param \stdClass $user
     * @param string $url the login link
     * @param \stdClass $config
     * @return bool
     */
    public static function send_link(\stdClass $user, string $url, \stdClass $config): bool {
        global $SITE;
        $lang = !empty($user->lang) ? $user->lang : current_language();
        $sitename = format_string($SITE->fullname, true, ['context' => \context_system::instance(), 'escape' => false]);

        $a = new \stdClass();
        $a->firstname = $user->firstname;
        $a->sitename = $sitename;
        $a->link = $url;
        $a->minutes = $config->lifetime;

        $ahtml = new \stdClass();
        $ahtml->firstname = s($user->firstname);
        $ahtml->sitename = s($sitename);
        $ahtml->link = s($url);
        $ahtml->minutes = $config->lifetime;

        $sm = get_string_manager();
        $subject = $sm->get_string('emailsubject', 'auth_kabmagic', $a, $lang);
        $text = $sm->get_string('emailtext', 'auth_kabmagic', $a, $lang);
        $html = $sm->get_string('emailhtml', 'auth_kabmagic', $ahtml, $lang);

        $from = \core_user::get_noreply_user();
        $from->customheaders = ['X-SMTPAPI: ' . json_encode(['category' => [self::SMTPAPI_CATEGORY]])];
        return (bool)email_to_user($user, $from, $subject, $text, $html);
    }

    /**
     * Drop rows older than the longest window plus a day (scheduled task).
     */
    public static function cleanup(): void {
        global $DB;
        $config = self::config();
        $keep = max($config->lifetime, $config->ratewindow) * 60 + DAYSECS;
        $DB->delete_records_select('auth_kabmagic_tokens', 'timecreated < :t', ['t' => time() - $keep]);
    }
}
