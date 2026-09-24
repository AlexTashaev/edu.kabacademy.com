-- Футер гостиных: «Как всё устроено» отдельной строкой и ссылкой на справочник
-- курса (mod/page id=13434). Страницы 17871-17877 (13366 сейчас скрыта — правим
-- заодно, чтобы тексты не разъезжались).
START TRANSACTION;
-- DEFAULT CHARSET обязателен: у базы дефолт latin1, иначе бэкап падает на кириллице.
CREATE TABLE IF NOT EXISTS _kab_bk20260924_gost_footer (id INT AUTO_INCREMENT PRIMARY KEY, pageid BIGINT, ts INT, contents LONGTEXT) DEFAULT CHARSET=utf8mb4;
INSERT INTO _kab_bk20260924_gost_footer (pageid, ts, contents) SELECT id, UNIX_TIMESTAMP(), contents FROM mdl_lesson_pages WHERE id BETWEEN 17871 AND 17877;
UPDATE mdl_lesson_pages SET contents = REPLACE(contents, '<br>
  <!-- TODO: подставить ссылку на справочник курса Осень-2026 -->
  Как здесь всё устроено (кабинет · вебинары · форум · Zoom) — в справочнике курса. Откроешь, когда понадобится.', '
  <div style="margin-top:8px"><a href="https://edu.kabacademy.com/mod/page/view.php?id=13434" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">«Как всё устроено»</a> (кабинет · вебинары · Zoom) — в справочнике курса. Откроешь, когда понадобится.</div>'), timemodified = UNIX_TIMESTAMP() WHERE id = 17871;
UPDATE mdl_lesson_pages SET contents = REPLACE(contents, '>Эта страница остаётся — возвращайтесь; «Как всё устроено» (кабинет · вебинары · Zoom) — в справочнике курса. Откроете, когда понадобится.</div>', '>Эта страница остаётся — возвращайтесь.<div style="margin-top: 8px;"><a style="color: #2b4a7a; font-weight: 600;" href="https://edu.kabacademy.com/mod/page/view.php?id=13434" target="_blank" rel="noopener">«Как всё устроено»</a> (кабинет · вебинары · Zoom) — в справочнике курса. Откроете, когда понадобится.</div></div>'), timemodified = UNIX_TIMESTAMP() WHERE id = 17872;
UPDATE mdl_lesson_pages SET contents = REPLACE(contents, '<br>
  <!-- TODO: подставить ссылку на справочник курса Осень-2026 -->
  Как здесь всё устроено (кабинет · вебинары · форум · Zoom) — в справочнике курса. Откроешь, когда понадобится.', '
  <div style="margin-top:8px"><a href="https://edu.kabacademy.com/mod/page/view.php?id=13434" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">«Как всё устроено»</a> (кабинет · вебинары · Zoom) — в справочнике курса. Откроете, когда понадобится.</div>'), timemodified = UNIX_TIMESTAMP() WHERE id = 17873;
UPDATE mdl_lesson_pages SET contents = REPLACE(contents, '<br>
  <!-- TODO: подставить ссылку на справочник курса Осень-2026 -->
  Как здесь всё устроено (кабинет · вебинары · форум · Zoom) — в справочнике курса. Откроешь, когда понадобится.', '
  <div style="margin-top:8px"><a href="https://edu.kabacademy.com/mod/page/view.php?id=13434" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">«Как всё устроено»</a> (кабинет · вебинары · Zoom) — в справочнике курса. Откроете, когда понадобится.</div>'), timemodified = UNIX_TIMESTAMP() WHERE id = 17874;
UPDATE mdl_lesson_pages SET contents = REPLACE(contents, '>Эта страница остаётся — возвращайтесь; «Как всё устроено» (кабинет · вебинары · Zoom) — в справочнике курса. Откроете, когда понадобится.</div>', '>Эта страница остаётся — возвращайтесь.<div style="margin-top: 8px;"><a style="color: #2b4a7a; font-weight: 600;" href="https://edu.kabacademy.com/mod/page/view.php?id=13434" target="_blank" rel="noopener">«Как всё устроено»</a> (кабинет · вебинары · Zoom) — в справочнике курса. Откроете, когда понадобится.</div></div>'), timemodified = UNIX_TIMESTAMP() WHERE id = 17875;
UPDATE mdl_lesson_pages SET contents = REPLACE(contents, '<br>
  <!-- TODO: подставить ссылку на справочник курса Осень-2026 -->
  Как здесь всё устроено (кабинет · вебинары · форум · Zoom) — в справочнике курса. Откроешь, когда понадобится.', '
  <div style="margin-top:8px"><a href="https://edu.kabacademy.com/mod/page/view.php?id=13434" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">«Как всё устроено»</a> (кабинет · вебинары · Zoom) — в справочнике курса. Откроете, когда понадобится.</div>'), timemodified = UNIX_TIMESTAMP() WHERE id = 17876;
UPDATE mdl_lesson_pages SET contents = REPLACE(contents, '<br>
  <!-- TODO: подставить ссылку на справочник курса Осень-2026 -->
  Как здесь всё устроено (кабинет · вебинары · форум · Zoom) — в справочнике курса. Откроешь, когда понадобится.', '
  <div style="margin-top:8px"><a href="https://edu.kabacademy.com/mod/page/view.php?id=13434" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">«Как всё устроено»</a> (кабинет · вебинары · Zoom) — в справочнике курса. Откроете, когда понадобится.</div>'), timemodified = UNIX_TIMESTAMP() WHERE id = 17877;
COMMIT;
