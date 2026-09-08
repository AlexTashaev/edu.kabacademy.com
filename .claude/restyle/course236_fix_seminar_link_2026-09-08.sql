-- Форумы 3838-3841 (день семинаров, уроки 14-15): ссылка «Правилами семинара»
-- вела на книгу 9746 чужого (прошлого) курса → книга 12948 этого курса.
-- Оригинал до стилизации — в _kab_bk20260908_forum236.
UPDATE mdl_forum SET timemodified=UNIX_TIMESTAMP(),
  intro=REPLACE(intro, '/mod/book/view.php?id=9746', '/mod/book/view.php?id=12948')
  WHERE id IN (3838,3839,3840,3841);
UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;
SELECT id, intro LIKE '%id=12948%' AS ok, intro LIKE '%id=9746%' AS old_left FROM mdl_forum WHERE id IN (3838,3839,3840,3841);
