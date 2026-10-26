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
 * Creates accounts, enrols them and routes the visitor to a thank-you page.
 *
 * Isolation: existing accounts are never modified — the only things that may
 * happen to them here are a manual enrolment into a whitelisted course and an
 * e-mail with a login link (issued by auth_kabmagic under its own rules).
 * New accounts are created by the standard user_create_user() with auth
 * "manual" and a random password nobody is told.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /** @var string SendGrid category of the welcome e-mails (X-SMTPAPI header). */
    const SMTPAPI_CATEGORY = 'moodle-signup';

    /** @var int Per-IP cap is this many times the per-address limit, same window. */
    const IP_MULTIPLIER = 10;

    /**
     * Plugin settings with defaults applied.
     *
     * @return \stdClass courses (id => thank-you URL or ''), campaigns (slug => URL),
     *                   defaultthankyou, existingpolicy ('enrol'|'login'),
     *                   ratelimit, ratewindow (min), retentiondays
     */
    public static function config(): \stdClass {
        // A setting that is missing, empty or zero means "use the default": the admin
        // settings page saves empty fields when it is first saved without touching them.
        $raw = get_config('local_kabsignup');
        $out = new \stdClass();
        $out->courses = self::parse_courses((string)($raw->courses ?? ''));
        $out->campaigns = self::parse_campaigns((string)($raw->campaigns ?? ''));
        $out->defaultthankyou = self::clean_target((string)($raw->defaultthankyou ?? ''));
        $out->existingpolicy = (($raw->existingpolicy ?? '') === 'login') ? 'login' : 'enrol';
        $out->ratelimit = (int)($raw->ratelimit ?? 0) > 0 ? (int)$raw->ratelimit : 5;
        $out->ratewindow = (int)($raw->ratewindow ?? 0) > 0 ? (int)$raw->ratewindow : 60;
        $retention = trim((string)($raw->retentiondays ?? ''));
        $out->retentiondays = ($retention === '') ? 365 : (int)$retention;
        return $out;
    }

    /**
     * "Courses open for signup" lines: "courseid" or "courseid|thank-you URL".
     *
     * @param string $raw
     * @return array courseid => URL ('' when the course has no page of its own)
     */
    public static function parse_courses(string $raw): array {
        $out = [];
        foreach (preg_split('/\R+/', $raw) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line, 2));
            $id = (int)$parts[0];
            if ($id <= 0) {
                continue;
            }
            $out[$id] = self::clean_target($parts[1] ?? '');
        }
        return $out;
    }

    /**
     * Campaign lines: "slug|thank-you URL".
     *
     * @param string $raw
     * @return array slug (lower-cased) => URL
     */
    public static function parse_campaigns(string $raw): array {
        $out = [];
        foreach (preg_split('/\R+/', $raw) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line, 2));
            $slug = \core_text::strtolower(clean_param($parts[0], PARAM_ALPHANUMEXT));
            $url = self::clean_target($parts[1] ?? '');
            if ($slug === '' || $url === '') {
                continue;
            }
            $out[$slug] = $url;
        }
        return $out;
    }

    /**
     * An admin-supplied thank-you target: an absolute https URL or a local path.
     * Anything else counts as "not set".
     *
     * @param string $url
     * @return string
     */
    public static function clean_target(string $url): string {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (strpos($url, 'https://') === 0 || $url[0] === '/') {
            return $url;
        }
        return '';
    }

    /**
     * The utm_* parameters of the current request, to pass through to the
     * thank-you page.
     *
     * @return array name => value
     */
    public static function utm_params(): array {
        $out = [];
        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $name) {
            $value = trim(optional_param($name, '', PARAM_NOTAGS));
            if ($value !== '') {
                $out[$name] = \core_text::substr($value, 0, 255);
            }
        }
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
        $byemail = $DB->count_records_select('local_kabsignup_log', 'emailhash = :h AND timecreated > :t',
            ['h' => $emailhash, 't' => $since]);
        if ($byemail >= $config->ratelimit) {
            return true;
        }
        if ($ip !== '') {
            $byip = $DB->count_records_select('local_kabsignup_log', 'ip = :ip AND timecreated > :t',
                ['ip' => $ip, 't' => $since]);
            if ($byip >= $config->ratelimit * self::IP_MULTIPLIER) {
                return true;
            }
        }
        return false;
    }

    /**
     * The live local account behind an address, or null. Any auth method and
     * even suspended accounts match: the question here is "is the address
     * taken", not "may it log in" — a duplicate account would be worse.
     *
     * @param string $email
     * @return \stdClass|null user table row
     */
    public static function find_user(string $email): ?\stdClass {
        global $DB, $CFG;
        $email = self::normalise_email($email);
        if ($email === '' || !validate_email($email)) {
            return null;
        }
        $select = $DB->sql_equal('email', ':email', false, false)
            . " AND deleted = 0 AND mnethostid = :mnethostid AND id <> :guestid";
        $params = [
            'email' => $email,
            'mnethostid' => $CFG->mnet_localhost_id,
            'guestid' => (int)($CFG->siteguest ?? 0),
        ];
        $candidates = $DB->get_records_select('user', $select, $params, 'lastaccess DESC, id ASC', '*', 0, 1);
        if (!$candidates) {
            return null;
        }
        return reset($candidates);
    }

    /**
     * A free username derived from the address.
     *
     * @param string $email
     * @return string
     */
    public static function unique_username(string $email): string {
        global $DB, $CFG;
        $base = clean_param(self::normalise_email($email), PARAM_USERNAME);
        if ($base === '') {
            $base = 'student';
        }
        $base = \core_text::substr($base, 0, 90);
        $candidate = $base;
        $suffix = 1;
        while ($DB->record_exists('user', ['username' => $candidate, 'mnethostid' => $CFG->mnet_localhost_id])) {
            $suffix++;
            $candidate = $base . '-' . $suffix;
        }
        return $candidate;
    }

    /**
     * Create the account: auth "manual", confirmed, site language, a random
     * password nobody is told (the account is entered by the login link).
     *
     * @param string $firstname
     * @param string $lastname may be empty
     * @param string $email
     * @return \stdClass the new user table row
     */
    public static function create_user(string $firstname, string $lastname, string $email): \stdClass {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/user/lib.php');

        $user = new \stdClass();
        $user->auth = 'manual';
        $user->confirmed = 1;
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->email = self::normalise_email($email);
        $user->username = self::unique_username($email);
        $user->firstname = \core_text::substr(trim($firstname), 0, 100);
        $user->lastname = \core_text::substr(trim($lastname), 0, 100);
        $user->password = random_string(40);
        $user->lang = $CFG->lang;

        $userid = user_create_user($user, true, true);
        return $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
    }

    /**
     * Enrol the user into the course by the standard manual method. A missing
     * manual instance is created with the defaults; a disabled one is used as
     * is and is NOT silently re-enabled (the enrolment takes effect when the
     * teacher turns the method back on).
     *
     * @param \stdClass $user
     * @param \stdClass $course
     * @return string 'enrolled' or 'already'
     */
    public static function enrol(\stdClass $user, \stdClass $course): string {
        global $DB;
        $context = \context_course::instance($course->id);
        if (is_enrolled($context, $user, '', true)) {
            return 'already';
        }
        $plugin = enrol_get_plugin('manual');
        $instance = null;
        foreach (enrol_get_instances($course->id, false) as $candidate) {
            if ($candidate->enrol === 'manual') {
                $instance = $candidate;
                break;
            }
        }
        if ($instance === null) {
            $instanceid = $plugin->add_default_instance($course);
            $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);
        }
        $roleid = (int)$instance->roleid;
        if ($roleid <= 0) {
            $roleid = (int)get_config('enrol_manual', 'roleid');
        }
        if ($roleid <= 0) {
            $studentroles = get_archetype_roles('student');
            $roleid = $studentroles ? (int)reset($studentroles)->id : 0;
        }
        // Also reactivates a suspended enrolment of the same method.
        $plugin->enrol_user($instance, $user->id, $roleid, time(), 0, ENROL_USER_ACTIVE);
        return 'enrolled';
    }

    /**
     * Handle a submitted signup form.
     *
     * @param \stdClass $course whitelisted course
     * @param string $firstname
     * @param string $lastname
     * @param string $email as typed
     * @param string $campaign camp= slug ('' when none)
     * @param string $ip requester's IP
     * @return array ['status' => created|enrolled|already|exists|throttled, 'user' => \stdClass|null]
     */
    public static function signup(\stdClass $course, string $firstname, string $lastname,
            string $email, string $campaign, string $ip): array {
        $config = self::config();
        $ip = \core_text::substr($ip, 0, 45);
        $emailhash = self::email_hash($email);

        if (self::throttled($emailhash, $ip, $config)) {
            return ['status' => 'throttled', 'user' => null];
        }

        $user = self::find_user($email);
        if ($user !== null && $config->existingpolicy === 'login') {
            self::log($course->id, (int)$user->id, $emailhash, 0, $campaign, 'exists', $ip);
            return ['status' => 'exists', 'user' => null];
        }

        $new = false;
        if ($user === null) {
            $user = self::create_user($firstname, $lastname, $email);
            $new = true;
        }

        $enrolled = self::enrol($user, $course);
        self::send_welcome($user, $course, $campaign, $ip);

        $status = $new ? 'created' : $enrolled;
        self::log($course->id, (int)$user->id, $emailhash, $new ? 1 : 0, $campaign, $status, $ip);
        return ['status' => $status, 'user' => $user];
    }

    /**
     * The welcome e-mail: "you are enrolled" plus a one-time login link that
     * opens the course (issued by auth_kabmagic; without it — the login page).
     * Tagged for SendGrid statistics per course and campaign.
     *
     * @param \stdClass $user
     * @param \stdClass $course
     * @param string $campaign
     * @param string $ip goes into the auth_kabmagic token log
     * @return bool
     */
    public static function send_welcome(\stdClass $user, \stdClass $course, string $campaign, string $ip): bool {
        global $SITE;

        $sitename = format_string($SITE->fullname, true, ['context' => \context_system::instance(), 'escape' => false]);
        $coursename = format_string($course->fullname, true,
            ['context' => \context_course::instance($course->id), 'escape' => false]);
        $courseurl = (new \moodle_url('/course/view.php', ['id' => $course->id]))->out(false);

        $link = null;
        $minutes = 0;
        if (class_exists('\auth_kabmagic\manager') && is_enabled_auth('kabmagic')) {
            $link = \auth_kabmagic\manager::issue_link($user, $courseurl, $ip);
            $minutes = \auth_kabmagic\manager::config()->lifetime;
        }

        $a = new \stdClass();
        $a->firstname = $user->firstname;
        $a->coursename = $coursename;
        $a->sitename = $sitename;
        $a->link = $link;
        $a->minutes = $minutes;
        $a->requesturl = (new \moodle_url('/auth/kabmagic/request.php'))->out(false);
        $a->loginurl = (new \moodle_url('/login/index.php'))->out(false);

        $ahtml = new \stdClass();
        $ahtml->firstname = s($user->firstname);
        $ahtml->coursename = s($coursename);
        $ahtml->sitename = s($sitename);
        $ahtml->link = s((string)$link);
        $ahtml->minutes = $minutes;
        $ahtml->requesturl = s($a->requesturl);
        $ahtml->loginurl = s($a->loginurl);

        $lang = !empty($user->lang) ? $user->lang : current_language();
        $sm = get_string_manager();
        $subject = $sm->get_string('emailsubject', 'local_kabsignup', $a, $lang);
        if ($link !== null) {
            $text = $sm->get_string('emailtext', 'local_kabsignup', $a, $lang);
            $html = $sm->get_string('emailhtml', 'local_kabsignup', $ahtml, $lang);
        } else {
            $text = $sm->get_string('emailtextnolink', 'local_kabsignup', $a, $lang);
            $html = $sm->get_string('emailhtmlnolink', 'local_kabsignup', $ahtml, $lang);
        }

        $categories = [self::SMTPAPI_CATEGORY, 'course-' . $course->id];
        if ($campaign !== '') {
            $categories[] = 'camp-' . $campaign;
        }
        $from = \core_user::get_noreply_user();
        $from->customheaders = ['X-SMTPAPI: ' . json_encode(['category' => $categories])];
        return (bool)email_to_user($user, $from, $subject, $text, $html);
    }

    /**
     * Where to send the visitor after the signup: the campaign page, else the
     * course page, else the default, else the plugin's built-in thanks.php.
     * UTM parameters and the campaign slug ride along.
     *
     * @param \stdClass $course
     * @param string $campaign
     * @param array $utm
     * @return \moodle_url
     */
    public static function thankyou_url(\stdClass $course, string $campaign, array $utm): \moodle_url {
        $config = self::config();
        $base = '';
        if ($campaign !== '' && isset($config->campaigns[$campaign])) {
            $base = $config->campaigns[$campaign];
        } else if (!empty($config->courses[$course->id])) {
            $base = $config->courses[$course->id];
        } else if ($config->defaultthankyou !== '') {
            $base = $config->defaultthankyou;
        }
        if ($base === '') {
            return new \moodle_url('/local/kabsignup/thanks.php', ['id' => $course->id] + $utm);
        }
        $params = $utm;
        if ($campaign !== '') {
            $params['camp'] = $campaign;
        }
        try {
            return new \moodle_url($base, $params);
        } catch (\moodle_exception $e) {
            return new \moodle_url('/local/kabsignup/thanks.php', ['id' => $course->id] + $utm);
        }
    }

    /**
     * One row per handled signup: rate limiting plus campaign statistics.
     *
     * @param int $courseid
     * @param int $userid
     * @param string $emailhash
     * @param int $newuser
     * @param string $campaign
     * @param string $status
     * @param string $ip
     */
    public static function log(int $courseid, int $userid, string $emailhash, int $newuser,
            string $campaign, string $status, string $ip): void {
        global $DB;
        $record = (object)[
            'courseid' => $courseid,
            'userid' => $userid,
            'emailhash' => $emailhash,
            'newuser' => $newuser,
            'campaign' => \core_text::substr($campaign, 0, 64),
            'status' => \core_text::substr($status, 0, 20),
            'ip' => \core_text::substr($ip, 0, 45),
            'timecreated' => time(),
        ];
        $DB->insert_record('local_kabsignup_log', $record);
    }

    /**
     * Drop log rows older than the retention period (scheduled task).
     */
    public static function cleanup(): void {
        global $DB;
        $config = self::config();
        if ($config->retentiondays <= 0) {
            return;
        }
        $DB->delete_records_select('local_kabsignup_log', 'timecreated < :t',
            ['t' => time() - $config->retentiondays * DAYSECS]);
    }
}
