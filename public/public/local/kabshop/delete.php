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
 * Удаление раздела или товара витрины: с подтверждением, занятые не удаляются.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\single_button;
use local_kabshop\catalog;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$type = required_param('type', PARAM_ALPHA);
$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$url = new moodle_url('/local/kabshop/delete.php', ['type' => $type, 'id' => $id]);
admin_externalpage_setup('local_kabshop_manage', '', null, $url);
require_capability('local/kabshop:manage', context_system::instance());

$p = 'local_kabshop';
$manageurl = new moodle_url('/local/kabshop/manage.php');
if ($type === 'section') {
    $record = $DB->get_record('local_kabshop_section', ['id' => $id], '*', MUST_EXIST);
    $blockers = catalog::section_delete_blockers($id);
} else if ($type === 'product') {
    $record = catalog::get_product($id);
    $blockers = catalog::product_delete_blockers($id);
} else {
    throw new moodle_exception('invalidparameter', 'debug');
}
$name = format_string($record->name);

if ($confirm && !$blockers) {
    require_sesskey();
    if ($type === 'section') {
        catalog::delete_section($id);
    } else {
        catalog::delete_product($id);
    }
    redirect($manageurl, get_string('deleted_' . $type, $p, $name), null, \core\output\notification::NOTIFY_SUCCESS);
}

$title = get_string('delete_' . $type, $p);
$PAGE->set_title($title);
echo $OUTPUT->header();

if ($blockers) {
    echo $OUTPUT->heading($title);
    echo $OUTPUT->notification(get_string('delblocked_' . $type, $p, $name) . html_writer::alist($blockers),
        \core\output\notification::NOTIFY_WARNING, false);
    echo html_writer::tag('p', get_string('delhide_' . $type, $p));
    echo html_writer::div(
        html_writer::link(new moodle_url('/local/kabshop/edit_' . $type . '.php', ['id' => $id]),
            get_string('edit' . $type, $p), ['class' => 'btn btn-primary me-2']) .
        html_writer::link($manageurl, get_string('back'), ['class' => 'btn btn-secondary']));
} else {
    $message = get_string('delconfirm_' . $type, $p, $name);
    if ($type === 'product' && $coupons = catalog::coupons_with_product($id)) {
        $message .= ' ' . get_string('delcoupons', $p, implode(', ', array_map(fn($c) => s($c->code), $coupons)));
    }
    echo $OUTPUT->confirm($message, new moodle_url($url, ['confirm' => 1, 'sesskey' => sesskey()]), $manageurl, [
        'confirmtitle' => $title,
        'continuestr' => get_string('delete'),
        'type' => single_button::BUTTON_DANGER,
    ]);
}

echo $OUTPUT->footer();
