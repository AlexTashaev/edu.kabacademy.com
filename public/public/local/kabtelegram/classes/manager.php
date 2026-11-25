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
 * Settings with defaults, link targets and the persona label.
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /** Personas when local_kabsignup is not installed (same list as its default). */
    const DEFAULT_PERSONAS = 'edik, mushi, gavriel, senja, rav, itzik';

    /** Extra SendPulse variables: WordPress sent the quiz score as kab_poll_score. */
    const DEFAULT_EXTRAVARS = 'kab_poll_score = quizscore';

    /** Real API hosts; the hidden settings apibase / tgapibase point the dev site at a stub. */
    const SENDPULSE_API = 'https://api.sendpulse.com';
    /** Telegram Bot API. */
    const TELEGRAM_API = 'https://api.telegram.org';

    /**
     * Plugin settings with defaults applied.
     *
     * @return \stdClass botusername, flowid, spclientid, spclientsecret, webhooksecret,
     *                   extravars (SendPulse variable => snapshot key), channelurl, channelchat,
     *                   channelbottoken, defaultlesson, tokendays, retentiondays, apibase, tgapibase
     */
    public static function config(): \stdClass {
        global $CFG;
        $raw = get_config('local_kabtelegram');
        $out = new \stdClass();
        $out->botusername = self::clean_bot((string)($raw->botusername ?? ''));
        $out->flowid = preg_replace('/[^A-Za-z0-9_-]/', '', trim((string)($raw->flowid ?? '')));
        $out->spclientid = trim((string)($raw->spclientid ?? ''));
        $out->spclientsecret = trim((string)($raw->spclientsecret ?? ''));
        $out->webhooksecret = trim((string)($raw->webhooksecret ?? ''));
        // Unset means default; an emptied field means "no extra variables".
        $out->extravars = self::parse_extravars(isset($raw->extravars) ? (string)$raw->extravars : self::DEFAULT_EXTRAVARS);
        $out->channelurl = self::clean_https((string)($raw->channelurl ?? ''));
        $chat = trim((string)($raw->channelchat ?? ''));
        $out->channelchat = preg_match('/^(@[A-Za-z0-9_]{4,64}|-?\d{5,20})$/', $chat) ? $chat : '';
        $out->channelbottoken = trim((string)($raw->channelbottoken ?? ''));
        $lesson = clean_param(trim((string)($raw->defaultlesson ?? '')), PARAM_LOCALURL);
        $out->defaultlesson = (preg_match('#^/[^/\\\\]#', $lesson) || strpos($lesson, $CFG->wwwroot . '/') === 0)
            ? $lesson : '';
        $days = (int)($raw->tokendays ?? 0);
        $out->tokendays = ($days > 0 && $days <= 90) ? $days : 7;
        $retention = trim((string)($raw->retentiondays ?? ''));
        $out->retentiondays = ($retention === '') ? 400 : max(0, (int)$retention);
        $out->apibase = rtrim(self::clean_https((string)($raw->apibase ?? '')) ?: self::SENDPULSE_API, '/');
        $out->tgapibase = rtrim(self::clean_https((string)($raw->tgapibase ?? '')) ?: self::TELEGRAM_API, '/');
        return $out;
    }

    /**
     * Bot username without "@" or a t.me prefix; '' when it does not look like one.
     *
     * @param string $raw
     * @return string
     */
    public static function clean_bot(string $raw): string {
        $raw = trim($raw);
        $raw = preg_replace('#^(https?://)?(t\.me|tg\.pulse\.is)/#i', '', $raw);
        $raw = ltrim($raw, '@');
        return preg_match('/^[A-Za-z0-9_]{4,64}$/', $raw) ? $raw : '';
    }

    /**
     * An absolute https URL or ''.
     *
     * @param string $raw
     * @return string
     */
    public static function clean_https(string $raw): string {
        $url = clean_param(trim($raw), PARAM_URL);
        return (stripos($url, 'https://') === 0) ? $url : '';
    }

    /**
     * Lines "sendpulse_variable = snapshot key". Unknown keys are kept: an empty
     * value is simply not sent.
     *
     * @param string $raw
     * @return array variable => key
     */
    public static function parse_extravars(string $raw): array {
        $out = [];
        foreach (preg_split('/\R/', $raw) as $line) {
            $parts = array_map('trim', explode('=', $line, 2));
            if (count($parts) !== 2) {
                continue;
            }
            [$var, $key] = $parts;
            if (preg_match('/^[A-Za-z0-9_]{1,64}$/', $var) && preg_match('/^[a-z0-9_]{1,32}$/', $key)) {
                $out[$var] = $key;
            }
        }
        return $out;
    }

    /**
     * The persona list and the profile field: taken from local_kabsignup when it
     * is installed, so that both plugins accept the same labels.
     *
     * @return array [string[] slugs, string profile field shortname]
     */
    public static function personas(): array {
        if (class_exists('\local_kabsignup\manager') && method_exists('\local_kabsignup\manager', 'config')) {
            $config = \local_kabsignup\manager::config();
            return [(array)$config->personas, (string)$config->personafield];
        }
        $list = array_values(array_filter(array_map(function(string $p): string {
            return \core_text::strtolower(clean_param(trim($p), PARAM_ALPHANUMEXT));
        }, preg_split('/[\s,;]+/', self::DEFAULT_PERSONAS))));
        return [$list, 'kab_persona'];
    }

    /**
     * Write the persona label into the profile field (overwrites, as WordPress
     * and local_kabsignup do: it is the choice of lounge, not personal data).
     * A label off the list or a missing field — nothing happens.
     *
     * @param int $userid
     * @param string $pres raw pres= value
     * @return string the label written, or ''
     */
    public static function save_persona(int $userid, string $pres): string {
        global $CFG, $DB;
        $pres = \core_text::strtolower(clean_param(trim($pres), PARAM_ALPHANUMEXT));
        [$list, $field] = self::personas();
        if ($userid <= 0 || $pres === '' || $field === '' || !in_array($pres, $list, true)) {
            return '';
        }
        if (!$DB->record_exists('user_info_field', ['shortname' => $field])) {
            return '';
        }
        require_once($CFG->dirroot . '/user/profile/lib.php');
        try {
            profile_save_custom_fields($userid, [$field => $pres]);
            return $pres;
        } catch (\Throwable $e) {
            debugging('local_kabtelegram: persona not saved: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return '';
        }
    }

    /**
     * Value of the src= parameter for statistics: latin letters, digits, - and _.
     *
     * @param string $raw
     * @return string
     */
    public static function clean_place(string $raw): string {
        return \core_text::substr(\core_text::strtolower(clean_param($raw, PARAM_ALPHANUMEXT)), 0, 20);
    }

    /**
     * Full webhook address for SendPulse, with the secret.
     *
     * @return string '' while there is no secret
     */
    public static function webhook_url(): string {
        $secret = self::config()->webhooksecret;
        if ($secret === '') {
            return '';
        }
        return (new \moodle_url('/local/kabtelegram/webhook.php', ['key' => $secret]))->out(false);
    }
}
