-- Откат: contents страниц из _kab_bk20260928s_l1962_pages (состояние перед этим обновлением схем).
UPDATE mdl_lesson_pages p JOIN _kab_bk20260928s_l1962_pages b ON b.id=p.id SET p.contents=b.contents, p.timemodified=b.timemodified WHERE p.lessonid=1962;
SELECT id, title, LENGTH(contents) len FROM mdl_lesson_pages WHERE lessonid=1962 ORDER BY id;
