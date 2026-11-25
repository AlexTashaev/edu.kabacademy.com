# local_kabtelegram — воронка Telegram без WordPress

Переносит в Moodle то, что делал WP-плагин `kab-telegram-enrollment` (kabacademy.com): ссылку в
бота SendPulse с данными человека, ссылку на канал курса, вебхук SendPulse, который пишет
переменные `kab_*` в контакт бота, статистику переходов и счёт подписчиков канала. Человека
узнаём по сессии Moodle — подписи между сайтами (мост Ed25519 `kab-bridge-secret.php`) не нужны.

Spec: `.claude/landings/spec_kabtelegram_funnel_2026-11.md`.

## Адреса

| Адрес | Что делает |
|---|---|
| `/local/kabtelegram/go.php?to=bot&src=<откуда>` | вошёл → токен `m<userid>_<16 символов>` (один на человека, живёт 7 дней от последнего перехода) → `tg.pulse.is/<бот>?start=<flow>&token=<токен>`; не вошёл или не задан flow → `t.me/<бот>`; бот не задан → главная |
| `go.php?to=channel&src=…` | ссылка канала из настроек (пусто → главная) |
| `go.php?to=lesson&url=<адрес этого сайта>&pres=<персона>&src=…` | не вошёл → вход Moodle и обратно; персона из списка `local_kabsignup` → поле профиля `kab_persona`; дальше `url` (только этот сайт), иначе урок по умолчанию, иначе `/my/` |
| `/local/kabtelegram/webhook.php?key=<секрет>` | вебхук SendPulse (POST JSON); GET с ключом отвечает `{"status":"ok"}` |
| `/local/kabtelegram/stats.php` | статистика (Администрирование → Плагины → Локальные плагины) |
| `/kab/tgbot.php[?to=channel]` | кнопки гостиных; ведёт на `go.php?to=…&src=tgbot`, как только задан бот (KAB patch в файле) |

`src` — латиница, цифры, `-`/`_`, до 20 символов; попадает в статистику («откуда переход»).
Шорткоды WP `[kab_telegram_join_lesson]` / `[kab_telegram_join_lesson_bot]` = кнопки «урок» / «урок» +
«бот» с этими адресами.

## Вебхук

- Секрет — `?key=` или заголовок `X-Webhook-Secret`; заводится при установке (32 символа), полный адрес —
  в настройках плагина. Пусто или не совпал → 403.
- Токен берём из `contact.variables.token` (его кладёт `tg.pulse.is`), запасные места — `/start <токен>`,
  текст сообщения, `tracking_data.contact_variables.token`. Telegram ID — `contact.telegram_id`.
- Токен жив → привязка `local_kabtelegram_contact` (событие `bot_linked` один раз) → переменные через
  `POST /telegram/contacts/setVariable` (токен API `POST /oauth/access_token`, кэш 50 мин). Данные читаются
  в момент вебхука (профиль, последнее зачисление, покупка `local_kabshop` на этот курс, балл квиза и
  кампания из журнала `local_kabsignup`, персона) — с токеном ничего не хранится.
- Переменные: `kab_first_name`, `kab_last_name`, `kab_email`, `kab_phone`, `kab_country`, `kab_city`,
  `kab_order_id` (номер покупки кассы), `kab_course_name` + настройка «Дополнительные переменные»
  (по умолчанию `kab_poll_score = quizscore`). Пустые не шлём.
- Те же значения тому же контакту повторно не пишем (хеш на привязке). Сбой API → error.log и
  adhoc-задача `sync_contact` (повторы с нарастающей паузой).
- Неизвестный или протухший токен (в том числе токены WP) → 200, `synced:false`.

## Настройки (владелец; ключи — только здесь, не в git)

| Настройка | Откуда взять |
|---|---|
| Имя бота, Flow ID | WP: Настройки → Course Start & TG (`kab_te_bot_username`, `kab_te_sp_flow_id`) |
| API ID / Secret SendPulse | там же или SendPulse → Настройки аккаунта → API |
| Секрет вебхука | уже заведён; адрес вебхука — в описании страницы настроек |
| Ссылка канала, канал для счёта, токен бота для счёта | WP: `kab_te_channel_invite_url`, `kab_te_channel_check_chat`, `kab_te_channel_bot_token` |
| Урок по умолчанию | пусто — «Мои курсы» |

В SendPulse → Чат-боты → бот → Настройки → Вебхуки: адрес WP `…/wp-json/kab-te/v1/sp-webhook` заменить
адресом из настроек плагина (события `new_subscriber`, `incoming_message`). Flow бота не меняется.

Скрытые настройки только для dev: `apibase` (вместо `https://api.sendpulse.com`) и `tgapibase` (вместо
`https://api.telegram.org`) — направить на заглушку. На прод не ставить.

## Установка без CLI

1. Каталог `public/public/local/kabtelegram` — под юзером сайта, из
   `git -c core.autocrlf=false -c core.eol=lf archive`.
2. `curl` на `127.0.0.1/admin/index.php?confirmrelease=1` с `Host:` сайта и `X-Forwarded-Proto: https`
   (на проде + `upgradekeyhash`), затем `&confirmplugincheck=1&cache=0`: три таблицы, две задачи, секрет.
3. Без настроек плагин ничего не делает, `tgbot.php` ведёт в WP. Заданный бот — переключение гостиных.
4. Строка аудита: `local/kabtelegram/version.php|` и `kab/tgbot.php|local_kabtelegram`.

Откат: удалить плагин в «Плагины → Обзор» — `tgbot.php` сам вернётся на путь WP.

## Статистика

`local_kabtelegram_event` — строка на переход: `bot_click` / `channel_click` / `lesson_click`
(источник `session` — вошёл, `anonymous` — нет), `bot_linked`, `channel_members` (снимок раз в час).
Страница: по дням и разрез «зачисленные на курс с даты — сколько перешли в бота, привязали, перешли в
канал». История до переезда — `wp kab-te-stats` по ssh на web-13 (WP-CLI работает и после выключения WP).
Хранение — 400 дней (настройка), протухшие токены удаляются каждую ночь.

## Грабли

- Из мобильного приложения Moodle ссылка открывается во внешнем браузере — без сессии человек уходит в
  бота без данных (так было и в WP).
- Переменная из «Дополнительных переменных» должна быть заведена в боте SendPulse, иначе API отвечает
  ошибкой и запись повторяется задачей.
- Вебхук идёт через Cloudflare: если WAF начнёт отвечать 403 на POST SendPulse — правило-исключение для
  `/local/kabtelegram/webhook.php`.
