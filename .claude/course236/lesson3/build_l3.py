# -*- coding: utf-8 -*-
"""Lesson 3 «Восприятие реальности» for course 236: page HTML builder.
Usage: python build_l3.py            -> writes l3html/*.html
       python build_l3.py --sql IDS  -> also writes lesson3.sql (IDS = json file with ids)
"""
import os, sys, json, time, binascii, re
os.chdir(r'C:\Users\tashaev\AppData\Local\Temp\kabsc2')
os.makedirs('l3html', exist_ok=True)
FILER = 'https://files.kabbalahmedia.info/kabacademy/MAK/'
IMG = 'https://edu.kabacademy.com/kab/img/'

def clip(name, ar='16 / 9'):
    return ('<div class="kab-clip" style="border-left: 3px solid #4a90a4; background: #f2f8fa; padding: 12px 16px; margin: 20px 0;">\n'
            '<video class="nomediaplugin" controls="controls" controlslist="nodownload" preload="metadata" playsinline="playsinline" oncontextmenu="return false;" '
            'poster="%s%s.jpg" style="display: block; width: 100%%; aspect-ratio: %s; background: #000;"><source src="%s%s.mp4" type="video/mp4"></video>\n</div>\n'
            % (FILER, name, ar, FILER, name))

def figure(code, n, w, h, alt, caption):
    return ('<div class="kab-figure" style="border: 1px solid #D9E8F2; border-radius: 12px; background: #fff; padding: 12px 12px 10px; margin: 20px 0;">\n'
            '<a href="%(i)sschemes/%(c)s.webp?v=1" target="_blank" rel="noopener" style="display: block;">\n<picture>\n'
            '<source media="(prefers-reduced-motion: reduce)" srcset="%(i)sschemes/%(c)s-static.png?v=1" width="%(w)d" height="%(h)d">\n'
            '<source type="image/webp" srcset="%(i)sschemes/%(c)s.webp?v=1" width="%(w)d" height="%(h)d">\n'
            '<img src="%(i)sschemes/%(c)s-static.png?v=1" width="%(w)d" height="%(h)d" alt="Схема %(n)d. %(alt)s" style="display: block; width: auto; max-width: 100%%; height: auto; margin: 0 auto; border-radius: 8px;">\n'
            '</picture>\n</a>\n'
            '<div style="border-top: 1px solid #EFF7FA; margin-top: 12px; padding-top: 10px; font-size: 13.5px; line-height: 1.6; color: #7A7A7A;"><span style="font-weight: 700; color: #3C5D90;">Схема %(n)d.</span> %(cap)s</div>\n'
            '</div>\n' % dict(i=IMG, c=code, n=n, w=w, h=h, alt=alt, cap=caption))

def p(t):
    return '<p>%s</p>\n' % t

def h4(t):
    return '<h4>%s</h4>\n' % t

# ---------------- Содержание ----------------
toc = '''<div class="kab-text">
<div class="kab-quote">
<ul class="kab-list">
<li>Глава 1. Чем мы воспринимаем и где границы</li>
<li>Глава 2. Закон подобия свойств</li>
<li>Глава 3. Желание рисует картину</li>
<li>Что с этим делать</li>
</ul>
</div>
<div class="kab-kicker">Прежде чем читать</div>
<p>Три вопроса для размышления.</p>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Откуда вы знаете, что мир вне вас именно такой, каким вы его видите?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Почему одно и то же событие двое переживают по-разному?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Если высшая сила есть, чем её ощущать?</div></div>
</div>
<div class="kab-kicker">О чём этот урок</div>
<p>Тема считается самой сложной в каббале. Не из-за терминов — их здесь почти нет, — а из-за того, что она делает с привычной картиной: выясняется, что картина не совпадает с миром.</p>
<p>Поэтому сразу, чтобы держаться за что-то твёрдое: <strong>у урока есть один закон и один вывод из него.</strong> Закон — подобие свойств: ощутить можно только то, чему ты подобен. Вывод — существует то, чего нашими пятью органами чувств не ощутить, и для этого нужен шестой.</p>
<p>Всё остальное в уроке — подступы к этому закону и следствия из него.</p>
</div>
<p></p>
'''

# ---------------- Глава 1 ----------------
ch1 = ''
ch1 += h4('Пять органов чувств и узкие щели')
ch1 += p('Зрение, слух, обоняние, вкус, осязание. У каждого узкий диапазон.')
ch1 += p('Глаз видит примерно от 420 до 650 нанометров. Ухо слышит примерно до 20 килогерц. Киты переговариваются ниже этого, летучие мыши выше; ни тех, ни других мы не слышим.')
ch1 += figure('L03-03', 6, 800, 382,
              'Видимый свет — узкая полоса электромагнитного спектра между ультрафиолетом и инфракрасным излучением, примерно от 400 до 700 нанометров',
              'Электромагнитный спектр: гамма-лучи, рентген, ультрафиолет, инфракрасное излучение, микроволны, радио. Видимый свет — узкая полоса посередине, примерно 400–700 нанометров. Это всё, что видит человек.')
ch1 += figure('L03-04', 7, 800, 738,
              'Звуковые колебания: инфразвук кита, слышимый человеком диапазон от 20 герц до 20 килогерц, ультразвук летучей мыши',
              'Звуковые колебания: кит переговаривается в инфразвуке, летучая мышь — в ультразвуке. Человек слышит полосу от 20 Гц до 20 кГц, и это всё, что он слышит.')
ch1 += p('Приборы расширяют диапазон: инфракрасная камера, ультрафиолет, рентген, микроскоп для малого, телескоп для далёкого. Каждый прибор добавляет полосу, которой у нас нет.')
ch1 += clip('Lesson-03-clip-02')
ch1 += p('<strong>Вывод из этого не тот, который делают обычно.</strong> Не «мир больше, чем мы видим» — это и так понятно. А вот что: <strong>о том, чего мы не уловили, мы не можем сказать ничего.</strong> Ни что там что-то есть, ни что там ничего нет.')
ch1 += figure('L03-01', 8, 800, 498,
              'Пять узких щелей в перегородке, через которые к человеку доходят зрение, слух, обоняние, вкус и осязание',
              'Пять узких щелей: из всего, что есть снаружи, до человека доходит только то, что проходит через зрение, слух, обоняние, вкус и осязание. Об остальном сказать нельзя ничего.')
ch1 += h4('Среду не видно изнутри')
ch1 += p('Две молодые рыбы встречают старую. Та говорит: «Доброе утро, парни, как вам вода?» Проплыв дальше, одна спрашивает другую: «Что такое вода?»')
ch1 += p('Среду, в которой находишься постоянно, нечем заметить: для этого нужна вторая точка отсчёта, а её нет.')
ch1 += h4('Форма возникает при встрече')
ch1 += p('Бааль Сулам в статье «Внутреннее созерцание» описывает это так: пять органов чувств передают в мозг только сигналы о свойствах, а форму эти свойства приобретают лишь при взаимодействии с органом чувств.')
ch1 += p('То есть форма — не свойство предмета. Форма — результат встречи предмета с тем, кто его воспринимает.')
ch1 += clip('Lesson-03-clip-01')
ch1 += h4('Четыре ответа на вопрос «каков мир на самом деле»')
ch1 += '''<table style="border-collapse: collapse; width: 100%;">
<thead><tr>
<th style="text-align: left; padding: 6px 10px; border-bottom: 2px solid #ccc;">Подход</th>
<th style="text-align: left; padding: 6px 10px; border-bottom: 2px solid #ccc;">Что утверждает</th>
</tr></thead>
<tbody>
<tr><td style="padding: 6px 10px; border-bottom: 1px solid #e3e3e3;">Классическая физика, Ньютон</td><td style="padding: 6px 10px; border-bottom: 1px solid #e3e3e3;">мир существует сам по себе, без связи с человеком, и неизменен</td></tr>
<tr><td style="padding: 6px 10px; border-bottom: 1px solid #e3e3e3;">Теория относительности, Эйнштейн</td><td style="padding: 6px 10px; border-bottom: 1px solid #e3e3e3;">восприятие относительно: зависит от наблюдателя, его места и скорости</td></tr>
<tr><td style="padding: 6px 10px; border-bottom: 1px solid #e3e3e3;">Квантовая физика</td><td style="padding: 6px 10px; border-bottom: 1px solid #e3e3e3;">наблюдатель влияет на наблюдаемое; воспринятое — среднее между свойствами наблюдателя и объекта</td></tr>
<tr><td style="padding: 6px 10px;">Каббала</td><td style="padding: 6px 10px;">человек воспринимает только то, что есть в нём; о том, что вне, сказать нельзя</td></tr>
</tbody></table>
'''
ch1 += p('Каббала не спорит с первыми тремя и не отменяет их. Она делает следующий шаг: если воспринятое зависит от воспринимающего, то работать надо с воспринимающим.')
ch1 += h4('Что это не значит')
ch1 += p('Субъективность восприятия не отменяет макромир.')
ch1 += p('Пока человек состоит из материи этого мира, сквозь стену он не пройдёт — сколько бы ни знал про то, что внутри атома пустоты больше, чем вещества, и что рентгеновское излучение проходит через стол насквозь. Мы живём в макромире теми чувствами, которые у нас есть, и стол здесь твёрдый.')
ch1 += p('«Всё субъективно» и «ничего не важно» — разные утверждения, и второе из первого не следует.')

# ---------------- Глава 2 ----------------
ch2 = ''
ch2 += p('Это опора урока. Остальное — следствия.')
ch2 += h4('Формулировка')
ch2 += p('<strong>Ощутить можно только то, чему ты подобен.</strong> Подобные в свойствах притягиваются — в мере подобия, вплоть до слияния в полном подобии.')
ch2 += h4('Как это работает')
ch2 += p('На частоте 103 FM что-то передают. Пока приёмник не настроен на эту частоту, для человека там нет ничего: ни музыки, ни помех, ничего. Настраиваешь генератор на ту же волну — возникает резонанс, и звук появляется.')
ch2 += p('Волна была и до этого. Приёмника не было.')
ch2 += figure('L03-02', 9, 800, 527,
              'Резонанс: волна снаружи, приёмник внутри; когда частоты совпадают, появляется звук',
              'Волна снаружи и приёмник внутри. Пока частоты не совпадают, для приёмника на этой волне нет ничего. Совпали — резонанс, и звук появился. Волна была и до этого; приёмника не было.')
ch2 += p('Обратная сторона того же: если кто-то машет флажками, а человек смотрит в другую сторону, связи не будет — при том что и сигнал есть, и глаза есть.')
ch2 += clip('Lesson-03-clip-05')
ch2 += h4('Зачем это в курсе')
ch2 += p('С первого урока говорится, что есть сила отдачи. Отсюда естественный вопрос: чем её ощутить?')
ch2 += p('По закону подобия свойств ответ такой: <strong>имеющимися пятью органами чувств — нечем.</strong> Все они устроены на получение: взять то, что приятно, отвергнуть то, что неприятно. Сила отдачи в этот диапазон не попадает — не потому что далеко или скрывается, а потому что нет приёмника.')
ch2 += p('Это и делает закон подобия свойств не отвлечённой формулой, а рабочим указанием: он говорит, что именно надо в себе вырастить.')

# ---------------- Глава 3 ----------------
ch3 = ''
ch3 += h4('Замечаешь то, чего хочешь')
ch3 += p('Из всего, что попало в диапазон, человек замечает то, что отвечает его желанию.')
ch3 += p('Захотел машину — начал видеть машины, которых до этого как будто не было. Родился ребёнок — обнаружились детские магазины на тех же улицах, где ходил годами.')
ch3 += clip('Lesson-03-clip-04')
ch3 += h4('И оцениваешь тем же')
ch3 += p('Одна и та же осень: один рад, что кончилась жара, другой тоскует от неопределённости. Погода одна, желания разные.')
ch3 += p('Двое в автобусе видят одно: человек тянется в чужой карман. Взрослый видит вора. Ребёнок думает, что тот хочет положить подарок. И честно: <strong>зачем он туда тянется, не знает ни один из них.</strong>')
ch3 += h4('Проверяемое сегодня')
ch3 += p('Проявили в соцсети взгляды — лента начинает подкидывать подтверждения. Через короткое время оказывается, что весь мир думает так же. У человека рядом другая лента: другие факты, другие события, другое объяснение. Алгоритм не злой, он зарабатывает.')
ch3 += p('Двадцать лет назад разговор о субъективности восприятия был отвлечённым. Сейчас он бытовой.')
ch3 += h4('Поворот на себя')
ch3 += p('Если картина складывается из свойств смотрящего, то претензия к картине — это сведения о смотрящем.')
ch3 += p('В Вавилонском Талмуде это сказано так: «Каждый отрицает согласно своему изъяну». У Бааль Шем Това — через зеркало: увидел недостаток в другом — считай, посмотрел в зеркало; если лицо испачкано, зеркало это и покажет.')
ch3 += p('Реакция на другого человека — самый доступный прибор наблюдения за собой. Он всегда под рукой и не требует ничего, кроме внимания.')
ch3 += clip('Lesson-03-clip-03')
ch3 += h4('Экран — шестой орган чувств')
ch3 += p('Часть Вселенной, которая ощущается пятью органами чувств, называется <strong>наш мир</strong>. Всё, что вне их диапазона, в него не входит.')
ch3 += p('Чтобы ощутить то, что за пределом, нужен дополнительный орган чувств. В каббале он называется <strong>экран</strong>. Каббала — методика его приобретения.')
ch3 += p('Слово провоцирует ложные догадки: третий глаз, интуиция, предчувствие. Ничего из этого. Речь о свойстве, которого сейчас нет и которое вырабатывается.')
ch3 += clip('Lesson-03-clip-06')
ch3 += h4('Экран — не аскеза')
ch3 += p('Это место чаще всего понимают наоборот, поэтому прямо.')
ch3 += p('<strong>Экран — не отказ от желания и не его уменьшение.</strong> Желание растёт, остановить его нельзя, и задача не в том.')
ch3 += p('Экран — это расчёт: получать не ради самого получения. Желание остаётся целиком, меняется то, ради чего оно наполняется.')
ch3 += p('Большие средства — это ресурс. Меценаты, построившие музеи и театры, употребили деньги ещё на что-то; о личных их качествах это не говорит ничего, но пример показывает разницу между размером желания и его употреблением.')
ch3 += p('Сила желания сама по себе ничем не плоха. Можно хотеть овладеть всем миром. Вопрос только в инструменте.')
ch3 += p('Это то же различение желания и намерения, что в уроке 2, повёрнутое в сторону восприятия: меняется намерение — меняется то, что человек вообще видит.')

# ---------------- Что с этим делать ----------------
do = ''
do += p('Тема оставляет закономерный вопрос: картина мира моя — и что мне с этим делать в понедельник утром?')
do += h4('Первое. Перестать проверять утверждение и начать проверять себя')
do += p('Спорить о том, существует ли мир вне человека, можно долго и без результата: прибора для этого спора нет ни у одной из сторон. А вот проверить, как собственное состояние меняет картину, можно на этой же неделе. Самое дешёвое наблюдение — заметить один случай, где реакция на человека сказала о вас больше, чем о нём.')
do += h4('Второе. Не в одиночку')
do += clip('Lesson-03-clip-K1')
do += p('Новый орган чувств не вырабатывается в одиночку, потому что проверяется он только на других людях. Отсюда учебные группы — не организационная форма, а прибор. Как этим пользоваться, разбирается в уроке 8.')
do += h4('Третье. Сегодня это не сработает')
do += p('Экран вырабатывается годами. Обещать быстрый результат было бы неправдой. Но направление названо, и оно проверяемое: не «поверьте», а «посмотрите, что получится».')
do += h4('Что дальше')
do += p('На следующей неделе — <strong>строение мироздания.</strong> Мы говорили, что всё, кроме силы отдачи, — это желание получать. Откуда оно взялось? Как из точки отдачи получилось желание получать, шаг за шагом.')

# ---------------- Резюме ----------------
resume = '''<div class="kab-page">
<div class="kab-card">
<div class="kab-row"><div class="kab-dot"></div><div>Наш мир — то, что улавливают пять органов чувств: зрение, слух, обоняние, вкус, осязание. О том, что вне их диапазона, сказать нельзя ничего.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Форма — не свойство предмета, а результат его встречи с воспринимающим.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div><b>Основной закон мироздания — закон подобия свойств:</b> подобные в свойствах притягиваются, в мере подобия.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Пять органов чувств устроены на получение, поэтому силу отдачи ими не ощутить.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Из всего доступного человек замечает и оценивает то, что отвечает его желанию. Претензия к картине — сведения о смотрящем.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div><b>Экран — дополнительный, шестой орган чувств</b>, которым ощущается высший мир. Не аскеза и не отказ: желание остаётся, меняется намерение.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div><b>Каббала — методика приобретения человеком экрана.</b></div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Вырабатывается он не в одиночку: проверяется только на других людях.</div></div>
</div>
<div class="kab-quote kab-quote--purple">На следующей неделе — <b>строение мироздания</b>. Мы говорили, что всё, кроме силы отдачи, — это желание получать. Откуда оно взялось? Как из точки отдачи получилось желание получать, шаг за шагом.</div>
</div>
'''

# ---------------- Отклик недели ----------------
otklik = '''<div class="kab-text">
<div class="kab-quote">Напишите своими словами ответ на любой из трёх вопросов. Один, не все три.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Заметьте за неделю один случай, где ваша реакция на человека сказала о вас больше, чем о нём. Что это было? Одной фразы достаточно.</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Почему в других обычно заметнее отрицательное — и почему мать видит в ребёнке в основном хорошее?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>При каких условиях в другом человеке становится видно хорошее?</div></div>
</div>
<div class="kab-note">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
</div>
'''

# ---------------- Запись вебинара ----------------
record = '''<div class="kab-page">
<div class="kab-quote">Записи трёх трансляций вебинара 15 октября появятся здесь на следующий день после эфира.</div>
<div style="border: 1px dotted #b8a06a; background: #e6f4ff; padding: 12px 16px; margin: 16px 0 0; border-radius: 4px;">
<ul class="kab-list">
<li><strong>Вебинар </strong>в <strong>20:00 </strong>из студии Петах-Тиквы (Израиль)</li>
</ul>
</div>
<p> </p>
<div style="border: 1px dotted #b8a06a; background: #e6f4ff; padding: 12px 16px; margin: 16px 0 0; border-radius: 4px;">
<ul class="kab-list">
<li><strong>Вебинар </strong>в <strong>17:00 </strong>из студии Петах-Тиквы (Израиль)</li>
</ul>
</div>
<p> </p>
<div style="border: 1px dotted #b8a06a; background: #e6f4ff; padding: 12px 16px; margin: 16px 0 0; border-radius: 4px;">
<ul class="kab-list">
<li><strong>Вебинар </strong>в <strong>8:00 </strong>из Сан-Франциско (США)</li>
</ul>
</div>
</div>
'''

# ---------------- Книга «Дополнительные материалы» ----------------
def yt(title, ytid, driveid):
    return ('<p style="text-align: center;"><a class="external-media-provider" href="https://youtu.be/%s">%s</a></p>\n'
            '<details class="kab-details">\n<summary><span style="color: #3c5d90;">Альтернативный плеер <em>(если видео выше не воспроизводится)</em></span></summary>\n'
            '<br><iframe src="https://drive.google.com/file/d/%s/preview" width="100%%" height="480" allow="autoplay"></iframe></details>\n' % (ytid, title, driveid))

book_ch1 = '<div class="kab-page">\n<div class="kab-quote">Лекция курса «Основы каббалы» по теме урока — в записи. Тот же материал в лекционном изложении; ядро урока на него не опирается.</div>\n'
book_ch1 += '<div class="kab-kicker">Восприятие реальности. Преподаватель Борис Белоцерковский</div>\n'
book_ch1 += yt('Восприятие реальности. Основы каббалы, часть 1 (преподаватель Борис Белоцерковский)', 'V12UNh9bZCw', '1QUBtBzzEQASTK9DfCnfpBBk_9qxU2uZv')
book_ch1 += '<div class="kab-kicker">Лекция в трёх частях</div>\n'
book_ch1 += '<p><strong>Реальность с точки зрения каббалы</strong></p>\n' + yt('Восприятие реальности. Часть 1', 'nYaLAwAi-XQ', '13VJXh7W3euTSd7ijXnBTB3sbk7kom3pD')
book_ch1 += '<p><strong>Постижение реальности. Желание. Адам</strong></p>\n' + yt('Восприятие реальности. Часть 2', 'MHppTAHOzPY', '10d0b0uzxx7SE3Wpyer8QiX6w67EyH-Id')
book_ch1 += '<p><strong>Картина восприятия. Орган ощущения реальности</strong></p>\n' + yt('Восприятие реальности. Часть 3', '4K8i50NvxVE', '1BCP2Ir7ZNWOqB_vsCMJMa0vCcBYw1QcH')
book_ch1 += '<div class="kab-kicker">Ответы на вопросы по теме</div>\n'
book_ch1 += yt('Ответы на вопросы по теме «Восприятие реальности»', '0R7EN9lP9pE', '1v_TBwNKxGnJHmyIkC_avWMVQmhgw89XM')
book_ch1 += '</div>\n'

book_ch2 = '''<div class="kab-page">
<div class="kab-quote">Беседа «Восприятие реальности» из курса «Основы каббалы», урок 5. 22 ноября 2018 г. Открывается разбором Ньютона, теории относительности и квантовой физики. Стенограмма — в следующей главе.</div>
<div style="position: relative; width: 100%; aspect-ratio: 16 / 9; margin: 16px 0;">
<iframe src="https://kabbalahmedia.info/programs/cu/kGtOXaK0?mediaType=video&amp;shareLang=ru&amp;embed=1&amp;autoPlay=0" style="position: absolute; inset: 0; width: 100%; height: 100%; border: 0;" scrolling="no" allowfullscreen=""></iframe>
</div>
<p><a class="kab-link" href="https://kabbalahmedia.info/ru/programs/cu/kGtOXaK0" target="_blank" rel="noopener">Открыть в архиве</a></p>
</div>
'''

book_ch4 = '''<div class="kab-page">
<div class="kab-quote">Факультатив, к неделе не привязан. Фильм о том, как картина мира зависит от смотрящего.</div>
<p style="text-align: center;"><a class="external-media-provider" href="https://www.youtube.com/watch?v=Db3Zf_h1o14">Фильм «Сан-Францисские горки»</a></p>
</div>
'''

pages = {
    'p_toc': toc, 'p_ch1': ch1, 'p_ch2': ch2, 'p_ch3': ch3, 'p_do': do,
    'page_resume': resume, 'forum_otklik': otklik, 'page_record': record,
    'book_ch1': book_ch1, 'book_ch2': book_ch2, 'book_ch4': book_ch4,
}
for k, v in pages.items():
    assert not re.search(r'\bРав\b', v), k
    open('l3html/%s.html' % k, 'w', encoding='utf-8', newline='\n').write(v)
print('pages written:', ', '.join('%s=%d' % (k, len(v)) for k, v in pages.items()))

# ---------------- Тест ----------------
QUESTIONS = [
    ('У3-01 Что можно сказать о неуловленном', 'Что можно сказать о том, что не попало ни в один наш орган чувств и ни в один прибор?', [
        ('Ничего', 'Верно. «Там ничего нет» и «мы не знаем, что там» — разные утверждения. Каббала говорит второе.'),
        ('Что этого не существует', '«Не уловили» не значит «нет». О том, что осталось вне диапазона, нельзя сказать ничего — ни что оно есть, ни что его нет.'),
        ('Что это существует, но скрыто', 'Это тоже утверждение о том, чего мы не уловили, — а о нём сказать нельзя ничего, даже что оно скрыто.'),
        ('Что это можно вычислить', 'Вычисление опирается на то, что уже попало в органы чувств или приборы. О том, что не попало, сказать нельзя ничего.')]),
    ('У3-02 Формулировка закона подобия свойств', 'В чём формулировка закона подобия свойств?', [
        ('Ощутить можно только то, чему ты подобен', 'Верно. Пока приёмник не настроен на частоту, для человека на ней нет ничего — при том что передача идёт.'),
        ('Подобное отталкивает подобное', 'Наоборот: подобные в свойствах притягиваются, в мере подобия. Ощутить можно только то, чему ты подобен.'),
        ('Свойства передаются по наследству', 'Закон не о наследовании. Он о восприятии: ощутить можно только то, чему ты подобен.'),
        ('Чем больше желание, тем точнее восприятие', 'Размер желания здесь ни при чём. Решает подобие свойств: ощутить можно только то, чему ты подобен.')]),
    ('У3-03 Почему силу отдачи нельзя ощутить', 'Почему силу отдачи нельзя ощутить пятью органами чувств?', [
        ('Они устроены на получение', 'Верно. Взять приятное, отвергнуть неприятное. По закону подобия свойств отдача в этот диапазон не попадает — нет приёмника, а не помеха на линии.'),
        ('Она слишком слабая', 'Дело не в силе сигнала. Пять органов чувств устроены на получение, а отдача им не подобна — нет приёмника.'),
        ('Она находится далеко', 'Расстояние ни при чём: и рядом её нечем уловить. Органы чувств устроены на получение, отдача в их диапазон не попадает.'),
        ('Она намеренно скрывается', 'Никто ничего не прячет. Органы чувств устроены на получение, и сила отдачи просто не попадает в их диапазон.')]),
    ('У3-04 Что такое экран', 'Что такое экран?', [
        ('Дополнительный орган чувств, который надо выработать', 'Верно. И это не аскеза: желание не уменьшается, меняется то, ради чего оно наполняется.'),
        ('Отказ от удовольствий', 'Отказ от желания — принципиально другой подход, не этот. Экран — дополнительный орган чувств, желание при нём остаётся целиком.'),
        ('Защита от чужого влияния', 'Экран ничего не отгораживает. Это дополнительный орган чувств, которого сейчас нет и который вырабатывается.'),
        ('Умение сосредоточиться', 'Сосредоточенность — свойство имеющихся органов чувств. Экран — орган, которого сейчас нет и который надо выработать.')]),
    ('У3-05 Вор или подарок', 'Двое видят, как человек тянется в чужой карман. Один видит вора, другой — подарок. Что из этого следует?', [
        ('Картина складывается из свойств смотрящего', 'Верно. Событие произошло — и при этом ни один из двоих не знает, зачем человек туда тянулся.'),
        ('Один из них наблюдательнее', 'Оба увидели одно и то же движение. Разница не в наблюдательности, а в свойствах смотрящего, из которых складывается картина.'),
        ('Событие не произошло', 'Событие произошло: рука потянулась в карман. Разной была не рука, а картина, которую каждый сложил из своих свойств.'),
        ('Истину установить нельзя никогда', 'Слишком сильный вывод. Пример говорит только о том, что картина складывается из свойств смотрящего, — и это можно проверить на себе.')]),
    ('У3-06 Что делать с субъективностью', 'Что предлагается делать с тем, что картина мира субъективна?', [
        ('Сближаться с другими людьми', 'Верно. Новый орган чувств проверяется только на других, поэтому в одиночку не вырабатывается. Разбор — в уроке 8.'),
        ('Уменьшить свои желания', 'Экран — не аскеза. Желание остаётся, меняется то, ради чего оно наполняется. Предлагается сближаться с другими людьми.'),
        ('Не доверять органам чувств', 'В макромире они работают, и стол остаётся твёрдым. Предлагается не отказ от чувств, а сближение с другими людьми.'),
        ('Изучить теорию до конца', 'Спор о теории можно вести без результата: прибора для него нет. Проверяемое направление — сближение с другими людьми.')]),
]

if '--sql' in sys.argv:
    ids = json.load(open(sys.argv[sys.argv.index('--sql') + 1], encoding='utf-8'))
    NOW = int(time.time())
    def hx(s):
        return '0x' + binascii.hexlify(s.encode('utf-8')).decode()
    def q(s):
        return "'" + s.replace(chr(92), chr(92) * 2).replace("'", "''") + "'"
    def avail(op, t, uid):
        return q('{"op":"&","c":[{"type":"date","d":"%s","t":%d,"nodeUID":%d}],"showc":[true]}' % (op, t, uid))
    T = ids['dates']
    sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
    L = ids['lesson']
    # lesson
    sql.append("UPDATE mdl_lesson SET name='Лекция 3. Восприятие реальности', available=%d, activitylink=%d, timemodified=%d WHERE id=%d;" % (T['open'], ids['cm_otklik'], NOW, L['id']))
    mappage = ('<p><a title="Начать" href="https://edu.kabacademy.com/mod/lesson/view.php?id=%d&amp;pageid=%d"><img class="img-fluid" src="%smaps/L03.png" alt="Карта Урок 3" width="1174" height="915"></a></p>\n'
               % (ids['cm_lesson'], L['toc'], IMG))
    open('l3html/p_map.html', 'w', encoding='utf-8', newline='\n').write(mappage)
    titles = {'map': ('*', mappage), 'toc': ('Содержание', toc), 'ch1': ('Глава 1. Чем мы воспринимаем и где границы', ch1),
              'ch2': ('Глава 2. Закон подобия свойств', ch2), 'ch3': ('Глава 3. Желание рисует картину', ch3), 'do': ('Что с этим делать', do)}
    for key, (title, html) in titles.items():
        sql.append("UPDATE mdl_lesson_pages SET title=%s, contents=%s, timemodified=%d WHERE id=%d AND lessonid=%d;" % (q(title), hx(html), NOW, L[key], L['id']))
    # quiz
    Q = ids['quiz']
    sql.append("UPDATE mdl_quiz SET name='Проверьте себя: Тест по уроку 3', timemodified=%d WHERE id=%d;" % (NOW, Q['id']))
    sql.append("UPDATE mdl_grade_items SET itemname='Проверьте себя: Тест по уроку 3', timemodified=%d WHERE itemmodule='quiz' AND iteminstance=%d AND courseid=236;" % (NOW, Q['id']))
    for (name, text, answers), qids in zip(QUESTIONS, Q['questions']):
        for qid, aids in qids:
            sql.append("UPDATE mdl_question SET name=%s, questiontext=%s, timemodified=%d WHERE id=%d;" % (q(name), hx('<p>%s</p>\n' % text), NOW, qid))
            assert len(aids) == 4
            for (atext, fb), aid in zip(answers, aids):
                sql.append("UPDATE mdl_question_answers SET answer=%s, feedback=%s WHERE id=%d AND question=%d;" % (hx('<p>%s</p>\n' % atext), hx('<p>%s</p>\n' % fb), aid, qid))
    # pages
    sql.append("UPDATE mdl_page SET name='Резюме урока 3', content=%s, timemodified=%d WHERE id=%d;" % (hx(resume), NOW, ids['page_resume']))
    sql.append("UPDATE mdl_page SET name='Запись вебинара 3 (архив)', content=%s, timemodified=%d WHERE id=%d;" % (hx(record), NOW, ids['page_record']))
    # forums
    sql.append("UPDATE mdl_forum SET name='Отклик недели 3: вопрос на выбор', intro=%s, timemodified=%d WHERE id=%d;" % (hx(otklik), NOW, ids['forum_otklik']))
    sql.append("UPDATE mdl_forum SET name='Семинар урока 3 (в записи)', timemodified=%d WHERE id=%d;" % (NOW, ids['forum_seminar']))
    sql.append("UPDATE mdl_forum SET name='Форум: обсуждение темы Урока 3', timemodified=%d WHERE id=%d;" % (NOW, ids['forum_general']))
    sql.append("UPDATE mdl_grade_items SET itemname='Форум: обсуждение темы Урока 3 за весь форум', timemodified=%d WHERE itemmodule='forum' AND iteminstance=%d AND courseid=236;" % (NOW, ids['forum_general']))
    # feedback
    sql.append("UPDATE mdl_feedback SET name='Вопрос по теме урока 3 к вебинару с преподавателями', timemodified=%d WHERE id=%d;" % (NOW, ids['feedback']))
    sql.append("UPDATE mdl_feedback_item SET name='Ваш вопрос по теме урока 3' WHERE feedback=%d AND typ='textarea';" % ids['feedback'])
    # book
    B = ids['book']
    sql.append("UPDATE mdl_book SET name='Дополнительные материалы к Уроку 3', timemodified=%d WHERE id=%d;" % (NOW, B['id']))
    sql.append("DELETE FROM mdl_book_chapters WHERE bookid=%d;" % B['id'])
    transcript = open('spring3/t9_2094_c64.html', encoding='utf-8').read()
    chapters = [('Лекция урока в записи', book_ch1), ('Беседа «Восприятие реальности» (курс «Основы каббалы», 2018)', book_ch2),
                ('Стенограмма беседы «Восприятие реальности»', transcript), ('Фильм «Сан-Францисские горки»', book_ch4)]
    for i, (title, html) in enumerate(chapters, 1):
        sql.append("INSERT INTO mdl_book_chapters (bookid,pagenum,subchapter,title,content,contentformat,hidden,timecreated,timemodified,importsrc) VALUES (%d,%d,0,%s,%s,1,0,%d,%d,'');" % (B['id'], i, q(title), hx(html), NOW, NOW))
    # renamed copies without content changes
    sql.append("UPDATE mdl_label SET name='Как устроен этот урок', timemodified=%d WHERE id=%d;" % (NOW, ids['label']))
    sql.append("UPDATE mdl_url SET name='Мой Дневник', timemodified=%d WHERE id=%d;" % (NOW, ids['url']))
    sql.append("UPDATE mdl_page SET name='Тематические дни', timemodified=%d WHERE id=%d;" % (NOW, ids['page_days']))
    # course modules: dates & availability
    sql.append("UPDATE mdl_course_modules SET completionexpected=%d WHERE id IN (%d,%d);" % (T['exp_l'], ids['cm_lesson'], ids['cm_otklik']))
    sql.append("UPDATE mdl_course_modules SET completionexpected=%d WHERE id=%d;" % (T['exp_q'], ids['cm_quiz']))
    sql.append("UPDATE mdl_course_modules SET availability=%s WHERE id=%d;" % (avail('<', T['fb_end'], 1788791814720), ids['cm_feedback']))
    sql.append("UPDATE mdl_course_modules SET availability=%s WHERE id=%d;" % (avail('>=', T['seminar'], 1790871362904), ids['cm_seminar']))
    sql.append("UPDATE mdl_course_modules SET availability=%s WHERE id=%d;" % (avail('>=', T['open'], 1788788141539), ids['cm_record']))
    for sid, uid in zip(ids['sections_dated'], (1789038899420, 1789038924677, 1789038952197)):
        sql.append("UPDATE mdl_course_sections SET availability=%s, timemodified=%d WHERE id=%d;" % (avail('>=', T['open'], uid), NOW, sid))
    # calendar events (names/dates for copies)
    sql.append("UPDATE mdl_event SET name='Лекция 3. Восприятие реальности открывается', timestart=%d, timesort=%d, timemodified=%d WHERE modulename='lesson' AND instance=%d AND eventtype='open';" % (T['open'], T['open'], NOW, L['id']))
    sql.append("UPDATE mdl_event SET name='Лекция 3. Восприятие реальности должно быть выполнено', timestart=%d, timesort=%d, timemodified=%d WHERE modulename='lesson' AND instance=%d AND eventtype='expectcompletionon';" % (T['exp_l'], T['exp_l'], NOW, L['id']))
    sql.append("UPDATE mdl_event SET name='Проверьте себя: Тест по уроку 3 должно быть выполнено', timestart=%d, timesort=%d, timemodified=%d WHERE modulename='quiz' AND instance=%d AND eventtype='expectcompletionon';" % (T['exp_q'], T['exp_q'], NOW, Q['id']))
    sql.append("UPDATE mdl_event SET name='Отклик недели 3: вопрос на выбор должно быть выполнено', timestart=%d, timesort=%d, timemodified=%d WHERE modulename='forum' AND instance=%d AND eventtype='expectcompletionon';" % (T['exp_l'], T['exp_l'], NOW, ids['forum_otklik']))
    sql.append("UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;")
    sql += ['COMMIT;', "SELECT id,title,LENGTH(contents) clen FROM mdl_lesson_pages WHERE lessonid=%d ORDER BY id;" % L['id']]
    open('lesson3.sql', 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
    print('sql statements:', len(sql), 'bytes:', os.path.getsize('lesson3.sql'))
