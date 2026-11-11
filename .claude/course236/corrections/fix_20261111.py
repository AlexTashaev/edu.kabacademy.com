# -*- coding: utf-8 -*-
"""Owner's request of 11.11.2026: titles above the videos on pages 2 and 3 of the lesson-7 book (book 973, cm 13690).

build_l7.py is already patched (archive() prints the chapter title as a kicker) and html/ regenerated. This script runs it
again (no --sql) and writes guarded UPDATEs for the two chapters: a row is updated only if its content still equals what the
generator produced before (md5 of the file in git HEAD). lesson7.sql must NOT be re-run: it deletes and inserts chapters.

Usage: python -I fix_20261111.py  -> fix_20261111.sql (backup + guarded updates + checks). Run it BEFORE committing html/.
"""
import binascii
import hashlib
import os
import runpy
import subprocess
import sys
import time

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(HERE)
GIT = os.path.normpath(os.path.join(ROOT, '..', '..'))
sys.stdout.reconfigure(encoding='utf-8')

CHAPTERS = {'book_ch2': 2302, 'book_ch3': 2303}   # generator key -> mdl_book_chapters.id


def hx(s):
    return '0x' + binascii.hexlify(s.encode('utf-8')).decode()


def md5(b):
    return hashlib.md5(b).hexdigest()


g = runpy.run_path(os.path.join(ROOT, 'lesson7', 'build_l7.py'), run_name='build')
book, pages = g['BOOK_ID'], g['PAGES']
NOW = int(time.time())
ids = ','.join(str(i) for i in CHAPTERS.values())
sql = ['SET NAMES utf8mb4;',
       'CREATE TABLE _kab_bk20261111_book973_chapters AS SELECT * FROM mdl_book_chapters WHERE id IN (%s);' % ids,
       'START TRANSACTION;']
checks = []
for key, cid in CHAPTERS.items():
    rel = '.claude/course236/lesson7/html/%s.html' % key
    old = md5(subprocess.run(['git', 'show', 'HEAD:' + rel], cwd=GIT, capture_output=True, check=True).stdout)
    new = pages[key]
    assert md5(new.encode('utf-8')) != old, key
    sql.append("UPDATE mdl_book_chapters SET content=%s, timemodified=%d WHERE id=%d AND bookid=%d AND MD5(content)='%s';"
               % (hx(new), NOW, cid, book, old))
    checks.append("SELECT id, title, MD5(content)='%s' ok, LENGTH(content) len FROM mdl_book_chapters WHERE id=%d;"
                  % (md5(new.encode('utf-8')), cid))
sql.append('UPDATE mdl_book SET revision=revision+1, timemodified=%d WHERE id=%d;' % (NOW, book))
sql.append('COMMIT;')
out = os.path.join(HERE, 'fix_20261111.sql')
open(out, 'w', encoding='utf-8', newline='\n').write('\n'.join(sql + checks) + '\n')
print('chapters', ids, 'statements', len(sql), 'bytes', os.path.getsize(out))
