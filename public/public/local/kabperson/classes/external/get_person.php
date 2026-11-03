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

namespace local_kabperson\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;

/**
 * WS-функция для бота поддержки: карточка человека по email, JSON-строкой.
 *
 * Своя служба kabperson_card со своим токеном и правом local/kabperson:view —
 * без Edwiser и без суперадмина (§8 спеки). Бот читает карточку как текст,
 * поэтому контракт отдаём одной JSON-строкой: вложенную структуру §9 не
 * пересказываем в external_structure, чтобы она не расходилась со страницей.
 *
 * @package    local_kabperson
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_person extends external_api {

    /**
     * Параметры.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'email'   => new external_value(PARAM_RAW_TRIMMED, 'Email человека'),
            'refresh' => new external_value(PARAM_BOOL, 'Пересобрать карточку мимо кэша', VALUE_DEFAULT, false),
        ]);
    }

    /**
     * Карточка по email.
     *
     * @param string $email
     * @param bool $refresh
     * @return string JSON по контракту §9 спеки
     */
    public static function execute(string $email, bool $refresh = false): string {
        $params = self::validate_parameters(self::execute_parameters(), ['email' => $email, 'refresh' => $refresh]);

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/kabperson:view', $context);

        $card = \local_kabperson\api::person($params['email'], (bool) $params['refresh']);

        \local_kabperson\event\card_viewed::create([
            'context'       => $context,
            'relateduserid' => $card['identity']['moodle_user_id'] ?? null,
            'other'         => [
                'email' => \core_text::strtolower(trim($params['email'])),
                'via'   => 'ws',
                'gated' => 0,
            ],
        ])->trigger();

        return json_encode($card, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Возврат: JSON-строка.
     *
     * @return external_value
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_RAW, 'Карточка человека в JSON (контракт §9 спеки Person Card)');
    }
}
