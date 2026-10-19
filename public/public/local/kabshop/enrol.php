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
 * Вход перед покупкой (GET) и бесплатная запись (POST).
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
$PAGE->set_url(new moodle_url('/local/kabshop/enrol.php', ['id' => $id]));
require_login(null, false);
if (isguestuser()) {
    redirect(get_login_url());
}

if (!data_submitted() || !confirm_sesskey()) {
    // Вошли — возвращаем на страницу товара, там уже кнопка покупки.
    redirect($producturl);
}

if (!$product->visible || !catalog::is_free($product) || $product->kind === 'external') {
    redirect($producturl);
}

if (!catalog::is_owned($product, $USER->id)) {
    catalog::grant($product, $USER->id, 0);
}

$target = $product->courseid ? new moodle_url('/course/view.php', ['id' => $product->courseid]) : $producturl;
redirect($target, get_string('enrolled', 'local_kabshop'), null, \core\output\notification::NOTIFY_SUCCESS);
