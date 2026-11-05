# local_kabperson — карточка человека (Person Card)

Read-only карточка «всё, в чём человек участвует» для оператора чата поддержки, бота и
(позже) учебного отдела. Этапы 1–2 спеки `spec_person_card_moodle_2026-10.md` (репо
kabacademy.com, ветка `claude/student-card-source-of-truth-58b2ea`): блоки Moodle,
кассы `local_kabshop` и заказов старого WP kabacademy (0.2.0); связка с Dashboard App
Chatwoot. kabbalah.info и books.kab.co.il — этап 5, в `sources` пока `off`.

Человек = пользователь Moodle (`mdl_user`), ключ — email (нижний регистр).
Плагин **ничего не пишет**, кроме своего MUC-кэша и события в журнал.

## Что отдаёт

JSON по контракту §9 спеки: `found`, `sources`, `identity` (имя, email, статус,
регистрация, последний вход, Telegram, сигналы дубля), `learning` (курсы: роль по
словарю владельца, статус записи, прогресс, последний заход, значки; когорты),
`payments` (покупки, подписки с автосписанием, незавершённые оплаты `paygw_kbb1`,
`donor_total` — суммы пожертвований по валютам). Роли по курсам читаются внутри
Moodle — это переезд WS-функции из `local_kabchat`, без Edwiser и токена суперадмина.

**Заказы старого WP kabacademy** (`source\kabacademy_wp`) вливаются в `payments.orders` с
`source: kabacademy_wp` (плюс `number`, `refunded`), прошедшие пожертвования — в
`donor_total`, `identity.wp_user_id` и адрес WP — в `identity`. Читается
`/wp-json/kab/v1/person?blocks=payments` (mu-plugin `kab-person-card.php` ≥ 0.3 в репо
kabacademy.com) со своим токеном `X-KAB-Token`, таймаут 4 с. Если по email Moodle никого
нет, а WP знает связь `moodle_user_id`, карточка показывает этого пользователя Moodle с
сигналом `moodle_email_differs`; если в Moodle человека нет совсем — блок «Человек» из WP
и его заказы при `found: false`. Источник упал — `sources.kabacademy_wp: error`,
карточка с ошибкой не кешируется.

## Точки входа

| Что | Кому | Защита |
|---|---|---|
| `/local/kabperson/view.php` (без параметров) | URL Dashboard App в Chatwoot | оболочка без данных: ждёт контакт через `postMessage` (только от origin'ов из `frameancestors`) и переходит на `?email=&contact_id=[&unverified=1]` |
| `/local/kabperson/view.php?email=…` | Dashboard App Chatwoot (iframe, без сессии Moodle); залогиненный оператор | PII-гейт (ниже) **или** право `local/kabperson:view` у залогиненного |
| `…view.php?…&format=json` | то же, но JSON контрактом | та же |
| `…view.php?…&refresh=1` | кнопка «Обновить» | та же; пересобирает карточку мимо кэша |
| WS `local_kabperson_get_person(email, refresh)` | бот поддержки | служба `kabperson_card`, свой токен, право `local/kabperson:view` |

**PII-гейт** (перенос с WP-карточки `kab-person-card.php` 0.2): карточка отдаётся только
по email, у которого в Chatwoot есть контакт с хотя бы одним диалогом; для анонимов
Dashboard App передаёт `contact_id`, и адрес сверяется с контакт-атрибутом
`kab_alt_email`. Лимит 30 запросов / 5 мин с IP (429). Chatwoot не настроен или не
ответил → 503, fail-closed. Вердикт гейта кешируется на 10 минут.

Токен Chatwoot уходит заголовком **`api-access-token` (через дефис)**: edge перед
Chatwoot (nginx) выбрасывает заголовки с подчёркиванием, а Rails принимает оба написания.
Поэтому `chatwootbase` — публичный адрес Chatwoot, внутренний не нужен.

**Dashboard App.** Контакт приходит событием `appContext`: основной email (он есть только
у подтверждённых сайтом, `identifier`) или «Другой email (из чата)» `kab_alt_email`. Адрес
из чата — с плашкой «не подтверждён, о курсах и оплатах не говорить», как в WP-карточке.
Слушатель висит и на страницах ошибок: оператор переключил диалог — карточка догонит.
`frame-ancestors` пуст → страницу встраивает только сам Moodle (`'self'`).

## Установка

1. Файлы: каталог `local/kabperson` в вебрут (deploy по git; CLI Moodle не запускать).
2. Веб-админка → «Уведомления» (admin/index.php) → апгрейд БД. Таблиц у плагина нет:
   регистрируются права, служба, кэши.
3. Настройки (Администрирование → Плагины → Локальные плагины → KAB: карточка человека):
   адрес Chatwoot, account id, API-токен (для гейта), `frame-ancestors` для iframe
   Dashboard App (origin Chatwoot), чтение кассы, адрес и токен WP kabacademy.
   На стороне WP: mu-plugin `kab-person-card.php` ≥ 0.3 и в wp-config
   `define('KAB_PERSON_CARD_TOKEN_EDU', '<случайная строка>')` — то же значение в `wptoken`.
   Токен свой, не бота: отзывается отдельно.
4. Для бота: Сервер → Веб-службы → Внешние службы → `KAB Person Card` → добавить
   авторизованного пользователя (со своей ролью, в которой разрешено
   `local/kabperson:view`) → создать токен. Протокол REST должен быть включён.
5. Chatwoot → Настройки → Интеграции → Dashboard Apps: новое приложение с URL
   `https://<moodle>/local/kabperson/view.php` (без параметров). Старая вкладка WP-карточки
   остаётся рядом, пока не переключили (этапы 3–4 спеки).

## Кэш

Карточка кешируется в MUC на 10 минут на email (`local_kabperson/person`).
«Обновить» (`refresh=1`) пересобирает её. Вердикты гейта — `gate` (10 мин),
счётчики лимита — `ratelimit` (5 мин). Сбросить всё: Администрирование →
Плагины → Кэширование → Очистить кэши (или точечно эти три определения).

## Журнал и privacy

Каждое открытие карточки — событие `\local_kabperson\event\card_viewed` в журнале
Moodle (кто, какой email, канал view/ws, через гейт или по праву). Privacy API:
плагин о человеке ничего не хранит; декларируются внешние передачи — искомый email
уходит в Chatwoot при проверке гейта, email и id пользователя Moodle — в старый WP
kabacademy за заказами.

## Откат

Плагин без таблиц и без хуков, читать его никто не обязан:

1. Администрирование → Плагины → Управление плагинами → `local_kabperson` → Удалить
   (снимет право, службу, настройки и кэш-определения).
2. Убрать каталог `local/kabperson` из вебрута (иначе Moodle предложит переустановить).

Порядок наоборот (сначала каталог) тоже работает: плагин станет «missing from disk»,
удалить из того же списка. Старая WP-карточка продолжает работать параллельно,
откат интеграции чата — вернуть прежний URL Dashboard App (§6 спеки).

## Dev-заметки

- dev-edu: ставить файлами + веб-админка dev; письма не уходят (`noemailever`),
  Chatwoot-гейт можно не настраивать — тогда анонимный `view.php` отвечает 503
  (fail-closed), а карточку смотреть под админом dev (гейт обходится правом).
- Сеть сервер Moodle → Chatwoot проверена 05.11.2026: публичный адрес отвечает, edge режет
  `api_access_token`, но пропускает `api-access-token` — плагин шлёт второй.
- WP-адаптер на dev смотрит в dev-копию площадки (не в прод WP) со своим dev-токеном.
- Тестовый юзер на dev: `alext` (id 123545) — покупки, подписка № 1, оплаты kbb1.
