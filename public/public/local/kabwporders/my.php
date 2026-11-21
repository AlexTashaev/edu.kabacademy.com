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
 * «Мои прежние оплаты» — оплаты текущего пользователя на старом сайте.
 *
 * Та же таблица, что касса показывает в «Моих заказах»; эта страница работает и без кассы.
 * Чужие оплаты посмотреть нельзя: параметров нет, берётся только текущий пользователь.
 *
 * @package    local_kabwporders
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login(null, false);
if (isguestuser()) {
    redirect(get_login_url());
}
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/kabwporders/my.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('my_title', 'local_kabwporders'));
$PAGE->set_heading(get_string('my_title', 'local_kabwporders'));

echo $OUTPUT->header();
$html = \local_kabwporders\myorders::render($USER);
echo $html !== '' ? $html : $OUTPUT->notification(get_string('my_empty', 'local_kabwporders'), 'info');
echo $OUTPUT->footer();
