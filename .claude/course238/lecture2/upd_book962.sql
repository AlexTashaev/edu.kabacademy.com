-- Урок 2 (book 962, cmid 13389, курс 238): схемы 4–5 вместо плейсхолдеров дизайнера.
-- Сгенерировано build.py; откат — rollback_book962.sql.
START TRANSACTION;
CREATE TABLE IF NOT EXISTS _kab_bk20260924_book962 DEFAULT CHARSET=utf8mb4 AS SELECT * FROM mdl_book_chapters WHERE bookid=962;
UPDATE mdl_book_chapters SET content='<p>У любого желания есть общие свойства. Они одинаковы для желания поесть и для желания признания, для желания ребёнка и для желания взрослого, для человека сегодня и для человечества за всю его историю.</p>
<p>Эти свойства — рабочая оптика. Многое из того, что объясняют усталостью, характером или невезением, оказывается устройством желания.</p>
<p>Оговорка сразу: <strong>свойства дают понимание, а не починку.</strong> Понятно станет, почему покупка не радует. Приёма, который заставит её радовать, здесь нет.</p>

<h4>Развитие идёт ступенями</h4>
<p>Мы не рождаемся сразу двадцатилетними. Сначала год, потом два, потом три — и каждая ступень включает в себя предыдущую.</p>
<p>В школе десять классов, а не один класс на десять лет. Не потому, что так удобнее расписанию: каждый следующий класс вбирает предыдущий и добавляет свой уровень. Пропустить нельзя — не на чем будет стоять.</p>
<p>Желания растут так же. Внутри человеческого уровня они идут по порядку: телесные — пища, секс, семья; богатство; почёт и власть; знание; и то, что обнаруживается за знанием.</p>
<p>Переход между ними <strong>качественный, а не количественный.</strong> Богатство не насыщается едой. Почёт не насыщается деньгами. Знание не насыщается властью. Сколько ни добавляй на своей ступени, следующая от этого не откроется.</p>
<div style="border:1px dashed #7fa8b8;background:#f2f8fa;padding:12px 16px;margin:20px 0;border-radius:6px">
<p style="margin:0"><strong>Клип.</strong> «То, что устраивало меня в 2017 году, в 2019 не устраивает. Я мог бы удовлетвориться своей прошлой зарплатой — сегодня я ей удовлетвориться не могу.»</p></div><!-- TODO: клип требует нарезки, нужен только первый фрагмент -->

<h4>Четыре уровня природы — и все четыре в человеке</h4>
<p><strong>Неживой</strong> — сохранить форму, ничего не присоединять извне. <strong>Растительный</strong> — тянуться к свету, расти; живёт циклами, индивидуальности нет. <strong>Животный</strong> — выжить и продолжить род; появляется индивидуальность и свой жизненный цикл. <strong>Человек</strong> — единственное существо, чьё развитие полностью зависит от себе подобных.</p>
<p>Все четыре есть внутри каждого человека. Волосы и ногти — растительный уровень в теле: мы их стрижём и не страдаем, тело этого даже не замечает. Потеря на растительном уровне не задевает животный и тем более человеческий.</p>
<div class="kab-figure" style="border: 1px solid #D9E8F2; border-radius: 12px; background: #fff; padding: 20px 16px 12px; margin: 20px 0;">
<div style="max-width: 560px; margin: 0 auto;">
<div style="display: flex; align-items: stretch; gap: 10px;">
<div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px;">
<div style="padding: 10px 12px; border-radius: 10px; background: #E3F1F8; color: #3C5D90;">
<div style="font-weight: 700; font-size: 14.5px; line-height: 1.3;">4 · Человек</div>
<div style="font-size: 12.5px; line-height: 1.4; color: #4A6690; margin-top: 2px;">единственное существо, чьё развитие полностью зависит от себе подобных</div>
</div>
<div style="padding: 10px 12px; border-radius: 10px; background: #B9DDEC; color: #3C5D90;">
<div style="font-weight: 700; font-size: 14.5px; line-height: 1.3;">3 · Животный</div>
<div style="font-size: 12.5px; line-height: 1.4; color: #3C5D90; margin-top: 2px;">выжить и продолжить род; появляется индивидуальность и свой жизненный цикл</div>
</div>
<div style="padding: 10px 12px; border-radius: 10px; background: #52B0D8; color: #fff;">
<div style="font-weight: 700; font-size: 14.5px; line-height: 1.3;">2 · Растительный</div>
<div style="font-size: 12.5px; line-height: 1.4; color: rgba(255,255,255,.9); margin-top: 2px;">тянуться к свету, расти; живёт циклами, индивидуальности нет</div>
<div style="font-size: 12px; line-height: 1.4; color: rgba(255,255,255,.88); margin-top: 6px; padding-top: 6px; border-top: 1px solid rgba(255,255,255,.35);">в теле человека это волосы и ногти: стрижём — и тело этого даже не замечает</div>
</div>
<div style="padding: 10px 12px; border-radius: 10px; background: #3C5D90; color: #fff;">
<div style="font-weight: 700; font-size: 14.5px; line-height: 1.3;">1 · Неживой</div>
<div style="font-size: 12.5px; line-height: 1.4; color: rgba(255,255,255,.85); margin-top: 2px;">сохранить форму, ничего не присоединять извне</div>
</div>
</div>
<div style="flex: none; display: flex; align-items: stretch; gap: 5px; padding: 4px 0;">
<div style="width: 9px; border: 2px solid #A42BB9; border-left: 0; border-radius: 0 8px 8px 0;"></div>
<div style="display: flex; align-items: center;"><div style="writing-mode: vertical-rl; transform: rotate(180deg); font-size: 11.5px; font-weight: 600; line-height: 1.1; color: #A42BB9; white-space: nowrap;">все четыре — внутри каждого человека</div></div>
</div>
</div>
</div>
<div style="border-top: 1px solid #EFF7FA; margin-top: 18px; padding-top: 12px; font-size: 13.5px; line-height: 1.6; color: #7A7A7A;"><span style="font-weight: 700; color: #3C5D90;">Схема 4.</span> Четыре уровня природы — неживой, растительный, животный, человек — и все четыре внутри одного человека. Уровни не смешиваются: потеря на растительном уровне не задевает животный и тем более человеческий.</div>
</div>

<h4>Та же лестница у человечества</h4>
<p>Человечество проходит те же ступени, что и отдельный человек, только медленнее.</p>
<table style="border-collapse:collapse;width:100%">
<thead><tr>
<th style="text-align:left;padding:6px 10px;border-bottom:2px solid #ccc">Эпоха</th>
<th style="text-align:left;padding:6px 10px;border-bottom:2px solid #ccc">Уровень</th>
<th style="text-align:left;padding:6px 10px;border-bottom:2px solid #ccc">Доминирующее желание</th>
</tr></thead>
<tbody>
<tr><td style="padding:6px 10px;border-bottom:1px solid #e3e3e3">Первобытность, ~35 тыс. — 4 тыс. до н. э.</td><td style="padding:6px 10px;border-bottom:1px solid #e3e3e3">неживой</td><td style="padding:6px 10px;border-bottom:1px solid #e3e3e3">телесные: еда, секс, семья</td></tr>
<tr><td style="padding:6px 10px;border-bottom:1px solid #e3e3e3">Древние цивилизации, 4 тыс. до н. э. — V век</td><td style="padding:6px 10px;border-bottom:1px solid #e3e3e3">растительный</td><td style="padding:6px 10px;border-bottom:1px solid #e3e3e3">богатство</td></tr>
<tr><td style="padding:6px 10px;border-bottom:1px solid #e3e3e3">Средневековье, V—XV век</td><td style="padding:6px 10px;border-bottom:1px solid #e3e3e3">животный</td><td style="padding:6px 10px;border-bottom:1px solid #e3e3e3">власть</td></tr>
<tr><td style="padding:6px 10px;border-bottom:1px solid #e3e3e3">Новое время, XV — конец XX века</td><td style="padding:6px 10px;border-bottom:1px solid #e3e3e3">человеческий</td><td style="padding:6px 10px;border-bottom:1px solid #e3e3e3">знание</td></tr>
<tr><td style="padding:6px 10px">Наше время</td><td style="padding:6px 10px">—</td><td style="padding:6px 10px">вопрос о смысле, единый закон мироздания</td></tr>
</tbody></table>
<p>Новое время называют «цивилизацией личности»: великие географические открытия, Ренессанс с человеком в центре, реформация, научная революция — всё это одно растущее желание знать.</p>
<p>Совпадение порядка не метафора: это один закон, проявленный на двух масштабах. Дальше речь только о человеке.</p>', timemodified=UNIX_TIMESTAMP() WHERE id=2258 AND bookid=962;
UPDATE mdl_book_chapters SET content='<h4>Наслаждение живёт в промежутке</h4>
<p>Наслаждение существует не в предмете и не в человеке, а <strong>между нехваткой и наполнением.</strong> Голодный наслаждается едой; сытый той же едой не наслаждается — еда та же, промежутка больше нет.</p>
<div class="kab-figure" style="border: 1px solid #D9E8F2; border-radius: 12px; background: #fff; padding: 20px 16px 12px; margin: 20px 0;">
<div style="max-width: 560px; margin: 0 auto;">
<div style="display: flex; align-items: flex-start; gap: 6px;">
<div style="flex: 1 1 0; min-width: 0; text-align: center;">
<div style="height: 36px; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 6px; font-size: 12px; line-height: 1.2; color: #7A7A7A;">наполнения нет</div>
<div style="width: 100%; max-width: 104px; height: 52px; margin: 0 auto; border: 2.5px solid #3C5D90; border-top: 0; border-radius: 0 0 50% 50% / 0 0 100% 100%; background: #fff;"></div>
<div style="margin-top: 10px; font-weight: 700; font-size: 13.5px; line-height: 1.3; color: #3C5D90;">Нехватка</div>
<div style="font-size: 12px; line-height: 1.35; color: #7A7A7A; margin-top: 3px;">желание есть, а наполнения нет</div>
</div>
<div style="flex: none; width: 14px; padding-top: 54px; display: flex; justify-content: center;"><div style="width: 0; height: 0; border-top: 7px solid transparent; border-bottom: 7px solid transparent; border-left: 10px solid #52B0D8;"></div></div>
<div style="flex: 1 1 0; min-width: 0; text-align: center;">
<div style="height: 36px; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; padding-bottom: 2px;">
<div style="width: 4px; height: 22px; border-radius: 2px; background: linear-gradient(to bottom, rgba(82,176,216,.15), #52B0D8);"></div>
<div style="width: 0; height: 0; border-left: 7px solid transparent; border-right: 7px solid transparent; border-top: 10px solid #52B0D8;"></div>
</div>
<div style="width: 100%; max-width: 104px; height: 52px; margin: 0 auto; border: 2.5px solid #3C5D90; border-top: 0; border-radius: 0 0 50% 50% / 0 0 100% 100%; overflow: hidden; display: flex; flex-direction: column; justify-content: flex-end; background: #fff;">
<div style="height: 55%; background: linear-gradient(to bottom, rgba(82,176,216,.35), rgba(82,176,216,.65));"></div>
</div>
<div style="margin-top: 10px; font-weight: 700; font-size: 13.5px; line-height: 1.3; color: #A42BB9;">Промежуток</div>
<div style="font-size: 12px; line-height: 1.35; color: #7A7A7A; margin-top: 3px;">наполнение входит в желание — здесь и живёт наслаждение</div>
</div>
<div style="flex: none; width: 14px; padding-top: 54px; display: flex; justify-content: center;"><div style="width: 0; height: 0; border-top: 7px solid transparent; border-bottom: 7px solid transparent; border-left: 10px solid #52B0D8;"></div></div>
<div style="flex: 1 1 0; min-width: 0; text-align: center;">
<div style="height: 36px; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 6px; font-size: 12px; line-height: 1.2; color: #7A7A7A;">промежутка нет</div>
<div style="width: 100%; max-width: 104px; height: 52px; margin: 0 auto; border: 2.5px solid #3C5D90; border-top: 0; border-radius: 0 0 50% 50% / 0 0 100% 100%; overflow: hidden; background: rgba(82,176,216,.65);"></div>
<div style="margin-top: 10px; font-weight: 700; font-size: 13.5px; line-height: 1.3; color: #3C5D90;">Наполнение</div>
<div style="font-size: 12px; line-height: 1.35; color: #7A7A7A; margin-top: 3px;">наслаждение исчезает — и желание вместе с ним</div>
</div>
</div>
<div style="margin-top: 14px; padding: 10px 14px; border-radius: 0 10px 10px 0; background: #F4F9FD; border-left: 3px solid #A42BB9; font-size: 13px; line-height: 1.5; color: #222;">Голодный наслаждается едой; сытый той же едой — нет. <span style="font-weight: 600; color: #3C5D90;">Еда та же — промежутка больше нет.</span></div>
</div>
<div style="border-top: 1px solid #EFF7FA; margin-top: 18px; padding-top: 12px; font-size: 13.5px; line-height: 1.6; color: #7A7A7A;"><span style="font-weight: 700; color: #3C5D90;">Схема 5.</span> Наслаждение существует не в предмете и не в человеке, а в промежутке между нехваткой и наполнением. Наполненное желание не уменьшается, а исчезает — вместе с наслаждением, которое давало.</div>
</div>

<h4>Наполненное желание исчезает</h4>
<p>Не уменьшается постепенно, а исчезает — вместе с наслаждением, которое давало.</p>
<p>Проверьте на себе за минуту: вспомните то, чего добивались долго и чего добились. Через сколько это перестало ощущаться?</p>
<div style="border-left:3px solid #4a90a4;background:#f2f8fa;padding:12px 16px;margin:20px 0">
<p style="margin:0"><strong>Клип, 3 мин.</strong> «Погоня за счастьем». «Когда я ожидаю счастье, ожидание даёт ему огромные рамки, а когда получаю его, эти рамки резко сужаются… Счастье — в движении к желаемому.»</p>
<p style="margin:.5em 0 0"><a href="https://kabbalahmedia.info/ru/lessons/cu/Y8OsAcsO" target="_blank" rel="noreferrer">Смотреть</a></p></div>

<h4>Поэтому желания растут</h4>
<p><strong>Чтобы наслаждение было дальше, нужно желание больше предыдущего.</strong> Не потому что человек ненасытен по характеру, а потому что при прежнем размере желания наслаждения не будет вовсе.</p>

<h4>Чем выше качество, тем дольше держится</h4>
<p>Наслаждение от еды измеряется минутами. От покупки — днями. От признания — неделями, иногда месяцами. От понимания чего-то важного — дольше всего.</p>
<p>Кончается на каждой ступени. Меняется только срок.</p>

<h4>Двойное опустошение</h4>
<p>Когда желание встречается с наполнением, ожидается наслаждение — а через некоторое время исчезают оба: и наполнение перестаёт наполнять, и желание перестаёт быть.</p>
<p>Остаётся не нейтральное состояние, а пустота на месте двух вещей сразу. Отсюда знакомая плоскость после достижения, которую обычно списывают на усталость.</p>

<h4>Что этим объясняется</h4>
<ul>
<li>Почему достижение цели даёт не радость, а странное ничто.</li>
<li>Почему следующая покупка должна быть дороже предыдущей.</li>
<li>Почему выгорание чаще настигает тех, у кого получается.</li>
</ul>
<p><strong>Вопрос для отклика.</strong> Вспомните желание, которого очень хотели и добились. Что осталось от того наслаждения через полгода?</p>', timemodified=UNIX_TIMESTAMP() WHERE id=2257 AND bookid=962;
UPDATE mdl_book SET timemodified=UNIX_TIMESTAMP() WHERE id=962;
COMMIT;
SELECT id, pagenum, title, LENGTH(content) len, (LENGTH(content)-LENGTH(REPLACE(content,'kab-figure','')))/LENGTH('kab-figure') figures, (LENGTH(content)-LENGTH(REPLACE(content,'заказ дизайнеру','')))/LENGTH('заказ дизайнеру') todo FROM mdl_book_chapters WHERE bookid=962 ORDER BY pagenum;
