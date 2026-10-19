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
 * Страница товара. Открыта гостям; покупка — после входа.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\catalog;

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$paid = optional_param('paid', 0, PARAM_BOOL);

$context = context_system::instance();
$product = catalog::get_product($id);
$canmanage = has_capability('local/kabshop:manage', $context);
if (!$product->visible && !$canmanage) {
    throw new moodle_exception('productnotfound', 'local_kabshop');
}

$url = new moodle_url('/local/kabshop/product.php', ['id' => $id]);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$name = format_string($product->name, true, ['context' => $context]);
$PAGE->set_title($name);
$PAGE->set_heading($name);
$PAGE->navbar->add(get_string('catalog', 'local_kabshop'), new moodle_url('/local/kabshop/index.php'));
$PAGE->navbar->add($name);

$loggedin = isloggedin() && !isguestuser();
$owned = $loggedin && catalog::is_owned($product, $USER->id);
$free = catalog::is_free($product);

$action = null;
if ($product->kind === 'external') {
    if (!empty($product->externalurl)) {
        $action = ['href' => $product->externalurl, 'label' => get_string('gotoexternal', 'local_kabshop')];
    }
} else if ($owned && $product->courseid) {
    $action = [
        'href' => (new moodle_url('/course/view.php', ['id' => $product->courseid]))->out(false),
        'label' => get_string('gotocourse', 'local_kabshop'),
    ];
} else if (!$loggedin) {
    $action = [
        'href' => (new moodle_url('/local/kabshop/enrol.php', ['id' => $id]))->out(false),
        'label' => get_string($free ? 'loginandenrol' : 'loginandbuy', 'local_kabshop'),
    ];
} else if ($free) {
    $action = [
        'post' => (new moodle_url('/local/kabshop/enrol.php'))->out(false),
        'id' => $id,
        'sesskey' => sesskey(),
        'label' => get_string('enrolfree', 'local_kabshop'),
    ];
} else if ($product->accountid) {
    $PAGE->requires->js_call_amd('core_payment/gateways_modal', 'init');
    $action = [
        'payment' => true,
        'component' => 'local_kabshop',
        'paymentarea' => 'product',
        'itemid' => $id,
        'cost' => (float)$product->price,
        'successurl' => \local_kabshop\payment\service_provider::get_success_url('product', $id)->out(false),
        'description' => $name,
        'label' => get_string('buy', 'local_kabshop'),
    ];
}

$data = catalog::card($product);
$data['description'] = format_text($product->description ?? '', $product->descriptionformat, ['context' => $context]);
$data['action'] = $action;
$data['nopayment'] = !$free && !$product->accountid && $product->kind !== 'external';
$data['paid'] = $paid;
$data['canmanage'] = $canmanage;
$data['editurl'] = (new moodle_url('/local/kabshop/edit_product.php', ['id' => $id]))->out(false);
$data['catalogurl'] = (new moodle_url('/local/kabshop/index.php'))->out(false);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_kabshop/product', $data);
echo $OUTPUT->footer();
