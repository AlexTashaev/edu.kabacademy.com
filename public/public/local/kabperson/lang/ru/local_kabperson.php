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
 * RU strings for local_kabperson.
 *
 * @package    local_kabperson
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'KAB: карточка человека';
$string['kabperson:view'] = 'Смотреть карточку человека (ПДн любого пользователя)';
$string['eventcardviewed'] = 'Карточку человека открыли';

// Настройки.
$string['settings_gate'] = 'Гейт страницы оператора (view.php)';
$string['settings_gate_desc'] = 'view.php открывает Dashboard App Chatwoot без входа в Moodle. Страница отдаёт данные только по email, у которого есть контакт с диалогом в чате (проверка через API Chatwoot ниже), с лимитом запросов по IP. Не настроено — анонимная страница закрыта (fail-closed). Залогиненным с правом local/kabperson:view гейт не нужен.';
$string['chatwootbase'] = 'Адрес Chatwoot';
$string['chatwootbase_desc'] = 'Публичный адрес Chatwoot, например https://support.edu.kabacademy.com. Токен уходит заголовком api-access-token (через дефис — его edge пропускает).';
$string['chatwootaccount'] = 'Chatwoot account id';
$string['chatwootaccount_desc'] = 'Числовой id аккаунта из адресной строки Chatwoot.';
$string['chatwoottoken'] = 'Токен API Chatwoot';
$string['chatwoottoken_desc'] = 'Access token агента/бота с чтением контактов. Хранится в настройках Moodle, не в git.';
$string['frameancestors'] = 'Кому можно встраивать в iframe (frame-ancestors)';
$string['frameancestors_desc'] = 'Через пробел — origin\'ы, которым разрешено открывать view.php в iframe (Dashboard App Chatwoot), напр. https://support.edu.kabacademy.com. От них же страница принимает контакт через postMessage. Пусто — встроить страницу может только сам Moodle, postMessage выключен.';
$string['settings_sources'] = 'Источники';
$string['settings_sources_desc'] = 'Сам Moodle всегда включён. Источник, который упал или не ответил за 4 с, помечается в карточке «error», остальные блоки показываются. kabbalah.info и books.kab.co.il — этап 5, пока «off».';
$string['settings_wp'] = 'Старый WP kabacademy (заказы WooCommerce)';
$string['settings_wp_desc'] = 'Блок оплат читается у /wp-json/kab/v1/person площадки (mu-plugin kab-person-card.php с версии 0.3, режим blocks=payments). Нужен, пока часть 1 и текущие оплаты живут в WooCommerce; после закрытия WP источник переключается на архив заказов.';
$string['enablewp'] = 'Читать заказы WP kabacademy';
$string['enablewp_desc'] = 'Без адреса и токена источник выключен («off»).';
$string['wpbase'] = 'Адрес WP';
$string['wpbase_desc'] = 'Например https://kabacademy.com (на dev-edu — dev-копия площадки).';
$string['wptoken'] = 'Токен X-KAB-Token';
$string['wptoken_desc'] = 'Значение KAB_PERSON_CARD_TOKEN_EDU из wp-config площадки (свой токен карточки Moodle, отдельно от токена бота). Не в git.';
$string['enablekabshop'] = 'Читать local_kabshop (оплаты академии)';
$string['enablekabshop_desc'] = 'Покупки, подписки и незавершённые оплаты из кассы Moodle. Выключить, если схема кассы поменяется и блок начнёт падать.';

// Страница карточки.
$string['viewtitle'] = 'Карточка человека';
$string['emaillabel'] = 'Email';
$string['show'] = 'Показать';
$string['refresh'] = 'Обновить';
$string['jsonlink'] = 'JSON';
$string['notfound'] = 'Нет в общей базе (пользователя Moodle с этим email нет).';
$string['sec_identity'] = 'Человек';
$string['sec_learning'] = 'Обучение';
$string['sec_payments'] = 'Оплаты академии';
$string['col_course'] = 'Курс';
$string['col_role'] = 'Роль';
$string['col_enrolstatus'] = 'Запись';
$string['col_progress'] = 'Прогресс';
$string['col_lastaccess'] = 'Последний заход';
$string['col_badges'] = 'Значки';
$string['col_date'] = 'Дата';
$string['col_product'] = 'Товар';
$string['col_kind'] = 'Тип';
$string['col_amount'] = 'Сумма';
$string['col_status'] = 'Статус';
$string['col_gateway'] = 'Шлюз';
$string['cohorts'] = 'Когорты';
$string['sitebadges'] = 'Значки сайта';
$string['orders'] = 'Покупки';
$string['subscriptions'] = 'Подписки';
$string['unfinished'] = 'Незавершённые оплаты';
$string['donortotal'] = 'Пожертвований всего';
$string['nextcharge'] = 'Следующее списание';
$string['failcount'] = 'Неудачных попыток';
$string['lasterror'] = 'Последняя ошибка';
$string['registered'] = 'В базе с';
$string['lastlogin'] = 'Последний вход';
$string['telegram'] = 'Telegram привязан';
$string['duplicates'] = 'Сигналы дубля';
$string['statusactive'] = 'активен';
$string['statussuspended'] = 'заблокирован';
$string['statusdeleted'] = 'удалён';
$string['nothing'] = '—';
$string['generatedat'] = 'Карточка собрана';
$string['fromcache'] = 'из кэша (до 10 минут давности)';
$string['waitingcontact'] = 'Ждём контакт из Chatwoot…';
$string['nocontactemail'] = 'У контакта в Chatwoot нет email (ни основного, ни «Другой email (из чата)») — карточку открыть не по чему.';
$string['unverified'] = '⚠ Адрес назван в чате и не подтверждён входом на сайт. О курсах и оплатах не говорить — попросите войти на сайте и написать оттуда.';
$string['wponly'] = 'В старом WP kabacademy этот человек есть:';
$string['col_source'] = 'Откуда';
$string['src_kabshop'] = 'касса Moodle';
$string['src_kabacademy_wp'] = 'WP kabacademy';
$string['nopaid'] = 'Оплат нет.';
$string['freerecords'] = 'Бесплатные записи ({$a})';
$string['refundednote'] = 'возврат {$a}';
$string['sig_same_name'] = 'тёзки в Moodle: id {$a}';
$string['sig_duplicate_email'] = 'тот же email у нескольких аккаунтов Moodle: id {$a}';
$string['sig_moodle_email_differs'] = 'в Moodle у человека другой email — нашли по связи из WP';
$string['sig_wp_email_differs'] = 'в WP у человека другой email';
$string['sig_wp_linked_to_moodle'] = 'WP-аккаунт связан с другим аккаунтом Moodle: id {$a}';
$string['sig_wp_no_moodle_link'] = 'WP-аккаунт не связан с Moodle';
$string['sig_wp_order_email_mismatch'] = 'есть заказ WP, оплаченный с другого email';

// Ошибки гейта.
$string['err_notconfigured'] = 'Доступ к Chatwoot не настроен';
$string['err_ratelimited'] = 'Слишком много запросов';
$string['err_bademail'] = 'Нужен корректный email';
$string['err_notacontact'] = 'По этому email нет контакта в чате';
$string['err_chatwoot'] = 'Не удалось проверить контакт (Chatwoot недоступен)';

// Privacy.
$string['privacy:metadata'] = 'Плагин «Карточка человека» сам ничего о человеке не хранит: только читает Moodle, local_kabshop и старый WP kabacademy и держит короткоживущий кэш.';
$string['privacy:metadata:chatwoot'] = 'Для гейта страницы оператора запрошенный email передаётся в систему поддержки Chatwoot — проверить, что этот email принадлежит контакту чата.';
$string['privacy:metadata:chatwoot:email'] = 'Искомый email.';
$string['privacy:metadata:kabacademy_wp'] = 'Для блока оплат запрошенный email и id пользователя Moodle передаются старому сайту kabacademy.com (WordPress), чтобы найти его заказы WooCommerce.';
$string['privacy:metadata:kabacademy_wp:email'] = 'Искомый email.';
$string['privacy:metadata:kabacademy_wp:moodleuserid'] = 'Id пользователя Moodle (поиск по связи, если email в WP другой).';

// Кэши.
$string['cachedef_person'] = 'Собранные карточки (10 минут на email)';
$string['cachedef_gate'] = 'Вердикты PII-гейта («email писал в чат»)';
$string['cachedef_ratelimit'] = 'Счётчики rate-limit по IP для view.php';
