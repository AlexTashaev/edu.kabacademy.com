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
 * Каталог витрины. Открыт гостям.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/kabshop/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('catalog', 'local_kabshop'));
$PAGE->set_heading(get_string('catalog', 'local_kabshop'));

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_kabshop/catalog', \local_kabshop\catalog::export_catalog());
echo $OUTPUT->footer();
