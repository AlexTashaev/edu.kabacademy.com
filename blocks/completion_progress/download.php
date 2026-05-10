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
 * Download saved completion progress report
 *
 * @package    block_completion_progress
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

$userid     = required_param('userid', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);
$filename   = required_param('filename', PARAM_FILE);
$sesskey    = required_param('sesskey', PARAM_RAW);

// Проверяем sesskey
require_sesskey();

// Проверяем что пользователь скачивает свой файл
if ($userid != $USER->id) {
    print_error('nopermissions', 'error', '', 'download report');
}

// Проверяем доступ к блоку
$block = $DB->get_record('block_instances', array('id' => $instanceid), '*', MUST_EXIST);
$blockcontext = context_block::instance($instanceid);
require_login();
require_capability('block/completion_progress:overview', $blockcontext);

// Формируем путь к файлу
$tempdir = make_temp_directory('block_completion_progress');
$filepath = $tempdir . '/' . $filename;

// Проверяем существование файла
if (!file_exists($filepath)) {
    print_error('filenotfound', 'error');
}

// Проверяем что имя файла соответствует паттерну userid_instanceid_timestamp.extension
$pattern = '/^' . $userid . '_' . $instanceid . '_\d+\.[a-z]+$/';
if (!preg_match($pattern, $filename)) {
    print_error('invalidfile', 'error');
}

// Определяем mime тип
$extension = pathinfo($filename, PATHINFO_EXTENSION);
$mime_types = [
    'csv' => 'text/csv',
    'xls' => 'application/vnd.ms-excel',
    'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    'html' => 'text/html'
];
$mime_type = $mime_types[$extension] ?? 'application/octet-stream';

// Отправляем файл
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

readfile($filepath);
exit;
