# -*- coding: utf-8 -*-
"""Owner, 11.11.2026: the caption «одна стрелка наружу» is still on scheme 21 (lesson 7, chapter 3, page 17926).

The caption was inside the designer's picture. lesson7/fix_scheme21.py removed it from L07-03.webp, make_static.py rebuilt
L07-03-static.png; both files go to /kab/img/schemes/ on the server. The page keeps the same markup, only the cache-busting
version of the two files changes (?v=1 -> ?v=2, build_l7.py). This script writes the guarded UPDATE of the page: it is
applied only if the page still equals what the generator produced before (md5 of the file at the pinned commit).

Usage: python -I fix_20261111c.py  -> fix_20261111c.sql (backup + guarded update + check)
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

OLD = '9adb16d'            # commit whose lesson7/html/p_ch3.html equals prod before this fix
PAGE, LESSON = 17926, 1968
REL = '.claude/course236/lesson7/html/p_ch3.html'

old = subprocess.run(['git', 'show', '%s:%s' % (OLD, REL)], cwd=GIT, capture_output=True, check=True).stdout
new = open(os.path.join(GIT, REL), 'rb').read().replace(b'\r\n', b'\n')   # autocrlf checkouts
assert new == old.replace(b'L07-03.webp?v=1', b'L07-03.webp?v=2').replace(b'L07-03-static.png?v=1', b'L07-03-static.png?v=2') != old
NOW = int(time.time())
md5 = lambda b: hashlib.md5(b).hexdigest()
sql = ['SET NAMES utf8mb4;',
       'CREATE TABLE _kab_bk20261111_l7_scheme21 AS SELECT * FROM mdl_lesson_pages WHERE id=%d;' % PAGE,
       'START TRANSACTION;',
       "UPDATE mdl_lesson_pages SET contents=0x%s, timemodified=%d WHERE id=%d AND lessonid=%d AND MD5(contents)='%s';"
       % (binascii.hexlify(new).decode(), NOW, PAGE, LESSON, md5(old)),
       'UPDATE mdl_lesson SET timemodified=%d WHERE id=%d;' % (NOW, LESSON),
       'COMMIT;',
       "SELECT id, title, MD5(contents)='%s' ok, contents LIKE '%%L07-03.webp?v=2%%' v2 FROM mdl_lesson_pages WHERE id=%d;" % (md5(new), PAGE)]
out = os.path.join(HERE, 'fix_20261111c.sql')
open(out, 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
print('page', PAGE, 'old md5', md5(old), 'bytes', os.path.getsize(out))
