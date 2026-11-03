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
 * WS-функции и служба local_kabperson.
 *
 * Своя служба и свой токен для бота поддержки (§8 спеки) — без Edwiser и без
 * суперадмина. Служба «на избранных»: админ добавляет пользователя-бота в
 * «Авторизованные пользователи» и создаёт ему токен.
 *
 * @package    local_kabperson
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_kabperson_get_person' => [
        'classname'    => 'local_kabperson\external\get_person',
        'methodname'   => 'execute',
        'description'  => 'Read-only: карточка человека по email (контракт §9 спеки Person Card) в JSON.',
        'type'         => 'read',
        'ajax'         => false,
        'capabilities' => 'local/kabperson:view',
    ],
];

$services = [
    'KAB Person Card' => [
        'functions'       => ['local_kabperson_get_person'],
        'restrictedusers' => 1,
        'enabled'         => 1,
        'shortname'       => 'kabperson_card',
        'downloadfiles'   => 0,
        'uploadfiles'     => 0,
    ],
];
