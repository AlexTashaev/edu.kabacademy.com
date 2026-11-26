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
 * Отчёты секретарю: что уйдёт в следующей рассылке, скачать файл, отправить сейчас.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\staffreports;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_kabshop_staffreports');
require_capability('local/kabshop:manage', context_system::instance());

$p = 'local_kabshop';
$url = new moodle_url('/local/kabshop/staffreports.php');
$download = optional_param('download', -1, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

$reports = staffreports::reports();

// Скачать файл отчёта — те же покупки, что уйдут в следующей рассылке; метка не двигается.
if ($download >= 0 && isset($reports[$download])) {
    [, $rows] = staffreports::collect($reports);
    \core\dataformat::download_data(staffreports::filename($reports[$download]), 'excel', staffreports::columns(),
        new ArrayIterator(array_values($rows[$download])), fn($r) => staffreports::row($r));
    exit;
}

if ($action === 'send' && confirm_sesskey()) {
    [$sent, $withrows] = staffreports::send();
    // Итог — параметрами адреса: запись файла (core\dataformat::write_data) закрывает сессию,
    // и сообщение через redirect() в сессию уже не попало бы.
    redirect(new moodle_url($url, ['sent' => $sent, 'reports' => $withrows]));
}
$sent = optional_param('sent', -1, PARAM_INT);

$PAGE->set_title(get_string('staffreports', $p));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('staffreports', $p));
if ($sent >= 0) {
    echo $OUTPUT->notification(get_string('staffreports_done', $p,
        (object) ['sent' => $sent, 'reports' => optional_param('reports', 0, PARAM_INT)]), 'success');
}
echo html_writer::tag('p', get_string('staffreports_help', $p), ['class' => 'text-muted']);

$settingsurl = new moodle_url('/admin/settings.php', ['section' => 'local_kabshop_staffreportsettings']);
$to = staffreports::recipients();
if ($to) {
    echo html_writer::tag('p', get_string('staffreports_to', $p, s(implode(', ', $to))));
} else {
    echo $OUTPUT->notification(get_string('staffreports_off', $p), 'warning');
}
$lastid = staffreports::lastid();
echo html_writer::tag('p', $lastid ? get_string('staffreports_last', $p, $lastid) : get_string('staffreports_first', $p));

[, $rows] = staffreports::collect($reports);
$t = new html_table();
$t->attributes['class'] = 'generaltable';
$t->head = [get_string('staffreports_title', $p), get_string('staffreports_products', $p),
    get_string('staffreports_new', $p), ''];
$names = $DB->get_records_menu('local_kabshop_product', null, '', 'id, name');
foreach ($reports as $i => $report) {
    $list = array_map(fn($id) => isset($names[$id]) ? format_string($names[$id]) : '#' . $id, $report['productids']);
    $t->data[] = [
        s($report['title']),
        implode(', ', $list),
        count($rows[$i]),
        html_writer::link(new moodle_url($url, ['download' => $i]), get_string('staffreports_download', $p)),
    ];
}
echo $reports ? html_writer::table($t) : $OUTPUT->notification(get_string('staffreports_none', $p), 'info');

if ($to && $reports) {
    echo $OUTPUT->single_button(new moodle_url($url, ['action' => 'send', 'sesskey' => sesskey()]),
        get_string('staffreports_send', $p), 'post', ['type' => single_button::BUTTON_PRIMARY]);
}
echo html_writer::tag('p', html_writer::link($settingsurl, get_string('staffreportsettings', $p)), ['class' => 'mt-3']);
echo $OUTPUT->footer();
