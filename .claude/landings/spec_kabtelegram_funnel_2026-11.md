# Спека: воронка Telegram без WordPress — `local_kabtelegram` 0.1

**Дата:** 25.11.2026 · **Статус:** spec + плагин в ветке `claude/kabtelegram-funnel`, проверка на dev-edu;
на прод — по слову владельца · **Основа:** этап 4 спеки `spec_campaign_signup_2026-11.md` (§7), с одним
изменением: статистику переходов и счёт подписчиков канала **переносим** (в §7 было «не переносим»).

**Связано:** WP-плагин `kab-telegram-enrollment` 2.22.0 (приватный репо kabacademy.com,
`wp-content/plugins/kab-telegram-enrollment/`); спека выключения WP (kabacademy.com, ветка
`claude/wp-close-week`, `.claude/analysis/spec_wp_shutdown_2026-11.md`, роутер `wp-content/kab-router.php`);
мост гостиных `public/public/kab/tgbot.php` (этот репо).

---

## 0. Суть

WordPress kabacademy.com выключается в ближайшие дни (решение владельца 24.11.2026). Вместе с ним
перестают работать все куски воронки Telegram, которые держит `kab-telegram-enrollment`: кнопка бота
и ссылка канала в гостиных, вебхук SendPulse, статистика. Переносим живое в Moodle — новый
локальный плагин `local_kabtelegram`. Человека Moodle узнаёт по своей сессии, поэтому подписи
Ed25519 между сайтами больше не нужны: переход, данные для бота и вебхук — всё на edu.

Живость на 25.11.2026 (`wp kab-te-stats`, последние 30 дней): переходы в канал из гостиных 0–10 в день,
в бота 0–5, ссылки «в курс» из писем 0–7, подписчиков канала `@osen2026MAK` ~1180 (снимок раз в час).

## 1. Что переносим и куда

| WordPress (`kab-telegram-enrollment`) | Moodle (`local_kabtelegram`) |
|---|---|
| `admin-ajax.php?action=kab_te_bot_go` (+ подпись `mu/ts/sig` от `/kab/tgbot.php`) | `/local/kabtelegram/go.php?to=bot` — человек по сессии Moodle |
| `admin-ajax.php?action=kab_te_channel_go` | `go.php?to=channel` |
| `admin-ajax.php?action=kab_te_lesson_go&order&key` (письмо о заказе WP) | роутер WP → `go.php?to=lesson&src=email` (§6): заказов WP в Moodle нет — ведём в «Мои курсы» |
| `?kab_te_sso=1&redirect_to=<edu>&kab_persona=<p>` (SSO Edwiser) | роутер WP → `go.php?to=lesson&url=<edu>&pres=<p>&src=sso` (§6); вход — штатный Moodle (пароль, ссылка на почту, Telegram) |
| шорткоды `[kab_telegram_join_lesson]`, `[kab_telegram_join_lesson_bot]` на «Спасибо» WP | адреса кнопок §3: «Урок» = `go.php?to=lesson&url=…&pres=…`, «Бот» = `go.php?to=bot`; «Спасибо» весны — статика на `/lp/` (спека записи §6) |
| токен `<заказ>_<rand>` / `u<wp user>_<rand>` + transient с данными (7 дней) | токен `m<userid>_<rand>`, таблица `local_kabtelegram_token` (только токен → человек, 7 дней); данные читаются в момент вебхука |
| вебхук `/wp-json/kab-te/v1/sp-webhook` → `setVariable` в SendPulse | `/local/kabtelegram/webhook.php?key=<секрет>` → то же |
| мета заказа `_kab_te_sp_contact_id`, `_kab_te_telegram_id`, `_kab_te_bot_linked_at` | таблица `local_kabtelegram_contact` (человек ↔ контакт SendPulse ↔ Telegram ID) |
| мета пользователя `_kab_te_{bot,channel}_click_{first,last,count}`, опция `kab_te_stats` | таблица `local_kabtelegram_event` (каждый переход строкой) |
| WP-крон `kab_te_channel_count` (getChatMemberCount раз в час) | задача `channel_count` (раз в час) |
| `wp kab-te-stats [--since --product]` | страница «Воронка Telegram — статистика» (`stats.php`, разрез «с даты по курсу») |
| синк `kab_persona` через веб-сервис Edwiser | прямо в профиль: `profile_save_custom_fields()` |

**Не переносим:** шорткоды `[kab_telegram_join]` (3 кнопки), `_v2` (автооткрытие бота), `_bot` — на
страницах не стоят; приветственное сообщение бота (`kab_te_sp_welcome_message` на проде пусто);
REST `user-data/{token}` (альтернатива вебхуку, не используется); счётчик `checkout_already_enrolled`
(чекаута WP больше нет); история `kab_te_stats` — остаётся в базе WP, читается `wp kab-te-stats`
по ssh и после выключения (WP-CLI работает, роутер стоит только на вебе).

## 2. Устройство

### 2.1 Переход в бота — `go.php?to=bot[&src=<откуда>]`

- Вошёл (не гость) → токен человека: строка в `local_kabtelegram_token`, одна на человека; пока не
  протухла — та же, срок (7 дней от последнего перехода) продлевается при каждом переходе; протухла —
  новый токен. Данных с токеном не храним (WP держал снимок в transient): вебхук читает их сам в момент
  прихода контакта — так в бота уходит актуальное и в таблице нет ПДн. Редирект на
  `https://tg.pulse.is/<бот>?start=<flow>&token=<токен>` — как «Спасибо» WP.
- Не вошёл, или не заданы flow / ключи → `https://t.me/<бот>` без токена (как у WP при сбое).
  Бот не задан → главная сайта.
- Снимок, который собирает вебхук (ключи): `firstname`, `lastname`, `email`, `phone` (phone1, иначе phone2), `country` (ISO,
  как `billing_country` WP), `city`, `course` (название курса: последняя запись `local_kabsignup`,
  иначе последняя покупка `local_kabshop`, иначе последнее зачисление), `orderid` (номер покупки
  кассы, если курс — из покупки), `quizscore` (последний балл квиза из журнала `local_kabsignup`),
  `persona`, `campaign`, `userid`. Журнал записи и касса — мягкие зависимости: нет таблиц — пусто.
- Засчитывается событие `bot_click` (источник `session` | `anonymous`, место `src`).

### 2.2 Канал — `go.php?to=channel[&src=…]`

Событие `channel_click`, редирект на ссылку канала из настроек (пусто — главная). Вступил ли человек,
отсюда не видно — как и в WP; общее число подписчиков пишет задача `channel_count`.

### 2.3 Урок — `go.php?to=lesson[&url=<адрес edu>][&pres=<персона>][&src=…]`

- Не вошёл → страница входа Moodle, после входа — обратно сюда (`wantsurl`).
- `pres` из списка персон (список и поле профиля — из настроек `local_kabsignup`, без него —
  `edik, mushi, gavriel, senja, rav, itzik` и `kab_persona`) → в поле профиля, с перезаписью: как WP и
  `local_kabsignup` §4.4 — метка не данные человека, а выбор гостиной.
- `url` — только адрес этого сайта (`PARAM_LOCALURL`), иначе — урок по умолчанию из настроек, иначе
  «Мои курсы» `/my/`. Событие `lesson_click`.

### 2.4 Вебхук SendPulse — `webhook.php?key=<секрет>`

- Секрет — параметр `key` или заголовок `X-Webhook-Secret` (как у WP); сравнение `hash_equals`.
  Секрет заводит установка (32 символа), полный адрес вебхука виден в настройках плагина. Секрет
  пуст или не совпал → 403. В WP секрет не был задан — вебхук WP принимал всё.
- Тело — массив событий SendPulse (`new_subscriber`, `incoming_message`, …) или одно событие.
  Токен — из `contact.variables.token` (так его кладёт deep-link `tg.pulse.is`), иначе из
  `/start <токен>` в `last_message`, иначе из текста сообщения / `tracking_data`. Telegram ID — из
  `contact.telegram_id` (WP ходил за ним отдельным запросом к API; в теле он есть).
- Токен найден и жив → привязка человека к контакту (`local_kabtelegram_contact`, событие
  `bot_linked` — один раз на пару человек–контакт) → переменные контакта через
  `POST /telegram/contacts/setVariable`, токен API — `POST /oauth/access_token`, кэш 50 минут.
- Переменные — те же имена, что у WP: `kab_first_name`, `kab_last_name`, `kab_email`, `kab_phone`,
  `kab_country`, `kab_city`, `kab_order_id`, `kab_course_name`; плюс строки настройки
  «Дополнительные переменные» `переменная = ключ снимка` (по умолчанию `kab_poll_score = quizscore`,
  как маппинг WP `kab_poll_score=poll_score`). Пустые значения не шлём (как WP).
- Повторно те же данные одному контакту не шлём (хеш переменных на привязке): WP переписывал все
  переменные на **каждое** входящее сообщение контакта с токеном.
- Сбой API → задача-повтор (adhoc `sync_contact`), ошибка — в error.log. Ответ SendPulse — всегда 200
  с `{"ok":true,"synced":…}` (кроме 403 и 400 на непонятное тело). Неизвестный токен (в том числе
  токены WP) — молча пропускаем.

### 2.5 Статистика

- `local_kabtelegram_event`: `event` (`bot_click`, `channel_click`, `lesson_click`, `bot_linked`,
  `channel_members`), `source` (`session` | `anonymous` | `webhook` | `cron`), `place` (`src` ссылки или
  канал для снимка), `userid`, `value` (число подписчиков для снимка), `timecreated`. Хранится
  `retentiondays` (400, как `STATS_DAYS` WP).
- Задача `channel_count` раз в час: `getChatMemberCount` Bot API (токен бота и чат — настройки; бот не
  обязан быть админом публичного канала — проверено WP 14.09.2026).
- `stats.php` (право `local/kabtelegram:viewstats`, менеджер): по дням — `bot_click:session`,
  `bot_click:anonymous`, `bot_linked`, `channel_click:…`, `lesson_click:…`, `channel_members` (последний
  снимок дня); разрез «люди, зачисленные на курс с даты»: сколько перешли в бота, привязали бота,
  перешли в канал — аналог `wp kab-te-stats --since --product`.
- Задача `cleanup` раз в сутки: протухшие токены (со снимком) удаляются, события старше срока — тоже.

### 2.6 Гостиные — `/kab/tgbot.php`

Кнопки и ссылки гостиных курса 236 (cmid 13360–13366, страницы 17871–17877) ведут на
`/kab/tgbot.php[?to=channel]`. Содержимое уроков не трогаем: `tgbot.php` (KAB patch) уводит на
`go.php?to=<bot|channel>&src=tgbot`, как только `local_kabtelegram` установлен и в нём задан бот;
до того — прежний путь в WP. Гостиные весеннего курса — копии осенних, ссылки те же.

## 3. Адреса кнопок (контракт со страницами «Спасибо», гостиными, письмами)

```
бот:     https://edu.kabacademy.com/local/kabtelegram/go.php?to=bot&src=<откуда>
канал:   https://edu.kabacademy.com/local/kabtelegram/go.php?to=channel&src=<откуда>
урок:    https://edu.kabacademy.com/local/kabtelegram/go.php?to=lesson&url=<адрес урока edu>&pres=<персона>&src=<откуда>
```

`src` — латиница, цифры, `-`/`_`, до 20 символов (`thanks`, `email`, `lounge`…): попадает в статистику.
Шорткод «Урок + Бот» = две кнопки: «урок» и «бот». После записи `local_kabsignup` человек уже вошёл на
edu, а kabacademy.com и edu.kabacademy.com — один сайт для браузера (SameSite), поэтому переход со
статичной «Спасибо» на `/lp/` в `go.php` несёт сессию Moodle и бот получает данные.

## 4. Изоляция

- Всё своё — в новом `local_kabtelegram` (три таблицы, кэш токена API, две задачи по расписанию,
  одна adhoc). Ядро, тему, `local_kabsignup`, `local_kabshop` не трогаем — только читаем их таблицы,
  если они есть.
- Единственная правка вне плагина — `public/public/kab/tgbot.php` (статика `/kab`, в git с 24.11): ветка
  «плагин настроен → `go.php`» в начале, прежний путь в WP — без изменений. Пометка `KAB patch`.
- Чужой профиль меняется только в одном поле — персона (§2.3), и только самим человеком по ссылке.
- Внешние сервисы (SendPulse, Telegram Bot API) — с таймаутами; их сбой не ломает переход: человек
  всё равно уходит в бота / канал / урок.
- Ключи (id и секрет API SendPulse, секрет вебхука, токен бота канала) — только в настройках плагина,
  не в git (репо публичное).
- Откат: удалить плагин («Плагины → Обзор»), `tgbot.php` сам вернётся на путь WP (пока WP жив).

## 5. Внешние зависимости — что делает владелец

| Что | Где | Значение |
|---|---|---|
| id и секрет API SendPulse | настройки `local_kabtelegram` | те же, что в WP (Настройки → Course Start & TG) или новые из SendPulse → Настройки аккаунта → API |
| имя бота, flow ID | там же | из настроек WP (`kab_te_bot_username`, `kab_te_sp_flow_id`) |
| ссылка канала, чат для счёта, токен бота для счёта | там же | из настроек WP (`kab_te_channel_invite_url`, `kab_te_channel_check_chat`, `kab_te_channel_bot_token`) |
| адрес вебхука | SendPulse → Чат-боты → бот → Настройки → Вебхуки | заменить `kabacademy.com/wp-json/kab-te/v1/sp-webhook` на адрес из настроек плагина; события `new_subscriber` и `incoming_message` (как сейчас). Если SendPulse даёт второй вебхук — добавить edu, не удаляя WP, WP удалить после его выключения |
| flow бота | SendPulse | не меняется: deep-link `tg.pulse.is/<бот>?start=<flow>&token=<токен>` тот же, переменные `kab_*` те же |
| роутер WP | kabacademy.com, `kab-router.php` | §6 — сессия переноса WP |

## 6. Роутер WP (kabacademy.com, не этот репо) — после установки плагина на прод

Сейчас роутер отвечает 410 на `admin-ajax.php` и уводит `kab_te_sso` прямо на адрес edu (персона
теряется). Предлагается:

| Адрес | Сейчас в роутере | Предлагается |
|---|---|---|
| `wp-admin/admin-ajax.php?action=kab_te_bot_go` | 410 | `edu/local/kabtelegram/go.php?to=bot&src=wp` |
| `…?action=kab_te_channel_go` | 410 | `go.php?to=channel&src=wp` |
| `…?action=kab_te_lesson_go&order=…&key=…` (письма о заказе WP) | 410 | `go.php?to=lesson&src=email` (→ «Мои курсы») |
| `?kab_te_sso=1&redirect_to=<edu>[&kab_persona=<p>]` | `<edu>` | `go.php?to=lesson&url=<edu>&pres=<p>&src=sso` |

## 7. Порядок выкладки

1. Ветка → PR; dev-edu: установка curl-ом, проверка §8.
2. Прод (по слову владельца): `git -c core.autocrlf=false -c core.eol=lf archive` → каталог → curl на
   `127.0.0.1/admin/index.php` с `upgradekeyhash`. Плагин без настроек ничего не делает, `tgbot.php`
   остаётся на WP.
3. Владелец — настройки §5 (ключи), затем адрес вебхука в SendPulse. С этой минуты гостиные идут в
   Moodle. Окно между шагами — минуты; переходов в бота 0–5 в день.
4. Новый `tgbot.php` на прод (тот же архив), строка аудита.
5. Роутер WP §6 — сессия переноса WP.
6. Проверка на проде: переход из гостиной под своим аккаунтом → бот → в SendPulse у контакта `kab_*`.

## 8. Проверка на dev-edu

SendPulse и Telegram на dev — заглушка (скрытая настройка `apibase`/`tgapibase` → временный скрипт на
dev, удаляется после проверки): настоящие ключи на dev не кладём (аудит 24.11 — секреты с dev убраны).

| # | Сценарий | Ожидание |
|---|---|---|
| 1 | Установка | таблицы, задачи, секрет вебхука заведён |
| 2 | `go.php?to=bot` гостем | `t.me/<бот>`, событие `bot_click:anonymous` |
| 3 | То же вошедшим | `tg.pulse.is/<бот>?start=<flow>&token=m<id>_…`, строка токена со снимком (телефон, город, курс, балл) |
| 4 | Повторный переход | тот же токен, обновлён срок |
| 5 | Вебхук без ключа / с чужим | 403 |
| 6 | Вебхук `new_subscriber` с токеном | привязка, `bot_linked`, 9 вызовов `setVariable` в заглушке |
| 7 | Повтор `incoming_message` того же контакта | без вызовов API |
| 8 | Неизвестный токен / токен WP | 200, `synced:false` |
| 9 | `go.php?to=channel` | редирект на канал, `channel_click` |
| 10 | `go.php?to=lesson&url=…&pres=edik` гостем → вход → обратно | персона в профиле, редирект в урок; чужой домен в `url` → `/my/` |
| 11 | `tgbot.php` / `tgbot.php?to=channel` | → `go.php?to=…&src=tgbot` |
| 12 | Задача `channel_count` (заглушка) | событие `channel_members` с числом |
| 13 | `stats.php` | таблица по дням, разрез по курсу |
| 14 | Вебхук через Cloudflare (POST JSON снаружи) | доходит, не 403 WAF |

## 9. Риски

| Риск | Что делаем |
|---|---|
| Из мобильного приложения Moodle ссылка открывается во внешнем браузере без сессии | бот без данных (как при WP); приложение умеет автологин для ссылок своего сайта — проверить на проде |
| Cloudflare не пропустит вебхук SendPulse | §8 п. 14; если режет — правило WAF для `/local/kabtelegram/webhook.php` (владелец) |
| Чужая ссылка `go.php?to=lesson&pres=…` меняет человеку гостиную | вред — только другая гостиная; так было и в WP |
| SendPulse ждёт ответ дольше, чем идут 9 вызовов API | таймауты 8 с на вызов, повтор задачей; обычно вызов ~0,2 с |
| Окно между переключением `tgbot.php` и вебхука | минуты, 0–5 переходов в день; токены WP и Moodle не пересекаются, чужие пропускаются |
