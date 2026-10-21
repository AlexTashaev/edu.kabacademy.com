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
 * Подписки с автосписанием.
 *
 * Списывает шлюз: класс \paygw_{gateway}\recurring с методом
 * charge(\stdClass $sub, \stdClass $product, \stdClass $user): array
 * — ['paymentid' => int] при успехе или ['error' => string]. Сейчас он есть у paygw_kbb1.
 * Шлюзы со своим автосписанием (Robokassa) сами присылают оплату — её продлевает grant().
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subscriptions {

    /** Запас доступа после даты списания — на повторные попытки. */
    const GRACE = 3 * DAYSECS;

    /** Сколько неудачных списаний подряд до остановки подписки. */
    const MAX_FAILS = 3;

    /** Пауза между повторами после неудачи. */
    const RETRY_AFTER = 20 * HOURSECS;

    /** Допустимые периоды, месяцев. */
    const PERIODS = [1, 3, 12];

    /**
     * Подпись периода к цене: «в месяц», «раз в 3 месяца», «в год».
     *
     * @param int $months
     * @return string
     */
    public static function period_label(int $months): string {
        return get_string('per' . $months, 'local_kabshop');
    }

    /**
     * Дата + N месяцев (последний день месяца не «перепрыгивает»: 31.01 + 1 → 28/29.02).
     *
     * @param int $ts
     * @param int $months
     * @return int
     */
    public static function add_months(int $ts, int $months): int {
        $d = new \DateTime('@' . $ts);
        $d->setTimezone(\core_date::get_server_timezone_object());
        $day = (int) $d->format('j');
        $d->modify('first day of this month')->modify('+' . $months . ' month');
        $d->setDate((int) $d->format('Y'), (int) $d->format('n'), min($day, (int) $d->format('t')));
        return $d->getTimestamp();
    }

    /**
     * Подписка по id.
     *
     * @param int $id
     * @param int $strictness
     * @return \stdClass|false
     */
    public static function get(int $id, int $strictness = MUST_EXIST) {
        global $DB;
        return $DB->get_record('local_kabshop_sub', ['id' => $id], '*', $strictness);
    }

    /**
     * Активная подписка пользователя на товар.
     *
     * @param int $productid
     * @param int $userid
     * @return \stdClass|null
     */
    public static function get_active(int $productid, int $userid): ?\stdClass {
        global $DB;
        $recs = $DB->get_records('local_kabshop_sub',
            ['productid' => $productid, 'userid' => $userid, 'status' => 'active'], 'id DESC', '*', 0, 1);
        return $recs ? reset($recs) : null;
    }

    /**
     * Подписки пользователя.
     *
     * @param int $userid
     * @return \stdClass[]
     */
    public static function for_user(int $userid): array {
        global $DB;
        return $DB->get_records('local_kabshop_sub', ['userid' => $userid], 'timecreated DESC');
    }

    /**
     * Завести подписку после первой оплаты регулярного товара.
     *
     * @param \stdClass $product
     * @param int $userid
     * @param \stdClass $payment запись payments
     * @return \stdClass
     */
    public static function create(\stdClass $product, int $userid, \stdClass $payment): \stdClass {
        global $DB;
        $now = time();
        $sub = (object) [
            'productid' => $product->id,
            'userid' => $userid,
            'gateway' => $payment->gateway,
            'accountid' => $payment->accountid,
            'firstpaymentid' => $payment->id,
            'lastpaymentid' => $payment->id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'months' => (int) $product->recurmonths,
            'status' => 'active',
            'nextcharge' => self::add_months($now, (int) $product->recurmonths),
            'failcount' => 0,
            'lastattempt' => 0,
            'lasterror' => null,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $sub->id = $DB->insert_record('local_kabshop_sub', $sub);
        return $sub;
    }

    /**
     * Продлить подписку после успешного списания: следующая дата — от прошлой (график
     * не сползает), но не раньше «сейчас + период» после долгого простоя.
     *
     * @param \stdClass $sub
     * @param int $paymentid
     * @return \stdClass
     */
    public static function extend(\stdClass $sub, int $paymentid): \stdClass {
        global $DB;
        $now = time();
        $next = self::add_months((int) $sub->nextcharge, (int) $sub->months);
        if ($next <= $now) {
            $next = self::add_months($now, (int) $sub->months);
        }
        $sub->nextcharge = $next;
        $sub->lastpaymentid = $paymentid;
        $sub->failcount = 0;
        $sub->lasterror = null;
        $sub->status = 'active';
        $sub->timemodified = $now;
        $DB->update_record('local_kabshop_sub', $sub);
        return $sub;
    }

    /**
     * Отменить подписку пользователем: дальше не списываем, доступ — до конца оплаченного.
     *
     * @param int $id
     * @param int $userid
     * @return bool
     */
    public static function cancel(int $id, int $userid): bool {
        global $DB;
        $sub = $DB->get_record('local_kabshop_sub', ['id' => $id, 'userid' => $userid]);
        if (!$sub || $sub->status !== 'active') {
            return false;
        }
        $DB->update_record('local_kabshop_sub', (object) ['id' => $sub->id, 'status' => 'cancelled',
            'timemodified' => time()]);
        return true;
    }

    /**
     * Класс автосписания шлюза или null, если шлюз списывает сам / не умеет.
     *
     * @param string $gateway
     * @return string|null
     */
    public static function charger(string $gateway): ?string {
        $class = '\\paygw_' . $gateway . '\\recurring';
        return class_exists($class) && method_exists($class, 'charge') ? $class : null;
    }

    /**
     * Списать все подписки, у которых подошёл срок. Вызывается задачей по расписанию.
     *
     * @return array [успешно, неудачно]
     */
    public static function charge_due(): array {
        global $DB;
        $now = time();
        $due = $DB->get_records_select('local_kabshop_sub',
            "status = 'active' AND nextcharge <= :now AND lastattempt <= :retry",
            ['now' => $now, 'retry' => $now - self::RETRY_AFTER], 'nextcharge');
        $ok = 0;
        $failed = 0;
        foreach ($due as $sub) {
            if (!self::charger($sub->gateway)) {
                continue; // Шлюз списывает сам (Robokassa) — продление придёт через grant().
            }
            self::charge_one($sub) ? $ok++ : $failed++;
        }
        return [$ok, $failed];
    }

    /**
     * Одно списание по подписке.
     *
     * @param \stdClass $sub
     * @return bool
     */
    public static function charge_one(\stdClass $sub): bool {
        global $DB;
        $class = self::charger($sub->gateway);
        $product = catalog::get_product($sub->productid, IGNORE_MISSING);
        $user = \core_user::get_user($sub->userid);
        if (!$class || !$product || !$user || $user->deleted || $user->suspended) {
            $DB->update_record('local_kabshop_sub', (object) ['id' => $sub->id, 'status' => 'failed',
                'lasterror' => 'product or user unavailable', 'timemodified' => time()]);
            return false;
        }

        // Метка попытки до звонка в шлюз: при сбое посреди списания задача не повторит его сразу.
        $sub->lastattempt = time();
        $DB->update_record('local_kabshop_sub', (object) ['id' => $sub->id, 'lastattempt' => $sub->lastattempt]);
        try {
            $res = $class::charge($sub, $product, $user);
        } catch (\Throwable $e) {
            $res = ['error' => $e->getMessage()];
        }

        if (!empty($res['paymentid'])) {
            $sub = self::extend($sub, (int) $res['paymentid']);
            catalog::grant($product, (int) $sub->userid, (int) $res['paymentid'], $sub);
            return true;
        }

        $sub->failcount = (int) $sub->failcount + 1;
        $sub->lasterror = \core_text::substr((string) ($res['error'] ?? 'unknown error'), 0, 250);
        $sub->status = $sub->failcount >= self::MAX_FAILS ? 'failed' : 'active';
        $sub->timemodified = time();
        $DB->update_record('local_kabshop_sub', $sub);
        mailer::charge_failed($product, $user, $sub);
        return false;
    }
}
