-- ============================================================================
-- Курс 238 — достройка стилизации: intro форумов и тестов в дизайн-системе
-- «Гостиной Рава». Тексты дословно, меняется только вёрстка. 2026-09-08.
-- Откат: rollback_238.sql (обновлён — восстанавливает и forum/quiz).
-- ============================================================================

CREATE TABLE IF NOT EXISTS _kab_bk20260908_forum238 AS SELECT * FROM mdl_forum WHERE course=238;
CREATE TABLE IF NOT EXISTS _kab_bk20260908_quiz238 AS SELECT * FROM mdl_quiz WHERE course=238;

-- ============================ ФОРУМЫ ========================================

-- 3890 · «Объявления» (секция 0)
UPDATE mdl_forum SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px">Главные новости и объявления</div>
</div>' WHERE id=3890;

-- 3891 · «Отклик недели — один вопрос на выбор» (урок 1, плоская версия)
UPDATE mdl_forum SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px;margin:0 0 18px">Напишите своими словами ответ на любой из трёх вопросов. Один, не все три.</div>
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:6px 18px">
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">1</div><div style="padding-top:1px">Какое из четырёх «не является» было для вас самым неожиданным — и почему?</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">2</div><div style="padding-top:1px">Вспомните желание, которого вы когда-то очень сильно хотели и добились. Что осталось от того наслаждения через полгода?</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">3</div><div style="padding-top:1px">Что из третьей главы вы бы захотели проверить первым — и как вы представляете себе такую проверку?</div></div>
</div>
<div style="font-size:13.5px;color:#7A7A7A;line-height:1.6;margin-top:12px">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
</div>' WHERE id=3891;

-- 3892 · «Отклик недели — один вопрос на выбор» (урок 2)
UPDATE mdl_forum SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px;margin:0 0 18px">Напишите своими словами ответ на любой из трёх вопросов. Один, не все три.</div>
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:6px 18px">
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">1</div><div style="padding-top:1px">Вспомните желание, которого очень хотели и добились. Что осталось от того наслаждения через полгода?</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">2</div><div style="padding-top:1px">Найдите в своей неделе случай, где сработало одно из этих свойств. Какое и как?</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">3</div><div style="padding-top:1px">Какое из свойств объяснило вам то, что вы объясняли иначе? Одной фразы достаточно.</div></div>
</div>
<div style="font-size:13.5px;color:#7A7A7A;line-height:1.6;margin-top:12px">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
<div style="font-size:13.5px;color:#7A7A7A;line-height:1.6;margin-top:6px">И если вы завели страницу «мой вопрос» на прошлой неделе — допишите, сдвинулось ли в нём что-нибудь.</div>
</div>' WHERE id=3892;

-- 3893 · «Отклик недели 1: вопрос на выбор» (подсекция 🟢 Основное)
UPDATE mdl_forum SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px;margin:0 0 18px">Напишите своими словами ответ на любой из трёх вопросов. Один, не все три.</div>
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:6px 18px">
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">1</div><div style="padding-top:1px">Какое из четырёх «не является» было для вас самым неожиданным — и почему?</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">2</div><div style="padding-top:1px">Вспомните желание, которого вы когда-то очень сильно хотели и добились. Что осталось от того наслаждения через полгода?</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">3</div><div style="padding-top:1px">Что из третьей главы вы бы захотели проверить первым — и как вы представляете себе такую проверку?</div></div>
</div>
<div style="font-size:13.5px;color:#7A7A7A;line-height:1.6;margin-top:12px">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
</div>' WHERE id=3893;

-- ============================ ТЕСТЫ =========================================

-- 1232 / 1233 / 1234 · «Проверьте себя» (текст intro одинаковый)
UPDATE mdl_quiz SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px">Шесть вопросов. Отвечайте по памяти, не возвращаясь к тексту. Попыток столько, сколько нужно; мы ничего не запираем и разбор даём на каждый вариант ответа — в том числе на неверный.</div>
</div>' WHERE id IN (1232,1233,1234);

-- ============================ КЭШ ===========================================
UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=238;

-- Контроль
SELECT id, CHAR_LENGTH(intro) AS len FROM mdl_forum WHERE course=238 ORDER BY id;
SELECT id, CHAR_LENGTH(intro) AS len FROM mdl_quiz WHERE course=238 ORDER BY id;
