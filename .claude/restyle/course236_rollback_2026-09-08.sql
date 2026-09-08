-- Откат стилизации боевого курса 236 от 2026-09-08 (контент + тема).
UPDATE mdl_forum f JOIN _kab_bk20260908_forum236 b ON b.id=f.id SET f.intro=b.intro, f.timemodified=b.timemodified;
UPDATE mdl_page p JOIN _kab_bk20260908_page236 b ON b.id=p.id SET p.content=b.content, p.timemodified=b.timemodified;
UPDATE mdl_course_sections s JOIN _kab_bk20260908_sections236 b ON b.id=s.id SET s.summary=b.summary;
UPDATE mdl_quiz q JOIN _kab_bk20260908_quiz236 b ON b.id=q.id SET q.intro=b.intro, q.timemodified=b.timemodified;
UPDATE mdl_feedback f JOIN _kab_bk20260908_feedback236 b ON b.id=f.id SET f.intro=b.intro, f.timemodified=b.timemodified;
-- Вернуть тему по умолчанию (Boost):
-- UPDATE mdl_course SET theme='' WHERE id=236;
UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;
