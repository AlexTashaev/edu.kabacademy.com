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

        // Страница курса: начало и продолжительность — в карточке покупки, темы уроков — списком
        // «В программе курса», короткий текст — блок «Этот курс» под карточкой.
        $mform->addElement('header', 'hdrpage', get_string('hdrpage', $p));
        $mform->setExpanded('hdrpage', true);

        $mform->addElement('date_selector', 'startdate', get_string('startdate', $p), ['optional' => true]);
        $mform->addHelpButton('startdate', 'startdate', $p);

        $mform->addElement('text', 'duration', get_string('duration', $p), ['size' => 30, 'maxlength' => 100]);
        $mform->setType('duration', PARAM_TEXT);
        $mform->addHelpButton('duration', 'duration', $p);

        $mform->addElement('text', 'stream', get_string('stream', $p), ['size' => 30, 'maxlength' => 100]);
        $mform->setType('stream', PARAM_TEXT);
        $mform->addHelpButton('stream', 'stream', $p);

        $mform->addElement('textarea', 'program', get_string('program', $p), ['rows' => 10, 'cols' => 60]);
        $mform->setType('program', PARAM_TEXT);
        $mform->addHelpButton('program', 'program', $p);

        $mform->addElement('textarea', 'about', get_string('about', $p), ['rows' => 6, 'cols' => 60]);
        $mform->setType('about', PARAM_TEXT);
        $mform->addHelpButton('about', 'about', $p);

        // Запись: товар на витрине всегда, а кнопка — только пока запись открыта.
        $mform->addElement('header', 'hdrsale', get_string('hdrsale', $p));
        $mform->setExpanded('hdrsale', true);

        $mform->addElement('select', 'salestate', get_string('salestate', $p), [
            'auto' => get_string('salestate_auto', $p),
            'soon' => get_string('salestate_soon', $p),
            'closed' => get_string('salestate_closed', $p),
        ]);
        $mform->addHelpButton('salestate', 'salestate', $p);
        $mform->setDefault('salestate', 'auto');

        $mform->addElement('date_time_selector', 'saleopen', get_string('saleopen', $p), ['optional' => true]);
        $mform->hideIf('saleopen', 'salestate', 'neq', 'auto');
        $mform->addElement('date_time_selector', 'saleclose', get_string('saleclose', $p), ['optional' => true]);
        $mform->hideIf('saleclose', 'salestate', 'neq', 'auto');

        $mform->addElement('text', 'salenote', get_string('salenote', $p), ['size' => 60, 'maxlength' => 255]);
        $mform->setType('salenote', PARAM_TEXT);
        $mform->addHelpButton('salenote', 'salenote', $p);

        $mform->addElement('header', 'hdrprice', get_string('hdrprice', $p));
        $mform->setExpanded('hdrprice', true);

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
        // Рубли — российская витрина; остальное — основная. Объяснение — в подсказке.
        $mform->addHelpButton('currency', 'currency', $p);

        $mform->addElement('text', 'pricelabel', get_string('pricelabel', $p), ['size' => 20]);
        $mform->setType('pricelabel', PARAM_TEXT);
        $mform->addHelpButton('pricelabel', 'pricelabel', $p);

        $mform->addElement('advcheckbox', 'customamount', get_string('customamount', $p));
        $mform->addHelpButton('customamount', 'customamount', $p);

        $mform->addElement('text', 'presets', get_string('presets', $p), ['size' => 30]);
        $mform->setType('presets', PARAM_TEXT);
        $mform->addHelpButton('presets', 'presets', $p);
        $mform->hideIf('presets', 'customamount', 'notchecked');

        $mform->addElement('text', 'defaultamount', get_string('defaultamount', $p), ['size' => 10]);
        $mform->setType('defaultamount', PARAM_FLOAT);
        $mform->addHelpButton('defaultamount', 'defaultamount', $p);
        $mform->hideIf('defaultamount', 'customamount', 'notchecked');

        $recur = [0 => get_string('recur_none', $p)];
        foreach (\local_kabshop\subscriptions::PERIODS as $m) {
            $recur[$m] = get_string('recur_' . $m, $p);
        }
        $mform->addElement('select', 'recurmonths', get_string('recurring', $p), $recur);
        $mform->addHelpButton('recurmonths', 'recurring', $p);
        $mform->setDefault('recurmonths', 0);

        $accounts = [0 => get_string('none')];
        foreach (\core_payment\helper::get_payment_accounts_menu(\context_system::instance()) as $aid => $aname) {
            $accounts[$aid] = $aname;
        }
        $mform->addElement('select', 'accountid', get_string('paymentaccount', 'payment'), $accounts);

        // Вторая цена — в другой валюте со своим счётом (рубли через Robokassa): тот же товар,
        // на странице — выбор, чем платить.
        $mform->addElement('select', 'altcurrency', get_string('altcurrency', $p),
            ['' => get_string('altcurrency_none', $p)] + $currencymenu);
        $mform->addHelpButton('altcurrency', 'altcurrency', $p);
        $mform->addElement('text', 'altprice', get_string('altprice', $p), ['size' => 10]);
        $mform->setType('altprice', PARAM_FLOAT);
        $mform->setDefault('altprice', 0);
        $mform->addHelpButton('altprice', 'altprice', $p);
        $mform->hideIf('altprice', 'altcurrency', 'eq', '');
        $mform->addElement('text', 'altpresets', get_string('altpresets', $p), ['size' => 30]);
        $mform->setType('altpresets', PARAM_TEXT);
        $mform->hideIf('altpresets', 'altcurrency', 'eq', '');
        $mform->hideIf('altpresets', 'customamount', 'notchecked');
        $mform->addElement('text', 'altdefaultamount', get_string('altdefaultamount', $p), ['size' => 10]);
        $mform->setType('altdefaultamount', PARAM_FLOAT);
        $mform->hideIf('altdefaultamount', 'altcurrency', 'eq', '');
        $mform->hideIf('altdefaultamount', 'customamount', 'notchecked');
        $mform->addElement('select', 'altaccountid', get_string('altaccount', $p), $accounts);
        $mform->hideIf('altaccountid', 'altcurrency', 'eq', '');

        $mform->addElement('text', 'sku', get_string('sku', $p), ['size' => 20, 'maxlength' => 64]);
        $mform->setType('sku', PARAM_TEXT);
        $mform->addHelpButton('sku', 'sku', $p);

        $mform->addElement('text', 'notifyemails', get_string('notifyemails', $p), ['size' => 60, 'maxlength' => 255]);
        $mform->setType('notifyemails', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('notifyemails', 'notifyemails', $p);

        $mform->addElement('text', 'courseid', get_string('courseid', $p), ['size' => 8]);
        $mform->setType('courseid', PARAM_INT);
        $mform->addHelpButton('courseid', 'courseid', $p);

        $mform->addElement('text', 'requirecourses', get_string('requirecourses', $p), ['size' => 20]);
        $mform->setType('requirecourses', PARAM_SEQUENCE);
        $mform->addHelpButton('requirecourses', 'requirecourses', $p);

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
        $required = \local_kabshop\catalog::parse_courseids((string) ($data['requirecourses'] ?? ''));
        if ($required) {
            $missing = array_diff($required, array_keys($DB->get_records_list('course', 'id', $required, '', 'id')));
            if ($missing) {
                $errors['requirecourses'] = get_string('coursenotfound', 'local_kabshop') . ': ' . implode(', ', $missing);
            } else if ($data['courseid'] && in_array((int) $data['courseid'], $required, true)) {
                $errors['requirecourses'] = get_string('requireself', 'local_kabshop');
            }
        }
        if ($data['price'] < 0) {
            $errors['price'] = get_string('invalidprice', 'local_kabshop');
        }
        $bad = \local_kabshop\mailer::bad_notify_emails((string) ($data['notifyemails'] ?? ''));
        if ($bad) {
            $errors['notifyemails'] = get_string('notifyemailsbad', 'local_kabshop', s(implode(', ', $bad)));
        }
        if (($data['salestate'] ?? 'auto') === 'auto' && !empty($data['saleopen']) && !empty($data['saleclose'])
                && $data['saleclose'] <= $data['saleopen']) {
            $errors['saleclose'] = get_string('salecloseearly', 'local_kabshop');
        }
        if ($data['kind'] === 'external' && empty($data['externalurl'])) {
            $errors['externalurl'] = get_string('required');
        }
        // Платный товар (или сумма на выбор) без платёжного счёта не продаётся — на странице
        // товара было бы «Оплата не настроена».
        $custom = !empty($data['customamount']) || $data['kind'] === 'contribution';
        $paid = (float) $data['price'] > 0 || $custom;
        if (!empty($data['recurmonths']) && !$paid) {
            $errors['recurmonths'] = get_string('recurfree', 'local_kabshop');
        }
        if ($paid && $data['kind'] !== 'external' && empty($data['accountid'])) {
            $errors['accountid'] = get_string('accountrequired', 'local_kabshop');
        }
        // Сумма по умолчанию — в пределах, которые примет страница оплаты: от цены (минимума) до MAX_AMOUNT.
        if ($custom) {
            $def = (float) ($data['defaultamount'] ?? 0);
            if ($def != 0 && ($def < (float) $data['price'] || $def > \local_kabshop\catalog::MAX_AMOUNT)) {
                $errors['defaultamount'] = get_string('defaultamount_range', 'local_kabshop');
            }
            $altdef = (float) ($data['altdefaultamount'] ?? 0);
            if (!empty($data['altcurrency']) && $altdef != 0
                    && ($altdef < (float) $data['altprice'] || $altdef > \local_kabshop\catalog::MAX_AMOUNT)) {
                $errors['altdefaultamount'] = get_string('defaultamount_range', 'local_kabshop');
            }
        }
        if (!empty($data['altcurrency'])) {
            if ($data['altcurrency'] === $data['currency']) {
                $errors['altcurrency'] = get_string('altcurrency_same', 'local_kabshop');
            } else if (!$paid || $data['kind'] === 'external') {
                $errors['altcurrency'] = get_string('altcurrency_free', 'local_kabshop');
            } else if ((float) $data['altprice'] < 0 || (!$custom && (float) $data['altprice'] <= 0)) {
                $errors['altprice'] = get_string('invalidaltprice', 'local_kabshop');
            } else if (empty($data['altaccountid'])) {
                $errors['altaccountid'] = get_string('accountrequired', 'local_kabshop');
            }
        }
        return $errors;
    }
}
