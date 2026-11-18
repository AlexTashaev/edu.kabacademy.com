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
 * Сумма на выбор и данные жертвователя: запоминает их и возвращает на страницу товара с кнопкой оплаты.
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
$taxform = catalog::needs_taxid($product) && !catalog::is_free($product);
if (isguestuser() || !$product->visible || (empty($product->customamount) && !$taxform)
        || !catalog::prerequisite_ok($product, $USER->id)) {
    redirect($producturl);
}
require_sesskey();

if (!empty($product->customamount)) {
    // Своя сумма важнее готовой: если поле заполнено — берём его.
    $amount = catalog::chosen_amount(optional_param('customvalue', '', PARAM_RAW_TRIMMED),
        optional_param('preset', 0, PARAM_FLOAT));
    if ($error = catalog::amount_error($product, $amount)) {
        redirect($producturl, $error, null, \core\output\notification::NOTIFY_ERROR);
    }
} else {
    $amount = (float) $product->price;
}

$tax = null;
if ($taxform) {
    $raw = [
        'country' => optional_param('taxcountry', '', PARAM_ALPHA),
        'type' => optional_param('taxtype', '', PARAM_ALPHANUM),
        'id' => optional_param('taxid', '', PARAM_RAW_TRIMMED),
    ];
    [$tax, $error] = catalog::clean_tax($raw['country'], $raw['type'], $raw['id']);
    if ($error) {
        // Введённое не теряем. Номер — персональные данные, поэтому в сессии, а не в адресе.
        $SESSION->local_kabshop_tax = $raw + ['productid' => $id, 'amount' => $amount];
        redirect($producturl, get_string($error, 'local_kabshop'), null, \core\output\notification::NOTIFY_ERROR);
    }
    unset($SESSION->local_kabshop_tax);
}

$intentid = catalog::create_intent($product, $USER->id, $amount, $tax);
redirect(new moodle_url('/local/kabshop/product.php', ['id' => $id, 'intent' => $intentid]));
