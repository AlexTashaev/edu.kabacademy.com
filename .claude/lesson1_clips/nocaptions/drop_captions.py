# -*- coding: utf-8 -*-
"""Лекция 1 (lesson 1960): убрать короткие описания над видео (просьба владельца 27.09.2026).

В каждом блоке <div class="kab-clip"> удаляется абзац-подпись <p style="margin: 0 0 10px;"><strong>🎬 Клип…</strong> …</p>
вместе с переводом строки перед <video>. Удаление делается в БД через REPLACE() по точной строке абзаца
(hex-литералы), остальной контент страниц не трогается. Ожидаемый результат и MD5 считаются по свежему дампу.

Usage: python drop_captions.py <dump_dir with page_<id>.html> <out_dir>
"""
import sys, os, re, hashlib

src, out = sys.argv[1], sys.argv[2]
EXPECT = {17879: 3, 17880: 1, 17881: 2}
CAPTION = re.compile(r'<p style="margin: 0 0 10px;"><strong>🎬 Клип[^<]*</strong>[^<]*</p>\r?\n(?=<video\b)')

def hexlit(s):
    return "CONVERT(X'%s' USING utf8mb4)" % s.encode('utf-8').hex()

sql = ['-- Лекция 1 (lesson 1960, cmid 13413, курс 238): убрать короткие описания над видео',
       'CREATE TABLE IF NOT EXISTS _kab_bk20260927b_l1960_pages AS SELECT * FROM mdl_lesson_pages WHERE lessonid=1960;']
checks = []
os.makedirs(out, exist_ok=True)
for pid, n_expect in EXPECT.items():
    html = open(os.path.join(src, 'page_%d.html' % pid), 'rb').read().decode('utf-8')
    caps = CAPTION.findall(html)
    if len(caps) != n_expect:
        raise SystemExit('page %d: found %d captions, expected %d' % (pid, len(caps), n_expect))
    for cap in caps:
        if html.count(cap) != 1:
            raise SystemExit('page %d: caption not unique' % pid)
        html = html.replace(cap, '')
        sql.append("UPDATE mdl_lesson_pages SET contents = REPLACE(contents, %s, ''), timemodified = UNIX_TIMESTAMP() "
                   "WHERE id=%d AND lessonid=1960 AND LOCATE(%s, contents) > 0;" % (hexlit(cap), pid, hexlit(cap)))
    if '🎬' in html or 'margin: 0 0 10px' in html:
        raise SystemExit('page %d: caption leftovers' % pid)
    data = html.encode('utf-8')
    open(os.path.join(out, 'page_%d.html' % pid), 'wb').write(data)
    md5 = hashlib.md5(data).hexdigest()
    checks.append((pid, len(data), md5))
    print(pid, 'removed', len(caps), 'bytes', len(data), 'md5', md5, 'video', html.count('<video'), 'kab-clip', html.count('kab-clip'))

sql.append("SELECT id, LENGTH(contents) len, MD5(contents) md5, "
           "(LENGTH(contents)-LENGTH(REPLACE(contents,'<video','')))/6 n_video "
           "FROM mdl_lesson_pages WHERE lessonid=1960 ORDER BY id;")
sql.append('-- ожидаемо: ' + '; '.join('%d len=%d md5=%s' % c for c in checks))
open(os.path.join(out, 'upd_lesson1_nocaptions.sql'), 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
print('sql ->', os.path.join(out, 'upd_lesson1_nocaptions.sql'))
