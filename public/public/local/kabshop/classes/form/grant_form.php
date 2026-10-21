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

namespace local_kabshop\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Ручная выдача товара: оплата переводом, подарок, исправление ошибки.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grant_form extends \moodleform {

    /**
     * Поля формы.
     */
    protected function definition() {
        global $DB;
        $mform = $this->_form;
        $p = 'local_kabshop';

        $mform->addElement('text', 'email', get_string('grantemail', $p), ['size' => 40]);
        $mform->setType('email', PARAM_RAW_TRIMMED);
        $mform->addRule('email', null, 'required');
        $mform->addHelpButton('email', 'grantemail', $p);

        $products = $DB->get_records_select_menu('local_kabshop_product', "kind <> 'external'", null, 'name', 'id, name');
        $mform->addElement('select', 'productid', get_string('col_subproduct', $p), $products);

        $mform->addElement('text', 'note', get_string('grantnote', $p), ['size' => 60]);
        $mform->setType('note', PARAM_TEXT);
        $mform->addRule('note', null, 'required');
        $mform->addHelpButton('note', 'grantnote', $p);

        $mform->addElement('date_selector', 'timeend', get_string('granttimeend', $p), ['optional' => true, 'startyear' => (int) date('Y') - 1, 'stopyear' => (int) date('Y') + 3]);
        $mform->addHelpButton('timeend', 'granttimeend', $p);

        $mform->addElement('advcheckbox', 'mail', get_string('grantmail', $p));
        $mform->setDefault('mail', 1);

        $this->add_action_buttons(true, get_string('grantsubmit', $p));
    }

    /**
     * Покупатель должен существовать: выдаём на существующий аккаунт.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        $email = \core_text::strtolower(trim((string) $data['email']));
        $users = $DB->get_records_select('user', 'LOWER(email) = ? AND deleted = 0', [$email], '', 'id', 0, 2);
        if (!validate_email($email)) {
            $errors['email'] = get_string('invalidemail');
        } else if (!$users) {
            $errors['email'] = get_string('grantnouser', 'local_kabshop');
        } else if (count($users) > 1) {
            $errors['email'] = get_string('grantmanyusers', 'local_kabshop');
        }
        if (!empty($data['timeend']) && $data['timeend'] < time()) {
            $errors['timeend'] = get_string('granttimeend_past', 'local_kabshop');
        }
        return $errors;
    }
}
