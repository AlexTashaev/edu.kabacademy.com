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

namespace local_kabshop\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * WS-функция для ИИ-ассистента: оплачен ли доступ у email.
 *
 * Своя служба kabshop_access со своим токеном и правом local/kabshop:checkaccess —
 * пользователь-бот без прав админа, токен с ограничением по IP ассистента.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class check_access extends external_api {

    /**
     * Параметры.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'email' => new external_value(PARAM_RAW_TRIMMED, 'Email человека, как на учебном сайте'),
        ]);
    }

    /**
     * Проверка доступа.
     *
     * @param string $email
     * @return array
     */
    public static function execute(string $email): array {
        $params = self::validate_parameters(self::execute_parameters(), ['email' => $email]);
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/kabshop:checkaccess', $context);
        return \local_kabshop\access::check($params['email']);
    }

    /**
     * Ответ.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'active' => new external_value(PARAM_BOOL, 'Доступ оплачен'),
            'until'  => new external_value(PARAM_INT, 'До какого момента оплачено (unixtime); 0 — бессрочно или доступа нет'),
            'source' => new external_value(PARAM_ALPHA, 'subscription | grant | none'),
        ]);
    }
}
