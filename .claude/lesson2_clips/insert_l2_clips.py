# -*- coding: utf-8 -*-
"""Урок 2 курса 238 (книга mod_book 962, cmid 13389): вставить клипы с files.kabbalahmedia.info/kabacademy/MAK/.

Каждый клип вставляется блоком <div class="kab-clip"><video …></video></div> (без подписи — как в лекции 1)
сразу после абзаца-якоря. Вставка делается в БД через REPLACE(content, якорь, якорь + блок) по точной строке
(hex-литералы), поэтому параллельные правки других мест главы не затираются. Перевод строки блока берётся
как у главы (главы 2256/2255 сохранены редактором — CRLF, 2258/2257 — LF). Ожидаемый результат и MD5 — по свежему дампу.

Usage: python insert_l2_clips.py <dump_dir with page_<chapterid>.html> <out_dir> <clip> [clip ...]
"""
import sys, os, hashlib

src, out, wanted = sys.argv[1], sys.argv[2], sys.argv[3:]
BASE = 'https://files.kabbalahmedia.info/kabacademy/MAK/'

# clip: (chapter id, anchor = конец абзаца, после которого встаёт видео, CSS aspect-ratio)
CLIPS = {
    # Глава 1 «Как устроено наполнение»: итог главы — промежуток, двойное опустошение, мальчик с монеткой, качество
    '03': (2257, 'Отсюда знакомая плоскость после достижения, которую обычно списывают на усталость.</p>', '16 / 9'),
    # Глава 2 «Кто управляет желаниями»: лестница на доске, «все эти желания я получаю от окружающих»
    '04': (2256, '<strong>перенято у тех, кто вокруг.</strong></p>', '51 / 40'),
    # Глава 2: мальчик с монеткой — «потеря своего ощущается больше, чем приобретение чужого»
    '02': (2256, 'И сравнение с другими весит больше, чем собственное положение.</p>', '16 / 9'),
    # Глава 3 «Желание нейтрально»: точка в сердце есть у всех, просто глубоко запрятана
    '05': (2255, 'Пока спит, человек живёт по лестнице и вопроса не задаёт.</p>', '16 / 9'),
    # Вводная «О чём этот урок…»: та же лестница у человечества и вопрос о смысле жизни (фрагмент фильма «Просто о каббале»)
    '06': (2258, 'всё это одно растущее желание знать.</p>', '79 / 47'),
}

def block(num, ratio, eol):
    name = 'Lesson-02-clip-%s' % num
    return ('<div class="kab-clip" style="border-left: 3px solid #4a90a4; background: #f2f8fa; padding: 12px 16px; margin: 20px 0;">' + eol +
            '<video class="nomediaplugin" controls="controls" controlslist="nodownload" preload="metadata" '
            'playsinline="playsinline" oncontextmenu="return false;" poster="%s%s.jpg" '
            'style="display: block; width: 100%%; aspect-ratio: %s; background: #000;">'
            '<source src="%s%s.mp4" type="video/mp4"></video>' % (BASE, name, ratio, BASE, name) + eol +
            '</div>' + eol)

def hexlit(s):
    return "CONVERT(X'%s' USING utf8mb4)" % s.encode('utf-8').hex()

chapters, touched = {}, []
sql = ['-- Урок 2 (book 962, cmid 13389, курс 238): клипы %s с files.kabbalahmedia.info/kabacademy/MAK/' % ', '.join(wanted),
       'CREATE TABLE IF NOT EXISTS _kab_bk20260927_book962_clips AS SELECT * FROM mdl_book_chapters WHERE bookid=962;']
for num in wanted:
    cid, anchor_text, ratio = CLIPS[num]
    if cid not in chapters:
        chapters[cid] = open(os.path.join(src, 'page_%d.html' % cid), 'rb').read().decode('utf-8')
    html = chapters[cid]
    eol = '\r\n' if '\r\n' in html else '\n'
    anchor = anchor_text + eol
    if html.count(anchor) != 1:
        raise SystemExit('clip %s: anchor found %d times in chapter %d' % (num, html.count(anchor), cid))
    if 'Lesson-02-clip-%s.mp4' % num in html:
        raise SystemExit('clip %s: already in chapter %d' % (num, cid))
    new = anchor + block(num, ratio, eol)
    chapters[cid] = html.replace(anchor, new)
    sql.append('UPDATE mdl_book_chapters SET content = REPLACE(content, %s, %s), timemodified = UNIX_TIMESTAMP() '
               'WHERE id=%d AND bookid=962 AND LOCATE(%s, content) > 0;' % (hexlit(anchor), hexlit(new), cid, hexlit(anchor)))
    touched.append(cid)

os.makedirs(out, exist_ok=True)
checks = []
for cid in sorted(set(touched)):
    data = chapters[cid].encode('utf-8')
    open(os.path.join(out, 'chapter_%d.html' % cid), 'wb').write(data)
    md5 = hashlib.md5(data).hexdigest()
    checks.append((cid, len(data), md5))
    print(cid, 'bytes', len(data), 'md5', md5, 'video', chapters[cid].count('<video'))
ids = ','.join(str(c) for c in sorted(set(touched)))
sql.append("SELECT id, LENGTH(content) len, MD5(content) md5, (LENGTH(content)-LENGTH(REPLACE(content,'<video','')))/6 n_video "
           "FROM mdl_book_chapters WHERE id IN (%s) ORDER BY pagenum;" % ids)
sql.append('-- ожидаемо: ' + '; '.join('%d len=%d md5=%s' % c for c in checks))
name = 'insert_l2_clips_%s.sql' % '_'.join(wanted)
open(os.path.join(out, name), 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
print('sql ->', os.path.join(out, name))
