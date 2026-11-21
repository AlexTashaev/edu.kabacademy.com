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
 * Импорт выгрузки WordPress в реестр: загрузкой трёх файлов или из каталога на сервере.
 *
 * Каталог задаётся только в config.php: $CFG->local_kabwporders_importdir. Из интерфейса его
 * не поменять — страница не должна читать произвольные пути.
 *
 * @package    local_kabwporders
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabwporders\importer;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$fromdir = optional_param('fromdir', 0, PARAM_BOOL);

admin_externalpage_setup('local_kabwporders_import');
$syscontext = context_system::instance();
require_capability('local/kabwporders:import', $syscontext);

$p = 'local_kabwporders';
$pageurl = new moodle_url('/local/kabwporders/import.php');
$serverfiles = importer::files_in((string) ($CFG->local_kabwporders_importdir ?? ''));

$form = new \local_kabwporders\form\import_form($pageurl);
$result = null;
$error = null;

if ($fromdir && $serverfiles && confirm_sesskey()) {
    try {
        $result = importer::run($serverfiles);
    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }
} else if ($data = $form->get_data()) {
    $fs = get_file_storage();
    $usercontext = context_user::instance($USER->id);
    $paths = [];
    foreach ($fs->get_area_files($usercontext->id, 'user', 'draft', $data->files, 'id', false) as $file) {
        $kind = importer::kind_of($file->get_filename());
        if ($kind !== null) {
            $paths[$kind] = $file->copy_content_to_temp('kabwporders');
        }
    }
    try {
        $result = importer::run($paths);
    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }
    // Выгрузка содержит почту и имена — на сервере её не оставляем.
    foreach ($paths as $path) {
        @unlink($path);
    }
    $fs->delete_area_files($usercontext->id, 'user', 'draft', $data->files);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('import', $p));

if ($error !== null) {
    echo $OUTPUT->notification(get_string('importfailed', $p, s($error)), 'error');
}
if ($result !== null) {
    echo $OUTPUT->notification(get_string('importdone', $p), 'success');
    $t = new html_table();
    $t->attributes['class'] = 'generaltable w-auto';
    foreach (['orders', 'items', 'itemswithcourse', 'orderswithoutitems', 'itemorphans', 'bylink', 'byemail',
            'unmatched', 'unmatchedemails', 'linkemaildiffers'] as $key) {
        $t->data[] = [get_string('res_' . $key, $p), (int) $result[$key]];
    }
    echo html_writer::table($t);
    echo html_writer::link(new moodle_url('/local/kabwporders/index.php'), get_string('registry', $p),
        ['class' => 'btn btn-primary mb-4']);
}

echo html_writer::tag('p', get_string('import_help', $p));

if ($serverfiles) {
    echo $OUTPUT->box_start('generalbox mb-4');
    echo html_writer::tag('p', get_string('importdir_found', $p));
    echo $OUTPUT->single_button(new moodle_url($pageurl, ['fromdir' => 1, 'sesskey' => sesskey()]),
        get_string('importdir_run', $p), 'post', ['type' => single_button::BUTTON_PRIMARY]);
    echo $OUTPUT->box_end();
}

$form->display();
echo $OUTPUT->footer();
