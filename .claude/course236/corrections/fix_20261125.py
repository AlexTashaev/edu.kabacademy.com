# -*- coding: utf-8 -*-
"""Check of lesson 9 against the doc «Урок 9 — видеоматериалы» after the editor (25.11.2026, sent by the owner):
  ch.1 (page 17939): ready clips 04 and 06 (green, «глава 1») were missing -> added after the quotes they voice;
  ch.3 (page 17938): clip K3 = fragment 01 «Свет хочет войти в меня насильно…», the doc says «не подходит» -> removed;
  ch.5 (page 17981): clip K5 = fragment 05 «А где же я?…», not marked ready in the doc -> removed, its quote stays as text.
build_l9.py is patched and html/ regenerated; the clips and posters are on the filer. This script writes guarded UPDATEs:
a row changes only if it still equals what the generator produced before (md5 of the file at the pinned commit).
lesson9.sql must NOT be re-run.

Usage: python -I fix_20261125.py  -> fix_20261125.sql (backup + guarded updates + checks)
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

OLD = '7019913'   # commit whose lesson9/html equals prod before this fix
LESSON = 1970
PAGES = [(17939, 'p_ch1'), (17938, 'p_ch3'), (17981, 'p_ch5')]


def md5(b):
    return hashlib.md5(b).hexdigest()


NOW = int(time.time())
sql = ['SET NAMES utf8mb4;',
       'CREATE TABLE _kab_bk20261125_l9_pages AS SELECT * FROM mdl_lesson_pages WHERE id IN (%s);' % ','.join(str(p) for p, _ in PAGES),
       'START TRANSACTION;']
checks = []
for pid, name in PAGES:
    rel = '.claude/course236/lesson9/html/%s.html' % name
    old = subprocess.run(['git', 'show', '%s:%s' % (OLD, rel)], cwd=GIT, capture_output=True, check=True).stdout
    new = open(os.path.join(GIT, rel), 'rb').read().replace(b'\r\n', b'\n')   # autocrlf checkouts
    assert new != old and b'clip-K3' not in new and b'clip-K5' not in new, rel
    sql.append("UPDATE mdl_lesson_pages SET contents=0x%s, timemodified=%d WHERE id=%d AND lessonid=%d AND MD5(contents)='%s';"
               % (binascii.hexlify(new).decode(), NOW, pid, LESSON, md5(old)))
    checks.append("SELECT '%s' t, id, title, MD5(contents)='%s' ok, LENGTH(contents) len FROM mdl_lesson_pages WHERE id=%d;" % (name, md5(new), pid))
sql += ['UPDATE mdl_lesson SET timemodified=%d WHERE id=%d;' % (NOW, LESSON), 'COMMIT;']
out = os.path.join(HERE, 'fix_20261125.sql')
open(out, 'w', encoding='utf-8', newline='\n').write('\n'.join(sql + checks) + '\n')
print('statements', len(sql), 'bytes', os.path.getsize(out))
