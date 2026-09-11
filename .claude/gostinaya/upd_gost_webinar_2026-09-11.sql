-- Гостиные курса 236 — правки 11.09.2026 (вслед за Муши 17872, скрипт upd_mushi_page_2026-09-11.sql):
-- 17871 Эдик, 17873 Гавриэль, 17874 Сеня, 17876 Ицик: блок вебинара → три региональных слота
-- 8:00 · 17:00 · 20:00 по Израилю (как в гостиной Михаэля Лайтмана 17875, без «совпадает с московским»),
-- «на первом вебинаре» → «на первом занятии», WhatsApp-бот рядом с Telegram, ссылка на бота
-- в «…у меня свой вопрос». Канал t.me/osen2026MAK и бот /kab/tgbot.php сверены — верные.
-- 17872 Муши: из приветствия убрано «— для своих Муши».
-- Применять на web-18: ssh web-18 'cat > /tmp/x.sql' < этот_файл; /tmp/kab_moodle_sql2.sh "$(cat /tmp/x.sql)"
-- Откат: UPDATE mdl_lesson_pages p JOIN _kab_bk20260911_gost_pages b ON b.pageid=p.id SET p.contents=b.contents;
START TRANSACTION;
CREATE TABLE IF NOT EXISTS _kab_bk20260911_gost_pages (
  id INT AUTO_INCREMENT PRIMARY KEY, pageid BIGINT, ts INT, contents LONGTEXT
) DEFAULT CHARSET=utf8mb4;
INSERT INTO _kab_bk20260911_gost_pages (pageid, ts, contents)
  SELECT id, UNIX_TIMESTAMP(), contents FROM mdl_lesson_pages WHERE id IN (17871,17872,17873,17874,17876);
UPDATE mdl_lesson_pages SET contents = '<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">

<!-- ============ 1 · ВСТРЕЧА ============ -->
<div style="display:flex;align-items:center;gap:14px;margin:4px 0 20px">
  <div style="width:58px;height:58px;border-radius:50%;flex:none;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:21px;background:linear-gradient(135deg,#52B0D8 0%,#3C5D90 55%,#A42BB9 100%);box-shadow:0 8px 18px -8px rgba(60,93,144,.6)">Э</div>
  <div>
    <div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px">Эдик</div>
    <div style="font-size:11px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:#52B0D8;margin-top:3px">тот самый, из ролика, по которому ты пришёл</div>
  </div>
</div>

<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:18px 20px;margin:0 0 22px;font-size:16.5px;line-height:1.65;color:#222">
  Ну вот, ты внутри. Знаешь, что меня самого больше всего удивило, когда я пришёл? Не книги. <b style="color:#3C5D90">Люди.</b> Тысячи таких же — а я-то был уверен, что один такой странный с этими вопросами.<br><br>
  Держи вводную — пара минут, одно видео. Ничего делать не надо, просто послушай.
</div>

<!-- ============ 2 · ВИДЕО ============ -->
<div style="position:relative;padding-top:56.25%;border-radius:10px;overflow:hidden;margin:0 0 16px;background:#00293D;box-shadow:0 12px 28px -14px rgba(0,41,61,.5)">
  <iframe src="https://www.youtube.com/embed/mJBJfYjFL4s" title="Вводная" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" allowfullscreen></iframe>
</div>

<div style="font-size:16.5px;line-height:1.65;color:#222;margin:0 0 30px">
  Вот с этого вопроса всё и начинается. Дальше — <b style="color:#3C5D90">15 недель, 15 шагов</b>, каждый строится на предыдущем. И не в одиночку: рядом группа и наставник.
</div>

<!-- ============ 3 · МАЛЕНЬКАЯ ПОБЕДА: вопрос → голос ============ -->
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 8px">Твой первый шаг</div>
<div style="font-size:14px;color:#7A7A7A;margin:0 0 14px;line-height:1.5">Наверняка что-то вертится в голове. Выбери свой вопрос — я отвечу голосом (нажми, чтобы раскрыть):</div>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Ничего не знаю — потяну?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Слушай, я сам пришёл с нуля и думал, что я тут самый странный. Каббала не про то, чтобы много знать — про то, чтобы наконец задать нормальный вопрос. Как это работает, ты почувствуешь сам к первой-третьей неделе. И ты не один: рядом группа и наставник.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 1 · «Суть науки каббала»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Эдика — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/edik/edik-a1-potyanu.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Мне казалось, что я какой-то странный: задаю вопросы о жизни, о смысле… мне не хватало»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Для чего я вообще живу?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    О, это ровно мой вопрос. Помнишь, в ролике — «живу и живу, ну корова»? Я не мог с этим согласиться. Готового ответа не дам, он всё равно будет чужой. Вопрос ты уже услышал — а по-настоящему он раскроется в пути.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 1, дальше — весь путь</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Эдика — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/edik/edik-a2-zachem.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Для чего ты живёшь? — Живу и живу. Ну, корова. Я не мог с этим согласиться»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Это не секта? Не эзотерика?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Понимаю, сам был скептик. Никакой мистики: это метод, и ты проверяешь его на себе. Боишься слова «Бог» — назови «природа». Работает или нет — увидишь на своём опыте, с первых занятий.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 6 · неделя 6</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Эдика — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/edik/edik-a3-sekta.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Я пальцем не пошевелю впустую — а тут природа такое замутила впустую? Боишься слова Бог — назови природа»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Кто я? Душа? Творец?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Вот прямо это: человек, душа, творец и весь мир вокруг — как они взаимодействуют и, главное, <i>для чего</i>. Этим займёмся на 7-й неделе, «Душа и тело». Спойлить не буду — но чтобы это не осталось словами, нужен первый шаг.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 7 · неделя 7</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Эдика — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/edik/edik-a4-dusha.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Человек, душа, творец, я и весь мир: как взаимодействуют и — самое важное — для чего»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Мои близкие меня не понимают</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Знакомо до боли. В автобусе к тебе никто не подойдёт спросить, зачем ты живёшь. Но такие есть — тысячи, просто ты их раньше не встречал. Твоя группа — это они. А почему одному не идётся, разберём на 8-й неделе.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 8 · «Свобода выбора и окружение»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Эдика — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/edik/edik-a5-blizkie.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Я думал, только я такой… В автобусе никто не подойдёт: а ты знаешь, зачем ты живёшь?»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">А если не пойму?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Ты дослушал мой ролик до конца — значит, зацепило. Этого достаточно. Не спеши понять всё сразу: то, что даётся мгновенно, не ценится; своим становится то, к чему пришёл сам. Просто сделай первый шаг.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ метод: понимание приходит усилием</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Эдика — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/edik/edik-a6-nepoymu.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Если вы дослушали — возможно, вам это подойдёт… Вдруг перед тобой духовная физика»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:14px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#7A7A7A;font-style:italic">…у меня свой вопрос</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Отлично — задай его наставнику в группе или <a href="https://edu.kabacademy.com/kab/tgbot.php" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">напиши в бот</a>: там живые люди, отвечают лучше любой инструкции. А пока сделаем следующий шаг вместе.
  </div>
</details>

<div style="display:flex;align-items:flex-start;gap:12px;background:#EFF7FA;border-radius:10px;padding:14px 16px;margin:0 0 30px">
  <div style="flex:none;width:24px;height:24px;border-radius:50%;background:#52B0D8;color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;margin-top:1px">✓</div>
  <div style="font-size:15px;line-height:1.6;color:#222">
    Выбрал и послушал? Всё — <b style="color:#3C5D90">первый шаг сделан</b>: у тебя появился настоящий вопрос. Здесь всё так и устроено: вопрос → шаг → новый вопрос.
  </div>
</div>

<!-- ============ 4 · ПОДАРОК: карточка конспекта ============ -->
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 10px">Забери с собой</div>
<div style="border-radius:12px;padding:28px 28px 22px;margin:0 0 8px;background:linear-gradient(120deg,#3C5D90 0%,#5A6DB0 45%,#A42BB9 100%);box-shadow:0 14px 30px -14px rgba(60,93,144,.6)">
  <div style="font-weight:500;font-size:20px;line-height:1.5;color:#fff;letter-spacing:-.2px">«Боишься слова „Бог“ — назови „природа“.<br>Не верь на слово — проверь на себе.»</div>
  <div style="margin-top:16px;font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:#C4E6FF;font-weight:600">Эдик · МАК · поток Осень-2026</div>
</div>
<div style="font-size:13px;color:#7A7A7A;margin:0 0 30px;line-height:1.5">Первая карточка твоего конспекта. Сохрани — или перешли тому, кто тоже задаёт вопросы.</div>

<!-- ============ 5 · СОЦДОК: Ира ============ -->
<div style="display:flex;align-items:flex-start;gap:12px;background:#fff;border:1px solid #D9E8F2;border-radius:10px;padding:14px 16px;margin-bottom:30px">
  <div style="width:36px;height:36px;flex:none;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;background:linear-gradient(135deg,#A42BB9,#3C5D90)">И</div>
  <div style="font-size:15.5px;line-height:1.6;color:#222">
    <span style="font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:#7A7A7A">Ира, твоя однокурсница</span><br>
    Я думала, я одна такая, кто про это спрашивает. А тут вся группа с теми же вопросами. Вот это и держит.
  </div>
</div>

<!-- ============ 6 · ИНТРИГА + CTA ВЕБИНАР ============ -->
<div style="background:#F4F9FD;border-left:3px solid #A42BB9;border-radius:0 10px 10px 0;padding:18px 20px;margin:0 0 16px;font-size:16.5px;line-height:1.65;color:#222">
  И один вопрос я специально оставил без ответа — даже голосом: <b style="color:#3C5D90">почему в одиночку не получается — даже у самых умных и упорных?</b><br><br>
  У меня есть что рассказать, но это надо вживую. Разберём на первом занятии — 1 октября, в четверг. Я там буду — приходи.
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
  И самое главное я тебе уже сказал: дело не в книгах — дело в <b style="color:#3C5D90">людях</b>. Знакомься, это твои.
  Все они в <a href="https://t.me/osen2026MAK" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">канале потока</a> — там жизнь курса, расписание и те самые тысячи таких же, как ты.
  А если что-то не открывается, потерялся пароль или просто хочется, чтобы тебя услышали, — <a href="https://edu.kabacademy.com/kab/tgbot.php" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">напиши нам в бот</a> (или <a href="https://wa.me/972557755702?text=%D0%BA%D1%83%D1%80%D1%81%20%D0%BA%D0%BB%D0%B0%D1%81%D1%81%D0%B8%D1%87%D0%B5%D1%81%D0%BA%D0%B0%D1%8F%20%D0%BA%D0%B0%D0%B1%D0%B1%D0%B0%D0%BB%D0%B0" target="_blank" rel="noopener" style="color:#00789F;font-weight:600;border-bottom:1px solid rgba(0,120,159,.35);text-decoration:none">в WhatsApp</a>): там отвечает живой человек, не робот. Я, кстати, тоже там.
</div>

<!-- ============ 8 · ФУТЕР ============ -->
<div style="border-top:1px solid #D9E8F2;padding-top:18px;font-size:13px;color:#7A7A7A;line-height:1.6">
  Эта страница теперь есть в твоём курсе — раздел «Вводный урок», самая первая. Возвращайся, когда захочется: она никуда не денется.<br>
  <!-- TODO: подставить ссылку на справочник курса Осень-2026 -->
  Как здесь всё устроено (кабинет · вебинары · форум · Zoom) — в справочнике курса. Откроешь, когда понадобится.
</div>

</div>

', timemodified = UNIX_TIMESTAMP() WHERE id = 17871;
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
  Ну вот, вы внутри. Меня зовут Михаэль, тот самый, из ролика. Знаете, что меня самого зацепило когда-то? Не обещания и не тайны. <b style="color:#3C5D90">Подход:</b> без веры на слово, только проверка на себе — как в науке. Я всегда был далёк от мистики, а вопросы о смысле никуда не девались.<br><br>
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
UPDATE mdl_lesson_pages SET contents = '<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">

<!-- ============ 1 · ВСТРЕЧА ============ -->
<div style="display:flex;align-items:center;gap:14px;margin:4px 0 20px">
  <div style="width:58px;height:58px;border-radius:50%;flex:none;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:21px;background:linear-gradient(135deg,#52B0D8 0%,#3C5D90 55%,#A42BB9 100%);box-shadow:0 8px 18px -8px rgba(60,93,144,.6)">Г</div>
  <div>
    <div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px">Гавриэль</div>
    <div style="font-size:11px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:#52B0D8;margin-top:3px">тот самый, из ролика, по которому вы пришли</div>
  </div>
</div>

<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:18px 20px;margin:0 0 22px;font-size:16.5px;line-height:1.65;color:#222">
  Ну вот, вы внутри. Когда-то, больше 30 лет назад, меня мучили вопросы, на которые, казалось, нет ответа: для чего живёшь? Ответов и правда нет — но, как оказалось, есть <b style="color:#3C5D90">путь</b> к своим собственным. По нему я иду до сих пор — и преподаю на этом курсе.<br><br>
  Держите вводную — пара минут, одно видео. Ничего делать не надо, просто послушайте.
</div>

<!-- ============ 2 · ВИДЕО ============ -->
<div style="position:relative;padding-top:56.25%;border-radius:10px;overflow:hidden;margin:0 0 16px;background:#00293D;box-shadow:0 12px 28px -14px rgba(0,41,61,.5)">
  <iframe src="https://www.youtube.com/embed/mJBJfYjFL4s" title="Вводная" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" allowfullscreen></iframe>
</div>

<div style="font-size:16.5px;line-height:1.65;color:#222;margin:0 0 30px">
  Вот с этого вопроса всё и начинается. Дальше — <b style="color:#3C5D90">15 недель, 15 шагов</b>, каждый строится на предыдущем. И не в одиночку: рядом группа и наставник.
</div>

<!-- ============ 3 · МАЛЕНЬКАЯ ПОБЕДА: вопрос → голос ============ -->
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 8px">Твой первый шаг</div>
<div style="font-size:14px;color:#7A7A7A;margin:0 0 14px;line-height:1.5">Наверняка что-то уже вертится в голове. Выберите свой вопрос — отвечу голосом (нажмите, чтобы раскрыть):</div>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Ничего не знаю — потяну?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Потянете. Методику мы выстроили как последовательное, чёткое изучение законов — шаг за шагом, без прыжков. Знать заранее ничего не нужно: нужен только ваш вопрос. Как это устроено, почувствуете к первой-третьей неделе.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 1 · «Суть науки каббала»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Гавриэля — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/gavriel/gavriel-a1-zakony.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Мы эту методику выстроили как последовательное, чёткое изучение этих законов — законов высшего мира, законов Творца»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Для чего я вообще живу?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Тот самый вопрос, который мучил меня больше 30 лет назад. Ну как на него ответить? И действительно — готовых ответов нет. Но есть путь к вашим собственным. Вопрос вы уже услышали — раскрываться он будет в пути.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 1, дальше — весь путь</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Гавриэля — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/gavriel/gavriel-a2-dlya-chego.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Меня мучили вопросы, на которые, казалось бы, нет ответа. Ну как можно ответить, для чего живёшь? И действительно, ответов нет»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Это не секта? Не эзотерика?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Нет. Это наука о силах, которые управляют мирозданием, — и изучаем мы её по первоисточникам: книга Зоар, сочинения Ари. Живое общение, тексты, проверка на себе — никакой мистики. С первоисточниками познакомимся на 5-й неделе.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 5 · «История каббалы»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Гавриэля — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/gavriel/gavriel-a3-zoar.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Это живое общение, это изучение материалов, первоисточников — в частности книга Зоар, сочинения Ари»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Кто я? Что такое душа?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Раскрыть свою душу, узнать, как она устроена, как устроен этот мир и мир духовный — ровно этим мы и занимаемся. Спойлить не буду: этим займёмся на 7-й неделе, «Душа и тело». Но чтобы это не осталось словами, нужен первый шаг.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 7 · «Душа и тело»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Гавриэля — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/gavriel/gavriel-a4-dusha.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Есть путь, есть методика, как прийти к вашим собственным ответам… раскрыть свою душу, узнать, как она устроена, как устроен этот мир и мир духовный»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Кто управляет моей судьбой?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Что это за силы, которые управляют мною, моей судьбой? Именно этот вопрос привёл в каббалу меня. Есть сила, которая управляет всем мирозданием, всей природой — включая людей. И с ней можно научиться взаимодействовать. Об этом — с 4-й недели, «Строение мироздания».
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 4 · «Строение мироздания»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Гавриэля — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/gavriel/gavriel-a5-sily.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Что это за силы, которые управляют мною, моей судьбой? Эта сила изучается в науке каббала»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Я один — близкие меня не понимают</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Поэтому это не собрание видеороликов, а глубокое изучение себя и мироздания вместе — с сообществом единомышленников, живым общением, опытными преподавателями и постоянной поддержкой. Ваша группа — люди с теми же вопросами. А почему одному не идётся, разберём на 8-й неделе.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 8 · «Свобода выбора и окружение»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Гавриэля — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/gavriel/gavriel-a6-soobshestvo.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Это не просто собрание видеороликов, это глубокое изучение себя и мироздания с сообществом единомышленников, с живым общением»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">А если не пойму?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Если вы дослушали ролик — думаю, это для вас. Понимание здесь приходит усилием, а не сразу, и это правильно: своим становится то, к чему пришёл сам. Не спешите. Просто сделайте первый шаг — искренне надеюсь увидеть вас в курсе.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ метод: понимание приходит усилием</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Гавриэля — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/gavriel/gavriel-a7-doslushali.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Если вы дослушали сейчас — думаю, что это для вас. Искренне надеюсь увидеть вас в нашем курсе каббалы»</div></div>
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
  <div style="font-weight:500;font-size:20px;line-height:1.5;color:#fff;letter-spacing:-.2px">«Готовых ответов нет.<br>Но есть путь к своим собственным.»</div>
  <div style="margin-top:16px;font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:#C4E6FF;font-weight:600">Гавриэль · МАК · поток Осень-2026</div>
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
  И один вопрос я специально оставил без ответа — даже голосом: <b style="color:#3C5D90">если этой силой управляется всё, включая нас, — где тогда наша свобода?</b><br><br>
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
  И самое главное я уже сказал: не видеоролики — <b style="color:#3C5D90">люди</b>. Знакомьтесь, это ваши.
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

', timemodified = UNIX_TIMESTAMP() WHERE id = 17873;
UPDATE mdl_lesson_pages SET contents = '<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">

<!-- ============ 1 · ВСТРЕЧА ============ -->
<div style="display:flex;align-items:center;gap:14px;margin:4px 0 20px">
  <div style="width:58px;height:58px;border-radius:50%;flex:none;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:21px;background:linear-gradient(135deg,#52B0D8 0%,#3C5D90 55%,#A42BB9 100%);box-shadow:0 8px 18px -8px rgba(60,93,144,.6)">С</div>
  <div>
    <div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px">Сеня</div>
    <div style="font-size:11px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:#52B0D8;margin-top:3px">тот самый, из ролика, по которому вы пришли</div>
  </div>
</div>

<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:18px 20px;margin:0 0 22px;font-size:16.5px;line-height:1.65;color:#222">
  Дорогие друзья — ну вот, вы внутри. Это Сеня, тот самый, из ролика. Знаете, что меня самого держит здесь столько лет? За всеми историями, за всем написанным есть <b style="color:#3C5D90">следующий слой</b> — и ещё один, и ещё. И открывается он не в книге, а в вас. Мы пойдём как следователи и исследователи самих себя.<br><br>
  Держите вводную — пара минут, одно видео. Ничего делать не надо, просто послушайте.
</div>

<!-- ============ 2 · ВИДЕО ============ -->
<div style="position:relative;padding-top:56.25%;border-radius:10px;overflow:hidden;margin:0 0 16px;background:#00293D;box-shadow:0 12px 28px -14px rgba(0,41,61,.5)">
  <iframe src="https://www.youtube.com/embed/mJBJfYjFL4s" title="Вводная" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" allowfullscreen></iframe>
</div>

<div style="font-size:16.5px;line-height:1.65;color:#222;margin:0 0 30px">
  Вот с этого вопроса всё и начинается. Дальше — <b style="color:#3C5D90">15 недель, 15 шагов</b>, каждый строится на предыдущем. И не в одиночку: рядом группа и наставник.
</div>

<!-- ============ 3 · МАЛЕНЬКАЯ ПОБЕДА: вопрос → голос ============ -->
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 8px">Твой первый шаг</div>
<div style="font-size:14px;color:#7A7A7A;margin:0 0 14px;line-height:1.5">Наверняка что-то уже вертится в голове. Выберите свой вопрос — отвечу голосом (нажмите, чтобы раскрыть):</div>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Ничего не знаю — потяну?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Потянете — потому что знать заранее ничего не нужно. Мы идём как следователи и исследователи самих себя: помещаем в себя микроскоп и смотрим, что там происходит. Инструмент один — ваш собственный вопрос, а он у вас уже есть. Как это работает, почувствуете к первой-третьей неделе.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 1 · «Суть науки каббала»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Сени — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/senja/senja-a1-issledovateli.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Мы будем с вами идти, знаете, как следователи и исследователи себя — помещать в себя этот микроскоп и видеть в себе весь этот путь»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Для чего я вообще живу?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Пусть это звучит громко, но ответ на этот вопрос — не одна фраза. Это следующий слой за тем, что вы видите сейчас, — и ещё один, и ещё. Готовый ответ я вам не выдам, он всё равно будет чужой. Вопрос вы уже услышали — раскрываться он будет в пути, слой за слоем.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 1, дальше — весь путь</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Сени — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/senja/senja-a2-sloi.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Пусть это звучит громко, но мы очень хотим за всем этим написанным, за всеми этими историями увидеть следующий слой, ещё один слой, ещё»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Это не секта? Не эзотерика?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Нет. Никто не просит верить в истории — мы учимся читать их не как истории, а как то, что происходит в вас. Это единственный критерий: если находите это в себе — работает, если нет — не работает. Проверка на себе, с первых занятий.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 3 · «Восприятие реальности»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Сени — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/senja/senja-a3-v-vas.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Вы же сами можете объяснить истории, которые там рассказываются, — не как истории, а как то, что происходит в вас»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Кто я? Что во мне происходит?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Вот это мы и будем разбирать: весь путь — Авраам, Моше, выход из Египта — оказывается путём внутри вас. Что такое «Египет» в вас, что такое природа эгоизма в человеке и как она работает. Спойлить не буду — этим займёмся со 2-й недели, «Развитие желаний».
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 2 · «Развитие желаний»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Сени — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/senja/senja-a4-egoizm.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«…этот путь Авраама, этот путь Моше, путь народа Израиля; что такое Египет в вас, природа эгоизма в человеке, как она работает»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Я один — близкие меня не понимают</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Знакомо. Весь путь здесь — от любви к себе к любви к ближнему, и в одиночку его не пройти по определению. Поэтому рядом группа и наставник: люди с теми же вопросами, что и у вас. Про то, что такое настоящая инструкция к гармонии, — на 8-й неделе.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 8 · «Свобода выбора и окружение»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Сени — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/senja/senja-a5-lyubov.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«И каждый из нас — от любви к себе к любви к ближнему. К пониманию, что такое настоящая Тора: инструкция, как прийти к гармонии любви»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">А если не пойму?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Через десять занятий — а может быть, уже на первых — у вас будет ощущение, что вы сами можете приоткрыть следующий слой. Не спешите понять всё сразу: своим становится то, к чему пришёл сам. Просто сделайте первый шаг — и приходите, приходите.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ метод: понимание приходит усилием</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Сени — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/senja/senja-a6-sloi-pervye.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Через 10 занятий у вас будет ощущение — а может быть, на первых занятиях, — что вы уже можете приоткрыть следующий слой»</div></div>
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
  <div style="font-weight:500;font-size:20px;line-height:1.5;color:#fff;letter-spacing:-.2px">«За всеми историями — совершенно потрясающее<br>состояние каждого из нас.»</div>
  <div style="margin-top:16px;font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:#C4E6FF;font-weight:600">Сеня · МАК · поток Осень-2026</div>
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
  И один вопрос я специально оставил без ответа — даже голосом: <b style="color:#3C5D90">если весь этот путь происходит внутри — почему в одиночку его не пройти?</b><br><br>
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
  И самое главное я уже сказал: не книги — <b style="color:#3C5D90">люди</b>. Знакомьтесь, это ваши.
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

', timemodified = UNIX_TIMESTAMP() WHERE id = 17874;
UPDATE mdl_lesson_pages SET contents = '<link rel="stylesheet" href="/kab/fonts.css">
<div style="max-width:760px;margin:0 auto;font-family:Montserrat,Arial,sans-serif;font-size:16.5px;line-height:1.65;color:#222">

<!-- ============ 1 · ВСТРЕЧА ============ -->
<div style="display:flex;align-items:center;gap:14px;margin:4px 0 20px">
  <div style="width:58px;height:58px;border-radius:50%;flex:none;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:21px;background:linear-gradient(135deg,#52B0D8 0%,#3C5D90 55%,#A42BB9 100%);box-shadow:0 8px 18px -8px rgba(60,93,144,.6)">И</div>
  <div>
    <div style="font-weight:700;font-size:18px;color:#3C5D90;letter-spacing:-.2px">Ицик</div>
    <div style="font-size:11px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:#52B0D8;margin-top:3px">тот самый, из ролика, по которому вы пришли</div>
  </div>
</div>

<div style="background:#F4F9FD;border-left:3px solid #52B0D8;border-radius:0 10px 10px 0;padding:18px 20px;margin:0 0 22px;font-size:16.5px;line-height:1.65;color:#222">
  Ну вот, вы внутри. Это Ицик, тот самый, из ролика. Мы же с вами знаем: человек никогда не доволен — ему плохо, даже когда хорошо. Со мной было так же, пока я не понял одну вещь: <b style="color:#3C5D90">условия, которые нам даны, — самые наилучшие.</b> Именно ваши. Осталось научиться их читать.<br><br>
  Держите вводную — пара минут, одно видео. Ничего делать не надо, просто послушайте.
</div>

<!-- ============ 2 · ВИДЕО ============ -->
<div style="position:relative;padding-top:56.25%;border-radius:10px;overflow:hidden;margin:0 0 16px;background:#00293D;box-shadow:0 12px 28px -14px rgba(0,41,61,.5)">
  <iframe src="https://www.youtube.com/embed/mJBJfYjFL4s" title="Вводная" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" allowfullscreen></iframe>
</div>

<div style="font-size:16.5px;line-height:1.65;color:#222;margin:0 0 30px">
  Вот с этого вопроса всё и начинается. Дальше — <b style="color:#3C5D90">15 недель, 15 шагов</b>, каждый строится на предыдущем. И не в одиночку: рядом группа и наставник.
</div>

<!-- ============ 3 · МАЛЕНЬКАЯ ПОБЕДА: вопрос → голос ============ -->
<div style="font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#A42BB9;margin:0 0 8px">Твой первый шаг</div>
<div style="font-size:14px;color:#7A7A7A;margin:0 0 14px;line-height:1.5">Наверняка что-то уже вертится в голове. Выберите свой вопрос — отвечу голосом (нажмите, чтобы раскрыть):</div>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Ничего не знаю — потяну?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Потянете. Я обещаю: тот, кто действительно начнёт этим заниматься, поймёт, что ему даны самые наилучшие условия — ровно ваши, какие есть. Знать заранее ничего не нужно, нужно только начать. Как это работает, почувствуете к первой-третьей неделе.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 1 · «Суть науки каббала»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Ицика — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/itzik/itzik-a1-usloviya.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«И я обещаю: тот, кто действительно начнёт этим заниматься, поймёт, что ему даны просто самые наилучшие условия»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Почему мне всегда чего-то не хватает?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Мы же с вами знаем: человек никогда не доволен. Ему плохо, даже когда хорошо, — всегда мало того, что есть, всегда смотрим на то, что у других. Это не ваш дефект, это природа. А вот что с ней делать — этим и займёмся, со 2-й недели, «Развитие желаний».
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 2 · «Развитие желаний»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Ицика — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/itzik/itzik-a2-nedovolen.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Человек никогда не доволен. Ему всегда плохо, даже когда ему хорошо… Это наша природа: мы всегда смотрим на то, что у других»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Можно ли изменить судьбу?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Переписать судьбу можно — но начинается это неожиданно: сначала с ней согласиться. Понять, поблагодарить за неё, начать обдумывать и следовать ей. Звучит просто, а работает — как именно, посмотрим на 3-й неделе, «Восприятие реальности».
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 3 · «Восприятие реальности»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Ицика — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/itzik/itzik-a3-sudba.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Переписать судьбу нужно… сначала с ней согласиться. Понять, поблагодарить за неё и начинать обдумывать её и следовать ей»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Бог, Творец — это про религию?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Нет. Маленький ребёнок знает маму: она греет, кормит, любит — для него это и есть бог. Но он не знает, что она ещё и директор предприятия. Так и от нас скрыто, каков Творец относительно других своих дел, — а мы всё считали себя его единственным делом. Как дети. Разберём на 6-й неделе, «Единственность Творца».
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 6 · «Единственность Творца»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Ицика — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/itzik/itzik-a4-mama.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Маленький ребёнок знает свою маму: она его согревает, кормит, любит. Это для него бог. Но он не знает, что она вместе с этим, может быть, директор предприятия»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">Этот мир вообще реален?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Говорят, что нет — но говорят это те, кто смог над ним подняться. Весь наш мир настроен на получение для себя; высший мир от этого свободен — и это, на самом деле, и называется свобода. Первое, что вы почувствуете, — не «другой мир», а другой взгляд на этот. Об этом — 4-я неделя.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ Лекция 4 · «Строение мироздания»</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Ицика — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/itzik/itzik-a5-svoboda.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«А высший мир свободен от получения… там нету ради себя. Человек свободен от этого — это, на самом деле, называется свобода»</div></div>
  </div>
</details>

<details style="border:1px solid #D9E8F2;border-radius:10px;padding:13px 18px;margin-bottom:8px;background:#fff">
  <summary style="cursor:pointer;font-weight:600;font-size:15.5px;color:#3C5D90">А если не пойму?</summary>
  <div style="margin-top:12px;font-size:15.5px;line-height:1.65;color:#222">
    Пока не проснёмся — этот мир для нас совершенно реальный, и это нормально. Каббалисты говорят, что, поднимаясь, они словно очнулись от сна. Не спешите проснуться за один вечер: своим становится то, к чему пришёл сам. Просто сделайте первый шаг.
    <div style="margin-top:10px;font-size:11px;letter-spacing:.12em;color:#52B0D8;font-weight:600;text-transform:uppercase">↑ метод: понимание приходит усилием</div>
    <div style="margin-top:12px;padding:12px 14px;background:#F4F9FD;border:1px solid #D9E8F2;border-radius:8px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3C5D90;font-weight:600;margin-bottom:8px">Голос Ицика — фрагмент его ролика</div><audio controls preload="none" style="width:100%;max-width:420px" src="https://kabacademy.com/wp-content/uploads/onboarding/itzik/itzik-a6-prosnulis.mp3"></audio><div style="font-size:12.5px;color:#7A7A7A;margin-top:6px;line-height:1.45">«Когда они поднимаются в высший мир, они говорят, что как бы очнулись от спячки, проснулись. То есть наш материальный мир для них — это сон»</div></div>
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
  <div style="font-weight:500;font-size:20px;line-height:1.5;color:#fff;letter-spacing:-.2px">«Тот, кто действительно начнёт этим заниматься, поймёт:<br>ему даны самые наилучшие условия.»</div>
  <div style="margin-top:16px;font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:#C4E6FF;font-weight:600">Ицик · МАК · поток Осень-2026</div>
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
  И один вопрос я специально оставил без ответа — даже голосом: <b style="color:#3C5D90">если этот мир — сон, кто тогда тот, кто проснётся?</b><br><br>
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
  И самое главное я уже сказал: условия даны наилучшие — и главное из них — <b style="color:#3C5D90">люди</b>. Знакомьтесь, это ваши.
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

', timemodified = UNIX_TIMESTAMP() WHERE id = 17876;
COMMIT;
SELECT id, LENGTH(contents) len, timemodified FROM mdl_lesson_pages WHERE id IN (17871,17872,17873,17874,17876);
