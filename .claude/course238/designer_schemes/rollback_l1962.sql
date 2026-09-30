-- Откат схем дизайнера (лекция 1, курс 236 (основной), cmid 13459): содержимое из _kab_bk20260930d_l1962.
UPDATE mdl_lesson_pages x JOIN _kab_bk20260930d_l1962 b ON b.id=x.id SET x.contents=b.contents, x.timemodified=b.timemodified WHERE x.id IN (17888,17889);
SELECT id, LENGTH(contents) len FROM mdl_lesson_pages WHERE id IN (17888,17889) ORDER BY id;
