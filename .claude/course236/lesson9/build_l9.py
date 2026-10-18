# -*- coding: utf-8 -*-
"""Lesson 9 «Намерение», course 236 — text of the doc «Урок 9 — СО финал» (18.10.2026, 1KLPC4BJtAeXe9hkU7USd3jBpOrRM-xVjV8_lbYDRRQM)
in the lesson 1–2 / 6–8 format: map -> contents (+ three questions) -> «О чём этот урок» (the doc's «Вступление» text)
-> chapters 1–6 (+ «Что с этим делать» at the end of chapter 6); quiz «Проверьте себя» — all eight questions of the doc
(slots 7–8 added to quiz 1243); résumé; otklik (two questions); book «Дополнительные материалы к Уроку 9».
In-text videos are fragments cut from archive programs (cut9.sh; boundaries from faster-whisper word timestamps + silencedetect).
Everything is due on Wednesday 23:59.

Writes html/*.html; with --sql writes lesson9.sql (book 975 = cm_duplicate of the lesson-8 book).
Not taken from the doc: «Повторение пройденного», separate «Задание» (first otklik question), «Итоговый тест» of the spring
course, links to kabbalahmedia.info (Факультатив lessons — embedded players instead), editorial notes in the book.
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
CHAPTERS = ['Глава 1. Желание и намерение', 'Глава 2. Одно действие — два поступка', 'Глава 3. Гость и хозяин',
            'Глава 4. Желание остаётся', 'Глава 5. Откуда берётся намерение', 'Глава 6. Где намерение проверяется']
ML = LAITMAN

# ---------------- Содержание ----------------
toc = ('<div class="kab-text">\n<div class="kab-quote">\n<ul class="kab-list">\n'
       + ''.join('<li>%s</li>\n' % c for c in [INTRO] + CHAPTERS) +
       '''</ul>
</div>
<div class="kab-kicker">Прежде чем читать</div>
<p>Три вопроса для размышления.</p>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Вспомните поступок, за который вас благодарили. Ради чего вы его сделали на самом деле?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Могут ли два человека сделать совершенно одинаковое действие, и это будут разные поступки?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Если желание дано человеку природой, то что в нём вообще можно изменить?</div></div>
</div>
</div>
<p></p>
''')

# ---------------- О чём этот урок («Вступление» дока; его видео — в книге) ----------------
intro = h4(INTRO)
intro += p('Желание человеку дано. Он не выбирал, чего ему хотеть, и не выбирает. Но у каждого желания есть ещё одна сторона: <strong>ради чего</strong> оно наполняется. Это и называется намерением.')
intro += p('На прошлой неделе мы закончили на том, что важно не только действие, но и то, ради чего оно сделано. Этот урок — о том, что такое это «ради чего», почему именно оно определяет результат и откуда оно берётся.')

# ---------------- Глава 1 ----------------
ch1 = ''
ch1 += h4('Ради чего')
ch1 += srcquote('«Главное – это намерение: ради чего, во имя чего, для кого я собираюсь что-то сделать или даже просто мыслить. Самое главное, куда я направлен своими мыслями, чувствами, устремлениями».', ML)
ch1 += p('Человек хочет чего-то, но сразу взять не может. Он выстраивает путь: идёт, договаривается, работает. Каждый шаг — отдельное действие, но внутри каждого шага сидит то, ради чего всё делается.')
ch1 += srcquote('<strong>«Намерение – это конечная цель, которая присутствует во всех моих промежуточных действиях».</strong>', ML + ', «Основы каббалы»')
ch1 += h4('Желание дано, намерение — выбор')
ch1 += p('<strong>Желание бывает только одного вида — насладиться.</strong> Это свойство природы, и человек его не выбирает. Это мы видели в уроке 7: основу и свойства человек получает готовыми.')
ch1 += p('<strong>У намерения возможностей две: ради себя или ради другого.</strong> Других нет.')
ch1 += srcquote('«Самое главное не само желание. Желание насладиться – это исконное желание самого творения, оно остаётся. А вот для чего насладиться – ради себя или ради Творца (есть всего лишь две эти возможности) – тут мы можем менять свои намерения».', ML)
ch1 += p('Отсюда простая конструкция. <strong>У высшей силы есть только желание отдавать. У человека есть и желание, и намерение.</strong> Желание — получать. Намерение — ради себя или ради другого. Меняя намерение, человек приближается к источнику или отдаляется от него — при том что желание остаётся прежним.')

# ---------------- Глава 2 ----------------
ch2 = ''
ch2 += h4('Проверка, которая не требует никакой теории')
ch2 += p('Два человека приносят третьему еду.')
ch2 += p('Первый — потому что тот голоден. Второй — потому что рассчитывает, что об этом узнают.')
ch2 += p('<strong>Действие совпадает полностью.</strong> Со стороны различить нельзя, и получивший еду в обоих случаях сыт.')
ch2 += p('И при этом это разные поступки. Все это чувствуют, и никто не может показать пальцем, в чём разница.')
ch2 += p('<strong>Желание у обоих одно: чтобы было хорошо.</strong> Отличается то, кому именно должно стать хорошо. Вот это «ради чего» и называется намерением.')
ch2 += h4('Двадцать часов в сутки')
ch2 += clip('Lesson-09-clip-K1', title='«Действовать ради других»', source=ML + ', «Духовные состояния. Жертвоприношение», 2023',
            quote='«Действие ради других – это намерение, а не само действие. Я могу работать 20 часов в сутки, но это ничего не значит. Для чего я это делаю? Если ради пользы других, и это является моей мотивацией, тогда я делаю добро. А если я работаю по 20 часов, чтобы заработать миллион, то я никому не приношу добра».')
ch2 += p('Рабочий у станка, учительница в школе — снаружи это польза другим. Суть решает «для чего». Прибора, который это измерит, нет. Иногда намерение скрыто и от самого человека.')
ch2 += h4('Сплетник')
ch2 += p('Можно сказать правду о том, что между людьми разрыв, и этим их развести. А можно сказать ту же самую правду и при этом думать, как их соединить.')
ch2 += clip('Lesson-09-clip-K2', title='«Сплетник»', source=ML + ', «Тайны вечной книги», глава «Кдошим», часть 5, 2014',
            quote='«Сплетник – это тот, кто разводит, отдаляет людей друг от друга. Можно сделать то же самое, что и сплетник, раскрыв какие-то разрывы связей между людьми, но при этом ты будешь думать о том, как соединить их. […] Сплетник – это тот, который не врёт, но работает не в сторону исправления».')
ch2 += h4('Намерение меняет суть действия')
ch2 += p('Из этого следует то, на чём стоит весь урок: <strong>намерение меняет суть самого действия.</strong> Не оценку действия, не отношение к нему — саму суть. Одно и то же действие оказывается двумя разными вещами.')
ch2 += p('Здесь легко услышать привычное «важны не поступки, а мотивы». Разница вот в чём. В привычном разговоре мотив — это то, за что человека хвалят или осуждают. Здесь намерение — это то, что <strong>определяет результат</strong>: что именно человек в итоге получит и почувствует. Оценки в этом нет. Есть механика.')

# ---------------- Глава 3 ----------------
ch3 = ''
ch3 += clip('Lesson-09-clip-K3', title='«Как гость и хозяин»', source=ML + ', «Введение в науку каббала», урок 3, пп. 14–16, 2023')
ch3 += p('Пример Бааль Сулама из статьи «Введение в науку каббала». Он идёт по шагам, и шаги важнее вывода.')
ch3 += h4('Шаг первый: угощение')
ch3 += p('Хозяин приготовил стол и зовёт гостя. Гость голоден, еда отличная. Гость ест.')
ch3 += h4('Шаг второй: неловкость')
ch3 += p('Но гость чувствует то, чего в самом угощении не было. Он <strong>берёт</strong>. Хозяин даёт, он получает, и это неравенство никуда не девается.')
ch3 += p('Чем лучше угощение, тем сильнее неловкость. Это то, о чём шла речь в уроке 4: вместе с наслаждением приходит ощущение того, кто его дал.')
ch3 += h4('Шаг третий: отказ')
ch3 += p('Гость отказывается. Это честно. Но на этом всё кончается: хозяин остался с накрытым столом, гость голодный, и никакой связи между ними не возникло. Как во второй стадии урока 4 — отказ честен, но никуда не ведёт.')
ch3 += h4('Шаг четвёртый: ради чего')
ch3 += p('Хозяин настаивает. И гость понимает: хозяину важно не накормить его, а доставить радость.')
ch3 += p('Тогда гость садится за стол снова. Он ест то же самое, с тем же аппетитом — <strong>но теперь его еда является подарком хозяину.</strong>')
ch3 += figure('L09-01', 23, 800, 282,
              'Четыре шага примера «Гость и хозяин», справа налево: угощение — гость ест; неловкость — он берёт, неравенство; отказ — не взять; ради чего — еда становится подарком хозяину',
              'Четыре шага примера (читаются справа налево): 1 — угощение, гость ест; 2 — неловкость: он берёт, неравенство; 3 — отказ: не взять; 4 — ради чего: еда становится подарком хозяину.')
ch3 += h4('Что изменилось')
ch3 += p('Ничего из видимого. Тот же гость, та же еда, тот же голод.')
ch3 += p('И голод обязателен: Бааль Сулам подчёркивает, что и теперь гость не станет есть без аппетита. Не бывает подарка от того, кому не хочется.')
ch3 += p('<strong>Изменилось только намерение.</strong> И этого оказалось достаточно, чтобы получающий стал отдающим. Вместо творения, которое получает, возникает творение, которое отдаёт.')
ch3 += h4('Ребёнок, который ест')
ch3 += p('То же самое в одну строку.')
ch3 += srcquote('«…как ребёнок ест, чтобы доставить удовольствие матери, и хотя физически в действии он получает, но на самом деле отдаёт, т.к. действует ради её наслаждения».', ML)
ch3 += p('Никакой жертвы, никакого отказа. То же самое действие.')

# ---------------- Глава 4 ----------------
ch4 = ''
ch4 += h4('Желание нейтрально')
ch4 += clip('Lesson-09-clip-K4', title='«Желания, данные нам от природы»', source=ML + ', «Эпоха посткоронавируса. Особенности человеческого эгоизма», 2020',
            quote='«Наши желания нейтральны, и именно намерение делает их эгоистичными? — Да. Намерение определяет вектор и величину желания. Допустим, я хочу съесть какой-то плод. А насколько я это желаю, что я должен для этого сделать, на что конкретно пойти – это уже определяется намерением».')
ch4 += p('Это мы видели в уроке 2. Слон хочет есть много, мышь мало, и слон от этого не больший эгоист. <strong>Величина желания не говорит ни о чём.</strong>')
ch4 += h4('С желаниями не воюют')
ch4 += p('Желание исправлять не нужно. Его не подавляют, не уменьшают и не ломают. Исправляется только намерение.')
ch4 += srcquote('«С желаниями вообще не воюют, воюют только с намерениями. Если они обращаются с намерений „ради себя“ на намерения „ради других“, то война на этом заканчивается. […] С психологической точки зрения надо всегда начинать с этого и не ломать себя».', ML)
ch4 += p('Рабаш говорит то же самое одной строкой: <strong>«Желания не имеют значения, имеют значение только намерения».</strong>')
ch4 += h4('Наседка')
ch4 += p('Желание при этом остаётся тем же самым — меняется то, что из него следует.')
ch4 += srcquote('«В принципе, желание остаётся, меняется только намерение. […] Я ощущаю его как свой, как наседка над всеми. Я считаю его своим: „Это моё! Я обязан о нём заботиться!“. А не: „Это моё! Я обязан из него всё высосать!“. Но желание – оно одно и то же».', ML)

# ---------------- Глава 5 ----------------
ch5 = ''
ch5 += h4('Оно не переключается решением')
ch5 += p('Попытка «теперь я буду делать всё ради других» даёт либо самообман, либо усталость, и обычно то и другое.')
ch5 += clip('Lesson-09-clip-K5', title='«Не ради себя»', source=ML + ', «Духовные состояния», 2018',
            quote='«Когда человек начинает пытаться что-то сделать ради других, он тут же ловит себя на том, что думает ради себя: „А где же я? А что со мной? Что я от этого имею?“ […] То есть он понимает, что здесь перед ним находится какой-то очень серьёзный блок, препятствие».')
ch5 += h4('Его дают в ответ на просьбу')
ch5 += p('Правильное намерение приходит свыше. Своими силами человек доходит до того, что видит, ради чего действует, — и просит.')
ch5 += srcquote('«В обычном земном человеке правильного намерения нет и быть не может. Поэтому наша задача – просить Творца дать нам его, даже не зная, что мы просим».', ML)
ch5 += p('Воздействие высшего света на желание придаёт ему намерение. Человек рождается в намерении «ради себя», и только благодаря помощи свыше оно обращается в намерение «ради другого».')
ch5 += h4('Начинают с того, что есть')
ch5 += srcquote('«Поэтому начинай таким, какой ты есть. Даже если у тебя есть намерение только ради себя и ничего другого […] – занимайся и преуспеешь».', ML)
ch5 += p('У этого пути есть название: <strong>«из ло лишма приходят к лишма»</strong> — от «ради себя» к «ради другого». Сначала человек делает то, что делает, и для себя. Под воздействием высшего света он постепенно приобретает способность отдавать без выгоды для себя.')

# ---------------- Глава 6 (+ «Что с этим делать» и «Что дальше») ----------------
ch6 = ''
ch6 += p('<strong>Намерение проверяется на других.</strong> Внутри себя проверить его нечем.')
ch6 += srcquote('«Ничего не меняется, меняется только намерение. Представь себе систему, которая или отключается, или включается в зависимости от намерения: „к себе“ – она автоматически отключается; „к другим“ – автоматически включается».', ML)
ch6 += p('Это продолжение урока 8. Среда не только назначает важное. В ней намерение растёт и в ней же становится видно.')
ch6 += srcquote('«У тебя вдруг появляется желание, намерение, направление отдавать им. Ты начинаешь ощущать, что в тебе возникает побуждение наполнить их. Откуда оно появляется? Именно так, как ты видишь в ребёнке: вчера он не умел что-то делать, а сегодня может».', ML)
ch6 += h4('Что с этим делать')
ch6 += p('<strong>Первое. Замечать, а не переключать.</strong> На этой неделе доступно одно: <strong>заметить своё намерение в одном конкретном случае.</strong> Не менять его. Увидеть.')
ch6 += p('<strong>Второе. Смотреть на обычное, а не на особенное.</strong> Не на поступки, которыми можно гордиться, а на самое рядовое: как вы кому-то ответили, что написали, зачем открыли телефон.')
ch6 += p('<strong>Третье. Начинать с того, что есть.</strong> Если окажется, что почти всё делается ради себя, — это нормальное начало. Ровно с этого и начинают.')
NEXT = 'На следующей неделе — <strong>откуда мы это знаем.</strong> Кто и когда всё это записал: как наука каббала появилась, почему была закрыта и почему открылась сейчас.'
ch6 += h4('Что дальше')
ch6 += p(NEXT)

# ---------------- Резюме ----------------
RESUME = [
    '<b>Намерение — это то, ради чего сделано действие.</b> Желание отвечает на вопрос «чего хочу», намерение — «ради кого».',
    '<b>Желание дано природой и бывает одного вида — насладиться. У намерения две возможности:</b> ради себя или ради другого.',
    '<b>Намерение меняет суть самого действия.</b> Одинаковые действия оказываются разными поступками.',
    'Это не мораль: намерение не то, за что хвалят, а то, что <b>определяет результат</b>.',
    '<b>Гость и хозяин:</b> гость садится за стол второй раз, и его еда становится подарком. Изменилось только намерение — и получающий стал отдающим. <b>Голод обязателен.</b>',
    '<b>Желание остаётся.</b> С желаниями не воюют, их не подавляют; исправляется только намерение.',
    '<b>Правильное намерение приходит свыше, в ответ на просьбу.</b> Начинают с того, что есть: из «ради себя» приходят к «ради другого».',
    '<b>Намерение проверяется на других.</b> Поэтому оно растёт в среде, о которой шла речь в уроке 8.',
]
resume = ('<div class="kab-page">\n<div class="kab-card">\n'
          + ''.join('<div class="kab-row"><div class="kab-dot"></div><div>%s</div></div>\n' % r for r in RESUME)
          + '</div>\n<div class="kab-quote kab-quote--purple">%s</div>\n</div>\n' % NEXT.replace('<strong>', '<b>').replace('</strong>', '</b>'))

# ---------------- Отклик недели (два вопроса: «Задание» + «Если захочется») ----------------
otklik = '''<div class="kab-text">
<div class="kab-quote">Напишите своими словами ответ на любой из вопросов.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Чем намерение отличается от желания? Приведите пример из жизни.</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>За неделю поймайте один случай, где вы заметили своё намерение. Не тот, которым можно гордиться, — самый обычный. Напишите о нём одной фразой.</div></div>
</div>
<div class="kab-note">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
</div>
'''

# ---------------- Книга «Дополнительные материалы к Уроку 9» (плееры архива, подписи — из дока, без ссылок) ----------------
book_ch1 = '<div class="kab-page">\n' + embed('programs', 'IzMjgpON', '«Основы каббалы. Эволюция желаний»',
                                             ML + ', 2018, 31 мин. О намерении — с 15-й минуты.', last=True) + '</div>\n'
book_ch2 = ('<div class="kab-page">\n'
            + embed('programs', '7Zu2jkQI', '«Учение Десяти Сфирот. Урок 3»', ML + ', 2022. Фрагмент с 24-й минуты: «Разум, желание и намерение».')
            + embed('lessons', '4xy2geXC', '«Как Творец раскрывается в отношениях между нами?»', 'Вебинар Михаэля Лайтмана, 2017. Фрагмент с 14-й минуты: «Главное — намерение».')
            + embed('lessons', 'VsAlgKia', '«Бааль Сулам. Учение каббалы и его суть. Часть 2»', 'Вебинар Михаэля Лайтмана, 2018. Фрагмент с 25-й минуты: «Заповеди и действия».')
            + embed('lessons', 'e5Tcolyd', '«Схема мироздания. 10 сфирот души»', 'Вебинар Михаэля Лайтмана, 2017. Фрагмент с 15-й минуты: «Гость и хозяин — Малхут и Кетэр».', last=True)
            + '</div>\n')
book_ch3 = ('<div class="kab-page">\n'
            + embed('lessons', 'KfVzr2UL', 'Бааль Сулам, «Введение в науку каббала», пп. 14–15', 'Пример «Гость и хозяин».')
            + embed('lessons', '2zXHGon3', 'Бааль Сулам, «Введение в науку каббала», пп. 18–21', 'Продолжение разбора: голод, отказ и получение ради хозяина.', last=True)
            + '</div>\n')
BOOK = [('Вступление: «Основы каббалы. Эволюция желаний»', book_ch1),
        ('Беседы о намерении', book_ch2),
        ('«Введение в науку каббала»: гость и хозяин', book_ch3)]

PAGES = {'p_toc': toc, 'p_intro': intro, 'p_ch1': ch1, 'p_ch2': ch2, 'p_ch3': ch3, 'p_ch4': ch4, 'p_ch5': ch5, 'p_ch6': ch6,
         'page_resume': resume, 'forum_otklik': otklik, 'book_ch1': book_ch1, 'book_ch2': book_ch2, 'book_ch3': book_ch3}

# ---------------- Тест «Проверьте себя» (quiz 1243, 8 слотов): вопросы и верные ответы из дока, разборы неверных — свои ----------------
QUESTIONS = [
    ('У9-01 Что называется намерением', 'Что называется намерением?', [
        ('То, ради чего действие сделано', 1.0, 'Верно. Желание отвечает на вопрос «чего хочу», намерение — «ради кого».'),
        ('Сила желания', 0.0, 'Сила желания — его величина, и она ни о чём не говорит. Намерение — то, ради чего действие сделано.'),
        ('План на будущее', 0.0, 'План — это шаги. Намерение — то, ради чего делается каждый из них: конечная цель во всех промежуточных действиях.'),
        ('Решение начать', 0.0, 'Решение начать — ещё не намерение. Намерение — то, ради чего действие сделано.')]),
    ('У9-02 Двое приносят голодному еду', 'Двое приносят голодному еду: один потому что тот голоден, другой чтобы об этом узнали. В чём разница?', [
        ('В намерении — действие одинаковое', 1.0, 'Верно. Действие совпадает полностью, и со стороны различить нельзя. Но это разные поступки.'),
        ('В количестве еды', 0.0, 'Еда одна и та же, и получивший сыт в обоих случаях. Разница — в том, ради чего её принесли.'),
        ('В том, что второй сделал это хуже', 0.0, 'Сделано одинаково. Отличается только «ради чего» — намерение.'),
        ('Разницы нет', 0.0, 'Разница есть, хотя со стороны её не видно: это разные поступки, их отличает намерение.')]),
    ('У9-03 Двадцать часов в сутки', 'Человек работает 20 часов в сутки. Что решает, приносит ли он добро?', [
        ('Ради чего он работает', 1.0, 'Верно. «Действие ради других – это намерение, а не само действие».'),
        ('Сколько часов', 0.0, 'Часы ничего не значат: «Я могу работать 20 часов в сутки, но это ничего не значит». Решает, для чего.'),
        ('Где он работает', 0.0, 'Рабочий у станка, учительница в школе — снаружи это польза другим. Суть решает «для чего».'),
        ('Сколько он зарабатывает', 0.0, 'Заработок не решает. Если работать ради миллиона, никому добра не приносишь; решает, ради чего работаешь.')]),
    ('У9-04 Гость и хозяин', 'Что изменилось в примере «Гость и хозяин», когда гость сел за стол второй раз?', [
        ('Только намерение', 1.0, 'Верно. Тот же гость, та же еда, тот же голод — и голод обязателен.'),
        ('Он стал есть меньше', 0.0, 'Он ест то же самое, с тем же аппетитом. Изменилось только намерение.'),
        ('Он перестал быть голодным', 0.0, 'Голод обязателен: и теперь гость не станет есть без аппетита. Изменилось только намерение.'),
        ('Хозяин переменил угощение', 0.0, 'Угощение то же. Изменилось только то, ради чего гость ест: теперь его еда — подарок хозяину.')]),
    ('У9-05 Слон и мышь', 'Кто больший эгоист — слон, который хочет есть много, или мышь, которая хочет мало?', [
        ('Ни тот, ни другой: величина желания ни о чём не говорит', 1.0, 'Верно. Эгоизм определяется намерением, а не величиной желания.'),
        ('Слон', 0.0, 'Слон хочет больше, но от этого не больший эгоист: величина желания ни о чём не говорит.'),
        ('Мышь', 0.0, 'Маленькое желание не делает ни лучше, ни хуже. Эгоизм определяется намерением.'),
        ('Тот, кто съест первым', 0.0, 'Дело не в очерёдности. Эгоизм определяется намерением, а не величиной желания.')]),
    ('У9-06 Желание при смене намерения', 'Что происходит с желанием, когда меняется намерение?', [
        ('Остаётся тем же', 1.0, 'Верно. С желаниями не воюют, исправляется только намерение.'),
        ('Исчезает', 0.0, 'Желание не исчезает: «желание остаётся, меняется только намерение».'),
        ('Уменьшается', 0.0, 'Желание не уменьшают и не подавляют. Исправляется только намерение.'),
        ('Сменяется другим желанием', 0.0, 'Желание то же самое — меняется то, что из него следует. Как у наседки.')]),
    ('У9-07 Откуда правильное намерение', 'Откуда берётся правильное намерение?', [
        ('Приходит свыше, в ответ на просьбу', 1.0, 'Верно. Намерение не переключается решением; его просят.'),
        ('Вырабатывается силой воли', 0.0, 'Попытка «теперь буду всё делать ради других» даёт самообман или усталость. Правильное намерение просят.'),
        ('Появляется с возрастом', 0.0, 'Возраст ни при чём. Правильное намерение приходит свыше, в ответ на просьбу.'),
        ('Передаётся по наследству', 0.0, 'Человек рождается в намерении «ради себя». Обращается оно только благодаря помощи свыше.')]),
    ('У9-08 Начинать с того, что есть', 'Можно ли начинать, если пока всё делается ради себя?', [
        ('Да — начинают с того, что есть', 1.0, 'Верно. «Начинай таким, какой ты есть… занимайся и преуспеешь».'),
        ('Нет, сначала надо исправиться', 0.0, 'Исправиться самому нельзя: «из ло лишма приходят к лишма» — начинают с «ради себя».'),
        ('Только после долгой подготовки', 0.0, 'Подготовка не нужна: начинают таким, какой ты есть.'),
        ('Только с учителем наедине', 0.0, 'Нет. Начинают с того, что есть, — и намерение растёт и проверяется среди людей.')]),
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
LESSON, CM_LESSON, CM_QUIZ, CM_RESUME, CM_OTKLIK, CM_BOOK = 1970, 13586, 13587, 13588, 13589, 13692
QUIZ, PAGE_RESUME, FORUM_OTKLIK, BOOK_ID = 1243, 2505, 3917, 975
# 17934 map -> 17935 toc -> 17936 intro -> 17939 ch1 -> 17937 ch2 -> 17938 ch3 -> new ch4 -> new ch5 -> new ch6
PAGE_IDS = {'map': 17934, 'toc': 17935, 'intro': 17936, 'ch1': 17939, 'ch2': 17937, 'ch3': 17938}
LAST_FINISH = 37292   # «Завершить» on 17938 -> «Продолжить»
EXP = int(datetime(2026, 12, 2, 23, 59, tzinfo=TZ).timestamp())   # lecture, otklik, test: Wednesday 23:59
# quiz slots -> question versions -> answer ids (from the DB, 18.10.2026); slots 7–8 = new questions 68873/68874 (copies of 68821)
QIDS = [([68814, 68815], [[284256, 284257, 284258, 284259], [284260, 284261, 284262, 284263]]),
        ([68816], [[284264, 284265, 284266, 284267]]),
        ([68817, 68818], [[284268, 284269, 284270, 284271], [284272, 284273, 284274, 284275]]),
        ([68819], [[284276, 284277, 284278, 284279]]),
        ([68820], [[284280, 284281, 284282, 284283]]),
        ([68821], [[284284, 284285, 284286, 284287]]),
        ([68873], [[284501, 284502, 284503, 284504]]),
        ([68874], [[284508, 284509, 284510, 284511]])]
ALLQ = [qid for qids, _ in QIDS for qid in qids]

if '--sql' in sys.argv:
    NOW = int(time.time())

    def hx(s):
        return '0x' + binascii.hexlify(s.encode('utf-8')).decode()

    def q(s):
        return "'" + s.replace(chr(92), chr(92) * 2).replace("'", "''") + "'"

    mappage = ('<p><a title="Начать" href="https://edu.kabacademy.com/mod/lesson/view.php?id=%d&amp;pageid=%d"><img class="img-fluid" src="%smaps/L09.png" alt="Карта Урок 9" width="1174" height="915"></a></p>\n'
               % (CM_LESSON, PAGE_IDS['toc'], IMG))
    open('html/p_map.html', 'w', encoding='utf-8', newline='\n').write(mappage)
    sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
    pages = [('map', '*', mappage), ('toc', 'Содержание', toc), ('intro', INTRO, intro)] + [
        ('ch%d' % i, CHAPTERS[i - 1], html) for i, html in enumerate((ch1, ch2, ch3), 1)]
    for key, title, html in pages:
        sql.append("UPDATE mdl_lesson_pages SET title=%s, contents=%s, timemodified=%d WHERE id=%d AND lessonid=%d;" % (q(title), hx(html), NOW, PAGE_IDS[key], LESSON))
    # chapter 3 (17938) was the last page: «Завершить» -> «Продолжить», then new pages for chapters 4–6
    last = PAGE_IDS['ch3']
    sql.append("UPDATE mdl_lesson_answers SET answer='Продолжить', jumpto=-1, timemodified=%d WHERE id=%d AND pageid=%d AND lessonid=%d;" % (NOW, LAST_FINISH, last, LESSON))
    sql.append("SET @prev = %d;" % last)
    for i, html in ((4, ch4), (5, ch5), (6, ch6)):
        sql.append("INSERT INTO mdl_lesson_pages (lessonid,prevpageid,nextpageid,qtype,qoption,layout,display,timecreated,timemodified,title,contents,contentsformat) "
                   "SELECT lessonid,@prev,0,qtype,qoption,layout,display,%d,%d,%s,%s,contentsformat FROM mdl_lesson_pages WHERE id=%d;" % (NOW, NOW, q(CHAPTERS[i - 1]), hx(html), last))
        sql.append("SET @n = LAST_INSERT_ID();")
        sql.append("UPDATE mdl_lesson_pages SET nextpageid=@n WHERE id=@prev;")
        final = (i == 6)
        sql.append("INSERT INTO mdl_lesson_answers (lessonid,pageid,jumpto,grade,score,flags,timecreated,timemodified,answer,answerformat,response,responseformat) VALUES "
                   "(%d,@n,-40,0,0,0,%d,%d,'Вернуться',0,NULL,0),(%d,@n,%d,0,0,0,%d,%d,'%s',0,NULL,0);"
                   % (LESSON, NOW, NOW, LESSON, -9 if final else -1, NOW, NOW, 'Завершить' if final else 'Продолжить'))
        sql.append("SET @prev = @n;")
    sql.append("UPDATE mdl_lesson SET timemodified=%d WHERE id=%d;" % (NOW, LESSON))
    # quiz
    for (name, text, answers), (qids, aidss) in zip(QUESTIONS, QIDS):
        assert sum(f for _, f, _ in answers) == 1.0, name
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
    # open the lecture, test and résumé (the content subsections stay date-locked until 27.11 08:00)
    sql.append("UPDATE mdl_course_modules SET visible=1, visibleold=1 WHERE id IN (%d,%d,%d);" % (CM_LESSON, CM_QUIZ, CM_RESUME))
    sql.append("UPDATE mdl_course_modules SET completionexpected=%d WHERE id IN (%d,%d,%d);" % (EXP, CM_LESSON, CM_OTKLIK, CM_QUIZ))
    for mod, inst in (('lesson', LESSON), ('forum', FORUM_OTKLIK), ('quiz', QUIZ)):
        sql.append("UPDATE mdl_event SET timestart=%d, timesort=%d, visible=1, timemodified=%d WHERE courseid=236 AND modulename='%s' AND instance=%d AND eventtype='expectcompletionon';" % (EXP, EXP, NOW, mod, inst))
    sql.append("UPDATE mdl_event SET visible=1, timemodified=%d WHERE courseid=236 AND modulename='lesson' AND instance=%d AND eventtype='open';" % (NOW, LESSON))
    # book 975 (cm 13692, a copy of the lesson-8 book): name and chapters
    sql.append("UPDATE mdl_book SET name='Дополнительные материалы к Уроку 9', revision=revision+1, timemodified=%d WHERE id=%d;" % (NOW, BOOK_ID))
    sql.append("DELETE FROM mdl_book_chapters WHERE bookid=%d;" % BOOK_ID)
    for i, (title, html) in enumerate(BOOK, 1):
        sql.append("INSERT INTO mdl_book_chapters (bookid,pagenum,subchapter,title,content,contentformat,hidden,timecreated,timemodified,importsrc) "
                   "VALUES (%d,%d,0,%s,%s,1,0,%d,%d,'');" % (BOOK_ID, i, q(title), hx(html), NOW, NOW))
    sql.append("UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;")
    sql += ['COMMIT;',
            "SELECT id,prevpageid,nextpageid,title,LENGTH(contents) clen FROM mdl_lesson_pages WHERE lessonid=%d ORDER BY id;" % LESSON,
            "SELECT id,pageid,jumpto,answer FROM mdl_lesson_answers WHERE lessonid=%d ORDER BY pageid,id;" % LESSON,
            "SELECT question,COUNT(*) n,SUM(fraction) s FROM mdl_question_answers WHERE question IN (%s) GROUP BY question;" % ','.join(map(str, ALLQ)),
            "SELECT id,name FROM mdl_question WHERE id IN (%s);" % ','.join(map(str, ALLQ)),
            "SELECT id,visible,completionexpected FROM mdl_course_modules WHERE id IN (%d,%d,%d,%d,%d);" % (CM_LESSON, CM_QUIZ, CM_RESUME, CM_OTKLIK, CM_BOOK),
            "SELECT id,name,modulename,instance,eventtype,FROM_UNIXTIME(timestart),visible FROM mdl_event WHERE courseid=236 AND ((modulename='lesson' AND instance=%d) OR (modulename='quiz' AND instance=%d) OR (modulename='forum' AND instance=%d));" % (LESSON, QUIZ, FORUM_OTKLIK),
            "SELECT id,pagenum,title,LENGTH(content) FROM mdl_book_chapters WHERE bookid=%d ORDER BY pagenum;" % BOOK_ID]
    open('lesson9.sql', 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
    print('sql statements:', len(sql), 'bytes:', os.path.getsize('lesson9.sql'), 'EXP', EXP)
