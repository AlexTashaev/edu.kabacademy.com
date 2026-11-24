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

namespace local_kabshop\payment;

use local_kabshop\catalog;

/**
 * Оплата товара витрины через core_payment.
 *
 * Зоны: 'product'    — itemid = id товара, основная цена товара;
 *       'productalt' — itemid = id товара, вторая цена (рубли через Robokassa);
 *       'custom'     — itemid = id выбранной суммы (local_kabshop_intent), сумма на выбор покупателя;
 *       'renewal'    — itemid = id подписки (local_kabshop_sub), автосписание.
 * Товар отдаётся в валюте платежа (catalog::variant): цена, валюта и счёт — этой валюты.
 * С 24.11.2026 (аудит) зоны привязаны к покупателю: intent и подписку платит только их владелец,
 * 'renewal' — только автосписание, снятый с продажи товар и уже купленный курс не оплачиваются.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class service_provider implements \core_payment\local\callback\service_provider {

    /**
     * Первая оплата регулярного товара (подписки)? Шлюз помечает такую оплату как повторяющуюся,
     * чтобы касса потом списывала следующие периоды (paygw_robokassa: Recurring=true).
     *
     * @param string $paymentarea
     * @param int $itemid
     * @return bool
     */
    public static function is_recurring(string $paymentarea, int $itemid): bool {
        if ($paymentarea === 'renewal') {
            return false;
        }
        try {
            [$product] = self::resolve($paymentarea, $itemid);
        } catch (\Throwable $e) {
            return false;
        }
        return !empty($product->recurmonths);
    }

    /**
     * Товар, сумма и подписка по зоне оплаты.
     *
     * @param string $paymentarea
     * @param int $itemid
     * @return array [товар в валюте платежа, сумма, подписка|null]
     */
    protected static function resolve(string $paymentarea, int $itemid): array {
        if ($paymentarea === 'custom') {
            $intent = catalog::get_intent($itemid);
            $product = catalog::get_product($intent->productid);
            return [catalog::variant($product, $intent->currency) ?: $product, (float)$intent->amount, null];
        }
        if ($paymentarea === 'renewal') {
            // Автосписание по подписке: itemid — id подписки.
            $sub = \local_kabshop\subscriptions::get($itemid);
            $product = catalog::get_product($sub->productid);
            return [catalog::variant($product, $sub->currency) ?: $product, (float)$sub->amount, $sub];
        }
        $product = catalog::get_product($itemid);
        if ($paymentarea === 'productalt') {
            $product = catalog::alt($product);
            if (!$product) {
                throw new \moodle_exception('nopayment', 'local_kabshop');
            }
        }
        return [$product, (float)$product->price, null];
    }

    /**
     * Живой покупатель: человек в браузере начинает оплату сам.
     *
     * Не живой — CLI и cron (свои автосписания и рекуррент Robokassa идут из cron, где $USER —
     * админ), управляющий кассой и колбэки шлюзов без входа: их проверки ниже не касаются.
     *
     * @return bool
     */
    protected static function live_buyer(): bool {
        return !CLI_SCRIPT && isloggedin() && !isguestuser()
            && !has_capability('local/kabshop:manage', \context_system::instance());
    }

    /**
     * Оплачен ли уже intent: есть покупка по платежу зоны 'custom' с этим itemid.
     *
     * @param int $intentid
     * @return bool
     */
    protected static function intent_paid(int $intentid): bool {
        global $DB;
        return $DB->record_exists_sql(
            "SELECT 1
               FROM {local_kabshop_purchase} p
               JOIN {payments} pm ON pm.id = p.paymentid
              WHERE pm.component = 'local_kabshop' AND pm.paymentarea = 'custom' AND pm.itemid = :itemid",
            ['itemid' => $intentid]);
    }

    /**
     * Запись в журнал для ручного разбора: выдача по оплате не прошла проверку.
     *
     * @param string $message
     */
    protected static function log_refused(string $message): void {
        // KAB: в error.log PHP (его смотрит аудит), без ПДн — только номера.
        error_log('local_kabshop deliver_order refused: ' . $message);
        debugging('local_kabshop: ' . $message, DEBUG_NORMAL);
    }

    /**
     * Цена, валюта и платёжный счёт.
     *
     * @param string $paymentarea
     * @param int $itemid
     * @return \core_payment\local\entities\payable
     */
    public static function get_payable(string $paymentarea, int $itemid): \core_payment\local\entities\payable {
        global $USER, $DB;
        [$product, $amount, $sub] = self::resolve($paymentarea, $itemid);
        // Проверяется только живой покупатель (см. live_buyer): автосписания из cron и колбэки
        // шлюзов без входа не трогаем. Аудит 24.11.2026: раньше зоны не были привязаны к покупателю —
        // по подставленному itemid в окне шлюза оплачивался чужой intent (с чужим купоном и чужими
        // данными жертвователя), чужая подписка (зона 'renewal' обходила и условие записи) и
        // снятый с продажи товар.
        if (self::live_buyer()) {
            $userid = (int) $USER->id;
            if ($paymentarea === 'renewal') {
                // Автосписание платит только cron кассы (charge_one) или рекуррент шлюза.
                throw new \moodle_exception('payforbidden', 'local_kabshop');
            }
            if (empty($product->visible)) {
                throw new \moodle_exception('payforbidden', 'local_kabshop');
            }
            if ($paymentarea === 'custom') {
                $intent = catalog::get_intent($itemid);
                if ((int) $intent->userid !== $userid) {
                    throw new \moodle_exception('payforbidden', 'local_kabshop');
                }
                if (self::intent_paid($itemid)) {
                    // Повторная оплата той же суммы (вторая вкладка, «назад» в браузере). Через
                    // повтор intent купон с лимитом и «один на человека» шёл бы по кругу.
                    throw new \moodle_exception('intentpaid', 'local_kabshop');
                }
                if (!empty($intent->couponid)) {
                    // Купон перепроверяется при оплате: включён, срок, лимит, «один на человека».
                    $coupon = $DB->get_record('local_kabshop_coupon', ['id' => $intent->couponid]);
                    $err = $coupon ? \local_kabshop\coupons::check($coupon, $product, $userid) : 'coupon_expired';
                    if ($err) {
                        throw new \moodle_exception($err, 'local_kabshop');
                    }
                }
            } else if (!empty($product->customamount) || catalog::needs_taxid($product)) {
                // Сумма на выбор и пожертвование с данными жертвователя — только через intent ('custom'),
                // иначе платится минимум товара и квитанция уходит без номера жертвователя.
                throw new \moodle_exception('payforbidden', 'local_kabshop');
            }
            if ($product->courseid && catalog::is_owned($product, $userid)) {
                // Курс уже есть (в том числе по действующей подписке) — вторая оплата не нужна.
                throw new \moodle_exception('alreadyowned', 'local_kabshop');
            }
            // Начать оплату товара с условием записи может только прошедший его — так часть 2 не
            // купить в обход страницы товара. Закрытая запись («ещё не открыта», «завершена»):
            // начать оплату нельзя, а уже начатая до закрытия доходит до выдачи — deliver_order
            // состояние записи не смотрит.
            if (!catalog::prerequisite_ok($product, $userid)) {
                throw new \moodle_exception('noprereq', 'local_kabshop', '', catalog::prerequisite_names($product));
            }
            if (catalog::sale_state($product) !== 'open') {
                throw new \moodle_exception('saleclosed', 'local_kabshop');
            }
        }
        if ($sub) {
            // Продление — валюта и счёт самой подписки: ими подписан запрос на списание, ими же колбэк
            // шлюза проверяет подпись. Цену или счёт товара могли поменять, пока ждали подтверждения.
            return new \core_payment\local\entities\payable((float) $sub->amount, $sub->currency, (int) $sub->accountid);
        }
        return new \core_payment\local\entities\payable($amount, $product->currency, (int)$product->accountid);
    }

    /**
     * Куда вести после оплаты: в курс, если он есть, иначе на страницу товара.
     *
     * @param string $paymentarea
     * @param int $itemid
     * @return \moodle_url
     */
    public static function get_success_url(string $paymentarea, int $itemid): \moodle_url {
        [$product] = self::resolve($paymentarea, $itemid);
        if ($product->courseid) {
            return new \moodle_url('/course/view.php', ['id' => $product->courseid]);
        }
        return catalog::product_url($product, ['paid' => 1]);
    }

    /**
     * Выдать оплаченный товар.
     *
     * @param string $paymentarea
     * @param int $itemid
     * @param int $paymentid
     * @param int $userid
     * @return bool
     */
    public static function deliver_order(string $paymentarea, int $itemid, int $paymentid, int $userid): bool {
        global $DB;
        [$product, , $sub] = self::resolve($paymentarea, $itemid);
        // Платит владелец намерения: intent и подписка — того, кому выдаём. Несовпадение — не
        // выдаём (иначе чужая скидка, чужой номер жертвователя, запись по чужой подписке), а
        // оставляем след для ручного разбора: деньги к этому моменту уже списаны.
        if ($sub && (int) $sub->userid !== $userid) {
            self::log_refused("payment $paymentid: renewal of sub {$sub->id} (user {$sub->userid}) paid by user $userid");
            return false;
        }
        $sublock = null;
        $transaction = null;
        try {
            if ($paymentarea === 'renewal' && $sub) {
                // Продление, подтверждённое колбэком шлюза или сверкой (Robokassa списывает асинхронно —
                // \paygw_robokassa\recurring): сдвинуть дату следующего списания до выдачи, как
                // subscriptions::charge_one() делает для kbb1. Под блокировкой подписки: два подтверждения
                // разных платежей не прочитают одну и ту же дату. Повтор по тому же платежу найдёт
                // покупку и второй раз не продлит.
                $sublock = \core\lock\lock_config::get_lock_factory('local_kabshop')->get_lock('sub_' . $sub->id, 30);
                if (!$sublock) {
                    throw new \moodle_exception('locktimeout');
                }
                // Продление и выдача — одной транзакцией: упала выдача — дата не сдвинута, метка ожидания
                // на месте, и следующая сверка повторит выдачу.
                $transaction = $DB->start_delegated_transaction();
                $sub = \local_kabshop\subscriptions::get((int) $sub->id);
                if ((int) $sub->lastpaymentid !== $paymentid
                        && !$DB->record_exists('local_kabshop_purchase', ['paymentid' => $paymentid])) {
                    // Не активная подписка (отменённая, остановленная после неудач, отозванная) такой и
                    // остаётся: оплаченный период человек получает, но списания не возобновляются.
                    $status = (string) $sub->status;
                    $sub = \local_kabshop\subscriptions::extend($sub, $paymentid);
                    if ($status !== 'active') {
                        $DB->set_field('local_kabshop_sub', 'status', $status, ['id' => $sub->id]);
                        $sub->status = $status;
                    }
                }
            }
            $ok = self::deliver($paymentarea, $itemid, $paymentid, $userid, $product, $sub);
            if ($transaction) {
                $transaction->allow_commit();
            }
            return $ok;
        } catch (\Throwable $e) {
            if ($transaction) {
                $transaction->rollback($e);
            }
            throw $e;
        } finally {
            if ($sublock) {
                $sublock->release();
            }
        }
    }

    /**
     * Выдача после проверок deliver_order().
     *
     * @param string $paymentarea
     * @param int $itemid
     * @param int $paymentid
     * @param int $userid
     * @param \stdClass $product
     * @param \stdClass|null $sub
     * @return bool
     */
    protected static function deliver(string $paymentarea, int $itemid, int $paymentid, int $userid, \stdClass $product,
            ?\stdClass $sub): bool {
        global $DB;
        $opts = [];
        if ($paymentarea === 'custom') {
            // Цена со скидкой по купону тоже идёт через зону 'custom' — учесть купон в заказе.
            $intent = catalog::get_intent($itemid);
            if ((int) $intent->userid !== $userid) {
                self::log_refused("payment $paymentid: intent $itemid (user {$intent->userid}) paid by user $userid");
                return false;
            }
            if (!$DB->record_exists('local_kabshop_purchase', ['paymentid' => $paymentid]) && self::intent_paid($itemid)) {
                // Та же сумма оплачена второй раз (две вкладки до проверки в get_payable). Деньги
                // взяты — заказ заводим, чтобы его было видно, а возврат решает человек.
                error_log("local_kabshop: intent $itemid paid twice, payment $paymentid (delivered, check refund)");
            }
            if (!empty($intent->couponid)) {
                $opts = ['couponid' => (int) $intent->couponid, 'amountoff' => (float) $intent->amountoff];
            }
            // Данные жертвователя — в заказ, а у регулярного пожертвования и в подписку.
            if (!empty($intent->taxtype)) {
                $opts += ['taxtype' => (string) $intent->taxtype, 'taxid' => (string) $intent->taxid];
            }
        }
        return catalog::grant($product, $userid, $paymentid, $sub, $opts) > 0;
    }
}
