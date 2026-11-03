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
 * Сумма на выбор: запоминает сумму и возвращает на страницу товара с кнопкой оплаты.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\catalog;

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$product = catalog::get_product($id);
$producturl = new moodle_url('/local/kabshop/product.php', ['id' => $id]);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/kabshop/donate.php', ['id' => $id]));
require_login(null, false);
if (isguestuser() || !$product->visible || empty($product->customamount)) {
    redirect($producturl);
}
require_sesskey();

// Своя сумма важнее готовой: если поле заполнено — берём его.
$custom = trim(optional_param('customvalue', '', PARAM_RAW_TRIMMED));
$amount = $custom !== '' ? (float)str_replace([',', ' '], ['.', ''], $custom) : optional_param('preset', 0, PARAM_FLOAT);

$min = max((float)$product->price, 0.01);
if ($amount < $min || $amount > catalog::MAX_AMOUNT) {
    redirect($producturl, get_string('amountrange', 'local_kabshop', (object)[
        'min' => \core_payment\helper::get_cost_as_string($min, $product->currency),
        'max' => \core_payment\helper::get_cost_as_string(catalog::MAX_AMOUNT, $product->currency),
    ]), null, \core\output\notification::NOTIFY_ERROR);
}

$intentid = catalog::create_intent($product, $USER->id, $amount);
redirect(new moodle_url('/local/kabshop/product.php', ['id' => $id, 'intent' => $intentid]));
