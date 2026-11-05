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

namespace local_kabperson;

/**
 * Сборка карточки человека: Moodle (идентичность, обучение) + касса local_kabshop
 * + заказы старого WP kabacademy (адаптер source\kabacademy_wp, этап 2).
 *
 * Только чтение. Контракт JSON — §9 спеки spec_person_card_moodle_2026-10.md
 * (репо kabacademy.com). kabbalah.info и books — этап 5, в sources они «off».
 *
 * @package    local_kabperson
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    /**
     * Словарь ролей Moodle: shortname → [категория, подпись]. Значения дал владелец (2026-07),
     * перенесено из kab-person-card.php 0.2 (WP). Порядок ВАЖЕН: подпись берётся по первой
     * совпавшей роли. Категории: staff = «ведёт», student = «учит», restriction =
     * модификатор-ограничение (персоналом не делает).
     *
     * @return array
     */
    public static function role_dict(): array {
        return [
            // Персонал курса — «ведёт».
            'editingteacher'   => ['staff', 'преподаватель'],
            'teacher2'         => ['staff', 'инструктор'],
            'tutor_ok'         => ['staff', 'тьютор'],
            'curator_pk'       => ['staff', 'куратор'],
            'koordinator'      => ['staff', 'координатор'],
            'moderator_assist' => ['staff', 'модератор-ассистент'],
            'teacher'          => ['staff', 'ассистент'],
            'manager'          => ['staff', 'админ'],
            'coursecreator'    => ['staff', 'создатель курсов'],
            'editor'           => ['staff', 'редактор'],
            'secretary'        => ['staff', 'секретарь'],
            'forummoderator'   => ['staff', 'модератор форума'],
            // Учится — специфичные варианты выше обычного студента.
            'student_pro'        => ['student', 'студент (продлённый доступ)'],
            'studentrestriction' => ['student', 'студент (запрет писать в форуме)'],
            'student'            => ['student', 'студент'],
            // Ограничения — важны оператору, но роль не меняют.
            'nevozmoznootpravlatsoobsenia' => ['restriction', 'запрет личных сообщений'],
        ];
    }

    /**
     * Роль в курсе по списку shortname: ['role' => teacher|student|null, 'label', 'restrictions'].
     * Неизвестную роль не выдаём за преподавателя — role=null, shortname как есть.
     *
     * @param string[] $shortnames
     * @return array
     */
    public static function course_role(array $shortnames): array {
        $dict = self::role_dict();
        $have = [];
        foreach ($shortnames as $sn) {
            $sn = strtolower(trim((string) $sn));
            if ($sn !== '') {
                $have[$sn] = true;
            }
        }
        $restrictions = [];
        foreach (array_keys($have) as $sn) {
            if (isset($dict[$sn]) && $dict[$sn][0] === 'restriction') {
                $restrictions[] = $dict[$sn][1];
            }
        }
        foreach (['staff', 'student'] as $cat) {
            foreach ($dict as $sn => $meta) {
                if ($meta[0] === $cat && isset($have[$sn])) {
                    return [
                        'role'         => ($cat === 'staff') ? 'teacher' : 'student',
                        'label'        => $meta[1],
                        'restrictions' => $restrictions,
                    ];
                }
            }
        }
        $unknown = array_diff(array_keys($have), array_keys($dict));
        return [
            'role'         => null,
            'label'        => $unknown ? implode(', ', $unknown) : null,
            'restrictions' => $restrictions,
        ];
    }

    /**
     * Карточка по email: из MUC-кэша (10 минут) или собрать заново.
     *
     * @param string $email
     * @param bool $refresh true — пересобрать (кнопка «обновить»)
     * @return array карточка по контракту §9
     */
    public static function person(string $email, bool $refresh = false): array {
        $email = \core_text::strtolower(trim($email));
        $cache = \cache::make('local_kabperson', 'person');
        $key = md5($email);
        if ($refresh) {
            $cache->delete($key);
        } else {
            $hit = $cache->get($key);
            if (is_array($hit)) {
                $hit['cached'] = true;
                return $hit;
            }
        }
        $card = self::build($email);
        // Источник упал — карточку не кешируем: следующее открытие попробует снова.
        if (!in_array('error', $card['sources'], true)) {
            $cache->set($key, $card);
        }
        $card['cached'] = false;
        return $card;
    }

    /**
     * Собрать карточку заново (без кэша).
     *
     * @param string $email нормализованный email
     * @return array
     */
    public static function build(string $email): array {
        global $DB;
        $sources = [
            'moodle'        => 'ok',
            'kabshop'       => 'off',
            'kabacademy_wp' => 'off',
            'kabbalah_info' => 'off',   // Этап 5.
            'books'         => 'off',   // Этап 5.
        ];
        $card = [
            'found'        => false,
            'sources'      => $sources,
            'generated_at' => date('c'),
        ];

        $user = self::find_user($email, $dupsignals);
        $wp = source\kabacademy_wp::fetch($email, $user ? (int) $user->id : 0);
        $sources['kabacademy_wp'] = $wp['state'];

        // По email Moodle никого не нашёл, а WP знает связь — тот же человек, в Moodle
        // у него другой адрес (§4: опрашиваем все известные адреса).
        if (!$user && $wp['found'] && $wp['identity']['moodle_user_id'] > 0) {
            $linked = $DB->get_record('user', ['id' => $wp['identity']['moodle_user_id'], 'deleted' => 0]);
            if ($linked) {
                $user = $linked;
                $dupsignals[] = 'moodle_email_differs';
            }
        }

        if (!$user) {
            if ($wp['found']) {
                // Нет в общей базе, но есть история оплат на WP — показываем её (§4).
                $card['identity'] = self::wp_only_identity($wp['identity'], $email);
                $card['payments'] = [
                    'orders'        => $wp['orders'],
                    'subscriptions' => [],
                    'unfinished'    => [],
                    'donor_total'   => $wp['donor'] ? $wp['donor'] : new \stdClass(),
                ];
            }
            $card['sources'] = $sources;
            return $card;
        }
        $card['found'] = true;
        $card['identity'] = self::identity($user, $email, $dupsignals);
        $card['learning'] = self::learning((int) $user->id);
        $card['payments'] = self::payments((int) $user->id, $sources);
        self::merge_wp($card, $wp);
        $card['sources'] = $sources;
        return $card;
    }

    /**
     * Влить в карточку данные старого WP: wp_user_id, адрес WP, сигналы, заказы, пожертвования.
     *
     * @param array $card (by ref) карточка с identity и payments
     * @param array $wp результат source\kabacademy_wp::fetch()
     */
    protected static function merge_wp(array &$card, array $wp): void {
        if (empty($wp['found'])) {
            return;
        }
        $wi = $wp['identity'];
        $id = &$card['identity'];
        $id['wp_user_id'] = $wi['wp_user_id'];
        $known = array_map(function ($e) {
            return \core_text::strtolower((string) $e);
        }, $id['emails']);
        if ($wi['email'] !== '' && !in_array($wi['email'], $known, true)) {
            $id['emails'][] = $wi['email'];
            $id['possible_duplicates'][] = 'wp_email_differs';
        }
        if ($wi['moodle_user_id'] > 0 && $wi['moodle_user_id'] !== (int) $id['moodle_user_id']) {
            $id['possible_duplicates'][] = 'wp_linked_to_moodle:' . $wi['moodle_user_id'];
        }
        foreach ($wi['possible_duplicates'] as $signal) {
            $id['possible_duplicates'][] = 'wp:' . $signal;
        }
        $id['possible_duplicates'] = array_values(array_unique($id['possible_duplicates']));
        unset($id);

        $pm = &$card['payments'];
        $pm['orders'] = array_merge($pm['orders'], $wp['orders']);
        usort($pm['orders'], function ($a, $b) {
            return strtotime((string) $b['date']) <=> strtotime((string) $a['date']);
        });
        $donor = (array) $pm['donor_total'];
        foreach ($wp['donor'] as $cur => $sum) {
            $donor[$cur] = round(($donor[$cur] ?? 0) + $sum, 2);
        }
        $pm['donor_total'] = $donor ? $donor : new \stdClass();
    }

    /**
     * Блок «человек» для того, кого нет в Moodle, но кто есть в WP.
     *
     * @param array $wi identity из адаптера WP
     * @param string $email искомый email
     * @return array
     */
    protected static function wp_only_identity(array $wi, string $email): array {
        $emails = [$email];
        if ($wi['email'] !== '' && $wi['email'] !== $email) {
            $emails[] = $wi['email'];
        }
        $signals = [];
        if ($wi['moodle_user_id'] > 0) {
            $signals[] = 'wp_linked_to_moodle:' . $wi['moodle_user_id']; // Связь есть, а аккаунта нет.
        }
        foreach ($wi['possible_duplicates'] as $signal) {
            $signals[] = 'wp:' . $signal;
        }
        return [
            'name'                => $wi['name'],
            'email'               => $wi['email'] !== '' ? $wi['email'] : $email,
            'emails'              => $emails,
            'moodle_user_id'      => 0,
            'wp_user_id'          => $wi['wp_user_id'],
            'status'              => 'none',
            'registered_at'       => $wi['registered_at'],
            'last_login'          => null,
            'telegram_linked'     => false,
            'possible_duplicates' => array_values(array_unique($signals)),
        ];
    }

    /**
     * Пользователь Moodle по email. Сначала точное совпадение (по индексу),
     * потом без регистра. Несколько совпадений / только удалённый — сигналы дубля.
     *
     * @param string $email
     * @param array|null $dupsignals (out) сигналы для possible_duplicates
     * @return \stdClass|null
     */
    protected static function find_user(string $email, ?array &$dupsignals): ?\stdClass {
        global $DB;
        $dupsignals = [];
        if ($email === '') {
            return null;
        }
        $matches = $DB->get_records('user', ['email' => $email, 'deleted' => 0]);
        if (!$matches) {
            $matches = $DB->get_records_select('user', 'deleted = 0 AND LOWER(email) = :em', ['em' => $email]);
        }
        if (count($matches) > 1) {
            $dupsignals[] = 'duplicate_email:' . implode(',', array_keys($matches));
        }
        if ($matches) {
            // Предпочесть активного и недавно заходившего.
            usort($matches, function ($a, $b) {
                return [$a->suspended, -$a->lastaccess] <=> [$b->suspended, -$b->lastaccess];
            });
            return reset($matches);
        }
        // Остался только удалённый? Показываем состояние честно (email у удалённых
        // Moodle обычно затирает, так что это редкий случай).
        $deleted = $DB->get_records('user', ['email' => $email, 'deleted' => 1]);
        return $deleted ? reset($deleted) : null;
    }

    /**
     * Блок «человек».
     *
     * @param \stdClass $user
     * @param string $email искомый email
     * @param array $dupsignals сигналы из поиска
     * @return array
     */
    protected static function identity(\stdClass $user, string $email, array $dupsignals): array {
        global $DB;
        $uid = (int) $user->id;

        $status = 'active';
        if (!empty($user->deleted)) {
            $status = 'deleted';
        } else if (!empty($user->suspended)) {
            $status = 'suspended';
        }

        // Дубли по имени: полные тёзки среди активных (лёгкая эвристика, как у WP-карточки).
        $fn = \core_text::strtolower(trim((string) $user->firstname));
        $ln = \core_text::strtolower(trim((string) $user->lastname));
        if ($fn !== '' && $ln !== '') {
            $same = $DB->get_records_select('user',
                'deleted = 0 AND id <> :id AND LOWER(firstname) = :fn AND LOWER(lastname) = :ln',
                ['id' => $uid, 'fn' => $fn, 'ln' => $ln], 'id', 'id', 0, 6);
            if ($same) {
                $dupsignals[] = 'same_name:' . implode(',', array_keys($same));
            }
        }

        // Telegram: профильное поле с shortname 'telegram', если заведено.
        $telegram = false;
        $fieldid = $DB->get_field('user_info_field', 'id', ['shortname' => 'telegram']);
        if ($fieldid) {
            $val = $DB->get_field('user_info_data', 'data', ['fieldid' => $fieldid, 'userid' => $uid]);
            $telegram = trim((string) $val) !== '';
        }

        $emails = [(string) $user->email];
        if ($email !== '' && \core_text::strtolower((string) $user->email) !== $email) {
            $emails[] = $email; // Нашли без регистра — показать оба написания.
        }

        $name = trim(fullname($user));
        return [
            'name'                => $name !== '' ? $name : null,
            'email'               => (string) $user->email,
            'emails'              => $emails,
            'moodle_user_id'      => $uid,
            'wp_user_id'          => 0, // Заполняет merge_wp(), если WP нашёл человека.
            'status'              => $status,
            'registered_at'       => self::iso((int) $user->timecreated),
            'last_login'          => self::iso((int) $user->lastaccess),
            'telegram_linked'     => $telegram,
            'possible_duplicates' => array_values($dupsignals),
        ];
    }

    /**
     * Блок «обучение»: курсы (роль, статус записи, прогресс, последний заход, значки) + когорты.
     * Роли читаются внутри Moodle — это переезд local_kabchat_get_user_course_roles (без WS).
     *
     * @param int $uid
     * @return array
     */
    protected static function learning(int $uid): array {
        global $CFG, $DB;
        require_once($CFG->libdir . '/completionlib.php');
        $now = time();

        // Записи на курсы (все способы записи разом).
        $enrolrows = $DB->get_records_sql(
            "SELECT ue.id AS ueid, e.courseid, ue.status AS uestatus, e.status AS estatus,
                    ue.timestart, ue.timeend
               FROM {user_enrolments} ue
               JOIN {enrol} e ON e.id = ue.enrolid
              WHERE ue.userid = :userid", ['userid' => $uid]);
        $enrolstatus = []; // courseid => лучший статус записи.
        $rank = ['active' => 3, 'not_started' => 2, 'expired' => 1, 'suspended' => 0];
        foreach ($enrolrows as $r) {
            if ($r->uestatus != 0 || $r->estatus != 0) {
                $st = 'suspended';
            } else if ($r->timestart && $r->timestart > $now) {
                $st = 'not_started';
            } else if ($r->timeend && $r->timeend < $now) {
                $st = 'expired';
            } else {
                $st = 'active';
            }
            $cid = (int) $r->courseid;
            if (!isset($enrolstatus[$cid]) || $rank[$st] > $rank[$enrolstatus[$cid]]) {
                $enrolstatus[$cid] = $st;
            }
        }

        // Роли по курсам — один запрос (как в local_kabchat, но внутри Moodle).
        $rolerows = $DB->get_records_sql(
            "SELECT ra.id AS raid, ctx.instanceid AS courseid, r.shortname
               FROM {role_assignments} ra
               JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = :clevel
               JOIN {role} r ON r.id = ra.roleid
              WHERE ra.userid = :userid", ['clevel' => CONTEXT_COURSE, 'userid' => $uid]);
        $rolemap = []; // courseid => [shortname, ...].
        foreach ($rolerows as $r) {
            $rolemap[(int) $r->courseid][] = $r->shortname;
        }

        // Последний заход по курсам.
        $lastaccess = $DB->get_records_menu('user_lastaccess', ['userid' => $uid], '', 'courseid, timeaccess');

        // Значки: курсовые (type=2) — к курсу, сайтовые (type=1) — отдельным списком.
        $badgerows = $DB->get_records_sql(
            "SELECT bi.id AS biid, b.name, b.type, b.courseid, bi.dateissued
               FROM {badge_issued} bi
               JOIN {badge} b ON b.id = bi.badgeid
              WHERE bi.userid = :userid
           ORDER BY bi.dateissued", ['userid' => $uid]);
        $coursebadges = [];
        $sitebadges = [];
        foreach ($badgerows as $b) {
            $entry = ['name' => $b->name, 'issued_at' => self::iso((int) $b->dateissued)];
            if ((int) $b->type === 2 && $b->courseid) {
                $coursebadges[(int) $b->courseid][] = $entry;
            } else {
                $sitebadges[] = $entry;
            }
        }

        // Курсы: записан И/ИЛИ имеет роль (роль без записи — тоже сигнал оператору).
        $courseids = array_unique(array_merge(array_keys($enrolstatus), array_keys($rolemap)));
        $courseids = array_diff($courseids, [SITEID]);
        $courses = [];
        if ($courseids) {
            $records = $DB->get_records_list('course', 'id', $courseids);
            // Стабильный порядок — как в навигации Moodle.
            uasort($records, function ($a, $b) {
                return $a->sortorder <=> $b->sortorder;
            });
            foreach ($records as $course) {
                $cid = (int) $course->id;
                $role = self::course_role($rolemap[$cid] ?? []);

                $progress = null;
                $completed = null;
                if (isset($enrolstatus[$cid]) && $course->enablecompletion) {
                    try {
                        $pct = \core_completion\progress::get_course_progress_percentage($course, $uid);
                        $progress = $pct === null ? null : (int) round($pct);
                        $cinfo = new \completion_info($course);
                        if ($cinfo->has_criteria() && $cinfo->is_tracked_user($uid)) {
                            $completed = $cinfo->is_course_complete($uid);
                        }
                    } catch (\Throwable $e) {
                        // Прогресс не посчитался — карточку не валим, поле остаётся null.
                        $progress = null;
                    }
                }

                $courses[] = [
                    'name'          => (string) $course->fullname,
                    'course_id'     => $cid,
                    'role'          => $role['role'],
                    'role_label'    => $role['label'],
                    'restrictions'  => $role['restrictions'],
                    'enrol_status'  => $enrolstatus[$cid] ?? 'none',
                    'progress_pct'  => $progress,
                    'completed'     => $completed,
                    'last_access'   => self::iso((int) ($lastaccess[$cid] ?? 0)),
                    'badges'        => $coursebadges[$cid] ?? [],
                ];
            }
        }

        // Когорты (клубы после переноса мемберств).
        $cohorts = $DB->get_fieldset_sql(
            "SELECT c.name
               FROM {cohort} c
               JOIN {cohort_members} cm ON cm.cohortid = c.id
              WHERE cm.userid = :userid
           ORDER BY c.name", ['userid' => $uid]);

        return [
            'courses'     => $courses,
            'cohorts'     => array_values($cohorts),
            'site_badges' => $sitebadges,
        ];
    }

    /**
     * Блок «оплаты академии»: local_kabshop (0.4.0) + mdl_payments + paygw_kbb1.
     * Схему читаем как есть — касса не окончательная (§6 спеки).
     *
     * @param int $uid
     * @param array $sources (by ref) статус источника kabshop
     * @return array
     */
    protected static function payments(int $uid, array &$sources): array {
        global $DB;
        $out = [
            'orders'        => [],
            'subscriptions' => [],
            'unfinished'    => [],
            'donor_total'   => new \stdClass(),
        ];
        // Не записанную настройку читаем как дефолт «включено» (как в auth_kabmagic
        // e523b78): до первого сохранения настроек get_config отдаёт false.
        $enablekabshop = get_config('local_kabperson', 'enablekabshop');
        if ($enablekabshop !== false && empty($enablekabshop)) {
            return $out;
        }
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('local_kabshop_purchase')) {
            return $out; // Касса не установлена — источник остаётся «off».
        }
        try {
            // Покупки: журнал local_kabshop_purchase + шлюз из payments.
            $rows = $DB->get_records_sql(
                "SELECT pu.id, pu.timecreated, pu.amount, pu.currency, pu.paymentid, pu.status,
                        pu.grantedby, pu.couponid,
                        pr.name AS productname, pr.kind AS productkind,
                        pm.gateway, pm.paymentarea
                   FROM {local_kabshop_purchase} pu
              LEFT JOIN {local_kabshop_product} pr ON pr.id = pu.productid
              LEFT JOIN {payments} pm ON pm.id = pu.paymentid
                  WHERE pu.userid = :userid
               ORDER BY pu.id DESC", ['userid' => $uid]);
            foreach ($rows as $r) {
                if (!empty($r->grantedby)) {
                    $type = 'manual';
                } else if (empty($r->paymentid)) {
                    $type = !empty($r->couponid) ? 'coupon' : 'free';
                } else {
                    $type = ($r->paymentarea === 'renewal') ? 'renewal' : 'paid';
                }
                $out['orders'][] = [
                    'source'   => 'kabshop',
                    'id'       => (int) $r->id, // Номер заказа из письма покупателю.
                    'date'     => self::iso((int) $r->timecreated),
                    'product'  => $r->productname ?? ('#' . $r->id),
                    'kind'     => $r->paymentid > 0 ? ($r->productkind ?: 'course') : 'free',
                    'type'     => $type,
                    'amount'   => round((float) $r->amount, 2),
                    'currency' => (string) $r->currency,
                    'status'   => (string) $r->status,
                    'gateway'  => (string) ($r->gateway ?? ''),
                ];
            }

            // Подписки с автосписанием.
            $rows = $DB->get_records_sql(
                "SELECT s.id, s.amount, s.currency, s.months, s.status, s.nextcharge,
                        s.failcount, s.lastattempt, s.lasterror, s.timecreated, s.gateway,
                        pr.name AS productname
                   FROM {local_kabshop_sub} s
              LEFT JOIN {local_kabshop_product} pr ON pr.id = s.productid
                  WHERE s.userid = :userid
               ORDER BY s.id DESC", ['userid' => $uid]);
            foreach ($rows as $r) {
                $out['subscriptions'][] = [
                    'id'           => (int) $r->id,
                    'product'      => $r->productname ?? ('#' . $r->id),
                    'status'       => (string) $r->status,
                    'amount'       => round((float) $r->amount, 2),
                    'currency'     => (string) $r->currency,
                    'months'       => (int) $r->months,
                    'next_charge'  => $r->status === 'active' ? self::iso((int) $r->nextcharge) : null,
                    'fail_count'   => (int) $r->failcount,
                    'last_attempt' => self::iso((int) $r->lastattempt),
                    'last_error'   => (string) ($r->lasterror ?? ''),
                    'gateway'      => (string) $r->gateway,
                    'started_at'   => self::iso((int) $r->timecreated),
                ];
            }

            // Начатые и не завершённые оплаты: paygw_kbb1 со статусом, отличным от paid.
            if ($dbman->table_exists('paygw_kbb1')) {
                $rows = $DB->get_records_sql(
                    "SELECT k.id, k.component, k.paymentarea, k.itemid, k.amount, k.currency,
                            k.method, k.status, k.timecreated
                       FROM {paygw_kbb1} k
                      WHERE k.userid = :userid AND k.status <> 'paid'
                   ORDER BY k.id DESC", ['userid' => $uid], 0, 50);
                foreach ($rows as $r) {
                    $out['unfinished'][] = [
                        'product'    => self::kbb1_product_label($r),
                        'amount'     => round((float) $r->amount, 2),
                        'currency'   => (string) $r->currency,
                        'status'     => (string) $r->status, // pending|cancel|error.
                        'method'     => (string) $r->method,
                        'started_at' => self::iso((int) $r->timecreated),
                    ];
                }
            }

            // Пожертвования: суммы по валютам, только реальные прошедшие оплаты.
            $rows = $DB->get_records_sql(
                "SELECT pu.currency, SUM(pu.amount) AS total
                   FROM {local_kabshop_purchase} pu
                   JOIN {local_kabshop_product} pr ON pr.id = pu.productid
                  WHERE pu.userid = :userid AND pu.status = 'ok' AND pu.paymentid > 0
                        AND pr.kind = 'donation'
               GROUP BY pu.currency", ['userid' => $uid]);
            $donor = [];
            foreach ($rows as $r) {
                $donor[(string) $r->currency] = round((float) $r->total, 2);
            }
            $out['donor_total'] = $donor ? $donor : new \stdClass();

            $sources['kabshop'] = 'ok';
        } catch (\Throwable $e) {
            // Схема кассы поменялась или запрос упал — блок помечаем, карточку не валим (§6).
            $sources['kabshop'] = 'error';
        }
        return $out;
    }

    /**
     * Название товара для незавершённой оплаты kbb1 по (component, paymentarea, itemid).
     *
     * @param \stdClass $r строка paygw_kbb1
     * @return string
     */
    protected static function kbb1_product_label(\stdClass $r): string {
        global $DB;
        try {
            if ($r->component === 'local_kabshop') {
                $productid = 0;
                if ($r->paymentarea === 'product') {
                    $productid = (int) $r->itemid;
                } else if ($r->paymentarea === 'custom') {
                    $productid = (int) $DB->get_field('local_kabshop_intent', 'productid', ['id' => $r->itemid]);
                } else if ($r->paymentarea === 'renewal') {
                    $productid = (int) $DB->get_field('local_kabshop_sub', 'productid', ['id' => $r->itemid]);
                }
                if ($productid) {
                    $name = $DB->get_field('local_kabshop_product', 'name', ['id' => $productid]);
                    if ($name !== false && $name !== null) {
                        return (string) $name;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Падать из-за подписи не стоит — ниже честный фолбэк.
        }
        return $r->component . '/' . $r->paymentarea . ' #' . $r->itemid;
    }

    /**
     * Timestamp → ISO 8601 (с поясом сервера) или null.
     *
     * @param int $ts
     * @return string|null
     */
    protected static function iso(int $ts): ?string {
        return $ts > 0 ? date('c', $ts) : null;
    }
}
