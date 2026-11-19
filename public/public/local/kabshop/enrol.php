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
 * Вход перед покупкой (GET) и бесплатная запись (POST). С donate=1 (окно «Безвозмездная
 * помощь») после записи ведёт на оплату взноса.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\catalog;

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$record = catalog::get_product($id);
// Гость нажал «Войти и купить» в выбранной валюте — после входа вернётся на неё.
$product = catalog::variant($record, optional_param('cur', '', PARAM_ALPHA)) ?: $record;
$producturl = catalog::product_url($product);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/kabshop/enrol.php', ['id' => $id]));
require_login(null, false);
if (isguestuser()) {
    redirect(get_login_url());
}

if (!data_submitted() || !confirm_sesskey()) {
    // Вошли — возвращаем на страницу товара, там уже кнопка покупки.
    redirect($producturl);
}

if (!$product->visible || !catalog::is_free($product) || $product->kind === 'external'
        || !catalog::prerequisite_ok($product, $USER->id)) {
    // Не прошедшему условие записи страница товара объяснит, почему.
    redirect($producturl);
}

// Взнос из окна «Безвозмездная помощь» (кнопка «Записаться и сделать взнос») в выбранной там валюте.
$offers = optional_param('donate', 0, PARAM_BOOL) ? catalog::free_donation_offer($product, (int) $USER->id) : null;
$currency = strtoupper(optional_param('currency', '', PARAM_ALPHA));
$offer = $offers ? ($offers[$currency] ?? reset($offers)) : null;

// Запись — всегда и первой: бросивший оплату взноса всё равно записан.
if (!catalog::is_owned($product, $USER->id)) {
    catalog::grant($product, $USER->id, 0);
}

if (!$offer) {
    $target = $product->courseid ? new moodle_url('/course/view.php', ['id' => $product->courseid]) : $producturl;
    redirect($target, get_string('enrolled', 'local_kabshop'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// Дальше — оплата взноса на странице товара-пожертвования.
$donation = $offer['product'];
$donationurl = catalog::product_url($donation);
if ($product->courseid) {
    // Ссылка «Перейти к курсу» на странице пожертвования — до оплаты и после неё.
    $SESSION->local_kabshop_aftercourse = ['productid' => (int) $donation->id, 'courseid' => (int) $product->courseid];
}

// Готовые суммы в окне — по группе на валюту: preset_USD, preset_RUB.
$amount = catalog::chosen_amount(optional_param('customvalue', '', PARAM_RAW_TRIMMED),
    optional_param('preset_' . $offer['product']->currency, 0, PARAM_FLOAT));
if ($error = catalog::amount_error($donation, $amount)) {
    redirect($donationurl, $error, null, \core\output\notification::NOTIFY_ERROR);
}

$message = get_string('enrolleddonate', 'local_kabshop',
    format_string($product->name, true, ['context' => context_system::instance()]));
$tax = null;
if (catalog::needs_taxid($donation)) {
    // Данные для квитанции — с прошлого пожертвования; на странице их видно и можно изменить.
    $prev = catalog::last_tax((int) $USER->id);
    if ($prev) {
        [$tax] = catalog::clean_tax($prev['country'], $prev['type'], $prev['id']);
    }
    if (!$tax) {
        // Данных ещё нет — страница пожертвования с выбранной суммой, форма данных там.
        $SESSION->local_kabshop_tax = ['productid' => (int) $donation->id, 'amount' => $amount,
            'country' => '', 'type' => '', 'id' => ''];
        redirect($donationurl, $message, null, \core\output\notification::NOTIFY_SUCCESS);
    }
}
$intentid = catalog::create_intent($donation, (int) $USER->id, $amount, $tax);
redirect(new moodle_url('/local/kabshop/product.php', ['id' => $donation->id, 'intent' => $intentid]), $message, null,
    \core\output\notification::NOTIFY_SUCCESS);
