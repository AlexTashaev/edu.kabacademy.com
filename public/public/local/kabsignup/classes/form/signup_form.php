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

use local_kabsignup\emailcheck;
use local_kabsignup\manager;

/**
 * The signup form: first name, last name (optional), e-mail, phone
 * (required unless the phonerequired setting is off), consent to the terms
 * (when a terms page is configured).
 *
 * Also carries an invisible honeypot field (not a moodleform element, so bots
 * that fill every input give themselves away); index.php checks it.
 *
 * Custom data: phonerequired, termsurl, buttonlabel, belowbutton, country,
 * courseid, campaign (slug, for the log of rejected addresses).
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
        $custom = $this->_customdata;

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

        $phoneattrs = [
            'size' => 40,
            'maxlength' => 30,
            'autocomplete' => 'tel',
            'inputmode' => 'tel',
            'placeholder' => '+7 999 123-45-67',
        ];
        if (!empty($custom['country'])) {
            $phoneattrs['data-country'] = $custom['country'];
        }
        $mform->addElement('text', 'phone', get_string('phonelabel', 'local_kabsignup'), $phoneattrs);
        $mform->setType('phone', PARAM_RAW_TRIMMED);
        if (!empty($custom['phonerequired'])) {
            $mform->addRule('phone', get_string('required'), 'required', null, 'client');
        }

        if (!empty($custom['termsurl'])) {
            $link = \html_writer::link($custom['termsurl'], get_string('consentlink', 'local_kabsignup'),
                ['target' => '_blank', 'rel' => 'noopener']);
            $mform->addElement('checkbox', 'consent', '', get_string('consenttext', 'local_kabsignup', $link),
                ['class' => 'ks-consent']);
            $mform->addRule('consent', get_string('consentrequired', 'local_kabsignup'), 'required', null, 'client');
        }

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

        $label = !empty($custom['buttonlabel']) ? $custom['buttonlabel'] : get_string('submit', 'local_kabsignup');
        $this->add_action_buttons(false, $label);

        if (!empty($custom['belowbutton'])) {
            $mform->addElement('html', \html_writer::div(s($custom['belowbutton']), 'ks-below'));
        }
    }

    /**
     * A real name, a syntactically valid and deliverable address, a phone
     * number, the consent. Whether the address is taken stays secret until
     * after submission.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $custom = $this->_customdata;
        if (trim($data['firstname'] ?? '') === '') {
            $errors['firstname'] = get_string('required');
        }
        if (!validate_email($data['email'] ?? '')) {
            $errors['email'] = get_string('invalidemail');
        } else if (\core_text::strlen($data['email']) > 100) {
            $errors['email'] = get_string('emailtoolong', 'local_kabsignup');
        }
        $phone = trim($data['phone'] ?? '');
        if ($phone === '') {
            if (!empty($custom['phonerequired'])) {
                $errors['phone'] = get_string('required');
            }
        } else if (manager::normalise_phone($phone) === '') {
            $errors['phone'] = get_string('invalidphone', 'local_kabsignup');
        }
        if (!empty($custom['termsurl']) && empty($data['consent'])) {
            $errors['consent'] = get_string('consentrequired', 'local_kabsignup');
        }
        if (!isset($errors['email']) && !$errors) {
            $error = self::check_address((string)$data['email'], (int)($custom['courseid'] ?? 0),
                (string)($custom['campaign'] ?? ''));
            if ($error !== null) {
                $errors['email'] = $error;
            }
        }
        return $errors;
    }

    /**
     * Clearout check of a new address (§4.6 of the spec). Skipped for taken
     * addresses and when the visitor is over the rate limit; a rejection is
     * logged (status "badmail") so that it counts toward the limit and nobody
     * can burn the Clearout credits by spamming the form.
     *
     * @param string $email
     * @param int $courseid
     * @param string $campaign
     * @return string|null error text
     */
    protected static function check_address(string $email, int $courseid, string $campaign): ?string {
        $config = manager::config();
        if ($config->clearouttoken === '' || manager::find_user($email) !== null) {
            return null;
        }
        $ip = \core_text::substr((string)getremoteaddr(), 0, 45);
        $emailhash = manager::email_hash($email);
        if (manager::throttled($emailhash, $ip, $config)) {
            return null;
        }
        if (emailcheck::check($email, $config) !== 'reject') {
            return null;
        }
        manager::log($courseid, 0, $emailhash, 0, $campaign, 'badmail', $ip);
        return get_string('undeliverable', 'local_kabsignup');
    }
}
