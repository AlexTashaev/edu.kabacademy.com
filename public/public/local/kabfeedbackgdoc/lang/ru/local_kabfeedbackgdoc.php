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
 * Russian strings.
 *
 * @package    local_kabfeedbackgdoc
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Обратная связь → Google Doc (KAB)';
$string['enabled'] = 'Включено';
$string['enabled_desc'] = 'Отправлять новые ответы из «Обратной связи» в веб-приложение Google Apps Script.';
$string['webhookurl'] = 'URL веб-приложения Apps Script';
$string['webhookurl_desc'] = 'URL развёртывания скрипта, привязанного к Google Doc (https://script.google.com/macros/s/.../exec). Пусто = ничего не отправляется.';
$string['secret'] = 'Общий секрет';
$string['secret_desc'] = 'Должен совпадать с константой SECRET в Apps Script. Пусто = без проверки.';
$string['cmids'] = 'Активности «Обратная связь» (cmid)';
$string['cmids_desc'] = 'Через запятую: id модулей курса (параметр id в URL активности). Пусто = все активности «Обратная связь» на сайте.';
$string['taskname'] = 'Отправить ответ из «Обратной связи» в Google Doc';
$string['privacy:metadata:webhook'] = 'Ответы (курс, активность, имя и e-mail респондента, ответы) отправляются в настроенное веб-приложение Google Apps Script, чтобы преподаватели читали их в Google Doc.';
$string['privacy:metadata:webhook:answers'] = 'Ответы, данные в активности «Обратная связь».';
$string['privacy:metadata:webhook:email'] = 'E-mail респондента.';
$string['privacy:metadata:webhook:fullname'] = 'Полное имя респондента.';
