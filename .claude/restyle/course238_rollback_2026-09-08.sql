-- Откат стилизации курса 238 от 2026-09-08: восстановление из бэкап-таблиц.
UPDATE mdl_label l JOIN _kab_bk20260908_label238 b ON b.id=l.id SET l.intro=b.intro, l.timemodified=b.timemodified;
UPDATE mdl_page p JOIN _kab_bk20260908_page238 b ON b.id=p.id SET p.content=b.content, p.timemodified=b.timemodified;
UPDATE mdl_course_sections s JOIN _kab_bk20260908_sections238 b ON b.id=s.id SET s.summary=b.summary;
UPDATE mdl_forum f JOIN _kab_bk20260908_forum238 b ON b.id=f.id SET f.intro=b.intro, f.timemodified=b.timemodified;
UPDATE mdl_quiz q JOIN _kab_bk20260908_quiz238 b ON b.id=q.id SET q.intro=b.intro, q.timemodified=b.timemodified;
UPDATE mdl_feedback f JOIN _kab_bk20260908_feedback238 b ON b.id=f.id SET f.intro=b.intro, f.timemodified=b.timemodified;
UPDATE mdl_assign a JOIN _kab_bk20260908_assign238 b ON b.id=a.id SET a.intro=b.intro, a.timemodified=b.timemodified;
UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=238;
