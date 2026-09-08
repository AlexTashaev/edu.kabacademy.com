-- ============================================================================
-- Курс 238 — достройка стилизации: intro трёх feedback и assign «мой вопрос»
-- в дизайн-системе «Гостиной Рава». Тексты дословно. 2026-09-08.
-- Откат: rollback_238.sql (обновлён — восстанавливает и feedback/assign).
-- ============================================================================

CREATE TABLE IF NOT EXISTS _kab_bk20260908_feedback238 AS SELECT * FROM mdl_feedback WHERE course=238;
CREATE TABLE IF NOT EXISTS _kab_bk20260908_assign238 AS SELECT * FROM mdl_assign WHERE course=238;

-- 31 · «Вопрос к вебинару следующей недели» (урок 1, плоская версия)
UPDATE mdl_feedback SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px">Остался вопрос после урока — напишите его здесь. Со следующей недели мы начинаем вебинар с разбора вопросов, присланных за прошлую неделю.</div>
</div>' WHERE id=31;

-- 32 · «Вопрос к вебинару следующей недели» (урок 2)
UPDATE mdl_feedback SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px">Остался вопрос после урока — напишите его здесь. Вебинар следующей недели мы начинаем с разбора вопросов, присланных за эту.</div>
</div>' WHERE id=32;

-- 33 · «Вопрос по теме урока 1 к вебинару с преподавателями» (🟢 Основное)
UPDATE mdl_feedback SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px">Остался вопрос после урока — напишите его здесь. Со следующей недели мы начинаем вебинар с разбора вопросов, присланных за прошлую неделю.</div>
</div>' WHERE id=33;

-- 9 · assign «Моя страница „мой вопрос“»
UPDATE mdl_assign SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px;margin:0 0 14px">Одна заметка, которую видите вы и мы. Сформулируйте вопрос, на который хотите получить ответ за пятнадцать недель.</div>
<p style="margin:0 0 10px">Вернётесь к ней на пятой, десятой и пятнадцатой неделе. К концу курса по ней видно, что с вашим вопросом стало, — мы считаем это главным результатом курса.</p>
<div style="font-size:13.5px;color:#7A7A7A;line-height:1.6">Не оцениваем, срока не ставим, править можно сколько угодно раз.</div>
</div>' WHERE id=9;

-- ============================ КЭШ ===========================================
UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=238;

-- Контроль
SELECT id, CHAR_LENGTH(intro) AS len FROM mdl_feedback WHERE course=238 ORDER BY id;
SELECT id, CHAR_LENGTH(intro) AS len FROM mdl_assign WHERE course=238;
