# -*- coding: utf-8 -*-
"""Lesson 6 «Душа и тело», course 236 — text of the doc «Урок 6 — СО финал» (14.10.2026,
1xKrAupZzRJprhM4B9a_vVK4ZsAbLhTm8j8sev8OYewQ) in the lesson 1–2 format: map -> contents (+ three questions)
-> «О чём этот урок» (the doc's «Вступление» text, like the intro page of lesson 2: four of the five
«Проверьте себя» questions are about it) -> chapters 1–4; quiz «Проверьте себя» (6); résumé;
otklik (two questions, as in lessons 2, 4, 5); book «Дополнительные материалы к Уроку 6».

Writes html/*.html; with --sql writes lesson6.sql (book 972 = cm_duplicate of the lesson-5 book).
Not taken from the doc (owner's rule 13.10): «Повторение пройденного», the «Вступление» video (goes to the book),
separate «Задание» (first otklik question) and «Итоговый тест» of the spring course (10 questions, incl.
gapselect/ordering/match) — except its question 7, which became the sixth «Проверьте себя» question: the doc
has five. Lessons 6–15 have no seminar.
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


INTRO = 'О чём этот урок'
CHAPTERS = ['Глава 1. Три теории тела и души', 'Глава 2. Что значит иметь душу',
            'Глава 3. Три этапа развития души', 'Глава 4. Цель кругооборотов душ']

# ---------------- Содержание (как в уроке 2: вводная страница + главы) ----------------
toc = ('<div class="kab-text">\n<div class="kab-quote">\n<ul class="kab-list">\n'
       + ''.join('<li>%s</li>\n' % c for c in [INTRO] + CHAPTERS) +
       '''</ul>
</div>
<div class="kab-kicker">Прежде чем читать</div>
<p>Три вопроса для размышления.</p>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Что вы сейчас называете словом «душа» — до всякой каббалы?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Если душа есть у всех с рождения, то что именно в человеке меняется за жизнь?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Чем душа отличалась бы от той жизненной силы, которая есть у любого животного?</div></div>
</div>
</div>
<p></p>
''')

# ---------------- О чём этот урок («Вступление» дока, без видео — оно в книге) ----------------
intro = ''   # the page title «О чём этот урок» is already shown by the lesson page — no heading inside (owner, 20.10.2026)
intro += p('Изучая каббалу, человек начинает понимать, что в течение своего существования он прошёл в нашем мире много жизней: спускался в наш мир, рождался – и снова уходил из нашего мира.')
intro += p('Сколько раз и до какого времени человек должен нисходить в наш мир? Пока не раскроет истинную сущность себя и всего мироздания.')
intro += p('До рождения в нашем мире человек находится в ином измерении. Затем он рождается в нашем мире и живёт в телесной оболочке – биологическом теле с пятью органами чувств. <strong>Цель его нахождения в нашем мире заключается в том, чтобы ощутить тело как внешнюю оболочку души, обрести шестой орган чувств и подняться в Высший мир.</strong> Так человек постигает самое совершенное состояние.')
intro += p('Раздел каббалы, изучающий цикл нисхождения в наш мир и существования вне тела, называется «кругооборот душ». В нём изучается, почему у каждого человека свои кругообороты.')

# ---------------- Глава 1 ----------------
ch1 = ''
ch1 += p('Бааль Сулам в статье «Тело и душа» сводит все распространённые представления к трём.')
ch1 += srcquote(
    '<p>«Все распространенные теории тела и души можно объединить в три нижеследующие теории:</p>\n'
    '<p><strong>1. Теория веры.</strong> Теория веры говорит, что нет ничего, кроме души или духа. […] Душа обладает самостоятельно существующей реальностью, прежде чем спускается в материальный мир и воплощается в тело человека. Смерть физического тела не влияет на душу, потому что она духовна, и является простой сущностью. […] В соответствии с представлением сторонников этой теории, тело является неким одеянием для души — духовной сущности. […]</p>\n'
    '<p><strong>2. Теория дуализма.</strong> […] По их мнению, тело является совершенным созданием. Оно живет, питается, в мере необходимости заботится о продолжении своего существования и нисколько не нуждается в помощи какой бы то ни было духовной сущности. Однако тело отнюдь не считается сутью человека. Основу сути человека представляет разумная душа, которая является духовной сущностью […].</p>\n'
    '<p><strong>3. Теория отрицания.</strong> Теории отрицания придерживаются исследователи, отрицающие наличие в теле некой духовной реальности и признающие только его материальность. Согласно их утверждениям, разум человека также является производным от тела. Сторонники теории отрицания представляют тело подобным исправной электрической машине с проводами, протянувшимися от тела к мозгу».</p>')
ch1 += p('И где обрывается каждая:')
ch1 += srcquote(
    '<p>«Теория отрицания чужда духу образованного человека, поскольку игнорирует личность и представляет человека в виде машины, приводимой в действие посредством внешних сил. Из нее следует, что у человека нет никакого свободного выбора в своих желаниях […].</p>\n'
    '<p>Считается, что тело, которое согласно третьей теории называется машиной, не является истинным человеком, а суть человека, его «я», представляет собой невидимую и неощущаемую вечную духовную сущность, в скрытой форме находящуюся внутри тела. Но как эта духовная сущность может приводить тело в движение? Ведь в соответствии с утверждением самой философии, у духовного нет никакого контакта с материальным, и оно не оказывает на него никакого влияния.</p>\n'
    '<p>Таким образом, ни философия, ни метафизика не могут представить решения по вопросу о душе».</p>',
    'Бааль Сулам, «Тело и душа»')
ch1 += table(['Теория', 'Что утверждает'], [
    ['Веры', 'есть только душа; тело — её одеяние'],
    ['Дуализма', 'тело живёт само, но суть человека — душа'],
    ['Отрицания', 'есть только тело, разум — его производное']])
ch1 += figure('L06-01', 17, 800, 538,
              'Три теории тела и души — веры, дуализма, отрицания — и где обрывается каждая; внизу: ни философия, ни метафизика решения по вопросу о душе не дают',
              'Три теории и где обрывается каждая. Теория веры и теория дуализма не объясняют, как духовное двигает материальное. Теория отрицания делает человека машиной — тогда у него нет свободы выбора. Ни философия, ни метафизика решения по вопросу о душе не дают.')

# ---------------- Глава 2 ----------------
ch2 = ''
ch2 += p('<strong>Душа – это духовный орган, постепенно зарождающийся в человеке, находящемся в нашем мире. Рождение души означает последовательное развитие ощущения, вызванного воздействием духовных сил, появление новых альтруистических желаний, возникновение минимального восприятия Творца.</strong>')
ch2 += p('Таким образом, <strong>наряду с физическим телом</strong>, живущим по законам физиологии, в человеке появляется тело духовное, существующее в мире духовном.')
ch2 += p('Пока человек не развил в себе душу, он имеет так называемую <strong>«витальную силу»</strong>, энергию, как у любого живого существа. Эта витальная сила не имеет никакого отношения к душе.')
ch2 += figure('L06-02', 18, 800, 498,
              'Шар желаний: каждая ячейка — какое-то желание, к еде, к деньгам, к уважению, к знанию; весь шар целиком — «тело»; внутри одна отдельная точка',
              'Шар желаний. Каждая ячейка — какое-то желание: к еде, к деньгам, к уважению, к знанию. Весь шар целиком и есть «тело». Внутри — одна отдельная точка: стремление, которое человек может развить как душу.')
ch2 += clip('Lesson-06-clip-01', title='«Что такое душа»', source=LAITMAN + ', «Новости», 2017',
            quote='«Тело существует как животное. […] Есть витальная сила, которая даёт возможность телу существовать. А душа — это часть Творца свыше. То есть это такое наше желание, которое достигло состояния подобия Творцу».')
ch2 += p('Как без знания законов природы и общества мы не смогли бы физически существовать в этом мире, так и наша душа, наше духовное тело не сможет пребывать в духовном мире без знакомства с его природой. Если поместить в современную социальную среду первобытного человека, не имеющего представления ни о законах социума, ни о техническом прогрессе, сможет ли он выжить? Точно так же душа человека не может пребывать в духовном мире, не приобретя определённых представлений о его природе и законах.')

# ---------------- Глава 3 ----------------
ch3 = ''
ch3 += p('В рождении и развитии физического тела различают три периода, являющиеся следствием трёх этапов развития души.')
ch3 += p('<strong>Первый этап — рождение, зародыш.</strong> Процесс рождения и появление на свет. На этом этапе отсутствуют знания и навыки, всё необходимое младенец получает от родителей, существуя исключительно благодаря их усилиям и заботе. Аналогично физическому происходит и духовное рождение: выход в духовный мир осуществляется после осознания своей эгоистической природы как зла и приобретения начального желания к исправлению. […] Душа, полученная живущим в нашем мире взрослым человеком, называется новорождённой.')
ch3 += p('<strong>Второй этап — рост и развитие.</strong> Рост и получение необходимых знаний и разума, дающих возможность избегать вредного и опасного, но при самостоятельной заботе о себе под контролем отца и матери.')
ch3 += p('<strong>Духовный рост состоит минимум из двух стадий. Там, где я могу что-то сделать для Творца, я делаю сам. Там, где не могу, – должен просить помощи Высшего.</strong>')
ch3 += p('<strong>Третий этап — большое состояние.</strong> Состояние взрослого, когда приобретённые знания дают силу для самостоятельного существования, подобного жизни зрелого человека в нашем мире.')
ch3 += table(['Этап', 'Название', 'Что происходит'], [
    ['Первый', 'рождение, зародыш', 'всё необходимое — от родителей, то есть от Высшего'],
    ['Второй', 'рост и развитие', 'что могу — делаю сам, что не могу — прошу помощи Высшего'],
    ['Третий', 'большое состояние', 'самостоятельность взрослого']])
ch3 += p('Любая душа постигает в своём знании все души. Даже в нашем мире мудрец может познать природу всего человечества через осознание самого себя, потому что каждая часть творения включает в себя элементы всех остальных его частей.')
ch3 += p('<strong>Поскольку все части творения взаимосвязаны и только эгоистические намерения разделяют нас, а наши души представляют собой единое духовное тело, называемое душой Адама, то, исправляя себя, человек исправляет весь мир.</strong>')

# ---------------- Глава 4 ----------------
ch4 = ''
ch4 += p('Творец создал душу – систему Адам Ришон, состоящую из <strong>613 желаний</strong>. Цель Творца состоит в том, чтобы творение (желание получать) достигло вечности и совершенства, уподобившись Ему Самому.')
ch4 += p('Чтобы измениться окончательно, необходимо отсечь предыдущее состояние и подняться к более высокому, то есть в большей мере соответствующему свойствам Творца. В этом процессе следует выделить: <strong>момент рождения, основные этапы физиологического развития человеческого организма, его вхождение в общественную сферу и затем – выход к духовному состоянию.</strong>')
ch4 += p('Человеку приходится работать, решать разного рода житейские проблемы, в ходе чего он, сам того не ведая, исправляется. Возложенная на нас задача сводится к тому, чтобы использовать, скажем, <strong>два свободных часа в день</strong> для изучения истинной методики исправления, которую мы способны и обязаны реализовать самостоятельно.')
ch4 += p('<strong>В земной жизни мы не в состоянии соединиться друг с другом душами, потому что нас разделяет эгоизм. Поэтому существуют процессы смены жизни и смерти в многочисленных кругооборотах, через которые проходит все человечество, становясь при этом все более подготовленным к объединению. […] Так, из кругооборота в кругооборот форма соединения становится все более возвышенной как в духовном, так и в материальном смысле. В конце концов, мы достигнем состояния, когда, оставаясь в телах, все люди соединятся душами.</strong>')
ch4 += clip('Lesson-06-clip-K1', title='«О перевоплощении душ»', source=LAITMAN + ', «Новости», 2018',
            quote='«С точки зрения каббалы существует реинкарнация, которая называется „гильгулим“, то есть „кругообороты“. Но это кругообороты не наших жизней, не возвращение в наш мир — смерть и снова рождение. Это кругообороты в духовном пространстве, если мы его достигаем».')
NEXT = 'На следующей неделе — <strong>свобода выбора.</strong> Что в человеке вообще свободно и где именно проходит его выбор.'
ch4 += h4('Что дальше')
ch4 += p(NEXT)

# ---------------- Резюме ----------------
RESUME = [
    '<b>Душа – это внутреннее чувство человека, желание получать наслаждение, наполниться близостью, подобием свойств с Творцом или Природой.</b>',
    'Пока человек не развил в себе душу, он имеет так называемую «витальную силу», энергию, как у любого живого существа. Эта витальная сила не имеет никакого отношения к душе.',
    'Между духовным и материальным существует лишь причинно-следственная связь.',
    '<b>Душа в человеке должна развиться осознанно его собственным выбором, собственным усилием, собственным желанием.</b>',
    'Желание получить, единственное творение, проходит многие кругообороты.',
    'В одном из таких состояний возникает новое стремление (точка в сердце), которое впоследствии человек может развить как душу. Это стремление не связано с пятью органами чувств, которыми мы ощущаем наш мир.',
    'Цель нашей жизни – взрастить из точки в сердце желание уровня «Человек», «Адам» – подобный Творцу. Если человек достиг такого уровня, то он должен помочь остальным в этой задаче.',
    'Пока все души не достигнут раскрытия Творца, что и является исправлением, они будут возвращаться в земные тела. Та близость, которой вы достигли к Творцу в этой жизни, облегчает процесс исправления в следующей жизни.',
    'Три распространённые теории — веры, дуализма и отрицания — решения по вопросу о душе не дают.',
]
resume = ('<div class="kab-page">\n<div class="kab-card">\n'
          + ''.join('<div class="kab-row"><div class="kab-dot"></div><div>%s</div></div>\n' % r for r in RESUME)
          + '</div>\n<div class="kab-quote kab-quote--purple">%s</div>\n</div>\n' % NEXT.replace('<strong>', '<b>').replace('</strong>', '</b>'))

# ---------------- Отклик недели (как в уроках 2, 4, 5: два вопроса) ----------------
otklik = '''<div class="kab-text">
<div class="kab-quote">Напишите своими словами ответ на любой из вопросов.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Почему каббала необходима каждому человеку?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Из трёх теорий выберите ту, которая до этого урока была ближе всего вам, и напишите одной фразой — почему.</div></div>
</div>
<div class="kab-note">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
</div>
'''

# ---------------- Книга «Дополнительные материалы к Уроку 6» ----------------
book_ch1 = '<div class="kab-page">\n'
book_ch1 += '<div class="kab-kicker">Вступление</div>\n'
book_ch1 += yt('Основы каббалы. Душа и тело', 'IyL2n0uqtxM', '1-3FqY7K36IKGc1cO1rku1CKLoaAt6UBW')
book_ch1 += '<div class="kab-kicker">Душа и тело. Преподаватель Константин Кальченко</div>\n'
book_ch1 += '<p><strong>Часть 1. Распространённые теории</strong></p>\n' + yt('Душа и тело. Часть 1. Распространённые теории (преподаватель Константин Кальченко)', 'U2plrWYKU40', '1j_2QTpnyDLBKEO5V2BxkAgTcpgwwItyP')
book_ch1 += '<p><strong>Часть 2. Понятие «душа и тело» в каббале</strong></p>\n' + yt('Душа и тело. Часть 2. Понятие «душа и тело» в каббале (преподаватель Константин Кальченко)', 'Y1Q4M9mP-Tk', '18VR-TobC-JUuooLlX2vkx8eLCezadqGQ')
book_ch1 += '<p><strong>Часть 3. Кругообороты согласно каббале</strong></p>\n' + yt('Душа и тело. Часть 3. Кругообороты согласно каббале (преподаватель Константин Кальченко)', '5MXdPHN0cj0', '111KxbhEb74vS6nzcU0A-kww4qjC_wxAr')
book_ch1 += '<div class="kab-kicker">Ответы на вопросы по теме</div>\n'
book_ch1 += yt('Ответы на вопросы по теме «Душа и тело»', 'Gb6us5_nblM', '1nF1Tw3UDvt1_zJRfic4f3baAQz-xvNx4')
book_ch1 += '</div>\n'

# Owner, 14.10.2026: no links to kabbalahmedia.info for students (the chapter with the link to the article
# «Тело и душа» is dropped) and no films in the additional materials (the film «Кругообороты душ» is dropped).
BOOK = [('Лекции урока в записи', book_ch1)]

PAGES = {'p_toc': toc, 'p_intro': intro, 'p_ch1': ch1, 'p_ch2': ch2, 'p_ch3': ch3, 'p_ch4': ch4,
         'page_resume': resume, 'forum_otklik': otklik, 'book_ch1': book_ch1}

# ---------------- Тест «Проверьте себя» (quiz 1240): вопросы и верные ответы из дока, разборы неверных — свои ----------------
# (name, text, [(answer, fraction, feedback)], single) — answers are shuffled by the quiz
THIRD = 0.3333333
MULTI = ' <em>Несколько правильных ответов.</em>'
QUESTIONS = [
    ('У6-01 Что объясняет нам каббала', 'Что объясняет нам каббала?' + MULTI, [
        ('Структуру мироздания', THIRD, 'Верно. Человек раскрывает истинную сущность себя и всего мироздания.'),
        ('Нашу истинную сущность', THIRD, 'Верно. Человек нисходит в наш мир, пока не раскроет истинную сущность себя и всего мироздания.'),
        ('Почему у каждого свои кругообороты', THIRD, 'Верно. Этим занимается раздел каббалы, который называется «кругооборот душ».'),
        ('Как выбрать, кем я хочу быть в следующей жизни', -1.0, 'Нет. Выбирать, кем родиться, каббала не учит: речь о раскрытии сущности себя и мироздания.')], False),
    ('У6-02 До какого времени человек нисходит в наш мир', 'До какого времени человек должен нисходить в наш мир?' + MULTI, [
        ('До полного раскрытия мироздания', 0.5, 'Верно. Пока не раскроет истинную сущность себя и всего мироздания.'),
        ('Пока не поднимется в совершенное состояние', 0.5, 'Верно. Обретя шестой орган чувств и поднявшись в Высший мир, человек постигает самое совершенное состояние.'),
        ('Пока не выполнит 10 кругооборотов', -1.0, 'Числа кругооборотов не задано: человек нисходит в наш мир, пока не раскроет истинную сущность себя и всего мироздания.')], False),
    ('У6-03 Как душа становится совершенной', 'Как душа становится совершенной?', [
        ('Находясь в телесной оболочке, раскрывает шестой орган чувств', 1.0, 'Верно. Цель нахождения в нашем мире — ощутить тело как внешнюю оболочку души, обрести шестой орган чувств и подняться в Высший мир.'),
        ('Ведёт жизнь, полную материальных страданий', 0.0, 'Страдания сами по себе ничего не дают. Цель — ощутить тело как внешнюю оболочку души и обрести шестой орган чувств.'),
        ('Выполняет религиозные обряды', 0.0, 'Обряды здесь ни при чём. Душа становится совершенной, когда человек обретает шестой орган чувств и поднимается в Высший мир.')], True),
    ('У6-04 Как ощутить состояние души вне тела', 'Через что можно ощутить состояние души вне тела?' + MULTI, [
        ('Через шестой орган чувств', 0.5, 'Верно. Состояние души ощущается шестым органом чувств — тем, который развивается в человеке.'),
        ('Ощутив тело как внешнюю оболочку души', 0.5, 'Верно. В этом и состоит цель нахождения человека в нашем мире.'),
        ('Путём медитации', -1.0, 'Нет. Пять органов чувств и всё, что с ними работает, для этого не годятся: состояние души ощущается шестым органом чувств.')], False),
    ('У6-05 Витальная сила и душа', 'Какая связь у витальной силы с душой?', [
        ('Никакой', 1.0, 'Верно. Витальная сила есть у любого живого существа и даёт телу существовать. Душа — духовный орган, который в человеке развивается.'),
        ('Это одно и то же', 0.0, 'Нет. Витальная сила — энергия, как у любого живого существа. Душа — духовный орган, который постепенно зарождается в человеке.'),
        ('Витальная сила — начало души', 0.0, 'Витальная сила не имеет никакого отношения к душе: она только даёт телу существовать.'),
        ('Витальная сила есть только у человека', 0.0, 'Витальная сила есть у любого живого существа — и к душе отношения не имеет.')], True),
    ('У6-06 Зачем множество материальных жизней', 'Зачем исправление души делится на множество материальных жизней?', [
        ('Из кругооборота в кругооборот форма соединения становится всё более возвышенной', 1.0, 'Верно. В земной жизни нас разделяет эгоизм, и через многочисленные кругообороты человечество становится всё более подготовленным к объединению.'),
        ('Тело не способно прожить срок, необходимый для исправления души', 0.0, 'Дело не в сроке жизни тела. Кругообороты нужны, чтобы форма соединения душ становилась всё более возвышенной.'),
        ('Необходимо реализовать всё разнообразие человеческих форм и характеров', 0.0, 'Цель не в разнообразии характеров, а в соединении: из кругооборота в кругооборот форма соединения становится всё более возвышенной.'),
        ('Чтобы в множестве тел человек получил больше удовольствий', 0.0, 'Нет. Через кругообороты человечество становится всё более подготовленным к объединению душ.')], True),
]

for k, v in PAGES.items():
    assert not re.search(r'\bРав\b', v), k
    assert 'М. Лайтман' not in v, k
    open('html/%s.html' % k, 'w', encoding='utf-8', newline='\n').write(v)
print('pages:', ', '.join('%s=%d' % (k, len(v)) for k, v in PAGES.items()))

# ---------------- SQL ----------------
TZ = ZoneInfo('Asia/Jerusalem')
LESSON, CM_LESSON, CM_QUIZ, CM_RESUME, CM_OTKLIK, CM_BOOK = 1967, 13541, 13542, 13543, 13544, 13688
QUIZ, PAGE_RESUME, FORUM_OTKLIK, BOOK_ID = 1240, 2496, 3911, 972
# 17916 map -> 17917 toc -> 17918 intro -> 17921 ch1 -> 17919 ch2 -> 17920 ch3 -> new ch4
PAGE_IDS = {'map': 17916, 'toc': 17917, 'intro': 17918, 'ch1': 17921, 'ch2': 17919, 'ch3': 17920}
LAST_FINISH = 37265   # «Завершить» on 17920 -> «Продолжить»
# Lesson 5 + 7 days, Israel time
EXP_L = int(datetime(2026, 11, 11, 23, 59, tzinfo=TZ).timestamp())   # lecture and otklik: Wednesday 23:59, as the test (owner, 18.10.2026)
EXP_Q = int(datetime(2026, 11, 11, 23, 59, tzinfo=TZ).timestamp())   # test: Wednesday 23:59
# quiz slots -> question versions -> answer ids (from the DB, 14.10.2026)
QIDS = [([68790, 68791], [[284160, 284161, 284162, 284163], [284164, 284165, 284166, 284167]]),
        ([68792], [[284168, 284169, 284170, 284171]]),
        ([68793, 68794], [[284172, 284173, 284174, 284175], [284176, 284177, 284178, 284179]]),
        ([68795], [[284180, 284181, 284182, 284183]]),
        ([68796], [[284184, 284185, 284186, 284187]]),
        ([68797], [[284188, 284189, 284190, 284191]])]

if '--sql' in sys.argv:
    NOW = int(time.time())

    def hx(s):
        return '0x' + binascii.hexlify(s.encode('utf-8')).decode()

    def q(s):
        return "'" + s.replace(chr(92), chr(92) * 2).replace("'", "''") + "'"

    mappage = ('<p><a title="Начать" href="https://edu.kabacademy.com/mod/lesson/view.php?id=%d&amp;pageid=%d"><img class="img-fluid" src="%smaps/L06.png" alt="Карта Урок 6" width="1174" height="915"></a></p>\n'
               % (CM_LESSON, PAGE_IDS['toc'], IMG))
    open('html/p_map.html', 'w', encoding='utf-8', newline='\n').write(mappage)
    sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
    pages = [('map', '*', mappage), ('toc', 'Содержание', toc), ('intro', INTRO, intro)] + [
        ('ch%d' % i, CHAPTERS[i - 1], html) for i, html in enumerate((ch1, ch2, ch3), 1)]
    for key, title, html in pages:
        sql.append("UPDATE mdl_lesson_pages SET title=%s, contents=%s, timemodified=%d WHERE id=%d AND lessonid=%d;" % (q(title), hx(html), NOW, PAGE_IDS[key], LESSON))
    # chapter 3 (17920) was the last page: «Завершить» -> «Продолжить», then a new page for chapter 4
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
            assert len(aids) == 4 and len(answers) in (3, 4)
            sql.append("UPDATE mdl_question SET name=%s, questiontext=%s, timemodified=%d WHERE id=%d;" % (q(name), hx('<p>%s</p>\n' % text), NOW, qid))
            sql.append("UPDATE mdl_qtype_multichoice_options SET single=%d WHERE questionid=%d;" % (1 if single else 0, qid))
            for (atext, frac, fb), aid in zip(answers, aids):
                sql.append("UPDATE mdl_question_answers SET answer=%s, fraction=%s, feedback=%s WHERE id=%d AND question=%d;" % (hx('<p>%s</p>\n' % atext), repr(frac), hx('<p>%s</p>\n' % fb), aid, qid))
            for aid in aids[len(answers):]:
                sql.append("DELETE FROM mdl_question_answers WHERE id=%d AND question=%d;" % (aid, qid))
    sql.append("UPDATE mdl_quiz SET timemodified=%d WHERE id=%d;" % (NOW, QUIZ))
    # résumé, otklik
    sql.append("UPDATE mdl_page SET content=%s, timemodified=%d WHERE id=%d;" % (hx(resume), NOW, PAGE_RESUME))
    sql.append("UPDATE mdl_forum SET intro=%s, timemodified=%d WHERE id=%d;" % (hx(otklik), NOW, FORUM_OTKLIK))
    # open the lecture, test and résumé (the lesson section itself stays date-locked until 06.11 08:00)
    sql.append("UPDATE mdl_course_modules SET visible=1, visibleold=1 WHERE id IN (%d,%d,%d);" % (CM_LESSON, CM_QUIZ, CM_RESUME))
    # all due on Wednesday 23:59 (owner, 18.10.2026)
    sql.append("UPDATE mdl_course_modules SET completionexpected=%d WHERE id IN (%d,%d);" % (EXP_L, CM_LESSON, CM_OTKLIK))
    sql.append("UPDATE mdl_course_modules SET completionexpected=%d WHERE id=%d;" % (EXP_Q, CM_QUIZ))
    sql.append("UPDATE mdl_event SET timestart=%d, timesort=%d, visible=1, timemodified=%d WHERE courseid=236 AND modulename='lesson' AND instance=%d AND eventtype='expectcompletionon';" % (EXP_L, EXP_L, NOW, LESSON))
    sql.append("UPDATE mdl_event SET timestart=%d, timesort=%d, visible=1, timemodified=%d WHERE courseid=236 AND modulename='forum' AND instance=%d AND eventtype='expectcompletionon';" % (EXP_L, EXP_L, NOW, FORUM_OTKLIK))
    sql.append("UPDATE mdl_event SET timestart=%d, timesort=%d, visible=1, timemodified=%d WHERE courseid=236 AND modulename='quiz' AND instance=%d AND eventtype='expectcompletionon';" % (EXP_Q, EXP_Q, NOW, QUIZ))
    sql.append("UPDATE mdl_event SET visible=1, timemodified=%d WHERE courseid=236 AND modulename='lesson' AND instance=%d AND eventtype='open';" % (NOW, LESSON))
    # book 972 (cm 13688, a copy of the lesson-5 book): name and chapters
    sql.append("UPDATE mdl_book SET name='Дополнительные материалы к Уроку 6', timemodified=%d WHERE id=%d;" % (NOW, BOOK_ID))
    sql.append("DELETE FROM mdl_book_chapters WHERE bookid=%d;" % BOOK_ID)
    for i, (title, html) in enumerate(BOOK, 1):
        sql.append("INSERT INTO mdl_book_chapters (bookid,pagenum,subchapter,title,content,contentformat,hidden,timecreated,timemodified,importsrc) "
                   "VALUES (%d,%d,0,%s,%s,1,0,%d,%d,'');" % (BOOK_ID, i, q(title), hx(html), NOW, NOW))
    sql.append("UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;")
    sql += ['COMMIT;',
            "SELECT id,prevpageid,nextpageid,title,LENGTH(contents) clen FROM mdl_lesson_pages WHERE lessonid=%d ORDER BY id;" % LESSON,
            "SELECT id,pageid,jumpto,answer FROM mdl_lesson_answers WHERE lessonid=%d ORDER BY pageid,id;" % LESSON,
            "SELECT question,COUNT(*) n,SUM(fraction) s,SUM(GREATEST(fraction,0)) pos FROM mdl_question_answers WHERE question BETWEEN 68790 AND 68797 GROUP BY question;",
            "SELECT questionid,single FROM mdl_qtype_multichoice_options WHERE questionid BETWEEN 68790 AND 68797;",
            "SELECT id,visible,completionexpected FROM mdl_course_modules WHERE id IN (%d,%d,%d,%d,%d);" % (CM_LESSON, CM_QUIZ, CM_RESUME, CM_OTKLIK, CM_BOOK),
            "SELECT id,name,modulename,instance,eventtype,FROM_UNIXTIME(timestart),visible FROM mdl_event WHERE courseid=236 AND ((modulename='lesson' AND instance=%d) OR (modulename='quiz' AND instance=%d) OR (modulename='forum' AND instance=%d));" % (LESSON, QUIZ, FORUM_OTKLIK),
            "SELECT id,pagenum,title,LENGTH(content) FROM mdl_book_chapters WHERE bookid=%d ORDER BY pagenum;" % BOOK_ID]
    open('lesson6.sql', 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
    print('sql statements:', len(sql), 'bytes:', os.path.getsize('lesson6.sql'), 'EXP_L', EXP_L, 'EXP_Q', EXP_Q)
