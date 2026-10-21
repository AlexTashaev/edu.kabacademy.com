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
 * Ручная выдача товара администратором.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\catalog;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_kabshop_orders', '', null, new moodle_url('/local/kabshop/grant.php'));
require_capability('local/kabshop:manage', context_system::instance());
$p = 'local_kabshop';
$ordersurl = new moodle_url('/local/kabshop/orders.php');

$form = new \local_kabshop\form\grant_form();
if ($form->is_cancelled()) {
    redirect($ordersurl);
} else if ($data = $form->get_data()) {
    $email = core_text::strtolower(trim($data->email));
    $user = $DB->get_record_select('user', 'LOWER(email) = ? AND deleted = 0', [$email], '*', MUST_EXIST);
    $product = catalog::get_product($data->productid);
    $timeend = !empty($data->timeend) ? (int) $data->timeend + DAYSECS - 1 : 0; // До конца выбранного дня.
    $purchaseid = catalog::grant($product, $user->id, 0, null, [
        'grantedby' => $USER->id,
        'note' => $data->note,
        'timeend' => $timeend,
        'mail' => !empty($data->mail),
    ]);
    redirect(new moodle_url('/local/kabshop/order.php', ['id' => $purchaseid]),
        get_string('granted', $p, $purchaseid), null, \core\output\notification::NOTIFY_SUCCESS);
}

$PAGE->set_title(get_string('grant', $p));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('grant', $p));
echo html_writer::tag('p', get_string('grant_help', $p), ['class' => 'text-muted']);
$form->display();
echo $OUTPUT->footer();
