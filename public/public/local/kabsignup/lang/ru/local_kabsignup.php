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
$string['settingsintro'] = 'Публичная страница записи на курс: аккаунт создаётся по имени и e-mail, человек зачисляется на курс и получает письмо. Ссылка для лендингов: <code>/local/kabsignup/index.php?id=ID_КУРСА&amp;camp=МЕТКА</code> плюс метки рекламы и квиза. Оформление страницы и адрес «спасибо» задаются в <a href="{$a->campaigns}">кампаниях</a>, заявки и выгрузка — на странице <a href="{$a->requests}">«Заявки»</a>.';

$string['signuptitle'] = 'Запись на курс';
$string['signupheading'] = 'Запись на курс «{$a}»';
$string['signupintro'] = 'Укажите имя, e-mail и телефон — мы запишем вас на курс. Если вы уже учились у нас, укажите тот же адрес: запись добавится к вашему аккаунту. Данные для входа придут на почту.';
$string['firstnamelabel'] = 'Имя';
$string['lastnamelabel'] = 'Фамилия (не обязательно)';
$string['emaillabel'] = 'E-mail';
$string['phonelabel'] = 'Телефон';
$string['submit'] = 'Записаться';
$string['emailtoolong'] = 'Слишком длинный адрес.';
$string['invalidphone'] = 'Проверьте номер: нужен номер телефона с кодом страны, например +7 999 123-45-67.';

$string['notavailable'] = 'Запись на этот курс сейчас не ведётся.';
$string['throttled'] = 'Слишком много попыток с этим адресом или устройством. Если вы уже отправляли форму, письмо со ссылкой для входа должно быть на вашей почте — проверьте и папку «Спам». Чуть позже можно попробовать ещё раз.';
$string['existstext'] = 'Аккаунт с этим адресом уже есть. Войдите в него — и запись на курс добавится автоматически.';
$string['loginbutton'] = 'Войти';
$string['magicbutton'] = 'Войти по ссылке на почту';
$string['prereqintro'] = 'Этот курс — продолжение: запись открыта для тех, кто учился на {$a}. Укажите e-mail, под которым вы учились.';
$string['noprereqheading'] = 'Запись только для продолжающих';
$string['noprereqtext'] = 'Запись на «{$a->course}» открыта только для тех, кто учился на {$a->required}. С этим адресом такой записи мы не нашли. Если вы учились у нас, укажите тот же e-mail, под которым были записаны, или войдите на сайт. Не помните адрес — напишите в поддержку.';
$string['noprereqtextloggedin'] = 'Запись на «{$a->course}» открыта только для тех, кто учился на {$a->required}. У вашего аккаунта ({$a->email}) такой записи нет. Если вы учились под другим адресом — напишите в поддержку, мы проверим.';
$string['tryagain'] = 'Указать другой адрес';

$string['thanksheading'] = 'Вы записаны!';
$string['thankstext'] = 'Вы записаны на курс «{$a}». Мы отправили на вашу почту письмо со ссылкой для входа — откройте его, чтобы начать учиться. Если письма нет, проверьте папку «Спам».';
$string['thankstextloggedin'] = 'Вы записаны на курс «{$a}» и уже вошли в аккаунт — можно начинать. Логин и пароль для следующих входов мы отправили на почту.';
$string['alreadyheading'] = 'Вы уже записаны';
$string['alreadytext'] = 'Вы уже записаны на курс «{$a}» — повторно записываться не нужно.';
$string['alreadytextguest'] = 'Вы уже записаны на курс «{$a}» — повторно записываться не нужно. Мы отправили на вашу почту письмо со ссылкой для входа. Если письма нет, проверьте папку «Спам».';
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
$string['emailnewtext'] = 'Здравствуйте, {$a->firstname}!

Вы записаны на курс «{$a->coursename}» на сайте {$a->sitename}. Вы уже вошли на сайт — можно начинать:
{$a->courseurl}

Данные для следующих входов:
Логин: {$a->username}
Пароль: {$a->password}
Страница входа: {$a->loginurl}

Сохраните это письмо. Пароль можно сменить в своём профиле на сайте. Если забудете пароль, войти можно по ссылке на почту — пароль не понадобится: {$a->requesturl}

Если вы не записывались на курс, просто не обращайте внимания на это письмо.';
$string['emailnewhtml'] = '<p>Здравствуйте, {$a->firstname}!</p>
<p>Вы записаны на курс «{$a->coursename}» на сайте {$a->sitename}. Вы уже вошли на сайт — <a href="{$a->courseurl}">можно начинать</a>.</p>
<p>Данные для следующих входов:<br>
Логин: <b>{$a->username}</b><br>
Пароль: <b>{$a->password}</b><br>
Страница входа: <a href="{$a->loginurl}">{$a->loginurl}</a></p>
<p>Сохраните это письмо. Пароль можно сменить в своём профиле на сайте. Если забудете пароль, войти можно <a href="{$a->requesturl}">по ссылке на почту</a> — пароль не понадобится.</p>
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
$string['campaigns'] = 'Кампании записи';
$string['prerequisites'] = 'Условия записи';
$string['prerequisites_desc'] = 'Курсы, на которые можно записаться только после другого курса (вторая часть — только для учившихся на первой). По строке на курс: ID курса, затем через | — ID курсов-условий через запятую; достаточно записи на любой из них (активной или приостановленной). Пример: <code>240|236</code> или <code>240|236,210</code> (первая часть двух потоков). Не прошедшему условие аккаунт не создаётся. Студентов первой части, которые должны сохранить право на вторую, не отчислять, а приостанавливать: при отчислении запись удаляется.';
$string['defaultthankyou'] = 'Общая страница «спасибо»';
$string['defaultthankyou_desc'] = 'Куда вести после записи, когда ни у кампании, ни у курса своя страница не указана. Абсолютный https-адрес или локальный путь. Пусто — встроенная страница плагина.';
$string['existingpolicy'] = 'Если e-mail уже занят';
$string['existingpolicy_desc'] = '«Записывать сразу» — зачисляем существующий аккаунт и шлём ему ссылку для входа (автовхода нет). «Просить войти» — показываем страницу «у вас уже есть аккаунт» с кнопками входа; после входа запись добавляется автоматически.';
$string['policyenrol'] = 'Записывать сразу';
$string['policylogin'] = 'Просить войти';
$string['phonerequired'] = 'Телефон обязателен';
$string['phonerequired_desc'] = 'Без телефона форма не отправляется. Телефон записывается в поле профиля «Телефон» (phone1) нового аккаунта и участвует в поиске дублей.';
$string['ipinfotoken'] = 'Токен ipinfo.io';
$string['ipinfotoken_desc'] = 'Для города и страны нового аккаунта по IP — как на WordPress. Токен можно взять из пула ipinfo в опциях темы WP. Без токена запросы идут анонимно, с малым лимитом ipinfo. Если на сайте задан файл GeoLite2 (geoip2file), сначала используется он.';
$string['dupalertto'] = 'Письма о возможных дублях';
$string['dupalertto_desc'] = 'Если новый аккаунт похож на существующий (e-mail с точностью до точек, опечатка в домене, тот же телефон), сюда уходит письмо со ссылками на оба профиля. Принцип «один аккаунт»: сливает только админ вручную. Пусто — писем нет.';
$string['ratelimit'] = 'Заявок с одного адреса';
$string['ratelimit_desc'] = 'Сколько заявок принимается с одного e-mail за окно ниже. На один IP-адрес лимит в десять раз больше. Лишние попытки получают просьбу подождать.';
$string['ratewindow'] = 'Окно лимита (минут)';
$string['ratewindow_desc'] = 'За сколько минут считается лимит выше.';
$string['retentiondays'] = 'Хранение журнала (дней)';
$string['retentiondays_desc'] = 'Через сколько дней задача очистки удаляет строки журнала заявок. 0 — хранить вечно.';

$string['taskcleanup'] = 'Очистка журнала записи на курс';

$string['dupalertsubject'] = '[KAB] Возможный дубль аккаунта: {$a}';
$string['dupalertintro'] = 'При записи на курс появился новый аккаунт, похожий на существующий.';
$string['dupalertcourse'] = 'Курс: {$a}';
$string['dupalertnew'] = 'Новый аккаунт:';
$string['dupalertmatches'] = 'Похожие аккаунты:';
$string['dupalertfooter'] = 'Принцип «один аккаунт»: дубли сливаем только вручную после проверки.
Письмо отправлено автоматически (Moodle, local_kabsignup).';
$string['dupsignal_email'] = 'e-mail совпадает с точностью до точек';
$string['dupsignal_typo'] = 'похоже на опечатку в домене почты';
$string['dupsignal_local'] = 'то же имя ящика на другом домене (слабый сигнал)';
$string['dupsignal_phone'] = 'совпадает телефон';

$string['privacy:metadata:log'] = 'Журнал заявок на запись: кто записался, на какой курс, когда, с какого IP-адреса и по какой кампании. Введённый адрес хранится только в виде хеша.';
$string['privacy:metadata:log:courseid'] = 'Курс, на который записывались.';
$string['privacy:metadata:log:userid'] = 'Пользователь, которого записали или нашли по адресу.';
$string['privacy:metadata:log:emailhash'] = 'Хеш введённого адреса электронной почты — для ограничения частоты заявок.';
$string['privacy:metadata:log:newuser'] = 'Был ли аккаунт создан этой заявкой.';
$string['privacy:metadata:log:campaign'] = 'Метка рекламной кампании из ссылки.';
$string['privacy:metadata:log:status'] = 'Итог заявки.';
$string['privacy:metadata:log:flags'] = 'Итоги фоновой обработки: определён ли город, найдены ли похожие аккаунты.';
$string['privacy:metadata:ipinfo'] = 'Для нового аккаунта IP-адрес отправляется в ipinfo.io, чтобы определить город и страну.';
$string['privacy:metadata:ipinfo:ip'] = 'IP-адрес, с которого отправлена заявка.';
$string['privacy:metadata:log:ip'] = 'IP-адрес, с которого отправлена заявка.';
$string['privacy:metadata:log:timecreated'] = 'Когда отправлена заявка.';
$string['privacy:path'] = 'Заявки на запись';

$string['existsheading'] = 'У вас уже есть аккаунт';
$string['consenttext'] = 'Я прочитал(а) и принимаю {$a}';
$string['consentlink'] = 'правила и условия';
$string['consentrequired'] = 'Нужно согласие с правилами.';
$string['consenttextprivacy'] = 'Я принимаю {$a->offer} и даю согласие на обработку моих персональных данных в соответствии с {$a->privacy}';
$string['consentoffer'] = 'условия оферты';
$string['consentprivacy'] = 'политикой конфиденциальности';
$string['undeliverable'] = 'Проверьте адрес: на него не получится отправить письмо.';
$string['previewnotice'] = 'Предпросмотр: так страницу видит гость. Отправка формы здесь никого не записывает.';
$string['previewsubmitted'] = 'Предпросмотр: форма прошла проверку, но заявка не отправлена.';

$string['termsurl'] = 'Страница правил';
$string['termsurl_desc'] = 'Куда ведёт ссылка в обязательном флажке «Я прочитал(а) и принимаю правила и условия». Пусто — оферта витрины (local_kabshop), если она стоит; «-» — флажка нет.';
$string['personas'] = 'Персоны';
$string['personas_desc'] = 'Допустимые значения параметра <code>pres</code> через запятую (персона объявления). Персона из списка записывается в поле профиля ниже — по нему в курсе открывается гостиная этой персоны — и подставляется вместо <code>{pres}</code> в адрес «спасибо» кампании. Пусто — персоны не используются.';
$string['personafield'] = 'Поле профиля для персоны';
$string['personafield_desc'] = 'Краткое имя поля профиля, в которое пишется персона (по умолчанию <code>kab_persona</code>). Перезаписывается и у существующих аккаунтов — как делал WordPress. Поля нет — ничего не пишется.';
$string['capiheading'] = 'Событие регистрации для Facebook (Conversions API)';
$string['capiheading_desc'] = 'После первой записи на курс сервер шлёт CompleteRegistration с тем же event_id, что получает страница «спасибо» (параметр <code>eid</code>): Meta склеивает браузерное и серверное событие в одно. Повторная запись события не даёт. Включается у кампании.';
$string['capipixel'] = 'ID пикселя';
$string['capipixel_desc'] = 'Пиксель, на который оптимизируются кампании. Пусто — событие не шлётся.';
$string['capitoken'] = 'Токен Conversions API';
$string['capitoken_desc'] = 'Токен доступа из Events Manager (сейчас лежит в wp-config WordPress).';
$string['capiversion'] = 'Версия Graph API';
$string['capiversion_desc'] = 'Например <code>v23.0</code>. Менять, когда Meta объявит версию устаревшей.';
$string['capitestcode'] = 'Код тестовых событий';
$string['capitestcode_desc'] = 'Только для проверки: с кодом события видны в Events Manager → Test Events и не идут в статистику. После проверки очистить.';
$string['clearoutheading'] = 'Проверка адреса (Clearout)';
$string['clearoutheading_desc'] = 'Новый адрес (которого нет в Moodle) проверяется при отправке формы. Отказ — только для несуществующих и одноразовых адресов; «неизвестно» и любой сбой сервиса запись пропускают.';
$string['clearouttoken'] = 'Токен Clearout';
$string['clearouttoken_desc'] = 'API-токен Clearout (сейчас в настройках плагина Clearout на WordPress). Пусто — проверки нет.';
$string['clearouttimeout'] = 'Таймаут проверки (секунд)';
$string['clearouttimeout_desc'] = 'Сколько ждать ответа Clearout; не дождались — адрес принимается.';

$string['kabsignup:manage'] = 'Управлять кампаниями записи';
$string['kabsignup:viewreports'] = 'Смотреть и выгружать заявки на запись';

$string['campaigns_intro'] = 'Кампания задаёт оформление страницы записи (цвета, логотип, карточка курса, текст кнопки) и страницу «спасибо». Ссылка с ленда: адрес из колонки «Ссылка для ленда» плюс метки рекламы и квиза.';
$string['campaigns_none'] = 'Кампаний пока нет.';
$string['campaigns_paramsnote'] = 'Ленд и квиз дописывают к ссылке utm_*, utm_id, pres, клик-иды (fbclid, gclid, yclid…), quiz_score и qa — всё это попадает в журнал заявок. На «спасибо» уходят camp, utm_*, pres и eid.';
$string['campaign_add'] = 'Новая кампания';
$string['campaign_edit'] = 'Кампания «{$a}»';
$string['campaign_deleted'] = 'Кампания удалена.';
$string['campaign_deleteconfirm'] = 'Удалить кампанию «{$a}»? Ссылки с этой меткой продолжат записывать, но без оформления и своей страницы «спасибо». Журнал заявок не меняется.';
$string['campaign_hidden'] = 'выключена';
$string['campaign_preview'] = 'Предпросмотр';
$string['campaign_link'] = 'Ссылка для ленда';
$string['campaign_general'] = 'Кампания';
$string['campaign_slug'] = 'Метка';
$string['campaign_slug_help'] = 'Значение параметра <code>camp</code> в ссылке: латиница, цифры, дефис и подчёркивание, например <code>spring27</code>. Метка попадает в журнал заявок и в категории писем SendGrid.';
$string['campaign_slug_invalid'] = 'Только латиница, цифры, дефис и подчёркивание.';
$string['campaign_slug_taken'] = 'Такая метка уже есть.';
$string['campaign_name'] = 'Название';
$string['campaign_course'] = 'Курс';
$string['campaign_course_help'] = 'Курс, на который записывает ссылка без <code>id</code>. В списке — курсы из настройки «Курсы с открытой записью».';
$string['campaign_nocourse'] = '— только из ссылки —';
$string['campaign_visible'] = 'Включена';
$string['campaign_capi'] = 'Событие регистрации для Facebook';
$string['campaign_capi_help'] = 'Слать с сервера CompleteRegistration за первую запись (нужны пиксель и токен в настройках плагина).';
$string['campaign_thanks'] = 'Страница «спасибо»';
$string['campaign_thankyouurl'] = 'Адрес «спасибо»';
$string['campaign_thankyouurl_help'] = 'Абсолютный https-адрес или локальный путь. <code>{pres}</code> заменяется персоной объявления: <code>https://kabacademy.com/lp/spring27/thanks/{pres}/</code>. Пусто — страница курса, общая или встроенная.';
$string['campaign_thankyoufallback'] = 'Адрес без персоны';
$string['campaign_thankyoufallback_help'] = 'Куда вести, если в адресе есть <code>{pres}</code>, а персоны в ссылке нет или её нет в списке персон.';
$string['campaign_badurl'] = 'Нужен адрес https://… или локальный путь /…';
$string['campaign_nopres'] = 'Здесь {pres} не подставляется.';
$string['campaign_page'] = 'Страница записи';
$string['campaign_title'] = 'Заголовок';
$string['campaign_intro'] = 'Текст над формой (HTML)';
$string['campaign_cardtitle'] = 'Карточка: название курса';
$string['campaign_cardnote'] = 'Карточка: подпись';
$string['campaign_cardimage'] = 'Карточка: картинка (адрес)';
$string['campaign_pricelabel'] = 'Карточка: цена';
$string['campaign_buttonlabel'] = 'Текст кнопки';
$string['campaign_belowbutton'] = 'Подпись под кнопкой';
$string['campaign_theme'] = 'Оформление';
$string['campaign_theme_desc'] = 'Цвета — в формате #rrggbb. Пустое поле — как на остальном сайте.';
$string['campaign_colour_bg'] = 'Фон страницы';
$string['campaign_colour_card'] = 'Фон карточки с формой';
$string['campaign_colour_ink'] = 'Текст';
$string['campaign_colour_muted'] = 'Второстепенный текст';
$string['campaign_colour_accent'] = 'Ссылки и акцент';
$string['campaign_colour_button'] = 'Кнопка';
$string['campaign_colour_buttontext'] = 'Текст кнопки';
$string['campaign_fontheading'] = 'Шрифт заголовков (font-family)';
$string['campaign_fontbody'] = 'Шрифт текста (font-family)';
$string['campaign_fontcss'] = 'Стили шрифтов (адрес CSS)';
$string['campaign_fontcss_help'] = 'Таблица стилей с @font-face, например Google Fonts или файл на edu (<code>/kab/…</code>). Шрифты с kabacademy.com не подойдут: браузер не грузит их с другого сайта без разрешения (CORS).';
$string['campaign_logo'] = 'Логотип (адрес картинки)';
$string['campaign_badcolour'] = 'Цвет в формате #rrggbb или #rgb.';
$string['campaign_badfont'] = 'Только названия шрифтов через запятую.';

$string['requests'] = 'Заявки на запись';
$string['requests_intro'] = 'Журнал заявок по кампаниям. Выгрузка CSV — с метками рекламы, баллом и ответами квиза и персоной; без e-mail, телефона и IP.';
$string['requests_from'] = 'С';
$string['requests_to'] = 'по';
$string['requests_campaign'] = 'Кампания';
$string['requests_allcampaigns'] = 'все';
$string['requests_show'] = 'Показать';
$string['requests_status'] = 'Итог';
$string['requests_count'] = 'Заявок';
$string['requests_newusers'] = 'Новых аккаунтов';
$string['requests_none'] = 'За этот период заявок нет.';
$string['requests_download'] = 'Скачать CSV';
$string['requests_statuses'] = 'Итоги: created — новый аккаунт; enrolled — записан существующий; loggedin — записался, уже войдя; already — был записан раньше; exists — попросили войти; noprereq — нет записи на первую часть; badmail — адрес отклонён проверкой.';

$string['privacy:metadata:log:attribution'] = 'Метки рекламы и квиза из ссылки (utm, персона, идентификаторы клика, балл и ответы квиза).';
$string['privacy:metadata:log:quizscore'] = 'Балл квиза.';
$string['privacy:metadata:log:persona'] = 'Персона объявления.';
$string['privacy:metadata:log:eventid'] = 'Случайный идентификатор события регистрации для пикселя.';
$string['privacy:metadata:log:productid'] = 'Товар витрины, если заявка — покупка.';
$string['privacy:metadata:facebook'] = 'При первой записи по кампании с включённым событием в Facebook (Conversions API) уходит событие регистрации.';
$string['privacy:metadata:facebook:emailhash'] = 'Хеш адреса электронной почты (SHA-256).';
$string['privacy:metadata:facebook:phonehash'] = 'Хеш номера телефона (SHA-256).';
$string['privacy:metadata:facebook:ip'] = 'IP-адрес, с которого отправлена заявка.';
$string['privacy:metadata:facebook:useragent'] = 'Строка браузера.';
$string['privacy:metadata:facebook:fbclid'] = 'Идентификатор клика по объявлению.';
$string['privacy:metadata:clearout'] = 'Новый адрес электронной почты проверяется сервисом Clearout.';
$string['privacy:metadata:clearout:email'] = 'Адрес электронной почты.';

// 1.4.0: новичок в витрине — «e-mail → аккаунт → страница товара» (buy.php).
$string['buytitle'] = 'Аккаунт на учебной платформе';
$string['buynotavailable'] = 'Этот товар сейчас недоступен.';
$string['buyheading'] = '{$a}';
$string['buyintro'] = 'Цена: {$a}. Для оплаты нужен аккаунт на учебной платформе. Укажите имя и e-mail — мы создадим аккаунт и сразу вернём вас к оплате.';
$string['buyintrofree'] = '{$a}. Для записи нужен аккаунт на учебной платформе. Укажите имя и e-mail — мы создадим аккаунт и сразу вернём вас к записи.';
$string['buybuttonpay'] = 'Перейти к оплате';
$string['buybuttondonate'] = 'Перейти к взносу';
$string['buybuttonfree'] = 'Продолжить';
$string['buycreated'] = 'Аккаунт создан, вы вошли на сайт. Логин и пароль мы отправили на почту. Теперь можно оплатить.';
$string['buycreatedfree'] = 'Аккаунт создан, вы вошли на сайт. Логин и пароль мы отправили на почту. Осталось нажать «Записаться».';
$string['buyexistsheading'] = 'Проверьте почту';
$string['buyexiststext'] = 'Если адрес {$a} уже есть на платформе, мы отправили на него ссылку для входа. По ней вы вернётесь на эту страницу и продолжите. Если письма нет несколько минут, проверьте папку «Спам».';
$string['buyback'] = 'Вернуться к «{$a}»';
$string['buyhaveaccount'] = 'Уже учитесь у нас? Войти';
$string['emailaccountsubject'] = 'Ваш аккаунт на {$a->sitename}';
$string['emailaccounttext'] = 'Здравствуйте, {$a->firstname}!

Мы создали для вас аккаунт на сайте {$a->sitename}, и вы уже вошли. Вернуться к «{$a->productname}»:
{$a->producturl}

Данные для следующих входов:
Логин: {$a->username}
Пароль: {$a->password}
Страница входа: {$a->loginurl}

Сохраните это письмо. Пароль можно сменить в своём профиле на сайте.

Если вы не создавали аккаунт, просто не обращайте внимания на это письмо.';
$string['emailaccounthtml'] = '<p>Здравствуйте, {$a->firstname}!</p>
<p>Мы создали для вас аккаунт на сайте {$a->sitename}, и вы уже вошли. <a href="{$a->producturl}">Вернуться к «{$a->productname}»</a>.</p>
<p>Данные для следующих входов:<br>
Логин: <b>{$a->username}</b><br>
Пароль: <b>{$a->password}</b><br>
Страница входа: <a href="{$a->loginurl}">{$a->loginurl}</a></p>
<p>Сохраните это письмо. Пароль можно сменить в своём профиле на сайте.</p>
<p>Если вы не создавали аккаунт, просто не обращайте внимания на это письмо.</p>';
$string['privacy:metadata:preference:unverified'] = 'Отметка «почта ещё не подтверждена» у аккаунта, созданного формой записи: снимается первым входом по паролю или по ссылке из письма.';
