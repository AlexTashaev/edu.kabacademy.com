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

use local_kabshop\catalog;

/**
 * Поля «Кто видит» для формы раздела и товара: курсы, глобальные группы, адреса, «любое/все».
 *
 * В базе: requirecourses и requirecohorts — id через запятую, requireemails — адрес на строку,
 * requiremode — 'any' | 'all'. Проверку условий делает catalog (conditions_ok).
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class conditions {

    /**
     * Добавить поля в форму.
     *
     * @param \MoodleQuickForm $mform
     * @param bool $isproduct товар (иначе раздел): разные подсказки
     */
    public static function add(\MoodleQuickForm $mform, bool $isproduct): void {
        global $DB;
        $p = 'local_kabshop';
        $mform->addElement('header', 'hdrconditions', get_string('hdrconditions', $p));
        $mform->setExpanded('hdrconditions', false);
        $mform->addElement('static', 'conditionshelp', '',
            get_string($isproduct ? 'conditionshelp_product' : 'conditionshelp_section', $p));

        $mform->addElement('text', 'requirecourses', get_string('requirecourses', $p), ['size' => 20]);
        $mform->setType('requirecourses', PARAM_SEQUENCE);
        $mform->addHelpButton('requirecourses', $isproduct ? 'requirecourses' : 'requirecourses_section', $p);

        $cohorts = [];
        foreach ($DB->get_records('cohort', null, 'name', 'id, name, idnumber') as $c) {
            $cohorts[$c->id] = format_string($c->name) . ($c->idnumber !== '' ? ' (' . s($c->idnumber) . ')' : '');
        }
        $mform->addElement('autocomplete', 'requirecohorts', get_string('requirecohorts', $p), $cohorts,
            ['multiple' => true, 'noselectionstring' => get_string('requirecohorts_none', $p)]);
        $mform->addHelpButton('requirecohorts', 'requirecohorts', $p);

        $mform->addElement('textarea', 'requireemails', get_string('requireemails', $p), ['rows' => 4, 'cols' => 50]);
        $mform->setType('requireemails', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('requireemails', 'requireemails', $p);

        $mform->addElement('select', 'requiremode', get_string('requiremode', $p), [
            'any' => get_string('requiremode_any', $p),
            'all' => get_string('requiremode_all', $p),
        ]);
        $mform->setDefault('requiremode', 'any');
        $mform->addHelpButton('requiremode', 'requiremode', $p);
    }

    /**
     * Запись из базы → значения формы (группы — массивом для autocomplete).
     *
     * @param \stdClass $record
     * @return \stdClass
     */
    public static function prepare(\stdClass $record): \stdClass {
        $record->requirecohorts = catalog::parse_courseids((string) ($record->requirecohorts ?? ''));
        return $record;
    }

    /**
     * Значения формы → поля записи.
     *
     * @param \stdClass $data
     * @return \stdClass
     */
    public static function clean(\stdClass $data): \stdClass {
        $data->requirecourses = implode(',', catalog::parse_courseids((string) ($data->requirecourses ?? ''))) ?: null;
        $cohorts = is_array($data->requirecohorts ?? null) ? implode(',', $data->requirecohorts)
            : (string) ($data->requirecohorts ?? '');
        $data->requirecohorts = implode(',', catalog::parse_courseids($cohorts)) ?: null;
        $data->requireemails = implode(PHP_EOL, catalog::parse_emails($data->requireemails ?? '')) ?: null;
        $data->requiremode = ($data->requiremode ?? '') === 'all' ? 'all' : 'any';
        return $data;
    }

    /**
     * Ошибки полей: несуществующие курсы, неверные адреса.
     *
     * @param array $data
     * @return array
     */
    public static function validate(array $data): array {
        global $DB;
        $errors = [];
        $required = catalog::parse_courseids((string) ($data['requirecourses'] ?? ''));
        if ($required) {
            $missing = array_diff($required, array_keys($DB->get_records_list('course', 'id', $required, '', 'id')));
            if ($missing) {
                $errors['requirecourses'] = get_string('coursenotfound', 'local_kabshop') . ': ' . implode(', ', $missing);
            }
        }
        $bad = [];
        foreach (preg_split('/[\s,;]+/', (string) ($data['requireemails'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $email) {
            if (!validate_email(trim($email))) {
                $bad[] = $email;
            }
        }
        if ($bad) {
            $errors['requireemails'] = get_string('notifyemailsbad', 'local_kabshop', s(implode(', ', $bad)));
        }
        return $errors;
    }
}
