-- ============================================================================
-- Курс 238 — перевод контента с inline-стилей на классы .kab-* темы
-- kabacademy (дочка Boost, включена на курсе). Тексты дословно.
-- 2026-09-08, этап 2. Требует: тема kabacademy с kab.scss от eace5c6 + Purge caches.
-- Бэкапы: _kab_bk20260908_*238 — исходное (до стилизации),
--         _kab_bk20260908b_*238 — inline-версия (этот снапшот).
-- Откат на inline-версию: rollback из *b*-таблиц; исходник — из таблиц без b.
-- ============================================================================

CREATE TABLE IF NOT EXISTS _kab_bk20260908b_label238 AS SELECT * FROM mdl_label WHERE course=238;
CREATE TABLE IF NOT EXISTS _kab_bk20260908b_page238 AS SELECT * FROM mdl_page WHERE course=238;
CREATE TABLE IF NOT EXISTS _kab_bk20260908b_sections238 AS SELECT * FROM mdl_course_sections WHERE course=238;
CREATE TABLE IF NOT EXISTS _kab_bk20260908b_forum238 AS SELECT * FROM mdl_forum WHERE course=238;
CREATE TABLE IF NOT EXISTS _kab_bk20260908b_quiz238 AS SELECT * FROM mdl_quiz WHERE course=238;
CREATE TABLE IF NOT EXISTS _kab_bk20260908b_feedback238 AS SELECT * FROM mdl_feedback WHERE course=238;
CREATE TABLE IF NOT EXISTS _kab_bk20260908b_assign238 AS SELECT * FROM mdl_assign WHERE course=238;

-- ============================ ЛЕЙБЛЫ ========================================

-- 515 · «Как устроен этот урок» (секция «Урок 1», плоская версия)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text">
<div class="kab-kicker">Как устроен этот урок</div>
<div class="kab-quote">Вчера прошёл вебинар — он открыл эту тему. Здесь мы разбираем её с начала.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-dot kab-dot--navy"></div><div><b>Важное — 25–30 минут:</b> три вопроса перед чтением, текст урока, тест, резюме. Прошли важное — считайте неделю сделанной.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div><b>Дополнительное</b> — запись вчерашнего вебинара, лекция целиком, ответы на вопросы; относится к этой неделе.</div></div>
<div class="kab-row"><div class="kab-dot kab-dot--purple"></div><div><b>Факультативное</b> — первоисточники и беседы; к неделе не привязано.</div></div>
</div>
<div class="kab-note">Стало тяжело — берите только важное и оставайтесь в процессе. Освободилось время — поднимайтесь; материалы всех недель открыты всегда.</div>
</div>' WHERE id=515;

-- 516 · «Если захочется» (урок 1)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text kab-divider">
<div class="kab-kicker">Если захочется</div>
<div class="kab-note">Ничего из этого мы не требуем, не проверяем и не напоминаем.</div>
</div>' WHERE id=516;

-- 517 · «Дополнительное — если есть время на этой неделе» (урок 1)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text kab-divider">
<div class="kab-kicker">Дополнительное — если есть время на этой неделе</div>
<div class="kab-note">Углубляет тему недели. Ядро урока на это не опирается.</div>
</div>' WHERE id=517;

-- 518 · «Факультативное — когда угодно» (урок 1)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text kab-divider">
<div class="kab-kicker">Факультативное — когда угодно</div>
<div class="kab-note">К неделе не привязано, берите в любой момент курса.</div>
</div>' WHERE id=518;

-- 519 · «Прежде чем начать» (секция «Как здесь всё устроено»)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text">
<div class="kab-kicker">Прежде чем начать</div>
<div class="kab-quote">Здесь всё, что нужно знать до начала: как устроена учебная неделя, как выбирать глубину, куда ведёт курс, как растёт дерево и что от вас требуется. Прочитайте один раз — возвращаться сюда не понадобится.</div>
</div>' WHERE id=519;

-- 520 · «Как устроен этот урок» (секция «Урок 2»)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text">
<div class="kab-kicker">Как устроен этот урок</div>
<div class="kab-quote">Вчера прошёл вебинар — он открыл эту тему. Здесь мы разбираем её с начала.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-dot kab-dot--navy"></div><div><b>Важное — 25–30 минут:</b> три вопроса перед чтением, текст урока, тест, резюме. Прошли важное — считайте неделю сделанной.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div><b>Дополнительное</b> — запись вчерашнего вебинара, лекция целиком, ответы на вопросы.</div></div>
<div class="kab-row"><div class="kab-dot kab-dot--purple"></div><div><b>Факультативное</b> — беседы и клипы про желания; к неделе не привязано.</div></div>
</div>
<div class="kab-note">Стало тяжело — берите только важное и оставайтесь в процессе. Освободилось время — поднимайтесь; материалы всех недель открыты всегда.</div>
</div>' WHERE id=520;

-- 521 · «Если захочется» (урок 2, со ссылкой на «мой вопрос»)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text kab-divider">
<div class="kab-kicker">Если захочется</div>
<div class="kab-note">Ничего из этого мы не требуем, не проверяем и не напоминаем.</div>
<div class="kab-note">Страница <a href="https://edu.kabacademy.com/mod/assign/view.php?id=13374" class="kab-link">«мой вопрос»</a> у вас одна на весь курс — она с прошлой недели, новую заводить не нужно.</div>
</div>' WHERE id=521;

-- 522 · «Дополнительное — если есть время на этой неделе» (урок 2)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text kab-divider">
<div class="kab-kicker">Дополнительное — если есть время на этой неделе</div>
<div class="kab-note">Углубляет тему недели. Ядро урока на это не опирается.</div>
</div>' WHERE id=522;

-- 523 · «Факультативное — когда угодно» (урок 2)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text kab-divider">
<div class="kab-kicker">Факультативное — когда угодно</div>
<div class="kab-note">К неделе не привязано, берите в любой момент курса.</div>
</div>' WHERE id=523;

-- 524 · «Как устроен этот урок» (подсекция, без заголовка)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text">
<div class="kab-quote">Вчера прошёл вебинар — он открыл эту тему. Здесь мы разбираем её с начала.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-dot kab-dot--navy"></div><div><b>Важное — 25–30 минут:</b> три вопроса перед чтением, текст урока, тест, резюме. Прошли важное — считайте неделю сделанной.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div><b>Дополнительное</b> — запись вчерашнего вебинара, лекция целиком, ответы на вопросы; относится к этой неделе.</div></div>
<div class="kab-row"><div class="kab-dot kab-dot--purple"></div><div><b>Факультативное</b> — первоисточники и беседы; к неделе не привязано.</div></div>
</div>
<div class="kab-note">Стало тяжело — берите только важное и оставайтесь в процессе. Освободилось время — поднимайтесь; материалы всех недель открыты всегда.</div>
</div>' WHERE id=524;

-- 530 · «вебинар в zoom» (подсекция ВЕБИНАР)
UPDATE mdl_label SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text">
<div class="kab-note" style="font-style:italic;margin-bottom:10px"><b>*</b> Здесь будет ссылка на zoom.</div>
<div class="kab-quote kab-quote--purple"><b style="font-size:20px">↑</b> Подключайтесь по этой ссылке в указанное время трансляций: <b>в 8:00, в 17:00 и в 20:00</b> по израильскому времени (GMT+3)</div>
</div>' WHERE id=530;

-- ==================== SUMMARY СЕКЦИИ «Урок 1» (id 1773) =====================

UPDATE mdl_course_sections SET summary='<p><img class="img-fluid" src="@@PLUGINFILE@@/%D0%9E%D0%9A%20%D0%A3%D1%80%D0%BE%D0%BA%201.png" alt="Урок 1" width="1120" height="176"></p>
<div class="kab-text">
<div class="kab-quote">
<ul class="kab-list">
<li>Чем каббала отличается от того, что вы о ней слышали раньше?</li>
<li>Что здесь называют Творцом и что — творением?</li>
<li>Почему это знание стало открытым только сейчас?</li>
</ul>
</div>
</div>' WHERE id=1773;

-- ============================ СТРАНИЦЫ ======================================

-- 2457 · «Прежде чем читать: три вопроса» (урок 1)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<div class="kab-quote">Три вопроса, на которые отвечает текст урока. Ответьте на них про себя, прежде чем читать.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Чем каббала отличается от того, что вы о ней слышали раньше?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Что здесь называют Творцом и что — творением?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Почему это знание стало открытым только сейчас?</div></div>
</div>
</div>' WHERE id=2457;

-- 2458 · «Резюме урока» (урок 1, плоская версия)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<div class="kab-card">
<div class="kab-row"><div class="kab-dot"></div><div>Каббала — наука о получении, а не о тайнах.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Две силы: Творец — сила отдачи, творение — желание получать. Больше ничего нет.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Раскрытие происходит <b>в этом мире, при жизни</b>.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Вниз творение шло без своего участия; вверх человек идёт только сам.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Наполненное желание исчезает — отсюда лестница желаний и её верхняя ступень, вопрос о смысле.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>В книгах записано только пережитое; там, где заканчивается постижение, заканчивается текст.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Закрытость держалась на отсутствии вопроса, а не на охране тайны.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Условие одно — вопрос. Всё остальное значения не имеет.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Метод не чинит материальную жизнь напрямую; он меняет того, кто её проживает.</div></div>
</div>
<div class="kab-quote kab-quote--purple">На следующей неделе — <b>развитие желаний</b>. Пирамиду, которую вы видели снаружи, как историю человечества, вы посмотрите изнутри.</div>
</div>' WHERE id=2458;

-- 2459 · «Архив урока 1» (скрыта; редакционная пометка как есть)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<p>Здесь собирается всё, что относится к теме недели, но не входит в ядро.</p>
<ul class="kab-list">
<li><b>Запись вчерашнего вебинара.</b> Появляется на следующий день после трансляции.</li>
<li><b>Лекция «Основы каббалы. Урок 1» целиком.</b> Тот же материал в лекционном изложении.</li>
<li><b>Ответы на вопросы по теме урока.</b></li>
</ul>
<div style="border:1px dashed #b8a06a;background:#faf7f0;padding:12px 16px;margin:16px 0 0;border-radius:6px">
<p style="margin:0"><strong>Редакционная пометка.</strong> Ссылки подставляются после первой трансляции потока и после решения по пересъёмке лекции. До этого страница скрыта от студентов.</p></div>
</div>' WHERE id=2459;

-- 2460 · «Как выглядит учебная неделя»
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<p>Курс идёт пятнадцать недель. Каждая устроена одинаково.</p>
<div class="kab-step"><div class="kab-step-num">1</div><div class="kab-step-title">Вебинар открывает тему</div></div>
<p>Неделя начинается живой встречей. Вебинар — это лекция: тему мы открываем здесь.</p>
<p>Транслируем трижды в один день: <b>8:00, 17:00 и 20:00</b> по израильскому времени. Это одна и та же тема — выбирайте удобное время. Запись выкладываем в архив урока на следующий день.</p>
<p>Со второй недели начинаем встречу с разбора вопросов, присланных за прошлую неделю.</p>
<div class="kab-step"><div class="kab-step-num">2</div><div class="kab-step-title">На следующий день открывается урок самообучения</div></div>
<p>То, что было в эфире, здесь разбираем заново и подробнее: определения, которые можно перечитать, разбор трудных мест, короткие видео, самопроверка.</p>
<p>Не попали на вебинар — начинайте прямо с урока. Мы пишем урок самодостаточным, догонять ничего не нужно.</p>
<div class="kab-step"><div class="kab-step-num">3</div><div class="kab-step-title">Семинар в группе завершает неделю</div></div>
<p>Разговор с сокурсниками о теме недели. Присоединяйтесь к учебной группе когда захотите — хоть на первой неделе, хоть на десятой.</p>
<div class="kab-h">Сколько это занимает</div>
<p><b>25–30 минут</b> — важное одной недели. Плюс час вебинара и час группы.</p>
<p>Материалы всех недель мы держим открытыми до конца курса.</p>
</div>' WHERE id=2460;

-- 2461 · «Три уровня глубины: важное, дополнительное, факультативное»
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<p>В каждом уроке мы делим материалы на три уровня. Уровень выбираете вы — и меняете когда угодно, сколько угодно раз.</p>
<div class="kab-step"><div class="kab-dot kab-dot--navy"></div><div class="kab-step-title">Важное</div></div>
<p>Три вопроса перед чтением, текст урока с короткими видео, тест на вспоминание, резюме. 25–30 минут.</p>
<p><b>Прошли важное — считайте неделю сделанной.</b> Важное каждой недели самодостаточно: оно не опирается на дополнительное и факультативное прошлых недель.</p>
<div class="kab-step"><div class="kab-dot"></div><div class="kab-step-title">Дополнительное</div></div>
<p>Углубляет тему недели: запись вчерашнего вебинара, лекция целиком, расширенный разбор. Относится к своей неделе.</p>
<div class="kab-step"><div class="kab-dot kab-dot--purple"></div><div class="kab-step-title">Факультативное</div></div>
<p>Первоисточники, беседы, записи семинаров, Дни книги и кино. К неделе не привязано, берите в любой момент курса.</p>
<div class="kab-h">Как переключаться</div>
<p>Мы рассчитываем на то, что обстоятельства в жизни меняются, и собрали курс так, чтобы он это переживал.</p>
<ul class="kab-list">
<li><b>Выпала неделя или стало тяжело — берите только важное</b> и оставайтесь в процессе. Это предусмотренный режим, а не аварийный.</li>
<li><b>Освободилось время — поднимайтесь обратно.</b> Материалы прошлых недель открыты всегда, а не только на своей неделе.</li>
<li><b>Мы не считаем пропущенное долгом</b> и о несделанном не напоминаем. Дополнительное и факультативное в задолженность не превращаются.</li>
</ul>
<div class="kab-h">Ничего не заперто</div>
<p>Мы ничего не запираем: в курсе нет условий вида «пройдите это, чтобы открылось то». Все элементы урока доступны сразу, в любом порядке. Тест ничего не открывает и не закрывает.</p>
</div>' WHERE id=2461;

-- 2462 · «Карта курса: куда ведут пятнадцать недель»
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<p>Каждая тема опирается на предыдущую и отвечает на вопрос, который та открыла.</p>
<div class="kab-h">Пять отрезков</div>
<div class="kab-card">
<table class="kab-table">
<tbody>
<tr><td>Недели 1–3</td><td>Узнавание. «Это про меня»</td></tr>
<tr><td>Недели 4–5</td><td>Смещение картины. «Мир устроен не так, как я думал»</td></tr>
<tr><td>Недели 6–9</td><td>Субъектность. «Со мной можно что-то сделать»</td></tr>
<tr><td>Недели 10–12</td><td>Опора. «За этим стоит настоящая система»</td></tr>
<tr><td>Недели 13–15</td><td>Самоопределение. «Я понимаю, о чём речь, и знаю, хочу ли этого»</td></tr>
</tbody></table>
</div>
<div class="kab-h">Темы</div>
<ol class="kab-list" style="padding-left:24px">
<li>Суть науки каббала: что это и для кого</li>
<li>Развитие желаний</li>
<li>Восприятие реальности</li>
<li>Строение мироздания: четыре стадии</li>
<li>Единственность Творца</li>
<li>Душа и тело</li>
<li>Свобода выбора: четыре фактора</li>
<li>Окружение как инструмент</li>
<li>Намерение: ради чего</li>
<li>Откуда мы это знаем</li>
<li>Зачем человеку высшие миры</li>
<li>Строение миров, разбиение и исправление души</li>
<li>Невозможно подняться в одиночку</li>
<li>Духовное и материальное: что меняется</li>
<li>Что дальше и чего это требует</li>
</ol>
<div class="kab-h">Три навыка, которые переносятся во вторую часть курса</div>
<ul class="kab-list">
<li>Различать в себе желание и наполнение.</li>
<li>Удерживать вопрос, не закрывая его быстрым ответом.</li>
<li>Пользоваться окружением.</li>
</ul>
<div class="kab-h">К чему вы придёте на пятнадцатой неделе</div>
<p>К тому, чтобы своими словами сказать, о чём эта наука, что здесь предлагают делать и хотите ли вы это делать.</p>
<div class="kab-quote kab-quote--purple">Ответ «да» и ответ «нет» здесь равноценны. Мы строим курс не для того, чтобы все дошли до конца, а для того, чтобы решение вы приняли на основании самих знаний, а не рассказов со стороны. Скажете к пятнадцатой неделе «я понял, о чём речь, и это не моё» — мы считаем, что сделали свою работу.</div>
</div>' WHERE id=2462;

-- 2463 · «Что требуется и что не требуется»
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<div class="kab-h">Условие одно — вопрос</div>
<p>Возраст, пол, национальность, страна, вера, образование и предварительная подготовка значения не имеют. Не нужно быть евреем, не нужно быть религиозным — и не нужно перестать им быть, если вы религиозны. Не нужно менять образ жизни, работу, семью или круг общения.</p>
<div class="kab-h">Оплата</div>
<p><b>Обучение платное.</b> Но стоимость определяете вы сами — по мере того, как оцените пользу, — и платите когда захотите: в процессе или в конце курса.</p>
<p>Мы уверены в своём продукте, поэтому готовы отдать этот выбор вам. Никаких маленьких букв: нет обязательного платежа, нет автоматических списаний, нет ограничения доступа за неоплату.</p>
<div class="kab-quote kab-quote--purple"><b>Ушли раньше — ничего не должны.</b></div>
<div class="kab-h">Обязательных заданий нет</div>
<p>В каждом уроке есть два элемента, которые предлагаются, но не требуются.</p>
<ul class="kab-list">
<li><b>Отклик недели.</b> Написать своими словами ответ на один из вопросов урока — на форум или в личную запись. Из откликов мы собираем блок вопросов для вебинара следующей недели.</li>
<li><b>Страница «мой вопрос».</b> Одна заметка с вопросом, на который вы хотите получить ответ за пятнадцать недель. К ней вы вернётесь на пятой, десятой и пятнадцатой неделе.</li>
</ul>
<p>Мы не считаем и не проверяем ни то, ни другое и о несделанном не напоминаем.</p>
<div class="kab-h">Что делать, если непонятно</div>
<p>Спрашивайте: в форме «вопрос к вебинару» внутри урока, на форуме или в учебной группе. Со второй недели каждый вебинар мы начинаем с разбора вопросов, присланных за прошлую неделю.</p>
</div>' WHERE id=2463;

-- 2464 · «Дерево, баллы, значок и сертификаты» (скрыта; пометки как есть)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<p>У каждого участника курса есть своё дерево. Оно начинается зерном и растёт по мере того, как вы проходите недели.</p>
<div class="kab-h">Как оно растёт</div>
<p>За пройденное вы получаете «капли живой воды», и на них дерево поднимается по уровням: росток, первые листочки, ствол, ветви, крона.</p>
<p><b>Важного каждой недели достаточно, чтобы вырастить дерево целиком.</b> Дополнительное и факультативное ускоряют рост, но идущий по минимуму приходит к вершине — он прошёл курс, а не отстал.</p>
<div class="kab-h">Что дерево не делает</div>
<ul class="kab-list">
<li><b>Не засыхает.</b> Пропущенная неделя останавливает рост, но мы ничего не отнимаем. Вернулись — продолжили с того места, где остановились.</li>
<li><b>Не сравнивает вас с другими.</b> Мы не выстраиваем участников в список: это ваше дерево и ваш путь.</li>
<li><b>Ничего не запирает.</b> Уровнем мы не открываем и не закрываем доступ к материалам. Все элементы урока доступны сразу.</li>
</ul>
<div class="kab-h">Тест внутри урока</div>
<p>Тест не влияет ни на дерево, ни на доступ. Попыток столько, сколько захотите, цена ошибки нулевая, разбор написан на каждый вариант ответа — в том числе на неверный. Мы ставим его, чтобы вы вспомнили, а не чтобы вас оценить.</p>
<div class="kab-h">Значок и сертификат</div>
<p>За прохождение курса мы выдаём значок участника и электронный сертификат. Кроме этого есть сертификат на приобретение книги в наших книжных магазинах.</p>
<div style="border:1px dashed #b8a06a;background:#faf7f0;padding:12px 16px;margin:16px 0 0;border-radius:6px">
<p style="margin:0"><strong>Редакционная пометка — страница скрыта от студентов.</strong></p>
<p style="margin:.5em 0 0">Здесь подставляются точные условия наград, когда они утверждены: что считается прохождением курса (предложено — важное 12 недель из 15 вместо цепочки из 45 отметок) и на каких условиях выдаётся сертификат на книгу (предложено — снять со уровней дерева и привязать к прохождению; число ступеней скидки — решение Бориса).</p>
<p style="margin:.5em 0 0">Формулировки «вы набрали максимальное количество баллов» на странице сознательно нет: она про сравнение с другими, а награда должна быть про сделанное.</p>
<p style="margin:.5em 0 0">Сама страница описывает целевую настройку Level Up, а не текущую. До того как администратор изменит пороги уровней и таблицу начисления, утверждения «важного достаточно, чтобы вырастить дерево целиком» и «не сравнивает вас с другими» неверны — сейчас порог второго уровня 120 капель и в блоке виден номер места. Открывать страницу студентам только после правок.</p></div>
</div>' WHERE id=2464;

-- 2465 · «Прежде чем читать: три вопроса» (урок 2)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<div class="kab-quote">Три вопроса, на которые отвечает текст урока. Ответьте на них про себя, прежде чем читать.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Почему то, чего вы долго добивались, перестало радовать?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Кто решает, чего вам хочется?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Какое желание не закрывается ничем из того, что можно получить?</div></div>
</div>
</div>' WHERE id=2465;

-- 2466 · «Резюме урока» (урок 2)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<div class="kab-card">
<div class="kab-row"><div class="kab-dot"></div><div>Наслаждение живёт в промежутке между нехваткой и наполнением; наполненное желание исчезает.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Желания растут, и переход между ступенями качественный.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Желания не под контролем человека и приходят от окружения.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Действуют на желание только наслаждение и страдание; уменьшенное желание меньше ранит.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Проигрыш весит больше выигрыша; полученное быстро становится нормой.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Само желание нейтрально — эгоизмом называется намерение, а не желание.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Чувства первичны, разум вторичен.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Одно желание не подчиняется этим свойствам, потому что не наполняется получением, — оно и привело вас сюда.</div></div>
</div>
<div class="kab-quote kab-quote--purple">На следующей неделе — <b>восприятие реальности</b>. Если всё, что человек чувствует, зависит от устройства его желаний, встаёт следующий вопрос: что он вообще видит вокруг себя?</div>
</div>' WHERE id=2466;

-- 2467 · «Архив урока 2» (скрыта; пометка как есть)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<ul class="kab-list">
<li><b>Запись вчерашнего вебинара.</b> Появляется на следующий день после трансляции.</li>
<li><b>Лекция «Развитие желаний» целиком.</b> Тот же материал в лекционном изложении.</li>
<li><b>Ответы на вопросы по теме урока.</b></li>
</ul>
<div style="border:1px dashed #b8a06a;background:#faf7f0;padding:12px 16px;margin:16px 0 0;border-radius:6px">
<p style="margin:0"><strong>Редакционная пометка.</strong> Ссылки подставляются после первой трансляции потока. До этого страница скрыта от студентов.</p></div>
</div>' WHERE id=2467;

-- 2468 · «Ещё про желания — беседы и клипы»
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<p>Четыре записи из архива, каждая про одно из свойств этого урока. Смотреть можно в любом порядке и в любое время.</p>
<div class="kab-card">
<div class="kab-row"><div class="kab-dot kab-dot--purple"></div><div><a href="https://kabbalahmedia.info/ru/lessons/cu/DqVYrQWV" target="_blank" rel="noreferrer" class="kab-link">Улыбающаяся депрессия</a> — 10 минут. «Может быть всё, но я чувствую себя пустым.»</div></div>
<div class="kab-row"><div class="kab-dot kab-dot--purple"></div><div><a href="https://kabbalahmedia.info/ru/lessons/cu/YqSkYVIh" target="_blank" rel="noreferrer" class="kab-link">Не в деньгах счастье</a> — про то, что счастье держится только над нехваткой.</div></div>
<div class="kab-row"><div class="kab-dot kab-dot--purple"></div><div><a href="https://kabbalahmedia.info/ru/lessons/cu/Tqj0YtQi" target="_blank" rel="noreferrer" class="kab-link">Почему мы любим приобретать</a> — откуда берётся «моё».</div></div>
<div class="kab-row"><div class="kab-dot kab-dot--purple"></div><div><a href="https://kabbalahmedia.info/ru/lessons/cu/hAhjSri2" target="_blank" rel="noreferrer" class="kab-link">Как правильно использовать злость, грусть и страх</a> — 9 минут; начинается с прямого примера роста желаний.</div></div>
</div>
</div>' WHERE id=2468;

-- 2470 · «Резюме урока 1» (подсекция 🟢 Основное)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<div class="kab-card">
<div class="kab-row"><div class="kab-dot"></div><div>Каббала — наука о получении, а не о тайнах.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Две силы: Творец — сила отдачи, творение — желание получать. Больше ничего нет.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Раскрытие происходит <b>в этом мире, при жизни</b>.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Вниз творение шло без своего участия; вверх человек идёт только сам.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Наполненное желание исчезает — отсюда лестница желаний и её верхняя ступень, вопрос о смысле.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>В книгах записано только пережитое; там, где заканчивается постижение, заканчивается текст.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Закрытость держалась на отсутствии вопроса, а не на охране тайны.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Условие одно — вопрос. Всё остальное значения не имеет.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Метод не чинит материальную жизнь напрямую; он меняет того, кто её проживает.</div></div>
</div>
<div class="kab-quote kab-quote--purple">На следующей неделе — <b>развитие желаний</b>. Пирамиду, которую вы видели снаружи, как историю человечества, вы посмотрите изнутри.</div>
</div>' WHERE id=2470;

-- 2471 · «Запись вебинара 1 (архив)» (подсекция ВЕБИНАР; пометка как есть)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<p>Здесь собирается всё, что относится к теме недели, но не входит в ядро.</p>
<ul class="kab-list">
<li><b>Запись вчерашних вебинаров.</b></li>
<li><b>Ответы на вопросы прошлой недели.</b></li>
</ul>
<div style="border:1px dashed #b8a06a;background:#faf7f0;padding:12px 16px;margin:16px 0 0;border-radius:6px">
<p style="margin:0"><strong>Редакционная пометка.</strong> Ссылки подставляются после первой трансляции потока и после решения по пересъёмке лекции. До этого страница скрыта от студентов.</p>
</div>
</div>' WHERE id=2471;

-- 2472 · «📓 Мой Дневник» (секция 0)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<div class="kab-quote">Заведите <b>Дневник</b> (бумажный или электронный) и ведите его во время прохождения курса.</div>
<p>Примерный формат:</p>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Для начала сформулируйте вопрос, на который хотите получить ответ за пятнадцать недель.</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Описывайте свои впечатления и состояние на каждой учебной неделе.</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Записывайте ваши мысли, в которых вы вынесли что-то новое или неожиданное, подтверждение ваших догадок и ощущений.</div></div>
</div>
<div class="kab-note">Заметки в процессе курса помогут вам лучше оценить ваше продвижение.</div>
</div>' WHERE id=2472;

-- 2473 · «Тематические дни» (подсекция 💠 Факультативное)
UPDATE mdl_page SET timemodified=UNIX_TIMESTAMP(), content='<div class="kab-page">
<p><b>В течение учебной недели</b> проходят мероприятия в рамках учебной программы в Telegram (см. <a href="https://edu.kabacademy.com/calendar/view.php?view=upcoming&amp;course=236" target="_blank" rel="noopener" class="kab-link">календарь курса</a>).</p>
<p>Подключайтесь в <a href="https://t.me/makosen2026" target="_blank" rel="noopener" class="kab-link">Telegram-канал</a> и следите за сообщениями!</p>
<div class="kab-kicker">Вас ждут тематические дни</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-dot kab-dot--purple"></div><div>день книги – в субботу</div></div>
<div class="kab-row"><div class="kab-dot kab-dot--purple"></div><div>день кино – в воскресенье</div></div>
<div class="kab-row"><div class="kab-dot kab-dot--purple"></div><div>день экскурсий – в пятницу (со 2-ой недели)</div></div>
</div>
<div class="kab-note">Пост с тематического дня публикуется в 8:00 по изр. вр. (GMT+3).</div>
<p style="margin-top:10px">Участвовать и делиться впечатлениями можно в удобное время в течение всей недели.</p>
</div>' WHERE id=2473;

-- ============================ ФОРУМЫ ========================================

-- 3890 · «Объявления»
UPDATE mdl_forum SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text">
<div class="kab-quote">Главные новости и объявления</div>
</div>' WHERE id=3890;

-- 3891 · «Отклик недели — один вопрос на выбор» (урок 1)
UPDATE mdl_forum SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text">
<div class="kab-quote">Напишите своими словами ответ на любой из трёх вопросов. Один, не все три.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Какое из четырёх «не является» было для вас самым неожиданным — и почему?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Вспомните желание, которого вы когда-то очень сильно хотели и добились. Что осталось от того наслаждения через полгода?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Что из третьей главы вы бы захотели проверить первым — и как вы представляете себе такую проверку?</div></div>
</div>
<div class="kab-note">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
</div>' WHERE id=3891;

-- 3892 · «Отклик недели — один вопрос на выбор» (урок 2)
UPDATE mdl_forum SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text">
<div class="kab-quote">Напишите своими словами ответ на любой из трёх вопросов. Один, не все три.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Вспомните желание, которого очень хотели и добились. Что осталось от того наслаждения через полгода?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Найдите в своей неделе случай, где сработало одно из этих свойств. Какое и как?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Какое из свойств объяснило вам то, что вы объясняли иначе? Одной фразы достаточно.</div></div>
</div>
<div class="kab-note">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
<div class="kab-note">И если вы завели страницу «мой вопрос» на прошлой неделе — допишите, сдвинулось ли в нём что-нибудь.</div>
</div>' WHERE id=3892;

-- 3893 · «Отклик недели 1: вопрос на выбор» (🟢 Основное)
UPDATE mdl_forum SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text">
<div class="kab-quote">Напишите своими словами ответ на любой из трёх вопросов. Один, не все три.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Какое из четырёх «не является» было для вас самым неожиданным — и почему?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Вспомните желание, которого вы когда-то очень сильно хотели и добились. Что осталось от того наслаждения через полгода?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Что из третьей главы вы бы захотели проверить первым — и как вы представляете себе такую проверку?</div></div>
</div>
<div class="kab-note">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
</div>' WHERE id=3893;

-- ============================ ТЕСТЫ =========================================

UPDATE mdl_quiz SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text">
<div class="kab-quote">Шесть вопросов. Отвечайте по памяти, не возвращаясь к тексту. Попыток столько, сколько нужно; мы ничего не запираем и разбор даём на каждый вариант ответа — в том числе на неверный.</div>
</div>' WHERE id IN (1232,1233,1234);

-- ============================ FEEDBACK ======================================

UPDATE mdl_feedback SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text">
<div class="kab-quote">Остался вопрос после урока — напишите его здесь. Со следующей недели мы начинаем вебинар с разбора вопросов, присланных за прошлую неделю.</div>
</div>' WHERE id IN (31,33);

UPDATE mdl_feedback SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text">
<div class="kab-quote">Остался вопрос после урока — напишите его здесь. Вебинар следующей недели мы начинаем с разбора вопросов, присланных за эту.</div>
</div>' WHERE id=32;

-- ============================ ASSIGN ========================================

UPDATE mdl_assign SET timemodified=UNIX_TIMESTAMP(), intro='<div class="kab-text">
<div class="kab-quote">Одна заметка, которую видите вы и мы. Сформулируйте вопрос, на который хотите получить ответ за пятнадцать недель.</div>
<p>Вернётесь к ней на пятой, десятой и пятнадцатой неделе. К концу курса по ней видно, что с вашим вопросом стало, — мы считаем это главным результатом курса.</p>
<div class="kab-note">Не оцениваем, срока не ставим, править можно сколько угодно раз.</div>
</div>' WHERE id=9;

-- ============================ КЭШ ===========================================
UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=238;

-- Контроль: нигде не должно остаться inline font-family
SELECT 'label' AS t, id FROM mdl_label WHERE course=238 AND intro LIKE '%font-family%'
UNION ALL SELECT 'page', id FROM mdl_page WHERE course=238 AND content LIKE '%font-family%'
UNION ALL SELECT 'forum', id FROM mdl_forum WHERE course=238 AND intro LIKE '%font-family%'
UNION ALL SELECT 'quiz', id FROM mdl_quiz WHERE course=238 AND intro LIKE '%font-family%'
UNION ALL SELECT 'feedback', id FROM mdl_feedback WHERE course=238 AND intro LIKE '%font-family%'
UNION ALL SELECT 'assign', id FROM mdl_assign WHERE course=238 AND intro LIKE '%font-family%'
UNION ALL SELECT 'section', id FROM mdl_course_sections WHERE course=238 AND summary LIKE '%font-family%';
SELECT 'kab-classed' AS what,
 (SELECT COUNT(*) FROM mdl_label WHERE course=238 AND intro LIKE '%kab-%') AS labels,
 (SELECT COUNT(*) FROM mdl_page WHERE course=238 AND content LIKE '%kab-page%') AS pages,
 (SELECT COUNT(*) FROM mdl_forum WHERE course=238 AND intro LIKE '%kab-%') AS forums,
 (SELECT COUNT(*) FROM mdl_quiz WHERE course=238 AND intro LIKE '%kab-%') AS quizzes,
 (SELECT COUNT(*) FROM mdl_feedback WHERE course=238 AND intro LIKE '%kab-%') AS feedbacks,
 (SELECT COUNT(*) FROM mdl_assign WHERE course=238 AND intro LIKE '%kab-%') AS assigns;
