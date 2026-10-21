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
$intentid = optional_param('intent', 0, PARAM_INT);

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
$activesub = ($loggedin && !empty($product->recurmonths))
    ? \local_kabshop\subscriptions::get_active($product->id, $USER->id) : null;
$free = catalog::is_free($product);

$action = null;
$donateform = null;
$couponform = null;
$couponinfo = null;
$custom = !empty($product->customamount);
// Зафиксированная цена (сумма на выбор или цена по купону) — только своя и для этого товара.
$intent = ($loggedin && $intentid) ? catalog::get_intent($intentid, IGNORE_MISSING) : null;
if ($intent && ((int) $intent->userid !== (int) $USER->id || (int) $intent->productid !== $id)) {
    $intent = null;
}
if ($product->kind === 'external') {
    if (!empty($product->externalurl)) {
        $action = ['href' => $product->externalurl, 'label' => get_string('gotoexternal', 'local_kabshop')];
    }
} else if ($owned && $product->courseid) {
    $action = [
        'href' => (new moodle_url('/course/view.php', ['id' => $product->courseid]))->out(false),
        'label' => get_string('gotocourse', 'local_kabshop'),
    ];
} else if ($activesub) {
    $action = [
        'href' => (new moodle_url('/local/kabshop/subscriptions.php'))->out(false),
        'label' => get_string('managesub', 'local_kabshop'),
    ];
} else if (!$loggedin) {
    $action = [
        'href' => (new moodle_url('/local/kabshop/enrol.php', ['id' => $id]))->out(false),
        'label' => get_string($free ? 'loginandenrol' : ($custom ? 'loginanddonate' : 'loginandbuy'), 'local_kabshop'),
    ];
} else if ($intent && $product->accountid && (float) $intent->amount > 0) {
    // Сумма выбрана или купон применён — кнопка оплаты, окно оплаты открывается сразу.
    $amountstr = \core_payment\helper::get_cost_as_string((float)$intent->amount, $intent->currency);
    $action = [
        'payment' => true,
        'autopay' => true,
        'component' => 'local_kabshop',
        'paymentarea' => 'custom',
        'itemid' => $intent->id,
        'cost' => (float)$intent->amount,
        'successurl' => \local_kabshop\payment\service_provider::get_success_url('custom', $intent->id)->out(false),
        'description' => $name,
        'label' => get_string('payamount', 'local_kabshop', $amountstr),
    ];
    if (!empty($intent->couponid) && ($coupon = $DB->get_record('local_kabshop_coupon', ['id' => $intent->couponid]))) {
        $couponinfo = get_string('couponapplied', 'local_kabshop', (object) [
            'code' => s($coupon->code),
            'off' => \core_payment\helper::get_cost_as_string((float) $intent->amountoff, $intent->currency),
            'was' => \core_payment\helper::get_cost_as_string((float) $product->price, $product->currency),
        ]);
    }
    $changeurl = (new moodle_url('/local/kabshop/product.php', ['id' => $id]))->out(false);
    $PAGE->requires->js_amd_inline("require(['core_payment/gateways_modal'], function(m) {
        m.init();
        var b = document.querySelector('[data-kabshop-autopay]');
        if (b) { b.click(); }
    });");
} else if ($custom && $product->accountid) {
    $presets = [];
    foreach (catalog::presets($product) as $i => $v) {
        $presets[] = [
            'value' => $v,
            'label' => \core_payment\helper::get_cost_as_string($v, $product->currency),
            'checked' => $i === 0,
        ];
    }
    $donateform = [
        'url' => (new moodle_url('/local/kabshop/donate.php'))->out(false),
        'id' => $id,
        'sesskey' => sesskey(),
        'presets' => $presets,
        'min' => max((float)$product->price, 0.01),
        'currency' => $product->currency,
    ];
} else if ($free) {
    $action = [
        'post' => (new moodle_url('/local/kabshop/enrol.php'))->out(false),
        'id' => $id,
        'sesskey' => sesskey(),
        'label' => get_string('enrolfree', 'local_kabshop'),
    ];
} else if ($product->accountid && !$custom) {
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
    $couponform = [
        'url' => (new moodle_url('/local/kabshop/coupon.php'))->out(false),
        'id' => $id,
        'sesskey' => sesskey(),
    ];
}

$data = catalog::card($product);
$data['description'] = format_text($product->description ?? '', $product->descriptionformat, ['context' => $context]);
$data['action'] = $action;
$data['donateform'] = $donateform;
$data['couponform'] = $couponform;
$data['couponinfo'] = $couponinfo;
$data['subinfo'] = $activesub ? get_string('subactive', 'local_kabshop',
    userdate($activesub->nextcharge, get_string('strftimedate', 'langconfig'))) : null;
$data['changeurl'] = !empty($action['autopay']) ? $changeurl : null;
$data['changelabel'] = get_string($couponinfo ? 'couponremove' : 'changeamount', 'local_kabshop');
$data['nopayment'] = !$free && !$product->accountid && $product->kind !== 'external';
$data['paid'] = $paid;
$data['canmanage'] = $canmanage;
$data['editurl'] = (new moodle_url('/local/kabshop/edit_product.php', ['id' => $id]))->out(false);
$data['catalogurl'] = (new moodle_url('/local/kabshop/index.php'))->out(false);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_kabshop/product', $data);
echo $OUTPUT->footer();
