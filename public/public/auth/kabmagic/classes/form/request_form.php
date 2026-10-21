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

namespace auth_kabmagic\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * The one-field form: e-mail address.
 *
 * @package    auth_kabmagic
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class request_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'email', get_string('emaillabel', 'auth_kabmagic'), [
            'size' => 40,
            'maxlength' => 100,
            'autocomplete' => 'email',
            'inputmode' => 'email',
            'autofocus' => 'autofocus',
        ]);
        $mform->setType('email', PARAM_RAW_TRIMMED);
        $mform->addRule('email', get_string('required'), 'required', null, 'client');

        $mform->addElement('hidden', 'wantsurl', $this->_customdata['wantsurl'] ?? '');
        $mform->setType('wantsurl', PARAM_LOCALURL);

        $this->add_action_buttons(true, get_string('sendlink', 'auth_kabmagic'));
    }

    /**
     * Only a syntactically valid address goes through; whether it exists stays secret.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!validate_email($data['email'] ?? '')) {
            $errors['email'] = get_string('invalidemail');
        }
        return $errors;
    }
}
