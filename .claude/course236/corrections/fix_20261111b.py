# -*- coding: utf-8 -*-
"""Owner's request of 11.11.2026, part 2: titles above the videos in the other books of course 236 where a chapter page
showed a video with no title (lessons 3, 4, 5, 8, 15) — same as fix_20261111.py did for the lesson-7 book.

The generators are already patched and their html/ regenerated (lesson 3: build_l3.py writes to a temp dir that no longer
exists, so its html/book_ch2.html got the same one-line insertion by hand). This script reads the regenerated chapter html
and writes guarded UPDATEs: a row is updated only if its content still equals what the generator produced before (md5 of
the file at the pinned commit). lessonN.sql must NOT be re-run: they delete and insert chapters.

Lesson 4 lives in its own branch (claude/lesson-4-patterns-generators-85d859): point L4_ROOT at a checkout of it.

Usage: L4_ROOT=C:/t/edu-l4 python -I fix_20261111b.py  -> fix_20261111b.sql (backup + guarded updates + checks)
"""
import binascii
import hashlib
import os
import subprocess
import sys
import time

HERE = os.path.dirname(os.path.abspath(__file__))
GIT = os.path.normpath(os.path.join(HERE, '..', '..', '..'))
L4_ROOT = os.environ.get('L4_ROOT', GIT)
sys.stdout.reconfigure(encoding='utf-8')

OLD, OLD_L4 = '650ace7', '52943de'   # commits whose html/ equals prod before this fix (pyatyy branch, lesson-4 branch)
# (chapter id, book id, lesson dir, file, root of the checkout with the new html, commit with the old html)
CHAPTERS = [(2270, 968, 'lesson3', 'book_ch2', GIT, OLD),
            (2284, 970, 'lesson4', 'book_ch2', L4_ROOT, OLD_L4),
            (2290, 971, 'lesson5', 'book_ch2', GIT, OLD),
            (2291, 971, 'lesson5', 'book_ch3', GIT, OLD),
            (2308, 974, 'lesson8', 'book_ch2', GIT, OLD),
            (2309, 974, 'lesson8', 'book_ch3', GIT, OLD),
            (2348, 981, 'lesson15', 'book_ch2', GIT, OLD)]


def md5(b):
    return hashlib.md5(b).hexdigest()


NOW = int(time.time())
ids = ','.join(str(c[0]) for c in CHAPTERS)
sql = ['SET NAMES utf8mb4;',
       'CREATE TABLE _kab_bk20261111_books_chapters AS SELECT * FROM mdl_book_chapters WHERE id IN (%s);' % ids,
       'START TRANSACTION;']
checks = []
for cid, book, lesson, name, root, commit in CHAPTERS:
    rel = '.claude/course236/%s/html/%s.html' % (lesson, name)
    old = subprocess.run(['git', 'show', '%s:%s' % (commit, rel)], cwd=GIT, capture_output=True, check=True).stdout
    new = open(os.path.join(root, rel), 'rb').read().replace(b'\r\n', b'\n')   # autocrlf checkouts
    assert new != old and new.count(b'<div class="kab-kicker">') == 1 and b'kab-kicker' not in old, rel
    sql.append("UPDATE mdl_book_chapters SET content=0x%s, timemodified=%d WHERE id=%d AND bookid=%d AND MD5(content)='%s';"
               % (binascii.hexlify(new).decode(), NOW, cid, book, md5(old)))
    checks.append("SELECT id, bookid, title, MD5(content)='%s' ok, LENGTH(content) len FROM mdl_book_chapters WHERE id=%d;" % (md5(new), cid))
for book in sorted(set(c[1] for c in CHAPTERS)):
    sql.append('UPDATE mdl_book SET revision=revision+1, timemodified=%d WHERE id=%d;' % (NOW, book))
sql.append('COMMIT;')
out = os.path.join(HERE, 'fix_20261111b.sql')
open(out, 'w', encoding='utf-8', newline='\n').write('\n'.join(sql + checks) + '\n')
print('chapters', ids, 'statements', len(sql), 'bytes', os.path.getsize(out))
