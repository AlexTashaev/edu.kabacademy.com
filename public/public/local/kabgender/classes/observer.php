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

namespace local_kabgender;

/**
 * Обсерверы: заполняют поле профиля «Пол» по имени при создании аккаунта и записи на курс.
 *
 * @package    local_kabgender
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /** @var string Shortname кастомного поля профиля. */
    const FIELD_SHORTNAME = 'gender';

    /** @var string[] Соответствие результата детектора опциям меню поля. */
    const VALUES = ['male' => 'муж', 'female' => 'жен'];

    /**
     * Новый аккаунт (любой путь, где событие не подавлено).
     *
     * @param \core\event\user_created $event событие
     */
    public static function user_created(\core\event\user_created $event): void {
        self::fill_gender((int) $event->objectid);
    }

    /**
     * Запись на курс: страховка для аккаунтов, созданных без события user_created
     * (auth_edwiserbridge cohort-flow), и ленивое дозаполнение старых пользователей.
     *
     * @param \core\event\user_enrolment_created $event событие
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event): void {
        self::fill_gender((int) $event->relateduserid);
    }

    /**
     * Определить и записать пол, только если поле ещё пусто. Никогда не бросает.
     *
     * @param int $userid id пользователя
     */
    public static function fill_gender(int $userid): void {
        global $DB;
        try {
            if ($userid <= 0) {
                return;
            }
            $field = $DB->get_record('user_info_field', ['shortname' => self::FIELD_SHORTNAME]);
            if (!$field) {
                return;
            }
            $existing = $DB->get_record('user_info_data', ['userid' => $userid, 'fieldid' => $field->id]);
            if ($existing && trim((string) $existing->data) !== '') {
                return;
            }
            $user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0], 'id, firstname, lastname');
            if (!$user) {
                return;
            }
            $gender = detector::detect((string) $user->firstname, (string) $user->lastname);
            if ($gender === null) {
                return;
            }
            $value = self::VALUES[$gender];
            // Значение должно существовать среди опций меню поля.
            $options = array_map('trim', preg_split('/\r?\n/', (string) $field->param1) ?: []);
            if (!in_array($value, $options, true)) {
                return;
            }
            if ($existing) {
                $existing->data = $value;
                $DB->update_record('user_info_data', $existing);
            } else {
                $DB->insert_record('user_info_data', (object) [
                    'userid' => $userid,
                    'fieldid' => $field->id,
                    'data' => $value,
                    'dataformat' => 0,
                ]);
            }
        } catch (\Throwable $e) {
            debugging('local_kabgender: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
}
