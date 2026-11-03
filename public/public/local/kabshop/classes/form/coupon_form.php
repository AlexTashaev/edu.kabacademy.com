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
 * Форма купона.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coupon_form extends \moodleform {

    /**
     * Поля формы.
     */
    protected function definition() {
        global $DB;
        $mform = $this->_form;
        $p = 'local_kabshop';

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'code', get_string('couponcode', $p), ['size' => 24, 'maxlength' => 64]);
        $mform->setType('code', PARAM_RAW_TRIMMED);
        $mform->addRule('code', null, 'required');
        $mform->addHelpButton('code', 'couponcode', $p);

        $mform->addElement('select', 'kind', get_string('couponkind', $p),
            ['percent' => get_string('couponkind_percent', $p), 'fixed' => get_string('couponkind_fixed', $p)]);

        $mform->addElement('text', 'value', get_string('couponvalue', $p), ['size' => 8]);
        $mform->setType('value', PARAM_FLOAT);
        $mform->addRule('value', null, 'required');

        $currencies = \core_payment\helper::get_supported_currencies();
        $mform->addElement('select', 'currency', get_string('currency'), array_combine($currencies, $currencies));
        $mform->setDefault('currency', 'USD');
        $mform->hideIf('currency', 'kind', 'eq', 'percent');

        $products = $DB->get_records_menu('local_kabshop_product', null, 'name', 'id, name');
        $mform->addElement('autocomplete', 'productids', get_string('couponproducts', $p), $products,
            ['multiple' => true, 'noselectionstring' => get_string('allproducts', $p)]);
        $mform->addHelpButton('productids', 'couponproducts', $p);

        $mform->addElement('date_time_selector', 'validfrom', get_string('couponfrom', $p), ['optional' => true, 'startyear' => (int) date('Y') - 1, 'stopyear' => (int) date('Y') + 3]);
        $mform->addElement('date_time_selector', 'validto', get_string('couponto', $p), ['optional' => true, 'startyear' => (int) date('Y') - 1, 'stopyear' => (int) date('Y') + 3]);

        $mform->addElement('text', 'maxuses', get_string('couponmaxuses', $p), ['size' => 6]);
        $mform->setType('maxuses', PARAM_INT);
        $mform->setDefault('maxuses', 0);
        $mform->addHelpButton('maxuses', 'couponmaxuses', $p);

        $mform->addElement('advcheckbox', 'peruser', get_string('couponperuser', $p));
        $mform->setDefault('peruser', 1);

        $mform->addElement('text', 'note', get_string('couponnote', $p), ['size' => 60]);
        $mform->setType('note', PARAM_TEXT);

        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'admin'));
        $mform->setDefault('enabled', 1);

        $this->add_action_buttons();
    }

    /**
     * Проверка: код уникален, скидка в разумных пределах.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        $code = \local_kabshop\coupons::normalize((string) $data['code']);
        if ($code === '' || !preg_match('/^[\p{L}\p{N}_-]+$/u', $code)) {
            $errors['code'] = get_string('couponcode_invalid', 'local_kabshop');
        } else if ($DB->record_exists_select('local_kabshop_coupon', 'code = ? AND id <> ?', [$code, (int) $data['id']])) {
            $errors['code'] = get_string('couponcode_taken', 'local_kabshop');
        }
        $v = (float) $data['value'];
        if ($v <= 0 || ($data['kind'] === 'percent' && $v > 100)) {
            $errors['value'] = get_string('couponvalue_invalid', 'local_kabshop');
        }
        if (!empty($data['validfrom']) && !empty($data['validto']) && $data['validto'] <= $data['validfrom']) {
            $errors['validto'] = get_string('couponto_invalid', 'local_kabshop');
        }
        return $errors;
    }
}
