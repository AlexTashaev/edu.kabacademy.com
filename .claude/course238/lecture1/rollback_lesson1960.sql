-- Откат схем лекции 1: contents страниц из _kab_bk20260924_lesson1960.
UPDATE mdl_lesson_pages p JOIN _kab_bk20260924_lesson1960 b ON b.id=p.id SET p.contents=b.contents, p.timemodified=b.timemodified WHERE p.lessonid=1960;
SELECT id, title, LENGTH(contents) len FROM mdl_lesson_pages WHERE lessonid=1960 ORDER BY id;
