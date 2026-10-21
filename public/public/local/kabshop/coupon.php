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

/**
 * Применить промокод: проверка, цена со скидкой (на сервере), переход к оплате.
 * Скидка 100% — товар выдаётся сразу, без оплаты.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\catalog;
use local_kabshop\coupons;

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$code = optional_param('code', '', PARAM_RAW_TRIMMED);
$product = catalog::get_product($id);
$producturl = new moodle_url('/local/kabshop/product.php', ['id' => $id]);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/kabshop/coupon.php', ['id' => $id]));
require_login(null, false);
if (isguestuser() || !$product->visible) {
    redirect($producturl);
}
require_sesskey();

$coupon = coupons::find($code);
$error = $coupon ? coupons::check($coupon, $product, $USER->id) : 'coupon_notfound';
if ($error) {
    redirect($producturl, get_string($error, 'local_kabshop'), null, \core\output\notification::NOTIFY_ERROR);
}

$intent = coupons::create_intent($coupon, $product, $USER->id);
if ((float) $intent->amount <= 0) {
    // Скидка 100%: оплаты нет — выдаём товар и учитываем купон.
    catalog::grant($product, $USER->id, 0, null,
        ['couponid' => (int) $coupon->id, 'amountoff' => (float) $intent->amountoff, 'mail' => true,
         'note' => get_string('coupon_freenote', 'local_kabshop', s($coupon->code))]);
    $target = $product->courseid ? new moodle_url('/course/view.php', ['id' => $product->courseid]) : $producturl;
    redirect($target, get_string($product->courseid ? 'enrolled' : 'coupon_freedone', 'local_kabshop'), null,
        \core\output\notification::NOTIFY_SUCCESS);
}
redirect(new moodle_url('/local/kabshop/product.php', ['id' => $id, 'intent' => $intent->id]));
