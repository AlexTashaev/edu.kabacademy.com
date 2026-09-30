-- Откат схем дизайнера (лекция 1, курс 238, cmid 13413): содержимое из _kab_bk20260930d_l1960.
UPDATE mdl_lesson_pages x JOIN _kab_bk20260930d_l1960 b ON b.id=x.id SET x.contents=b.contents, x.timemodified=b.timemodified WHERE x.id IN (17879,17880);
SELECT id, LENGTH(contents) len FROM mdl_lesson_pages WHERE id IN (17879,17880) ORDER BY id;
