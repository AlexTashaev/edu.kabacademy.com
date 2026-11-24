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
 * @package    auth_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Вход через Telegram';
$string['auth_kabtelegramdescription'] = 'Добавляет на страницу входа кнопку «Войти через Telegram» (Telegram Login). Входить так могут только существующие аккаунты, к которым привязан Telegram: аккаунты плагин не создаёт, пароли и способ аутентификации пользователей не меняет. Привязать Telegram пользователь может сам: Настройки → Учётная запись → Вход через Telegram. Неизвестный Telegram получает предложение войти по ссылке на почту или с паролем и привязать его.<br><br>Нужно: токен бота входа (ниже) и домен этого бота в BotFather (<code>/setdomain</code>) — <code>{$a}</code>. Пока токен не задан, кнопки на странице входа нет.';
$string['loginbutton'] = 'Войти через Telegram';
$string['logintitle'] = 'Вход через Telegram';
$string['loggingin'] = 'Входим… Если ничего не происходит, нажмите кнопку.';
$string['notfinished'] = 'Вход через Telegram не завершён: Telegram не вернул подтверждение. Попробуйте ещё раз или войдите по ссылке на почту.';
$string['needjs'] = 'Для входа через Telegram в браузере должен быть включён JavaScript.';
$string['backtologin'] = 'К странице входа';
$string['backtolink'] = 'Назад';
$string['stateinvalid'] = 'Вход через Telegram не удался: страница устарела или открыта в другом браузере. Начните вход ещё раз со страницы входа.';
$string['resultinvalid'] = 'Telegram не подтвердил вход, или подтверждение устарело. Попробуйте ещё раз.';
$string['loginrefused'] = 'Через Telegram в этот аккаунт сейчас войти нельзя. Войдите по ссылке на почту или с паролем.';
$string['notenabled'] = 'Вход через Telegram на этом сайте не включён.';
$string['linktitle'] = 'Вход через Telegram';
$string['linkdone'] = 'Telegram {$a} привязан. Теперь можно входить через Telegram.';
$string['linktaken'] = 'Этот Telegram уже привязан к другому аккаунту на сайте. Войдите в тот аккаунт или напишите в поддержку.';
$string['unlinkdone'] = 'Telegram отвязан. Входить через него больше нельзя.';
$string['notlinkedtitle'] = 'Telegram ещё не привязан';
$string['notlinkedintro'] = 'Telegram {$a} не связан ни с одним аккаунтом на сайте. Войдите обычным способом — по ссылке на почту или с паролем, — и мы предложим привязать этот Telegram. После этого входить можно будет и через Telegram.';
$string['notlinkedemail'] = 'Войти по ссылке на почту';
$string['notlinkedpassword'] = 'Войти с паролем';
$string['confirmlink'] = 'Привязать Telegram {$a->telegram} к аккаунту {$a->account}? После этого в аккаунт можно будет входить через этот Telegram.';
$string['confirmreplace'] = 'Сейчас к аккаунту привязан другой Telegram — {$a}. Он будет отвязан.';
$string['confirmlinkyes'] = 'Привязать';
$string['confirmlinkno'] = 'Не привязывать';
$string['linkedstatus'] = 'К вашему аккаунту привязан Telegram {$a->telegram} (с {$a->since}). Через него можно входить на сайт кнопкой «Войти через Telegram».';
$string['lastlogin'] = 'Последний вход через Telegram: {$a}.';
$string['lastloginnever'] = 'Через Telegram ещё не входили.';
$string['unlink'] = 'Отвязать Telegram';
$string['relink'] = 'Привязать другой Telegram';
$string['notlinkedstatus'] = 'Telegram не привязан. Привяжите его — и сможете входить на сайт кнопкой «Войти через Telegram», без пароля и письма.';
$string['linkbutton'] = 'Привязать Telegram';
$string['bottoken'] = 'Токен бота';
$string['bottoken_desc'] = 'Токен бота входа от BotFather, вида <code>123456789:AA…</code>. Им проверяется подпись ответов Telegram; число до двоеточия — id бота.';
$string['maxage'] = 'Срок ответа Telegram (минут)';
$string['maxage_desc'] = 'Ответ Telegram старше этого срока не принимается. Не больше суток (1440).';
$string['allowedauths'] = 'Кому разрешён вход через Telegram';
$string['allowedauths_desc'] = 'Через Telegram входят только аккаунты с одним из этих способов аутентификации. Заблокированные, неподтверждённые и удалённые аккаунты не входят никогда.';
$string['privacy:metadata:links'] = 'Привязка Telegram к аккаунту: какой Telegram, когда привязан и когда через него последний раз входили.';
$string['privacy:metadata:links:userid'] = 'Аккаунт на сайте.';
$string['privacy:metadata:links:tgid'] = 'Id пользователя Telegram.';
$string['privacy:metadata:links:tgusername'] = 'Имя пользователя (@username) в Telegram.';
$string['privacy:metadata:links:tgname'] = 'Имя и фамилия из Telegram.';
$string['privacy:metadata:links:source'] = 'Откуда привязка: перенесена со старого сайта или сделана самим пользователем.';
$string['privacy:metadata:links:timecreated'] = 'Когда Telegram привязан.';
$string['privacy:metadata:links:timelastlogin'] = 'Когда последний раз входили через Telegram.';
$string['privacy:metadata:telegram'] = 'Для входа браузер пользователя открывает страницу Telegram (oauth.telegram.org), где пользователь подтверждает вход.';
$string['privacy:metadata:telegram:origin'] = 'Адрес сайта, на который Telegram возвращает подтверждение.';
$string['privacy:path'] = 'Вход через Telegram';
