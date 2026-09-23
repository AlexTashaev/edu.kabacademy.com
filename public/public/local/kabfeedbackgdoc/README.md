# local_kabfeedbackgdoc — ответы «Обратной связи» → Google Doc

Каждый отправленный ответ в активности **Обратная связь** (mod_feedback) уходит
POST-ом в веб-приложение Google Apps Script, привязанное к Google Doc, и
появляется в документе сверху: дата, имя, ссылки, ответы, пустая строка
«Ответ преподавателя:». Преподаватели читают и отвечают прямо в документе.

## Как это устроено

```
студент отправляет форму
  → \mod_feedback\event\response_submitted
  → observer (фильтр по cmid из настроек) → adhoc-задача
  → cron: sender::build_payload() → curl POST JSON → Apps Script doPost()
  → DocumentApp вставляет блок в Doc
```

- Отправка **не** делается в запросе студента: observer только ставит adhoc-задачу,
  cron шлёт и при ошибке ретраит с back-off.
- Apps Script дедуплицирует по `completedid` (ScriptProperties), так что ретраи
  не плодят копии.
- Ответы отдаются через `feedback_item_base::get_printval()`, т.е. для multichoice
  приходит текст варианта, а не индекс.

## Настройки (Администрирование → Плагины → Локальные плагины)

| Настройка | Что |
|---|---|
| `enabled` | вкл/выкл |
| `webhookurl` | URL развёртывания Apps Script `…/exec` |
| `secret` | строка, совпадающая с `SECRET` в `Code.gs` (пусто = без проверки) |
| `cmids` | через запятую cmid активностей (параметр `id` в URL); пусто = все feedback на сайте |

## Установка Apps Script (один раз)

1. Открыть Google Doc → *Расширения → Apps Script*.
2. Заменить содержимое `Code.gs` на [apps_script/Code.gs](apps_script/Code.gs), задать `SECRET`.
3. *Начать развёртывание → Новое развёртывание → Веб-приложение*:
   *Запуск от имени:* я; *У кого есть доступ:* все. Авторизовать. Скопировать URL `…/exec`.
4. Вставить URL и секрет в настройки плагина.
5. Проверка без Moodle: запустить `testInsert` в редакторе скрипта — в документе появится тестовый блок.

После правок `Code.gs` нужно *Управление развёртываниями → карандаш → Новая версия*,
иначе `/exec` продолжает исполнять старую версию.

## Формат payload

```json
{
  "event": "feedback_response",
  "site": "https://edu.kabacademy.com",
  "completedid": 123,
  "timestamp": 1790000000,
  "datetime": "23.09.2026 14:05",
  "course":   {"id": 238, "shortname": "...", "fullname": "..."},
  "feedback": {"id": 33, "cmid": 13407, "name": "...", "url": ".../mod/feedback/view.php?id=13407"},
  "anonymous": false,
  "user": {"id": 8, "fullname": "...", "email": "...", "url": ".../user/view.php?id=8&course=238"},
  "responseurl": ".../mod/feedback/show_entries.php?id=13407&userid=8&showcompleted=123",
  "answers": [{"itemid": 163, "name": "...", "type": "multichoice", "value": "в 17:00 изр"}],
  "secret": "..."
}
```

## Диагностика

- Очередь: `SELECT * FROM mdl_task_adhoc WHERE classname LIKE '%kabfeedbackgdoc%'` —
  `faildelay > 0` значит Google отвечал ошибкой, текст в `mtrace` лога cron.
- `GET <webhookurl>` должен вернуть `{"ok":true,"ping":"local_kabfeedbackgdoc"}`.
