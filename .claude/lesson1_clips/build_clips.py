# -*- coding: utf-8 -*-
"""Build new lesson-page HTML (lesson 1960, pages 17879-17881) with Google Drive clip embeds
and the SQL to apply it. Usage: python build_clips.py <scratch_dir> <out_dir> [durations.json]

ВНИМАНИЕ (24.09.2026): после применения клипов страницы 17879/17880/17881 менялись ещё раз
(схемы вместо плашек «Схема 1–3», замена «Рав» → «Михаэль Лайтман»). Не пересобирать страницы
из снимков before/ — перед новой правкой снимать актуальный contents из БД и вставлять клипы в него.
Правило владельца для всех текстов курса, включая подписи к клипам: не «Рав», а «Михаэль Лайтман»."""
import sys, os, json, re

src, out = sys.argv[1], sys.argv[2]
dur = {}
if len(sys.argv) > 3 and os.path.exists(sys.argv[3]):
    dur = json.load(open(sys.argv[3], encoding='utf-8'))

# Drive file ids of the web/ versions (folder 1PRxK1cQTg_NSYUSl1fXLO-x8NuMFAvl-)
IDS = {
    '01': '1q922lD3BntPr9WW1JimeWKv9j10QoSeI',  # «Верхние полки библиотеки», 2017
    '02': '1v62fJMZl-jHvvNdrtzjTCMtSjGJSH4f4',  # «Портной режет ткань», 2007-12-30
    '03': '1tU5Rd2eup5-y_S9_ifi4UzLCLhzwXy7H',  # «Картина, вышитая крестиками», 2005
    '04': '1vMvXl2HgunmGpNgRUhYNDdQahL3xIM1s',  # «Никакие красные нитки…», 2004
    '05': '15RTwRlfHhLB5tJBbo2pxPYdcT66gPAid',  # «Каббала за 20 секунд», 2009
    '06': '1IcClZUW-Ns0T38wcJJ8hgdnsw0eehxCK',  # «Второе определение» (Михаэль Лайтман), 2007-12-09
}

def clip(num, caption):
    d = dur.get(num)
    head = '🎬 Клип, %s.' % d if d else '🎬 Клип.'
    return (
        '<div class="kab-clip" style="border-left: 3px solid #4a90a4; background: #f2f8fa; padding: 12px 16px; margin: 20px 0;">\n'
        '<p style="margin: 0 0 10px;"><strong>%s</strong> %s</p>\n' % (head, caption) +
        '<iframe src="https://drive.google.com/file/d/%s/preview" width="100%%" height="480" allow="autoplay"></iframe>\n' % IDS[num] +
        '</div>'
    )

CLIPS = {
    '05': clip('05', '«Каббала за 20 секунд». Блогер просит объяснить, что такое каббала, и даёт минуту. Михаэль Лайтман укладывается в двадцать секунд — короткое определение рядом с длинным.'),
    '02': clip('02', '«Портной режет ткань». Сын видит, как отец кромсает красивую материю, и по своему опыту уверен, что ничего не выйдет. «Так же и мы: наблюдаем только процесс — как нас режет и кромсает жизнь, — но не видим, что из этого получится».'),
    '06': clip('06', 'Второе определение. Если формулировка Бааль Сулама показалась тяжёлой — Михаэль Лайтман говорит об этом прямо: учёные втискивают в определение все свои знания, и потому, кроме них самих, никому ничего не понятно. И даёт своё: «наука каббала — это раскрытие наивысшей силы природы человеку, вследствие чего человек обретает ощущение вечности и совершенства».'),
    '03': clip('03', '«Картина, вышитая крестиками». С лицевой стороны — отдельные фигуры; связи между ними видны только с изнанки: «посмотрите сзади на ниточки, протянутые между всеми частями картины». И в том же фрагменте — как понять, ваша ли это наука: если почувствуете желание раскрыть высший мир, она для вас; если нет — знания всё равно помогут по-другому относиться к миру.'),
    '04': clip('04', '«И никаким иным методом ты не можешь что-либо сделать — только внутренним программированием. Никакие красные нитки, кресты, минареты — ничто из того, что человечество создало, — тебе не поможет».'),
    '01': clip('01', 'Михаэль Лайтман вспоминает домашнюю библиотеку: до верхних полок он в детстве не дотягивался. «Человеку надо доставать только до того уровня, который будет его правильно воспитывать… Наука каббала ждала не своего человека, а своего поколения, в котором она может раскрыться. Мы и являемся тем поколением».'),
}

def load(pid):
    # dump from `mysql -e` (batch mode): newline escaped as the two characters \n, CR kept raw
    raw = open(os.path.join(src, 'page_%s.orig.html' % pid), encoding='utf-8', newline='').read()
    if '\\\\' in raw or '\\t' in raw:
        raise SystemExit('page %s: unexpected escaped backslash/tab in dump' % pid)
    return raw.replace('\r', '').replace('\\n', '\n')

def must_replace(html, old, new, label):
    if html.count(old) != 1:
        raise SystemExit('%s: anchor found %d times' % (label, html.count(old)))
    return html.replace(old, new)

pages = {}

# ---------- Глава 1 (17879) ----------
h = load('17879')
# 1) убрать плашку-заглушку «Каббала за 20 секунд» (стоит после «Разберём по частям.»)
ph05 = re.search(r'<div style="border: 1px dashed #7fa8b8;[^\n]*\n<p style="margin: 0;"><strong>Клип\.</strong> «Каббала за 20 секунд»[^\n]*\n<!-- TODO: клип требует нарезки, подставить готовый --></div>\n', h)
if not ph05:
    raise SystemExit('ch1: placeholder 05 not found')
h = h.replace(ph05.group(0), '')
# 2) клип 05 сразу после определения Бааль Сулама (перед «Разберём по частям.»)
h = must_replace(h, '</blockquote>\n<p>Разберём по частям.</p>', '</blockquote>\n' + CLIPS['05'] + '\n<p>Разберём по частям.</p>', 'ch1 clip05')
# 3) клип 02 после абзаца «Связанным между собой и направленным на достижение одной цели»
h = must_replace(h, 'входит в общее движение.</p>\n', 'входит в общее движение.</p>\n' + CLIPS['02'] + '\n', 'ch1 clip02')
# 4) клип 06 после разбора определения, перед «Два слова, из которых состоит всё»
h = must_replace(h, 'проверить нельзя, значит и изучать нечего.</p>\n<h4>Два слова, из которых состоит всё</h4>', 'проверить нельзя, значит и изучать нечего.</p>\n' + CLIPS['06'] + '\n<h4>Два слова, из которых состоит всё</h4>', 'ch1 clip06')
pages['17879'] = h

# ---------- Глава 2 (17880) ----------
h = load('17880')
h = must_replace(h, 'Проверить это утверждение можно только самому и не сегодня.</p>\n<h4>Путь сверху вниз</h4>', 'Проверить это утверждение можно только самому и не сегодня.</p>\n' + CLIPS['03'] + '\n<h4>Путь сверху вниз</h4>', 'ch2 clip03')
pages['17880'] = h

# ---------- Глава 3 (17881) ----------
h = load('17881')
ph04 = re.search(r'<div style="border: 1px dashed #7fa8b8;[^\n]*\n<p style="margin: 0;"><strong>Клип\.</strong> «Никакие красные нитки[^\n]*\n<!-- TODO: клип требует нарезки, подставить готовый --></div>', h)
if not ph04:
    raise SystemExit('ch3: placeholder 04 not found')
h = h.replace(ph04.group(0), CLIPS['04'])
box01 = re.search(r'<div style="border-left: 3px solid #4a90a4;[^\n]*\n<p style="margin: 0;"><strong>🎬 Клип, 2 мин 41 с\.</strong>[^\n]*\n<p style="margin: \.5em 0 0;"><a href="https://kabbalahmedia\.info/ru/lessons/cu/iwaJaTX6"[^\n]*\n</div>', h)
if not box01:
    raise SystemExit('ch3: link box 01 not found')
h = h.replace(box01.group(0), CLIPS['01'])
pages['17881'] = h

os.makedirs(out, exist_ok=True)
sql = ['-- Лекция 1 (lesson 1960, cmid 13413, курс 238): вставка клипов из Google Drive (папка web/)',
       '-- Бэкап всех страниц урока перед правкой:',
       'CREATE TABLE IF NOT EXISTS _kab_bk20260924_l1960_pages AS SELECT * FROM mdl_lesson_pages WHERE lessonid=1960;']
for pid, html in pages.items():
    html_crlf = html.replace('\r', '').replace('\n', '\r\n')  # как сохраняет редактор Moodle
    open(os.path.join(out, 'page_%s.new.html' % pid), 'w', encoding='utf-8', newline='').write(html_crlf)
    hexs = html_crlf.encode('utf-8').hex()
    sql.append("UPDATE mdl_lesson_pages SET contents = CONVERT(X'%s' USING utf8mb4), timemodified = UNIX_TIMESTAMP() WHERE id=%s AND lessonid=1960;" % (hexs, pid))
sql.append("SELECT id, title, LENGTH(contents) len, (LENGTH(contents)-LENGTH(REPLACE(contents,'drive.google.com/file/d/','')))/24 n_clips, timemodified FROM mdl_lesson_pages WHERE lessonid=1960 ORDER BY id;")
open(os.path.join(out, 'upd_lesson1_clips.sql'), 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
for pid, html in pages.items():
    print(pid, 'len', len(html), 'clips', html.count('drive.google.com/file/d/'))
print('sql ->', os.path.join(out, 'upd_lesson1_clips.sql'))
