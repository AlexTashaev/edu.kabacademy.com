# -*- coding: utf-8 -*-
"""Lesson 13 «Духовный подъем», course 236 — text of the doc «Урок 13 — СО финал» (20.10.2026, 1C9de6vFwKpAhc9SeQ3B0y9be50DCR3WoLja1FZacwL0;
the doc calls the theme «Невозможно подняться в одиночку» / «Распространение каббалы в мире», the course keeps «Духовный подъем»)
in the lesson 1–2 / 6–12 format: map -> contents (+ three questions) -> «О чём этот урок» (the doc's «Вступление» text) -> chapters 1–5
(+ «Что с этим делать» in chapter 5); quiz «Проверьте себя» — all seven questions of the doc (slot 7 added to quiz 1247); résumé;
otklik («Задание»: two questions + «Если захочется»); book «Дополнительные материалы к Уроку 13» (lectures, «Основы каббалы»,
the doc's «Факультатив» — «Последнее поколение» — as the two archive programs on the article).
In-text videos: sixteen fragments cut from archive programs (cut13.sh; windows from «Урок 13 — видеоматериалы» where it has them,
otherwise found by the quote) and three whole archive clips (copied as is). No designer's schemes for this lesson (none delivered).
Everything is due on Wednesday 23:59.

Writes html/*.html; with --sql (and BOOK_ID=979) writes lesson13.sql.
Not taken from the doc: «Как устроен этот урок», «Повторение пройденного» (its recap video is in the book), «Итоговый тест» of the
spring course (10 questions, incl. multi/gapselect), links to kabbalahmedia.info.
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
CHAPTERS = ['Глава 1. Почему один не поднимается', 'Глава 2. Распространение — часть развития самого человека', 'Глава 3. Внутреннее распространение',
            'Глава 4. Внешнее распространение', 'Глава 5. Зачем это названо целью']
ML = LAITMAN
# the host's words that Michael Laitman answers (checked by the frames of the recording): a dialogue, not a quote of his
DIALOG = '<b>Ведущий:</b> %s<br><b>' + LAITMAN + ':</b> %s'

# ---------------- Содержание ----------------
toc = ('<div class="kab-text">\n<div class="kab-quote">\n<ul class="kab-list">\n'
       + ''.join('<li>%s</li>\n' % c for c in [INTRO] + CHAPTERS) +
       '''</ul>
</div>
<div class="kab-kicker">Прежде чем читать</div>
<p>Три вопроса. Держите их в голове, пока читаете.</p>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Есть ли что-то, чего человек в принципе не может сделать один?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Если связь с другими нужна для собственного продвижения — это ещё отдача или уже расчёт?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Что меняется в вас, когда рядом есть кто-то, кому то же самое важно?</div></div>
</div>
</div>
<p></p>
''')

# ---------------- О чём этот урок («Вступление» дока; видеолекция — в книге) ----------------
intro = ''   # the page title «О чём этот урок» is already shown by the lesson page — no heading inside (owner, 20.10.2026)
intro += p('Двенадцать недель в курсе повторялась одна мысль, каждый раз с новой стороны: новый орган чувств проверяется только на других (урок 3), группа — место, где методика применяется (урок 8), «Книга Зоар» написана из соединения (урок 10), своя часть проявляется только относительно других (урок 12).')
intro += p('<strong>Этот урок — о распространении каббалы в мире: почему оно часть духовного развития самого человека.</strong>')
intro += srcquote('«Абсолютная обязанность возложена на каждого – заниматься каббалой, без которой человек не достигнет цели своего творения».', 'Бааль Сулам, «Предисловие к книге „Уста мудрого“»')
intro += p('<strong>Цель распространения науки каббала – это раскрытие Творца творениям в этом мире.</strong>')

# ---------------- Глава 1 ----------------
ch1 = h4('Связь находится между')
ch1 += p('В уроке 12 было сказано: единое целое разделилось на части, и части друг друга не ощущают. Каждый может исправить только свою часть — и обнаруживает её только относительно других.')
ch1 += p('<strong>Связь по определению находится между.</strong> Изнутри одной части её не собрать, сколько бы усилий ни прилагать. Поэтому в одиночку человек не поднимается — не потому, что трудно, а потому, что предмет работы находится не внутри него.')
ch1 += clip('Lesson-13-clip-K1', title='«Почему каббалу не изучают в одиночку?»', source=ML + ', вебинар, 2016',
            quote='«Почему я не ощущаю высший свет? — Потому что вы не соединяетесь ни с кем в систему, которая может служить сенсором для высшего света. Этот сенсор – десятка».')
ch1 += h4('Это не взаимопомощь')
ch1 += p('<strong>Взаимопомощь — когда каждый мог бы справиться сам, но вместе легче и быстрее.</strong> Здесь не так. Один не справится в принципе, ни за какое время, потому что то, что нужно вырастить, существует только в промежутке между людьми.')
ch1 += p('Каждый по-прежнему отвечает за свою часть. Но обнаруживается она там.')
ch1 += h4('А великие каббалисты?')
ch1 += clip('Lesson-13-clip-K2', title='«Почему каббалу не изучают в одиночку?»', source=ML + ', вебинар, 2016',
            quote='«Во-первых, не сравнивайте себя с Ари, Рамхалем, Бааль Суламом. Есть особые души, которые являются в этот мир, поскольку занимают в общей душе определённые места, как, допустим, сердце, лёгкие, печень в теле человека».')

# ---------------- Глава 2 ----------------
ch2 = h4('Отдачу нельзя только изучать')
ch2 += p('Тринадцать недель мы изучаем свойство отдачи. Но свойство, которое только изучают, не приобретается. <strong>Отдачу надо практиковать</strong> — и для этого нужно, кому отдавать.')
ch2 += clip('Lesson-13-clip-K3', title='«Почему жажда духовного познания пробуждается в наши дни?»', source=ML + ', вебинар, 2016',
            quote='«Как тренировать отдачу в повседневной жизни? Хочется больше практики. — Выходи и распространяй, встречайся с товарищами, и вместе делайте все, чтобы вы смогли объединиться».')
ch2 += h4('Получил — передай')
ch2 += p('Если человек что-то получил — знания, воодушевление, смысл существования, — естественное продолжение этого — передать. Распространение не добавка к учёбе, а часть того же процесса.')
ch2 += clip('Lesson-13-clip-K4', title='«Основы каббалы. Вера выше знания»', source=ML + ', 2019',
            quote='«Очень важно распространение. Если человек занимается распространением этих знаний на любом уровне, то это перекрывает его личную духовную работу. О ней позаботится Творец. Главное – что человек распространяет имя Творца в мире, настоящий подход к Нему».')
ch2 += h4('Я завишу от всех')
ch2 += p('Весь мир связан. Я завишу от всех — от кого-то больше, от кого-то меньше, но от всех.')
ch2 += clip('Lesson-13-clip-K5', title='«Почувствовать зависимость друг от друга»', source=ML + ', вебинар, 2020',
            quote='«Каждый должен быть в ответе за других, потому что мы являемся частичками единой интегральной системы. И поэтому если в этой системе что-то не в порядке, то и во мне обязательно что-то испорчено. Никуда не денешься».')
ch2 += h4('Личного исправления недостаточно')
ch2 += p('Поэтому даже тот, кто достиг личного исправления, всё равно нуждается в исправлении общего. Бааль Сулам называет это законом поручительства, и он охватывает весь мир.')
ch2 += clip('Lesson-13-clip-K6', title='«Основы каббалы. Круг ответственности и суть нашей связи»', source=ML + ', 2018',
            quote='«Ни один народ, ни одна группа людей не сможет достичь [исправления] в своем существовании, если не будет поддержки и полного участия всех остальных живущих на Земле».')
ch2 += h4('Ценность, а не призыв')
ch2 += p('Здесь говорится о ценности распространения, а не о том, что надо немедленно что-то делать. <strong>Наоборот: вам самим ничего предпринимать не надо.</strong> Посоветуйтесь — или не делайте ничего. Всем, кто захочет участвовать в распространении, подскажут и помогут.')

# ---------------- Глава 3 ----------------
ch3 = p('<strong>Внутреннее распространение – это усилия объединиться с остальными и тем самым вызвать воздействие сил, которые могут нас изменить.</strong> Это распространение на уровне сил и мыслей, которыми мы изначально связаны в системе Адам Ришон.')
ch3 += p('Обратите внимание на порядок. Сначала усилие, потом воздействие. Не «сначала изменюсь, потом смогу объединяться».')
ch3 += clip('Lesson-13-clip-K7', title='«Видеоконференция с Москвой, Петербургом, Киевом»', source=ML + ', 2017',
            quote='«Надо развивать между собой только хорошие состояния и максимально поддерживать друг друга, когда каждый из десятки забывает о себе и думает лишь о других. В той мере, в которой ты толкаешь их к цели, ты сам двигаешься к ней. […] Я говорю это как учитель, поскольку и мое личное продвижение в каббале зависит от того, насколько удачно я толкаю вас вперед».')
ch3 += h4('Почему это работает')
ch3 += p('Мы связаны между собой изначально — той самой системой, которая в уроке 12 названа Адам Ришон. Связь никуда не девалась, её просто не ощущают. <strong>Усилие объединиться — это не создание связи, а обращение к той, что уже есть.</strong>')
ch3 += clip('Lesson-13-clip-K8', title='«Духовные состояния. Тайна числа десять»', source=ML + ', 2022',
            quote=DIALOG % ('«То есть настроиться на Творца, на эту волну, на эту силу можно только когда есть чёткое взаимодействие с этими десятью людьми? Как радио — ловлю волну?»',
                            '«Как вот эти маятнички — когда они начинают вместе качаться».'))
ch3 += p('Объединившись, десять человек получают намного большую мощность, чем каждый из них. Добавляется даже то, что раньше их разделяло: отторжение, над которым они поднимаются, начинает работать на сближение.')
ch3 += clip('Lesson-13-clip-K9', title='«Коммуникативные навыки. Коллективный интеллект»', source=ML + ', 2020')
ch3 += h4('Что это значит практически')
ch3 += p('Ничего героического. Это работа в учебной группе, на семинарах, в обсуждениях — там, где есть люди, которым важно то же самое. Не потому, что они приятные, и не потому, что вместе веселее. <strong>Потому что это место, где эта работа происходит.</strong>')

# ---------------- Глава 4 ----------------
ch4 = p('<strong>Внешнее распространение – создание окружения, которое будет примером для правильных отношений между людьми.</strong> Это создание материалов, статей, книг, видео, фильмов для широкого распространения. И каждый человек в мере того, насколько его пробуждает изнутри, вдруг незаметно для себя читает статью, смотрит нужное видео, слышит передачу.')
ch4 += clip('Lesson-13-clip-K10', ar='4 / 3', maxw=640, title='«Тайны вечной книги». Глава «Эмор», часть 2', source=ML + ', 2014',
            quote='«Нельзя никак расти на своей ступени, не развивая более низкую ступень. В этом и есть твое развитие – всегда, потому что проходит по цепочке: сверху-вниз и снизу-вверх».')
ch4 += h4('Что предъявляется')
ch4 += p('Ключевое слово — <strong>пример</strong>. Человек видит не то, что ему говорят, а то, как ведут себя между собой люди, которые чему-то учатся.')
ch4 += clip('Lesson-13-clip-K11', title='«Последнее поколение. Развитие точки в сердце»', source=ML + ', 2017',
            quote='«Чтобы правильно по ней работать, он должен войти в группу, которая была бы ему приятна по земным признакам и одновременно занималась духовным развитием. Даже если у него нет никаких побуждений к духовному развитию, но будучи вместе с ними, он сможет потихоньку „заразиться“ этим вирусом».')
ch4 += h4('Без давления')
ch4 += clip('Lesson-13-clip-K12', title='«Каббала и эзотерика»', source=ML + ', 2018',
            quote='«Нет давления в духовном. Ни в коем случае не давить. Человек, который начинает ощущать, о чём говорит каббала, сам приходит к её изучению».')
ch4 += p('Это продолжает урок 6: готовность видно по одному признаку — человек начинает искать сам.')
ch4 += h4('Три основы у Бааль Сулама')
ch4 += p('В статье «Последнее поколение» Бааль Сулам называет три основы для распространения науки каббала в обществе.')
ch4 += clip('Lesson-13-clip-K13', title='«Последнее поколение. Распространение религии Последнего поколения»', source=ML + ', 2017',
            quote=DIALOG % ('«В каждом человеке существует стремление к единению с Творцом».',
                            '«Да, потому что в нашем корне все мы были с Ним связаны. Поэтому, даже разбившись и удалившись друг от друга — и от Творца в той же мере, — мы всё равно испытываем необходимость вернуться».'))
ch4 += p('<strong>Первая — удовлетворение страстей.</strong>')
ch4 += srcquote('«У каждого человека есть неизведанная искра, требующая единения с Творцом. Пробуждаясь, время от времени, она будит в нем страсть к познанию Творца или к Его отрицанию, что одно и то же. И если найдет человек того, кто вызовет в нем удовлетворение этой страсти, то согласится на все».', 'Бааль Сулам, «Последнее поколение»')
ch4 += p('<em>Пример.</em> Человек приходит к этому по-разному: может задуматься о смысле жизни, может пройти через большие страдания, это может быть просто ощущение смерти. Какой смысл вкладывать в эту столь короткую жизнь, если кроме этого ничего нет? Задача — чтобы в этот момент рядом оказался ответ: статья, книга, фильм.')
ch4 += p('<strong>Вторая — доказательства:</strong> «того, что нет миру иного существования, а тем более во времена атомных и водородных бомб».')
ch4 += clip('Lesson-13-clip-K14', title='«Основы каббалы. Распространение каббалы в мире»', source=ML + ', 2019',
            quote='«Это значит, что если мы не сделаем исправление, то миру придет конец? — Да. Творец будет постепенно подгонять нас к Своему раскрытию все более драматическими состояниями».')
ch4 += p('<em>Пример.</em> Мир стал глобальным: экономически, информационно все государства взаимозависимы. И единственное, чего ему не хватает, — правильной связи между людьми.')
ch4 += clip('Lesson-13-clip-K15', title='«Ядерный мир»', source=ML + ', клип, 2018',
            quote='«Для того чтобы человечество отрезвело и поняло, что, в принципе, надо что-то делать с самим собой. Не с оружием и не с кем-то чужим, а с самим собой».')
ch4 += p('<strong>Третья — агитация</strong>, а по сути воспитание. «Агитация необходима, чтобы вызвать в человеке страсть или страх, если он их не испытывает».')
ch4 += clip('Lesson-13-clip-K16', title='«Последнее поколение. Распространение религии Последнего поколения»', source=ML + ', 2017',
            quote='«Агитация и пропаганда – это очень важные инструменты влияния на человека, на его психологию, на его подсознание. Но пропаганда должна идти через множество каналов: телевидение, кино, распространение, обучение в школах и других просветительных учреждениях».')
ch4 += clip('Lesson-13-clip-K17', title='«Никакой перспективы в войне нет»', source=ML + ', клип, 2022',
            quote='«Самое главное – показать человеку перспективу. […] Воспитательный процесс заключается в том, чтобы заранее показать человеку, к чему это может привести».')
ch4 += p('<em>Пример.</em> На протяжении двадцати лет шла рекламная кампания о том, что курение вредит здоровью, — и сегодня курящих намного меньше. Хороший фильм может изменить мировоззрение миллионов. Агитация — это профилактика: показать человеку его более продвинутые состояния еще до того, как он в них чувственно войдет.')
ch4 += h4('О чём рассказывать')
ch4 += p('Темы — те, что мы проходили: насколько все человечество является единым организмом и насколько мы взаимосвязаны; природа человека и общества; восприятие реальности; где точка свободы выбора; влияние общества на человека.')
ch4 += h4('Почему именно сейчас')
ch4 += p('Скрытие держалось на трёх причинах: не было необходимости, это было невозможно, и тайна Творца. Сегодня нет необходимости становится необходимым, и невозможное становится возможным. Бааль Сулам пишет, что получил такие знания и качества сверху, чтобы у него появилась возможность всё это расшифровать.')

# ---------------- Глава 5 (+ «Что с этим делать», «Что дальше») ----------------
ch5 = p('Цель сформулирована крупно: <strong>раскрытие Творца творениям в этом мире.</strong> Фраза звучит как задача мирового масштаба, и от неё хочется отойти: моё-то дело маленькое.')
ch5 += p('Но если вспомнить урок 5, она становится буквальной: ощущение Творца — это ощущение нашей внутренней связи между собой.')
ch5 += clip('Lesson-13-clip-K18', title='«Духовные состояния. Имена Творца»', source=ML + ', 2022',
            quote='«Невозможно раскрыть Творца, если ты не объединяешься с другими людьми, иначе Он просто не проявляется. — Где проявляется Творец? Где его искать? — Между людьми».')
ch5 += p('<strong>«Раскрытие Творца творениям» и «связь между людьми, в которой это раскрывается» — одно и то же событие</strong>, названное с двух сторон.')
ch5 += h4('Четвёртое средство')
ch5 += p('Для духовного продвижения должна быть группа единомышленников, учитель и первоисточники — это урок 8. В наше время к ним добавляется четвёртое: <strong>распространение</strong>. Это и место, и средство для объединения с товарищами.')
ch5 += clip('Lesson-13-clip-K19', title='«Основы каббалы. Распространение каббалы в мире»', source=ML + ', 2019',
            quote='«Сегодня мы можем сделать Творцу большой подарок – распространить всем людям методику Его постижения, Его природу».')
ch5 += h4('Что с этим делать')
ch5 += p('<strong>Первое. Проверить формулировку, с которой вы живёте.</strong> «Мне надо заниматься, а группа — это дополнительно» и «работа происходит там» — разные установки, и они дают разный результат.')
ch5 += p('<strong>Второе. Ничего не предпринимать самим.</strong> Этот урок о ценности распространения, а не призыв к действию. Если захочется участвовать — посоветуйтесь с преподавателем. Всем, кто захочет, подскажут и помогут. А если не захочется — не делайте ничего.')
ch5 += p('<strong>Третье. Без давления на близких.</strong> Распространение — это пример, а не уговоры. Кто готов, начнёт искать сам.')
NEXT = 'На предпоследней неделе — <strong>духовное и материальное: что меняется.</strong> Что в обычной жизни человека меняется от всего этого, а что остаётся как было.'
ch5 += h4('Что дальше')
ch5 += p(NEXT)

# ---------------- Резюме ----------------
RESUME = [
    '<b>Цель распространения науки каббала – это раскрытие Творца творениям в этом мире.</b>',
    '<b>В одиночку человек не поднимается</b> — предмет работы находится между людьми, а не внутри одного. Это не взаимопомощь: здесь один не справится в принципе.',
    '<b>Внутреннее распространение – это усилия объединиться с остальными и тем самым вызвать воздействие сил, которые могут нас изменить.</b> Это распространение на уровне сил и мыслей, которыми мы изначально связаны в системе Адам Ришон.',
    '<b>Распространение — часть развития самого человека.</b> Отдачу нельзя только изучать, её надо практиковать. Получил знания, воодушевление, смысл — передай. Я завишу от всех, и даже достигнув личного исправления, все нуждаются в исправлении общего.',
    '<b>Это ценность, а не призыв к действию.</b> Самим ничего предпринимать не надо: посоветуйтесь или не делайте ничего. Кто захочет участвовать — тому подскажут и помогут.',
    '<b>Внешнее распространение – создание окружения, которое будет примером для правильных отношений между людьми.</b> Без давления: кто готов, ищет сам.',
    '<b>Три основы для распространения науки каббала у Бааль Сулама</b> – удовлетворение страстей, доказательства, агитация.',
    'Нам необходимо сделать человека интегральным: он должен понимать, в каком мире он живет, и тогда по-другому будет относиться к другим людям и к тому короткому отрезку времени, который проживает здесь.',
    '<b>Распространение — четвёртое средство духовного продвижения</b> наряду с книгами, группой и учителем.',
]
resume = ('<div class="kab-page">\n<div class="kab-card">\n'
          + ''.join('<div class="kab-row"><div class="kab-dot"></div><div>%s</div></div>\n' % r for r in RESUME)
          + '</div>\n<div class="kab-quote kab-quote--purple">%s</div>\n</div>\n' % NEXT.replace('<strong>', '<b>').replace('</strong>', '</b>'))

# ---------------- Отклик недели («Задание»: два вопроса + «Если захочется») ----------------
otklik = '''<div class="kab-text">
<div class="kab-quote">Напишите своими словами ответ на любой из вопросов.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Чем для вас важно распространение?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Какое распространение вы считаете наиболее эффективным?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Вспомните случай, когда рядом оказался человек, которому важно то же, что вам, — и что-то в вас от этого сдвинулось. Напишите одной фразой.</div></div>
</div>
<div class="kab-note">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
</div>
'''

# ---------------- Книга «Дополнительные материалы к Уроку 13» ----------------
book_ch1 = '<div class="kab-page">\n'
book_ch1 += '<div class="kab-kicker">Вступление: видеолекция урока «Основы распространения». Преподаватель Михаил Санилевич</div>\n'
book_ch1 += yt('Основы распространения', 'DcWBsPVvtSU', '1ddwow1w6umZ_wFdn_AOInG5AFxoH_Izx')
book_ch1 += '<div class="kab-kicker">Лекция урока по частям. Преподаватель Михаил Санилевич</div>\n'
book_ch1 += yt('Темы распространения', '80L_9jRihvQ', '1uZ-5TyLoR3rEWOHrOTmyo3tOqvQPDhjv')
book_ch1 += yt('Важность раскрытия каббалы', 'U7hUXYfcrYw', '1OCO8sNdvBJPmrEpYs6LOCuIuk_Hsav3F')
book_ch1 += yt('Внутреннее и внешнее распространение', 'b2CIXfEgW4o', '16Zs4T9DBxOxwMlfrSko9VLraER9US90c')
book_ch1 += '<div class="kab-kicker">Краткое повторение изученного материала</div>\n'
book_ch1 += yt('Краткое повторение изученного материала', 'WZWg1XA3eCA', '16z7VnuvrNPmRfSXPbqLzW680sZZsdsSQ')
book_ch1 += '<div class="kab-kicker">Клип «Распространение каббалы в мире»</div>\n'
book_ch1 += yt('Распространение каббалы в мире', 'DHNdbjYKLh8')
book_ch1 += '</div>\n'
book_ch2 = '<div class="kab-page">\n' + embed('programs', 'mFOcVIfb', '«Основы каббалы. Распространение каббалы в мире»', ML + ', 2019, 31 мин.', last=True) + '</div>\n'
book_ch3 = '<div class="kab-page">\n'
book_ch3 += '<p>Статья, из которой взяты три основы распространения. Передачи цикла «Последнее поколение» по ней:</p>\n'
book_ch3 += embed('programs', 'KJIIBkPo', '«Последнее поколение. Распространение религии Последнего поколения»', ML + ', 2017.')
book_ch3 += embed('programs', 'nFUHk9MT', '«Последнее поколение. Развитие точки в сердце»', ML + ', 2017.', last=True)
book_ch3 += '</div>\n'
BOOK = [('Лекции урока в записи', book_ch1),
        ('«Основы каббалы. Распространение каббалы в мире» (2019)', book_ch2),
        ('Бааль Сулам, «Последнее поколение»', book_ch3)]

PAGES = {'p_toc': toc, 'p_intro': intro, 'p_ch1': ch1, 'p_ch2': ch2, 'p_ch3': ch3, 'p_ch4': ch4, 'p_ch5': ch5,
         'page_resume': resume, 'forum_otklik': otklik, 'book_ch1': book_ch1, 'book_ch2': book_ch2, 'book_ch3': book_ch3}

# ---------------- Тест «Проверьте себя» (quiz 1247, 7 слотов): вопросы и верные ответы из дока, разборы неверных — свои ----------------
QUESTIONS = [
    ('У13-01 Почему человек не поднимается в одиночку', 'Почему человек не поднимается в одиночку?', [
        ('Предмет работы находится не внутри него', 1.0, 'Верно. Предмет работы — связь, а она находится между.'),
        ('Не хватает сил', 0.0, 'Дело не в силах: то, что нужно вырастить, существует только между людьми.'),
        ('Нет мотивации', 0.0, 'Мотивация ни при чём. Связь по определению находится между, изнутри одной части её не собрать.'),
        ('Нужен контроль', 0.0, 'Нет. Предмет работы — связь между людьми, а не контроль над человеком.')], True),
    ('У13-02 Чем это отличается от взаимопомощи', 'Чем это отличается от взаимопомощи?', [
        ('При взаимопомощи каждый мог бы справиться сам', 1.0, 'Верно. Здесь один не справится в принципе.'),
        ('Ничем', 0.0, 'Отличие есть: при взаимопомощи каждый мог бы справиться сам, здесь один не справится в принципе.'),
        ('Взаимопомощь надёжнее', 0.0, 'Дело не в надёжности. Взаимопомощь — когда вместе легче, а здесь в одиночку невозможно вообще.'),
        ('Здесь помогают только новичкам', 0.0, 'Нет. Каждый отвечает за свою часть, но обнаруживается она только относительно других.')], True),
    ('У13-03 Что такое внутреннее распространение', 'Что называется внутренним распространением?', [
        ('Усилия объединиться с остальными', 1.0, 'Верно. Сначала усилие, потом воздействие сил, которые способны изменить.'),
        ('Работа наедине с собой', 0.0, 'Наоборот: внутреннее распространение — усилия объединиться с остальными.'),
        ('Чтение первоисточников', 0.0, 'Чтение — одно из средств, но внутреннее распространение — это усилия объединиться с остальными.'),
        ('Размышление', 0.0, 'Нет. Это усилия объединиться с остальными, чтобы вызвать воздействие сил, которые могут нас изменить.')], True),
    ('У13-04 Усилие объединиться', 'Усилие объединиться создаёт связь или обращается к существующей?', [
        ('Обращается к существующей', 1.0, 'Верно. Мы связаны изначально, в системе Адам Ришон.'),
        ('Создаёт новую', 0.0, 'Связь никуда не девалась, её просто не ощущают. Усилие обращается к той, что уже есть.'),
        ('Заменяет старую', 0.0, 'Нет. Связь одна, изначальная — система Адам Ришон; усилие обращается к ней.'),
        ('Ни то, ни другое', 0.0, 'Усилие объединиться обращается к связи, которая уже есть, — в системе Адам Ришон.')], True),
    ('У13-05 Что предъявляется во внешнем распространении', 'Что предъявляется во внешнем распространении?', [
        ('Пример отношений между людьми', 1.0, 'Верно. «Нет давления в духовном».'),
        ('Давление', 0.0, '«Нет давления в духовном. Ни в коем случае не давить». Предъявляется пример отношений.'),
        ('Доказательства превосходства', 0.0, 'Человек видит не то, что ему говорят, а то, как ведут себя между собой люди, которые чему-то учатся.'),
        ('Личный успех', 0.0, 'Нет. Предъявляется пример отношений между людьми, а не личный успех.')], True),
    ('У13-06 Почему распространение — часть развития', 'Почему распространение — часть духовного развития самого человека?', [
        ('Отдачу нельзя только изучать, её надо практиковать', 1.0, 'Верно. Получил — передай; я завишу от всех, и даже личного исправления недостаточно без исправления общего.'),
        ('Это обязательная норма', 0.0, 'Это не норма, а ценность: самим ничего предпринимать не надо. Причина — отдачу надо практиковать.'),
        ('Так быстрее учиться', 0.0, 'Дело не в скорости. Свойство, которое только изучают, не приобретается — его надо практиковать.'),
        ('Это нужно только учителям', 0.0, 'Нет. Отдачу надо практиковать каждому, и для этого нужно, кому отдавать.')], True),
    ('У13-07 Раскрытие Творца творениям', 'Что означает «раскрытие Творца творениям»?', [
        ('То же, что связь между людьми, названное с другой стороны', 1.0, 'Верно. «Где проявляется Творец? — Между людьми».'),
        ('Массовое просвещение', 0.0, 'Ощущение Творца — это ощущение нашей внутренней связи между собой, а не просвещение.'),
        ('Чудо', 0.0, 'Нет. Это одно и то же событие со связью между людьми, названное с другой стороны.'),
        ('Конец пути', 0.0, 'Это не конец пути: раскрытие происходит в связи между людьми там, где они есть.')], True),
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
LESSON, CM_LESSON, CM_QUIZ, CM_RESUME, CM_OTKLIK, CM_BOOK = 1974, 13646, 13647, 13648, 13649, 13696
QUIZ, PAGE_RESUME, FORUM_OTKLIK = 1247, 2517, 3925
BOOK_ID = int(os.environ.get('BOOK_ID', '0'))
# 17958 map -> 17959 toc -> 17960 intro -> 17963 ch1 -> 17961 ch2 -> 17962 ch3 -> new ch4 -> ch5
PAGE_IDS = {'map': 17958, 'toc': 17959, 'intro': 17960, 'ch1': 17963, 'ch2': 17961, 'ch3': 17962}
LAST_FINISH = 37328   # «Завершить» on 17962 -> «Продолжить»
EXP = int(datetime(2026, 12, 30, 23, 59, tzinfo=TZ).timestamp())   # lecture, otklik, test: Wednesday 23:59
# quiz slots -> question versions -> answer ids (from the DB, 19.10.2026); slot 7 = new question 68885 (copy of 68853)
QIDS = [([68846, 68847], [[284384, 284385, 284386, 284387], [284388, 284389, 284390, 284391]]),
        ([68848], [[284392, 284393, 284394, 284395]]),
        ([68849, 68850], [[284396, 284397, 284398, 284399], [284400, 284401, 284402, 284403]]),
        ([68851], [[284404, 284405, 284406, 284407]]),
        ([68852], [[284408, 284409, 284410, 284411]]),
        ([68853], [[284412, 284413, 284414, 284415]]),
        ([68885], [[284585, 284586, 284587, 284588]])]
ALLQ = [qid for qids, _ in QIDS for qid in qids]

if '--sql' in sys.argv:
    assert BOOK_ID, 'set BOOK_ID'
    NOW = int(time.time())

    def hx(s):
        return '0x' + binascii.hexlify(s.encode('utf-8')).decode()

    def q(s):
        return "'" + s.replace(chr(92), chr(92) * 2).replace("'", "''") + "'"

    mappage = ('<p><a title="Начать" href="https://edu.kabacademy.com/mod/lesson/view.php?id=%d&amp;pageid=%d"><img class="img-fluid" src="%smaps/L13.png" alt="Карта Урок 13" width="1174" height="915"></a></p>\n'
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
    sql.append("UPDATE mdl_book SET name='Дополнительные материалы к Уроку 13', revision=revision+1, timemodified=%d WHERE id=%d;" % (NOW, BOOK_ID))
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
    open('lesson13.sql', 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
    print('sql statements:', len(sql), 'bytes:', os.path.getsize('lesson13.sql'), 'EXP', EXP)
