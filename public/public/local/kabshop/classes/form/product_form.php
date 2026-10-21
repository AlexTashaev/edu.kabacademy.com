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
 * Форма товара.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class product_form extends \moodleform {

    /**
     * Поля формы.
     */
    protected function definition() {
        global $DB;
        $mform = $this->_form;
        $p = 'local_kabshop';

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $sections = $DB->get_records_menu('local_kabshop_section', null, 'sortorder, id', 'id, name');
        $mform->addElement('select', 'sectionid', get_string('section', $p), $sections);

        $mform->addElement('text', 'name', get_string('name'), ['size' => 60]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');

        $mform->addElement('textarea', 'summary', get_string('summary', $p), ['rows' => 3, 'cols' => 60]);
        $mform->setType('summary', PARAM_TEXT);

        $mform->addElement('editor', 'description_editor', get_string('description', $p), null,
            ['maxfiles' => 0, 'noclean' => false]);
        $mform->setType('description_editor', PARAM_RAW);

        $mform->addElement('filemanager', 'image_filemanager', get_string('image', $p), null,
            ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['web_image']]);
        $mform->addHelpButton('image_filemanager', 'image', $p);

        $kinds = [];
        foreach (\local_kabshop\catalog::KINDS as $k) {
            $kinds[$k] = get_string('kind_' . $k, $p);
        }
        $mform->addElement('select', 'kind', get_string('kind', $p), $kinds);

        $mform->addElement('text', 'price', get_string('price', $p), ['size' => 10]);
        $mform->setType('price', PARAM_FLOAT);
        $mform->addHelpButton('price', 'price', $p);

        $currencies = \core_payment\helper::get_supported_currencies();
        $currencymenu = array_combine($currencies, $currencies);
        $mform->addElement('select', 'currency', get_string('currency'), $currencymenu);
        $mform->setDefault('currency', 'USD');

        $mform->addElement('text', 'pricelabel', get_string('pricelabel', $p), ['size' => 20]);
        $mform->setType('pricelabel', PARAM_TEXT);
        $mform->addHelpButton('pricelabel', 'pricelabel', $p);

        $mform->addElement('advcheckbox', 'customamount', get_string('customamount', $p));
        $mform->addHelpButton('customamount', 'customamount', $p);

        $mform->addElement('text', 'presets', get_string('presets', $p), ['size' => 30]);
        $mform->setType('presets', PARAM_TEXT);
        $mform->addHelpButton('presets', 'presets', $p);
        $mform->hideIf('presets', 'customamount', 'notchecked');

        $accounts = [0 => get_string('none')];
        foreach (\core_payment\helper::get_payment_accounts_menu(\context_system::instance()) as $aid => $aname) {
            $accounts[$aid] = $aname;
        }
        $mform->addElement('select', 'accountid', get_string('paymentaccount', 'payment'), $accounts);

        $mform->addElement('text', 'courseid', get_string('courseid', $p), ['size' => 8]);
        $mform->setType('courseid', PARAM_INT);
        $mform->addHelpButton('courseid', 'courseid', $p);

        $mform->addElement('duration', 'enrolperiod', get_string('enrolperiod', $p), ['optional' => true,
            'defaultunit' => DAYSECS]);
        $mform->addHelpButton('enrolperiod', 'enrolperiod', $p);

        $mform->addElement('text', 'externalurl', get_string('externalurl', $p), ['size' => 60]);
        $mform->setType('externalurl', PARAM_URL);

        $mform->addElement('text', 'sortorder', get_string('sortorder', $p), ['size' => 4]);
        $mform->setType('sortorder', PARAM_INT);
        $mform->setDefault('sortorder', 0);

        $mform->addElement('advcheckbox', 'visible', get_string('visible'));
        $mform->setDefault('visible', 1);

        $this->add_action_buttons();
    }

    /**
     * Проверка полей.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        if ($data['courseid'] && !$DB->record_exists('course', ['id' => $data['courseid']])) {
            $errors['courseid'] = get_string('coursenotfound', 'local_kabshop');
        }
        if ($data['price'] < 0) {
            $errors['price'] = get_string('invalidprice', 'local_kabshop');
        }
        if ($data['kind'] === 'external' && empty($data['externalurl'])) {
            $errors['externalurl'] = get_string('required');
        }
        // Платный товар (или сумма на выбор) без платёжного счёта не продаётся — на странице
        // товара было бы «Оплата не настроена».
        $paid = (float) $data['price'] > 0 || !empty($data['customamount']);
        if ($paid && $data['kind'] !== 'external' && empty($data['accountid'])) {
            $errors['accountid'] = get_string('accountrequired', 'local_kabshop');
        }
        return $errors;
    }
}
