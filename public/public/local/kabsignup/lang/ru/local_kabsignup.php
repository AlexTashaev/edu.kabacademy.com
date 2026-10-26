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
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Запись на курс';
$string['settingsintro'] = 'Публичная страница записи на курс: аккаунт создаётся по имени и e-mail, человек зачисляется на курс и получает письмо со ссылкой для входа. Ссылка для лендингов: <code>/local/kabsignup/index.php?id=ID_КУРСА&amp;camp=МЕТКА</code> (метка кампании не обязательна). UTM-параметры из ссылки дописываются к странице «спасибо».';

$string['signuptitle'] = 'Запись на курс';
$string['signupheading'] = 'Запись на курс «{$a}»';
$string['signupintro'] = 'Укажите имя и e-mail — мы запишем вас на курс. Если вы уже учились у нас, укажите тот же адрес: запись добавится к вашему аккаунту. Пароль не нужен — для входа мы пришлём ссылку на почту.';
$string['firstnamelabel'] = 'Имя';
$string['lastnamelabel'] = 'Фамилия (не обязательно)';
$string['emaillabel'] = 'E-mail';
$string['submit'] = 'Записаться';
$string['emailtoolong'] = 'Слишком длинный адрес.';

$string['notavailable'] = 'Запись на этот курс сейчас не ведётся.';
$string['throttled'] = 'Слишком много попыток с этим адресом или устройством. Если вы уже отправляли форму, письмо со ссылкой для входа должно быть на вашей почте — проверьте и папку «Спам». Чуть позже можно попробовать ещё раз.';
$string['existstext'] = 'Аккаунт с этим адресом уже есть. Войдите в него — и запись на курс добавится автоматически.';
$string['loginbutton'] = 'Войти';
$string['magicbutton'] = 'Войти по ссылке на почту';

$string['thanksheading'] = 'Вы записаны!';
$string['thankstext'] = 'Вы записаны на курс «{$a}». Мы отправили на вашу почту письмо со ссылкой для входа — откройте его, чтобы начать учиться. Если письма нет, проверьте папку «Спам».';
$string['thankstextloggedin'] = 'Вы записаны на курс «{$a}» и уже вошли в аккаунт — можно начинать.';
$string['thankstextgeneric'] = 'Мы отправили на вашу почту письмо со ссылкой для входа — откройте его, чтобы начать учиться.';
$string['gotocourse'] = 'Перейти к курсу';

$string['emailsubject'] = 'Вы записаны на курс «{$a->coursename}»';
$string['emailtext'] = 'Здравствуйте, {$a->firstname}!

Вы записаны на курс «{$a->coursename}» на сайте {$a->sitename}.

Чтобы начать учиться, войдите по ссылке:
{$a->link}

Ссылка действует {$a->minutes} мин. и работает один раз. Если она устарела, запросите новую на странице входа: {$a->requesturl}

Если вы не записывались на курс, просто не обращайте внимания на это письмо.';
$string['emailhtml'] = '<p>Здравствуйте, {$a->firstname}!</p>
<p>Вы записаны на курс «{$a->coursename}» на сайте {$a->sitename}.</p>
<p>Чтобы начать учиться, войдите по ссылке:<br><a href="{$a->link}">{$a->link}</a></p>
<p>Ссылка действует {$a->minutes} мин. и работает один раз.<br>Если она устарела, запросите новую на <a href="{$a->requesturl}">странице входа</a>.</p>
<p>Если вы не записывались на курс, просто не обращайте внимания на это письмо.</p>';
$string['emailtextnolink'] = 'Здравствуйте, {$a->firstname}!

Вы записаны на курс «{$a->coursename}» на сайте {$a->sitename}.

Чтобы начать учиться, откройте страницу входа:
{$a->loginurl}

Если вы не помните пароль, на странице входа есть ссылка «Забыли пароль?».

Если вы не записывались на курс, просто не обращайте внимания на это письмо.';
$string['emailhtmlnolink'] = '<p>Здравствуйте, {$a->firstname}!</p>
<p>Вы записаны на курс «{$a->coursename}» на сайте {$a->sitename}.</p>
<p>Чтобы начать учиться, откройте <a href="{$a->loginurl}">страницу входа</a>. Если вы не помните пароль, там есть ссылка «Забыли пароль?».</p>
<p>Если вы не записывались на курс, просто не обращайте внимания на это письмо.</p>';

$string['courses'] = 'Курсы с открытой записью';
$string['courses_desc'] = 'По строке на курс: ID курса, затем через | — адрес страницы «спасибо» для этого курса (не обязателен). Запись работает только для перечисленных курсов. Пример: <code>236|https://kabacademy.com/thankyou-autumn/</code>';
$string['campaigns'] = 'Страницы «спасибо» кампаний';
$string['campaigns_desc'] = 'По строке на кампанию: метка, затем через | — адрес страницы «спасибо». Метка передаётся в ссылке записи параметром <code>camp</code> и имеет приоритет над страницей курса. Пример: <code>autumn26|https://kabacademy.com/ty-autumn26/</code>';
$string['defaultthankyou'] = 'Общая страница «спасибо»';
$string['defaultthankyou_desc'] = 'Куда вести после записи, когда ни у кампании, ни у курса своя страница не указана. Абсолютный https-адрес или локальный путь. Пусто — встроенная страница плагина.';
$string['existingpolicy'] = 'Если e-mail уже занят';
$string['existingpolicy_desc'] = '«Записывать сразу» — зачисляем существующий аккаунт и шлём ему ссылку для входа (автовхода нет). «Просить войти» — показываем страницу «у вас уже есть аккаунт» с кнопками входа; после входа запись добавляется автоматически.';
$string['policyenrol'] = 'Записывать сразу';
$string['policylogin'] = 'Просить войти';
$string['ratelimit'] = 'Заявок с одного адреса';
$string['ratelimit_desc'] = 'Сколько заявок принимается с одного e-mail за окно ниже. На один IP-адрес лимит в десять раз больше. Лишние попытки получают просьбу подождать.';
$string['ratewindow'] = 'Окно лимита (минут)';
$string['ratewindow_desc'] = 'За сколько минут считается лимит выше.';
$string['retentiondays'] = 'Хранение журнала (дней)';
$string['retentiondays_desc'] = 'Через сколько дней задача очистки удаляет строки журнала заявок. 0 — хранить вечно.';

$string['taskcleanup'] = 'Очистка журнала записи на курс';

$string['privacy:metadata:log'] = 'Журнал заявок на запись: кто записался, на какой курс, когда, с какого IP-адреса и по какой кампании. Введённый адрес хранится только в виде хеша.';
$string['privacy:metadata:log:courseid'] = 'Курс, на который записывались.';
$string['privacy:metadata:log:userid'] = 'Пользователь, которого записали или нашли по адресу.';
$string['privacy:metadata:log:emailhash'] = 'Хеш введённого адреса электронной почты — для ограничения частоты заявок.';
$string['privacy:metadata:log:newuser'] = 'Был ли аккаунт создан этой заявкой.';
$string['privacy:metadata:log:campaign'] = 'Метка рекламной кампании из ссылки.';
$string['privacy:metadata:log:status'] = 'Итог заявки.';
$string['privacy:metadata:log:ip'] = 'IP-адрес, с которого отправлена заявка.';
$string['privacy:metadata:log:timecreated'] = 'Когда отправлена заявка.';
$string['privacy:path'] = 'Заявки на запись';
