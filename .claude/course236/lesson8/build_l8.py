# -*- coding: utf-8 -*-
"""Lesson 8 «Факторы развития» (doc theme «Окружение как инструмент»), course 236 — text of the doc «Урок 8 — СО финал»
(17.10.2026, 10zCpp6selr5pCkZTyzIcBNnWzudvo-oXq6S41DH3Cqo) in the lesson 1–2 / 6–7 format: map -> contents (+ three questions)
-> «О чём этот урок» (the doc's «Вступление» text) -> chapters 1–4 (+ «Что с этим делать» at the end of chapter 4);
quiz «Проверьте себя» — all eight questions of the doc (slots 7–8 added to quiz 1242); résumé; otklik (two questions);
book «Дополнительные материалы к Уроку 8». Everything is due on Wednesday 23:59 (owner, 18.10.2026).

Writes html/*.html; with --sql writes lesson8.sql (book 974 = cm_duplicate of the lesson-7 book).
Not taken from the doc: «Повторение пройденного», separate «Задание» (first otklik question), «Итоговый тест» of the spring
course, links to kabbalahmedia.info (Факультатив: articles «Свобода воли» and Rabash — no chapter), editorial notes in the book.
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




def archive_prog(uid):
    """Embedded archive player of a program chapter (programs/cu): no link and no editorial note."""
    return ('<div class="kab-page">\n'
            '<div style="position: relative; width: 100%%; aspect-ratio: 16 / 9; margin: 16px 0;">\n'
            '<iframe src="https://kabbalahmedia.info/programs/cu/%s?mediaType=video&amp;shareLang=ru&amp;embed=1&amp;autoPlay=0" '
            'style="position: absolute; inset: 0; width: 100%%; height: 100%%; border: 0;" scrolling="no" allowfullscreen=""></iframe>\n</div>\n</div>\n'
            % uid)


INTRO = 'О чём этот урок'
CHAPTERS = ['Глава 1. Как среда действует на самом деле', 'Глава 2. Из чего состоит окружение',
            'Глава 3. Как выбирать', 'Глава 4. Почему в одиночку это не работает']

# ---------------- Содержание ----------------
toc = ('<div class="kab-text">\n<div class="kab-quote">\n<ul class="kab-list">\n'
       + ''.join('<li>%s</li>\n' % c for c in [INTRO] + CHAPTERS) +
       '''</ul>
</div>
<div class="kab-kicker">Прежде чем читать</div>
<p>Три вопроса для размышления.</p>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Назовите три вещи, которые вы считаете важными. Откуда вы взяли, что они важны?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Кого вы за последний год начали уважать — и что после этого изменилось в ваших занятиях?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Если выбор среды — единственная свобода, значит ли это, что надо менять круг общения?</div></div>
</div>
</div>
<p></p>
''')

# ---------------- О чём этот урок («Вступление» дока; его видео — в книге) ----------------
intro = h4(INTRO)
intro += p('Есть три фактора духовного развития человека в каббале:')
intro += ('<ul>\n<li><strong>Книги</strong> содержат описание всех состояний, которые должен пройти человек до полного раскрытия Творца.</li>\n'
          '<li><strong>Группа единомышленников</strong> является местом исследования.</li>\n'
          '<li><strong>Учитель</strong> дает направление и объясняет методы исследования.</li>\n</ul>\n')
intro += p('На прошлой неделе выяснилось, что единственное, что зависит от человека, — выбор среды, под управление которой он себя ставит. Этот урок — о том, как ею пользоваться. <strong>Ничего в своей жизни ломать для этого не нужно</strong>: ни работу, ни семью, ни круг общения.')

# ---------------- Глава 1 ----------------
ch1 = ''
ch1 += h4('Она не приказывает — она назначает важное')
ch1 += p('Среда не заставляет. Она делает так, что одни вещи кажутся важными, а другие нет. Дальше человек идёт к важному сам и совершенно искренне считает, что выбрал.')
ch1 += p('Бааль Сулам описывает это без всякой мягкости:')
ch1 += srcquote('<strong>«Я определённым образом сижу, одеваюсь, говорю, ем — всё это не потому, что я хочу так сидеть, одеваться, говорить или есть, а потому что другие хотят, чтобы я сидел, одевался, говорил или ел таким образом».</strong>')
ch1 += p('И добавляет то, что обычно пропускают: человек делает это <strong>вопреки собственному удобству</strong>. Ему было бы проще и легче иначе, но движения скованы вкусами и манерами окружающих.')
ch1 += h4('Проверяемое на себе')
ch1 += p('Самый честный опыт — посмотреть на свою одежду прямо сейчас и спросить, ради кого она выбрана. Если в комнате никого нет, ответ обычно всё равно не «ради себя».')
ch1 += p('Психолог Эрих Фромм формулировал то же самое: большинство людей уверены, что следуют собственным вкусам и пришли к своим мнениям в результате собственных размышлений, — а совпадение этих мнений с мнением большинства считают случайностью.')
ch1 += clip('Lesson-08-clip-K1', title='«Рождённый свободным»', source=LAITMAN + ', «Новости», 2022',
            quote='«Ты выбираешь то, что выбирает окружение, то, что одобряют близкие, друзья, то, что в моде. […] Часто идти вслед за большинством нас побуждает страх упущенных возможностей — состояние, когда вы начинаете думать, что за чужим забором трава зеленее».')
ch1 += h4('Среда — это не только люди')
ch1 += p('<strong>Средой называются не только те, кто рядом.</strong> Книги, передачи, лента, реклама, идеи, герои, к которым хочется быть похожим, — всё это среда, и действует она так же.')
ch1 += p('Поэтому «сменить окружение» не равно «сменить компанию».')

# ---------------- Глава 2 ----------------
ch2 = ''
ch2 += p('Если среда — инструмент, то у инструмента есть части. <strong>Окружение складывается из трёх вещей: источники, коллектив единомышленников, учитель.</strong>')
ch2 += figure('L08-01', 22, 800, 491,
              'Человек стоит на окружении, окружение — на трёх опорах: источники, товарищи, учитель',
              'Окружение стоит на трёх опорах. Источники — описание всех состояний, которые человек проходит. Товарищи — место, где методика проверяется и применяется. Учитель даёт направление и объясняет методы.')
ch2 += clip('Lesson-08-clip-K2', title='«Изучение каббалы»', source=LAITMAN + ', «Новости», 2017',
            quote='«Не надо заниматься самообразованием. Самоучители хороши, но не в каббале. […] Необходимо руководство, и самое главное — связь с такими же товарищами, которые желают освоить каббалу».')
ch2 += h4('Источники')
ch2 += clip('Lesson-08-clip-K3', title='«Три фактора духовного развития. Книги»')
ch2 += p('Все каббалистические книги содержат описание системы взаимоотношений Творца и созданных им творений. В книгах каббалисты описывают все состояния нисхождения от наивысшей точки слияния с Творцом до нашего мира. В них также содержится информация обо всех потенциальных состояниях, которые должно пройти человечество и каждый лично в своем развитии.')
ch2 += p('<strong>Книга – источник духовных сил, а накопление знаний – вещь второстепенная. Изучая по книге законы Высшего мира, мы вызываем на себя его воздействие.</strong>')
ch2 += p('В процессе изучения каббалистических книг человек проходит три этапа:')
ch2 += '<ol>\n<li>усваивается общая картина мироздания;</li>\n<li>подробный анализ каждой детали;</li>\n<li>соединение деталей в общую картину.</li>\n</ol>\n'
ch2 += p('Рекомендуется заучивать основные определения наизусть, чтобы избежать овеществления изучаемых состояний. Если эта методика усвоена правильно, то каждое слово должно вызывать чувственную, а не умозрительную реакцию.')
ch2 += p('Это проверяется легко: одна и та же статья читается по-разному в разные недели. Не потому, что память подводит. Изменился читающий — изменилось и то, что он в ней различает.')
ch2 += h4('Коллектив единомышленников')
ch2 += clip('Lesson-08-clip-K4', title='«Три фактора духовного развития. Группа»')
ch2 += p('Изучение каббалы невозможно без группы единомышленников. <strong>Группа – это духовная лаборатория, где полученные духовные знания и навыки обретаются, обновляются и передаются от всех каждому и от каждого всем.</strong>')
ch2 += p('Группа — место, где методика проверяется и применяется. Свойство отдачи проявляется только там, где есть кому отдавать; проверить его внутри себя нечем.')
ch2 += p('<strong>Главным в работе должно стать стремление изменить эгоцентрическое восприятие мира.</strong> Поддержка, общение, совместное изучение — следствия этого.')
ch2 += p('Правильный выбор, возвышающий свойство отдачи над свойством получения, осуществляется с помощью группы. Группа – это та часть, которой не хватает каждому из нас и которую мы должны присоединить к себе. Устремляясь к объединению с группой, мы получаем от нее силу, позволяющую нам сделать свое первое свободное движение, первый свободный выбор.')
ch2 += h4('Учитель')
ch2 += clip('Lesson-08-clip-K5', title='«Три фактора духовного развития. Учитель»')
ch2 += p('<strong>Обязанность учителя – помочь ученику установить связь с внутренней сутью каббалистических источников.</strong> Он передаёт ученику необходимые методические знания и помогает правильно настроиться на ощущение духовного мира.')
ch2 += p('Как отличить настоящего учителя-каббалиста? Есть несколько обязательных условий, которым учитель должен соответствовать:')
ch2 += ('<ul>\n<li>получил знания от признанного учителя;</li>\n<li>обучает по оригинальным источникам;</li>\n<li>не привлекает внимание к своей личности;</li>\n'
        '<li>направляет на раскрытие Творца;</li>\n<li>ответы учителя должны настраивать на дальнейшее внутреннее развитие;</li>\n'
        '<li>учитель обязан дать ученику знания о строении духовных миров и внутренней работе человека.</li>\n</ul>\n')
ch2 += p('Формула, которой больше двух тысяч лет: <strong>«Сделай себе учителя и купи себе товарища»</strong>. Оба действия — активные: не «найди, если повезёт», а сделай и купи.')
ch2 += table(['Фактор', 'Что даёт'], [
    ['Книги', 'описание всех духовных состояний; источник духовных сил'],
    ['Группа', 'духовная лаборатория; место исследования и реализации методики'],
    ['Учитель', 'направление и методы исследования; связь с внутренней сутью источников']])

# ---------------- Глава 3 ----------------
ch3 = ''
ch3 += h4('Только на ступень выше')
ch3 += srcquote(
    '<p>«„Книга Зоар“ приводит в пример бедного мудреца, которому богач предложил переехать к нему. На что услышал отказ:</p>\n'
    '<p>— Ни на каких условиях я не поселюсь в месте, где нет мудрецов!</p>\n'
    '<p>— Но ведь ты – самый большой мудрец поколения! – воскликнул богач. – У кого тебе еще учиться!</p>\n'
    '<p>И услышал в ответ, что если даже такой большой мудрец поместит себя среди неучей, то сам в скором времени станет подобен им. Поэтому надо поступать по известному указанию: „Сделай себе учителя и обрети себе товарища“. То есть создай себе окружение, потому что только выбор окружения может принести успех человеку».</p>',
    'Бааль Сулам, «Свобода воли»')
ch3 += p('Отсюда правило: <strong>среду выбирают не под себя, а на ступень выше себя.</strong> Иначе движение идёт вниз — не потому что кто-то виноват, а потому что таков механизм.')
ch3 += p('<strong>Нынешняя форма окружения – это будущая форма человека</strong>, а потому, если человек недоволен обществом, в котором находится, и не заинтересован в том, чтобы уподобиться ему, он должен найти альтернативное окружение, соответствующее его желаниям. Например, если он хочет стать профессором, то должен выбрать окружение ученых, а если хочет стать музыкантом, то среду, которая интересуется музыкой. На этом его выбор закончен, остальное происходит само собой. Если у человека нет окружения, соответствующего его желаниям, он должен выбрать его себе.')
ch3 += h4('Прийти — ещё не значит выбрать')
ch3 += p('Человека часто <strong>приводят</strong>: друг позвал, попалась ссылка, совпало по времени. Это ещё не выбор окружения. Это обстоятельства, а они относятся к тем же факторам из урока 7.')
ch3 += p('<strong>Выбор начинается потом</strong> — когда человек уже внутри и решает, насколько он даёт этой среде на себя действовать.')
ch3 += h4('Ничего не ломать')
ch3 += p('<strong>В своей жизни принудительно менять не нужно ничего.</strong> Ни работу, ни семью, ни привычный уклад, ни круг общения.')
ch3 += p('<strong>Вся работа — в изменении того, что считается важным.</strong>')
ch3 += p('Способ такой: <strong>выстроить новое окружение параллельно обычному</strong> — то, в котором главной ценностью является духовность. Не вместо старого. Рядом с ним.')
ch3 += p('Именно в этом, по Бааль Суламу, и заключается свобода выбора. Не в разрыве с прежней жизнью, а в том, что рядом с ней появляется ещё одна среда, и человек сам решает, какой из них позволить на себя влиять.')

# ---------------- Глава 4 (+ «Что с этим делать» и «Что дальше») ----------------
ch4 = ''
ch4 += h4('Инструмент собирается из людей')
ch4 += p('Творение было создано одно, потом разделилось на множество частей, и части друг друга перестали ощущать. Каждый видит другого ровно в той мере, в какой тот может быть ему полезен.')
ch4 += p('<strong>Поэтому то, что надо в себе вырастить, вырастает только между людьми.</strong> Другого места для этого нет.')
ch4 += h4('Глина в руках ваятеля')
ch4 += srcquote('«Ведь после того как человек выбрал окружение, он отдан в его руки, как глина в руки ваятеля».', 'Бааль Сулам, «Свобода воли»')
ch4 += p('Это не про подчинение кому-то. Это про то, что <strong>действие среды не выборочное</strong>: нельзя взять от неё полезное и не взять остальное. Выбор делается — какой среде себя отдать. Дальше она действует целиком.')
ch4 += p('Ровно поэтому выбор и оказывается единственным настоящим действием человека.')
ch4 += h4('Что с этим делать')
ch4 += p('<strong>Первое. Проверить, откуда взялось важное.</strong> Возьмите три вещи, которые вы назвали важными в начале урока. По каждой спросите: кто мне сказал, что это важно.')
ch4 += p('<strong>Второе. Не ломать.</strong> Если после этого урока захотелось что-то оборвать — уволиться, разойтись с людьми, — это не то, о чём здесь речь. Новое окружение строится рядом с прежней жизнью.')
ch4 += p('<strong>Третье. Одно действие, а не программа.</strong> Учебная группа на курсе — это и есть та самая параллельная среда, собранная заранее. '
         '<a href="https://edu.kabacademy.com/mod/feedback/view.php?id=12951">Записаться в неё</a> можно на любой неделе.')
NEXT = 'На следующей неделе — <strong>намерение.</strong> Важно не только действие, но и то, ради чего оно сделано: что такое намерение, чем оно отличается от желания и почему именно оно определяет результат.'
ch4 += h4('Что дальше')
ch4 += p(NEXT)

# ---------------- Резюме ----------------
RESUME = [
    '<b>Три фактора духовного развития</b> — учеба по истинным каббалистическим источникам, коллектив единомышленников, учитель.',
    'Учитель дает направление и объясняет методы исследования.',
    'Группа единомышленников является местом исследования и реализации каббалистической методики.',
    'Книги содержат описание всех состояний, которые должен пройти человек до полного раскрытия Творца.',
    '<b>Главным в работе в группе должно стать стремление изменить эгоцентрическое восприятие мира.</b>',
    'Связь с учителем – это восприятие от учителя внутренней сути каббалистических источников. Учитель постепенно приводит ученика к правильному настрою на ощущение духовного мира.',
    'Учитель считается истинным, если получил знания от признанного учителя, обучает по оригинальным источникам, не привлекает внимание к своей личности, направляет ученика на раскрытие Творца.',
    'Среда действует не приказом, а тем, что <b>назначает важное</b>. Средой называются не только люди: книги, передачи, лента, реклама, идеи.',
    '<b>Среду выбирают на ступень выше себя.</b> Нынешняя форма окружения – это будущая форма человека.',
    '<b>Ломать в своей жизни ничего не нужно.</b> Наша работа — выстроить параллельно нашему обычному окружению новое окружение, в котором главной ценностью является духовность.',
]
resume = ('<div class="kab-page">\n<div class="kab-card">\n'
          + ''.join('<div class="kab-row"><div class="kab-dot"></div><div>%s</div></div>\n' % r for r in RESUME)
          + '</div>\n<div class="kab-quote kab-quote--purple">%s</div>\n</div>\n' % NEXT.replace('<strong>', '<b>').replace('</strong>', '</b>'))

# ---------------- Отклик недели (два вопроса: «Задание» + «Если захочется») ----------------
otklik = '''<div class="kab-text">
<div class="kab-quote">Напишите своими словами ответ на любой из вопросов.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>В чём вы видите ценность каббалистических книг?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Возьмите одну вещь, которую вы считаете важной, и попробуйте восстановить, откуда она к вам пришла. Не для того, чтобы от неё отказаться, — просто посмотреть. Напишите одной фразой, если получится что-то неожиданное.</div></div>
</div>
<div class="kab-note">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
</div>
'''

# ---------------- Книга «Дополнительные материалы к Уроку 8» (без своих надписей и ссылок на архив) ----------------
book_ch1 = '<div class="kab-page">\n'
book_ch1 += '<div class="kab-kicker">Вступление: «Основы каббалы. Три фактора духовного развития»</div>\n'
book_ch1 += ('<div style="position: relative; width: 100%; aspect-ratio: 16 / 9; margin: 16px 0 40px;">\n'
             '<iframe src="https://kabbalahmedia.info/programs/cu/XfZcwQkf?mediaType=video&amp;shareLang=ru&amp;embed=1&amp;autoPlay=0" '
             'style="position: absolute; inset: 0; width: 100%; height: 100%; border: 0;" scrolling="no" allowfullscreen=""></iframe>\n</div>\n')
book_ch1 += '<div class="kab-kicker">Факторы духовного продвижения. Преподаватель Александр Козлов</div>\n'
book_ch1 += '<p><strong>Книги</strong></p>\n' + yt('Факторы духовного продвижения. Книги (преподаватель Александр Козлов)', '0JEN09kK_F0', '133pMsFIeLbcRv0zLtOE8Hhjv_0kbNeHG')
book_ch1 += '<p><strong>Группа</strong></p>\n' + yt('Факторы духовного продвижения. Группа (преподаватель Александр Козлов)', 'hs41OcAvd48', '1F4aF6G_-ESKsWr1HA1bB2CqAEPem-fif')
book_ch1 += '<p><strong>Учитель</strong></p>\n' + yt('Факторы духовного продвижения. Учитель (преподаватель Александр Козлов)', 'V3NvSZkVx-Y', '15cyZ_5itXKadAGbxtHFU-qUzmeuDvcle')
book_ch1 += '<div class="kab-kicker">Ответы на вопросы по теме. Преподаватель Дани Половец</div>\n'
book_ch1 += yt('Ответы на вопросы по теме «Три фактора духовного развития»', '_-gQcpWNnvc', '1PirHdlPDX3f3sL6p_uXe1xwyYAa5L3gs')
book_ch1 += '</div>\n'
book_ch2 = archive_prog('GUo21ol0')
book_ch3 = archive_prog('RbJPpuzw')
BOOK = [('Лекции урока в записи', book_ch1),
        ('«Влияние общественного мнения на сознание человека» (2020)', book_ch2),
        ('«Основы каббалы. Методика изучения каббалы» (2018)', book_ch3)]

PAGES = {'p_toc': toc, 'p_intro': intro, 'p_ch1': ch1, 'p_ch2': ch2, 'p_ch3': ch3, 'p_ch4': ch4,
         'page_resume': resume, 'forum_otklik': otklik, 'book_ch1': book_ch1, 'book_ch2': book_ch2, 'book_ch3': book_ch3}

# ---------------- Тест «Проверьте себя» (quiz 1242, 8 слотов): вопросы и верные ответы из дока, разборы неверных — свои ----------------
THIRD = 0.3333333
MULTI = ' <em>Несколько правильных ответов.</em>'
QUESTIONS = [
    ('У8-01 С чего начинается становление каббалиста', 'С чего начинается становление каббалиста?', [
        ('С изучения каббалистических книг', 1.0, 'Верно. Книги содержат описание всех состояний, которые должен пройти человек.'),
        ('С отшельничества', 0.0, 'Нет. Методика проверяется и применяется среди людей, в группе. Начинается всё с книг.'),
        ('С личного знакомства с каббалистом', 0.0, 'Учитель важен, но начало — книги: в них описаны все состояния, которые должен пройти человек.'),
        ('С чтения молитв и псалмов', 0.0, 'Нет. Становление каббалиста начинается с изучения каббалистических книг — источника духовных сил.')], True),
    ('У8-02 Что мы получаем из книг (неправильный ответ)', 'Укажите один неправильный ответ. Что мы получаем из каббалистических книг?', [
        ('Советы, как преуспеть в материальном мире', 1.0, 'Верно, это неправильный ответ. Книга — источник духовных сил, а не советов для материального успеха.'),
        ('Воздействие, которое строит во мне экран', 0.0, 'Это мы из книг получаем: изучая законы Высшего мира, мы вызываем на себя его воздействие. Неправильный ответ — советы для материального успеха.'),
        ('Информацию о строении высших миров', 0.0, 'Это в книгах есть: описание системы взаимоотношений Творца и творений. Неправильный ответ — советы для материального успеха.'),
        ('Понимание пути нашего духовного развития', 0.0, 'Это в книгах есть: все состояния, которые должен пройти человек. Неправильный ответ — советы для материального успеха.')], True),
    ('У8-03 Что воздействует при изучении книг', 'Что воздействует на человека при изучении им каббалистических книг?' + MULTI, [
        ('Высшее постижение автора-каббалиста', 0.5, 'Верно. Изучая по книге законы Высшего мира, мы вызываем на себя его воздействие.'),
        ('Высшая энергия', 0.5, 'Верно. Книга – источник духовных сил.'),
        ('Произнесение вслух букв и слов', -0.5, 'Нет. Действует не звучание, а то, что описано в книге: законы Высшего мира, постигнутые автором.'),
        ('Язык иврит, на котором написана книга', -0.5, 'Язык здесь ни при чём: действует постижение автора и высшая энергия, а не буквы оригинала.')], False),
    ('У8-04 Что улучшает влияние общества', 'Какие действия человека в каббалистическом обществе улучшают влияние общества на него?' + MULTI, [
        ('Поднятие значимости духовного развития', THIRD, 'Верно. Среда действует тем, что назначает важное, — и это можно усиливать.'),
        ('Комплименты духовным действиям других', THIRD, 'Верно. Так в группе растёт значимость духовного.'),
        ('Рассказ о прочитанных книгах и статьях', THIRD, 'Верно. Это тоже поднимает значимость того, ради чего группа собралась.'),
        ('Выяснения, кто лучше знает каббалу', -1.0, 'Нет. Такие выяснения поднимают значимость себя, а не духовного развития.')], False),
    ('У8-05 Как среда действует на человека', 'Как среда действует на человека?', [
        ('Назначает, что считать важным', 1.0, 'Верно. Дальше человек идёт к важному сам и искренне считает, что выбрал.'),
        ('Прямо запрещает и приказывает', 0.0, 'Среда не заставляет: она делает так, что одни вещи кажутся важными, а другие нет.'),
        ('Никак, если человек внимателен', 0.0, 'Внимательность не спасает: большинство уверены, что следуют собственным вкусам, а совпадение с мнением большинства считают случайностью.'),
        ('Только через близких людей', 0.0, 'Средой называются не только те, кто рядом: книги, передачи, лента, реклама, идеи.')], True),
    ('У8-06 Окружение кроме людей', 'Что относится к окружению, кроме людей?', [
        ('Книги, передачи, лента, реклама, идеи', 1.0, 'Верно. Поэтому «сменить окружение» и «сменить компанию» — разные вещи.'),
        ('Только семья и друзья', 0.0, 'Семья и друзья — люди. Кроме них средой являются книги, передачи, лента, реклама, идеи.'),
        ('Только коллеги', 0.0, 'Коллеги — тоже люди. А средой называются ещё книги, передачи, лента, реклама, идеи.'),
        ('Ничего', 0.0, 'Средой называются не только те, кто рядом: книги, передачи, лента, реклама, идеи действуют так же.')], True),
    ('У8-07 Среда на ступень выше', 'Почему среду выбирают на ступень выше себя?', [
        ('Иначе движение идёт вниз', 1.0, 'Верно. Даже самый большой мудрец, поместив себя среди неучей, скоро станет подобен им.'),
        ('Так принято', 0.0, 'Дело не в обычае, а в механизме: нынешняя форма окружения — это будущая форма человека.'),
        ('Чтобы было интереснее', 0.0, 'Интерес ни при чём. Среда ниже себя тянет вниз — таков механизм.'),
        ('Чтобы быстрее выучиться', 0.0, 'Дело не в скорости учёбы: нынешняя форма окружения — это будущая форма человека, и среда ниже себя тянет вниз.')], True),
    ('У8-08 Что поменять, чтобы выбрать среду', 'Что нужно поменять в своей жизни, чтобы выбрать среду?', [
        ('Ничего ломать не нужно — новое окружение строится параллельно', 1.0, 'Верно. Вся работа — в изменении того, что считается важным.'),
        ('Работу', 0.0, 'Работу менять не нужно. Новое окружение строится рядом с прежней жизнью, а не вместо неё.'),
        ('Круг общения', 0.0, 'Круг общения ломать не нужно: новое окружение строится параллельно обычному.'),
        ('Место жительства', 0.0, 'Переезжать не нужно. Вся работа — в изменении того, что считается важным.')], True),
]

for k, v in PAGES.items():
    assert not re.search(r'\bРав\b', v), k
    assert 'М. Лайтман' not in v, k
    assert not re.search(r'href="https?://(www\.|cdn\.)?kabbalahmedia\.info', v), k
    assert 'ильм' not in v, k
    open('html/%s.html' % k, 'w', encoding='utf-8', newline='\n').write(v)
print('pages:', ', '.join('%s=%d' % (k, len(v)) for k, v in PAGES.items()))

# ---------------- SQL ----------------
TZ = ZoneInfo('Asia/Jerusalem')
LESSON, CM_LESSON, CM_QUIZ, CM_RESUME, CM_OTKLIK, CM_BOOK = 1969, 13571, 13572, 13573, 13574, 13691
QUIZ, PAGE_RESUME, FORUM_OTKLIK, BOOK_ID = 1242, 2502, 3915, 974
# 17928 map -> 17929 toc -> 17930 intro -> 17933 ch1 -> 17931 ch2 -> 17932 ch3 -> new ch4
PAGE_IDS = {'map': 17928, 'toc': 17929, 'intro': 17930, 'ch1': 17933, 'ch2': 17931, 'ch3': 17932}
LAST_FINISH = 37283   # «Завершить» on 17932 -> «Продолжить»
# Since 18.10.2026 everything of a lesson is due on Wednesday 23:59 (lecture, otklik and test), Israel time
EXP = int(datetime(2026, 11, 25, 23, 59, tzinfo=TZ).timestamp())
# quiz slots -> question versions -> answer ids (from the DB, 18.10.2026); slots 7–8 = new questions 68871/68872 (copies of 68813)
QIDS = [([68806, 68807], [[284224, 284225, 284226, 284227], [284228, 284229, 284230, 284231]]),
        ([68808], [[284232, 284233, 284234, 284235]]),
        ([68809, 68810], [[284236, 284237, 284238, 284239], [284240, 284241, 284242, 284243]]),
        ([68811], [[284244, 284245, 284246, 284247]]),
        ([68812], [[284248, 284249, 284250, 284251]]),
        ([68813], [[284252, 284253, 284254, 284255]]),
        ([68871], [[284487, 284488, 284489, 284490]]),
        ([68872], [[284494, 284495, 284496, 284497]])]
ALLQ = [qid for qids, _ in QIDS for qid in qids]

if '--sql' in sys.argv:
    NOW = int(time.time())

    def hx(s):
        return '0x' + binascii.hexlify(s.encode('utf-8')).decode()

    def q(s):
        return "'" + s.replace(chr(92), chr(92) * 2).replace("'", "''") + "'"

    mappage = ('<p><a title="Начать" href="https://edu.kabacademy.com/mod/lesson/view.php?id=%d&amp;pageid=%d"><img class="img-fluid" src="%smaps/L08.png" alt="Карта Урок 8" width="1174" height="915"></a></p>\n'
               % (CM_LESSON, PAGE_IDS['toc'], IMG))
    open('html/p_map.html', 'w', encoding='utf-8', newline='\n').write(mappage)
    sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
    pages = [('map', '*', mappage), ('toc', 'Содержание', toc), ('intro', INTRO, intro)] + [
        ('ch%d' % i, CHAPTERS[i - 1], html) for i, html in enumerate((ch1, ch2, ch3), 1)]
    for key, title, html in pages:
        sql.append("UPDATE mdl_lesson_pages SET title=%s, contents=%s, timemodified=%d WHERE id=%d AND lessonid=%d;" % (q(title), hx(html), NOW, PAGE_IDS[key], LESSON))
    # chapter 3 (17932) was the last page: «Завершить» -> «Продолжить», then a new page for chapter 4
    last = PAGE_IDS['ch3']
    sql.append("UPDATE mdl_lesson_answers SET answer='Продолжить', jumpto=-1, timemodified=%d WHERE id=%d AND pageid=%d AND lessonid=%d;" % (NOW, LAST_FINISH, last, LESSON))
    sql.append("INSERT INTO mdl_lesson_pages (lessonid,prevpageid,nextpageid,qtype,qoption,layout,display,timecreated,timemodified,title,contents,contentsformat) "
               "SELECT lessonid,%d,0,qtype,qoption,layout,display,%d,%d,%s,%s,contentsformat FROM mdl_lesson_pages WHERE id=%d;" % (last, NOW, NOW, q(CHAPTERS[3]), hx(ch4), last))
    sql.append("SET @n4 = LAST_INSERT_ID();")
    sql.append("UPDATE mdl_lesson_pages SET nextpageid=@n4 WHERE id=%d;" % last)
    sql.append("INSERT INTO mdl_lesson_answers (lessonid,pageid,jumpto,grade,score,flags,timecreated,timemodified,answer,answerformat,response,responseformat) VALUES "
               "(%d,@n4,-40,0,0,0,%d,%d,'Вернуться',0,NULL,0),(%d,@n4,-9,0,0,0,%d,%d,'Завершить',0,NULL,0);" % (LESSON, NOW, NOW, LESSON, NOW, NOW))
    sql.append("UPDATE mdl_lesson SET timemodified=%d WHERE id=%d;" % (NOW, LESSON))
    # quiz
    for (name, text, answers, single), (qids, aidss) in zip(QUESTIONS, QIDS):
        assert abs(sum(f for _, f, _ in answers if f > 0) - 1) < 1e-6, name
        for qid, aids in zip(qids, aidss):
            assert len(aids) == len(answers) == 4
            sql.append("UPDATE mdl_question SET name=%s, questiontext=%s, timemodified=%d WHERE id=%d;" % (q(name), hx('<p>%s</p>\n' % text), NOW, qid))
            sql.append("UPDATE mdl_qtype_multichoice_options SET single=%d WHERE questionid=%d;" % (1 if single else 0, qid))
            for (atext, frac, fb), aid in zip(answers, aids):
                sql.append("UPDATE mdl_question_answers SET answer=%s, fraction=%s, feedback=%s WHERE id=%d AND question=%d;" % (hx('<p>%s</p>\n' % atext), repr(frac), hx('<p>%s</p>\n' % fb), aid, qid))
    sql.append("UPDATE mdl_quiz SET timemodified=%d WHERE id=%d;" % (NOW, QUIZ))
    # résumé, otklik
    sql.append("UPDATE mdl_page SET content=%s, timemodified=%d WHERE id=%d;" % (hx(resume), NOW, PAGE_RESUME))
    sql.append("UPDATE mdl_forum SET intro=%s, timemodified=%d WHERE id=%d;" % (hx(otklik), NOW, FORUM_OTKLIK))
    # open the lecture, test and résumé (the content subsections stay date-locked until 20.11 08:00)
    sql.append("UPDATE mdl_course_modules SET visible=1, visibleold=1 WHERE id IN (%d,%d,%d);" % (CM_LESSON, CM_QUIZ, CM_RESUME))
    sql.append("UPDATE mdl_course_modules SET completionexpected=%d WHERE id IN (%d,%d,%d);" % (EXP, CM_LESSON, CM_OTKLIK, CM_QUIZ))
    for mod, inst in (('lesson', LESSON), ('forum', FORUM_OTKLIK), ('quiz', QUIZ)):
        sql.append("UPDATE mdl_event SET timestart=%d, timesort=%d, visible=1, timemodified=%d WHERE courseid=236 AND modulename='%s' AND instance=%d AND eventtype='expectcompletionon';" % (EXP, EXP, NOW, mod, inst))
    sql.append("UPDATE mdl_event SET visible=1, timemodified=%d WHERE courseid=236 AND modulename='lesson' AND instance=%d AND eventtype='open';" % (NOW, LESSON))
    # book 974 (cm 13691, a copy of the lesson-7 book): name and chapters
    sql.append("UPDATE mdl_book SET name='Дополнительные материалы к Уроку 8', revision=revision+1, timemodified=%d WHERE id=%d;" % (NOW, BOOK_ID))
    sql.append("DELETE FROM mdl_book_chapters WHERE bookid=%d;" % BOOK_ID)
    for i, (title, html) in enumerate(BOOK, 1):
        sql.append("INSERT INTO mdl_book_chapters (bookid,pagenum,subchapter,title,content,contentformat,hidden,timecreated,timemodified,importsrc) "
                   "VALUES (%d,%d,0,%s,%s,1,0,%d,%d,'');" % (BOOK_ID, i, q(title), hx(html), NOW, NOW))
    sql.append("UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;")
    sql += ['COMMIT;',
            "SELECT id,prevpageid,nextpageid,title,LENGTH(contents) clen FROM mdl_lesson_pages WHERE lessonid=%d ORDER BY id;" % LESSON,
            "SELECT id,pageid,jumpto,answer FROM mdl_lesson_answers WHERE lessonid=%d ORDER BY pageid,id;" % LESSON,
            "SELECT question,COUNT(*) n,SUM(fraction) s,SUM(GREATEST(fraction,0)) pos FROM mdl_question_answers WHERE question IN (%s) GROUP BY question;" % ','.join(map(str, ALLQ)),
            "SELECT q.id,q.name,o.single FROM mdl_question q JOIN mdl_qtype_multichoice_options o ON o.questionid=q.id WHERE q.id IN (%s);" % ','.join(map(str, ALLQ)),
            "SELECT id,visible,completionexpected FROM mdl_course_modules WHERE id IN (%d,%d,%d,%d,%d);" % (CM_LESSON, CM_QUIZ, CM_RESUME, CM_OTKLIK, CM_BOOK),
            "SELECT id,name,modulename,instance,eventtype,FROM_UNIXTIME(timestart),visible FROM mdl_event WHERE courseid=236 AND ((modulename='lesson' AND instance=%d) OR (modulename='quiz' AND instance=%d) OR (modulename='forum' AND instance=%d));" % (LESSON, QUIZ, FORUM_OTKLIK),
            "SELECT id,pagenum,title,LENGTH(content) FROM mdl_book_chapters WHERE bookid=%d ORDER BY pagenum;" % BOOK_ID]
    open('lesson8.sql', 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
    print('sql statements:', len(sql), 'bytes:', os.path.getsize('lesson8.sql'), 'EXP', EXP)
