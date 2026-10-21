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
 * Правка подписки администратором: ближайший платёж, сумма, статус.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sub_form extends \moodleform {

    /**
     * Поля формы.
     */
    protected function definition() {
        $mform = $this->_form;
        $p = 'local_kabshop';

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('date_time_selector', 'nextcharge', get_string('subnext', $p),
            ['step' => 5, 'startyear' => (int) date('Y') - 1, 'stopyear' => (int) date('Y') + 3]);
        $mform->addHelpButton('nextcharge', 'subnext', $p);

        $mform->addElement('text', 'amount', get_string('subamount', $p), ['size' => 8]);
        $mform->setType('amount', PARAM_FLOAT);
        $mform->addRule('amount', null, 'required');

        $mform->addElement('select', 'status', get_string('substatus', $p), [
            'active' => get_string('substatus_active', $p),
            'cancelled' => get_string('substatus_cancelled', $p),
            'failed' => get_string('substatus_failed', $p),
        ]);

        $mform->addElement('advcheckbox', 'resetfails', get_string('subresetfails', $p));

        $this->add_action_buttons(false, get_string('savechanges'));
    }

    /**
     * Сумма > 0.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ((float) $data['amount'] <= 0) {
            $errors['amount'] = get_string('invalidprice', 'local_kabshop');
        }
        return $errors;
    }
}
