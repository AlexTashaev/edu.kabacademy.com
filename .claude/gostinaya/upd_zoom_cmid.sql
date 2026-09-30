-- Вебинар в гостиных: старый mod_zoom cmid 13171 удалён из Moodle (кнопка «Вход в Zoom»
-- вела в ошибку) → актуальный «ПЕРВЫЙ ВЕБИНАР в zoom 1 октября», cmid 13437 курса 236.
-- Три вхождения на страницу: комментарий вёрстки, ссылка кнопки и адрес внутри
-- Google-календарной ссылки (там id закодирован как id%3D13171).
-- Применять: /tmp/kab_moodle_sql2.sh "$(cat upd_zoom_cmid.sql)"
START TRANSACTION;

-- DEFAULT CHARSET обязателен: у базы дефолт latin1, иначе бэкап падает на кириллице.
CREATE TABLE IF NOT EXISTS _kab_bk20260930_gost_zoom (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pageid BIGINT,
  ts INT,
  contents LONGTEXT
) DEFAULT CHARSET=utf8mb4;

INSERT INTO _kab_bk20260930_gost_zoom (pageid, ts, contents)
  SELECT id, UNIX_TIMESTAMP(), contents FROM mdl_lesson_pages WHERE id BETWEEN 17871 AND 17877;

UPDATE mdl_lesson_pages
   SET contents = REPLACE(contents, '13171', '13437'),
       timemodified = UNIX_TIMESTAMP()
 WHERE id BETWEEN 17871 AND 17877;

COMMIT;
