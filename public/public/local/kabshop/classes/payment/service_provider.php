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
 * Оплата товара витрины через core_payment. Платёжная зона — 'product', itemid — id товара.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class service_provider implements \core_payment\local\callback\service_provider {

    /**
     * Цена, валюта и платёжный счёт товара.
     *
     * @param string $paymentarea
     * @param int $itemid
     * @return \core_payment\local\entities\payable
     */
    public static function get_payable(string $paymentarea, int $itemid): \core_payment\local\entities\payable {
        $product = catalog::get_product($itemid);
        return new \core_payment\local\entities\payable((float)$product->price, $product->currency,
            (int)$product->accountid);
    }

    /**
     * Куда вести после оплаты: в курс, если он есть, иначе на страницу товара.
     *
     * @param string $paymentarea
     * @param int $itemid
     * @return \moodle_url
     */
    public static function get_success_url(string $paymentarea, int $itemid): \moodle_url {
        $product = catalog::get_product($itemid);
        if ($product->courseid) {
            return new \moodle_url('/course/view.php', ['id' => $product->courseid]);
        }
        return new \moodle_url('/local/kabshop/product.php', ['id' => $itemid, 'paid' => 1]);
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
        $product = catalog::get_product($itemid);
        return catalog::grant($product, $userid, $paymentid);
    }
}
