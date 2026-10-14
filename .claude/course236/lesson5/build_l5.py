# -*- coding: utf-8 -*-
"""Lesson 5 «Единственность Творца», course 236 — text of the doc «Урок 5 — СО финал» (14.10.2026,
1PjbB0EX1iJJcbMi0C8CCmVlto-NFPEqc1NLbz2kVS7A) in the lesson 1–4 format: map -> contents (+ three questions)
-> chapters 1–4; quiz «Проверьте себя» (6); résumé; otklik (two questions, as in lessons 2 and 4);
book «Дополнительные материалы к Уроку 5».

Writes html/*.html; with --sql writes lesson5.sql (book 971 = cm_duplicate of the lesson-4 book).
Not taken from the doc (owner's rule 13.10): «Повторение пройденного», «Вступление» as a page (its video and
text go to the book), separate «Задание» (first otklik question) and «Итоговый тест» of the spring course
(8 questions, incl. gapselect/match) — except its question 4, which became the sixth «Проверьте себя» question:
the doc has five. The doc has no seminar post for this lesson.
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
        out += ('<details class="kab-details">\n<summary><span style="color: #3c5d90;">Альтернативный плеер <em>(если видео выше не воспроизводится)</em></span></summary>\n'
                '<br><iframe src="https://drive.google.com/file/d/%s/preview" width="100%%" height="480" allow="autoplay"></iframe></details>\n' % driveid)
    return out


def srcquote(body, who=None):
    """body: plain text (wrapped in <p>) or ready HTML starting with '<'."""
    out = '<div class="kab-quote">\n%s\n' % (body if body.startswith('<') else '<p>%s</p>' % body)
    if who:
        out += '<div style="text-align: right; font-size: 13.5px; line-height: 1.5; color: %s; margin-top: 10px;">— %s</div>\n' % (GRAY, who)
    return out + '</div>\n'


def archive(uid, intro):
    return ('<div class="kab-page">\n<div class="kab-quote">%s</div>\n'
            '<div style="position: relative; width: 100%%; aspect-ratio: 16 / 9; margin: 16px 0;">\n'
            '<iframe src="https://kabbalahmedia.info/lessons/cu/%s?mediaType=video&amp;shareLang=ru&amp;embed=1&amp;autoPlay=0" '
            'style="position: absolute; inset: 0; width: 100%%; height: 100%%; border: 0;" scrolling="no" allowfullscreen=""></iframe>\n</div>\n'
            '<p><a class="kab-link" href="https://kabbalahmedia.info/ru/lessons/cu/%s" target="_blank" rel="noopener">Открыть в архиве</a></p>\n</div>\n'
            % (intro, uid, uid))


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


CHAPTERS = ['Глава 1. Противоречие в управлении', 'Глава 2. Четыре ответа человечества',
            'Глава 3. Творец — абсолютное добро', 'Глава 4. Одна сила']

# ---------------- Содержание (как в уроках 2–4) ----------------
toc = ('<div class="kab-text">\n<div class="kab-quote">\n<ul class="kab-list">\n'
       + ''.join('<li>%s</li>\n' % c for c in CHAPTERS) +
       '''</ul>
</div>
<div class="kab-kicker">Прежде чем читать</div>
<p>Три вопроса для размышления.</p>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Если сила, стоящая за миром, добрая — откуда в мире столько тяжёлого?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Что вы отвечаете себе на этот вопрос сейчас, до всякой каббалы?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Чем вообще можно проверить любой ответ на такой вопрос?</div></div>
</div>
</div>
<p></p>
''')

# ---------------- Глава 1 ----------------
ch1 = ''
ch1 += p('Урок построен по статье Бааль Сулама «Мир». Статья начинается с противоречия, которое видит каждый.')
ch1 += srcquote(
    '<p>«Наблюдая окружающую нас действительность, мы находим в ней две противоположности:</p>\n<ol>\n'
    '<li>Когда мы смотрим на творение с точки зрения его существования и выживаемости, то бросается в глаза постоянное управление, глубина мудрости и степень талантливости которого поражает воображение, как в отношении всей действительности, так и ее частей. […] Так же, как о человеке, природа заботится обо всех видах творения: животных, растениях и неживом уровне. И делает это разумно и милосердно, чтобы обеспечить и само их существование, и продолжение рода.</li>\n'
    '<li>Однако если посмотреть с точки зрения существования и выживания, то бросается в глаза беспорядок и большая путаница. Как будто нет никакого управляющего, никакого надзора, и каждый делает то, что он хочет, и каждый строит собственное благополучие на несчастье другого. […]</li>\n'
    '</ol>\n<p>Это противоречие, заметное любому образованному и чувствующему человеку, занимает человечество еще с древних времен».</p>',
    'Бааль Сулам, «Мир»')
ch1 += figure('L05-01', 15, 800, 418,
              'Две стрелки к одному человеку: заботливая природа — всё связано, всё хрупко и точно подогнано; жёсткий закон — страданий больше, чем удовольствий',
              'Противоречие, с которого начинается статья. С одной стороны — заботливая природа: всё связано, всё хрупко и точно подогнано. С другой — жёсткий закон: страданий больше, чем удовольствий. И то и другое приходит к одному и тому же человеку.')
ch1 += clip('Lesson-05-clip-K1', title='«Вокруг одно горе, а Творец добр?»', source=LAITMAN + ', «Новости», 2023',
            quote='«То, что он видит перед своими глазами, — это делают люди, а не Творец. Творец поставил условия, и если бы мы их выполняли, то мы бы жили совершенно по-другому».')

# ---------------- Глава 2 ----------------
ch2 = ''
ch2 += p('Бааль Сулам перебирает ответы, которые люди давали на это противоречие. Каждый из них логичен, у каждого есть сторонники.')
ch2 += p('<strong>Теория первая: природа.</strong>')
ch2 += srcquote('«…люди пришли к предположению, что Творец жестко управляет существованием всего созданного Им так, что ничего не ускользает от Него, и нет у Него ни разума ни чувств. […] В соответствии с этим, люди назвали управление «Природой», что означает управление без разума и чувства. А потому людям не на кого сердиться, некому молиться, и не перед кем оправдываться».')
ch2 += p('<strong>Теория вторая: две власти.</strong>')
ch2 += srcquote('«Видя, что управление действительностью осуществляется с великой мудростью, превышающей любую человеческую, они не могли согласиться, что у руководящего всем этим отсутствует мудрость. […] А потому появилась вторая теория, согласно которой существует два управляющих: один – Творец, творящий добро, а другой – Творец, творящий зло».')
ch2 += p('<strong>Теория третья: множество богов.</strong>')
ch2 += srcquote('«Общее воздействие Высшего управления было разделено на отдельные составляющие, такие, как сила, богатство, власть, красота, голод, смерть, беспорядки и прочее. Ответственность за каждое из этих действий люди возложили на какого-то определенного божка».')
ch2 += p('<strong>Теория четвёртая: прекратил опеку.</strong>')
ch2 += srcquote('«…создатель и руководитель действительностью мудр, и чувства ведомы ему. Однако с высоты его величия, с которым ничто не сравнится, наш мир видится ему как песчинка, и ничего не стоит в его глазах, и не пристало ему заниматься такими мелкими делами. А потому существование наше столь беспорядочно, и каждый делает, что хочет».',
                'Бааль Сулам, «Мир»')
ch2 += table(['Теория', 'Как объясняет противоречие'], [
    ['Природа', 'управление есть, но без разума и чувств — обращаться не к кому'],
    ['Две власти', 'добро от одной силы, зло от другой'],
    ['Множество богов', 'у каждой области жизни своя сила'],
    ['Прекратил опеку', 'создатель мудр, но нашим миром не занимается']])

# ---------------- Глава 3 ----------------
ch3 = ''
ch3 += p('Творец – это абсолютное добро. Невозможно, чтобы Он причинил кому-либо какое-то зло, и это воспринимается нами как главный закон. Природа Высшей силы, или Творца, есть абсолютное добро, потому что у Него отсутствуют недостатки. Поэтому, если существует кто-то, кроме Него, то есть творение, то Его отношение к нему проявляется как абсолютно доброе.')
ch3 += p('Однако о самом Творце, о Его сути невозможно сказать, что Он добр, потому что свойство доброты указывает на действие, направленное вовне. Доброта возможна лишь по отношению к кому-либо. Определение «абсолютно добр» даёт каббалист, который открывает в себе Высшую силу и таким образом ощущает Его отношение к себе. <strong>Это происходит по закону подобия свойств</strong>, когда каббалист на определённом этапе своего развития начинает относиться к Высшей силе как к абсолютно доброй.')
ch3 += p('<strong>Почему добро Творца абсолютно?</strong> Человек может дать Творцу определение «абсолютный» только тогда, когда испытает на себе отношение Высшей силы и исследует Его всевозможными методами, в различных ситуациях и на протяжении всего времени своих взаимоотношений с Ним. Каббалист проверяет это также относительно остальных людей на протяжении истории во всех состояниях, через которые они прошли. Он обязан удостовериться на сто процентов, что никогда и ни к кому Творец не относился плохо. Только опираясь на подобное исследование, каббалист устанавливает, что Творец абсолютно добр.')
ch3 += clip('Lesson-05-clip-K2', title='«Творец подталкивает»', source=LAITMAN + ', «Новости», 2017',
            quote='«Творец ни в коем случае не творит зло. Раскрывая нам Себя постепенно, Он показывает, насколько мы противоположны Ему. И это воспринимается нами как боль, зло, страдания. […] Раскрывая Себя, Творец проявляет в нас зло нашей противоположной Ему природы, и тем подталкивает нас к её исправлению. И когда наша природа становится подобной Творцу, мы раскрываем абсолютное добро».')
ch3 += clip('Lesson-05-clip-04', title='«Невозможно управление только одной силой»', source=LAITMAN + ', вебинар «Добро и зло в управлении Творца», 2018',
            quote='«Творец управляет нами двумя силами: положительной и отрицательной. Надо признать, что так оно и есть: невозможно управление только одной силой. […] Творец постоянно ведёт нас к добру, но таким путём, чтобы действительно привести к его получению».')

# ---------------- Глава 4 ----------------
ch4 = ''
ch4 += p('Вместе с этим каббалист постигает Творца <strong>как одну, единую и единственную силу в мироздании.</strong>')
ch4 += srcquote('«Сказано: „Нет никого, кроме Него“, что означает, что нет никакой другой силы в мире, у которой была бы способность сделать что-либо против Творца».',
                'Бааль Сулам, «Шамати», статья 1')
ch4 += figure('L05-03', 16, 800, 502,
              'Одна сила сверху и две чаши: мало подобия — ощущается как жестокость; больше подобия — ощущается как забота; разное не в силе, а в мере подобия ей',
              'Сила одна. Как она ощущается, зависит от меры подобия ей: мало подобия — ощущается как жестокость, больше подобия — как забота. Разное не в силе, а в мере подобия ей.')
ch4 += clip('Lesson-05-clip-K3', title='«Один, Единый, Единственный»', source=LAITMAN + ', фрагмент вебинара')
ch4 += h4('Отрывок из статьи Бааль Сулама «Внутреннее созерцание»')
ch4 += p('<strong>Одной мыслью создано всё творение.</strong> Одной только мыслью или одним действием Высшей силы создано всё мироздание в своём единственно существующем законченном виде. Сама эта мысль, и замысел, и причина, и действие, и Действующий, и ожидаемое вознаграждение, и суть всех усилий – в общем, всё, что только сердце может задумать и представить, – всё это в Высшей управляющей силе – одно целое.')
ch4 += p('Человек должен представить, что всё мироздание находится в своём единственном совершенном виде, потому что только таким его создала совершенная управляющая альтруистическая сила. <strong>Человек тоже находится в совершенном состоянии, но ощущает его неисправным в меру своего эгоизма</strong>, отличия от альтруистического свойства Высшей силы. Он должен исправить свой эгоизм и ощутить себя в этом совершенном состоянии.')
ch4 += p('<strong>Как из совершенного Действующего произошли несовершенные действия.</strong> Как всё изобилие и наслаждение, так и все неисправности и недостатки исходят от той же совершенной, альтруистической управляющей всем мирозданием Высшей силы, ведь нет иной силы и воли в мироздании. Но как это возможно, чтобы из одного источника исходило множество противоположных качеств?')
ch4 += p('<strong>Выяснение замысла творения.</strong> Безусловно, что конец действия изначально существует в его замысле, и конец действия – это цель «насладить творения». Для этого и создано творение. Высшая управляющая сила, высший замысел творения завершается сразу и немедленно, а не так, как у человека в нашем мире, который нуждается в инструментах и времени для выполнения действия.')
ch4 += p('<strong>Желание отдавать порождает желание получать.</strong> Желание отдачи Высшей силы рождает желание получать в творении. Желание получать – это и есть сосуд, в который творение получает наслаждение. Мера света и наслаждения соответствует мере желания насладиться.')
NEXT = 'На следующей неделе — <strong>душа и тело.</strong> Что во мне «я», а что оболочка; что развивается, а что остаётся.'
ch4 += h4('Что дальше')
ch4 += p(NEXT)

# ---------------- Резюме ----------------
RESUME = [
    '<b>Творец – это свойство отдачи. Природа Высшей силы или Творца есть абсолютное добро.</b>',
    'Чтобы ощутить Творца, человек должен приобрести Его свойства, т.е. стать подобным Ему по свойствам. <b>В мере подобия человек ощущает Творца.</b>',
    'Понять Творца, не ощутив Его, невозможно.',
    'Цель творения – насладить творения. От замысла «сотворить и насладить сотворённых» сразу исходит и распространяется Высший свет во всех видах, уровнях и величинах наслаждений, которые задумала Высшая сила. В эту мысль включено всё.',
    'Желание получать – это и есть сосуд, в который творение получает наслаждение.',
    '<b>Ощущение Творца – это не что иное, как ощущение нашей внутренней связи между собой.</b>',
    'Нельзя персонифицировать и создавать образы Творца.',
    '<b>Не существует ничего, кроме Творца.</b> Творец включает в Себя всю действительность.',
    'Противоречие в управлении человечество объясняло четырьмя теориями: природа · две власти · множество богов · прекратил опеку.',
]
resume = ('<div class="kab-page">\n<div class="kab-card">\n'
          + ''.join('<div class="kab-row"><div class="kab-dot"></div><div>%s</div></div>\n' % r for r in RESUME)
          + '</div>\n<div class="kab-quote kab-quote--purple">%s</div>\n</div>\n' % NEXT.replace('<strong>', '<b>').replace('</strong>', '</b>'))

# ---------------- Отклик недели (как в уроках 2 и 4: два вопроса) ----------------
otklik = '''<div class="kab-text">
<div class="kab-quote">Напишите своими словами ответ на любой из вопросов.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Согласны ли вы с тем, что Творец добрый и несущий добро, — и почему?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Из четырёх теорий выберите ту, которая до этого урока была ближе всего лично вам, и напишите одной фразой — почему.</div></div>
</div>
<div class="kab-note">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
</div>
'''

# ---------------- Книга «Дополнительные материалы к Уроку 5» ----------------
book_ch1 = '<div class="kab-page">\n<div class="kab-quote">Лекции по теме урока — в записи. Тот же материал в лекционном изложении; ядро урока на него не опирается.</div>\n'
book_ch1 += '<div class="kab-kicker">Вступление</div>\n'
book_ch1 += yt('Основы каббалы. Единственность Творца', '4oGy31qwWqw', '1BoJfhe5YdY99hFIR3aOadfWyAwdkHfc8')
book_ch1 += p('Человек постоянно находится под воздействием различных сил, которые практически не поддаются прогнозированию и управлению. Следуя общепринятым нормам поведения, человек хотел бы получать предсказуемый результат своих действий. Однако реакция окружающей среды, включая природу и общество, как правило, противоположна ожиданиям человека.')
book_ch1 += p('Так происходит потому, что алгоритм, по которому окружающая среда реагирует на воздействие человека, нам не известен.')
book_ch1 += p('Вся совокупность воздействия окружающей среды на человека называется в каббале «природа». В числовом выражении слово «природа» («тэва») соответствует слову «Творец». <strong>Всё, что воздействует на человека, является частным проявлением Творца как единой управляющей силы.</strong> Творец полностью управляет человеком, оставляя в его распоряжении часть, которая называется «свобода воли».')
book_ch1 += p('Реализуя свою свободу воли по каббалистической методике, человек постигает алгоритм управления природы и начинает воспринимать Творца как силу, направляющую его к правильному развитию его желаний.')
book_ch1 += '<div class="kab-kicker">Единственность Творца. Преподаватель Дмитрий Перкин</div>\n'
book_ch1 += '<p><strong>Часть 1</strong></p>\n' + yt('Единственность Творца. Часть 1 (преподаватель Дмитрий Перкин)', 'BLRyASE4gUQ', '1NCxNCZJr6AH8ag1Kn0vEWR_8TdIWeC92')
book_ch1 += '<p><strong>Часть 2</strong></p>\n' + yt('Единственность Творца. Часть 2 (преподаватель Дмитрий Перкин)', 'YSkPgOTH4Po', '1mcyef-fKUKv8L0Ff6JsgDXT7vSHrboRB')
book_ch1 += '<div class="kab-kicker">Ответы на вопросы по теме</div>\n'
book_ch1 += yt('Ответы на вопросы по теме «Единственность Творца»', 'R3luDvQCtsY', '1yqxMPCJI2Fkf9CT8wTXq8lVo5cBZ1JeQ')
book_ch1 += '<div class="kab-kicker">Клип «Источник всего сущего», 1 минута</div>\n'
book_ch1 += yt('Источник всего сущего', 'w7afJnV6GwE')
book_ch1 += '</div>\n'

book_ch2 = archive('YA89b1a9', 'Вебинар Михаэля Лайтмана 14 октября 2018 года, 62 минуты. Полный разбор темы урока: Творец управляет двумя силами, положительной и отрицательной, и постоянно ведёт к добру. Клип «Невозможно управление только одной силой» в главе 3 лекции — начало этого вебинара.')
book_ch3 = archive('JZPDGZ9D', 'Урок Михаэля Лайтмана 21 ноября 2010 года, 59 минут, по статье Бааль Сулама «Мир» — той, на которой построен урок: противоречие в управлении и теории, которыми его объясняло человечество.')
book_ch4 = '''<div class="kab-page">
<div class="kab-quote">Факультатив, к неделе не привязан. Статьи, на которых построен урок, — целиком.</div>
<p><a class="kab-link" href="https://kabbalahmedia.info/ru/sources/qEcXTJ41" target="_blank" rel="noopener">Бааль Сулам, «Мир»</a> — статья целиком, в редакции «Сборника трудов Бааль Сулама».</p>
<p><a class="kab-link" href="https://kabbalahmedia.info/ru/sources/hFeGidcS" target="_blank" rel="noopener">Бааль Сулам, «Нет никого, кроме Него»</a> — первая статья сборника «Шамати».</p>
</div>
'''
BOOK = [('Лекции урока в записи', book_ch1),
        ('Вебинар «Добро и зло в управлении Творца» (2018)', book_ch2),
        ('Урок «Бааль Сулам. Мир. Контрасты и противоречия управления» (2010)', book_ch3),
        ('Статьи Бааль Сулама «Мир» и «Нет никого, кроме Него»', book_ch4)]

PAGES = {'p_toc': toc, 'p_ch1': ch1, 'p_ch2': ch2, 'p_ch3': ch3, 'p_ch4': ch4,
         'page_resume': resume, 'forum_otklik': otklik, 'book_ch1': book_ch1, 'book_ch2': book_ch2,
         'book_ch3': book_ch3, 'book_ch4': book_ch4}

# ---------------- Тест «Проверьте себя» (quiz 1239): вопросы и верные ответы из дока, разборы неверных — свои ----------------
# (name, text, [(answer, fraction, feedback)], single) — answers are shuffled by the quiz
THIRD = 0.3333333
QUESTIONS = [
    ('У5-01 Какой Он — Творец', 'Какой Он — Творец? <em>Несколько правильных ответов.</em>', [
        ('Добрый, несущий добро', THIRD, 'Верно. Его отношение к творению проявляется как абсолютно доброе.'),
        ('Неизменный', THIRD, 'Верно. Никогда и ни к кому Творец не относился плохо — Его отношение не меняется ни в каких ситуациях и ни в какое время.'),
        ('Один, единый, единственный', THIRD, 'Верно. «Нет никого, кроме Него»: нет никакой другой силы в мире, у которой была бы способность сделать что-либо против Творца.'),
        ('Помогающий только каббалистам', -1.0, 'Нет. Его отношение к творению проявляется как абсолютно доброе — ко всем, а не только к каббалистам.')], False),
    ('У5-02 Почему каббалисты говорят, что Творец абсолютно добр', 'На каком основании каббалисты утверждают, что Творец абсолютно добр?', [
        ('Исследовав и испытав на себе отношение Высшей силы', 1.0, 'Верно. Каббалист проверяет это во всех ситуациях и относительно всех людей на протяжении истории — и только тогда даёт определение «абсолютно добр».'),
        ('Исходя из религиозных убеждений', 0.0, 'Убеждение ничего не проверяет. Определение «абсолютно добр» каббалист даёт, только испытав отношение Высшей силы на себе — во всех ситуациях и на протяжении всего времени.'),
        ('На основании общепринятого мнения', 0.0, 'Общепринятое мнение здесь ни при чём. Каббалист исследует отношение Высшей силы сам — на себе и относительно всех людей на протяжении истории.'),
        ('Из анализа священных текстов', 0.0, 'Определение даёт не анализ текстов, а исследование: каббалист испытывает отношение Высшей силы на себе во всех ситуациях.')], True),
    ('У5-03 В чём замысел творения', 'В чём заключается замысел творения?', [
        ('Дать бесконечное наполнение творению', 1.0, 'Верно. Конец действия — цель «насладить творения» — изначально существует в замысле.'),
        ('В эволюции видов', 0.0, 'Эволюция — путь, а не цель. Замысел — «насладить творения»: дать творению бесконечное наполнение.'),
        ('В продолжении рода', 0.0, 'О продолжении рода природа заботится, но это не цель. Замысел — «насладить творения».'),
        ('В создании творения', 0.0, 'Творение создано ради цели: «насладить творения». «Для этого и создано творение».')], True),
    ('У5-04 Причина зла в нашем мире', 'Что является причиной зла в нашем мире?', [
        ('Желание получать для себя', 1.0, 'Верно. Человек ощущает мир неисправным в меру своего эгоизма — отличия от свойства отдачи.'),
        ('Отрицательное воздействие Высшей силы', 0.0, 'Творец ни в коем случае не творит зло. Раскрывая Себя, Он показывает, насколько мы противоположны Ему, — и это воспринимается как зло.'),
        ('Желание получать', 0.0, 'Само желание получать — не зло: это сосуд, в который творение получает наслаждение. Зло — в получении для себя, в отличии от свойства отдачи.'),
        ('Вредные привычки человека', 0.0, 'Привычки — следствие. Причина — желание получать для себя: человек ощущает мир неисправным в меру своего эгоизма.')], True),
    ('У5-05 Теория «прекратил опеку»', 'Какая теория утверждает, что создатель мудр, но нашим миром не занимается?', [
        ('Прекратил опеку', 1.0, 'Верно. С высоты его величия наш мир видится ему как песчинка.'),
        ('Природа', 0.0, 'Теория природы — управление без разума и чувств, обращаться не к кому. Мудрый создатель, который не занимается нашим миром, — «прекратил опеку».'),
        ('Две власти', 0.0, 'Две власти — добро от одной силы, зло от другой. Мудрый создатель, который не занимается нашим миром, — «прекратил опеку».'),
        ('Множество богов', 0.0, 'Множество богов — у каждой области жизни своя сила. Мудрый создатель, который не занимается нашим миром, — «прекратил опеку».')], True),
    ('У5-06 Свойство замысла творения', 'Что является важнейшим свойством замысла творения?', [
        ('Конец действия изначально существует в его замысле', 1.0, 'Верно. Высший замысел творения завершается сразу и немедленно, а не так, как у человека в нашем мире, которому нужны инструменты и время.'),
        ('Конец действия не детерминирован и зависит от творения', 0.0, 'Нет. Конец действия — цель «насладить творения» — изначально существует в замысле.'),
        ('Конец действия не определён и зависит от Творца', 0.0, 'Конец действия определён: это цель «насладить творения», и она изначально существует в замысле.'),
        ('Конец действия определён, но замысел переменен', 0.0, 'Замысел не меняется: высший замысел творения завершается сразу и немедленно, и конец действия изначально существует в нём.')], True),
]

for k, v in PAGES.items():
    assert not re.search(r'\bРав\b', v), k
    assert 'М. Лайтман' not in v, k
    open('html/%s.html' % k, 'w', encoding='utf-8', newline='\n').write(v)
print('pages:', ', '.join('%s=%d' % (k, len(v)) for k, v in PAGES.items()))

# ---------------- SQL ----------------
TZ = ZoneInfo('Asia/Jerusalem')
LESSON, CM_LESSON, CM_QUIZ, CM_RESUME, CM_OTKLIK = 1966, 13525, 13526, 13527, 13528
QUIZ, PAGE_RESUME, FORUM_OTKLIK, BOOK_ID = 1239, 2493, 3908, 971
# 17910 map -> 17911 toc -> 17912 ch1 (was «О чём этот урок») -> 17915 ch2 -> 17913 ch3 -> 17914 ch4 («Завершить»)
PAGE_IDS = {'map': 17910, 'toc': 17911, 'ch1': 17912, 'ch2': 17915, 'ch3': 17913, 'ch4': 17914}
# Lesson 4 + 7 days, Israel time (winter time since 25.10)
EXP_L = int(datetime(2026, 11, 3, 23, 59, tzinfo=TZ).timestamp())   # lecture and otklik: Tuesday 23:59
EXP_Q = int(datetime(2026, 11, 4, 23, 59, tzinfo=TZ).timestamp())   # test: Wednesday 23:59
# quiz slots -> question versions -> answer ids (from the DB, 14.10.2026)
QIDS = [([68782, 68783], [[284128, 284129, 284130, 284131], [284132, 284133, 284134, 284135]]),
        ([68784], [[284136, 284137, 284138, 284139]]),
        ([68785, 68786], [[284140, 284141, 284142, 284143], [284144, 284145, 284146, 284147]]),
        ([68787], [[284148, 284149, 284150, 284151]]),
        ([68788], [[284152, 284153, 284154, 284155]]),
        ([68789], [[284156, 284157, 284158, 284159]])]

if '--sql' in sys.argv:
    NOW = int(time.time())

    def hx(s):
        return '0x' + binascii.hexlify(s.encode('utf-8')).decode()

    def q(s):
        return "'" + s.replace(chr(92), chr(92) * 2).replace("'", "''") + "'"

    mappage = ('<p><a title="Начать" href="https://edu.kabacademy.com/mod/lesson/view.php?id=%d&amp;pageid=%d"><img class="img-fluid" src="%smaps/L05.png" alt="Карта Урок 5" width="1174" height="915"></a></p>\n'
               % (CM_LESSON, PAGE_IDS['toc'], IMG))
    open('html/p_map.html', 'w', encoding='utf-8', newline='\n').write(mappage)
    sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
    pages = [('map', '*', mappage), ('toc', 'Содержание', toc)] + [
        ('ch%d' % i, CHAPTERS[i - 1], html) for i, html in enumerate((ch1, ch2, ch3, ch4), 1)]
    for key, title, html in pages:
        sql.append("UPDATE mdl_lesson_pages SET title=%s, contents=%s, timemodified=%d WHERE id=%d AND lessonid=%d;" % (q(title), hx(html), NOW, PAGE_IDS[key], LESSON))
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
    # open the lecture, test and résumé (the lesson section itself stays date-locked until 30.10 08:00)
    sql.append("UPDATE mdl_course_modules SET visible=1, visibleold=1 WHERE id IN (%d,%d,%d);" % (CM_LESSON, CM_QUIZ, CM_RESUME))
    # dates as in lessons 3–4: lecture/otklik Tuesday, test Wednesday
    sql.append("UPDATE mdl_course_modules SET completionexpected=%d WHERE id IN (%d,%d);" % (EXP_L, CM_LESSON, CM_OTKLIK))
    sql.append("UPDATE mdl_course_modules SET completionexpected=%d WHERE id=%d;" % (EXP_Q, CM_QUIZ))
    sql.append("UPDATE mdl_event SET timestart=%d, timesort=%d, visible=1, timemodified=%d WHERE courseid=236 AND modulename='lesson' AND instance=%d AND eventtype='expectcompletionon';" % (EXP_L, EXP_L, NOW, LESSON))
    sql.append("UPDATE mdl_event SET timestart=%d, timesort=%d, visible=1, timemodified=%d WHERE courseid=236 AND modulename='forum' AND instance=%d AND eventtype='expectcompletionon';" % (EXP_L, EXP_L, NOW, FORUM_OTKLIK))
    sql.append("UPDATE mdl_event SET timestart=%d, timesort=%d, visible=1, timemodified=%d WHERE courseid=236 AND modulename='quiz' AND instance=%d AND eventtype='expectcompletionon';" % (EXP_Q, EXP_Q, NOW, QUIZ))
    sql.append("UPDATE mdl_event SET visible=1, timemodified=%d WHERE courseid=236 AND modulename='lesson' AND instance=%d AND eventtype='open';" % (NOW, LESSON))
    # book 971 (cm 13687, a copy of the lesson-4 book): name and chapters
    sql.append("UPDATE mdl_book SET name='Дополнительные материалы к Уроку 5', timemodified=%d WHERE id=%d;" % (NOW, BOOK_ID))
    sql.append("DELETE FROM mdl_book_chapters WHERE bookid=%d;" % BOOK_ID)
    for i, (title, html) in enumerate(BOOK, 1):
        sql.append("INSERT INTO mdl_book_chapters (bookid,pagenum,subchapter,title,content,contentformat,hidden,timecreated,timemodified,importsrc) "
                   "VALUES (%d,%d,0,%s,%s,1,0,%d,%d,'');" % (BOOK_ID, i, q(title), hx(html), NOW, NOW))
    sql.append("UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;")
    sql += ['COMMIT;',
            "SELECT id,prevpageid,nextpageid,title,LENGTH(contents) clen FROM mdl_lesson_pages WHERE lessonid=%d ORDER BY id;" % LESSON,
            "SELECT question,COUNT(*) n,SUM(fraction) s,SUM(GREATEST(fraction,0)) pos FROM mdl_question_answers WHERE question BETWEEN 68782 AND 68789 GROUP BY question;",
            "SELECT questionid,single FROM mdl_qtype_multichoice_options WHERE questionid BETWEEN 68782 AND 68789;",
            "SELECT id,visible,completionexpected FROM mdl_course_modules WHERE id IN (%d,%d,%d,%d,13687);" % (CM_LESSON, CM_QUIZ, CM_RESUME, CM_OTKLIK),
            "SELECT id,name,modulename,instance,eventtype,FROM_UNIXTIME(timestart),visible FROM mdl_event WHERE courseid=236 AND ((modulename='lesson' AND instance=%d) OR (modulename='quiz' AND instance=%d) OR (modulename='forum' AND instance=%d));" % (LESSON, QUIZ, FORUM_OTKLIK),
            "SELECT id,pagenum,title,LENGTH(content) FROM mdl_book_chapters WHERE bookid=%d ORDER BY pagenum;" % BOOK_ID]
    open('lesson5.sql', 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
    print('sql statements:', len(sql), 'bytes:', os.path.getsize('lesson5.sql'), 'EXP_L', EXP_L, 'EXP_Q', EXP_Q)
