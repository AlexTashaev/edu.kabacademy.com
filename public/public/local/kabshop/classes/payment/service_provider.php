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
 * Зоны: 'product' — itemid = id товара, цена из товара;
 *       'custom'  — itemid = id выбранной суммы (local_kabshop_intent), сумма на выбор покупателя;
 *       'renewal' — itemid = id подписки (local_kabshop_sub), автосписание.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class service_provider implements \core_payment\local\callback\service_provider {

    /**
     * Товар и сумма к оплате для зоны.
     *
     * @param string $paymentarea
     * @param int $itemid
     * @return array [товар, сумма, подписка|null]
     */
    protected static function resolve(string $paymentarea, int $itemid): array {
        if ($paymentarea === 'custom') {
            $intent = catalog::get_intent($itemid);
            return [catalog::get_product($intent->productid), (float)$intent->amount, null];
        }
        if ($paymentarea === 'renewal') {
            // Автосписание по подписке: itemid — id подписки.
            $sub = \local_kabshop\subscriptions::get($itemid);
            return [catalog::get_product($sub->productid), (float)$sub->amount, $sub];
        }
        $product = catalog::get_product($itemid);
        return [$product, (float)$product->price, null];
    }

    /**
     * Цена, валюта и платёжный счёт.
     *
     * @param string $paymentarea
     * @param int $itemid
     * @return \core_payment\local\entities\payable
     */
    public static function get_payable(string $paymentarea, int $itemid): \core_payment\local\entities\payable {
        global $USER;
        [$product, $amount] = self::resolve($paymentarea, $itemid);
        // Начать оплату товара с условием записи может только прошедший его — так часть 2 не купить
        // в обход страницы товара. Проверяется только живой покупатель: автосписания (свои и
        // рекуррент Robokassa из cron, где $USER — админ) и колбэки шлюзов без входа не трогаем.
        if ($paymentarea !== 'renewal' && !CLI_SCRIPT && isloggedin() && !isguestuser()
                && !has_capability('local/kabshop:manage', \context_system::instance())
                && !catalog::prerequisite_ok($product, (int) $USER->id)) {
            throw new \moodle_exception('noprereq', 'local_kabshop', '', catalog::prerequisite_names($product));
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
        return new \moodle_url('/local/kabshop/product.php', ['id' => $product->id, 'paid' => 1]);
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
        [$product, , $sub] = self::resolve($paymentarea, $itemid);
        $opts = [];
        if ($paymentarea === 'custom') {
            // Цена со скидкой по купону тоже идёт через зону 'custom' — учесть купон в заказе.
            $intent = catalog::get_intent($itemid);
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
