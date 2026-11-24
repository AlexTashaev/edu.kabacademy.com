# auth_kabtelegram — «Вход через Telegram»

Вход через Telegram прямо в Moodle, без WordPress и моста Edwiser. Замена `auth_telegram_wp`
(кнопка вела на kabacademy.com, там `wptelegram-login` + `kab-telegram-bridge`, сессию Moodle
поднимал Edwiser SSO). Spec — `.claude/kabtelegram/spec_kabtelegram_2026-11.md`.

## Как работает

1. Кнопка «Войти через Telegram» на странице входа (`loginpage_idp_list()`; есть, только если
   задан токен бота) → `start.php`: в `$SESSION` одноразовый `state` (16 байт, 30 минут),
   редирект на `https://oauth.telegram.org/auth?bot_id=…&origin=<сайт>&return_to=callback.php?state=…`.
2. Telegram возвращает на `callback.php?state=…#tgAuthResult=<base64url JSON>`. Фрагмент до
   сервера не доходит: страница читает его скриптом, стирает из адресной строки и отправляет POST
   (`tgauth`, `state`, `sesskey`).
3. POST: `sesskey`; `state` = сессии (`hash_equals`, одноразовый); подпись Telegram
   (`HMAC-SHA256(data_check_string, SHA256(bot_token))`); `auth_date` не старше `maxage`
   (60 мин, максимум сутки) и не из будущего (5 мин допуска); привязка `tgid → userid`;
   аккаунт жив, подтверждён, не заблокирован, метод из `allowedauths` (`manual,email`) и включён;
   `auth_date` строго новее последнего принятого (`lastauthdate`, под блокировкой) — повтор
   ответа не впускает. Затем `complete_user_login()` и редирект на `wantsurl` (только внутри
   сайта, без картинок/стилей), иначе `/my/`.
4. Telegram не привязан → аккаунт **не создаётся**. Проверенный Telegram ждёт в сессии
   30 минут, `link.php` гостю объясняет: войдите по ссылке на почту (`auth_kabmagic`, если
   включён) или с паролем — `wantsurl` = `link.php`. После входа `link.php` спрашивает
   «Привязать Telegram … к аккаунту …?» (POST + `sesskey`), молча не привязывает.
5. Настройки → Учётная запись → «Вход через Telegram» (`link.php`): статус, «Привязать Telegram»
   (`start.php?mode=link&sesskey=…`, тот же круг через Telegram, `state` привязан к `userid`),
   «Привязать другой», «Отвязать». Telegram другого аккаунта не перепривязывается.

## Изоляция

`user_login()` всегда `false`; `is_internal`, `can_change_password`, `can_reset_password`,
`can_signup`, `can_confirm`, `can_be_manually_set`, `prevent_local_passwords` → `false`.
Пароли и `auth` пользователей не трогаются, аккаунты не создаются. Своё — одна таблица.
Ядро и тема не патчатся. `auth_kabmagic` не меняется (только ссылка на его `request.php`).

## Внешние зависимости

- Бот входа @kabacademy_login_bot: токен — настройка `bottoken` (вводит владелец); `bot_id` =
  число до `:` в токене.
- Домен бота в BotFather (`/setdomain`) должен быть доменом Moodle. У бота один домен: до
  24.11.2026 это `kabacademy.com` (oauth.telegram.org для `edu.kabacademy.com` отвечает
  «Bot domain invalid»). После смены вход через Telegram на WP перестаёт работать.
- oauth.telegram.org из браузера пользователя (в РФ Telegram местами режут).

## Таблица `auth_kabtelegram_links`

| Поле | Что |
|---|---|
| `userid` (unique) | аккаунт Moodle |
| `tgid` (unique, bigint) | id пользователя Telegram |
| `tgusername`, `tgname` | @username и имя из Telegram на момент последнего входа/привязки |
| `source` | `wp` — импорт со старого сайта, `user` — привязал сам |
| `lastauthdate` | последний принятый `auth_date` |
| `timecreated`, `timemodified`, `timelastlogin` | — |

Удаление пользователя — наблюдатель `user_deleted` удаляет привязку. Privacy API — экспорт и удаление.

## Настройки

| Настройка | По умолчанию | Что |
|---|---|---|
| `bottoken` | пусто | токен бота; пока пусто — кнопки нет |
| `maxage` | 60 | срок ответа Telegram, минут (не больше 1440) |
| `allowedauths` | `manual, email` | каким методам аутентификации разрешён вход через Telegram |

## Установка без CLI

1. Каталог — под юзером сайта, из `git archive` (с Windows — `git -c core.autocrlf=false -c core.eol=lf archive`).
2. `curl` на `127.0.0.1/admin/index.php?confirmrelease=1` с `Host:` сайта и `X-Forwarded-Proto: https`:
   `status-new` только у `auth_kabtelegram`; затем `&confirmplugincheck=1&cache=0` — таблица и
   наблюдатель. Плагин после установки выключен.
3. Импорт привязок со старого сайта — `tools/kabtelegram-import/` (ниже).
4. Владелец: токен в настройках, `/setdomain` в BotFather, «Управление аутентификацией» —
   включить «Вход через Telegram» (MFA). «Telegram через WordPress» на проде выключен 24.11.2026.
5. Проверка: кнопка на `/login/index.php`, вход своим Telegram, «Настройки → Вход через Telegram».

На dev то же; включить можно SQL-ом `auth` **до** шага 2 (установка сама сбрасывает кэши).

## Импорт привязок со старого WordPress

`tools/kabtelegram-import/` (данных и адресов серверов в репозитории нет):

1. На сервере WP: `wp db query < wp_export.sql > wp.tsv` (`wptelegram_user_id`, `wptelegram_username`,
   `wptelegram_first_name`, `moodle_user_id`, e-mail).
2. `python3 build.py fetch wp.tsv > fetch.sql` → на сервере Moodle `mysql … < fetch.sql > moodle.tsv`.
3. `python3 build.py import wp.tsv moodle.tsv > import.sql` — отчёт в stderr: сопоставление по
   `moodle_user_id` (связка Edwiser) **и** почте, иначе по единственному живому аккаунту с этой почтой;
   неоднозначные, ненайденные и дубли не импортируются (в отчёте — только id WP).
4. `mysql … < import.sql`; повтор безвреден (`NOT EXISTS` по `tgid` и `userid`).
5. Файлы выгрузки удалить (почта и id Telegram людей).

24.11.2026: 132 из 132 сошлись по связке и почте, все аккаунты живые, `manual`/`email`.

## Проверка на dev

Настоящий Telegram на dev не проверить (домен бота нужен проду). На dev стоит тестовый токен
(бот `1000000001` — не существует, Telegram отвечает «bot_id required»), ответы Telegram
подписываются им же и подкладываются в `callback.php`. 24.11.2026 проверено curl-ом (46 проверок):
кнопка, редирект с `bot_id`/`origin`/`return_to`+`state`, `Referrer-Policy`, неизвестный Telegram
(аккаунт не создан, гостю — вход по почте с `wantsurl=link.php`), вход привязанным (редирект на
`wantsurl`, `auth` пользователя не тронут), повтор ответа, подмена поля, ответ старше 2 часов и из
будущего, чужой `state` и его одноразовость, неверный `sesskey`, пустой ответ, заблокированный
аккаунт, «ожидает привязки → вход по ссылке на почту → подтверждение», привязка из настроек
(чужой Telegram не перехватывается, замена своего), `mode=link` без `sesskey`, пункт в настройках,
отвязка, «Не привязывать». В браузере: скрипт `#tgAuthResult` → вход и `/my/`, страница «Telegram
ещё не привязан», страница в настройках. Страница настроек плагина под админом не открывалась.

## Диагностика

```sql
SELECT l.userid, u.username, l.tgusername, l.source, FROM_UNIXTIME(l.timecreated) linked,
       FROM_UNIXTIME(NULLIF(l.timelastlogin, 0)) lastlogin
  FROM mdl_auth_kabtelegram_links l JOIN mdl_user u ON u.id = l.userid
 ORDER BY l.timelastlogin DESC LIMIT 20;
SELECT source, COUNT(*), SUM(timelastlogin > 0) used FROM mdl_auth_kabtelegram_links GROUP BY source;
```

- «Bot domain invalid» на странице Telegram — домен бота в BotFather не тот.
- «Telegram не подтвердил вход» у всех — неверный токен (подпись не сходится) или часы сервера.
- «страница устарела» — между кнопкой и возвратом прошло больше 30 минут, cookie сессии потерялась
  (другой браузер, встроенный браузер Telegram) или кнопку нажали дважды.
- Кнопки нет — плагин выключен, не задан токен или токен не вида `123:AA…`.

## Откат

Выключить «Вход через Telegram» в «Управлении аутентификацией». Таблица привязок остаётся.
