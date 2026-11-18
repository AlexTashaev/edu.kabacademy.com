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
 * "manual" and a generated password that is e-mailed to the address.
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
     *                   defaultthankyou, existingpolicy ('enrol'|'login'), phonerequired,
     *                   ipinfotoken, dupalertto, ratelimit, ratewindow (min), retentiondays
     */
    public static function config(): \stdClass {
        // A setting that is missing, empty or zero means "use the default": the admin
        // settings page saves empty fields when it is first saved without touching them.
        $raw = get_config('local_kabsignup');
        $out = new \stdClass();
        $out->courses = self::parse_courses((string)($raw->courses ?? ''));
        $out->campaigns = self::parse_campaigns((string)($raw->campaigns ?? ''));
        $out->prerequisites = self::parse_prerequisites((string)($raw->prerequisites ?? ''));
        $out->defaultthankyou = self::clean_target((string)($raw->defaultthankyou ?? ''));
        $out->existingpolicy = (($raw->existingpolicy ?? '') === 'login') ? 'login' : 'enrol';
        // Settings added in 1.1.0 stay unset until the settings page is saved: unset means default.
        $out->phonerequired = isset($raw->phonerequired) ? (bool)$raw->phonerequired : true;
        $out->ipinfotoken = trim((string)($raw->ipinfotoken ?? ''));
        $out->dupalertto = isset($raw->dupalertto) ? trim((string)$raw->dupalertto) : dupcheck::DEFAULT_TO;
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
     * "Signup conditions" lines: "courseid|required courseid[,courseid...]" —
     * signup for the first course is open only to those enrolled in any of the
     * listed ones (part 2 only after part 1; several ids for several streams of
     * part 1).
     *
     * @param string $raw
     * @return array courseid => int[] required course ids
     */
    public static function parse_prerequisites(string $raw): array {
        $out = [];
        foreach (preg_split('/\R+/', $raw) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line, 2));
            $id = (int)$parts[0];
            $required = array_values(array_unique(array_filter(
                array_map('intval', preg_split('/[\s,;]+/', $parts[1] ?? '')),
                function(int $r) use ($id): bool {
                    return $r > 0 && $r !== $id;
                }
            )));
            if ($id > 0 && $required) {
                $out[$id] = array_merge($out[$id] ?? [], $required);
            }
        }
        return $out;
    }

    /**
     * May this user sign up for the course under the "signup conditions"?
     * True when the course has no condition, when the user is already enrolled
     * in it (then the flow says "already enrolled"), or when the user has an
     * enrolment — any method, active or suspended — in one of the required
     * courses. An unenrolled user has no row left, hence "suspend, do not
     * unenrol" for part-1 students who should keep the right to part 2.
     *
     * @param int $userid 0 for a not yet existing account
     * @param int $courseid
     * @param \stdClass $config
     * @return bool
     */
    public static function prerequisite_ok(int $userid, int $courseid, \stdClass $config): bool {
        global $DB;
        $required = $config->prerequisites[$courseid] ?? [];
        if (!$required) {
            return true;
        }
        if ($userid <= 0) {
            return false;
        }
        [$insql, $params] = $DB->get_in_or_equal(array_merge([$courseid], $required), SQL_PARAMS_NAMED);
        return $DB->record_exists_sql(
            "SELECT 1
               FROM {user_enrolments} ue
               JOIN {enrol} e ON e.id = ue.enrolid
              WHERE ue.userid = :userid AND e.courseid $insql",
            ['userid' => $userid] + $params);
    }

    /**
     * Names of the courses a conditional course requires, for the messages.
     *
     * @param int $courseid
     * @param \stdClass $config
     * @return string «A», «B»
     */
    public static function prerequisite_names(int $courseid, \stdClass $config): string {
        global $DB;
        $required = $config->prerequisites[$courseid] ?? [];
        if (!$required) {
            return '';
        }
        $names = [];
        foreach ($DB->get_records_list('course', 'id', $required, 'id', 'id, fullname') as $c) {
            $names[] = '«' . format_string($c->fullname, true, ['context' => \context_course::instance($c->id)]) . '»';
        }
        return implode(', ', array_unique($names));
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
     * Phone as stored in phone1: digits with a leading "+" when the number was
     * typed international ("+…" or "00…"); '' when it is not a phone number.
     *
     * @param string $raw
     * @return string
     */
    public static function normalise_phone(string $raw): string {
        $raw = trim($raw);
        $digits = preg_replace('/\D/', '', $raw);
        $plus = (strpos($raw, '+') === 0);
        if (!$plus && strpos($digits, '00') === 0) {
            $digits = substr($digits, 2);
            $plus = true;
        }
        if (strlen($digits) < 7 || strlen($digits) > 15) {
            return '';
        }
        return ($plus ? '+' : '') . $digits;
    }

    /**
     * Password for a new account, to be e-mailed. With the site password
     * policy on, the core generator (checked against the policy); otherwise
     * 10 characters from random_int without look-alikes (0/O, 1/l/I), with at
     * least one digit, one lower- and one upper-case letter.
     *
     * @return string
     */
    public static function new_password(): string {
        global $CFG;
        if (!empty($CFG->passwordpolicy)) {
            $length = max(10, (int)($CFG->minpasswordlength ?? 0));
            $password = generate_password($length);
            for ($i = 0; $i < 20; $i++) {
                $error = '';
                if (check_password_policy($password, $error)) {
                    break;
                }
                $password = generate_password($length);
            }
            return $password;
        }
        $alphabet = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $max = strlen($alphabet) - 1;
        do {
            $password = '';
            for ($i = 0; $i < 10; $i++) {
                $password .= $alphabet[random_int(0, $max)];
            }
        } while (!preg_match('/\d/', $password) || !preg_match('/[a-z]/', $password)
            || !preg_match('/[A-Z]/', $password));
        return $password;
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
     * Create the account: auth "manual", confirmed, site language, the phone
     * in phone1 and a generated password that goes to the welcome e-mail.
     * The user_created event fires as usual (local_kabgender sets the gender
     * from the name, the Edwiser bridge tells WordPress).
     *
     * @param string $firstname
     * @param string $lastname may be empty
     * @param string $email
     * @param string $phone already normalised, may be empty
     * @return array [\stdClass the new user table row, string the plain password]
     */
    public static function create_user(string $firstname, string $lastname, string $email, string $phone): array {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/user/lib.php');

        $password = self::new_password();
        $user = new \stdClass();
        $user->auth = 'manual';
        $user->confirmed = 1;
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->email = self::normalise_email($email);
        $user->username = self::unique_username($email);
        $user->firstname = \core_text::substr(trim($firstname), 0, 100);
        $user->lastname = \core_text::substr(trim($lastname), 0, 100);
        $user->phone1 = \core_text::substr($phone, 0, 20);
        $user->password = $password;
        $user->lang = $CFG->lang;

        $userid = user_create_user($user, true, true);
        return [$DB->get_record('user', ['id' => $userid], '*', MUST_EXIST), $password];
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
     * @param string $phone as typed, may be empty
     * @param string $campaign camp= slug ('' when none)
     * @param string $ip requester's IP
     * @return array ['status' => created|enrolled|already|exists|throttled|noprereq, 'user' => \stdClass|null]
     */
    public static function signup(\stdClass $course, string $firstname, string $lastname,
            string $email, string $phone, string $campaign, string $ip): array {
        $config = self::config();
        $ip = \core_text::substr($ip, 0, 45);
        $emailhash = self::email_hash($email);

        if (self::throttled($emailhash, $ip, $config)) {
            return ['status' => 'throttled', 'user' => null];
        }

        $user = self::find_user($email);
        // Signup conditions (part 2 only after part 1): checked before anything is
        // created — a stranger gets no account, since there is no course for it.
        if (!self::prerequisite_ok($user ? (int)$user->id : 0, (int)$course->id, $config)) {
            self::log($course->id, $user ? (int)$user->id : 0, $emailhash, 0, $campaign, 'noprereq', $ip);
            return ['status' => 'noprereq', 'user' => null];
        }
        if ($user !== null && $config->existingpolicy === 'login') {
            self::log($course->id, (int)$user->id, $emailhash, 0, $campaign, 'exists', $ip);
            return ['status' => 'exists', 'user' => null];
        }

        // Rule of 09.11.2026: an account exists only together with an enrolment —
        // without a course there is nothing to do on the site. A new account and
        // its enrolment are one transaction, so a failed enrolment leaves no
        // course-less account behind (events go out after the commit).
        global $DB;
        $new = false;
        $password = null;
        $transaction = $DB->start_delegated_transaction();
        try {
            if ($user === null) {
                [$user, $password] = self::create_user($firstname, $lastname, $email, self::normalise_phone($phone));
                $new = true;
            }
            $enrolled = self::enrol($user, $course);
            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
        }
        self::send_welcome($user, $course, $campaign, $ip, $password);

        $status = $new ? 'created' : $enrolled;
        $logid = self::log($course->id, (int)$user->id, $emailhash, $new ? 1 : 0, $campaign, $status, $ip);
        if ($new) {
            self::queue_after_signup($logid, (int)$user->id, (int)$course->id, $ip);
        }
        return ['status' => $status, 'user' => $user];
    }

    /**
     * Hand the slow part (ipinfo.io, duplicate search, alert e-mail) to cron.
     * A failure here never breaks the signup itself.
     *
     * @param int $logid
     * @param int $userid
     * @param int $courseid
     * @param string $ip
     */
    public static function queue_after_signup(int $logid, int $userid, int $courseid, string $ip): void {
        try {
            $task = new \local_kabsignup\task\after_signup();
            $task->set_custom_data(['logid' => $logid, 'userid' => $userid, 'courseid' => $courseid, 'ip' => $ip]);
            \core\task\manager::queue_adhoc_task($task);
        } catch (\Throwable $e) {
            debugging('local_kabsignup: could not queue after_signup: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    /**
     * The welcome e-mail "you are enrolled". A new account gets its login and
     * password: the person is already signed in, and a one-time link would be
     * dead by the next visit. An existing account (password unknown to us)
     * gets a one-time login link that opens the course (issued by
     * auth_kabmagic; without it — the login page). Tagged for SendGrid
     * statistics per course and campaign.
     *
     * @param \stdClass $user
     * @param \stdClass $course
     * @param string $campaign
     * @param string $ip goes into the auth_kabmagic token log
     * @param string|null $password plain password of a just-created account
     * @return bool
     */
    public static function send_welcome(\stdClass $user, \stdClass $course, string $campaign, string $ip,
            ?string $password = null): bool {
        global $SITE;

        $sitename = format_string($SITE->fullname, true, ['context' => \context_system::instance(), 'escape' => false]);
        $coursename = format_string($course->fullname, true,
            ['context' => \context_course::instance($course->id), 'escape' => false]);
        $courseurl = (new \moodle_url('/course/view.php', ['id' => $course->id]))->out(false);

        $link = null;
        $minutes = 0;
        if ($password === null && class_exists('\auth_kabmagic\manager') && is_enabled_auth('kabmagic')) {
            $link = \auth_kabmagic\manager::issue_link($user, $courseurl, $ip);
            $minutes = \auth_kabmagic\manager::config()->lifetime;
        }

        $a = new \stdClass();
        $a->firstname = $user->firstname;
        $a->coursename = $coursename;
        $a->sitename = $sitename;
        $a->link = $link;
        $a->minutes = $minutes;
        $a->courseurl = $courseurl;
        $a->username = $user->username;
        $a->password = (string)$password;
        $a->requesturl = (new \moodle_url('/auth/kabmagic/request.php'))->out(false);
        $a->loginurl = (new \moodle_url('/login/index.php'))->out(false);

        $ahtml = new \stdClass();
        foreach ((array)$a as $key => $value) {
            $ahtml->$key = s((string)$value);
        }

        $lang = !empty($user->lang) ? $user->lang : current_language();
        $sm = get_string_manager();
        $subject = $sm->get_string('emailsubject', 'local_kabsignup', $a, $lang);
        if ($password !== null) {
            $text = $sm->get_string('emailnewtext', 'local_kabsignup', $a, $lang);
            $html = $sm->get_string('emailnewhtml', 'local_kabsignup', $ahtml, $lang);
        } else if ($link !== null) {
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
     * Somebody already enrolled gets the built-in "you are already enrolled"
     * page instead: the campaign page fires the ad pixels, and a repeat signup
     * must not count as a new registration (same as kab-already-enrolled on WP).
     *
     * @param \stdClass $course
     * @param string $campaign
     * @param array $utm
     * @param bool $already
     * @return \moodle_url
     */
    public static function thankyou_url(\stdClass $course, string $campaign, array $utm, bool $already = false): \moodle_url {
        if ($already) {
            return new \moodle_url('/local/kabsignup/thanks.php', ['id' => $course->id, 'already' => 1] + $utm);
        }
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
     * @return int row id
     */
    public static function log(int $courseid, int $userid, string $emailhash, int $newuser,
            string $campaign, string $status, string $ip): int {
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
        return (int)$DB->insert_record('local_kabsignup_log', $record);
    }

    /**
     * Add markers to a log row's flags (comma-separated, up to 100 chars).
     *
     * @param int $logid
     * @param string[] $flags
     */
    public static function add_flags(int $logid, array $flags): void {
        global $DB;
        if ($logid <= 0 || !$flags) {
            return;
        }
        $current = (string)$DB->get_field('local_kabsignup_log', 'flags', ['id' => $logid]);
        $all = array_values(array_unique(array_merge(array_filter(explode(';', $current)), $flags)));
        $DB->set_field('local_kabsignup_log', 'flags', \core_text::substr(implode(';', $all), 0, 100), ['id' => $logid]);
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
