# -*- coding: utf-8 -*-
"""Lesson 14 «Духовное и материальное», course 236 — text of the doc «Урок 14 — СО финал» (20.10.2026, 1Eo1jZLHaj9iEDr54PkruthQiVEGkOXat0O1V-MziBfA;
the doc's title is «Духовное и материальное: что меняется», the old theme — «Каббала в современной жизни») in the lesson 1–2 / 6–13 format:
map -> contents (+ three questions) -> «О чём этот урок» (the doc's «Вступление» text) -> chapters 1–6 (+ «Что с этим делать» in chapter 6);
quiz «Проверьте себя» — all six questions of the doc (six slots as in the skeleton); résumé; otklik («Задание»: two questions — the doc's
«Если захочется» is «для себя, не для форума»); book «Дополнительные материалы к Уроку 14» (lectures, the three «Основы каббалы» programs
as archive players). In-text videos: ten fragments cut from archive programs by the windows of «Урок 14 — видеоматериалы» (cut14.sh).
No designer's schemes for this lesson (none delivered; the video doc: «схем в уроке нет»). Everything is due on Wednesday 23:59.

Writes html/*.html; with --sql (and BOOK_ID=<book id>) writes lesson14.sql.
Not taken from the doc: «Как устроен этот урок», «Повторение пройденного» (its recap video is in the book), «Итоговый тест» of the
spring course (9 questions, incl. multi/gapselect), links to kabbalahmedia.info.
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
CHAPTERS = ['Глава 1. Что не меняется', 'Глава 2. Что меняется', 'Глава 3. Здоровье и связь между людьми',
            'Глава 4. Семья как лаборатория', 'Глава 5. Оба начала равноценны', 'Глава 6. Экономика — отражение отношений']
ML = LAITMAN
OK = 'Основы каббалы'

# ---------------- Содержание ----------------
toc = ('<div class="kab-text">\n<div class="kab-quote">\n<ul class="kab-list">\n'
       + ''.join('<li>%s</li>\n' % c for c in [INTRO] + CHAPTERS) +
       '''</ul>
</div>
<div class="kab-kicker">Прежде чем читать</div>
<p>Три вопроса. Держите их в голове, пока читаете.</p>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Что, по-вашему, должно измениться в жизни человека, который четырнадцать недель этим занимается?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Почему люди тысячи лет не могут договориться — при том что все хотят одного?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Где в вашей жизни вы чаще всего сталкиваетесь с чужой волей?</div></div>
</div>
</div>
<p></p>
''')

# ---------------- О чём этот урок («Вступление» дока; видеолекция — в книге) ----------------
intro = ''   # the page title «О чём этот урок» is already shown by the lesson page — no heading inside (owner, 20.10.2026)
intro += p('Используя метод духовного развития, предлагаемый каббалой, мы сможем расширить и переосмыслить отношения с окружающими.')
intro += p('Четырнадцать недель позади, и вопрос уже назрел: <strong>и что, собственно, меняется?</strong> Он законный, и ответ на него должен быть точным. Поэтому сначала — что не меняется.')

# ---------------- Глава 1 ----------------
ch1 = p('<strong>Материальная жизнь сама по себе не улучшается.</strong> Не прибавляется денег, здоровья и удачи. Не исчезают неприятности, не решаются сами проблемы, которые были до.')
ch1 += clip('Lesson-14-clip-K1', title='«Как Творец раскрывается в отношениях между нами?»', source=ML + ', вебинар, 2017',
            quote='«Чем мне, обычному человеку, поможет принятие концепции, что все делается Творцом? Это улучшит мои отношения с другими людьми? — Нет. Если вы имеете в виду, что у вас только чисто практическая задача, как преуспеть в этой жизни, то для этого вам не нужна каббала».')
ch1 += p('<strong>Ничего в укладе менять не требуется.</strong> Работа, семья, привычки, круг общения — всё остаётся как есть. Это уже говорилось в уроке 8.')
ch1 += clip('Lesson-14-clip-K2', title='«Как каббалисты относятся к жизни»', source=ML + ', вебинар, 2018',
            quote='«Каббалист – обычный человек. Он желает, чтобы все вокруг было устроено просто и удобно. […] При этом каббалист не накладывает на себя никаких ограничений. У него есть все, что положено человеку: пища, секс, семья, работа».')
ch1 += p('<strong>Никаких особых состояний не появляется.</strong> Ни видений, ни голосов, ни предчувствий.')
ch1 += p('<strong>И про сроки:</strong> то, о чём идёт речь, вырабатывается годами.')
ch1 += p('Если после этого списка возникает вопрос «а зачем тогда всё это» — он правильный. Дальше на него и отвечаем.')

# ---------------- Глава 2 ----------------
ch2 = h4('Мир оказывается одной системой')
ch2 += p('Первое, что меняется, — <strong>масштаб того, что человек считает связанным.</strong>')
ch2 += p('Современный мир является единой системой. Все население Земли, все страны сейчас становятся элементами одной большой системы связи, которая была когда-то в системе Адам Ришон. И хотя на Земле живет почти 8 миллиардов человек, мир сегодня стал очень тесным и взаимосвязанным.')
ch2 += clip('Lesson-14-clip-K3', title='«Эпоха посткоронавируса. Потребность в социальных связях»', source=ML + ', 2020',
            quote='«Мы связаны в одну общую душу. Наши свойства, ощущения, переживания, мысли – все это общее. Ни у кого из нас нет ничего личного».')
ch2 += p('Осознаем мы это или нет, но общество, в котором нам приходится жить, заставляет нас соответствовать стандартам: как следует одеваться, как обставлять квартиру, какую пищу есть, к чему следует стремиться. Представления о добре и зле, любви, истине, красоте формируются под воздействием общества.')
ch2 += h4('Становится видно, что мешает')
ch2 += p('<strong>Мои желания находятся в эгоцентрическом намерении использовать другого. Поэтому на уровне социума мы никак не можем выстроить нормальную коммуникацию, хотя пытаемся это сделать уже тысячи лет.</strong>')
ch2 += p('Это то же намерение из урока 9, только увиденное на уровне отношений между людьми. Дело не в том, что не нашли правильного устройства или законов. Дело в том, <strong>ради чего</strong> каждый входит в связь.')
ch2 += clip('Lesson-14-clip-K4', title='«Духовные состояния. Братство и единство»', source=ML + ', 2019',
            quote='«Получается, что все мы, конечно, хотим любви, но дать любовь другому – это уже проблема! Это против нас».')
ch2 += p('Единственное, что вызовет равновесие, это когда я буду считаться с другими людьми, при принятии решений учитывать их желания и интересы.')

# ---------------- Глава 3 ----------------
ch3 = p('С точки зрения каббалы, под здоровьем понимается <strong>мера связи человека с общей системой Природы, или Творцом.</strong> Здоровье — это правильная связь между людьми, обществом и Творцом.')
ch3 += p('Природа, или Творец, воздействует на каждого из нас через общество. Я и окружающие меня люди обязаны достичь равновесия между собой, наладить взаимоотношения друг с другом.')
ch3 += p('<strong>Речь об уровне отношений, а не о медицине.</strong> Каббала ни в коем случае не отменяет систему здравоохранения, а, наоборот, добавляет к сфере ее деятельности ответственность за создание правильного окружения.')
ch3 += clip('Lesson-14-clip-K5', title='«Основы каббалы. Каббала и здоровье»', source=ML + ', 2018',
            quote='«Что делать человеку, занимающемуся каббалой, когда у него что-то болит? […] — Он должен идти к врачу. Он ничего не может сделать. До тех пор, пока общество не будет в относительном равновесии, то и каббалисты, и все другие люди будут болеть».')
ch3 += h4('Основа — воспитание')
ch3 += p('Каббалисты называют основой <strong>образование, воспитание и правильную связь между людьми.</strong> Образование здесь — не передача сведений, а среда, которая меняет то, что человек считает важным, — ровно то, что разбиралось в уроке 8.')
ch3 += clip('Lesson-14-clip-K6', title='«Основы каббалы. Каббала и здоровье»', source=ML + ', 2018',
            quote='«Это проблема, которая, в первую очередь, сталкивается с вопросом перевоспитания. Нам надо воспитывать людей».')

# ---------------- Глава 4 ----------------
ch4 = p('Семья является ближайшим окружением человека, с ней связаны самые личные стороны нашей жизни. Вместе с тем, семья — самая малая общность, минимальное окружение, ячейка общества.')
ch4 += p('<strong>Семья является минимальным окружением человека, которое может его поддерживать на духовном пути, на котором он учится любить, поскольку проще всего любить и отдавать в семье.</strong>')
ch4 += clip('Lesson-14-clip-K7', title='«Основы каббалы. Семья и брак»', source=ML + ', 2019',
            quote='«Семья демонстрирует условие, что если человек ощущается мною как близкий, то я готов относиться к нему как к себе. Значит, вся проблема в том, чтобы ощутить каждого близким».')
ch4 += p('<strong>Семья — это группа, маленькая лаборатория для исправления человека, своего рода школа, где можно тренироваться, экспериментировать, чтобы достичь главного результата — создания правильных отношений, в которых раскрывается Творец.</strong> И у неё есть свойство, которого нет у другой среды: из неё нельзя выйти по желанию.')
ch4 += h4('Что такое любовь')
ch4 += p('В обычной жизни мы называем любовью своё отношение к тому, что даёт нам наслаждение. <strong>Любовь на самом деле — это не получение наслаждения от кого-то, а отдача объекту любви.</strong> Наслаждаться от того, что я кого-то люблю, я могу намного больше, чем, если меня любят.')
ch4 += p('Природа закладывает в нас инстинктивную любовь к детям, и мы как бы поневоле заботимся о них, получая пример альтруистического отношения ко всему человечеству, которое по сути тоже является одной большой семьей.')
ch4 += h4('Чего здесь нет')
ch4 += p('<strong>Никаких требований к близким этот урок не предъявляет.</strong>')
ch4 += clip('Lesson-14-clip-K8', title='«Основы каббалы. Мужское и женское начало в каббале»', source=ML + ', 2018',
            quote='«Не пытаться требовать от супруга менять свои свойства? — Никак! Они даны нам от природы и такими же останутся».')
ch4 += clip('Lesson-14-clip-K9', title='«Основы каббалы. Семья и брак»', source=ML + ', 2019',
            quote='«Если домашние не идут твоим путем, не надо посвящать их в свои проблемы. Спокойно проходи все свои состояния, чтобы они этого не чувствовали. Дай им возможность комфортно существовать».')

# ---------------- Глава 5 ----------------
ch5 = p('Каббалисты пишут о том, что взаимоотношения между полами лежат в основе всего мироздания. Люди делятся на мужчин и женщин потому, что существует Творец и творение. Как два партнера в танце.')
ch5 += clip('Lesson-14-clip-K10', title='«Основы каббалы. Мужское и женское начало в каббале»', source=ML + ', 2018',
            quote='«Две силы мироздания – сила отдачи и сила получения – отображаются в материи в образе мужчины и женщины. […] Да, но чисто условно».')
ch5 += p('<strong>Оба начала — и мужское и женское — равноценны относительно замысла творения.</strong> В каждом из нас оба эти начала есть, в разных пропорциях, иначе мы не могли бы ощущать друг друга.')
ch5 += p('Наши различия — это настоящий подарок свыше, благодаря которому, правильно соединяя эти разные начала, мы можем решить самую большую загадку всей природы — ради чего сотворен весь наш мир.')

# ---------------- Глава 6 (+ «Что с этим делать», «Что дальше») ----------------
ch6 = p('<strong>Экономика — это отражение отношений между людьми в обществе, поэтому экономический кризис — это кризис взаимоотношений между людьми.</strong>')
ch6 += p('Примеры: продажа изделий с ограниченным сроком годности, чтобы их покупали вновь и вновь; выбрасывание продуктов питания, чтобы удержать цены на рынке, — при этом половина населения планеты голодает; недостаток доверия, на котором держится вся экономика.')
ch6 += p('Причины, по которым обществу потребления приходит конец: нехватка доверия; ограниченные ресурсы; рост неравенства между богатыми и бедными; эгоистическое развитие — материальные блага больше не удовлетворяют, удовлетворение теперь в связях друг с другом.')
ch6 += p('Задача экономики — обеспечить людям нормальный уровень жизни. Остальное время люди смогут посвятить выстраиванию правильных связей между собой и раскрытию Творца. С точки зрения каббалы, это и является целью творения.')
ch6 += h4('Что с этим делать')
ch6 += p('<strong>Первое. Проверить, чего вы ждали.</strong> Четырнадцать недель назад у вас были какие-то ожидания. Стоит посмотреть, какие из них сбылись, какие нет и какие оказались не про то. Это подготовка к последней неделе.')
ch6 += p('<strong>Второе. Посмотреть на ближний круг.</strong> Не менять в нём ничего. Просто заметить один случай, где столкновение с чужой волей было не помехой, а тем самым материалом, о котором шла речь.')
ch6 += p('<strong>Третье. Ничего не объяснять дома.</strong>')
NEXT = 'Последняя неделя — <strong>итоги и что дальше.</strong> Соберём пройденное и дойдём до вопроса, на который отвечаете только вы: продолжать или нет — и когда это решение получается самым точным.'
ch6 += h4('Что дальше')
ch6 += p(NEXT)

# ---------------- Резюме ----------------
RESUME = [
    '<b>Материальная жизнь сама по себе не улучшается</b>, уклад менять не требуется, особых состояний не появляется.',
    '<b>Мир — единая система.</b> Желания человека находятся в эгоцентрическом намерении использовать другого. Поэтому на уровне социума мы никак не можем выстроить нормальную коммуникацию, хотя пытаемся это сделать уже тысячи лет.',
    '<b>Здоровье — это правильная связь между людьми, обществом и Творцом.</b> Речь об уровне отношений, а не о медицине: каббала не отменяет здравоохранение. Основа — образование, воспитание, правильная связь между людьми.',
    '<b>Семья — основа духовной работы человека.</b> Семья — это группа, маленькая лаборатория для исправления человека, своего рода школа, где можно тренироваться, экспериментировать, чтобы достичь главного результата — создания правильных отношений, в которых раскрывается Творец.',
    '<b>Любовь — это не получение наслаждения от кого-то, а отдача объекту любви.</b>',
    '<b>Оба начала — и мужское и женское — равноценны относительно замысла творения.</b> В каждом из нас оба эти начала есть, в разных пропорциях.',
    '<b>Экономика — это отражение отношений между людьми в обществе</b>, поэтому экономический кризис — это кризис взаимоотношений между людьми.',
    'Люди смогут реализовать свой личный и общественный потенциал, выстраивая правильные связи между собой и раскрывая Творца.',
]
resume = ('<div class="kab-page">\n<div class="kab-card">\n'
          + ''.join('<div class="kab-row"><div class="kab-dot"></div><div>%s</div></div>\n' % r for r in RESUME)
          + '</div>\n<div class="kab-quote kab-quote--purple">%s</div>\n</div>\n' % NEXT.replace('<strong>', '<b>').replace('</strong>', '</b>'))

# ---------------- Отклик недели («Задание»: два вопроса; «Если захочется» в доке — «для себя, не для форума») ----------------
otklik = '''<div class="kab-text">
<div class="kab-quote">Напишите своими словами ответ на любой из вопросов.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Что вы делаете для выстраивания правильных отношений с людьми?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Доставляет ли вам удовольствие отдача другим людям? Кому?</div></div>
</div>
<div class="kab-note">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
</div>
'''

# ---------------- Книга «Дополнительные материалы к Уроку 14» ----------------
book_ch1 = '<div class="kab-page">\n'
book_ch1 += '<div class="kab-kicker">Вступление: видеолекция урока «Мир — единая система». Преподаватель Михаил Санилевич</div>\n'
book_ch1 += yt('Мир — единая система', 'dCHCjWzUrcY', '1aEmq9VtzH_C7VEA0B9dqwHcOW1CY2j48')
book_ch1 += '<div class="kab-kicker">Лекции о семье и работе. Преподаватель Михаил Санилевич</div>\n'
book_ch1 += yt('Семья — основа духовной работы человека', 'yuTVvhX9UOA', '13GGyJhaVg3ROEzwn4sDPJ3qYwmA25doo')
book_ch1 += yt('Каббала и работа', 'GoysbmRlgn8', '1O_yVF-aVsIedz9BsOIUPMiNavOQNtikT')
book_ch1 += '<div class="kab-kicker">Краткое повторение изученного материала</div>\n'
book_ch1 += yt('Краткое повторение изученного материала', 'd4eOBsZElTo', '1fawx91db7p7TAFHpQLeiuF-Swsz-IXnt')
book_ch1 += '<div class="kab-kicker">Клип «Каббала предназначена для всех»</div>\n'
book_ch1 += yt('Каббала предназначена для всех', 's29VFeuBiIQ')
book_ch1 += '</div>\n'
book_ch2 = '<div class="kab-page">\n'
book_ch2 += embed('programs', 'nqMBH5TU', '«Основы каббалы. Каббала и здоровье»', ML + ', 2018, 31 мин.')
book_ch2 += embed('programs', 'CHpGLfy8', '«Основы каббалы. Мужское и женское начало в каббале»', ML + ', 2018, 30 мин.')
book_ch2 += embed('programs', 'W9cZxomk', '«Основы каббалы. Семья и брак»', ML + ', 2019, 31 мин.', last=True)
book_ch2 += '</div>\n'
BOOK = [('Лекции урока в записи', book_ch1),
        ('Передачи «Основы каббалы»: здоровье, мужское и женское начало, семья и брак', book_ch2)]

PAGES = {'p_toc': toc, 'p_intro': intro, 'p_ch1': ch1, 'p_ch2': ch2, 'p_ch3': ch3, 'p_ch4': ch4, 'p_ch5': ch5, 'p_ch6': ch6,
         'page_resume': resume, 'forum_otklik': otklik, 'book_ch1': book_ch1, 'book_ch2': book_ch2}

# ---------------- Тест «Проверьте себя» (quiz 1248, 6 слотов): вопросы и верные ответы из дока, разборы неверных — свои ----------------
QUESTIONS = [
    ('У14-01 Что меняется в материальной жизни', 'Что меняется в материальной жизни человека?', [
        ('Сама по себе — ничего из того, чего обычно ждут', 1.0, 'Верно. «Если у вас только чисто практическая задача, как преуспеть в этой жизни, то для этого вам не нужна каббала».'),
        ('Прибавляется здоровья', 0.0, 'Здоровья не прибавляется: заболел — иди к врачу. Материальная жизнь сама по себе не улучшается.'),
        ('Решаются проблемы', 0.0, 'Проблемы, которые были до, сами не решаются. Меняется другое — масштаб того, что человек считает связанным.'),
        ('Становится легче', 0.0, 'Нет. Не исчезают неприятности и не прибавляется удачи; то, о чём идёт речь, вырабатывается годами.')], True),
    ('У14-02 Почему не выстраивается связь', 'Почему на уровне общества не выстраивается нормальная связь?', [
        ('Из-за намерения использовать другого', 1.0, 'Верно. Пока не меняется то, ради чего каждый входит в связь, любое устройство даёт один и тот же результат.'),
        ('Не найдено правильное устройство', 0.0, 'Дело не в устройстве, а в том, ради чего каждый входит в связь: в намерении использовать другого.'),
        ('Не хватает законов', 0.0, 'Законов хватает: «свобода, равенство, братство» нравятся всем, пока речь об отношении ко мне. Мешает намерение.'),
        ('Люди слишком разные', 0.0, 'Различия — подарок свыше. Мешает намерение использовать другого.')], True),
    ('У14-03 Если заболел', 'Что делать человеку, изучающему каббалу, если он заболел?', [
        ('Идти к врачу', 1.0, 'Верно. Каббала не отменяет систему здравоохранения.'),
        ('Медитировать', 0.0, 'Речь о животном организме, а не о психологии: если болит — к врачу, а лучше к двум-трём.'),
        ('Ждать, пока пройдёт', 0.0, 'Нет. Пока общество не в равновесии, каббалисты болеют, как все, и идут к врачу.'),
        ('Искать причину в себе вместо лечения', 0.0, 'Здоровье в уроке — уровень отношений, а не медицина. Лечиться — у врача.')], True),
    ('У14-04 Почему семья — лаборатория', 'Почему семья названа лабораторией?', [
        ('Там учатся любить и отдавать, и из неё нельзя выйти по желанию', 1.0, 'Верно. Проще всего любить и отдавать в семье.'),
        ('Там спокойнее', 0.0, 'Дело не в спокойствии: в семье учатся любить и отдавать, и из неё нельзя выйти по желанию.'),
        ('Там больше времени', 0.0, 'Время ни при чём. Семья — минимальное окружение, где учатся любить и отдавать.'),
        ('Там все согласны', 0.0, 'Наоборот: там приходится иметь дело с тем, что есть. Поэтому это и лаборатория.')], True),
    ('У14-05 Мужское и женское начала', 'Как соотносятся мужское и женское начала?', [
        ('Равноценны относительно замысла творения', 1.0, 'Верно. В каждом из нас есть оба начала, в разных пропорциях.'),
        ('Одно главное, другое вспомогательное', 0.0, 'Нет. Оба начала равноценны относительно замысла творения.'),
        ('Они не связаны', 0.0, 'Они связаны и дополняют друг друга: без обоих мы не могли бы ощущать друг друга.'),
        ('Об этом не говорится', 0.0, 'Говорится: отношения между полами лежат в основе всего мироздания, и оба начала равноценны.')], True),
    ('У14-06 Почему кризис — кризис отношений', 'Почему экономический кризис называют кризисом отношений?', [
        ('Экономика — отражение отношений между людьми', 1.0, 'Верно. Всё упирается в то, как люди относятся друг к другу.'),
        ('Из-за нехватки денег', 0.0, 'Денег и ресурсов хватило бы: половина планеты голодает, пока продукты выбрасывают ради цен. Дело в отношениях.'),
        ('Из-за плохих законов', 0.0, 'Законы — следствие. Экономика — отражение отношений между людьми в обществе.'),
        ('Из-за технологий', 0.0, 'Технологии ни при чём. Экономический кризис — кризис взаимоотношений между людьми.')], True),
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
LESSON, CM_LESSON, CM_QUIZ, CM_RESUME, CM_OTKLIK, CM_BOOK = 1975, 13661, 13662, 13663, 13664, 13697
QUIZ, PAGE_RESUME, FORUM_OTKLIK = 1248, 2520, 3927
BOOK_ID = int(os.environ.get('BOOK_ID', '0'))
# 17964 map -> 17965 toc -> 17966 intro -> 17969 ch1 -> 17967 ch2 -> 17968 ch3 -> new ch4 -> ch5 -> ch6
PAGE_IDS = {'map': 17964, 'toc': 17965, 'intro': 17966, 'ch1': 17969, 'ch2': 17967, 'ch3': 17968}
LAST_FINISH = 37337   # «Завершить» on 17968 -> «Продолжить»
EXP = int(datetime(2027, 1, 6, 23, 59, tzinfo=TZ).timestamp())   # lecture, otklik, test: Wednesday 23:59
# quiz slots -> question versions -> answer ids (from the DB, 19.10.2026); six slots as in the skeleton
QIDS = [([68854, 68855], [[284416, 284417, 284418, 284419], [284420, 284421, 284422, 284423]]),
        ([68856], [[284424, 284425, 284426, 284427]]),
        ([68857, 68858], [[284428, 284429, 284430, 284431], [284432, 284433, 284434, 284435]]),
        ([68859], [[284436, 284437, 284438, 284439]]),
        ([68860], [[284440, 284441, 284442, 284443]]),
        ([68861], [[284444, 284445, 284446, 284447]])]
ALLQ = [qid for qids, _ in QIDS for qid in qids]

if '--sql' in sys.argv:
    assert BOOK_ID, 'set BOOK_ID'
    NOW = int(time.time())

    def hx(s):
        return '0x' + binascii.hexlify(s.encode('utf-8')).decode()

    def q(s):
        return "'" + s.replace(chr(92), chr(92) * 2).replace("'", "''") + "'"

    mappage = ('<p><a title="Начать" href="https://edu.kabacademy.com/mod/lesson/view.php?id=%d&amp;pageid=%d"><img class="img-fluid" src="%smaps/L14.png" alt="Карта Урок 14" width="1174" height="915"></a></p>\n'
               % (CM_LESSON, PAGE_IDS['toc'], IMG))
    open('html/p_map.html', 'w', encoding='utf-8', newline='\n').write(mappage)
    sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
    pages = [('map', '*', mappage), ('toc', 'Содержание', toc), ('intro', INTRO, intro)] + [
        ('ch%d' % i, CHAPTERS[i - 1], html) for i, html in enumerate((ch1, ch2, ch3), 1)]
    for key, title, html in pages:
        sql.append("UPDATE mdl_lesson_pages SET title=%s, contents=%s, timemodified=%d WHERE id=%d AND lessonid=%d;" % (q(title), hx(html), NOW, PAGE_IDS[key], LESSON))
    last = str(PAGE_IDS['ch3'])
    sql.append("UPDATE mdl_lesson_answers SET answer='Продолжить', jumpto=-1, timemodified=%d WHERE id=%d AND pageid=%s AND lessonid=%d;" % (NOW, LAST_FINISH, last, LESSON))
    new = list(enumerate((ch4, ch5, ch6), 4))
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
    sql.append("UPDATE mdl_book SET name='Дополнительные материалы к Уроку 14', revision=revision+1, timemodified=%d WHERE id=%d;" % (NOW, BOOK_ID))
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
    open('lesson14.sql', 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
    print('sql statements:', len(sql), 'bytes:', os.path.getsize('lesson14.sql'), 'EXP', EXP)
