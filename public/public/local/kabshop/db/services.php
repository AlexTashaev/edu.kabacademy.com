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

/**
 * WS-функции и служба local_kabshop.
 *
 * Служба «на избранных»: админ добавляет пользователя-бота ассистента в «Авторизованные
 * пользователи» и создаёт ему токен с ограничением по IP.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_kabshop_check_access' => [
        'classname'    => 'local_kabshop\external\check_access',
        'methodname'   => 'execute',
        'description'  => 'Read-only: оплачен ли доступ к сервису (товары из настройки accessproducts) у email.',
        'type'         => 'read',
        'ajax'         => false,
        'capabilities' => 'local/kabshop:checkaccess',
    ],
];

$services = [
    'KAB Shop access' => [
        'functions'       => ['local_kabshop_check_access'],
        'restrictedusers' => 1,
        'enabled'         => 1,
        'shortname'       => 'kabshop_access',
        'downloadfiles'   => 0,
        'uploadfiles'     => 0,
    ],
];
