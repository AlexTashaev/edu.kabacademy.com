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
 * @package    auth_kabmagic
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Вход по ссылке на почту';
$string['auth_kabmagicdescription'] = 'Добавляет на страницу входа кнопку «Войти по ссылке на почту». Пользователь вводит адрес электронной почты своего аккаунта и получает одноразовую ссылку, по которой входит без пароля. Плагин не проверяет и не меняет пароли и не назначается пользователям как способ аутентификации: он только впускает существующие аккаунты, у которых способ аутентификации — из списка ниже.';
$string['loginbutton'] = 'Войти по ссылке на почту';
$string['requesttitle'] = 'Вход по ссылке на почту';
$string['requestintro'] = 'Укажите адрес электронной почты, на который зарегистрирован ваш аккаунт. Мы пришлём письмо со ссылкой для входа — пароль не понадобится.';
$string['emaillabel'] = 'E-mail';
$string['sendlink'] = 'Отправить ссылку';
$string['sent'] = 'Если такой адрес есть, письмо со ссылкой для входа отправлено. Проверьте почту, в том числе папку «Спам»: ссылка работает один раз и недолго.';
$string['backtologin'] = 'К странице входа';
$string['logintitle'] = 'Вход';
$string['loggingin'] = 'Входим… Если ничего не происходит, нажмите кнопку.';
$string['loginconfirm'] = 'Войти';
$string['linkinvalid'] = 'Ссылка для входа недействительна, уже использована или устарела. Запросите новую.';
$string['requestnew'] = 'Запросить новую ссылку';
$string['notenabled'] = 'Вход по ссылке на почту на этом сайте не включён.';
$string['lifetime'] = 'Срок действия ссылки (минут)';
$string['lifetime_desc'] = 'Сколько минут ссылка остаётся действительной после отправки. Каждая ссылка работает один раз.';
$string['ratelimit'] = 'Запросов на один адрес';
$string['ratelimit_desc'] = 'Сколько ссылок можно запросить на один адрес за окно ниже. Лишние запросы молча игнорируются. На один IP-адрес лимит в десять раз больше.';
$string['ratewindow'] = 'Окно лимита (минут)';
$string['ratewindow_desc'] = 'За сколько минут считается лимит выше.';
$string['allowedauths'] = 'Кому разрешён вход по ссылке';
$string['allowedauths_desc'] = 'Ссылку получают только пользователи с одним из этих способов аутентификации. Остальные аккаунты (а также заблокированные, неподтверждённые и удалённые) игнорируются; форма всегда отвечает одинаково.';
$string['taskcleanup'] = 'Удаление устаревших ссылок для входа';
$string['emailsubject'] = 'Вход на {$a->sitename}';
$string['emailtext'] = 'Здравствуйте, {$a->firstname}!

Чтобы войти на {$a->sitename}, перейдите по ссылке:
{$a->link}

Ссылка действует {$a->minutes} мин. и работает один раз.
Если вы не запрашивали вход, просто не обращайте внимания на это письмо.';
$string['emailhtml'] = '<p>Здравствуйте, {$a->firstname}!</p>
<p>Чтобы войти на {$a->sitename}, перейдите по ссылке:<br><a href="{$a->link}">{$a->link}</a></p>
<p>Ссылка действует {$a->minutes} мин. и работает один раз.<br>Если вы не запрашивали вход, просто не обращайте внимания на это письмо.</p>';
$string['privacy:metadata:tokens'] = 'Запросы ссылок для входа: кто запросил ссылку, когда, с какого IP-адреса и когда ссылка была использована. Сама ссылка хранится только в виде хеша.';
$string['privacy:metadata:tokens:userid'] = 'Пользователь, которому выдана ссылка.';
$string['privacy:metadata:tokens:emailhash'] = 'Хеш введённого адреса электронной почты — для ограничения частоты запросов.';
$string['privacy:metadata:tokens:wantsurl'] = 'Страница, которую открыть после входа.';
$string['privacy:metadata:tokens:timecreated'] = 'Когда ссылка запрошена.';
$string['privacy:metadata:tokens:timeused'] = 'Когда ссылка использована.';
$string['privacy:metadata:tokens:ip'] = 'IP-адрес, с которого запрошена ссылка.';
$string['privacy:path'] = 'Ссылки для входа';
