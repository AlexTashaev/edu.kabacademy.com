# -*- coding: utf-8 -*-
"""Teacher's edits 13.10.2026 (doc «Изменения в 3 уроке»): captions under kept clips, two clips removed, résumé bullet replaced."""
import os
os.chdir(r'C:\Users\tashaev\AppData\Local\Temp\kabsc2')
p = 'build_l3f.py'
s = open(p, encoding='utf-8').read()

def rep(old, new, count=1):
    global s
    assert s.count(old) == count, (old[:60], s.count(old))
    s = s.replace(old, new)

# clip(): optional caption under the video (title — source; quote)
rep('''def clip(name, ar='16 / 9', maxw=None):
    extra = ' max-width: %dpx; margin: 0 auto;' % maxw if maxw else ''
    return ('<div class="kab-clip" style="border-left: 3px solid #4a90a4; background: #f2f8fa; padding: 12px 16px; margin: 20px 0;">\\n'
            '<video class="nomediaplugin" controls="controls" controlslist="nodownload" preload="metadata" playsinline="playsinline" oncontextmenu="return false;" '
            'poster="%s%s.jpg" style="display: block; width: 100%%;%s aspect-ratio: %s; background: #000;"><source src="%s%s.mp4" type="video/mp4"></video>\\n</div>\\n'
            % (FILER, name, extra, ar, FILER, name))''',
    '''def clip(name, ar='16 / 9', maxw=None, title=None, source=None, quote=None):
    extra = ' max-width: %dpx; margin: 0 auto;' % maxw if maxw else ''
    out = ('<div class="kab-clip" style="border-left: 3px solid #4a90a4; background: #f2f8fa; padding: 12px 16px; margin: 20px 0;">\\n'
           '<video class="nomediaplugin" controls="controls" controlslist="nodownload" preload="metadata" playsinline="playsinline" oncontextmenu="return false;" '
           'poster="%s%s.jpg" style="display: block; width: 100%%;%s aspect-ratio: %s; background: #000;"><source src="%s%s.mp4" type="video/mp4"></video>\\n'
           % (FILER, name, extra, ar, FILER, name))
    if title:
        out += ('<div style="margin-top: 10px; font-size: 14px; line-height: 1.55; color: %s;"><span style="font-weight: 600; color: #3C5D90;">%s</span>'
                '%s%s</div>\\n' % (GRAY, title, (' — ' + source) if source else '', ('<br>' + quote) if quote else ''))
    out += '</div>\\n'
    return out''')

# глава 1: оставить первый клип с подписью, второй (2010, 320x240) убрать
rep("ch1 += clip('Lesson-03-clip-01')\nch1 += clip('Lesson-03-clip-02', '4 / 3', 480)\n",
    "ch1 += clip('Lesson-03-clip-01', title='«Мир находится внутри нас»', source='Михаэль Лайтман, «Вместе о главном. Революция сознания», 2018',\n"
    "            quote='«Мы живём в определённом объёме, который рисуется нами как бы существующим вне нас. На самом деле он находится внутри нас, поскольку мы изображаем его в наших внутренних свойствах. […] Оказывается, мир не имеет какого-то своего образа, своей картины, своего вида, а мы его создаём».')\n")
# глава 3: оба клипа оставить, подписать
rep("ch3 += clip('Lesson-03-clip-03', '4 / 3', 480)\nch3 += clip('Lesson-03-clip-04', '4 / 3', 480)\n",
    "ch3 += clip('Lesson-03-clip-03', '4 / 3', 480, title='«Картина мира»', source='Михаэль Лайтман, 2010',\n"
    "            quote='«Вся картина мира, как в кино, — я могу её заказать своим внутренним изменением. […] Глядя на мир, ты видишь своё отношение к нему, к Творцу, к людям. Это отношение, это не сам мир».')\n"
    "ch3 += clip('Lesson-03-clip-04', '4 / 3', 480, title='«Купил машину — и увидел их повсюду»', source='Михаэль Лайтман, «Спроси каббалиста», 2009',\n"
    "            quote='«Ты настраиваешь себя на определённое ощущение. Художник, музыкант, преподаватель, шофёр — каждый видит мир в определённом ракурсе».')\n")
# глава 4: подпись
rep("ch4 += clip('Lesson-03-clip-05')\n",
    "ch4 += clip('Lesson-03-clip-05', title='«Жестокий человек не увидит милосердия»', source='Михаэль Лайтман, вебинар «Насколько реальна наша реальность?», 2016',\n"
    "            quote='«В нашем мире в силу этого закона мы не можем наблюдать те свойства, которых в нас нет. Я могу видеть только то, что есть во мне. Например, жестокий человек не увидит милосердия в других — необходимо подобие».')\n")
# глава 5: первый клип подписать, второй (K1) убрать вместе с подводкой
rep("ch5 += clip('Lesson-03-clip-06')\nch5 += p('Единственный способ видеть мир по-другому — сближение друг с другом.')\nch5 += clip('Lesson-03-clip-K1')\n",
    "ch5 += clip('Lesson-03-clip-06', title='«Экран — и только потом ощущение»', source='Михаэль Лайтман, вебинар «Видеть хорошее. Восприятие реальности через каббалу», 2016',\n"
    "            quote='«Для того чтобы почувствовать Высший мир, сначала на это желание надо сделать сокращение, затем экран, обратный свет и только потом приступить к получению. Только после этого мы начнём ощущать в себе Высший мир, потому что теперь между нами и Высшим миром образуется подобие свойств».')\n")
# резюме: пункт заменён по правке преподавателя
rep('<div class="kab-row"><div class="kab-dot"></div><div>Наши эгоцентрические желания определяют то, что мы видим. Органы чувств — агенты желания получать.</div></div>',
    '<div class="kab-row"><div class="kab-dot"></div><div>Наши эгоистические желания определяют то, что мы видим. Органы чувств в данном ракурсе — представители желания получать.</div></div>')

open(p, 'w', encoding='utf-8', newline='\n').write(s)
print('patched')
