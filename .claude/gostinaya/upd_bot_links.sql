-- Кнопка «Чат-бот в Telegram» в гостиных (курс 236, страницы 17871–17877):
-- голая ссылка t.me (и промежуточный вариант через admin-ajax) → /kab/tgbot.php.
-- Редиректор подписывает переход, WP узнаёт человека и передаёт его данные
-- в контакт SendPulse — так же, как кнопка на странице «Спасибо».
-- Применять: /tmp/kab_moodle_sql2.sh "$(cat upd_bot_links.sql)"
START TRANSACTION;

-- DEFAULT CHARSET обязателен: у базы дефолт latin1, иначе бэкап падает на кириллице.
CREATE TABLE IF NOT EXISTS _kab_bk20260910_gost_pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pageid BIGINT,
  ts INT,
  contents LONGTEXT
) DEFAULT CHARSET=utf8mb4;

INSERT INTO _kab_bk20260910_gost_pages (pageid, ts, contents)
  SELECT id, UNIX_TIMESTAMP(), contents FROM mdl_lesson_pages WHERE id BETWEEN 17871 AND 17877;

-- Гостиная Рава (17875) уже была переведена на admin-ajax — приводим к общему виду.
UPDATE mdl_lesson_pages
   SET contents = REPLACE(contents,
        'https://kabacademy.com/wp-admin/admin-ajax.php?action=kab_te_bot_go',
        'https://edu.kabacademy.com/kab/tgbot.php'),
       timemodified = UNIX_TIMESTAMP()
 WHERE id BETWEEN 17871 AND 17877;

-- Остальные шесть гостиных — с голой ссылки на бота.
-- Ссылка на канал потока (t.me/makosen2026) не затрагивается.
UPDATE mdl_lesson_pages
   SET contents = REPLACE(contents,
        'https://t.me/kabacademycom3_bot',
        'https://edu.kabacademy.com/kab/tgbot.php'),
       timemodified = UNIX_TIMESTAMP()
 WHERE id BETWEEN 17871 AND 17877;

COMMIT;
