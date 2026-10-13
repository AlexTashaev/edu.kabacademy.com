# -*- coding: utf-8 -*-
"""Lesson 3 «Восприятие реальности», course 236 — FINAL text (doc «Урок 3 — СО финал», 13.10.2026),
kept in the lesson 1–2 format: map -> contents (+ three questions) -> chapters 1–5; quiz «Проверьте себя» (6);
résumé; otklik with three questions. Writes l3f/*.html; with --sql writes lesson3f.sql.
"""
import os, sys, time, binascii, re
os.chdir(r'C:\Users\tashaev\AppData\Local\Temp\kabsc2')
os.makedirs('l3f', exist_ok=True)
FILER = 'https://files.kabbalahmedia.info/kabacademy/MAK/'
IMG = 'https://edu.kabacademy.com/kab/img/'
GRAY = '#7A7A7A'

def clip(name, ar='16 / 9', maxw=None):
    extra = ' max-width: %dpx; margin: 0 auto;' % maxw if maxw else ''
    return ('<div class="kab-clip" style="border-left: 3px solid #4a90a4; background: #f2f8fa; padding: 12px 16px; margin: 20px 0;">\n'
            '<video class="nomediaplugin" controls="controls" controlslist="nodownload" preload="metadata" playsinline="playsinline" oncontextmenu="return false;" '
            'poster="%s%s.jpg" style="display: block; width: 100%%;%s aspect-ratio: %s; background: #000;"><source src="%s%s.mp4" type="video/mp4"></video>\n</div>\n'
            % (FILER, name, extra, ar, FILER, name))

def figure(code, n, w, h, alt, caption):
    return ('<div class="kab-figure" style="border: 1px solid #D9E8F2; border-radius: 12px; background: #fff; padding: 12px 12px 10px; margin: 20px 0;">\n'
            '<a href="%(i)sschemes/%(c)s.webp?v=1" target="_blank" rel="noopener" style="display: block;">\n<picture>\n'
            '<source media="(prefers-reduced-motion: reduce)" srcset="%(i)sschemes/%(c)s-static.png?v=1" width="%(w)d" height="%(h)d">\n'
            '<source type="image/webp" srcset="%(i)sschemes/%(c)s.webp?v=1" width="%(w)d" height="%(h)d">\n'
            '<img src="%(i)sschemes/%(c)s-static.png?v=1" width="%(w)d" height="%(h)d" alt="Схема %(n)d. %(alt)s" style="display: block; width: auto; max-width: 100%%; height: auto; margin: 0 auto; border-radius: 8px;">\n'
            '</picture>\n</a>\n'
            '<div style="border-top: 1px solid #EFF7FA; margin-top: 12px; padding-top: 10px; font-size: 13.5px; line-height: 1.6; color: #7A7A7A;"><span style="font-weight: 700; color: #3C5D90;">Схема %(n)d.</span> %(cap)s</div>\n'
            '</div>\n' % dict(i=IMG, c=code, n=n, w=w, h=h, alt=alt, cap=caption))

def yt(title, ytid, driveid):
    return ('<p style="text-align: center;"><a class="external-media-provider" href="https://youtu.be/%s">%s</a></p>\n'
            '<details class="kab-details">\n<summary><span style="color: #3c5d90;">Альтернативный плеер <em>(если видео выше не воспроизводится)</em></span></summary>\n'
            '<br><iframe src="https://drive.google.com/file/d/%s/preview" width="100%%" height="480" allow="autoplay"></iframe></details>\n' % (ytid, title, driveid))

def srcquote(text, who):
    return ('<div class="kab-quote">\n<p>%s</p>\n'
            '<div style="text-align: right; font-size: 13.5px; line-height: 1.5; color: %s; margin-top: 10px;">— %s</div>\n</div>\n' % (text, GRAY, who))

def p(t):
    return '<p>%s</p>\n' % t

def h4(t):
    return '<h4>%s</h4>\n' % t

# ---------------- Содержание (как в уроке 2) ----------------
toc = '''<div class="kab-text">
<div class="kab-quote">
<ul class="kab-list">
<li>Глава 1. Наш мир и пять органов чувств</li>
<li>Глава 2. Четыре подхода к тому, какова реальность</li>
<li>Глава 3. Желание определяет то, что человек видит</li>
<li>Глава 4. Закон подобия свойств</li>
<li>Глава 5. Экран — шестой орган чувств</li>
</ul>
</div>
<div class="kab-kicker">Прежде чем читать</div>
<p>Три вопроса для размышления.</p>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Откуда вы знаете, что мир вне вас именно такой, каким вы его видите?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Почему одно и то же событие двое переживают по-разному?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>Если высшая сила есть, чем её ощущать?</div></div>
</div>
</div>
<p></p>
'''

# ---------------- Глава 1 ----------------
ch1 = ''
ch1 += p('<strong>Та часть Вселенной, которую мы ощущаем нашими органами чувств, называется «наш мир».</strong>')
ch1 += p('Органов чувств пять: зрение, слух, обоняние, вкус, осязание. Всё, что через них не проходит, в наш мир не входит.')
ch1 += figure('L03-01', 6, 800, 498,
              'Пять узких щелей в перегородке, через которые к человеку доходят зрение, слух, обоняние, вкус и осязание',
              'Пять узких щелей: из всего, что есть снаружи, до человека доходит только то, что проходит через зрение, слух, обоняние, вкус и осязание. Остальное в наш мир не входит.')
ch1 += clip('Lesson-03-clip-01')
ch1 += clip('Lesson-03-clip-02', '4 / 3', 480)
ch1 += figure('L03-03', 7, 800, 382,
              'Видимый свет — узкая полоса электромагнитного спектра между ультрафиолетом и инфракрасным излучением, примерно от 400 до 700 нанометров',
              'Электромагнитный спектр: гамма-лучи, рентген, ультрафиолет, инфракрасное излучение, микроволны, радио. Видимый свет — узкая полоса посередине, примерно 400–700 нанометров. Это всё, что видит человек; остальное добавляют приборы.')
ch1 += figure('L03-04', 8, 800, 738,
              'Звуковые колебания: инфразвук кита, слышимый человеком диапазон от 20 герц до 20 килогерц, ультразвук летучей мыши',
              'Звуковые колебания: кит переговаривается в инфразвуке, летучая мышь — в ультразвуке. Человек слышит полосу от 20 Гц до 20 кГц, и это всё, что он слышит.')

# ---------------- Глава 2 ----------------
ch2 = ''
ch2 += p('Вопрос «каков мир на самом деле» человечество решало не однажды. Каббала не спорит с предыдущими ответами — она отвечает всем трём и делает следующий шаг.')
ch2 += '''<table style="border-collapse: collapse; width: 100%;">
<thead><tr>
<th style="text-align: left; padding: 6px 10px; border-bottom: 2px solid #ccc;">Подход</th>
<th style="text-align: left; padding: 6px 10px; border-bottom: 2px solid #ccc;">Что утверждает</th>
</tr></thead>
<tbody>
<tr><td style="padding: 6px 10px; border-bottom: 1px solid #e3e3e3;">Классическая физика, Ньютон</td><td style="padding: 6px 10px; border-bottom: 1px solid #e3e3e3;">Мир существует сам по себе, без связи с человеком, и неизменен</td></tr>
<tr><td style="padding: 6px 10px; border-bottom: 1px solid #e3e3e3;">Теория относительности, Эйнштейн</td><td style="padding: 6px 10px; border-bottom: 1px solid #e3e3e3;">Мир шире, чем мы его воспринимаем. Восприятие относительно: зависит от наблюдателя, его органов чувств, места и скорости</td></tr>
<tr><td style="padding: 6px 10px; border-bottom: 1px solid #e3e3e3;">Квантовая физика</td><td style="padding: 6px 10px; border-bottom: 1px solid #e3e3e3;">Наблюдатель влияет на наблюдаемое и тем изменяет картину. Воспринятое — среднее между свойствами наблюдателя и объекта</td></tr>
<tr><td style="padding: 6px 10px;">Каббала</td><td style="padding: 6px 10px;">Человек сам определяет собственную картину реальности</td></tr>
</tbody></table>
'''
ch2 += p('<strong>Зачем это знать.</strong> Осознание того, что человек сам строит свою реальность, побуждает к действию — к изменению себя. Если картина зависит от смотрящего, то работать надо со смотрящим.')
ch2 += srcquote('«…нет у нас никакого постижения и восприятия ни в каком материале, так как все пять наших органов чувств не готовы к этому: зрение, слух, обоняние, вкус и осязание передают в мозг для обработки только сигналы, отражающие некие свойства разновидностей сущности, приобретающие форму только при взаимодействии с нашими органами чувств».', 'Бааль Сулам, «Внутреннее созерцание»')
ch2 += p('То есть <strong>форма — не свойство предмета.</strong> Форма возникает при взаимодействии предмета с тем, кто его воспринимает.')

# ---------------- Глава 3 ----------------
ch3 = ''
ch3 += p('<strong>Наши эгоцентрические желания определяют нашу реальность.</strong> Мы смотрим на мир через призму эгоизма.')
ch3 += p('Желание получать работает по простой схеме:')
ch3 += '<ul>\n<li><strong>получить то, что для него благотворно;</strong></li>\n<li><strong>отвергнуть то, что ему неблагоприятно.</strong></li>\n</ul>\n'
ch3 += p('Все органы чувств и все механизмы обработки, включая экран на задней стенке мозга, где проецируется картина действительности, — <strong>агенты желания получать</strong>, выполняющие его волю: взять приятное, избежать неприятного.')
ch3 += clip('Lesson-03-clip-03', '4 / 3', 480)
ch3 += clip('Lesson-03-clip-04', '4 / 3', 480)
ch3 += p('Отсюда следствие, которое проверяется на себе. Если картина складывается из свойств смотрящего, то и то, что он видит в другом человеке, — сведения о нём самом.')
ch3 += srcquote('<strong>«Каждый отрицает согласно своему изъяну».</strong>', 'Вавилонский Талмуд, III век')
ch3 += srcquote('<strong>«Если человек видит в ком-то недостаток, то он словно смотрится в зеркало: если его лицо испачкано, то это и показывает ему зеркало. А если лицо чистое, то он не видит в зеркале никакого дефекта. Он видит себя таким, как он есть».</strong>', 'Бааль Шем Тов')

# ---------------- Глава 4 ----------------
ch4 = ''
ch4 += p('Это основной закон мироздания, и на нём стоит весь дальнейший курс.')
ch4 += p('<strong>Подобные в свойствах притягиваются — в мере подобия, вплоть до слияния в полном подобии.</strong>')
ch4 += p('Сформулировать можно и так: <strong>ощутить какое-либо явление можно только в той мере, в какой ты сам из него состоишь.</strong> Если я что-то вижу, слышу, чувствую на вкус или запах — значит во мне есть свойства, которые есть и в том, что я ощущаю.')
ch4 += p('Значит, чтобы что-то ощутить, <strong>надо создать в себе прибор, улавливающий существующие снаружи частоты.</strong>')
ch4 += p('<strong>Радиоприёмник.</strong> Что происходит, когда крутят ручку настройки? В приёмнике генерируется определённая волна. Если такая же волна есть снаружи, антенна улавливает её по принципу резонанса — и в динамике появляется звук.')
ch4 += figure('L03-02', 9, 800, 527,
              'Резонанс: волна снаружи, приёмник внутри; когда частоты совпадают, появляется звук',
              'Волна снаружи и приёмник внутри. Пока частоты не совпадают, для приёмника на этой волне нет ничего. Совпали — резонанс, и звук появился. Волна была и до этого; приёмника не было.')
ch4 += clip('Lesson-03-clip-05')
ch4 += p('<strong>Зачем это в курсе.</strong> С первого урока говорится, что есть сила отдачи. Отсюда вопрос: чем её ощутить?')
ch4 += p('По закону подобия свойств ответ такой: <strong>имеющимися пятью органами чувств — нечем.</strong> Все они устроены на получение. Сила отдачи в этот диапазон не попадает — не потому что далеко или скрывается, а потому что нет приёмника.')
ch4 += p('<strong>Творец, или Природа, — это свойство отдачи. Значит творение должно развить в себе такое же свойство, чтобы его почувствовать.</strong>')
ch4 += p('И для этого <strong>нам дано общество</strong> — место, где человек может проверить и точно определить своё отношение к ближнему. Как этим пользоваться, разбирается в уроке 8.')

# ---------------- Глава 5 ----------------
ch5 = ''
ch5 += p('<strong>Есть дополнительный, шестой орган чувств, который называется «экран». С его помощью можно проникнуть сквозь наш мир и увидеть его более внутренние слои.</strong>')
ch5 += p('<strong>Как обрести экран? Этим и занимается каббала.</strong> Она рассказывает человеку, как это можно сделать.')
ch5 += p('Речь о свойстве, которого у человека сейчас нет и которое вырабатывается.')
ch5 += p('<strong>Экран — не отказ от желания и не его уменьшение.</strong> Желание остаётся целиком; меняется то, ради чего оно наполняется. Это то же различение желания и намерения, что в уроке 2, и подробно оно разбирается в уроке 9.')
ch5 += clip('Lesson-03-clip-06')
ch5 += p('Единственный способ видеть мир по-другому — сближение друг с другом.')
ch5 += clip('Lesson-03-clip-K1')
ch5 += h4('Что дальше')
ch5 += p('На следующей неделе — <strong>строение мироздания.</strong> Мы говорили, что всё, кроме силы отдачи, — это желание получать. Откуда оно взялось и как развивалось, шаг за шагом.')

# ---------------- Резюме ----------------
resume = '''<div class="kab-page">
<div class="kab-card">
<div class="kab-row"><div class="kab-dot"></div><div><b>Основной закон мироздания — «Закон подобия свойств»</b>, в соответствии с которым подобные в свойствах притягиваются, в мере подобия.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div><b>Наш мир мы ощущаем пятью органами чувств:</b> зрением, слухом, обонянием, вкусом, осязанием.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div><b>«Экран» — дополнительный, шестой орган чувств</b>, которым мы будем ощущать высший мир.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div><b>Каббала — методика приобретения человеком экрана.</b></div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Форма не является свойством предмета: она возникает при взаимодействии предмета с органом чувств.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Наши эгоцентрические желания определяют то, что мы видим. Органы чувств — агенты желания получать.</div></div>
<div class="kab-row"><div class="kab-dot"></div><div>Чтобы ощутить свойство отдачи, творение должно развить в себе это свойство. Место, где оно проверяется, — общество.</div></div>
</div>
<div class="kab-quote kab-quote--purple">На следующей неделе — <b>строение мироздания</b>. Мы говорили, что всё, кроме силы отдачи, — это желание получать. Откуда оно взялось и как развивалось, шаг за шагом.</div>
</div>
'''

# ---------------- Отклик недели (формат урока 2: три вопроса на выбор) ----------------
otklik = '''<div class="kab-text">
<div class="kab-quote">Напишите своими словами ответ на любой из трёх вопросов. Один, не все три.</div>
<div class="kab-card">
<div class="kab-row"><div class="kab-num">1</div><div>Что такое «экран» и зачем он нужен?</div></div>
<div class="kab-row"><div class="kab-num">2</div><div>Почему мы видим в других отрицательные качества, а мать видит в своём ребёнке только положительное?</div></div>
<div class="kab-row"><div class="kab-num">3</div><div>При каких условиях можно увидеть в другом положительное?</div></div>
</div>
<div class="kab-note">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>
</div>
'''

# ---------------- Книга «Дополнительные материалы», глава 1: + вступительный ролик старого урока ----------------
book_ch1 = '<div class="kab-page">\n<div class="kab-quote">Лекция курса «Основы каббалы» по теме урока — в записи. Тот же материал в лекционном изложении; ядро урока на него не опирается.</div>\n'
book_ch1 += '<div class="kab-kicker">Вступление</div>\n'
book_ch1 += yt('Восприятие реальности. Вступление к лекции', 'egjTNqzDh9w', '1EHM4EuYqPbeBO6m8rqtsU2vj9WzhenjH')
book_ch1 += '<div class="kab-kicker">Восприятие реальности. Преподаватель Борис Белоцерковский</div>\n'
book_ch1 += yt('Восприятие реальности. Основы каббалы, часть 1 (преподаватель Борис Белоцерковский)', 'V12UNh9bZCw', '1QUBtBzzEQASTK9DfCnfpBBk_9qxU2uZv')
book_ch1 += '<div class="kab-kicker">Лекция в трёх частях</div>\n'
book_ch1 += '<p><strong>Реальность с точки зрения каббалы</strong></p>\n' + yt('Восприятие реальности. Часть 1', 'nYaLAwAi-XQ', '13VJXh7W3euTSd7ijXnBTB3sbk7kom3pD')
book_ch1 += '<p><strong>Постижение реальности. Желание. Адам</strong></p>\n' + yt('Восприятие реальности. Часть 2', 'MHppTAHOzPY', '10d0b0uzxx7SE3Wpyer8QiX6w67EyH-Id')
book_ch1 += '<p><strong>Картина восприятия. Орган ощущения реальности</strong></p>\n' + yt('Восприятие реальности. Часть 3', '4K8i50NvxVE', '1BCP2Ir7ZNWOqB_vsCMJMa0vCcBYw1QcH')
book_ch1 += '<div class="kab-kicker">Ответы на вопросы по теме</div>\n'
book_ch1 += yt('Ответы на вопросы по теме «Восприятие реальности»', '0R7EN9lP9pE', '1v_TBwNKxGnJHmyIkC_avWMVQmhgw89XM')
book_ch1 += '</div>\n'

# ---------------- Семинар: текст темы (пост публикует владелец, как в уроках 1–2) ----------------
seminar_post = '''<div class="kab-text">
<div class="kab-quote">На третьей учебной неделе у нас семинар на тему «Связь нашего и высшего миров».<br>Приглашаем всех посмотреть ролик и ответить на вопрос:<br><em>Как, находясь в нашем мире, ощутить Высший мир?</em></div>
<p style="text-align: center;"><a class="kab-btn" href="https://www.youtube.com/watch?v=t8j5VG9tlz4" target="_blank" rel="noopener">Семинар 3 «Связь нашего и высшего миров»</a></p>
<details class="kab-details">
<summary>Альтернативный плеер <em>(если видео выше не воспроизводится)</em></summary>
<br><iframe src="https://drive.google.com/file/d/1BcZuLc_-MnDqVQPC8U8W5_BbgBVPLu9q/preview" width="100%" height="480" allow="autoplay"></iframe>
</details>
<div class="kab-note">Для ответа нажмите «Ответить» справа внизу под этим сообщением.</div>
</div>
'''

PAGES = {'p_toc': toc, 'p_ch1': ch1, 'p_ch2': ch2, 'p_ch3': ch3, 'p_ch4': ch4, 'p_ch5': ch5,
         'page_resume': resume, 'forum_otklik': otklik, 'book_ch1': book_ch1, 'seminar_post': seminar_post}
for k, v in PAGES.items():
    assert not re.search(r'\bРав\b', v), k
    open('l3f/%s.html' % k, 'w', encoding='utf-8', newline='\n').write(v)
print('pages:', ', '.join('%s=%d' % (k, len(v)) for k, v in PAGES.items()))

# ---------------- Тест «Проверьте себя» (quiz 1237) ----------------
# (name, text, answers[(text, fraction, feedback)], single)
QUESTIONS = [
    ('У3-01 Как формируется картина мира', 'Как в нас формируется картина нашего мира?', [
        ('С помощью пяти органов чувств', 1.0, 'Верно. Зрение, слух, обоняние, вкус, осязание. Шестой — экран — не дан, его вырабатывают.'),
        ('С помощью шести органов чувств', 0.0, 'Шестой орган чувств — экран — человеку не дан, его вырабатывают. Картина нашего мира складывается пятью.'),
        ('С помощью интуиции', 0.0, 'Интуиция — не отдельный канал восприятия. Картина складывается из того, что доходит через пять органов чувств.')], True),
    ('У3-02 Что такое «наш мир»', 'Что такое «наш мир»? <em>Несколько правильных ответов.</em>', [
        ('То, что ощущается в пяти органах чувств', 0.5, 'Верно. Наш мир — та часть Вселенной, которая доходит до пяти органов чувств.'),
        ('Моя семья, работа, друзья, природа', 0.5, 'Верно. Всё это доходит до нас через пять органов чувств и потому входит в наш мир.'),
        ('То, что я могу вообразить', -0.5, 'Воображаемое не ощущается органами чувств — в наш мир оно не входит.'),
        ('То, что я не могу ощутить', -0.5, 'Наоборот: наш мир — ровно то, что ощущается. Всё, что вне диапазона органов чувств, в него не входит.')], False),
    ('У3-03 Для чего нужен экран', 'Для чего нужен экран?', [
        ('Чтобы увидеть более внутренние слои мира', 1.0, 'Верно. И заметьте, чего в ответе нет: экран не уменьшает желание. Желание остаётся, меняется то, ради чего оно наполняется.'),
        ('Чтобы получить безграничное удовольствие', 0.0, 'Экран — не способ взять больше. Это орган, которым ощущаются более внутренние слои мира.'),
        ('Чтобы перестать хотеть', 0.0, 'Экран не уменьшает желание. Желание остаётся целиком, меняется то, ради чего оно наполняется.'),
        ('Чтобы защититься от чужого влияния', 0.0, 'Экран ничего не отгораживает. Это шестой орган чувств, которым видны более внутренние слои мира.')], True),
    ('У3-04 Закон подобия свойств', 'В чём состоит закон подобия свойств?', [
        ('Подобные в свойствах притягиваются, в мере подобия', 1.0, 'Верно. Ощутить можно только то, чему ты подобен: пока приёмник не настроен на частоту, для человека на ней нет ничего — при том что передача идёт.'),
        ('Подобное отталкивает подобное', 0.0, 'Наоборот: подобные в свойствах притягиваются, в мере подобия.'),
        ('Свойства передаются по наследству', 0.0, 'Закон не о наследовании, а о восприятии: подобные в свойствах притягиваются, в мере подобия.'),
        ('Чем больше желание, тем точнее восприятие', 0.0, 'Размер желания ни при чём. Решает подобие свойств: ощутить можно только то, чему ты подобен.')], True),
    ('У3-05 Почему силу отдачи нельзя ощутить', 'Почему силу отдачи нельзя ощутить пятью органами чувств?', [
        ('Они устроены на получение', 1.0, 'Верно. Взять благотворное, отвергнуть неблагоприятное. Отдача в этот диапазон не попадает — нет приёмника, а не помеха на линии.'),
        ('Она слишком слабая', 0.0, 'Дело не в силе сигнала. Пять органов чувств устроены на получение, а отдача им не подобна — нет приёмника.'),
        ('Она находится далеко', 0.0, 'Расстояние ни при чём: и рядом её нечем уловить. Органы чувств устроены на получение, отдача в их диапазон не попадает.'),
        ('Она намеренно скрывается', 0.0, 'Никто ничего не прячет. Органы чувств устроены на получение, и сила отдачи просто не попадает в их диапазон.')], True),
    ('У3-06 Каббала и квантовая физика', 'Чем отличается подход каббалы от квантовой физики?', [
        ('Каббала говорит, что человек сам определяет свою картину реальности', 1.0, 'Верно. Квантовая физика говорит, что наблюдатель влияет на наблюдаемое; каббала делает следующий шаг и переносит работу на самого наблюдателя.'),
        ('Каббала отрицает влияние наблюдателя', 0.0, 'Нет: каббала не спорит с квантовой физикой, а идёт дальше — человек сам определяет свою картину реальности.'),
        ('Каббала отрицает существование мира', 0.0, 'Каббала не отрицает мир. Она говорит, что сказать можно только о том, что человек ощущает, и картину он определяет сам.'),
        ('Они не отличаются', 0.0, 'Отличие есть: квантовая физика говорит о влиянии наблюдателя, каббала — что человек сам определяет картину, и потому работать надо с ним.')], True),
]

if '--sql' in sys.argv:
    NOW = int(time.time())
    def hx(s):
        return '0x' + binascii.hexlify(s.encode('utf-8')).decode()
    def q(s):
        return "'" + s.replace(chr(92), chr(92) * 2).replace("'", "''") + "'"
    L = 1964
    sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
    # chain: 17898 * -> 17899 toc -> 17900 ch1 -> 17903 ch2 -> 17901 ch3 -> 17902 ch4 -> new ch5
    existing = {17899: ('Содержание', toc), 17900: ('Глава 1. Наш мир и пять органов чувств', ch1),
                17903: ('Глава 2. Четыре подхода к тому, какова реальность', ch2),
                17901: ('Глава 3. Желание определяет то, что человек видит', ch3),
                17902: ('Глава 4. Закон подобия свойств', ch4)}
    for pid, (title, html) in existing.items():
        sql.append("UPDATE mdl_lesson_pages SET title=%s, contents=%s, timemodified=%d WHERE id=%d AND lessonid=%d;" % (q(title), hx(html), NOW, pid, L))
    sql.append("UPDATE mdl_lesson_answers SET answer='Продолжить', jumpto=-1, timemodified=%d WHERE id=37238 AND pageid=17902 AND lessonid=%d;" % (NOW, L))
    sql.append("INSERT INTO mdl_lesson_pages (lessonid,prevpageid,nextpageid,qtype,qoption,layout,display,timecreated,timemodified,title,contents,contentsformat) "
               "SELECT lessonid,17902,0,qtype,qoption,layout,display,%d,%d,%s,%s,contentsformat FROM mdl_lesson_pages WHERE id=17902;" % (NOW, NOW, q('Глава 5. Экран — шестой орган чувств'), hx(ch5)))
    sql.append("SET @n5 = LAST_INSERT_ID();")
    sql.append("UPDATE mdl_lesson_pages SET nextpageid=@n5 WHERE id=17902;")
    sql.append("INSERT INTO mdl_lesson_answers (lessonid,pageid,jumpto,grade,score,flags,timecreated,timemodified,answer,answerformat,response,responseformat) VALUES "
               "(%d,@n5,-40,0,0,0,%d,%d,'Вернуться',0,NULL,0),(%d,@n5,-9,0,0,0,%d,%d,'Завершить',0,NULL,0);" % (L, NOW, NOW, L, NOW, NOW))
    sql.append("UPDATE mdl_lesson SET timemodified=%d WHERE id=%d;" % (NOW, L))
    # quiz 1237 «Проверьте себя»
    QIDS = [([68766, 68767], [[284064, 284065, 284066, 284067], [284068, 284069, 284070, 284071]]),
            ([68768], [[284072, 284073, 284074, 284075]]),
            ([68769, 68770], [[284076, 284077, 284078, 284079], [284080, 284081, 284082, 284083]]),
            ([68771], [[284084, 284085, 284086, 284087]]),
            ([68772], [[284088, 284089, 284090, 284091]]),
            ([68773], [[284092, 284093, 284094, 284095]])]
    for (name, text, answers, single), (qids, aidss) in zip(QUESTIONS, QIDS):
        for qid, aids in zip(qids, aidss):
            sql.append("UPDATE mdl_question SET name=%s, questiontext=%s, timemodified=%d WHERE id=%d;" % (q(name), hx('<p>%s</p>\n' % text), NOW, qid))
            sql.append("UPDATE mdl_qtype_multichoice_options SET single=%d WHERE questionid=%d;" % (1 if single else 0, qid))
            for (atext, frac, fb), aid in zip(answers, aids):
                sql.append("UPDATE mdl_question_answers SET answer=%s, fraction=%s, feedback=%s WHERE id=%d AND question=%d;" % (hx('<p>%s</p>\n' % atext), repr(frac), hx('<p>%s</p>\n' % fb), aid, qid))
            for aid in aids[len(answers):]:
                sql.append("DELETE FROM mdl_question_answers WHERE id=%d AND question=%d;" % (aid, qid))
    # résumé, otklik, book chapter 1
    sql.append("UPDATE mdl_page SET content=%s, timemodified=%d WHERE id=2487;" % (hx(resume), NOW))
    sql.append("UPDATE mdl_forum SET intro=%s, timemodified=%d WHERE id=3902;" % (hx(otklik), NOW))
    sql.append("UPDATE mdl_book_chapters SET content=%s, timemodified=%d WHERE id=2269 AND bookid=968;" % (hx(book_ch1), NOW))
    sql.append("UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;")
    sql += ['COMMIT;', "SELECT id,prevpageid,nextpageid,title,LENGTH(contents) clen FROM mdl_lesson_pages WHERE lessonid=%d ORDER BY id; SELECT id,pageid,jumpto,answer FROM mdl_lesson_answers WHERE lessonid=%d ORDER BY pageid,id; SELECT id,name FROM mdl_question WHERE id BETWEEN 68766 AND 68773; SELECT question,COUNT(*) n,SUM(fraction) s FROM mdl_question_answers WHERE question BETWEEN 68766 AND 68773 GROUP BY question;" % (L, L)]
    open('lesson3f.sql', 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
    print('sql statements:', len(sql), 'bytes:', os.path.getsize('lesson3f.sql'))
