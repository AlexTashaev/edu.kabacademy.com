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

namespace local_kabperson\event;

/**
 * Событие «карточку человека открыли» — журнал «кто какую карточку смотрел» (§8 спеки).
 *
 * other: email (искомый адрес), via (view|ws), gated (1 — через PII-гейт, 0 — по праву).
 * relateduserid — найденный пользователь Moodle, если нашёлся.
 *
 * @package    local_kabperson
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class card_viewed extends \core\event\base {

    /**
     * Read-событие на системном контексте.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Название события.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventcardviewed', 'local_kabperson');
    }

    /**
     * Описание для журнала.
     *
     * @return string
     */
    public function get_description() {
        $email = $this->other['email'] ?? '?';
        $via = $this->other['via'] ?? '?';
        return "Открыта карточка человека по email '{$email}' (канал: {$via}).";
    }
}
