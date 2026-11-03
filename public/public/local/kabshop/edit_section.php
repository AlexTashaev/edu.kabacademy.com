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
 * Создание и правка раздела витрины.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$id = optional_param('id', 0, PARAM_INT);

admin_externalpage_setup('local_kabshop_manage', '', null, new moodle_url('/local/kabshop/edit_section.php', ['id' => $id]));
require_capability('local/kabshop:manage', context_system::instance());

$manageurl = new moodle_url('/local/kabshop/manage.php');
$form = new \local_kabshop\form\section_form();
if ($id) {
    $form->set_data($DB->get_record('local_kabshop_section', ['id' => $id], '*', MUST_EXIST));
}

if ($form->is_cancelled()) {
    redirect($manageurl);
} else if ($data = $form->get_data()) {
    $data->timemodified = time();
    if ($data->id) {
        $DB->update_record('local_kabshop_section', $data);
    } else {
        $DB->insert_record('local_kabshop_section', $data);
    }
    redirect($manageurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$PAGE->set_title(get_string($id ? 'editsection' : 'addsection', 'local_kabshop'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string($id ? 'editsection' : 'addsection', 'local_kabshop'));
$form->display();
echo $OUTPUT->footer();
