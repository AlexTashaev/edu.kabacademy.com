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
 * Possible duplicate accounts of a freshly created one, and the e-mail to
 * support about them. Only reads the user table: the "one account" rule says
 * duplicates are merged by an admin by hand, never automatically.
 *
 * Rules mirror the WordPress mu-plugin kab-dup-alert.php and the batch audit
 * .claude/analysis/account_dedup/dedup_audit.py (repo kabacademy.com) —
 * change them together.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dupcheck {

    /** @var string Default recipient of the alerts. */
    const DEFAULT_TO = 'support@kabacademy.com';

    /** @var string SendGrid category of the alerts (X-SMTPAPI header). */
    const SMTPAPI_CATEGORY = 'moodle-dupalert';

    /** @var int Max matches per signal kind that are looked at. */
    const LIMIT = 20;

    /** @var string[] Popular domains for the typo check (gmai.com → gmail.com). */
    const POPULAR_DOMAINS = [
        'gmail.com', 'mail.ru', 'yandex.ru', 'ya.ru', 'ukr.net', 'bk.ru', 'list.ru',
        'inbox.ru', 'rambler.ru', 'yahoo.com', 'hotmail.com', 'icloud.com', 'inbox.lv',
        'outlook.com', 'walla.co.il',
    ];

    /** @var string[] Mailbox names too common for "same name on another domain". */
    const GENERIC_LOCALS = [
        'info', 'admin', 'mail', 'test', 'office', 'contact', 'support', 'hello', 'post',
        'shop', 'sales', 'master', 'home', 'love', 'mama', 'anna', 'elena', 'irina',
        'olga', 'lena', 'sveta', 'natali', 'marina', 'tanya',
    ];

    /**
     * Phone key: the last 9 digits; '' for junk (too short or long, repeated
     * digits, keyboard runs).
     *
     * @param string $raw
     * @return string
     */
    public static function phone_key(string $raw): string {
        $digits = preg_replace('/\D/', '', $raw);
        if (strpos($digits, '00') === 0) {
            $digits = substr($digits, 2);
        }
        $len = strlen($digits);
        if ($len < 7 || $len > 15) {
            return '';
        }
        $key = substr($digits, -9);
        if (count(array_unique(str_split($key))) <= 2
                || preg_match('/(\d)\1{5,}/', $key)
                || in_array($key, ['123456789', '234567890', '321456987', '987654321'], true)) {
            return '';
        }
        return $key;
    }

    /**
     * [mailbox name without dots, domain], lower-cased, or null.
     *
     * @param string $email
     * @return array|null
     */
    public static function email_parts(string $email): ?array {
        $email = \core_text::strtolower(trim($email));
        $at = strrpos($email, '@');
        if ($at === false || $at === 0) {
            return null;
        }
        $local = str_replace('.', '', substr($email, 0, $at));
        $domain = substr($email, $at + 1);
        if ($local === '' || strpos($domain, '.') === false) {
            return null;
        }
        return [$local, $domain];
    }

    /**
     * The popular domain $domain looks like a typo of, or null.
     *
     * @param string $domain
     * @return string|null
     */
    public static function typo_target(string $domain): ?string {
        if (in_array($domain, self::POPULAR_DOMAINS, true)) {
            return null;
        }
        foreach (self::POPULAR_DOMAINS as $popular) {
            $max = strlen($domain) <= 8 ? 1 : 2;
            if (levenshtein($domain, $popular) <= $max) {
                return $popular;
            }
        }
        return null;
    }

    /**
     * Candidates for duplicates of a user.
     *
     * @param \stdClass $user needs id, email, phone1, phone2
     * @return array of ['user' => \stdClass, 'signal' => email|typo|local|phone]
     */
    public static function find(\stdClass $user): array {
        global $DB;
        if ($DB->get_dbfamily() !== 'mysql') {
            return [];
        }
        $fields = 'id, email, firstname, lastname, phone1, phone2, auth, suspended, timecreated, lastaccess';
        $matches = [];

        $parts = self::email_parts((string)$user->email);
        if ($parts !== null) {
            [$local, $domain] = $parts;
            $rows = $DB->get_records_sql(
                "SELECT $fields FROM {user}
                  WHERE deleted = 0 AND id <> :id
                    AND REPLACE(SUBSTRING_INDEX(LOWER(email), '@', 1), '.', '') = :local",
                ['id' => $user->id, 'local' => $local], 0, self::LIMIT);
            $typo = self::typo_target($domain);
            foreach ($rows as $row) {
                $other = self::email_parts((string)$row->email);
                if ($other === null) {
                    continue;
                }
                $otherdomain = $other[1];
                if ($otherdomain === $domain) {
                    $signal = 'email';
                } else if ($typo === $otherdomain || self::typo_target($otherdomain) === $domain
                        || ($typo !== null && self::typo_target($otherdomain) === $typo)) {
                    $signal = 'typo';
                } else {
                    if (strlen($local) < 5 || in_array($local, self::GENERIC_LOCALS, true) || ctype_digit($local)) {
                        continue;
                    }
                    $signal = 'local';
                }
                $matches[] = ['user' => $row, 'signal' => $signal];
            }
        }

        $keys = array_unique(array_filter([
            self::phone_key((string)($user->phone1 ?? '')),
            self::phone_key((string)($user->phone2 ?? '')),
        ]));
        if ($keys) {
            $clean = function(string $field): string {
                return "RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE($field,"
                    . " '+', ''), '-', ''), ' ', ''), '(', ''), ')', ''), '.', ''), 9)";
            };
            [$insql1, $params1] = $DB->get_in_or_equal(array_values($keys), SQL_PARAMS_NAMED, 'ka');
            [$insql2, $params2] = $DB->get_in_or_equal(array_values($keys), SQL_PARAMS_NAMED, 'kb');
            $rows = $DB->get_records_sql(
                "SELECT $fields FROM {user}
                  WHERE deleted = 0 AND id <> :id
                    AND (" . $clean('phone1') . " $insql1 OR " . $clean('phone2') . " $insql2)",
                ['id' => $user->id] + $params1 + $params2, 0, self::LIMIT);
            foreach ($rows as $row) {
                $matches[] = ['user' => $row, 'signal' => 'phone'];
            }
        }

        return $matches;
    }

    /**
     * Signal kinds present in a match list, in a stable order.
     *
     * @param array $matches
     * @return string[]
     */
    public static function signals(array $matches): array {
        $present = array_unique(array_column($matches, 'signal'));
        return array_values(array_intersect(['email', 'typo', 'local', 'phone'], $present));
    }

    /**
     * Tell support about the candidates (plain text, Russian: the reader is the
     * support team).
     *
     * @param \stdClass $user the new account
     * @param array $matches from find()
     * @param string $to recipient address
     * @param \stdClass|null $course course the signup was for
     * @return bool
     */
    public static function alert(\stdClass $user, array $matches, string $to, ?\stdClass $course = null): bool {
        global $CFG;
        if ($to === '' || !validate_email($to) || !$matches) {
            return false;
        }
        $sm = get_string_manager();
        $lang = 'ru';
        $describe = function(\stdClass $u) use ($CFG): string {
            $name = trim($u->firstname . ' ' . $u->lastname);
            $phone = trim(($u->phone1 ?? '') . ' ' . ($u->phone2 ?? ''));
            return sprintf('#%d  %s  %s%s%s', $u->id, $u->email, $name !== '' ? $name : '—',
                $phone !== '' ? '  тел. ' . $phone : '',
                !empty($u->suspended) ? '  [заблокирован]' : '')
                . "\n    " . $CFG->wwwroot . '/user/profile.php?id=' . $u->id;
        };

        $lines = [];
        $lines[] = $sm->get_string('dupalertintro', 'local_kabsignup', null, $lang);
        if ($course) {
            $lines[] = $sm->get_string('dupalertcourse', 'local_kabsignup', format_string($course->fullname), $lang);
        }
        $lines[] = '';
        $lines[] = $sm->get_string('dupalertnew', 'local_kabsignup', null, $lang);
        $lines[] = '  ' . $describe($user);
        $lines[] = '';
        $lines[] = $sm->get_string('dupalertmatches', 'local_kabsignup', null, $lang);
        $seen = [];
        foreach (array_slice($matches, 0, 10) as $match) {
            $key = $match['user']->id . '-' . $match['signal'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $lines[] = '  ' . $describe($match['user']) . "\n    — "
                . $sm->get_string('dupsignal_' . $match['signal'], 'local_kabsignup', null, $lang);
        }
        if (count($matches) > 10) {
            $lines[] = '  … +' . (count($matches) - 10);
        }
        $lines[] = '';
        $lines[] = $sm->get_string('dupalertfooter', 'local_kabsignup', null, $lang);

        $subject = $sm->get_string('dupalertsubject', 'local_kabsignup', $user->email, $lang);

        $recipient = clone(\core_user::get_noreply_user());
        $recipient->email = $to;
        $recipient->firstname = 'Support';
        $recipient->lastname = '';
        $recipient->mailformat = 0;

        $from = \core_user::get_noreply_user();
        $from->customheaders = ['X-SMTPAPI: ' . json_encode(['category' => [self::SMTPAPI_CATEGORY]])];
        return (bool)email_to_user($recipient, $from, $subject, implode("\n", $lines), '');
    }
}
