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

namespace local_kabsignup\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * The signup form: first name, last name (optional), e-mail.
 *
 * Also carries an invisible honeypot field (not a moodleform element, so bots
 * that fill every input give themselves away); index.php checks it.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class signup_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'firstname', get_string('firstnamelabel', 'local_kabsignup'), [
            'size' => 40,
            'maxlength' => 100,
            'autocomplete' => 'given-name',
            'autofocus' => 'autofocus',
        ]);
        $mform->setType('firstname', PARAM_NOTAGS);
        $mform->addRule('firstname', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'lastname', get_string('lastnamelabel', 'local_kabsignup'), [
            'size' => 40,
            'maxlength' => 100,
            'autocomplete' => 'family-name',
        ]);
        $mform->setType('lastname', PARAM_NOTAGS);

        $mform->addElement('text', 'email', get_string('emaillabel', 'local_kabsignup'), [
            'size' => 40,
            'maxlength' => 100,
            'autocomplete' => 'email',
            'inputmode' => 'email',
        ]);
        $mform->setType('email', PARAM_RAW_TRIMMED);
        $mform->addRule('email', get_string('required'), 'required', null, 'client');

        $mform->addElement('html', \html_writer::div(
            \html_writer::empty_tag('input', [
                'type' => 'text',
                'name' => 'kab_website',
                'value' => '',
                'tabindex' => '-1',
                'autocomplete' => 'off',
                'aria-hidden' => 'true',
            ]),
            '',
            ['style' => 'position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden;']
        ));

        $this->add_action_buttons(false, get_string('submit', 'local_kabsignup'));
    }

    /**
     * A real name and a syntactically valid address; whether the address is
     * taken stays secret until after submission.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (trim($data['firstname'] ?? '') === '') {
            $errors['firstname'] = get_string('required');
        }
        if (!validate_email($data['email'] ?? '')) {
            $errors['email'] = get_string('invalidemail');
        } else if (\core_text::strlen($data['email']) > 100) {
            $errors['email'] = get_string('emailtoolong', 'local_kabsignup');
        }
        return $errors;
    }
}
