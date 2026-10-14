# -*- coding: utf-8 -*-
"""Lesson 4 «Строение мироздания», course 236 — text of the doc «Урок 4 — СО финал» (13.10.2026,
1NG9pLdYMuL3b9hyb_e_yROmhnAIQK32y9QIgsHx8y20) in the lesson 1–3 format: map -> contents (+ three questions)
-> chapters 1–5; quiz «Проверьте себя» (6); résumé; otklik (two questions, as in lesson 2);
book «Дополнительные материалы к Уроку 4»; seminar post for the owner.

Writes html/*.html; with --sql writes lesson4.sql (needs the new book id: --book <bookid>).
Not taken from the doc (owner's rule 13.10): «Повторение пройденного», «Вступление» (the video goes to the
book), separate «Задание» (first otklik question) and «Итоговый тест» (9 questions of the spring course).
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
        out += ('<details class="kab-details">\n<summary><span style="color: #3c5d90;">Альтернативный плеер <em>(если видео выше не воспроизводится)</em></span></summary>\n'
                '<br><iframe src="https://drive.google.com/file/d/%s/preview" width="100%%" height="480" allow="autoplay"></iframe></details>\n' % driveid)
    return out


def srcquote(text, who):
    return ('<div class="kab-quote">\n<p>%s</p>\n'
            '<div style="text-align: right; font-size: 13.5px; line-height: 1.5; color: %s; margin-top: 10px;">— %s</div>\n</div>\n' % (text, GRAY, who))


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


CHAPTERS = ['Глава 1. Ацмуто и замысел творения', 'Глава 2. Четыре стадии', 'Глава 3. Сокращение света и миры',
            'Глава 4. Время, место и движение в духовном мире', 'Глава 5. Язык ветвей']

# ---------------- Содержание (как в уроках 2–3) ----------------
toc = ('<div class="kab-text">\n<div class="kab-quote">\n<ul class="kab-list">\n'
       + ''.join('<li>%s</li>\n' % c for c in CHAPTERS) +
       '''</ul>
</div>
<div class="kab-kicker">Прежде чем читать</div>
<p>Три вопроса для размышления.</p>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Если изначально было только желание отдавать, откуда взялось желание получать?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Почему нельзя было просто дать творению наслаждение и на этом закончить?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Что значит «уподобиться» — и почему без этого наслаждения не получается?</div></div>
</div>
</div>
<p></p>
''')

# ---------------- Глава 1 ----------------
ch1 = ''
ch1 += p('<strong>Существует Высшая сила, которая называется Ацмуто</strong>, что в переводе означает «сама по себе», суть, сущность. Эта сила непознаваема в принципе, потому что не имеет связи с творением.')
ch1 += p('Ацмуто — это категория, означающая нечто само по себе. Зарождение мысли о будущем творении является уже внешним проявлением и непосредственно к Ацмуто отношения не имеет. Оно относится исключительно к будущему творению.')
ch1 += figure('L04-01', 10, 800, 527,
              'Ацмуто за рамкой — не обсуждается; о чём говорим: то, что до нас доходит, замысел насладить творение, свет и желание получать, четыре стадии и миры',
              'Ацмуто — за рамкой: о сущности самой по себе не говорится ничего. Разговор идёт о том, что до нас доходит: о замысле насладить творение, о свете и желании получать, о четырёх стадиях и мирах.')
ch1 += p('<strong>Мысль создать творение для того, чтобы наполнить его абсолютным наслаждением, называется «Творец».</strong> Сама эта мысль. Результатом этой мысли, её отпечатком, является творение, которое наслаждается этой мыслью.')
ch1 += p('В какой мере оно может наслаждаться ею? <strong>В мере своего подобия.</strong> Эта мысль — совершенство, она наивысшая. Поэтому привести творение к совершенству, к наслаждению, значит поднять его на уровень этой мысли, Замысла творения. Подобие этой мысли означает в творении его исправление и возвышение, достижение им наивысшего, наилучшего состояния.')
ch1 += p('<strong>Целью творения является создание новой, ранее отсутствовавшей субстанции</strong>, называемой «творение», и наполнение её абсолютным наслаждением. Поэтому в природу своего создания Творец заложил всепоглощающее желание получить наслаждение.')
ch1 += p('«Желание получать» образно можно представить в виде сосуда (кли), ёмкость которого соответствует величине желания, а получаемое наслаждение — количеству света, наполняющего этот сосуд.')
ch1 += p('Свет, исходящий от Творца, существовал и до появления творения. Желание же получать наслаждение в самом Творце отсутствует, оно относится к вновь созданному творению. Поэтому говорят: <strong>«нечто, возникшее из ничего»</strong>, «существующее из ничего».')

# ---------------- Глава 2 ----------------
ch2 = ''
ch2 += p('Творение начинается с замысла Творца создать творение. Оно было создано из ничего и представляет собой желание получить наслаждение, которое испытывает сам Творец. Эта стадия зарождения творения называется <strong>нулевая стадия, или кетер.</strong>')
ch2 += p('Из замысла Творца, кетера, возникает <strong>первая стадия желания — хохма.</strong> Она желает получать свет и наслаждаться им, в полном согласии с замыслом Творца.')
ch2 += figure('L04-02', 11, 800, 400,
              'Полусфера — желание; сверху в неё входит свет наслаждения',
              'Первая стадия — хохма: свет наслаждения входит в желание и наполняет его. Та же полусфера, что в уроке 1.')
ch2 += p('В конце первой стадии желание начинает ощущать свой источник, который даёт ему наслаждение. Оно чувствует, что источник обладает желанием отдавать, а у него есть только желание получать. Это рождает стремление стать подобным источнику и отдавать, как и он. Так возникает <strong>вторая стадия — бина.</strong>')
ch2 += figure('L04-03', 12, 800, 364,
              'Вторая стадия: вокруг полусферы вторая оболочка, стрелка направлена вверх, наружу; подпись «бэт · 2, кли = желание отдавать»',
              'Вторая стадия — бина: желание хочет отдавать, как источник. Стрелка направлена наружу, к дающему.')
ch2 += clip('Lesson-04-clip-02', title='«Как маленький человек относительно большого»', source='Михаэль Лайтман, вебинар «Схема мироздания. 10 сфирот души», 2017',
            quote='«Желание начинает воспринимать свойства света и становится подобным ему, как маленький человек относительно большого, когда он воспринимает от взрослого его свойства, привычки и взгляды».')
ch2 += p('Уподобляясь источнику, бина реализует возникшее в ней желание отдавать, но при этом не совершает никакого действия. Чтобы отдавать что-то Творцу, необходимо получить от Него. Отсюда возникает <strong>третья стадия — получать, чтобы отдавать.</strong> Она называется <strong>зеир анпин.</strong>')
ch2 += figure('L04-04', 13, 800, 364,
              'Третья стадия: три оболочки, одна стрелка входит внутрь, другая направлена наружу; подпись «гимэль · 3, желание получать для отдачи»',
              'Третья стадия — зеир анпин: получать, чтобы отдавать. Две стрелки одновременно — свет входит внутрь, а направление — к дающему.')
ch2 += p('В <strong>четвёртой стадии — малхут</strong> — в творении возникает осознанное, самостоятельное желание получать от самого Творца. Это обусловлено тем, что после стадии бина творение ощущает не только исходящее от Творца наслаждение, но и Его суть. Малхут желает одновременно ощущать Творца и наполняться Его наслаждением.')
ch2 += figure('L04-05', 14, 800, 1200,
              'Все стадии подряд сверху вниз: от Творца исходит свет (0), ниже желание получать (1), желание отдавать (2), желание получать для отдачи (3), малхут (4)',
              'Все стадии подряд: свет, исходящий от Творца (0), желание получать (1), желание отдавать (2), желание получать ради отдачи (3), малхут (4). Слева — буквы, которыми стадии обозначают в каббале: шорэш (корень), алеф, бэт, гимэль, далет.')
ch2 += clip('Lesson-04-clip-01', title='«Четыре стадии прямого света»', source='Михаэль Лайтман, вебинар «Как Творец управляет тобой и миром?», 2019',
            quote='«Свет — это желание отдавать, или нулевая стадия, называемая Кетэр. Она строит первую стадию — кли, которое называется Хохма, или желание получать. Потом это желание преобразуется во вторую стадию — Бину, или желание отдавать обратно, которое уже исходит от творения. Бина рождает третью стадию — Зэир Анпин, состоящий из двух частей: одна из них подобна Бине, другая — Хохме. А затем уже выстраивается Малхут, которая полностью желает получать. Окончательная Малхут и представляет собой творение».')
ch2 += table(['Стадия', 'Название', 'Что происходит'], [
    ['Нулевая', 'кетер', 'замысел Творца создать творение и насладить его'],
    ['Первая', 'хохма', 'желание получать свет и наслаждаться им'],
    ['Вторая', 'бина', 'стремление стать подобным источнику и отдавать'],
    ['Третья', 'зеир анпин', 'получать, чтобы отдавать'],
    ['Четвёртая', 'малхут', 'самостоятельное желание получать от самого Творца — ощущать Его и наполняться Его наслаждением']])
ch2 += clip('Lesson-04-clip-K1', '4 / 3', 640, title='«Четыре стадии распространения света»', source='курс «Основы каббалы», 2011')

# ---------------- Глава 3 ----------------
ch3 = ''
ch3 += p('<strong>Свет сокращает себя, создаёт ступени скрытия, называемые мирами. Из состояния скрытия творение должно самостоятельно раскрыть Творца.</strong>')
ch3 += clip('Lesson-04-clip-04', title='«Ступени скрытия образуют лестницу»', source='Михаэль Лайтман, «Введение в науку каббала», урок 4, 2023',
            quote='«Это ступени скрытия. Множество ступеней скрытия, последовательно соединяясь между собой, образуют лестницу, называемую мирами. […] Главное, что наивысшая ступень — это Творец, наинизшая — это самая низкая ступень нашего мира, скрытия. […] В принципе, есть пять основных видов скрытия, то есть пять миров».')
ch3 += p('Стадиям зарождения творения — четырём стадиям прямого света — соответствуют <strong>пять духовных миров</strong>: Адам Кадмон, Ацилут, Брия, Ецира и Асия.')
ch3 += table(['Стадия', 'Мир'], [['кетер', 'Адам Кадмон'], ['хохма', 'Ацилут'], ['бина', 'Брия'], ['зеир анпин', 'Ецира'], ['малхут', 'Асия']])
ch3 += srcquote('«Раскрытия света во мне называются мирами. „Мир“ на иврите „олам“, „алама“ — от слова „сокрытие“. […] Когда я полностью скрываю себя, то это мир Асия. Выше него, когда я скрываю себя чуть поменьше, — это мир Ецира. Далее — мир Брия. А когда я начинаю серьёзно раскрывать свет — это мир Ацилут. Полное раскрытие света называется миром Адам Кадмон».',
                'Михаэль Лайтман, «Основы каббалы. Строение мироздания», 2018')

# ---------------- Глава 4 ----------------
ch4 = ''
ch4 += p('<strong>Описание духовного мира есть описание человеческой души и ступеней её сближения с Творцом</strong>, то есть всё большего ощущения Создателя. Каббала делит общую душу на части, даёт каждой из них определённое название согласно её свойству и описывает действия этих составляющих.')
ch4 += p('В Высших мирах отсутствуют такие физические понятия, как время, пространство, движение.')
ch4 += p('<strong>Вместо времени</strong> в духовных мирах рассматриваются причинно-следственные связи, переход одного состояния в другое, видоизменения формы. Если вы спросите, сколько времени понадобилось для протекания тех или иных процессов, то вопрос не будет иметь смысла, так как под временем понимается лишь последовательность действий. Поэтому в каббале материал изучается по цепочке — от причины каждого явления к его следствию, или, что одно и то же, от начала творения до его грядущего окончания.')
ch4 += p('<strong>Место</strong> — это не определённая, занимаемая телом часть пространства, а положение на шкале духовных свойств, качеств, где за сто условно принимается свойство Творца, а за нулевую отметку — первоначальное качество творения.')
ch4 += p('Таким образом, <strong>перемещение в духовном мире — это изменение духовным объектом своих свойств.</strong> Он как бы перемещается в духовном пространстве по шкале соответствующих ценностей — ближе или дальше относительно Творца. Однако в духовном мире объект не исчезает, там лишь возникает его новая форма.')
ch4 += p('Другими словами, с появлением новых качеств от духовного объекта отделяется его обновлённая форма, а прежняя продолжает существовать, как и ранее. Было одно духовное тело, оно изменило свои свойства, тотчас же отделилось от старого тела и стало существовать самостоятельно.')
ch4 += clip('Lesson-04-clip-03', title='«Место, время и пространство»', source='Михаэль Лайтман, вебинар «Схема мироздания: место, время и пространство», 2017',
            quote='«В духовном мире нет времени, места, пространства, перемещения. […] Духовный мир состоит всего лишь из двух компонентов: света и материи — желания. […] А творение, чтобы получить свет, должно достичь подобия ему».')

# ---------------- Глава 5 ----------------
ch5 = ''
ch5 += p('Как говорить о мирах, которых мы не ощущаем? У людей в нашем мире существует общность ощущений, но как передать собеседнику знания об объекте, которого он никогда не видел? Это возможно только при наличии связи миров.')
ch5 += p('Всё, что существует в нашем мире, в обязательном порядке начинается в мире Высшем, а затем постепенно нисходит в наш. Каббалисты видят высший объект — корень — и низший объект в нашем мире, получающий воздействие от Высшего и являющийся его порождением. Поэтому каббалисты могут называть объекты-корни в Высших мирах именами их материальных следствий, ветвей в нашем мире.')
ch5 += srcquote('<strong>«Язык каббалистов — это язык ветвей, что означает, что он создан на основании указаний, полученных ветвями от их корней, обязательно существующих в высшем мире и являющихся эталоном для своих ветвей. Ведь нет ничего в низшем мире, что не брало бы своего начала и не проистекало бы из высшего по отношению к нему мира, подобно тому, как оттиск повторяет форму печати».</strong>',
                'Бааль Сулам, «Суть науки каббала»')
ch5 += p('Отсюда и наименование: «язык ветвей», а не язык корней — ведь это корням даётся название ветвей, а не наоборот. Итак, чтобы описать строение мироздания, каббалисты берут названия в нашем мире и с их помощью описывают высшие объекты, являющиеся корнями нашего мира.')
NEXT = 'На следующей неделе — <strong>единственность Творца.</strong> Если сила одна и она абсолютное добро, откуда в мире то, что мы переживаем как зло?'
ch5 += h4('Что дальше')
ch5 += p(NEXT)

# ---------------- Резюме ----------------
RESUME = [
    '<b>Существует Высшая сила, которая называется Ацмуто.</b>',
    'В Ацмуто возникла мысль: создать творение для того, чтобы его насладить.',
    '<b>Свет сокращает себя, создаёт ступени скрытия, называемые мирами.</b>',
    'Из состояния скрытия творение должно самостоятельно раскрыть Творца.',
    'Исходная точка возникновения творения — ноль, «существующее из ничего».',
    '<b>Мысль о создании творения, чтобы насладить его, называется «Творец».</b>',
    'Творением является желание наслаждаться этой мыслью.',
    '<b>Творение наслаждается в той мере, в какой оно уподобляется этой мысли.</b>',
    'Уподобление мысли означает исправление и возвышение до уровня Творца.',
    'Цель творения — создание новой субстанции и наполнение её наслаждением.',
    'Четыре стадии прямого света — кетер, хохма, бина, зеир анпин, малхут; им соответствуют пять миров — Адам Кадмон, Ацилут, Брия, Ецира, Асия.',
    'В духовном мире нет времени, места и движения: время — последовательность причин и следствий, место — положение на шкале свойств, перемещение — изменение свойств.',
    'Каббалисты описывают высшие корни именами их ветвей в нашем мире — это язык ветвей.',
]
resume = ('<div class="kab-page">\n<div class="kab-card">\n'
          + ''.join('<div class="kab-row"><div class="kab-dot"></div><div>%s</div></div>\n' % r for r in RESUME)
          + '</div>\n<div class="kab-quote kab-quote--purple">%s</div>\n</div>\n' % NEXT.replace('<strong>', '<b>').replace('</strong>', '</b>'))

# ---------------- Отклик недели (как в уроке 2: два вопроса) ----------------
otklik = '''<div class="kab-text">
<div class="kab-quote">Напишите своими словами ответ на любой из вопросов.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Что такое духовные миры?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Из четырёх стадий выберите ту, которая показалась понятнее остальных, и опишите её одной фразой своими словами — без слов на иврите.</div></div>
</div>
<div class="kab-note">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
</div>
'''

# ---------------- Книга «Дополнительные материалы к Уроку 4» ----------------
book_ch1 = '<div class="kab-page">\n<div class="kab-quote">Лекции по теме урока — в записи. Тот же материал в лекционном изложении; ядро урока на него не опирается.</div>\n'
book_ch1 += '<div class="kab-kicker">Вступление</div>\n'
book_ch1 += yt('Основы каббалы. Строение мироздания', 'PvHqBR4SFY0', '1iNhXQ0gOGrxD0drPIQ4H4QYVTSceZbwA')
book_ch1 += '<div class="kab-kicker">Описание духовных миров. Преподаватель Гавриэль Шустерман</div>\n'
book_ch1 += yt('Описание духовных миров (преподаватель Гавриэль Шустерман)', 'ySyhQj0fOxc', '1-7-58JLvuVcYhnUIoFL04FWOTRbduzgO')
book_ch1 += '<div class="kab-kicker">Язык ветвей. Преподаватель Гавриэль Шустерман</div>\n'
book_ch1 += yt('Язык ветвей (преподаватель Гавриэль Шустерман)', 'NbR4ojynFgo', '1iOMkA2yJYRZdgQu1lqm8agw8hoGmo0UJ')
book_ch1 += '<div class="kab-kicker">Ответы на вопросы по теме</div>\n'
book_ch1 += yt('Ответы на вопросы по теме «Строение мироздания»', 'MLSOjg7XR5E', '1TnIEbwJ6NZiu_Yo7ZY_e8Wc2Eburv8hA')
book_ch1 += '</div>\n'

book_ch2 = '''<div class="kab-page">
<div class="kab-quote">Урок курса «Основы каббалы» 2011 года, 58 минут. Схема мироздания и четыре стадии развития желания: кто создал желание отдавать, идея сокращения, точка в сердце.</div>
<div style="position: relative; width: 100%; aspect-ratio: 16 / 9; margin: 16px 0;">
<iframe src="https://kabbalahmedia.info/lessons/cu/AtE3Ww80?mediaType=video&amp;shareLang=ru&amp;embed=1&amp;autoPlay=0" style="position: absolute; inset: 0; width: 100%; height: 100%; border: 0;" scrolling="no" allowfullscreen=""></iframe>
</div>
<p><a class="kab-link" href="https://kabbalahmedia.info/ru/lessons/cu/AtE3Ww80" target="_blank" rel="noopener">Открыть в архиве</a></p>
</div>
'''

book_ch3 = '''<div class="kab-page">
<div class="kab-quote">Факультатив, к неделе не привязан. Коротко о теме всего урока.</div>
<p style="text-align: center;"><a class="external-media-provider" href="https://www.youtube.com/watch?v=yT0b880qoS4">Мультфильм «Строение мироздания. Коротко о каббале — 15»</a></p>
</div>
'''
BOOK = [('Лекции урока в записи', book_ch1),
        ('Урок «Схема мироздания. Четыре стадии развития желания» (курс «Основы каббалы», 2011)', book_ch2),
        ('Мультфильм «Строение мироздания. Коротко о каббале — 15»', book_ch3)]

# ---------------- Семинар: текст темы (пост публикует владелец, как в уроках 1–3) ----------------
seminar_post = '''<div class="kab-text">
<div class="kab-quote">На этой неделе у нас семинар на тему «Язык ветвей».<br>Приглашаем всех посмотреть ролик и ответить на вопрос:<br><em>Как язык ветвей помогает нам понять духовные явления и процессы?</em></div>
<p style="text-align: center;"><a class="kab-btn" href="https://www.youtube.com/watch?v=-MOsnBvt4es" target="_blank" rel="noopener">Семинар 4 «Язык ветвей»</a></p>
<details class="kab-details">
<summary>Альтернативный плеер <em>(если видео выше не воспроизводится)</em></summary>
<br><iframe src="https://drive.google.com/file/d/1FdC7t9vn24sO0AExqrARUTUH4cobSaUI/preview" width="100%" height="480" allow="autoplay"></iframe>
</details>
<div class="kab-note">Для ответа нажмите «Ответить» справа внизу под этим сообщением.</div>
</div>
'''

PAGES = {'p_toc': toc, 'p_ch1': ch1, 'p_ch2': ch2, 'p_ch3': ch3, 'p_ch4': ch4, 'p_ch5': ch5,
         'page_resume': resume, 'forum_otklik': otklik, 'book_ch1': book_ch1, 'book_ch2': book_ch2,
         'book_ch3': book_ch3, 'seminar_post': seminar_post}

# ---------------- Тест «Проверьте себя» (quiz 1238): вопросы и верные ответы из дока, разборы неверных — свои ----------------
# (name, text, [(answer, fraction, feedback)]) — first answer is the right one (answers are shuffled by the quiz)
QUESTIONS = [
    ('У4-01 Что можно сказать об Ацмуто', 'Что можно сказать об Ацмуто?', [
        ('Она непознаваема в принципе', 1.0, 'Верно. Ацмуто не имеет связи с творением. Мысль о творении — уже внешнее проявление и к самой Ацмуто отношения не имеет.'),
        ('Это свет', 0.0, 'Свет исходит от Творца и наполняет творение. Ацмуто — сущность сама по себе, без связи с творением, и она непознаваема в принципе.'),
        ('Это мысль о наслаждении', 0.0, 'Мысль создать творение и насладить его называется «Творец». Это уже внешнее проявление, к самой Ацмуто оно отношения не имеет.'),
        ('Это первая стадия', 0.0, 'Первая стадия — хохма, желание получать, то есть уже творение. Ацмуто к стадиям не относится: она непознаваема в принципе.')]),
    ('У4-02 Что называется «Творец»', 'Что в этом уроке называется «Творец»?', [
        ('Мысль создать творение, чтобы наполнить его наслаждением', 1.0, 'Верно. Сама эта мысль. А творение — её отпечаток.'),
        ('Ацмуто', 0.0, 'Ацмуто — сущность сама по себе, о ней сказать нечего. «Творцом» называется мысль создать творение и насладить его.'),
        ('Свет', 0.0, 'Свет — наслаждение, которое наполняет творение. «Творцом» называется сама мысль создать творение и насладить его.'),
        ('Первая стадия', 0.0, 'Первая стадия — хохма, желание получать, то есть уже творение. «Творец» — мысль, из которой оно возникло.')]),
    ('У4-03 В какой мере творение наслаждается', 'В какой мере творение может наслаждаться?', [
        ('В мере своего подобия замыслу', 1.0, 'Верно. Привести творение к наслаждению — значит поднять его на уровень замысла. Это тот же закон подобия свойств, что в уроке 3.'),
        ('В мере своего желания', 0.0, 'Желание задаёт ёмкость сосуда, но наслаждается творение не по величине желания, а в мере подобия замыслу.'),
        ('В мере полученного света', 0.0, 'Свет существовал и до появления творения. Сколько наслаждения творение ощутит, определяет его подобие замыслу.'),
        ('Безгранично', 0.0, 'Замысел совершенен, но творение наслаждается им в мере своего подобия этой мысли.')]),
    ('У4-04 Что рождает бину', 'Что рождает вторую стадию — бину?', [
        ('Желание ощутило источник и захотело стать подобным ему', 1.0, 'Верно. Оно чувствует, что источник отдаёт, а у него есть только желание получать, — и стремится стать подобным.'),
        ('Свет кончился', 0.0, 'Свет не кончается. Бина возникает, когда желание ощущает дающего и хочет стать подобным ему.'),
        ('Желание стало больше', 0.0, 'Дело не в величине желания. В конце первой стадии желание ощущает источник и хочет отдавать, как и он.'),
        ('Так было задумано без причины', 0.0, 'Причина есть: желание ощутило источник наслаждения и захотело стать подобным ему. Каббала изучает цепочку причин и следствий.')]),
    ('У4-05 Почему возникает зеир анпин', 'Почему возникает третья стадия — зеир анпин?', [
        ('Чтобы отдавать Творцу, нужно получить от Него', 1.0, 'Верно. Бина хочет отдавать, но действия не совершает. Отсюда третья стадия: получать, чтобы отдавать.'),
        ('Бина устала отдавать', 0.0, 'Бина как раз ничего не отдаёт: действия в ней нет. Чтобы отдать что-то Творцу, нужно сначала получить от Него.'),
        ('Свет ушёл', 0.0, 'Свет никуда не уходит. Третья стадия возникает потому, что отдавать Творцу можно, только получив от Него.'),
        ('Желание исчезло', 0.0, 'Желание не исчезает ни в одной из стадий. В третьей оно получает, чтобы отдавать.')]),
    ('У4-06 Место в духовном мире', 'Что такое место в духовном мире?', [
        ('Положение на шкале духовных свойств', 1.0, 'Верно. Поэтому и перемещение в духовном мире — изменение свойств: ближе или дальше относительно Творца.'),
        ('Часть пространства, занятая телом', 0.0, 'Так определяется место в нашем мире. В духовном пространства нет: место — положение на шкале свойств.'),
        ('Расстояние до Творца в километрах', 0.0, 'Духовные объекты разделяет не расстояние, а отличие свойств. Место — положение на шкале духовных свойств.'),
        ('Его не существует', 0.0, 'Понятие места в духовном есть, только это не часть пространства, а положение на шкале духовных свойств.')]),
]

for k, v in PAGES.items():
    assert not re.search(r'\bРав\b', v), k
    assert 'М. Лайтман' not in v, k
    open('html/%s.html' % k, 'w', encoding='utf-8', newline='\n').write(v)
print('pages:', ', '.join('%s=%d' % (k, len(v)) for k, v in PAGES.items()))

# ---------------- SQL ----------------
TZ = ZoneInfo('Asia/Jerusalem')
LESSON, CM_LESSON, CM_QUIZ, CM_RESUME, CM_OTKLIK = 1965, 13509, 13510, 13511, 13512
QUIZ, PAGE_RESUME, FORUM_OTKLIK = 1238, 2490, 3905
# 17904 map -> 17905 toc -> 17906 ch1 -> 17909 ch2 -> 17907 ch3 -> 17908 ch4 -> new ch5
PAGE_IDS = {'map': 17904, 'toc': 17905, 'ch1': 17906, 'ch2': 17909, 'ch3': 17907, 'ch4': 17908}
# Lesson 3 + 7 days, Israel time (25.10 the clocks go back: count in local time, not +604800 s)
EXP_L = int(datetime(2026, 10, 27, 23, 59, tzinfo=TZ).timestamp())   # lecture and otklik: Tuesday 23:59
EXP_Q = int(datetime(2026, 10, 28, 23, 59, tzinfo=TZ).timestamp())   # test: Wednesday 23:59
# quiz slots -> question versions -> answer ids (from the DB, 14.10.2026)
QIDS = [([68774, 68775], [[284096, 284097, 284098, 284099], [284100, 284101, 284102, 284103]]),
        ([68776], [[284104, 284105, 284106, 284107]]),
        ([68777, 68778], [[284108, 284109, 284110, 284111], [284112, 284113, 284114, 284115]]),
        ([68779], [[284116, 284117, 284118, 284119]]),
        ([68780], [[284120, 284121, 284122, 284123]]),
        ([68781], [[284124, 284125, 284126, 284127]])]

if '--sql' in sys.argv:
    book = int(sys.argv[sys.argv.index('--book') + 1]) if '--book' in sys.argv else None
    NOW = int(time.time())

    def hx(s):
        return '0x' + binascii.hexlify(s.encode('utf-8')).decode()

    def q(s):
        return "'" + s.replace(chr(92), chr(92) * 2).replace("'", "''") + "'"

    mappage = ('<p><a title="Начать" href="https://edu.kabacademy.com/mod/lesson/view.php?id=%d&amp;pageid=%d"><img class="img-fluid" src="%smaps/L04.png" alt="Карта Урок 4" width="1174" height="915"></a></p>\n'
               % (CM_LESSON, PAGE_IDS['toc'], IMG))
    open('html/p_map.html', 'w', encoding='utf-8', newline='\n').write(mappage)
    sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
    pages = [('map', '*', mappage), ('toc', 'Содержание', toc)] + [
        ('ch%d' % i, CHAPTERS[i - 1], html) for i, html in enumerate((ch1, ch2, ch3, ch4), 1)]
    for key, title, html in pages:
        sql.append("UPDATE mdl_lesson_pages SET title=%s, contents=%s, timemodified=%d WHERE id=%d AND lessonid=%d;" % (q(title), hx(html), NOW, PAGE_IDS[key], LESSON))
    # chapter 4 (17908) was the last page: «Завершить» -> «Продолжить», then a new page for chapter 5
    sql.append("UPDATE mdl_lesson_answers SET answer='Продолжить', jumpto=-1, timemodified=%d WHERE id=37247 AND pageid=17908 AND lessonid=%d;" % (NOW, LESSON))
    sql.append("INSERT INTO mdl_lesson_pages (lessonid,prevpageid,nextpageid,qtype,qoption,layout,display,timecreated,timemodified,title,contents,contentsformat) "
               "SELECT lessonid,17908,0,qtype,qoption,layout,display,%d,%d,%s,%s,contentsformat FROM mdl_lesson_pages WHERE id=17908;" % (NOW, NOW, q(CHAPTERS[4]), hx(ch5)))
    sql.append("SET @n5 = LAST_INSERT_ID();")
    sql.append("UPDATE mdl_lesson_pages SET nextpageid=@n5 WHERE id=17908;")
    sql.append("INSERT INTO mdl_lesson_answers (lessonid,pageid,jumpto,grade,score,flags,timecreated,timemodified,answer,answerformat,response,responseformat) VALUES "
               "(%d,@n5,-40,0,0,0,%d,%d,'Вернуться',0,NULL,0),(%d,@n5,-9,0,0,0,%d,%d,'Завершить',0,NULL,0);" % (LESSON, NOW, NOW, LESSON, NOW, NOW))
    sql.append("UPDATE mdl_lesson SET timemodified=%d WHERE id=%d;" % (NOW, LESSON))
    # quiz
    for (name, text, answers), (qids, aidss) in zip(QUESTIONS, QIDS):
        for qid, aids in zip(qids, aidss):
            assert len(aids) == len(answers) == 4
            sql.append("UPDATE mdl_question SET name=%s, questiontext=%s, timemodified=%d WHERE id=%d;" % (q(name), hx('<p>%s</p>\n' % text), NOW, qid))
            sql.append("UPDATE mdl_qtype_multichoice_options SET single=1 WHERE questionid=%d;" % qid)
            for (atext, frac, fb), aid in zip(answers, aids):
                sql.append("UPDATE mdl_question_answers SET answer=%s, fraction=%s, feedback=%s WHERE id=%d AND question=%d;" % (hx('<p>%s</p>\n' % atext), repr(frac), hx('<p>%s</p>\n' % fb), aid, qid))
    sql.append("UPDATE mdl_quiz SET timemodified=%d WHERE id=%d;" % (NOW, QUIZ))
    # résumé, otklik
    sql.append("UPDATE mdl_page SET content=%s, timemodified=%d WHERE id=%d;" % (hx(resume), NOW, PAGE_RESUME))
    sql.append("UPDATE mdl_forum SET intro=%s, timemodified=%d WHERE id=%d;" % (hx(otklik), NOW, FORUM_OTKLIK))
    # open the lecture, test and résumé (the lesson section itself stays date-locked until 23.10 08:00)
    sql.append("UPDATE mdl_course_modules SET visible=1, visibleold=1 WHERE id IN (%d,%d,%d);" % (CM_LESSON, CM_QUIZ, CM_RESUME))
    # dates as in lesson 3 + 7 days: lecture/otklik Tuesday, test Wednesday
    sql.append("UPDATE mdl_course_modules SET completionexpected=%d WHERE id IN (%d,%d);" % (EXP_L, CM_LESSON, CM_OTKLIK))
    sql.append("UPDATE mdl_course_modules SET completionexpected=%d WHERE id=%d;" % (EXP_Q, CM_QUIZ))
    sql.append("UPDATE mdl_event SET timestart=%d, timesort=%d, visible=1, timemodified=%d WHERE courseid=236 AND modulename='lesson' AND instance=%d AND eventtype='expectcompletionon';" % (EXP_L, EXP_L, NOW, LESSON))
    sql.append("UPDATE mdl_event SET timestart=%d, timesort=%d, visible=1, timemodified=%d WHERE courseid=236 AND modulename='forum' AND instance=%d AND eventtype='expectcompletionon';" % (EXP_L, EXP_L, NOW, FORUM_OTKLIK))
    sql.append("UPDATE mdl_event SET timestart=%d, timesort=%d, visible=1, timemodified=%d WHERE courseid=236 AND modulename='quiz' AND instance=%d AND eventtype='expectcompletionon';" % (EXP_Q, EXP_Q, NOW, QUIZ))
    sql.append("UPDATE mdl_event SET visible=1, timemodified=%d WHERE courseid=236 AND modulename='lesson' AND instance=%d AND eventtype='open';" % (NOW, LESSON))
    # book (a copy of book 968 made by cm_duplicate): name and chapters
    if book:
        sql.append("UPDATE mdl_book SET name='Дополнительные материалы к Уроку 4', timemodified=%d WHERE id=%d;" % (NOW, book))
        sql.append("DELETE FROM mdl_book_chapters WHERE bookid=%d;" % book)
        for i, (title, html) in enumerate(BOOK, 1):
            sql.append("INSERT INTO mdl_book_chapters (bookid,pagenum,subchapter,title,content,contentformat,hidden,timecreated,timemodified,importsrc) "
                       "VALUES (%d,%d,0,%s,%s,1,0,%d,%d,'');" % (book, i, q(title), hx(html), NOW, NOW))
    sql.append("UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;")
    sql += ['COMMIT;',
            "SELECT id,prevpageid,nextpageid,title,LENGTH(contents) clen FROM mdl_lesson_pages WHERE lessonid=%d ORDER BY id;" % LESSON,
            "SELECT id,pageid,jumpto,answer FROM mdl_lesson_answers WHERE lessonid=%d ORDER BY pageid,id;" % LESSON,
            "SELECT question,COUNT(*) n,SUM(fraction) s FROM mdl_question_answers WHERE question BETWEEN 68774 AND 68781 GROUP BY question;",
            "SELECT id,visible,completionexpected FROM mdl_course_modules WHERE id IN (%d,%d,%d,%d);" % (CM_LESSON, CM_QUIZ, CM_RESUME, CM_OTKLIK),
            "SELECT id,name,modulename,instance,eventtype,FROM_UNIXTIME(timestart),visible FROM mdl_event WHERE courseid=236 AND ((modulename='lesson' AND instance=%d) OR (modulename='quiz' AND instance=%d) OR (modulename='forum' AND instance=%d));" % (LESSON, QUIZ, FORUM_OTKLIK)]
    if book:
        sql.append("SELECT id,pagenum,title,LENGTH(content) FROM mdl_book_chapters WHERE bookid=%d ORDER BY pagenum;" % book)
    open('lesson4.sql', 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
    print('sql statements:', len(sql), 'bytes:', os.path.getsize('lesson4.sql'), 'EXP_L', EXP_L, 'EXP_Q', EXP_Q)
