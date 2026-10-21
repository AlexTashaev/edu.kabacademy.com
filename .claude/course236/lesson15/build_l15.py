# -*- coding: utf-8 -*-
"""Lesson 15 «Итоги и что дальше», course 236 — text of the doc «Урок 15 — СО финал» (21.10.2026, 1a0keaBI20YfsqhaGTe-nAOlK37xq15UQpU_1f9Nx6L0)
in the lesson 1–2 / 6–14 format: map -> contents (+ three questions) -> «О чём этот урок» (the doc's own section) -> chapters 1–5
(chapter 5 «Оплата» + «Что дальше»); quiz «Проверьте себя» — all seven questions of the doc (slot 7 added to quiz 1249); «Резюме курса»;
otklik («Задание»: one question — the doc's «Если захочется» is personal, not for the forum); book «Дополнительные материалы к Уроку 15»
(the lecture «Итоги курса», answers, recap; the doc's extra fragment on the General Law as a clip).
In-text videos: eight fragments cut from archive programs by the windows of «Урок 15 — видеоматериалы» (cut15.sh) + one in the book.
No designer's schemes for this lesson (none delivered; the video doc: «схем в уроке нет и не нужно»). Everything is due on Wednesday 23:59.

Writes html/*.html; with --sql (and BOOK_ID=<book id>) writes lesson15.sql.
Not taken from the doc: «Как устроен этот урок» (the course has its own label; «Итоговая анкета курса» is page 2385 already in the skeleton),
«Повторение пройденного» (its recap video is in the book), «Итоговый тест» of the spring course (15 questions, incl. match/gapselect),
links to kabbalahmedia.info.
"""
import binascii
import os
import re
import sys
import time
from datetime import datetime
from zoneinfo import ZoneInfo

HERE = os.path.dirname(os.path.abspath(__file__))
os.chdir(HERE)
os.makedirs('html', exist_ok=True)
FILER = 'https://files.kabbalahmedia.info/kabacademy/MAK/'
IMG = 'https://edu.kabacademy.com/kab/img/'
GRAY = '#7A7A7A'
TD = 'padding: 6px 10px; border-bottom: 1px solid #e3e3e3;'
TH = 'text-align: left; padding: 6px 10px; border-bottom: 2px solid #ccc;'
LAITMAN = 'Михаэль Лайтман'


def clip(name, ar='16 / 9', maxw=None, title=None, source=None, quote=None):
    extra = ' max-width: %dpx; margin: 0 auto;' % maxw if maxw else ''
    out = ('<div class="kab-clip" style="border-left: 3px solid #4a90a4; background: #f2f8fa; padding: 12px 16px; margin: 20px 0;">\n'
           '<video class="nomediaplugin" controls="controls" controlslist="nodownload" preload="metadata" playsinline="playsinline" oncontextmenu="return false;" '
           'poster="%s%s.jpg" style="display: block; width: 100%%;%s aspect-ratio: %s; background: #000;"><source src="%s%s.mp4" type="video/mp4"></video>\n'
           % (FILER, name, extra, ar, FILER, name))
    if title:
        out += ('<div style="margin-top: 10px; font-size: 14px; line-height: 1.55; color: %s;"><span style="font-weight: 600; color: #3C5D90;">%s</span>'
                '%s%s</div>\n' % (GRAY, title, (' — ' + source) if source else '', ('<br>' + quote) if quote else ''))
    out += '</div>\n'
    return out


def figure(code, n, w, h, alt, caption):
    return ('<div class="kab-figure" style="border: 1px solid #D9E8F2; border-radius: 12px; background: #fff; padding: 12px 12px 10px; margin: 20px 0;">\n'
            '<a href="%(i)sschemes/%(c)s.webp?v=1" target="_blank" rel="noopener" style="display: block;">\n<picture>\n'
            '<source media="(prefers-reduced-motion: reduce)" srcset="%(i)sschemes/%(c)s-static.png?v=1" width="%(w)d" height="%(h)d">\n'
            '<source type="image/webp" srcset="%(i)sschemes/%(c)s.webp?v=1" width="%(w)d" height="%(h)d">\n'
            '<img src="%(i)sschemes/%(c)s-static.png?v=1" width="%(w)d" height="%(h)d" alt="Схема %(n)d. %(alt)s" style="display: block; width: auto; max-width: 100%%; height: auto; margin: 0 auto; border-radius: 8px;">\n'
            '</picture>\n</a>\n'
            '<div style="border-top: 1px solid #EFF7FA; margin-top: 12px; padding-top: 10px; font-size: 13.5px; line-height: 1.6; color: #7A7A7A;"><span style="font-weight: 700; color: #3C5D90;">Схема %(n)d.</span> %(cap)s</div>\n'
            '</div>\n' % dict(i=IMG, c=code, n=n, w=w, h=h, alt=alt, cap=caption))


def yt(title, ytid, driveid=None):
    out = '<p style="text-align: center;"><a class="external-media-provider" href="https://youtu.be/%s">%s</a></p>\n' % (ytid, title)
    if driveid:
        out += ('<details class="kab-details" style="margin-bottom: 40px;">\n<summary><span style="color: #3c5d90;">Альтернативный плеер <em>(если видео выше не воспроизводится)</em></span></summary>\n'
                '<br><iframe src="https://drive.google.com/file/d/%s/preview" width="100%%" height="480" allow="autoplay"></iframe></details>\n' % driveid)
    return out


def archive(uid):
    """Embedded archive player only: no link to kabbalahmedia.info and no editorial note (owner, 14–15.10.2026)."""
    return ('<div class="kab-page">\n'
            '<div style="position: relative; width: 100%%; aspect-ratio: 16 / 9; margin: 16px 0;">\n'
            '<iframe src="https://kabbalahmedia.info/lessons/cu/%s?mediaType=video&amp;shareLang=ru&amp;embed=1&amp;autoPlay=0" '
            'style="position: absolute; inset: 0; width: 100%%; height: 100%%; border: 0;" scrolling="no" allowfullscreen=""></iframe>\n</div>\n</div>\n'
            % uid)


def srcquote(body, who=None):
    """body: plain text (wrapped in <p>) or ready HTML starting with '<'."""
    out = '<div class="kab-quote">\n%s\n' % (body if body.startswith('<') else '<p>%s</p>' % body)
    if who:
        out += '<div style="text-align: right; font-size: 13.5px; line-height: 1.5; color: %s; margin-top: 10px;">— %s</div>\n' % (GRAY, who)
    return out + '</div>\n'


def table(head, rows):
    out = '<table style="border-collapse: collapse; width: 100%;">\n<thead><tr>\n'
    out += ''.join('<th style="%s">%s</th>\n' % (TH, h) for h in head)
    out += '</tr></thead>\n<tbody>\n'
    for i, row in enumerate(rows):
        st = TD if i < len(rows) - 1 else 'padding: 6px 10px;'
        out += '<tr>' + ''.join('<td style="%s">%s</td>' % (st, c) for c in row) + '</tr>\n'
    return out + '</tbody></table>\n'


def p(t):
    return '<p>%s</p>\n' % t


def h4(t):
    return '<h4>%s</h4>\n' % t




def embed(kind, uid, title, caption=None, last=False):
    """Archive player block for the book: kicker title, the doc's caption (gray), embedded player. No links."""
    out = '<div class="kab-kicker">%s</div>\n' % title
    if caption:
        out += '<p style="color: %s; font-size: 14px; line-height: 1.55; margin: 4px 0 8px;">%s</p>\n' % (GRAY, caption)
    out += ('<div style="position: relative; width: 100%%; aspect-ratio: 16 / 9; margin: 8px 0 %dpx;">\n'
            '<iframe src="https://kabbalahmedia.info/%s/cu/%s?mediaType=video&amp;shareLang=ru&amp;embed=1&amp;autoPlay=0" '
            'style="position: absolute; inset: 0; width: 100%%; height: 100%%; border: 0;" scrolling="no" allowfullscreen=""></iframe>\n</div>\n'
            % (16 if last else 40, kind, uid))
    return out


INTRO = 'О чём этот урок'
CHAPTERS = ['Глава 1. Что это было', 'Глава 2. Чем оказалась сама учёба', 'Глава 3. Что это за наука', 'Глава 4. Решение', 'Глава 5. Оплата']
ML = LAITMAN

# ---------------- Содержание ----------------
toc = ('<div class="kab-text">\n<div class="kab-quote">\n<ul class="kab-list">\n'
       + ''.join('<li>%s</li>\n' % c for c in [INTRO] + CHAPTERS) +
       '''</ul>
</div>
<div class="kab-kicker">Прежде чем читать</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Что вы теперь можете сказать своими словами о том, чем занимались пятнадцать недель?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Что из этого вы проверили на себе, а что приняли на слово?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Хотите ли вы этим заниматься дальше?</div></div>
</div>
</div>
<p></p>
''')

# ---------------- О чём этот урок (в доке — свой раздел) ----------------
intro = ''   # the page title «О чём этот урок» is already shown by the lesson page — no heading inside (owner, 20.10.2026)
intro += p('Последняя неделя делает три вещи.')
intro += p('<strong>Собирает пройденное</strong> — не пересказывает, а показывает, что это была одна линия, а не пятнадцать разных тем.')
intro += p('<strong>Добавляет то, чего ещё не было:</strong> чем является сама эта наука, из чего состоит и на чём основана.')
intro += p('<strong>И ставит вопрос, на который отвечаете только вы.</strong> Продолжать или нет. Решение ваше — важно лишь, чтобы оно было основано на том, что вы узнали сами. И у этого урока есть ответ на вопрос, когда такое решение получается самым точным.')

# ---------------- Глава 1 ----------------
ch1 = p('Пятнадцать недель складываются в одну линию. Вот она целиком.')
ch1 += p('<strong>Началось с вопроса о вас</strong> — что это за наука и для кого. Потом выяснилось, что желания растут и наполнением не закрываются, а эгоизмом называется не желание, а намерение.')
ch1 += p('<strong>Потом сместилась картина мира.</strong> Ощутить можно только то, чему подобен; поэтому нужен шестой орган чувств. Устройство разобрали по четырём стадиям. И оказалось, что сила одна, а разное ощущение берётся от разной меры подобия ей.')
ch1 += p('<strong>Потом стало ясно, с чем работать.</strong> Душа — не данность, а то, что развивают. Из четырёх факторов человек выбирает один — среду. Среда складывается из источников, товарищей и учителя. А главное в любом действии — то, ради чего оно сделано.')
ch1 += p('<strong>Потом появилось основание.</strong> Кто это записал и почему открылось сейчас. Что такое высшие миры и зачем они нам, откуда взялся экран и почему свет уходит из парцуфа. Как из решимот выстроились миры, что такое второе сокращение и разбиение — и почему в одиночку собрать себя нельзя.')
ch1 += p('<strong>И в конце — про жизнь.</strong> Почему распространение оказывается частью развития самого человека. Что меняется, а что остаётся как было.')
ch1 += p('<strong>Если вы можете пересказать эту линию своими словами — курс пройден.</strong> Помнить термины при этом не обязательно.')

# ---------------- Глава 2 ----------------
ch2 = p('Здесь то, что обычно замечают только к концу.')
ch2 += p('<strong>Сам учебный процесс развивает дополнительный орган ощущения.</strong>')
ch2 += p('Не готовит к развитию, не рассказывает о нём — <strong>развивает</strong>. Чтение, обсуждение, семинары действуют не только тем, что сообщают сведения.')
ch2 += clip('Lesson-15-clip-K1', title='«Наука каббала: патент бесконечного наслаждения»', source=ML + ', вебинар, 2016',
            quote='«Желание отдавать возникнет в вас вдруг под воздействием высшего света. […] Мы ничего не знаем об этом действии. Как маленький ребенок, который развивается: бегает, прыгает, кричит, плачет, радуется, делает всё, что требует его тело, и в итоге развивается. Так и мы».')
ch2 += p('Почему так: каббалисты записывали то, что постигли сами. Текст, написанный из ощущения высшего мира, действует на читающего, даже когда тот ничего в нём не понимает.')
ch2 += clip('Lesson-15-clip-K2', title='«Основы каббалы. Методика изучения каббалы»', source=ML + ', 2018',
            quote='«Драматический рассказ воздействует на тебя чисто психологически. А здесь ты можешь читать, ничего не понимая, но этот текст все-таки влияет на тебя, и ты начинаешь постепенно ощущать, что в тебе что-то происходит».')
ch2 += p('Отсюда и то, что многие заметили за эти недели: <strong>одна и та же статья читается по-разному в разное время.</strong> Изменился не текст.')
ch2 += clip('Lesson-15-clip-K3', title='«Мир – реальность или иллюзия?»', source=ML + ', вебинар, 2019',
            quote='«Те, кто сегодня первый раз читают „Суть науки каббала“, даже не представляют, что они еще увидят в ней, пройдя по второму, третьему разу, через год, через 10 лет. […] Я нынешний и тот человек, который 40 лет назад открывал эту статью, совершенно разные люди».')
ch2 += p('<strong>И отсюда же ответ на вопрос, который мучил с третьей недели:</strong> как проверить то, чего нечем проверить. Проверка идёт не рассуждением, а тем, что человек постепенно начинает различать. Медленно, годами — но идёт.')

# ---------------- Глава 3 ----------------
ch3 = p('Теперь то, чего в курсе ещё не было.')
ch3 += h4('Определение')
ch3 += p('<strong>Каббала — выверенная временем методика, позволяющая развить дополнительный орган ощущения.</strong>')
ch3 += p('Она даёт возможность получать добавочную информацию о том, что существует в мироздании, — и <strong>освоив метод, человек начинает осознавать мир вне зависимости от собственного субъективного восприятия.</strong>')
ch3 += p('Последнее — прямой ответ на урок 3. Там было сказано: человек подобен чёрному ящику, картина мира складывается из свойств смотрящего, и выйти из неё нечем. <strong>Здесь сказано, чем.</strong>')
ch3 += p('Определение, которое стало классическим, дал Бааль Сулам в статье «Суть науки каббала»:')
ch3 += srcquote('«Каббала есть не что иное, как полная и достаточная информация о порядке причинно-следственного нисхождения Высших сил, согласно постоянным и абсолютным законам, связанным между собой и направленным на достижение одной высочайшей цели — цели мироздания, определяемой как „раскрытие Творца творениям в этом мире“».', 'Бааль Сулам')
ch3 += clip('Lesson-15-clip-K4', title='«В чём ценность каббалы?»', source=ML + ', вебинар, 2017',
            quote='«Наука каббала – это методика раскрытия Творца человеку в нашем мире. Если человек желает познать высшую систему управления, скрытую от нас в нашем мире, понять, как она управляет нами, как в ответ мы можем управлять ею; если его интересует не то, что находится перед ним на этой земле, а нечто более широкое, вечное, то ему нужна методика, которая называется „каббала“».')
ch3 += h4('Из чего она состоит')
ch3 += p('<strong>В каббале три основных раздела</strong>, и в каждом говорится об одном — о постижении Общего Закона мироздания.')
ch3 += p('<strong>Первый — нисхождение миров.</strong> Как силы спускаются ступень за ступенью вплоть до нашего мира, как они управляют нами и как наши поступки влияют на них. Этим занимались уроки 11 и 12.')
ch3 += p('<strong>Второй — развитие души.</strong> Методика того, как развивается внутренняя часть человека, относящаяся к высшему миру. Сюда же относится то, что называется кругооборотами душ. Этим курс занимался в середине: душа, среда, намерение.')
ch3 += p('<strong>Третий — язык описания.</strong> Аппарат, которым каббалист описывает духовные процессы: измеряет, сравнивает, сопоставляет то, что приходит свыше, с собственной реакцией. Это гематрии — числовые записи состояний, графики и таблицы. В этом курсе он появлялся краем — в схемах четырёх стадий и парцуфа.')
ch3 += p('Разделы разные по языку и по подходу. Предмет один.')
ch3 += p('Это и есть ответ на вопрос, который мог возникнуть за пятнадцать недель: почему одни тексты выглядят как чертежи, другие как рассказы, третьи как разговор о внутренней работе. <strong>Это не разные учения. Это один предмет с трёх сторон.</strong>')
ch3 += h4('Две лестницы')
ch3 += p('Уроки 11 и 12 описывали порядок сверху вниз: от замысла через миры до нашего мира. Зачем это знать человеку внизу?')
ch3 += p('<strong>Порядок нисхождения — модель подъёма.</strong> Человек поднимается по тем же ступеням снизу вверх, повторяя все состояния и связи, через которые прошло нисхождение. Поэтому уровни постижения и называются ступенями.')
ch3 += p('Подняться на ступень, которой не ощущаешь, невозможно: нельзя желать неизвестного. Подъём возможен потому, что следующая ступень даёт ощутить своё присутствие. Возникает разница между уровнями — и понимание, что нужно сделать, чтобы подняться.')
ch3 += p('Это продолжает урок 11. Миры — это фильтры, ослабляющие ощущение мира Бесконечности; наш мир — самый плотный из них. Подъём — снятие фильтров, слой за слоем.')
ch3 += h4('На чём она основана')
ch3 += p('<strong>Каббала основана только на проверенном опытом.</strong> Теории и гипотезы она в расчёт не принимает. Весь её материал получен от людей, лично постигших высший мир, — проверивших, измеривших и описавших то, что постигли.')
ch3 += p('Отсюда правило: <strong>описывается только постигаемое практически.</strong> Того, чего каббалист не постиг, для него не существует. Поэтому в каббале нет абстрактных понятий, хотя со стороны они кажутся именно такими.')
ch3 += p('Отсюда и язык. Строение всех миров повторяет друг друга, и каждый объект нашего мира — следствие своего корня наверху. Каббалисты берут слово нашего мира — ветвь — и обозначают им корень. Это <strong>язык ветвей</strong>, на котором написаны «Книга Зоар» и «Древо жизни».')
ch3 += h4('Общий Закон')
ch3 += p('Он уже назывался в курсе, просто не этим именем: <strong>закон подобия свойств</strong> из урока 3.')
ch3 += clip('Lesson-15-clip-K5', title='«Путь достижения высшей цели»', source=ML + ', вебинар, 2019',
            quote='«Мы раскроем Его как абсолютный, четкий, высший закон, который связывает между собой все части творения. […] Только на пути к достижению этого единства, этой общей силы, общей системы, мы путаемся в различных мелких, частных силах и взаимосвязях».')
ch3 += p('Бааль Сулам называет отличие и подобие формы «главным ключом к науке»: материальные объекты сближаются и удаляются расстоянием, а духовные — подобием и отличием свойств.')
ch3 += clip('Lesson-15-clip-K6', title='«Учение Десяти Сфирот», урок 2', source=ML + ', 2022',
            quote='«У творения нет никакой возможности проявлять себя относительно Творца, как только подобием Ему. Потому что, исходя из абсолютной противоположности Творцу, единственное, что остается творению и что ему действительно нужно – прийти к подобию Творцу».')
ch3 += p('За пятнадцать недель этот закон появился четырежды, и каждый раз с новой стороны:')
ch3 += ('<ul class="kab-list">\n<li><strong>урок 3</strong> — чем можно ощутить;</li>\n<li><strong>урок 4</strong> — в какой мере наслаждаться;</li>\n'
        '<li><strong>урок 5</strong> — почему одно и то же переживается как забота или как жестокость;</li>\n<li><strong>урок 11</strong> — как появился инструмент уподобления.</li>\n</ul>\n')
ch3 += clip('Lesson-15-clip-K7', title='«Каббалистические принципы, их духовный смысл и применение. Часть 1»', source=ML + ', вебинар, 2017',
            quote='«Это абсолютно четкий закон подобия свойств, существующий во всем мире на любых условиях, на любых уровнях науки. […] Все зависит от того, как ты Его проявляешь. Так же как электричество, которое может охлаждать, греть, светить. Спектр ощущений зависит не от силы, а от того, как ты ее используешь».')
ch3 += p('<strong>Один закон, четыре поворота.</strong> Если вы это заметили по ходу — вы поняли устройство курса.')

# ---------------- Глава 4 ----------------
ch4 = h4('Вопрос')
ch4 += p('Теперь вопрос, к которому вёл курс: <strong>что дальше?</strong>')
ch4 += p('Решение ваше. Нажима здесь нет — и это принцип самой методики.')
ch4 += clip('Lesson-15-clip-K8', title='«Схема мироздания: зарождение творения и возникновение миров»', source=ML + ', вебинар, 2017',
            quote='«Я не могу советовать вам, что делать. Человек сам выбирает себе поле занятий. В каббале сказано: „Нет насилия в духовном“. Поэтому мы никого не тянем за собой, просто предлагаем нашу методику и объясняем ее».')
ch4 += p('<strong>Плохой ответ только один — тот, который не ваш.</strong>')
ch4 += h4('Что на самом деле продвигает человека')
ch4 += p('<strong>Духовное развитие человека определяется одним: находится ли он под воздействием света.</strong>')
ch4 += p('Это то, что сказано в главе 2: изменения делает свет. Насколько хорошо усвоен материал, насколько человек включён в учёбу, сколько в нём воодушевления — всё это вторично. Понимание приходит и уходит, воодушевление тоже. Свет действует.')
ch4 += p('Действует он на того, кто владеет методикой: <strong>правильно участвует в хорошем окружении — с источниками, товарищами и учителем.</strong> Это та самая среда, которую человек выбирает из четырёх факторов. Без этого человек не продвигается, сколько бы он ни понял.')
ch4 += p('<strong>Первая часть курса познакомила с основными идеями.</strong> Что такое желание и намерение, как устроено мироздание, зачем нужна среда.')
ch4 += p('<strong>Вторая часть знакомит с инструментами продвижения.</strong> Статьи Рабаша о внутренней работе. Методика налаживания связи между людьми — так, чтобы между нами раскрылся Творец. Это продолжает урок 13: предмет работы находится между людьми.')
ch4 += p('По идеям можно понять, о чём эта наука. Но работает ли методика, видно только тогда, когда пользуешься её инструментами. <strong>Поэтому выяснение, сделанное после двух частей, намного качественнее,</strong> чем после одной: человек судит уже не по идеям, а по самой методике.')
ch4 += p('Противоречия с тем, что решение ваше, здесь нет: это ответ на вопрос, когда решать.')
ch4 += h4('Если желание продолжить есть')
ch4 += p('Не нужно ждать, пока станет всё понятно, и не нужно постоянного воодушевления. Это вторично.')
ch4 += p('Если в вас есть желание продолжить, даже неясное, — это и есть то, с чем работает методика. Его стоит проверить там, где оно может проявиться: во второй части, с её инструментами, в окружении, с источниками и учителем.')
ch4 += p('Никакого экзамена, отбора и условий для перехода нет.')

# ---------------- Глава 5 (+ «Что дальше») ----------------
ch5 = p('На первой неделе мы договорились: обучение платное, стоимость вы определяете сами, платите когда захотите — в процессе курса или в конце.')
ch5 += p('<strong>Первая часть курса завершена.</strong>')
ch5 += p('<strong>Свою часть договора мы выполнили:</strong> пятнадцать недель уроков, вебинаров, семинаров и ответов на вопросы.')
ch5 += p('<strong>Теперь ваша очередь.</strong>')
ch5 += p('<strong>Сумму определяете вы</strong> — по тому, чем для вас оказались эти пятнадцать недель.')
ch5 += p('<strong>Автоматических списаний нет, доступ к материалам не закрывается.</strong> Эта модель держится на доверии — с обеих сторон.')
NEXT = '<strong>Вторая часть курса</strong> — знакомство с инструментами продвижения: статьи Рабаша, методика налаживания связи между людьми, чтобы между нами раскрылся Творец. Термины, названные и отложенные здесь, там разбираются. Условий для перехода нет. Решение — ваше.'
ch5 += h4('Что дальше')
ch5 += p(NEXT)
ch5 += p('Что бы вы ни решили — спасибо за эти пятнадцать недель. Вы прошли их сами.')

# ---------------- Резюме курса ----------------
RESUME = [
    '<b>Желания растут и наполнением не закрываются.</b> Эгоизмом называется намерение, а не желание.',
    '<b>Ощутить можно только то, чему подобен.</b> Для того, что за пределом пяти чувств, нужен шестой орган — экран.',
    '<b>Творение проходит четыре стадии</b>, и наслаждается в мере уподобления замыслу.',
    '<b>Сила одна.</b> Разное ощущение берётся от разной меры подобия ей.',
    '<b>Душа — не данность, а то, что развивают</b> собственным усилием.',
    'Из четырёх факторов человеку доступен один: <b>выбор среды.</b> Среда — это источники, товарищи и учитель.',
    '<b>Намерение меняет суть действия.</b>',
    'Знание было закрыто, пока не созрело поколение, и <b>проверяется оно не авторитетом, а совпадением с собственным наблюдением.</b>',
    '<b>Высшие миры — ступени скрытия.</b> Порядок их нисхождения — модель подъёма.',
    '<b>Целое разбито, и собрать свою часть в одиночку нельзя:</b> она проявляется только относительно других. Поэтому распространение — часть развития самого человека.',
    '<b>Материальная жизнь напрямую не улучшается.</b> Меняется то, что человек различает. Изменения опосредованные: чем больше человек в гармонии с Природой, тем меньше помех. Никаких чудес.',
    '<b>Изменения делает свет.</b> Понимание и воодушевление вторичны; свет действует на того, кто правильно участвует в окружении — с источниками, товарищами и учителем.',
    '<b>Сам учебный процесс и есть инструмент.</b>',
    '<b>Общий Закон — закон подобия свойств.</b> Он прошёл через весь курс четыре раза.',
]
resume = ('<div class="kab-page">\n<div class="kab-card">\n'
          + ''.join('<div class="kab-row"><div class="kab-dot"></div><div>%s</div></div>\n' % r for r in RESUME)
          + '</div>\n<div class="kab-quote kab-quote--purple">%s</div>\n</div>\n' % NEXT.replace('<strong>', '<b>').replace('</strong>', '</b>'))

# ---------------- Отклик недели («Задание»: один вопрос; «Если захочется» в доке — личное, не для форума) ----------------
otklik = '''<div class="kab-text">
<div class="kab-quote">Напишите своими словами.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Что изменилось за время курса в том, как вы смотрите на себя, на людей вокруг и на происходящее? Если ничего — так и напишите: это тоже ответ.</div></div>
</div>
<div class="kab-note">Отклики читаем мы. Что бы вы ни решили — спасибо за эти пятнадцать недель.</div>
</div>
'''

# ---------------- Книга «Дополнительные материалы к Уроку 15» ----------------
book_ch1 = '<div class="kab-page">\n'
book_ch1 += '<div class="kab-kicker">Лекция «Итоги курса». Преподаватель Гавриэль Шустерман</div>\n'
book_ch1 += yt('Итоги курса', 'S_SvdGYomwU', '1QmjZJ0m-LIFNJ2nuQUg35eFreDI0YOTP')
book_ch1 += '<div class="kab-kicker">Ответы на вопросы</div>\n'
book_ch1 += yt('Ответы на вопросы', 'Dq2U2Kul5Yg', '1axaUs9bhMujZfUZ9HW7ghb-QiRBENBLh')
book_ch1 += '<div class="kab-kicker">Краткое повторение изученного материала</div>\n'
book_ch1 += yt('Краткое повторение изученного материала', 'XTkJCrmK3C4', '1RqxPMN-rl-gKVWNZIOQLuYccFr9PLloK')
book_ch1 += '</div>\n'
book_ch2 = '<div class="kab-page">\n'
book_ch2 += clip('Lesson-15-clip-K9', title='«Учение Десяти Сфирот», урок 2: подобие свойств — основной закон каббалы', source=ML + ', 2022',
                 quote='«Если мы желаем Его ощущать, – пожалуйста, – измени себя со злого существа на доброе, и в мере подобия Творцу, когда ты станешь таким же добрым, как Он, ты будешь ощущать Его. Подобие свойств и есть основной закон каббалы».')
book_ch2 += '</div>\n'
BOOK = [('Лекция урока и ответы на вопросы', book_ch1),
        ('Ещё один фрагмент об Общем Законе', book_ch2)]

PAGES = {'p_toc': toc, 'p_intro': intro, 'p_ch1': ch1, 'p_ch2': ch2, 'p_ch3': ch3, 'p_ch4': ch4, 'p_ch5': ch5,
         'page_resume': resume, 'forum_otklik': otklik, 'book_ch1': book_ch1, 'book_ch2': book_ch2}

# ---------------- Тест «Проверьте себя» (quiz 1249, 7 слотов): вопросы и верные ответы из дока, разборы неверных — свои ----------------
QUESTIONS = [
    ('У15-01 Что развивает сам учебный процесс', 'Что развивает сам учебный процесс?', [
        ('Дополнительный орган ощущения', 1.0, 'Верно. Не готовит к развитию и не рассказывает о нём — развивает. Отсюда и то, что одна статья читается по-разному в разное время.'),
        ('Память', 0.0, 'Память ни при чём: термины помнить не обязательно. Учёба развивает дополнительный орган ощущения.'),
        ('Дисциплину', 0.0, 'Нет. Чтение, обсуждение, семинары действуют не только тем, что сообщают сведения, — они развивают орган ощущения.'),
        ('Знание терминов', 0.0, 'Термины вторичны. Сам учебный процесс развивает дополнительный орган ощущения.')], True),
    ('У15-02 Что даёт освоенный метод', 'Что человек получает, освоив метод?', [
        ('Возможность осознавать мир вне своего субъективного восприятия', 1.0, 'Верно. Это прямой ответ на урок 3: там сказано, что картина складывается из свойств смотрящего, здесь — чем из неё выйти.'),
        ('Управление событиями', 0.0, 'Материальная жизнь напрямую не меняется. Метод даёт восприятие вне собственного субъективного.'),
        ('Спокойствие', 0.0, 'Нет. Освоив метод, человек начинает осознавать мир вне зависимости от собственного субъективного восприятия.'),
        ('Знание будущего', 0.0, 'Не знание будущего, а добавочная информация о том, что существует в мироздании, — вне субъективного восприятия.')], True),
    ('У15-03 Разделы каббалы', 'Сколько основных разделов в каббале и о чём они?', [
        ('Три, и все об одном — о постижении Общего Закона', 1.0, 'Верно. Нисхождение миров, развитие души, язык описания. Поэтому одни тексты выглядят как чертежи, другие как рассказы.'),
        ('Три, и все о разном', 0.0, 'Разделы разные по языку и подходу, но предмет один — постижение Общего Закона мироздания.'),
        ('Один', 0.0, 'Разделов три: нисхождение миров, развитие души и язык описания. Предмет у них один.'),
        ('Их количество не определено', 0.0, 'Определено: три основных раздела, и в каждом говорится об одном — о постижении Общего Закона.')], True),
    ('У15-04 Какой закон назывался четыре раза', 'Какой закон назывался в курсе четыре раза?', [
        ('Закон подобия свойств', 1.0, 'Верно. Урок 3 — чем ощутить; урок 4 — в какой мере наслаждаться; урок 5 — почему одно переживается по-разному; урок 11 — как появился инструмент уподобления.'),
        ('Закон корня и ветви', 0.0, 'Закон корня и ветви был в уроке 10. Четыре раза через курс прошёл закон подобия свойств.'),
        ('Закон сохранения', 0.0, 'Нет. Общий Закон курса — закон подобия свойств.'),
        ('Закон причины и следствия', 0.0, 'Причинно-следственный порядок — часть определения науки, но четыре раза в курсе появлялся закон подобия свойств.')], True),
    ('У15-05 Плохой ответ', 'Какой ответ на вопрос «продолжать или нет» считается плохим?', [
        ('Тот, который не ваш', 1.0, 'Верно. Данный за компанию, из уважения или потому что неудобно уходить. «Нет» — полноценный результат.'),
        ('«Нет»', 0.0, '«Нет» — полноценный результат. Плохой ответ только один — тот, который не ваш.'),
        ('«Не знаю»', 0.0, 'Не нужно ждать, пока станет всё понятно. Плохой ответ — тот, который дан не вами.'),
        ('«Пока подожду»', 0.0, 'Решение ваше, и нажима нет. Плохой ответ только тот, который не ваш.')], True),
    ('У15-06 Что определяет духовное развитие', 'Что определяет духовное развитие человека?', [
        ('Нахождение под воздействием света', 1.0, 'Верно. Понимание, включённость и воодушевление вторичны. Свет действует на того, кто правильно участвует в окружении — с источниками, товарищами и учителем.'),
        ('Качество усвоения материала', 0.0, 'Насколько усвоен материал — вторично. Развитие определяет нахождение под воздействием света.'),
        ('Сила воодушевления', 0.0, 'Воодушевление приходит и уходит. Изменения делает свет.'),
        ('Количество прочитанного', 0.0, 'Дело не в количестве. Свет действует на того, кто правильно участвует в хорошем окружении.')], True),
    ('У15-07 Для чего вторая часть курса', 'Для чего предназначена вторая часть курса?', [
        ('Первая часть дала основные идеи, вторая даёт инструменты продвижения', 1.0, 'Верно. По идеям понятно, о чём наука; работает ли методика, видно, когда пользуешься её инструментами.'),
        ('Во второй части то же самое объясняется подробнее', 0.0, 'Нет: вторая часть знакомит с инструментами — статьями Рабаша и методикой связи между людьми.'),
        ('После второй части проводится экзамен', 0.0, 'Никакого экзамена, отбора и условий нет. Вторая часть даёт инструменты продвижения.'),
        ('Без второй части не выдают сертификат', 0.0, 'Условий для перехода нет, и дело не в сертификате: вторая часть даёт инструменты, по которым видно, работает ли методика.')], True),
]

for k, v in PAGES.items():
    assert not re.search(r'\bРав\b', v), k
    assert 'М. Лайтман' not in v, k
    assert not re.search(r'href="https?://(www\.|cdn\.)?kabbalahmedia\.info', v), k
    assert not (k.startswith('book') and 'ильм' in v), k   # no films in the additional materials; the lesson text may say the word
    open('html/%s.html' % k, 'w', encoding='utf-8', newline='\n').write(v)
print('pages:', ', '.join('%s=%d' % (k, len(v)) for k, v in PAGES.items()))

# ---------------- SQL ----------------
TZ = ZoneInfo('Asia/Jerusalem')
LESSON, CM_LESSON, CM_QUIZ, CM_RESUME, CM_OTKLIK, CM_BOOK = 1976, 13676, 13677, 13678, 13679, 13698
QUIZ, PAGE_RESUME, FORUM_OTKLIK = 1249, 2523, 3929
BOOK_ID = int(os.environ.get('BOOK_ID', '0'))
# 17970 map -> 17971 toc -> 17972 intro -> 17975 ch1 -> 17973 ch2 -> 17974 ch3 -> new ch4 -> ch5
PAGE_IDS = {'map': 17970, 'toc': 17971, 'intro': 17972, 'ch1': 17975, 'ch2': 17973, 'ch3': 17974}
LAST_FINISH = 37346   # «Завершить» on 17974 -> «Продолжить»
EXP = int(datetime(2027, 1, 13, 23, 59, tzinfo=TZ).timestamp())   # lecture, otklik, test: Wednesday 23:59
# quiz slots -> question versions -> answer ids (from the DB, 19.10.2026); slot 7 = new question 68886 (copy of 68869)
QIDS = [([68862, 68863], [[284448, 284449, 284450, 284451], [284452, 284453, 284454, 284455]]),
        ([68864], [[284456, 284457, 284458, 284459]]),
        ([68865, 68866], [[284460, 284461, 284462, 284463], [284464, 284465, 284466, 284467]]),
        ([68867], [[284468, 284469, 284470, 284471]]),
        ([68868], [[284472, 284473, 284474, 284475]]),
        ([68869], [[284476, 284477, 284478, 284479]]),
        ([68886], [[284592, 284593, 284594, 284595]])]
ALLQ = [qid for qids, _ in QIDS for qid in qids]

if '--sql' in sys.argv:
    assert BOOK_ID, 'set BOOK_ID'
    NOW = int(time.time())

    def hx(s):
        return '0x' + binascii.hexlify(s.encode('utf-8')).decode()

    def q(s):
        return "'" + s.replace(chr(92), chr(92) * 2).replace("'", "''") + "'"

    mappage = ('<p><a title="Начать" href="https://edu.kabacademy.com/mod/lesson/view.php?id=%d&amp;pageid=%d"><img class="img-fluid" src="%smaps/L15.png" alt="Карта Урок 15" width="1174" height="915"></a></p>\n'
               % (CM_LESSON, PAGE_IDS['toc'], IMG))
    open('html/p_map.html', 'w', encoding='utf-8', newline='\n').write(mappage)
    sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
    pages = [('map', '*', mappage), ('toc', 'Содержание', toc), ('intro', INTRO, intro)] + [
        ('ch%d' % i, CHAPTERS[i - 1], html) for i, html in enumerate((ch1, ch2, ch3), 1)]
    for key, title, html in pages:
        sql.append("UPDATE mdl_lesson_pages SET title=%s, contents=%s, timemodified=%d WHERE id=%d AND lessonid=%d;" % (q(title), hx(html), NOW, PAGE_IDS[key], LESSON))
    last = str(PAGE_IDS['ch3'])
    sql.append("UPDATE mdl_lesson_answers SET answer='Продолжить', jumpto=-1, timemodified=%d WHERE id=%d AND pageid=%s AND lessonid=%d;" % (NOW, LAST_FINISH, last, LESSON))
    new = list(enumerate((ch4, ch5), 4))
    for i, html in new:
        var = '@n%d' % i
        sql.append("INSERT INTO mdl_lesson_pages (lessonid,prevpageid,nextpageid,qtype,qoption,layout,display,timecreated,timemodified,title,contents,contentsformat) "
                   "SELECT lessonid,%s,0,qtype,qoption,layout,display,%d,%d,%s,%s,contentsformat FROM mdl_lesson_pages WHERE id=%d;" % (last, NOW, NOW, q(CHAPTERS[i - 1]), hx(html), PAGE_IDS['ch3']))
        sql.append("SET %s = LAST_INSERT_ID();" % var)
        sql.append("UPDATE mdl_lesson_pages SET nextpageid=%s WHERE id=%s;" % (var, last))
        fin = ("'Завершить'", -9) if i == new[-1][0] else ("'Продолжить'", -1)
        sql.append("INSERT INTO mdl_lesson_answers (lessonid,pageid,jumpto,grade,score,flags,timecreated,timemodified,answer,answerformat,response,responseformat) VALUES "
                   "(%d,%s,-40,0,0,0,%d,%d,'Вернуться',0,NULL,0),(%d,%s,%d,0,0,0,%d,%d,%s,0,NULL,0);" % (LESSON, var, NOW, NOW, LESSON, var, fin[1], NOW, NOW, fin[0]))
        last = var
    sql.append("UPDATE mdl_lesson SET timemodified=%d WHERE id=%d;" % (NOW, LESSON))
    for (name, text, answers, single), (qids, aidss) in zip(QUESTIONS, QIDS):
        assert abs(sum(f for _, f, _ in answers if f > 0) - 1) < 1e-6, name
        for qid, aids in zip(qids, aidss):
            assert len(aids) == len(answers) == 4
            sql.append("UPDATE mdl_question SET name=%s, questiontext=%s, timemodified=%d WHERE id=%d;" % (q(name), hx('<p>%s</p>\n' % text), NOW, qid))
            sql.append("UPDATE mdl_qtype_multichoice_options SET single=%d WHERE questionid=%d;" % (1 if single else 0, qid))
            for (atext, frac, fb), aid in zip(answers, aids):
                sql.append("UPDATE mdl_question_answers SET answer=%s, fraction=%s, feedback=%s WHERE id=%d AND question=%d;" % (hx('<p>%s</p>\n' % atext), repr(frac), hx('<p>%s</p>\n' % fb), aid, qid))
    sql.append("UPDATE mdl_quiz SET timemodified=%d WHERE id=%d;" % (NOW, QUIZ))
    sql.append("UPDATE mdl_page SET content=%s, timemodified=%d WHERE id=%d;" % (hx(resume), NOW, PAGE_RESUME))
    sql.append("UPDATE mdl_forum SET intro=%s, timemodified=%d WHERE id=%d;" % (hx(otklik), NOW, FORUM_OTKLIK))
    sql.append("UPDATE mdl_course_modules SET visible=1, visibleold=1 WHERE id IN (%d,%d,%d);" % (CM_LESSON, CM_QUIZ, CM_RESUME))
    sql.append("UPDATE mdl_course_modules SET completionexpected=%d WHERE id IN (%d,%d,%d);" % (EXP, CM_LESSON, CM_OTKLIK, CM_QUIZ))
    for mod, inst in (('lesson', LESSON), ('forum', FORUM_OTKLIK), ('quiz', QUIZ)):
        sql.append("UPDATE mdl_event SET timestart=%d, timesort=%d, visible=1, timemodified=%d WHERE courseid=236 AND modulename='%s' AND instance=%d AND eventtype='expectcompletionon';" % (EXP, EXP, NOW, mod, inst))
    sql.append("UPDATE mdl_event SET visible=1, timemodified=%d WHERE courseid=236 AND modulename='lesson' AND instance=%d AND eventtype='open';" % (NOW, LESSON))
    sql.append("UPDATE mdl_book SET name='Дополнительные материалы к Уроку 15', revision=revision+1, timemodified=%d WHERE id=%d;" % (NOW, BOOK_ID))
    sql.append("DELETE FROM mdl_book_chapters WHERE bookid=%d;" % BOOK_ID)
    for i, (title, html) in enumerate(BOOK, 1):
        sql.append("INSERT INTO mdl_book_chapters (bookid,pagenum,subchapter,title,content,contentformat,hidden,timecreated,timemodified,importsrc) "
                   "VALUES (%d,%d,0,%s,%s,1,0,%d,%d,'');" % (BOOK_ID, i, q(title), hx(html), NOW, NOW))
    sql.append("UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;")
    sql += ['COMMIT;',
            "SELECT id,prevpageid,nextpageid,title,LENGTH(contents) clen FROM mdl_lesson_pages WHERE lessonid=%d ORDER BY id;" % LESSON,
            "SELECT pageid,answer,jumpto FROM mdl_lesson_answers WHERE lessonid=%d ORDER BY pageid,id;" % LESSON,
            "SELECT question,COUNT(*) n,SUM(fraction) s,SUM(GREATEST(fraction,0)) pos FROM mdl_question_answers WHERE question IN (%s) GROUP BY question;" % ','.join(map(str, ALLQ)),
            "SELECT q.id,q.name,o.single FROM mdl_question q JOIN mdl_qtype_multichoice_options o ON o.questionid=q.id WHERE q.id IN (%s);" % ','.join(map(str, ALLQ)),
            "SELECT id,visible,completionexpected FROM mdl_course_modules WHERE id IN (%d,%d,%d,%d,%d);" % (CM_LESSON, CM_QUIZ, CM_RESUME, CM_OTKLIK, CM_BOOK),
            "SELECT id,name,modulename,instance,eventtype,FROM_UNIXTIME(timestart),visible FROM mdl_event WHERE courseid=236 AND ((modulename='lesson' AND instance=%d) OR (modulename='quiz' AND instance=%d) OR (modulename='forum' AND instance=%d));" % (LESSON, QUIZ, FORUM_OTKLIK),
            "SELECT id,pagenum,title,LENGTH(content) FROM mdl_book_chapters WHERE bookid=%d ORDER BY pagenum;" % BOOK_ID]
    open('lesson15.sql', 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
    print('sql statements:', len(sql), 'bytes:', os.path.getsize('lesson15.sql'), 'EXP', EXP)
