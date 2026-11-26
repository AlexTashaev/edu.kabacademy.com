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

namespace local_kabshop;

/**
 * Ежедневные отчёты кассы секретарю: новые покупки по товарам отчёта — файлом xlsx на почту,
 * как выгрузки заказов WordPress (Advanced Order Export). Спека: .claude/kabshop/spec_staff_reports_2026-11.md.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class staffreports {

    /** Отчёты, пока настройка staffreports не сохранена: «Тема | товары», s<id> — раздел витрины. */
    const DEFAULTS = "Заказы ОК | s1, s2\nВзносы за курс ОК | s12\nВзносы в фонд МАК | s7";

    /** Настройка-метка: номер последней покупки, уже разобранной по отчётам. */
    const LASTID = 'staffreportslastid';

    /**
     * Покупки моложе этого не берём: номер покупки выдаётся до конца транзакции выдачи, и
     * незакоммиченная покупка с меньшим номером иначе осталась бы за меткой навсегда.
     */
    const SETTLE = 120;

    /** Столбцы файла (строки report_col_*). */
    const COLUMNS = ['order', 'date', 'firstname', 'lastname', 'userid', 'email', 'phone', 'city', 'country', 'timezone',
        'gender', 'product', 'amount', 'currency', 'discount', 'coupon', 'type', 'method', 'serviceref', 'txn'];

    /**
     * Отчёты из настройки.
     *
     * @return array[] ['title' => string, 'productids' => int[]]
     */
    public static function reports(): array {
        global $DB;
        $raw = get_config('local_kabshop', 'staffreports');
        if ($raw === false) {
            $raw = self::DEFAULTS;
        }
        $out = [];
        foreach (preg_split('/\R/u', (string) $raw) as $line) {
            if (strpos($line, '|') === false) {
                continue;
            }
            [$title, $spec] = array_map('trim', explode('|', $line, 2));
            if ($title === '') {
                continue;
            }
            $ids = [];
            $sections = [];
            foreach (preg_split('/[\s,;]+/', $spec, -1, PREG_SPLIT_NO_EMPTY) as $token) {
                if (preg_match('/^s(\d+)$/i', $token, $m)) {
                    $sections[] = (int) $m[1];
                } else if (ctype_digit($token)) {
                    $ids[] = (int) $token;
                }
            }
            if ($sections) {
                [$in, $params] = $DB->get_in_or_equal($sections);
                $ids = array_merge($ids, $DB->get_fieldset_select('local_kabshop_product', 'id', "sectionid $in", $params));
            }
            $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
            if ($ids) {
                $out[] = ['title' => $title, 'productids' => $ids];
            }
        }
        return $out;
    }

    /**
     * Получатели (пусто — отчёты выключены).
     *
     * @return string[]
     */
    public static function recipients(): array {
        return mailer::notify_emails((string) get_config('local_kabshop', 'staffreportemails'));
    }

    /**
     * Номер последней разобранной покупки.
     *
     * @return int
     */
    public static function lastid(): int {
        return (int) get_config('local_kabshop', self::LASTID);
    }

    /**
     * Новые покупки по каждому отчёту.
     *
     * @param array $reports из reports()
     * @return array [номер, до которого разобрано, [индекс отчёта => записи]]
     */
    public static function collect(array $reports): array {
        global $DB;
        $lastid = self::lastid();
        $uptoid = (int) $DB->get_field_sql('SELECT MAX(id) FROM {local_kabshop_purchase} WHERE timecreated <= ?',
            [time() - self::SETTLE]);
        $rows = [];
        foreach ($reports as $i => $report) {
            $rows[$i] = $uptoid > $lastid ? self::rows($report['productids'], $lastid, $uptoid) : [];
        }
        return [max($uptoid, $lastid), $rows];
    }

    /**
     * Покупки товаров с номером в (after, upto], со статусом ok.
     *
     * @param int[] $productids
     * @param int $after
     * @param int $upto
     * @return \stdClass[]
     */
    protected static function rows(array $productids, int $after, int $upto): array {
        global $DB;
        [$in, $params] = $DB->get_in_or_equal($productids, SQL_PARAMS_NAMED, 'p');
        $params += ['after' => $after, 'upto' => $upto, 'gender' => 'gender'];
        $names = \core_user\fields::for_name()->get_sql('u');
        $haskbb1 = $DB->get_manager()->table_exists('paygw_kbb1');
        $kbb1sel = $haskbb1 ? ', k.id AS serviceref, k.method, k.txnid' : ', NULL AS serviceref, NULL AS method, NULL AS txnid';
        $kbb1join = $haskbb1 ? 'LEFT JOIN {paygw_kbb1} k ON k.paymentid = pu.paymentid AND pu.paymentid > 0' : '';
        return $DB->get_records_sql(
            "SELECT pu.id, pu.timecreated, pu.amount, pu.currency, pu.paymentid, pu.productid, pu.userid,
                    pu.status, pu.grantedby, pu.couponid, pu.amountoff, cp.code AS couponcode,
                    pr.name AS productname, u.email, u.phone1, u.phone2, u.city, u.country, u.timezone,
                    g.data AS gender {$names->selects},
                    pm.gateway, pm.paymentarea $kbb1sel
               FROM {local_kabshop_purchase} pu
               JOIN {user} u ON u.id = pu.userid
          LEFT JOIN {local_kabshop_product} pr ON pr.id = pu.productid
          LEFT JOIN {payments} pm ON pm.id = pu.paymentid
          LEFT JOIN {local_kabshop_coupon} cp ON cp.id = pu.couponid
               $kbb1join
          LEFT JOIN {user_info_field} gf ON gf.shortname = :gender
          LEFT JOIN {user_info_data} g ON g.userid = u.id AND g.fieldid = gf.id
              WHERE pu.id > :after AND pu.id <= :upto AND pu.status = 'ok' AND pu.productid $in
           ORDER BY pu.id", $params);
    }

    /**
     * Заголовки столбцов.
     *
     * @return string[]
     */
    public static function columns(): array {
        return array_map(fn($c) => get_string('report_col_' . $c, 'local_kabshop'), self::COLUMNS);
    }

    /**
     * Строка файла.
     *
     * @param \stdClass $r запись rows()
     * @return array
     */
    public static function row(\stdClass $r): array {
        static $countries = null;
        $countries = $countries ?? get_string_manager()->get_list_of_countries();
        $phone = trim((string) $r->phone1) !== '' ? $r->phone1 : $r->phone2;
        return [
            (int) $r->id,
            userdate($r->timecreated, '%d.%m.%Y %H:%M', \core_date::get_server_timezone(), false, false),
            (string) $r->firstname,
            (string) $r->lastname,
            (int) $r->userid,
            (string) $r->email,
            trim((string) $phone),
            (string) $r->city,
            $r->country ? ($countries[$r->country] ?? $r->country) : '',
            ((string) $r->timezone !== '' && (string) $r->timezone !== '99') ? $r->timezone : '',
            (string) ($r->gender ?? ''),
            format_string($r->productname ?? ('#' . $r->productid), true,
                ['context' => \context_system::instance(), 'escape' => false]),
            (float) $r->amount,
            (string) $r->currency,
            (float) $r->amountoff > 0 ? (float) $r->amountoff : '',
            (string) ($r->couponcode ?? ''),
            orders::type($r),
            orders::method($r),
            (string) ($r->serviceref ?? ''),
            (string) ($r->txnid ?? ''),
        ];
    }

    /**
     * Имя файла отчёта для письма и скачивания (без расширения).
     *
     * @param array $report
     * @return string
     */
    public static function filename(array $report): string {
        return clean_filename($report['title'] . ' ' . userdate(time(), '%Y-%m-%d', \core_date::get_server_timezone(), false));
    }

    /**
     * Разослать новые покупки по всем отчётам и сдвинуть метку. Метка сдвигается, только если
     * ушли все письма, — иначе следующая отправка повторит эти покупки.
     *
     * @param callable|null $log построчный журнал (mtrace у задачи)
     * @return array [писем отправлено, отчётов с новыми покупками]
     */
    public static function send(?callable $log = null): array {
        global $SITE;
        $log = $log ?? function(string $s): void {
        };
        $to = self::recipients();
        if (!$to) {
            $log('staff reports: no recipients, reports are off');
            return [0, 0];
        }
        $lock = \core\lock\lock_config::get_lock_factory('local_kabshop')->get_lock('staffreports', 0);
        if (!$lock) {
            $log('staff reports: another sending is running');
            return [0, 0];
        }
        try {
            $p = 'local_kabshop';
            $reports = self::reports();
            [$uptoid, $rows] = self::collect($reports);
            $date = userdate(time(), '%d.%m.%Y', \core_date::get_server_timezone(), false);
            $site = format_string($SITE->fullname, true, ['context' => \context_system::instance(), 'escape' => false]);
            $ordersurl = (new \moodle_url('/local/kabshop/orders.php'))->out(false);
            $sent = 0;
            $withrows = 0;
            $ok = true;
            foreach ($reports as $i => $report) {
                if (empty($rows[$i])) {
                    continue;
                }
                $withrows++;
                $name = self::filename($report);
                $path = \core\dataformat::write_data('staffreport' . $i, 'excel', self::columns(),
                    new \ArrayIterator(array_values($rows[$i])), fn($r) => self::row($r));
                $a = (object) ['title' => $report['title'], 'date' => $date, 'count' => count($rows[$i]),
                    'url' => $ordersurl, 'site' => $site];
                $subject = get_string('report_subject', $p, $a);
                $text = get_string('report_body', $p, $a) . "\n\n" . get_string('report_all', $p, $a) . "\n\n"
                    . get_string('mail_signature', $p, $a);
                $html = nl2br(preg_replace('#(https?://[^\s<]+)#', '<a href="$1">$1</a>', s($text)));
                foreach ($to as $email) {
                    $from = \core_user::get_noreply_user();
                    $from->customheaders = ['X-SMTPAPI: ' . json_encode(['category' => ['moodle-shop-report']])];
                    if (email_to_user(mailer::recipient($email), $from, $subject, $text, $html, $path, $name . '.xlsx')) {
                        $sent++;
                    } else {
                        $ok = false;
                        $log("staff reports: mail '{$report['title']}' to $email failed");
                    }
                }
                $log("staff reports: '{$report['title']}' — " . count($rows[$i]) . ' orders');
            }
            if ($ok && $uptoid > self::lastid()) {
                set_config(self::LASTID, $uptoid, 'local_kabshop');
            }
            $log("staff reports: mails sent $sent, reports with orders $withrows, last order " . self::lastid());
            return [$sent, $withrows];
        } finally {
            $lock->release();
        }
    }
}
