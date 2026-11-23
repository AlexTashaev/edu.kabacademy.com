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
 * Создание и правка товара.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$id = optional_param('id', 0, PARAM_INT);

admin_externalpage_setup('local_kabshop_manage', '', null, new moodle_url('/local/kabshop/edit_product.php', ['id' => $id]));
$context = context_system::instance();
require_capability('local/kabshop:manage', $context);

$manageurl = new moodle_url('/local/kabshop/manage.php');
$editoroptions = ['maxfiles' => 0, 'context' => $context];
$fileoptions = ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['web_image']];

if ($id) {
    $product = $DB->get_record('local_kabshop_product', ['id' => $id], '*', MUST_EXIST);
} else {
    $product = (object)['id' => 0, 'description' => '', 'descriptionformat' => FORMAT_HTML, 'visible' => 1,
        'currency' => 'USD', 'kind' => 'course', 'price' => 0, 'sortorder' => 0, 'altprice' => 0, 'altaccountid' => 0,
        'startdate' => 0, 'salestate' => 'auto', 'saleopen' => 0, 'saleclose' => 0];
}
$product = file_prepare_standard_editor($product, 'description', $editoroptions, $context);
$product = file_prepare_standard_filemanager($product, 'image', $fileoptions, $context, 'local_kabshop', 'image',
    $product->id ?: null);

$form = new \local_kabshop\form\product_form();
$form->set_data($product);

if ($form->is_cancelled()) {
    redirect($manageurl);
} else if ($data = $form->get_data()) {
    $data = file_postupdate_standard_editor($data, 'description', $editoroptions, $context);
    $data->enrolperiod = (int)($data->enrolperiod ?? 0);
    $data->courseid = (int)$data->courseid;
    $data->sku = trim((string) ($data->sku ?? '')) ?: null;
    $data->requirecourses = implode(',', \local_kabshop\catalog::parse_courseids((string) ($data->requirecourses ?? '')))
        ?: null;
    // Взнос за курс — всегда сумма на выбор.
    if ($data->kind === 'contribution') {
        $data->customamount = 1;
    }
    $data->altcurrency = trim((string) ($data->altcurrency ?? '')) ?: null;
    if (!$data->altcurrency) {
        $data->altprice = 0;
        $data->altpresets = null;
        $data->altaccountid = 0;
    }
    // Дата начала — полдень UTC выбранного дня: читателю в любом часовом поясе это тот же день.
    if (!empty($data->startdate)) {
        $day = usergetdate($data->startdate);
        $data->startdate = gmmktime(12, 0, 0, $day['mon'], $day['mday'], $day['year']);
    } else {
        $data->startdate = 0;
    }
    $data->duration = trim((string) ($data->duration ?? '')) ?: null;
    $data->stream = trim((string) ($data->stream ?? '')) ?: null;
    $data->program = implode(PHP_EOL, \local_kabshop\catalog::lines($data->program ?? '', true)) ?: null;
    $data->about = implode(PHP_EOL, \local_kabshop\catalog::lines($data->about ?? '')) ?: null;
    $data->salestate = in_array($data->salestate ?? '', ['soon', 'closed'], true) ? $data->salestate : 'auto';
    $data->saleopen = (int) ($data->saleopen ?? 0);
    $data->saleclose = (int) ($data->saleclose ?? 0);
    $data->salenote = trim((string) ($data->salenote ?? '')) ?: null;
    $data->timemodified = time();
    if ($data->id) {
        $DB->update_record('local_kabshop_product', $data);
    } else {
        $data->timecreated = time();
        $data->id = $DB->insert_record('local_kabshop_product', $data);
    }
    file_postupdate_standard_filemanager($data, 'image', $fileoptions, $context, 'local_kabshop', 'image', $data->id);
    redirect($manageurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$PAGE->set_title(get_string($id ? 'editproduct' : 'addproduct', 'local_kabshop'));
// Вид «Взнос за курс» сам отмечает «Сумма на выбор» — поле готовых сумм раскрывается сразу.
$PAGE->requires->js_amd_inline("(function() {
    var kind = document.getElementById('id_kind');
    var custom = document.getElementById('id_customamount');
    if (!kind || !custom) {
        return;
    }
    var sync = function() {
        if (kind.value === 'contribution' && !custom.checked) {
            custom.checked = true;
            custom.dispatchEvent(new Event('change', {bubbles: true}));
            custom.dispatchEvent(new Event('click', {bubbles: true}));
        }
    };
    kind.addEventListener('change', sync);
    sync();
})();");
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string($id ? 'editproduct' : 'addproduct', 'local_kabshop'));
$form->display();
if ($id) {
    echo html_writer::div(html_writer::link(new moodle_url('/local/kabshop/delete.php', ['type' => 'product', 'id' => $id]),
        get_string('delete_product', 'local_kabshop'), ['class' => 'btn btn-outline-danger']), 'mt-4');
}
echo $OUTPUT->footer();
