# -*- coding: utf-8 -*-
"""Лекция 1 (lesson 1960): заменить iframe-плееры Google Drive на <video> с files.kabbalahmedia.info/kabacademy/MAK/.

Правило владельца (27.09.2026): видео в уроки ставить только с https://files.kabbalahmedia.info/kabacademy/MAK/.

Замена делается в самой БД через REPLACE() по точной строке iframe (hex-литералы), поэтому
параллельные правки других частей страницы (схемы, тексты) не затираются. Локально по свежему
дампу считается ожидаемый результат и его MD5 — для сверки после применения.

Usage: python swap_to_filer.py <dump_dir with page_<id>.html> <out_dir>
"""
import sys, os, hashlib

src, out = sys.argv[1], sys.argv[2]
BASE = 'https://files.kabbalahmedia.info/kabacademy/MAK/'

# clip: (Drive file id, page id, CSS aspect-ratio of the video)
CLIPS = {
    '01': ('1q922lD3BntPr9WW1JimeWKv9j10QoSeI', 17881, '16 / 9'),   # 2560x1440
    '02': ('1v62fJMZl-jHvvNdrtzjTCMtSjGJSH4f4', 17879, '4 / 3'),    # 1536x1152
    '03': ('1tU5Rd2eup5-y_S9_ifi4UzLCLhzwXy7H', 17880, '26 / 15'),  # 1248x720
    '04': ('1vMvXl2HgunmGpNgRUhYNDdQahL3xIM1s', 17881, '4 / 3'),    # 1280x960
    '05': ('15RTwRlfHhLB5tJBbo2pxPYdcT66gPAid', 17879, '4 / 3'),    # 1280x960
    '06': ('1IcClZUW-Ns0T38wcJJ8hgdnsw0eehxCK', 17879, '13 / 10'),  # 1248x960
}

def iframe(drive_id):
    return ('<iframe src="https://drive.google.com/file/d/%s/preview" width="100%%" height="480" '
            'allow="autoplay"></iframe>' % drive_id)

def video(num, ratio):
    name = 'Lesson-01-clip-01-%s' % num
    # nomediaplugin: native player, Moodle media filter does not rewrite the tag (как в гостиных)
    return ('<video class="nomediaplugin" controls="controls" controlslist="nodownload" preload="metadata" '
            'playsinline="playsinline" oncontextmenu="return false;" poster="%s%s.jpg" '
            'style="display: block; width: 100%%; aspect-ratio: %s; background: #000;">'
            '<source src="%s%s.mp4" type="video/mp4"></video>' % (BASE, name, ratio, BASE, name))

def hexlit(s):
    return "CONVERT(X'%s' USING utf8mb4)" % s.encode('utf-8').hex()

pages = {}
for pid in sorted({c[1] for c in CLIPS.values()}):
    pages[pid] = open(os.path.join(src, 'page_%d.html' % pid), 'rb').read().decode('utf-8')

sql = ['-- Лекция 1 (lesson 1960, cmid 13413, курс 238): Drive iframe -> <video> с files.kabbalahmedia.info/kabacademy/MAK/',
       'CREATE TABLE IF NOT EXISTS _kab_bk20260927_l1960_pages AS SELECT * FROM mdl_lesson_pages WHERE lessonid=1960;']
for num, (drive_id, pid, ratio) in sorted(CLIPS.items()):
    old, new = iframe(drive_id), video(num, ratio)
    n = pages[pid].count(old)
    if n != 1:
        raise SystemExit('clip %s: iframe found %d times on page %d' % (num, n, pid))
    pages[pid] = pages[pid].replace(old, new)
    sql.append('UPDATE mdl_lesson_pages SET contents = REPLACE(contents, %s, %s), timemodified = UNIX_TIMESTAMP() '
               'WHERE id=%d AND lessonid=1960 AND LOCATE(%s, contents) > 0;' % (hexlit(old), hexlit(new), pid, hexlit(old)))

os.makedirs(out, exist_ok=True)
checks = []
for pid, html in pages.items():
    data = html.encode('utf-8')
    open(os.path.join(out, 'page_%d.html' % pid), 'wb').write(data)
    md5 = hashlib.md5(data).hexdigest()
    checks.append((pid, len(data), md5))
    left = html.count('drive.google.com/file/d/')
    print(pid, 'bytes', len(data), 'md5', md5, 'video', html.count('<video'), 'drive left', left)
    if left:
        raise SystemExit('page %d: Drive links left' % pid)

sql.append("SELECT id, LENGTH(contents) len, MD5(contents) md5, "
           "(LENGTH(contents)-LENGTH(REPLACE(contents,'drive.google.com/file/d/','')))/24 n_drive, "
           "(LENGTH(contents)-LENGTH(REPLACE(contents,'<video','')))/6 n_video "
           "FROM mdl_lesson_pages WHERE lessonid=1960 ORDER BY id;")
sql.append('-- ожидаемо: ' + '; '.join('%d len=%d md5=%s' % c for c in checks))
open(os.path.join(out, 'upd_lesson1_filer.sql'), 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
print('sql ->', os.path.join(out, 'upd_lesson1_filer.sql'))
