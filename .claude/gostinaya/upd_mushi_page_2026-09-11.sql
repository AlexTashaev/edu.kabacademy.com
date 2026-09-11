-- Гостиная · Муши (курс 236, lesson 1954, страница 17872) — правки 11.09.2026:
-- имя «Михаил»/«Муши» → «Михаэль» (полное имя персонажа; «для своих Муши» в приветствии оставлено),
-- блок вебинара → три региональных слота 8:00 · 17:00 · 20:00 по Израилю (как в гостиной
-- Михаэля Лайтмана 17875, без «совпадает с московским»), кнопка календаря с новым описанием,
-- ссылка на бота в «…у меня свой вопрос», WhatsApp-бот рядом с Telegram.
-- Канал t.me/osen2026MAK и бот /kab/tgbot.php сверены с 17875 — верные.
-- Применять на web-18: /tmp/kab_moodle_sql2.sh "$(cat upd_mushi_page_2026-09-11.sql)"
-- Откат: UPDATE mdl_lesson_pages p JOIN _kab_bk20260911_gost_mushi b ON b.pageid=p.id SET p.contents=b.contents WHERE p.id=17872;
START TRANSACTION;
CREATE TABLE IF NOT EXISTS _kab_bk20260911_gost_mushi (
  id INT AUTO_INCREMENT PRIMARY KEY, pageid BIGINT, ts INT, contents LONGTEXT
) DEFAULT CHARSET=utf8mb4;
INSERT INTO _kab_bk20260911_gost_mushi (pageid, ts, contents)
  SELECT id, UNIX_TIMESTAMP(), contents FROM mdl_lesson_pages WHERE id = 17872;
UPDATE mdl_lesson_pages SET contents = '<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">

<!-- ============ 1 · ВСТРЕЧА ============ -->
<div style="display:flex;align-items:center;gap:14px;margin:4px 0 20px">
  <div style="width:58px;height:58px;border-radius:50%;flex:none;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:21px;background:linear-gradient(135deg,#52B0D8 0%,#3C5D90 55%,#A42BB9 100%);box-shadow:0 8px 18px -8px rgba(60,93,144,.6)">М</div>
  <div>
    <div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px">Михаэль</div>
    <div style="font-size:11px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:#52B0D8;margin-top:3px">тот самый, из ролика, по которому вы пришли</div>
  </div>
</div>

<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:18px 20px;margin:0 0 22px;font-size:16.5px;line-height:1.65;color:#222">
  Ну вот, вы внутри. Меня зовут Михаэль — для своих Муши, тот самый, из ролика. Знаете, что меня самого зацепило когда-то? Не обещания и не тайны. <b style="color:#3C5D90">Подход:</b> без веры на слово, только проверка на себе — как в науке. Я всегда был далёк от мистики, а вопросы о смысле никуда не девались.<br><br>
  Держите вводную — пара минут, одно видео. Ничего делать не надо, просто послушайте.
</div>

<!-- ============ 2 · ВИДЕО ============ -->
<div style="position:relative;padding-top:56.25%;border-radius:10px;overflow:hidden;margin:0 0 16px;background:#00293D;box-shadow:0 12px 28px -14px rgba(0,41,61,.5)">
  <iframe src="https://www.youtube.com/embed/mJBJfYjFL4s" title="Вводная" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" allowfullscreen></iframe>
</div>

<div style="font-size:16.5px;line-height:1.65;color:#222;margin:0 0 30px">
  Вот с этого вопроса всё и начинается. Дальше — <b style="color:#3C5D90">15 недель, 15 шагов</b>, каждый строится на предыдущем. И не в одиночку: рядом группа и наставник — я, кстати, тоже преподаю на этом курсе.
</div>

<!-- ============ 3 · МАЛЕНЬКАЯ ПОБЕДА: вопрос → голос ============ -->
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 8px">Твой первый шаг</div>
<div style="font-size:14px;color:#7A7A7A;margin:0 0 14px;line-height:1.5">Наверняка что-то уже вертится в голове. Выберите свой вопрос — отвечу голосом (нажмите, чтобы раскрыть):</div>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Я далёк от мистики. Это точно про меня?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Тогда мы с вами похожи. Я всегда был далёк от мистики — а вопросы о смысле существования и о том, как управляется мир, никуда не уходили. Здесь никто не просит верить: только научный, исследовательский подход. Именно это меня когда-то и купило.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 1 · «Суть науки каббала»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Михаэля — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/mushi/mushi-a3-mistika.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Я всегда был далёк от мистики, но вопросы о смысле существования и о том, как управляется этот мир, меня всегда интересовали»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">А ответы вообще есть?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Есть. Более 30 лет назад я нашёл этот путь — глубокий, серьёзный — и нашёл ответы на все вопросы, которые себе задавал. Готовые ответы я вам не выдам: чужие не работают, их надо получить самому. Но метод, как к ним прийти, — вот он, с первой недели.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 1, дальше — весь путь</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Михаэля — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/mushi/mushi-a2-otvety.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Более 30 лет назад я нашёл этот путь — путь глубокий, серьёзный. Я нашёл ответы на все вопросы, которые я себе задавал»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Верить на слово я не умею</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    И не надо. Без веры на слово — только научный, исследовательский подход: берёте, проверяете на себе, смотрите, что меняется. Это единственное, что здесь считается доказательством. Как это делается на практике, разберём на первых же занятиях.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 6 · «Единственность Творца»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Михаэля — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/mushi/mushi-a1-podhod.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Без веры на слово — только научный исследовательский подход. Это то, что купило меня»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Почему мне так плохо — и что с этим делать?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Как устроен мир, по каким законам он действует, как найти в нём своё место — и почему мы страдаем и как от этого избавиться: это не разные вопросы, а один. Много лет назад мне помогло ровно то, что я предлагаю вам. Начнём с устройства желаний — со 2-й недели.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 2 · «Развитие желаний»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Михаэля — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/mushi/mushi-a4-stradaete.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«…понять, почему вы страдаете, как избавиться от этих страданий, — могу предложить вам то, что помогло мне много лет назад»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Я один — близкие меня не понимают</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Знакомо. Поэтому наш курс — не собрание видеороликов. Это сообщество единомышленников, живое общение с преподавателями и постоянная поддержка. Ваша группа — люди с теми же вопросами, что и у вас. А почему одному не идётся, разберём на 8-й неделе.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 8 · «Свобода выбора и окружение»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Михаэля — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/mushi/mushi-a5-community.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Наш курс — не просто собрание видеороликов. Это комьюнити единомышленников, живое общение с опытными преподавателями и постоянная живая поддержка»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">А если не пойму?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Если вы дослушали ролик до конца — я практически уверен, что это для вас. Понимание здесь не даётся сразу, и это нормально: своим становится то, к чему пришёл сам. Не спешите. Просто сделайте первый шаг — я вас жду.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ метод: понимание приходит усилием</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Михаэля — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/mushi/mushi-a6-doslushali.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Если вы дослушали этот ролик до конца — я практически уверен, что это для вас. Искренне жду вас на нашем новом курсе»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:14px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#7A7A7A;font-style:italic">…у меня свой вопрос</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Отлично — задайте его наставнику в группе или <a href="https://edu.kabacademy.com/kab/tgbot.php" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">напишите в бот</a>: там живые люди, отвечают лучше любой инструкции. А пока сделаем следующий шаг вместе.
  </div>
</details>

<div style="display:flex;align-items:flex-start;gap:12px;background:#EFF7FA;border-radius:10px;padding:14px 16px;margin:0 0 30px">
  <div style="flex:none;width:24px;height:24px;border-radius:50%;background:#52B0D8;color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;margin-top:1px">✓</div>
  <div style="font-size:15px;line-height:1.6;color:#222">
    Выбрали и послушали? Всё — <b style="color:#3C5D90">первый шаг сделан</b>: у вас появился настоящий вопрос. Здесь всё так и устроено: вопрос → шаг → новый вопрос.
  </div>
</div>

<!-- ============ 4 · ПОДАРОК: карточка конспекта ============ -->
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 10px">Забери с собой</div>
<div style="border-radius:12px;padding:28px 28px 22px;margin:0 0 8px;background:linear-gradient(120deg,#3C5D90 0%,#5A6DB0 45%,#A42BB9 100%);box-shadow:0 14px 30px -14px rgba(60,93,144,.6)">
  <div style="font-weight:500;font-size:20px;line-height:1.5;color:#fff;letter-spacing:-.2px">«Без веры на слово.<br>Только проверка на себе — как в науке.»</div>
  <div style="margin-top:16px;font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:#C4E6FF;font-weight:600">Михаэль · МАК · поток Осень-2026</div>
</div>
<div style="font-size:13px;color:#7A7A7A;margin:0 0 30px;line-height:1.5">Первая карточка вашего конспекта. Сохраните — или перешлите тому, кто тоже задаёт вопросы.</div>

<!-- ============ 5 · СОЦДОК: Ира ============ -->
<div style="display:flex;align-items:flex-start;gap:12px;background:#fff;border:1px solid #D9E8F2;border-radius:10px;padding:14px 16px;margin-bottom:30px">
  <div style="width:36px;height:36px;flex:none;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;background:linear-gradient(135deg,#A42BB9,#3C5D90)">И</div>
  <div style="font-size:15.5px;line-height:1.6;color:#222">
    <span style="font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:#7A7A7A">Ира, ваша однокурсница</span><br>
    Я думала, я одна такая, кто про это спрашивает. А тут вся группа с теми же вопросами. Вот это и держит.
  </div>
</div>

<!-- ============ 6 · ИНТРИГА + CTA ВЕБИНАР ============ -->
<div style="background:#F4F9FD;border-left:3px solid #A42BB9;border-radius:0 10px 10px 0;padding:18px 20px;margin:0 0 16px;font-size:16.5px;line-height:1.65;color:#222">
  И один вопрос я специально оставил без ответа — даже голосом: <b style="color:#3C5D90">если мир управляется законами — можно ли на них влиять, а не только подчиняться?</b><br><br>
  Об этом надо говорить вживую. Разберём на первом занятии — 1 октября, в четверг. Я там буду — приходите.
</div>

<div style="border:1px solid #D9E8F2;border-radius:12px;overflow:hidden;margin:0 0 30px;background:#fff;box-shadow:0 12px 28px -18px rgba(60,93,144,.45)">
  <div style="background:linear-gradient(90deg,#52B0D8 0%,#3C5D90 50%,#A42BB9 100%);padding:11px 20px;font-size:11px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#fff">Первое занятие потока</div>
  <div style="padding:22px 22px 20px;text-align:center">
    <!-- Вводный вебинар потока Осень-2026: mod_zoom cmid 13171; занятие идёт в трёх региональных слотах (по Израилю) — как в гостиной Михаэля Лайтмана 17875 -->
    <div style="display:flex;align-items:baseline;justify-content:center;gap:14px;flex-wrap:wrap">
      <div style="font-weight:700;font-size:30px;color:#3C5D90;letter-spacing:-.6px;line-height:1.1">1 октября</div>
      <div style="font-weight:600;font-size:15px;color:#7A7A7A;letter-spacing:.06em;text-transform:uppercase">четверг</div>
    </div>
    <div style="font-size:13px;color:#7A7A7A;margin-top:8px;line-height:1.5">Zoom · вживую, не запись</div>
    <div style="font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#7A7A7A;margin-top:18px;font-weight:600">три трансляции (по Израилю)</div>
    <div style="margin-top:10px"><span style="display:inline-block;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:10px;padding:8px 16px;font-weight:700;font-size:20px;color:#3C5D90;margin:0 8px 8px 0">8:00</span><span style="display:inline-block;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:10px;padding:8px 16px;font-weight:700;font-size:20px;color:#3C5D90;margin:0 8px 8px 0">17:00</span><span style="display:inline-block;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:10px;padding:8px 16px;font-weight:700;font-size:20px;color:#3C5D90;margin:0 8px 8px 0">20:00</span></div>
    <div style="font-size:15.5px;color:#222;line-height:1.5;margin-top:6px">Где бы вы ни были — найдётся удобное время. <b style="color:#3C5D90">Выберите своё.</b></div>
    <div style="font-size:13px;color:#7A7A7A;line-height:1.55;margin-top:8px">Обычно выбирают: <b>8:00</b> Америка, <b>17:00</b> Азия и Дальний Восток, <b>20:00</b> Израиль и Европа.</div>
    <div style="margin-top:18px;display:flex;justify-content:center;gap:10px;flex-wrap:wrap">
      <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&amp;text=%D0%9F%D0%B5%D1%80%D0%B2%D0%BE%D0%B5+%D0%B7%D0%B0%D0%BD%D1%8F%D1%82%D0%B8%D0%B5+%D0%9C%D0%90%D0%9A+%C2%B7+%D0%9E%D1%81%D0%B5%D0%BD%D1%8C-2026&amp;dates=20261001T170000Z%2F20261001T180000Z&amp;details=%D0%92%D1%85%D0%BE%D0%B4+%D0%B2+Zoom+%E2%80%94+%D0%B8%D0%B7+%D0%BA%D1%83%D1%80%D1%81%D0%B0+%D0%BD%D0%B0+edu.kabacademy.com%3A+https%3A%2F%2Fedu.kabacademy.com%2Fmod%2Fzoom%2Fview.php%3Fid%3D13171+%28%D0%BE%D1%82%D0%BA%D1%80%D0%BE%D0%B5%D1%82%D1%81%D1%8F+%D0%B7%D0%B0+15+%D0%BC%D0%B8%D0%BD%D1%83%D1%82+%D0%B4%D0%BE+%D0%BD%D0%B0%D1%87%D0%B0%D0%BB%D0%B0%29.+%D0%A1%D0%BB%D0%BE%D1%82%D1%8B+%D0%BF%D0%BE+%D0%98%D0%B7%D1%80%D0%B0%D0%B8%D0%BB%D1%8E%3A+8%3A00+%C2%B7+17%3A00+%C2%B7+20%3A00.&amp;location=Zoom+%C2%B7+edu.kabacademy.com&amp;ctz=Asia%2FJerusalem" class="kab-cta" target="_blank" rel="noopener" style="display:inline-block;background:#A42BB9;color:#fff;font-weight:600;font-size:14px;padding:13px 30px;border-radius:8px;text-decoration:none;box-shadow:0 8px 18px -8px rgba(164,43,185,.7)">Добавить в календарь</a>
      <a href="https://edu.kabacademy.com/mod/zoom/view.php?id=13171" class="kab-cta" style="display:inline-block;background:#fff;color:#3C5D90;font-weight:600;font-size:14px;padding:12px 24px;border:1.5px solid #52B0D8;border-radius:8px;text-decoration:none">Вход в Zoom →</a>
    </div>
    <div style="font-size:12.5px;color:#7A7A7A;margin-top:12px;line-height:1.5">Кнопка «Вход в Zoom» откроется за 15 минут до начала — прямо здесь, в курсе. Напоминание пришлём на почту и в канал.</div>
    <div style="font-size:13px;color:#7A7A7A;margin-top:12px;line-height:1.5;border-top:1px solid #EFF7FA;padding-top:12px">Дальше вебинар будет открывать каждую учебную неделю — это главная точка встречи.</div>
  </div>
</div>

<!-- ============ 7 · СВОИ: канал + бот ============ -->
<div style="font-size:16.5px;line-height:1.65;color:#222;margin:0 0 26px">
  И самое главное я уже сказал: это не собрание видеороликов — это <b style="color:#3C5D90">люди</b>. Знакомьтесь, это ваши.
  Все они в <a href="https://t.me/osen2026MAK" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">канале потока</a> — там жизнь курса, расписание и те самые единомышленники.
  А если что-то не открывается, потерялся пароль или просто хочется, чтобы вас услышали, — <a href="https://edu.kabacademy.com/kab/tgbot.php" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">напишите нам в бот</a> (или <a href="https://wa.me/972557755702?text=%D0%BA%D1%83%D1%80%D1%81%20%D0%BA%D0%BB%D0%B0%D1%81%D1%81%D0%B8%D1%87%D0%B5%D1%81%D0%BA%D0%B0%D1%8F%20%D0%BA%D0%B0%D0%B1%D0%B1%D0%B0%D0%BB%D0%B0" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">в WhatsApp</a>): там отвечает живой человек, не робот. Я, кстати, тоже там.
</div>

<!-- ============ 8 · ФУТЕР ============ -->
<div style="border-top:1px solid #D9E8F2;padding-top:18px;font-size:13px;color:#7A7A7A;line-height:1.6">
  Эта страница теперь есть в вашем курсе — раздел «Вводный урок», самая первая. Возвращайтесь, когда захочется: она никуда не денется.<br>
  <!-- TODO: подставить ссылку на справочник курса Осень-2026 -->
  Как здесь всё устроено (кабинет · вебинары · форум · Zoom) — в справочнике курса. Откроешь, когда понадобится.
</div>

</div>

', timemodified = UNIX_TIMESTAMP() WHERE id = 17872;
COMMIT;
SELECT id, LENGTH(contents) len, timemodified FROM mdl_lesson_pages WHERE id = 17872;
