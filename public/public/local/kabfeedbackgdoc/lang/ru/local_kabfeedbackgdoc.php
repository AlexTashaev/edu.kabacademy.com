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

$string['pluginname'] = 'Обратная связь → Google-таблица (KAB)';
$string['courseids'] = 'Курсы (id)';
$string['courseids_desc'] = 'Через запятую: id курсов, из которых пересылать все активности «Обратная связь» (id из URL курса). Пусто = любой курс. Удобно вместе с шаблоном названия: новые еженедельные формы подхватываются сами.';
$string['namepattern'] = 'Шаблон названия формы';
$string['namepattern_desc'] = 'Регулярное выражение без учёта регистра, которому должно соответствовать название активности, например <code>вопрос</code>. Пусто = любое название.';
$string['enabled'] = 'Включено';
$string['enabled_desc'] = 'Отправлять новые ответы из «Обратной связи» в веб-приложение Google Apps Script.';
$string['webhookurl'] = 'URL веб-приложения Apps Script';
$string['secret'] = 'Общий секрет';
$string['secret_desc'] = 'Должен совпадать с константой SECRET в Apps Script. Пусто = без проверки.';
$string['cmids'] = 'Активности «Обратная связь» (cmid)';
$string['cmids_desc'] = 'Через запятую: id модулей курса (параметр id в URL активности). Пусто = не ограничивать по конкретным активностям. Все три фильтра (курсы, шаблон, cmid) применяются одновременно.';
$string['webhookurl_desc'] = 'URL развёртывания скрипта, привязанного к Google-таблице (https://script.google.com/macros/s/.../exec). Пусто = ничего не отправляется.';
$string['defaulttarget'] = 'Таблица вопросов (по умолчанию)';
$string['defaulttarget_desc'] = 'Ссылка на Google-таблицу (или её id), куда идут формы, прошедшие три фильтра выше: раскладка «Задать вопрос преподавателю». Пусто = таблица, прописанная в самом скрипте (SPREADSHEET_ID). В названии таблицы должно быть <code>(Moodle)</code>, а у аккаунта, от имени которого развёрнут скрипт, — право её редактировать.';
$string['routes'] = 'Формы со своей таблицей';
$string['routes_desc'] = 'По одной форме на строку: <code>cmid = ссылка на Google-таблицу</code>, например <code>12951 = https://docs.google.com/spreadsheets/d/…/edit</code>. Необязательно — имя листа через <code>|</code>. Такая форма пересылается независимо от фильтров, в раскладке «одна колонка на каждый пункт формы»; колонки скрипт создаёт сам. В названии таблицы должно быть <code>(Moodle)</code>. Заголовки колонок можно переименовывать, колонки — двигать и добавлять свои: скрипт находит колонку по примечанию к заголовку.';
$string['statuslink'] = 'Какие формы куда идут, очередь отправки и кнопка «Отправить все ответы заново» — на <a href="{$a}">странице состояния</a>.';
$string['statuspage'] = 'Обратная связь → Google-таблица: что куда идёт';
$string['statusintro'] = 'Формы «Обратной связи» из курсов, где пересылка настроена. Серым — формы, которые в таблицу не попадают.';
$string['status_disabled'] = 'Пересылка выключена в настройках: новые ответы в таблицы не уходят.';
$string['status_nowebhook'] = 'Не задан URL веб-приложения Apps Script: новые ответы в таблицы не уходят.';
$string['col_form'] = 'Форма';
$string['col_target'] = 'Куда идут ответы';
$string['col_responses'] = 'Ответов';
$string['col_last'] = 'Последний ответ';
$string['col_result'] = 'Что ответил скрипт';
$string['target_default'] = 'Таблица вопросов';
$string['target_builtin'] = '(задана в скрипте)';
$string['target_own'] = 'Своя таблица';
$string['target_none'] = 'не пересылается';
$string['resend'] = 'Отправить все ответы заново';
$string['resendconfirm'] = 'Отправить в таблицу все ответы формы «{$a}»? Строки, которые в таблице уже есть, не задвоятся.';
$string['resendqueued'] = 'Форма «{$a->name}» поставлена в очередь, ответов: {$a->count}. Таблица заполнится в течение нескольких минут.';
$string['ping'] = 'Проверить связь со скриптом';
$string['pingok'] = 'Скрипт отвечает, версия {$a}.';
$string['pingfail'] = 'Скрипт не отвечает как надо: {$a}';
$string['queue'] = 'Очередь отправки';
$string['queuestate'] = 'Задач в очереди: {$a->pending}, из них с ошибкой (ждут повтора): {$a->failing}.';
$string['recent'] = 'Последние отправки';
$string['recentnote'] = '«Ошибка» у отдельной отправки — не потеря ответа: задача повторится сама, а строки, которые уже в таблице, скрипт второй раз не запишет. Пометка «try 2» или «try 3» значит, что Google ответил не с первого раза — с ним это бывает. Тревожный признак один: задачи с ошибкой, которые стоят в очереди дольше часа.';
$string['fail'] = 'Ошибка';
$string['taskname'] = 'Отправить ответ из «Обратной связи» в Google-таблицу';
$string['taskresend'] = 'Отправить заново все ответы формы в Google-таблицу';
$string['taskarchive'] = 'Перенести ответы закрытых форм на вкладку «Архив»';
$string['closes'] = 'закроется {$a}';
$string['closedwaiting'] = 'закрыта {$a}, ответы уйдут в «Архив» в течение часа';
$string['archived'] = 'закрыта {$a}, ответы в «Архиве»';
$string['privacy:metadata:webhook'] = 'Ответы (курс, активность, имя и e-mail респондента, ответы) отправляются в настроенное веб-приложение Google Apps Script, чтобы сотрудники читали их в Google-таблице.';
$string['privacy:metadata:webhook:answers'] = 'Ответы, данные в активности «Обратная связь».';
$string['privacy:metadata:webhook:email'] = 'E-mail респондента.';
$string['privacy:metadata:webhook:fullname'] = 'Полное имя респондента.';
