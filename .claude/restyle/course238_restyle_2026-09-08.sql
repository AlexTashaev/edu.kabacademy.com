-- ============================================================================
-- Курс 238 («Тренировочный курс» / Классическая каббала Часть 1) —
-- стилизация в дизайн-системе «Гостиной Рава» (lesson cmid 13364):
-- Montserrat (/kab/fonts.css), палитра #52B0D8 / #3C5D90 / #A42BB9,
-- подложки #F4F9FD, карточки #fff с рамкой #D9E8F2, кикеры капсом.
-- Тексты сохранены дословно, меняется только вёрстка.
-- 2026-09-08. Rollback: rollback_238.sql (восстанавливает из _kab_bk20260908_*).
-- ============================================================================

CREATE TABLE IF NOT EXISTS _kab_bk20260908_label238 AS SELECT * FROM mdl_label WHERE course=238;
CREATE TABLE IF NOT EXISTS _kab_bk20260908_page238 AS SELECT * FROM mdl_page WHERE course=238;
CREATE TABLE IF NOT EXISTS _kab_bk20260908_sections238 AS SELECT * FROM mdl_course_sections WHERE course=238;

-- ============================ ЛЕЙБЛЫ ========================================

-- 515 · «Как устроен этот урок» (секция «Урок 1», плоская версия)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 10px">Как устроен этот урок</div>
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px;margin:0 0 14px">Вчера прошёл вебинар — он открыл эту тему. Здесь мы разбираем её с начала.</div>
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:6px 18px;margin:0 0 12px">
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#3C5D90;margin-top:9px"></div><div style="font-size:15px;line-height:1.6"><b style="color:#3C5D90">Важное — 25–30 минут:</b> три вопроса перед чтением, текст урока, тест, резюме. Прошли важное — считайте неделю сделанной.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div style="font-size:15px;line-height:1.6"><b style="color:#3C5D90">Дополнительное</b> — запись вчерашнего вебинара, лекция целиком, ответы на вопросы; относится к этой неделе.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#A42BB9;margin-top:9px"></div><div style="font-size:15px;line-height:1.6"><b style="color:#3C5D90">Факультативное</b> — первоисточники и беседы; к неделе не привязано.</div></div>
</div>
<div style="font-size:13.5px;color:#7A7A7A;line-height:1.6">Стало тяжело — берите только важное и оставайтесь в процессе. Освободилось время — поднимайтесь; материалы всех недель открыты всегда.</div>
</div>' WHERE id=515;

-- 516 · «Если захочется» (урок 1, плоская версия)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222;border-top:1px solid #D9E8F2;padding-top:16px;margin-top:6px">
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 8px">Если захочется</div>
<div style="font-size:14px;color:#7A7A7A;line-height:1.6">Ничего из этого мы не требуем, не проверяем и не напоминаем.</div>
</div>' WHERE id=516;

-- 517 · «Дополнительное — если есть время на этой неделе» (урок 1)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222;border-top:1px solid #D9E8F2;padding-top:16px;margin-top:6px">
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 8px">Дополнительное — если есть время на этой неделе</div>
<div style="font-size:14px;color:#7A7A7A;line-height:1.6">Углубляет тему недели. Ядро урока на это не опирается.</div>
</div>' WHERE id=517;

-- 518 · «Факультативное — когда угодно» (урок 1)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222;border-top:1px solid #D9E8F2;padding-top:16px;margin-top:6px">
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 8px">Факультативное — когда угодно</div>
<div style="font-size:14px;color:#7A7A7A;line-height:1.6">К неделе не привязано, берите в любой момент курса.</div>
</div>' WHERE id=518;

-- 519 · «Прежде чем начать» (секция «Как здесь всё устроено»)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 10px">Прежде чем начать</div>
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px">Здесь всё, что нужно знать до начала: как устроена учебная неделя, как выбирать глубину, куда ведёт курс, как растёт дерево и что от вас требуется. Прочитайте один раз — возвращаться сюда не понадобится.</div>
</div>' WHERE id=519;

-- 520 · «Как устроен этот урок» (секция «Урок 2»)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 10px">Как устроен этот урок</div>
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px;margin:0 0 14px">Вчера прошёл вебинар — он открыл эту тему. Здесь мы разбираем её с начала.</div>
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:6px 18px;margin:0 0 12px">
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#3C5D90;margin-top:9px"></div><div style="font-size:15px;line-height:1.6"><b style="color:#3C5D90">Важное — 25–30 минут:</b> три вопроса перед чтением, текст урока, тест, резюме. Прошли важное — считайте неделю сделанной.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div style="font-size:15px;line-height:1.6"><b style="color:#3C5D90">Дополнительное</b> — запись вчерашнего вебинара, лекция целиком, ответы на вопросы.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#A42BB9;margin-top:9px"></div><div style="font-size:15px;line-height:1.6"><b style="color:#3C5D90">Факультативное</b> — беседы и клипы про желания; к неделе не привязано.</div></div>
</div>
<div style="font-size:13.5px;color:#7A7A7A;line-height:1.6">Стало тяжело — берите только важное и оставайтесь в процессе. Освободилось время — поднимайтесь; материалы всех недель открыты всегда.</div>
</div>' WHERE id=520;

-- 521 · «Если захочется» (урок 2, со ссылкой на «мой вопрос»)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222;border-top:1px solid #D9E8F2;padding-top:16px;margin-top:6px">
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 8px">Если захочется</div>
<div style="font-size:14px;color:#7A7A7A;line-height:1.6">Ничего из этого мы не требуем, не проверяем и не напоминаем.</div>
<div style="font-size:14px;color:#7A7A7A;line-height:1.6;margin-top:6px">Страница <a href="https://edu.kabacademy.com/mod/assign/view.php?id=13374" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">«мой вопрос»</a> у вас одна на весь курс — она с прошлой недели, новую заводить не нужно.</div>
</div>' WHERE id=521;

-- 522 · «Дополнительное — если есть время на этой неделе» (урок 2)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222;border-top:1px solid #D9E8F2;padding-top:16px;margin-top:6px">
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 8px">Дополнительное — если есть время на этой неделе</div>
<div style="font-size:14px;color:#7A7A7A;line-height:1.6">Углубляет тему недели. Ядро урока на это не опирается.</div>
</div>' WHERE id=522;

-- 523 · «Факультативное — когда угодно» (урок 2)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222;border-top:1px solid #D9E8F2;padding-top:16px;margin-top:6px">
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 8px">Факультативное — когда угодно</div>
<div style="font-size:14px;color:#7A7A7A;line-height:1.6">К неделе не привязано, берите в любой момент курса.</div>
</div>' WHERE id=523;

-- 524 · «Как устроен этот урок» (подсекция в «Как здесь всё устроено», без заголовка)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px;margin:0 0 14px">Вчера прошёл вебинар — он открыл эту тему. Здесь мы разбираем её с начала.</div>
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:6px 18px;margin:0 0 12px">
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#3C5D90;margin-top:9px"></div><div style="font-size:15px;line-height:1.6"><b style="color:#3C5D90">Важное — 25–30 минут:</b> три вопроса перед чтением, текст урока, тест, резюме. Прошли важное — считайте неделю сделанной.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div style="font-size:15px;line-height:1.6"><b style="color:#3C5D90">Дополнительное</b> — запись вчерашнего вебинара, лекция целиком, ответы на вопросы; относится к этой неделе.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#A42BB9;margin-top:9px"></div><div style="font-size:15px;line-height:1.6"><b style="color:#3C5D90">Факультативное</b> — первоисточники и беседы; к неделе не привязано.</div></div>
</div>
<div style="font-size:13.5px;color:#7A7A7A;line-height:1.6">Стало тяжело — берите только важное и оставайтесь в процессе. Освободилось время — поднимайтесь; материалы всех недель открыты всегда.</div>
</div>' WHERE id=524;

-- 530 · «вебинар в zoom» (подсекция ВЕБИНАР)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="font-size:13px;color:#7A7A7A;font-style:italic;margin:0 0 10px"><b>*</b> Здесь будет ссылка на zoom.</div>
<div style="background:#F4F9FD;border-left:3px solid #A42BB9;border-radius:0 10px 10px 0;padding:18px 20px"><b style="color:#3C5D90;font-size:20px">↑</b> Подключайтесь по этой ссылке в указанное время трансляций: <b style="color:#3C5D90">в 8:00, в 17:00 и в 20:00</b> по израильскому времени (GMT+3)</div>
</div>' WHERE id=530;

-- ==================== SUMMARY СЕКЦИИ «Урок 1» (id 1773) =====================

UPDATE mdl_course_sections SET summary='<p><img class="img-fluid" src="@@PLUGINFILE@@/%D0%9E%D0%9A%20%D0%A3%D1%80%D0%BE%D0%BA%201.png" alt="Урок 1" width="1120" height="176"></p>
<link rel="stylesheet" href="/kab/fonts.css">
<div style="font-family:Montserrat,Arial,sans-serif;font-size:16px;line-height:1.65;color:#222">
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px">
<ul style="margin:0;padding-left:20px">
<li style="margin:0 0 8px">Чем каббала отличается от того, что вы о ней слышали раньше?</li>
<li style="margin:0 0 8px">Что здесь называют Творцом и что — творением?</li>
<li style="margin:0">Почему это знание стало открытым только сейчас?</li>
</ul>
</div>
</div>' WHERE id=1773;

-- ============================ СТРАНИЦЫ ======================================

-- 2457 · «Прежде чем читать: три вопроса» (урок 1)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px;margin:0 0 18px">Три вопроса, на которые отвечает текст урока. Ответьте на них про себя, прежде чем читать.</div>
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:6px 18px">
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">1</div><div style="padding-top:1px">Чем каббала отличается от того, что вы о ней слышали раньше?</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">2</div><div style="padding-top:1px">Что здесь называют Творцом и что — творением?</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">3</div><div style="padding-top:1px">Почему это знание стало открытым только сейчас?</div></div>
</div>
</div>' WHERE id=2457;

-- 2458 · «Резюме урока» (урок 1, плоская версия)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:6px 18px;margin:0 0 16px">
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Каббала — наука о получении, а не о тайнах.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Две силы: Творец — сила отдачи, творение — желание получать. Больше ничего нет.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Раскрытие происходит <b style="color:#3C5D90">в этом мире, при жизни</b>.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Вниз творение шло без своего участия; вверх человек идёт только сам.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Наполненное желание исчезает — отсюда лестница желаний и её верхняя ступень, вопрос о смысле.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>В книгах записано только пережитое; там, где заканчивается постижение, заканчивается текст.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Закрытость держалась на отсутствии вопроса, а не на охране тайны.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Условие одно — вопрос. Всё остальное значения не имеет.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Метод не чинит материальную жизнь напрямую; он меняет того, кто её проживает.</div></div>
</div>
<div style="background:#F4F9FD;border-left:3px solid #A42BB9;border-radius:0 10px 10px 0;padding:16px 20px">На следующей неделе — <b style="color:#3C5D90">развитие желаний</b>. Пирамиду, которую вы видели снаружи, как историю человечества, вы посмотрите изнутри.</div>
</div>' WHERE id=2458;

-- 2459 · «Архив урока 1» (скрыта; редакционная пометка сохранена как есть)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<p style="margin:0 0 14px">Здесь собирается всё, что относится к теме недели, но не входит в ядро.</p>
<ul style="margin:0 0 4px;padding-left:22px">
<li style="margin:0 0 10px"><b style="color:#3C5D90">Запись вчерашнего вебинара.</b> Появляется на следующий день после трансляции.</li>
<li style="margin:0 0 10px"><b style="color:#3C5D90">Лекция «Основы каббалы. Урок 1» целиком.</b> Тот же материал в лекционном изложении.</li>
<li style="margin:0"><b style="color:#3C5D90">Ответы на вопросы по теме урока.</b></li>
</ul>
<div style="border:1px dashed #b8a06a;background:#faf7f0;padding:12px 16px;margin:16px 0 0;border-radius:6px">
<p style="margin:0"><strong>Редакционная пометка.</strong> Ссылки подставляются после первой трансляции потока и после решения по пересъёмке лекции. До этого страница скрыта от студентов.</p></div>
</div>' WHERE id=2459;

-- 2460 · «Как выглядит учебная неделя»
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<p style="margin:0 0 16px">Курс идёт пятнадцать недель. Каждая устроена одинаково.</p>
<div style="display:flex;align-items:center;gap:12px;margin:24px 0 12px"><div style="flex:none;width:28px;height:28px;border-radius:50%;background:#52B0D8;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700">1</div><div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px">Вебинар открывает тему</div></div>
<p style="margin:0 0 14px">Неделя начинается живой встречей. Вебинар — это лекция: тему мы открываем здесь.</p>
<p style="margin:0 0 14px">Транслируем трижды в один день: <b style="color:#3C5D90">8:00, 17:00 и 20:00</b> по израильскому времени. Это одна и та же тема — выбирайте удобное время. Запись выкладываем в архив урока на следующий день.</p>
<p style="margin:0 0 14px">Со второй недели начинаем встречу с разбора вопросов, присланных за прошлую неделю.</p>
<div style="display:flex;align-items:center;gap:12px;margin:26px 0 12px"><div style="flex:none;width:28px;height:28px;border-radius:50%;background:#52B0D8;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700">2</div><div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px">На следующий день открывается урок самообучения</div></div>
<p style="margin:0 0 14px">То, что было в эфире, здесь разбираем заново и подробнее: определения, которые можно перечитать, разбор трудных мест, короткие видео, самопроверка.</p>
<p style="margin:0 0 14px">Не попали на вебинар — начинайте прямо с урока. Мы пишем урок самодостаточным, догонять ничего не нужно.</p>
<div style="display:flex;align-items:center;gap:12px;margin:26px 0 12px"><div style="flex:none;width:28px;height:28px;border-radius:50%;background:#52B0D8;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700">3</div><div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px">Семинар в группе завершает неделю</div></div>
<p style="margin:0 0 14px">Разговор с сокурсниками о теме недели. Присоединяйтесь к учебной группе когда захотите — хоть на первой неделе, хоть на десятой.</p>
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:26px 0 12px">Сколько это занимает</div>
<p style="margin:0 0 14px"><b style="color:#3C5D90">25–30 минут</b> — важное одной недели. Плюс час вебинара и час группы.</p>
<p style="margin:0">Материалы всех недель мы держим открытыми до конца курса.</p>
</div>' WHERE id=2460;

-- 2461 · «Три уровня глубины: важное, дополнительное, факультативное»
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<p style="margin:0 0 16px">В каждом уроке мы делим материалы на три уровня. Уровень выбираете вы — и меняете когда угодно, сколько угодно раз.</p>
<div style="display:flex;align-items:center;gap:10px;margin:24px 0 12px"><div style="flex:none;width:12px;height:12px;border-radius:50%;background:#3C5D90"></div><div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px">Важное</div></div>
<p style="margin:0 0 14px">Три вопроса перед чтением, текст урока с короткими видео, тест на вспоминание, резюме. 25–30 минут.</p>
<p style="margin:0 0 14px"><b style="color:#3C5D90">Прошли важное — считайте неделю сделанной.</b> Важное каждой недели самодостаточно: оно не опирается на дополнительное и факультативное прошлых недель.</p>
<div style="display:flex;align-items:center;gap:10px;margin:26px 0 12px"><div style="flex:none;width:12px;height:12px;border-radius:50%;background:#52B0D8"></div><div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px">Дополнительное</div></div>
<p style="margin:0 0 14px">Углубляет тему недели: запись вчерашнего вебинара, лекция целиком, расширенный разбор. Относится к своей неделе.</p>
<div style="display:flex;align-items:center;gap:10px;margin:26px 0 12px"><div style="flex:none;width:12px;height:12px;border-radius:50%;background:#A42BB9"></div><div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px">Факультативное</div></div>
<p style="margin:0 0 14px">Первоисточники, беседы, записи семинаров, Дни книги и кино. К неделе не привязано, берите в любой момент курса.</p>
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:26px 0 12px">Как переключаться</div>
<p style="margin:0 0 14px">Мы рассчитываем на то, что обстоятельства в жизни меняются, и собрали курс так, чтобы он это переживал.</p>
<ul style="margin:0 0 14px;padding-left:22px">
<li style="margin:0 0 10px"><b style="color:#3C5D90">Выпала неделя или стало тяжело — берите только важное</b> и оставайтесь в процессе. Это предусмотренный режим, а не аварийный.</li>
<li style="margin:0 0 10px"><b style="color:#3C5D90">Освободилось время — поднимайтесь обратно.</b> Материалы прошлых недель открыты всегда, а не только на своей неделе.</li>
<li style="margin:0"><b style="color:#3C5D90">Мы не считаем пропущенное долгом</b> и о несделанном не напоминаем. Дополнительное и факультативное в задолженность не превращаются.</li>
</ul>
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:26px 0 12px">Ничего не заперто</div>
<p style="margin:0">Мы ничего не запираем: в курсе нет условий вида «пройдите это, чтобы открылось то». Все элементы урока доступны сразу, в любом порядке. Тест ничего не открывает и не закрывает.</p>
</div>' WHERE id=2461;

-- 2462 · «Карта курса: куда ведут пятнадцать недель»
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<p style="margin:0 0 16px">Каждая тема опирается на предыдущую и отвечает на вопрос, который та открыла.</p>
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:24px 0 12px">Пять отрезков</div>
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:4px 18px;margin:0 0 10px">
<table style="border-collapse:collapse;width:100%">
<tbody>
<tr><td style="padding:10px 14px 10px 0;border-bottom:1px solid #EFF7FA;white-space:nowrap;vertical-align:top"><b style="color:#3C5D90">Недели 1–3</b></td><td style="padding:10px 0;border-bottom:1px solid #EFF7FA">Узнавание. «Это про меня»</td></tr>
<tr><td style="padding:10px 14px 10px 0;border-bottom:1px solid #EFF7FA;white-space:nowrap;vertical-align:top"><b style="color:#3C5D90">Недели 4–5</b></td><td style="padding:10px 0;border-bottom:1px solid #EFF7FA">Смещение картины. «Мир устроен не так, как я думал»</td></tr>
<tr><td style="padding:10px 14px 10px 0;border-bottom:1px solid #EFF7FA;white-space:nowrap;vertical-align:top"><b style="color:#3C5D90">Недели 6–9</b></td><td style="padding:10px 0;border-bottom:1px solid #EFF7FA">Субъектность. «Со мной можно что-то сделать»</td></tr>
<tr><td style="padding:10px 14px 10px 0;border-bottom:1px solid #EFF7FA;white-space:nowrap;vertical-align:top"><b style="color:#3C5D90">Недели 10–12</b></td><td style="padding:10px 0;border-bottom:1px solid #EFF7FA">Опора. «За этим стоит настоящая система»</td></tr>
<tr><td style="padding:10px 14px 10px 0;white-space:nowrap;vertical-align:top"><b style="color:#3C5D90">Недели 13–15</b></td><td style="padding:10px 0">Самоопределение. «Я понимаю, о чём речь, и знаю, хочу ли этого»</td></tr>
</tbody></table>
</div>
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:26px 0 12px">Темы</div>
<ol style="margin:0 0 10px;padding-left:24px">
<li style="margin:0 0 7px">Суть науки каббала: что это и для кого</li>
<li style="margin:0 0 7px">Развитие желаний</li>
<li style="margin:0 0 7px">Восприятие реальности</li>
<li style="margin:0 0 7px">Строение мироздания: четыре стадии</li>
<li style="margin:0 0 7px">Единственность Творца</li>
<li style="margin:0 0 7px">Душа и тело</li>
<li style="margin:0 0 7px">Свобода выбора: четыре фактора</li>
<li style="margin:0 0 7px">Окружение как инструмент</li>
<li style="margin:0 0 7px">Намерение: ради чего</li>
<li style="margin:0 0 7px">Откуда мы это знаем</li>
<li style="margin:0 0 7px">Зачем человеку высшие миры</li>
<li style="margin:0 0 7px">Строение миров, разбиение и исправление души</li>
<li style="margin:0 0 7px">Невозможно подняться в одиночку</li>
<li style="margin:0 0 7px">Духовное и материальное: что меняется</li>
<li style="margin:0">Что дальше и чего это требует</li>
</ol>
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:26px 0 12px">Три навыка, которые переносятся во вторую часть курса</div>
<ul style="margin:0 0 10px;padding-left:22px">
<li style="margin:0 0 7px">Различать в себе желание и наполнение.</li>
<li style="margin:0 0 7px">Удерживать вопрос, не закрывая его быстрым ответом.</li>
<li style="margin:0">Пользоваться окружением.</li>
</ul>
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:26px 0 12px">К чему вы придёте на пятнадцатой неделе</div>
<p style="margin:0 0 14px">К тому, чтобы своими словами сказать, о чём эта наука, что здесь предлагают делать и хотите ли вы это делать.</p>
<div style="background:#F4F9FD;border-left:3px solid #A42BB9;border-radius:0 10px 10px 0;padding:16px 20px">Ответ «да» и ответ «нет» здесь равноценны. Мы строим курс не для того, чтобы все дошли до конца, а для того, чтобы решение вы приняли на основании самих знаний, а не рассказов со стороны. Скажете к пятнадцатой неделе «я понял, о чём речь, и это не моё» — мы считаем, что сделали свою работу.</div>
</div>' WHERE id=2462;

-- 2463 · «Что требуется и что не требуется»
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:0 0 12px">Условие одно — вопрос</div>
<p style="margin:0 0 14px">Возраст, пол, национальность, страна, вера, образование и предварительная подготовка значения не имеют. Не нужно быть евреем, не нужно быть религиозным — и не нужно перестать им быть, если вы религиозны. Не нужно менять образ жизни, работу, семью или круг общения.</p>
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:26px 0 12px">Оплата</div>
<p style="margin:0 0 14px"><b style="color:#3C5D90">Обучение платное.</b> Но стоимость определяете вы сами — по мере того, как оцените пользу, — и платите когда захотите: в процессе или в конце курса.</p>
<p style="margin:0 0 14px">Мы уверены в своём продукте, поэтому готовы отдать этот выбор вам. Никаких маленьких букв: нет обязательного платежа, нет автоматических списаний, нет ограничения доступа за неоплату.</p>
<div style="background:#F4F9FD;border-left:3px solid #A42BB9;border-radius:0 10px 10px 0;padding:14px 18px;margin:0 0 14px"><b style="color:#3C5D90">Ушли раньше — ничего не должны.</b></div>
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:26px 0 12px">Обязательных заданий нет</div>
<p style="margin:0 0 14px">В каждом уроке есть два элемента, которые предлагаются, но не требуются.</p>
<ul style="margin:0 0 14px;padding-left:22px">
<li style="margin:0 0 10px"><b style="color:#3C5D90">Отклик недели.</b> Написать своими словами ответ на один из вопросов урока — на форум или в личную запись. Из откликов мы собираем блок вопросов для вебинара следующей недели.</li>
<li style="margin:0"><b style="color:#3C5D90">Страница «мой вопрос».</b> Одна заметка с вопросом, на который вы хотите получить ответ за пятнадцать недель. К ней вы вернётесь на пятой, десятой и пятнадцатой неделе.</li>
</ul>
<p style="margin:0 0 14px">Мы не считаем и не проверяем ни то, ни другое и о несделанном не напоминаем.</p>
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:26px 0 12px">Что делать, если непонятно</div>
<p style="margin:0">Спрашивайте: в форме «вопрос к вебинару» внутри урока, на форуме или в учебной группе. Со второй недели каждый вебинар мы начинаем с разбора вопросов, присланных за прошлую неделю.</p>
</div>' WHERE id=2463;

-- 2464 · «Дерево, баллы, значок и сертификаты» (скрыта; пометки сохранены)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<p style="margin:0 0 16px">У каждого участника курса есть своё дерево. Оно начинается зерном и растёт по мере того, как вы проходите недели.</p>
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:24px 0 12px">Как оно растёт</div>
<p style="margin:0 0 14px">За пройденное вы получаете «капли живой воды», и на них дерево поднимается по уровням: росток, первые листочки, ствол, ветви, крона.</p>
<p style="margin:0 0 14px"><b style="color:#3C5D90">Важного каждой недели достаточно, чтобы вырастить дерево целиком.</b> Дополнительное и факультативное ускоряют рост, но идущий по минимуму приходит к вершине — он прошёл курс, а не отстал.</p>
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:26px 0 12px">Что дерево не делает</div>
<ul style="margin:0 0 14px;padding-left:22px">
<li style="margin:0 0 10px"><b style="color:#3C5D90">Не засыхает.</b> Пропущенная неделя останавливает рост, но мы ничего не отнимаем. Вернулись — продолжили с того места, где остановились.</li>
<li style="margin:0 0 10px"><b style="color:#3C5D90">Не сравнивает вас с другими.</b> Мы не выстраиваем участников в список: это ваше дерево и ваш путь.</li>
<li style="margin:0"><b style="color:#3C5D90">Ничего не запирает.</b> Уровнем мы не открываем и не закрываем доступ к материалам. Все элементы урока доступны сразу.</li>
</ul>
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:26px 0 12px">Тест внутри урока</div>
<p style="margin:0 0 14px">Тест не влияет ни на дерево, ни на доступ. Попыток столько, сколько захотите, цена ошибки нулевая, разбор написан на каждый вариант ответа — в том числе на неверный. Мы ставим его, чтобы вы вспомнили, а не чтобы вас оценить.</p>
<div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px;margin:26px 0 12px">Значок и сертификат</div>
<p style="margin:0 0 14px">За прохождение курса мы выдаём значок участника и электронный сертификат. Кроме этого есть сертификат на приобретение книги в наших книжных магазинах.</p>
<div style="border:1px dashed #b8a06a;background:#faf7f0;padding:12px 16px;margin:16px 0 0;border-radius:6px">
<p style="margin:0"><strong>Редакционная пометка — страница скрыта от студентов.</strong></p>
<p style="margin:.5em 0 0">Здесь подставляются точные условия наград, когда они утверждены: что считается прохождением курса (предложено — важное 12 недель из 15 вместо цепочки из 45 отметок) и на каких условиях выдаётся сертификат на книгу (предложено — снять со уровней дерева и привязать к прохождению; число ступеней скидки — решение Бориса).</p>
<p style="margin:.5em 0 0">Формулировки «вы набрали максимальное количество баллов» на странице сознательно нет: она про сравнение с другими, а награда должна быть про сделанное.</p>
<p style="margin:.5em 0 0">Сама страница описывает целевую настройку Level Up, а не текущую. До того как администратор изменит пороги уровней и таблицу начисления, утверждения «важного достаточно, чтобы вырастить дерево целиком» и «не сравнивает вас с другими» неверны — сейчас порог второго уровня 120 капель и в блоке виден номер места. Открывать страницу студентам только после правок.</p></div>
</div>' WHERE id=2464;

-- 2465 · «Прежде чем читать: три вопроса» (урок 2)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px;margin:0 0 18px">Три вопроса, на которые отвечает текст урока. Ответьте на них про себя, прежде чем читать.</div>
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:6px 18px">
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">1</div><div style="padding-top:1px">Почему то, чего вы долго добивались, перестало радовать?</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">2</div><div style="padding-top:1px">Кто решает, чего вам хочется?</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 0"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">3</div><div style="padding-top:1px">Какое желание не закрывается ничем из того, что можно получить?</div></div>
</div>
</div>' WHERE id=2465;

-- 2466 · «Резюме урока» (урок 2)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:6px 18px;margin:0 0 16px">
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Наслаждение живёт в промежутке между нехваткой и наполнением; наполненное желание исчезает.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Желания растут, и переход между ступенями качественный.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Желания не под контролем человека и приходят от окружения.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Действуют на желание только наслаждение и страдание; уменьшенное желание меньше ранит.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Проигрыш весит больше выигрыша; полученное быстро становится нормой.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Само желание нейтрально — эгоизмом называется намерение, а не желание.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Чувства первичны, разум вторичен.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Одно желание не подчиняется этим свойствам, потому что не наполняется получением, — оно и привело вас сюда.</div></div>
</div>
<div style="background:#F4F9FD;border-left:3px solid #A42BB9;border-radius:0 10px 10px 0;padding:16px 20px">На следующей неделе — <b style="color:#3C5D90">восприятие реальности</b>. Если всё, что человек чувствует, зависит от устройства его желаний, встаёт следующий вопрос: что он вообще видит вокруг себя?</div>
</div>' WHERE id=2466;

-- 2467 · «Архив урока 2» (скрыта; пометка сохранена)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<ul style="margin:0 0 4px;padding-left:22px">
<li style="margin:0 0 10px"><b style="color:#3C5D90">Запись вчерашнего вебинара.</b> Появляется на следующий день после трансляции.</li>
<li style="margin:0 0 10px"><b style="color:#3C5D90">Лекция «Развитие желаний» целиком.</b> Тот же материал в лекционном изложении.</li>
<li style="margin:0"><b style="color:#3C5D90">Ответы на вопросы по теме урока.</b></li>
</ul>
<div style="border:1px dashed #b8a06a;background:#faf7f0;padding:12px 16px;margin:16px 0 0;border-radius:6px">
<p style="margin:0"><strong>Редакционная пометка.</strong> Ссылки подставляются после первой трансляции потока. До этого страница скрыта от студентов.</p></div>
</div>' WHERE id=2467;

-- 2468 · «Ещё про желания — беседы и клипы»
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<p style="margin:0 0 16px">Четыре записи из архива, каждая про одно из свойств этого урока. Смотреть можно в любом порядке и в любое время.</p>
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:6px 18px">
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#A42BB9;margin-top:9px"></div><div><a href="https://kabbalahmedia.info/ru/lessons/cu/DqVYrQWV" target="_blank" rel="noreferrer" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">Улыбающаяся депрессия</a> — 10 минут. «Может быть всё, но я чувствую себя пустым.»</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#A42BB9;margin-top:9px"></div><div><a href="https://kabbalahmedia.info/ru/lessons/cu/YqSkYVIh" target="_blank" rel="noreferrer" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">Не в деньгах счастье</a> — про то, что счастье держится только над нехваткой.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#A42BB9;margin-top:9px"></div><div><a href="https://kabbalahmedia.info/ru/lessons/cu/Tqj0YtQi" target="_blank" rel="noreferrer" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">Почему мы любим приобретать</a> — откуда берётся «моё».</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#A42BB9;margin-top:9px"></div><div><a href="https://kabbalahmedia.info/ru/lessons/cu/hAhjSri2" target="_blank" rel="noreferrer" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">Как правильно использовать злость, грусть и страх</a> — 9 минут; начинается с прямого примера роста желаний.</div></div>
</div>
</div>' WHERE id=2468;

-- 2470 · «Резюме урока 1» (подсекция 🟢 Основное)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:6px 18px;margin:0 0 16px">
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Каббала — наука о получении, а не о тайнах.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Две силы: Творец — сила отдачи, творение — желание получать. Больше ничего нет.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Раскрытие происходит <b style="color:#3C5D90">в этом мире, при жизни</b>.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Вниз творение шло без своего участия; вверх человек идёт только сам.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Наполненное желание исчезает — отсюда лестница желаний и её верхняя ступень, вопрос о смысле.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>В книгах записано только пережитое; там, где заканчивается постижение, заканчивается текст.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Закрытость держалась на отсутствии вопроса, а не на охране тайны.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Условие одно — вопрос. Всё остальное значения не имеет.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#52B0D8;margin-top:9px"></div><div>Метод не чинит материальную жизнь напрямую; он меняет того, кто её проживает.</div></div>
</div>
<div style="background:#F4F9FD;border-left:3px solid #A42BB9;border-radius:0 10px 10px 0;padding:16px 20px">На следующей неделе — <b style="color:#3C5D90">развитие желаний</b>. Пирамиду, которую вы видели снаружи, как историю человечества, вы посмотрите изнутри.</div>
</div>' WHERE id=2470;

-- 2471 · «Запись вебинара 1 (архив)» (подсекция ВЕБИНАР; пометка сохранена)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<p style="margin:0 0 14px">Здесь собирается всё, что относится к теме недели, но не входит в ядро.</p>
<ul style="margin:0 0 4px;padding-left:22px">
<li style="margin:0 0 10px"><b style="color:#3C5D90">Запись вчерашних вебинаров.</b></li>
<li style="margin:0"><b style="color:#3C5D90">Ответы на вопросы прошлой недели.</b></li>
</ul>
<div style="border:1px dashed #b8a06a;background:#faf7f0;padding:12px 16px;margin:16px 0 0;border-radius:6px">
<p style="margin:0"><strong>Редакционная пометка.</strong> Ссылки подставляются после первой трансляции потока и после решения по пересъёмке лекции. До этого страница скрыта от студентов.</p>
</div>
</div>' WHERE id=2471;

-- 2472 · «📓 Мой Дневник» (секция 0)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:16px 20px;margin:0 0 16px">Заведите <b style="color:#3C5D90">Дневник</b> (бумажный или электронный) и ведите его во время прохождения курса.</div>
<p style="margin:0 0 10px">Примерный формат:</p>
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:6px 18px">
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">1</div><div style="padding-top:1px">Для начала сформулируйте вопрос, на который хотите получить ответ за пятнадцать недель.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">2</div><div style="padding-top:1px">Описывайте свои впечатления и состояние на каждой учебной неделе.</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0"><div style="flex:none;width:24px;height:24px;border-radius:50%;border:1.5px solid #52B0D8;color:#3C5D90;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">3</div><div style="padding-top:1px">Записывайте ваши мысли, в которых вы вынесли что-то новое или неожиданное, подтверждение ваших догадок и ощущений.</div></div>
</div>
<div style="font-size:13px;color:#7A7A7A;line-height:1.5;margin-top:10px">Заметки в процессе курса помогут вам лучше оценить ваше продвижение.</div>
</div>' WHERE id=2472;

-- 2473 · «Тематические дни» (подсекция 💠 Факультативное)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">
<p style="margin:0 0 14px"><b style="color:#3C5D90">В течение учебной недели</b> проходят мероприятия в рамках учебной программы в Telegram (см. <a href="https://edu.kabacademy.com/calendar/view.php?view=upcoming&amp;course=236" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">календарь курса</a>).</p>
<p style="margin:0 0 18px">Подключайтесь в <a href="https://t.me/makosen2026" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">Telegram-канал</a> и следите за сообщениями!</p>
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 10px">Вас ждут тематические дни</div>
<div style="border:1px solid #D9E8F2;border-radius:12px;background:#fff;padding:6px 18px;margin:0 0 12px">
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#A42BB9;margin-top:9px"></div><div>день книги – в субботу</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #EFF7FA"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#A42BB9;margin-top:9px"></div><div>день кино – в воскресенье</div></div>
<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0"><div style="flex:none;width:10px;height:10px;border-radius:50%;background:#A42BB9;margin-top:9px"></div><div>день экскурсий – в пятницу (со 2-ой недели)</div></div>
</div>
<div style="font-size:13px;color:#7A7A7A;line-height:1.5;margin:0 0 10px">Пост с тематического дня публикуется в 8:00 по изр. вр. (GMT+3).</div>
<p style="margin:0">Участвовать и делиться впечатлениями можно в удобное время в течение всей недели.</p>
</div>' WHERE id=2473;

-- ============================ КЭШ ===========================================
UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=238;

-- Контроль
SELECT id, CHAR_LENGTH(intro) AS len FROM mdl_label WHERE course=238 ORDER BY id;
SELECT id, CHAR_LENGTH(content) AS len FROM mdl_page WHERE course=238 ORDER BY id;
SELECT id, CHAR_LENGTH(summary) AS len FROM mdl_course_sections WHERE id=1773;
