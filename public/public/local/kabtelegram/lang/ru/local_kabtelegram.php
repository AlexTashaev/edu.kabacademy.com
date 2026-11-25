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
 * Русские строки.
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['botheading'] = 'Бот';
$string['botusername'] = 'Имя бота';
$string['botusername_desc'] = 'Telegram-бот SendPulse, без @. Пусто — ссылка бота ведёт на главную, а /kab/tgbot.php по-прежнему уводит в WordPress.';
$string['cachedef_sptoken'] = 'Токен доступа к API SendPulse';
$string['channelbottoken'] = 'Токен бота для счёта подписчиков';
$string['channelbottoken_desc'] = 'Любой бот из BotFather; для публичного канала админом он быть не обязан. Пусто — подписчиков не считаем.';
$string['channelchat'] = 'Канал для счёта подписчиков';
$string['channelchat_desc'] = '@имя канала (или его числовой id). Считаем раз в час.';
$string['channelheading'] = 'Канал курса';
$string['channelurl'] = 'Ссылка на канал';
$string['channelurl_desc'] = 'Куда ведёт go.php?to=channel (https://t.me/… или ссылка-приглашение).';
$string['col_bot_click'] = 'Перешли в бота';
$string['col_bot_linked'] = 'Привязали бота';
$string['col_channel_click'] = 'Перешли в канал';
$string['col_lesson_click'] = 'Перешли в урок';
$string['col_people'] = 'Зачислено';
$string['defaultlesson'] = 'Урок по умолчанию';
$string['defaultlesson_desc'] = 'Адрес на этом сайте для go.php?to=lesson без url= (старые ссылки из писем о заказе WordPress). Пусто — «Мои курсы».';
$string['errorsync'] = 'Не удалось записать переменные в контакт SendPulse {$a}';
$string['extravars'] = 'Дополнительные переменные';
$string['extravars_desc'] = 'По строке: <code>переменная_SendPulse = ключ</code>. Ключи: userid, firstname, lastname, email, phone, country, city, course, courseid, orderid, quizscore, persona, campaign. Всегда уходят: kab_first_name, kab_last_name, kab_email, kab_phone, kab_country, kab_city, kab_order_id, kab_course_name. Переменная должна быть заведена в боте.';
$string['flowid'] = 'Flow ID SendPulse';
$string['flowid_desc'] = 'Флоу, который запускает deep-link tg.pulse.is/&lt;бот&gt;?start=&lt;flow&gt;&amp;token=&lt;токен&gt;. Пусто — вошедшие получают простую ссылку t.me без данных.';
$string['otherheading'] = 'Урок и статистика';
$string['pluginname'] = 'Воронка Telegram';
$string['privacy:metadata:contact'] = 'Кто запустил бота по нашей ссылке: контакт SendPulse и Telegram ID.';
$string['privacy:metadata:contact:contactid'] = 'ID контакта SendPulse';
$string['privacy:metadata:contact:telegramid'] = 'Telegram ID пользователя';
$string['privacy:metadata:contact:timecreated'] = 'Когда запущен бот';
$string['privacy:metadata:contact:timesynced'] = 'Когда данные последний раз записаны в контакт';
$string['privacy:metadata:contact:userid'] = 'Пользователь';
$string['privacy:metadata:event'] = 'Переходы в бота, канал и урок.';
$string['privacy:metadata:event:event'] = 'Куда переход';
$string['privacy:metadata:event:place'] = 'Где стояла ссылка';
$string['privacy:metadata:event:timecreated'] = 'Когда';
$string['privacy:metadata:event:userid'] = 'Пользователь';
$string['privacy:metadata:sendpulse'] = 'Когда бот запущен по нашей ссылке, данные уходят в контакт SendPulse.';
$string['privacy:metadata:sendpulse:city'] = 'Город';
$string['privacy:metadata:sendpulse:country'] = 'Страна';
$string['privacy:metadata:sendpulse:course'] = 'Курс';
$string['privacy:metadata:sendpulse:email'] = 'E-mail';
$string['privacy:metadata:sendpulse:firstname'] = 'Имя';
$string['privacy:metadata:sendpulse:lastname'] = 'Фамилия';
$string['privacy:metadata:sendpulse:phone'] = 'Телефон';
$string['privacy:metadata:sendpulse:quizscore'] = 'Балл квиза из ссылки записи';
$string['privacy:metadata:token'] = 'Токен ссылки в бота.';
$string['privacy:metadata:token:timecreated'] = 'Когда создан';
$string['privacy:metadata:token:timeexpires'] = 'Когда истекает';
$string['privacy:metadata:token:token'] = 'Токен';
$string['privacy:metadata:token:userid'] = 'Пользователь';
$string['retentiondays'] = 'Хранить статистику, дней';
$string['retentiondays_desc'] = 'Более старые переходы удаляются каждую ночь. 0 — хранить всегда.';
$string['settingsintro'] = '<p>Воронка старого сайта WordPress (kab-telegram-enrollment) внутри Moodle.</p>
<p>Адреса кнопок: бот <code>{$a->bot}</code>, канал <code>{$a->channel}</code>, урок <code>{$a->lesson}</code>.
Гостиные ссылаются на <code>/kab/tgbot.php</code> — он ведёт сюда, как только задан бот.</p>
<p>Вебхук для SendPulse (события new_subscriber и incoming_message): <code>{$a->webhook}</code></p>';
$string['spclientid'] = 'API ID SendPulse';
$string['spclientid_desc'] = 'SendPulse → Настройки аккаунта → API.';
$string['spclientsecret'] = 'API Secret SendPulse';
$string['spclientsecret_desc'] = '';
$string['spheading'] = 'API SendPulse и вебхук';
$string['statscohort'] = 'Зачисленные на «{$a->course}» с {$a->since}';
$string['statscourse'] = 'ID курса';
$string['statsdaily'] = 'По дням, последние {$a} дн.';
$string['statsday'] = 'День';
$string['statsdays'] = 'Дней';
$string['statsheading'] = 'Воронка Telegram — статистика';
$string['statsnone'] = 'За этот период переходов нет.';
$string['statsnote'] = 'session — вошедший человек, anonymous — не вошёл (данные в бота не уходят). channel_members — последний за день счёт подписчиков канала. История до переезда — «wp kab-te-stats» на старом сайте.';
$string['statsshow'] = 'Показать';
$string['statssince'] = 'Зачислены с';
$string['taskchannelcount'] = 'Воронка Telegram: подписчики канала';
$string['taskcleanup'] = 'Воронка Telegram: чистка';
$string['tasksynccontact'] = 'Воронка Telegram: данные в контакт SendPulse';
$string['tokendays'] = 'Срок токена, дней';
$string['tokendays_desc'] = 'Сколько живёт ссылка человека в бота после последнего перехода (в WordPress — 7).';
$string['webhooknosecret'] = '(секрета нет — вебхук отвечает 403; задайте его ниже)';
$string['webhooksecret'] = 'Секрет вебхука';
$string['webhooksecret_desc'] = 'Часть адреса вебхука (?key=) или заголовок X-Webhook-Secret. Заводится при установке. Сменили — смените адрес и в SendPulse.';
