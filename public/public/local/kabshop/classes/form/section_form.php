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
 * Форма раздела витрины.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_form extends \moodleform {

    /**
     * Поля формы.
     */
    protected function definition() {
        $mform = $this->_form;
        $p = 'local_kabshop';

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name'), ['size' => 60]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');

        $mform->addElement('textarea', 'description', get_string('description', $p), ['rows' => 3, 'cols' => 60]);
        $mform->setType('description', PARAM_RAW);
        $mform->addHelpButton('description', 'sectiondesc', $p);

        $icons = ['' => get_string('sectionicon_none', $p)];
        foreach (\local_kabshop\catalog::SECTION_ICONS as $icon) {
            $icons[$icon] = get_string('sectionicon_' . $icon, $p);
        }
        $mform->addElement('select', 'icon', get_string('sectionicon', $p), $icons);

        $mform->addElement('text', 'sidelabel', get_string('sectionsidelabel', $p), ['size' => 40, 'maxlength' => 100]);
        $mform->setType('sidelabel', PARAM_TEXT);
        $mform->addHelpButton('sidelabel', 'sectionsidelabel', $p);

        $mform->addElement('advcheckbox', 'collapsed', get_string('sectioncollapsed', $p));
        $mform->addHelpButton('collapsed', 'sectioncollapsed', $p);

        $mform->addElement('text', 'sortorder', get_string('sortorder', $p), ['size' => 4]);
        $mform->setType('sortorder', PARAM_INT);
        $mform->setDefault('sortorder', 0);
        $mform->addHelpButton('sortorder', 'sortorder', $p);

        $mform->addElement('advcheckbox', 'visible', get_string('visible'));
        $mform->setDefault('visible', 1);

        // Кто видит раздел в каталоге; что показать, если доступных товаров в нём нет.
        conditions::add($mform, false);

        $mform->addElement('text', 'emptytext', get_string('emptytext', $p), ['size' => 60, 'maxlength' => 255,
            'placeholder' => get_string('emptytext_default', $p)]);
        $mform->setType('emptytext', PARAM_TEXT);
        $mform->addHelpButton('emptytext', 'emptytext', $p);

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
        return parent::validation($data, $files) + conditions::validate($data);
    }
}
