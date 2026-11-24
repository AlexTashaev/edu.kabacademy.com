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

    /**
     * Метка в lasterror: шлюз принял запрос на списание, а результат пришлёт сам (Robokassa —
     * на ResultURL). Подтверждение продлевает подписку через deliver_order('renewal') и снимает
     * метку; не пришло до следующего запуска — попытка считается неудачной.
     */
    const PENDING_PREFIX = 'pending:';

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
     * @param float|null $renewamount сумма следующих списаний, если первая оплата была со скидкой
     * @return \stdClass
     */
    public static function create(\stdClass $product, int $userid, \stdClass $payment, ?float $renewamount = null,
            array $tax = []): \stdClass {
        global $DB;
        $now = time();
        $sub = (object) [
            'productid' => $product->id,
            'userid' => $userid,
            'gateway' => $payment->gateway,
            'accountid' => $payment->accountid,
            'firstpaymentid' => $payment->id,
            'lastpaymentid' => $payment->id,
            'amount' => $renewamount ?? $payment->amount,
            'currency' => $payment->currency,
            'months' => (int) $product->recurmonths,
            'status' => 'active',
            'nextcharge' => self::add_months($now, (int) $product->recurmonths),
            'failcount' => 0,
            'lastattempt' => 0,
            'lasterror' => null,
            // Данные жертвователя первого платежа — автосписания наследуют их, как в WordPress.
            'taxtype' => $tax['type'] ?? null,
            'taxid' => $tax['id'] ?? null,
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
                continue; // Шлюз не умеет автосписание — подписку ведёт кто-то другой.
            }
            $res = self::charge_one($sub);
            if ($res === true) {
                $ok++;
            } else if ($res === false) {
                $failed++;
            }
        }
        return [$ok, $failed];
    }

    /**
     * Одно списание по подписке.
     *
     * @param \stdClass $sub подписка, какой её видел вызывающий (выборка cron или страница админа)
     * @param bool $force «Списать сейчас» админа: не ждать срока; без него — только подошедший срок
     * @return bool|null null — списание этой подписки уже идёт (или она изменилась), ничего не делали
     */
    public static function charge_one(\stdClass $sub, bool $force = false): ?bool {
        // Одна подписка — одно списание одновременно: cron и «Списать сейчас», две вкладки. Не ждём:
        // если списание уже идёт, этот вызов ничего не делает. Отдельный ключ, не 'sub_<id>': на том
        // ждёт колбэк шлюза, который приходит во время нашего запроса, а ветка сверки сама вызывает
        // deliver_order, которому нужен 'sub_<id>' (блокировки Moodle не реентерабельны).
        $lock = \core\lock\lock_config::get_lock_factory('local_kabshop')->get_lock('charge_' . $sub->id, 0);
        if (!$lock) {
            return null;
        }
        try {
            return self::charge_one_locked($sub, $force);
        } finally {
            $lock->release();
        }
    }

    /**
     * Одно списание по подписке под блокировкой charge_<id>.
     *
     * @param \stdClass $seen подписка, какой её видел вызывающий
     * @param bool $force не ждать срока (админ)
     * @return bool|null
     */
    protected static function charge_one_locked(\stdClass $seen, bool $force): ?bool {
        global $DB;
        // Подписку могли отменить или продлить, пока шёл цикл charge_due (в нём HTTP-запросы) или
        // пока админ смотрел страницу: поздний колбэк снял метку ожидания и сдвинул дату. Тогда
        // ничего не делаем — иначе это второе списание за тот же период.
        $sub = $DB->get_record('local_kabshop_sub', ['id' => $seen->id]);
        if (!$sub || $sub->status !== 'active') {
            return false;
        }
        if ((int) $sub->lastpaymentid !== (int) $seen->lastpaymentid
                || (string) $sub->lasterror !== (string) $seen->lasterror) {
            return null;
        }
        $now = time();
        if (!$force && ((int) $sub->nextcharge > $now || (int) $sub->lastattempt > $now - self::RETRY_AFTER)) {
            return null;
        }
        $class = self::charger($sub->gateway);
        $product = catalog::get_product($sub->productid, IGNORE_MISSING);
        $user = \core_user::get_user($sub->userid);
        if (!$class || !$product || !$user || $user->deleted || $user->suspended) {
            $DB->update_record('local_kabshop_sub', (object) ['id' => $sub->id, 'status' => 'failed',
                'lasterror' => 'product or user unavailable', 'timemodified' => time()]);
            return false;
        }

        // Прошлый запрос на списание шлюз принял, а подтверждения пока нет. Прежде чем просить
        // снова, узнать у шлюза, чем он кончился, — иначе потерянное или запоздавшее подтверждение
        // обернётся вторым списанием за тот же период.
        $pendingid = self::pending_paymentid($sub);
        if ($pendingid) {
            $state = method_exists($class, 'pending_state') ? $class::pending_state($sub, $pendingid) : 'failed';
            // Возраст — от создания ожидающего платежа, а не от lastattempt (его сбрасывает админ).
            $created = (int) $DB->get_field('payments', 'timecreated', ['id' => $pendingid]);
            $age = $created ? time() - $created : PHP_INT_MAX;
            if ($state === 'unknown' && $age > 3 * DAYSECS) {
                // Шлюз так и не ответил за трое суток — считаем неудачей.
                $state = 'failed';
            } else if ($state === 'unknown') {
                $state = 'wait';
            }
            if ($state === 'wait' && $age > 7 * DAYSECS) {
                // Неделя «в работе» у шлюза (деньги зачисляются, операция приостановлена): новое
                // списание может обернуться двойным — останавливаем подписку и оставляем разбор
                // администратору (кабинет Robokassa), покупателю не пишем.
                error_log("local_kabshop: sub {$sub->id}: payment $pendingid still pending after 7 days, subscription stopped");
                self::record_failure($sub, $product, $user, 'payment ' . $pendingid . ' pending over 7 days: check it in Robokassa',
                    false, true);
                return false;
            }
            if ($state === 'paid') {
                // Деньги списаны, а подтверждение до нас не дошло — выдаём сами, как сделал бы колбэк.
                try {
                    \core_payment\helper::deliver_order('local_kabshop', 'renewal', (int) $sub->id, $pendingid,
                        (int) $sub->userid);
                    if (method_exists($class, 'mark_paid')) {
                        $class::mark_paid($pendingid);
                    }
                } catch (\Throwable $e) {
                    // Повтор — в следующий запуск; в журнал, чтобы постоянный сбой (удалён курс) увидели.
                    error_log('local_kabshop: sub ' . $sub->id . ': delivery of confirmed payment ' . $pendingid
                        . ' failed: ' . $e->getMessage());
                    return false;
                }
                return true;
            }
            if ($state === 'wait') {
                return true; // Платёж ещё в работе у шлюза — второй запрос не шлём.
            }
            $before = (int) $sub->lastpaymentid;
            $sub = self::record_failure($sub, $product, $user, 'no confirmation for payment ' . $pendingid);
            if (!$sub || (int) $sub->lastpaymentid !== $before) {
                return true; // Пока сверяли, подписку продлило подтверждение.
            }
            if ($sub->status !== 'active') {
                return false;
            }
        }

        // Метка попытки до звонка в шлюз: при сбое посреди списания задача не повторит его сразу.
        $sub->lastattempt = time();
        $DB->update_record('local_kabshop_sub', (object) ['id' => $sub->id, 'lastattempt' => $sub->lastattempt]);
        try {
            $res = $class::charge($sub, $product, $user);
        } catch (\Throwable $e) {
            $res = ['error' => $e->getMessage()];
        }

        if (!empty($res['pending'])) {
            // Шлюз принял запрос, результат придёт колбэком: продлит подписку deliver_order('renewal').
            // Колбэк может успеть раньше — тогда подписка уже продлена этим платежом, метку не ставим.
            $pendingid = (int) $res['pending'];
            $DB->execute('UPDATE {local_kabshop_sub} SET lasterror = ? WHERE id = ? AND lastpaymentid <> ?',
                [self::PENDING_PREFIX . $pendingid, $sub->id, $pendingid]);
            return true;
        }

        if (!empty($res['paymentid'])) {
            $sub = self::extend($sub, (int) $res['paymentid']);
            catalog::grant($product, (int) $sub->userid, (int) $res['paymentid'], $sub);
            return true;
        }

        if (!empty($res['skip'])) {
            // Не неудача покупателя (например, не тот сайт): счётчик не трогаем, пишем в журнал.
            error_log('local_kabshop: sub ' . $sub->id . ' skipped: ' . (string) ($res['error'] ?? ''));
            return false;
        }

        self::record_failure($sub, $product, $user, (string) ($res['error'] ?? 'unknown error'),
            empty($res['nomail']), !empty($res['stop']));
        return false;
    }

    /**
     * Номер платежа, подтверждения которого ждём (метка PENDING_PREFIX в lasterror), или 0.
     *
     * @param \stdClass $sub
     * @return int
     */
    public static function pending_paymentid(\stdClass $sub): int {
        $error = (string) ($sub->lasterror ?? '');
        return strpos($error, self::PENDING_PREFIX) === 0 ? (int) substr($error, strlen(self::PENDING_PREFIX)) : 0;
    }

    /**
     * Записать неудачу списания, не затирая то, что успел сделать колбэк шлюза.
     *
     * Подписка перечитывается и обновляется условием «последний платёж тот же»: если за время
     * запроса её продлил колбэк (lastpaymentid сменился), неудачи нет. Не активная подписка
     * (отменённая, остановленная) статус не меняет.
     *
     * @param \stdClass $sub подписка, какой её видел вызывающий
     * @param \stdClass $product
     * @param \stdClass $user
     * @param string $error
     * @param bool $mail письмо покупателю (по правилам mailer::charge_failed)
     * @param bool $stop остановить подписку сразу (повторять бессмысленно)
     * @return \stdClass|null подписка после записи
     */
    protected static function record_failure(\stdClass $sub, \stdClass $product, \stdClass $user, string $error,
            bool $mail = true, bool $stop = false): ?\stdClass {
        global $DB;
        $fresh = $DB->get_record('local_kabshop_sub', ['id' => $sub->id]);
        if (!$fresh || (int) $fresh->lastpaymentid !== (int) $sub->lastpaymentid || $fresh->status !== 'active') {
            // Продлена колбэком или отменена/остановлена за время запроса — неудачи и письма нет.
            return $fresh ?: null;
        }
        $failcount = (int) $fresh->failcount + 1;
        $status = (string) $fresh->status;
        if ($status === 'active' && ($stop || $failcount >= self::MAX_FAILS)) {
            $status = 'failed';
        }
        $DB->execute('UPDATE {local_kabshop_sub} SET failcount = ?, lasterror = ?, status = ?, timemodified = ?
                       WHERE id = ? AND lastpaymentid = ?',
            [$failcount, \core_text::substr($error, 0, 250), $status, time(), $sub->id, (int) $sub->lastpaymentid]);
        $after = $DB->get_record('local_kabshop_sub', ['id' => $sub->id]);
        if ($after && (int) $after->lastpaymentid === (int) $sub->lastpaymentid && $mail) {
            mailer::charge_failed($product, $user, $after);
        }
        return $after ?: null;
    }
}
