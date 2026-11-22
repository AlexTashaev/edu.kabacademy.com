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
 * Документы витрины: оферты и политика конфиденциальности. Открыты гостям.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\catalog;
use local_kabshop\legal;

require_once(__DIR__ . '/../../config.php');

$doc = required_param('doc', PARAM_ALPHAEXT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(legal::doc_url($doc));
$PAGE->set_pagelayout('standard');

$html = legal::doc_html($doc);
if ($html === null) {
    throw new moodle_exception('docnotfound', 'local_kabshop');
}

$title = get_string('doc_' . $doc, 'local_kabshop');
$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_kabshop/doc', [
    'html' => $html,
    'catalogurl' => catalog::catalog_url($doc === 'offer_ru' ? 'ru' : 'main')->out(false),
]);
echo $OUTPUT->footer();
