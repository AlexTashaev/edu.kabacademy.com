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

namespace local_kabwporders\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Форма загрузки выгрузки WordPress: три файла orders/items/products (.tsv или .tsv.gz).
 *
 * @package    local_kabwporders
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class import_form extends \moodleform {

    /**
     * Поля формы.
     */
    protected function definition() {
        $mform = $this->_form;
        $p = 'local_kabwporders';

        $mform->addElement('filemanager', 'files', get_string('importfiles', $p), null, [
            'subdirs' => 0,
            'maxfiles' => 3,
            'accepted_types' => '*',
        ]);
        $mform->addHelpButton('files', 'importfiles', $p);
        $mform->addRule('files', null, 'required');

        $this->add_action_buttons(false, get_string('importrun', $p));
    }
}
