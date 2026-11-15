# -*- coding: utf-8 -*-
"""Owner's request of 15.11.2026: check the videos of lessons 4–9 against the docs «Урок N — видеоматериалы» (ready clips are
marked green there, files in the Drive folders «Видео») and replace where needed. Owner's decision: replacements only.

Only lesson 9 had clips with a ready counterpart that differed from what stood on the site:
  lecture, ch.2 (page 17937): Lesson-09-clip-K1 -> Lesson-09-clip-07, Lesson-09-clip-K2 -> Lesson-09-clip-03
  lecture, ch.4 (page 17980): Lesson-09-clip-K4 -> Lesson-09-clip-08
  book 975, ch.2 (chapter 2314): archive players «фрагмент с 24-й / 25-й минуты» -> Lesson-09-clip-09, Lesson-09-clip-10
The clips and their posters are on the filer; build_l9.py is patched and html/ regenerated. This script writes guarded
UPDATEs: a row changes only if it still equals what the generator produced before (md5 of the file at the pinned commit).
lesson9.sql must NOT be re-run.

Usage: python -I fix_20261115.py  -> fix_20261115.sql (backup + guarded updates + checks)
"""
import binascii
import hashlib
import os
import subprocess
import sys
import time

HERE = os.path.dirname(os.path.abspath(__file__))
GIT = os.path.normpath(os.path.join(HERE, '..', '..', '..'))
sys.stdout.reconfigure(encoding='utf-8')

OLD = '8043196'   # commit whose lesson9/html equals prod before this fix
LESSON, BOOK = 1970, 975
# (table, content column, id, parent column, parent id, generator file)
ROWS = [('mdl_lesson_pages', 'contents', 17937, 'lessonid', LESSON, 'p_ch2'),
        ('mdl_lesson_pages', 'contents', 17980, 'lessonid', LESSON, 'p_ch4'),
        ('mdl_book_chapters', 'content', 2314, 'bookid', BOOK, 'book_ch2')]


def md5(b):
    return hashlib.md5(b).hexdigest()


NOW = int(time.time())
sql = ['SET NAMES utf8mb4;',
       'CREATE TABLE _kab_bk20261115_l9_pages AS SELECT * FROM mdl_lesson_pages WHERE id IN (17937,17980);',
       'CREATE TABLE _kab_bk20261115_l9_chapter AS SELECT * FROM mdl_book_chapters WHERE id=2314;',
       'START TRANSACTION;']
checks = []
for table, col, rid, pcol, pid, name in ROWS:
    rel = '.claude/course236/lesson9/html/%s.html' % name
    old = subprocess.run(['git', 'show', '%s:%s' % (OLD, rel)], cwd=GIT, capture_output=True, check=True).stdout
    new = open(os.path.join(GIT, rel), 'rb').read().replace(b'\r\n', b'\n')   # autocrlf checkouts
    assert new != old and b'Lesson-09-clip-K1' not in new and b'Lesson-09-clip-K2' not in new and b'Lesson-09-clip-K4' not in new, rel
    sql.append("UPDATE %s SET %s=0x%s, timemodified=%d WHERE id=%d AND %s=%d AND MD5(%s)='%s';"
               % (table, col, binascii.hexlify(new).decode(), NOW, rid, pcol, pid, col, md5(old)))
    checks.append("SELECT '%s' t, id, title, MD5(%s)='%s' ok, LENGTH(%s) len FROM %s WHERE id=%d;" % (name, col, md5(new), col, table, rid))
sql += ['UPDATE mdl_lesson SET timemodified=%d WHERE id=%d;' % (NOW, LESSON),
        'UPDATE mdl_book SET revision=revision+1, timemodified=%d WHERE id=%d;' % (NOW, BOOK),
        'COMMIT;']
out = os.path.join(HERE, 'fix_20261115.sql')
open(out, 'w', encoding='utf-8', newline='\n').write('\n'.join(sql + checks) + '\n')
print('statements', len(sql), 'bytes', os.path.getsize(out))
