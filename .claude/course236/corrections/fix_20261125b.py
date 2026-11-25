# -*- coding: utf-8 -*-
"""Lessons 4–8 brought to the docs «Урок N — видеоматериалы» (25.11.2026, owner: «Да, приводи»), as lesson 9 the same day:
every clip marked green in the doc stands where the doc puts it; what the doc replaced is removed.
  L4: + 07, 08, 09 (ch.1), 10, 12 (ch.2), 05, 14 (ch.3); 11 — new book chapter «О второй стадии — бина» (doc: «в дополнительное»)
  L5: + 06, 07 (ch.2), 08, 09 (ch.3), 10, 11, 12, 13, 13-1, 14 (ch.4); − K3 «Один, Единый, Единственный» (YouTube, not checked in the doc)
  L6: + 01b (ch.1), 06, 02, 07 (ch.2), 08 (ch.3), 03 (short version), 11, 10 (ch.4)
  L7: + 06, 02, 07, 04A (ch.2), 03, 08, 04B (ch.3)
  L8: + 01, 06 (ch.1), 07, 08, 09, 03, 04 (ch.2), 10, 05, 11 (ch.3); − K1 «Рождённый свободным» (replaced in the doc by 01),
      − K3–K5 «Три фактора» (YouTube, replaced by 07, 08, 09, 03, 04)
Generators are patched and html/ regenerated (L5–L8 in this branch, L4 in claude/lesson-4-patterns-generators-85d859 — L4_ROOT).
Guarded UPDATEs: a page changes only if it still equals what the generator produced before (md5 of the file at the pinned
commit). The new book chapter is inserted only if book 970 still has exactly two chapters. lessonN.sql must NOT be re-run.

Usage: L4_ROOT=C:/t/edu-l4 python -I fix_20261125b.py  -> fix_20261125b.sql (all) and fix_20261125b_0.sql (backups) +
fix_20261125b_L4…L8.sql (one transaction per lesson): the helper on the server takes the SQL as one argv string, and a single
argument is limited to 128 KB (MAX_ARG_STRLEN), so the 265 KB script is applied in parts, backups first.
"""
import binascii
import hashlib
import os
import re
import subprocess
import sys
import time

HERE = os.path.dirname(os.path.abspath(__file__))
GIT = os.path.normpath(os.path.join(HERE, '..', '..', '..'))
L4_ROOT = os.environ.get('L4_ROOT', GIT)
sys.stdout.reconfigure(encoding='utf-8')

OLD = {4: (L4_ROOT, 'fc25025'), 5: (GIT, '8630b9c'), 6: (GIT, '8630b9c'), 7: (GIT, '8630b9c'), 8: (GIT, '8630b9c')}
CHANGED = {4: ['ch1', 'ch2', 'ch3'], 5: ['ch2', 'ch3', 'ch4'], 6: ['ch1', 'ch2', 'ch3', 'ch4'], 7: ['ch2', 'ch3'], 8: ['ch1', 'ch2', 'ch3']}
BOOK4, BOOK4_CH = 970, 3


def md5(b):
    return hashlib.md5(b).hexdigest()


def gen_ids(n):
    root = OLD[n][0]
    src = open(os.path.join(root, '.claude/course236/lesson%d/build_l%d.py' % (n, n)), encoding='utf-8').read()
    ids = dict(re.findall(r"'(\w+)': (\d+)", re.search(r'PAGE_IDS = \{([^}]*)\}', src).group(1)))
    lesson = int(re.search(r'^LESSON(?:, [A-Z_]+)* = (\d+)', src, re.M).group(1))
    ids = {k: int(v) for k, v in ids.items()}
    ids.update(EXTRA_IDS.get(n, {}))
    return ids, lesson


# chapters added after the frame was built (INSERT in lessonN.sql), not in PAGE_IDS: L6 ch.4 «Цель кругооборотов душ»
EXTRA_IDS = {6: {'ch4': 17978}}


NOW = int(time.time())
upd, checks, bk = [], [], []
parts = {}
for n, keys in CHANGED.items():
    root, commit = OLD[n]
    ids, lesson = gen_ids(n)
    for key in keys:
        rel = '.claude/course236/lesson%d/html/p_%s.html' % (n, key)
        old = subprocess.run(['git', 'show', '%s:%s' % (commit, rel)], cwd=root, capture_output=True, check=True).stdout
        new = open(os.path.join(root, rel), 'rb').read().replace(b'\r\n', b'\n')
        assert new != old, rel
        pid = ids[key]
        bk.append(pid)
        upd.append("UPDATE mdl_lesson_pages SET contents=0x%s, timemodified=%d WHERE id=%d AND lessonid=%d AND MD5(contents)='%s';"
                   % (binascii.hexlify(new).decode(), NOW, pid, lesson, md5(old)))
        checks.append("SELECT 'L%d %s' t, id, title, MD5(contents)='%s' ok, (LENGTH(contents)-LENGTH(REPLACE(contents,'<video','')))/6 videos FROM mdl_lesson_pages WHERE id=%d;"
                      % (n, key, md5(new), pid))
    upd.append('UPDATE mdl_lesson SET timemodified=%d WHERE id=%d;' % (NOW, lesson))
    parts[n] = (len(upd), len(checks))

# lesson 4 book: new chapter 3 with clip 11
src4 = open(os.path.join(L4_ROOT, '.claude/course236/lesson4/build_l4.py'), encoding='utf-8').read()
title4 = re.search(r"^BOOK_T3 = '([^']*)'", src4, re.M).group(1)
html4 = open(os.path.join(L4_ROOT, '.claude/course236/lesson4/html/book_ch3.html'), 'rb').read().replace(b'\r\n', b'\n')
q = lambda s: "'" + s.replace(chr(92), chr(92) * 2).replace("'", "''") + "'"
upd.append("INSERT INTO mdl_book_chapters (bookid,pagenum,subchapter,title,content,contentformat,hidden,timecreated,timemodified,importsrc) "
           "SELECT %d,%d,0,%s,0x%s,1,0,%d,%d,'' FROM DUAL WHERE (SELECT COUNT(*) FROM mdl_book_chapters WHERE bookid=%d)=2;"
           % (BOOK4, BOOK4_CH, q(title4), binascii.hexlify(html4).decode(), NOW, NOW, BOOK4))
upd.append('UPDATE mdl_book SET revision=revision+1, timemodified=%d WHERE id=%d;' % (NOW, BOOK4))
checks.append("SELECT id, pagenum, title, MD5(content)='%s' ok FROM mdl_book_chapters WHERE bookid=%d ORDER BY pagenum;" % (md5(html4), BOOK4))

sql = ['SET NAMES utf8mb4;',
       'CREATE TABLE _kab_bk20261125_l4_8_pages AS SELECT * FROM mdl_lesson_pages WHERE id IN (%s);' % ','.join(map(str, bk)),
       'CREATE TABLE _kab_bk20261125_l4_book AS SELECT * FROM mdl_book_chapters WHERE bookid=%d;' % BOOK4,
       'START TRANSACTION;'] + upd + ['UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;', 'COMMIT;'] + checks
out = os.path.join(HERE, 'fix_20261125b.sql')
open(out, 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
print('pages', bk, 'statements', len(sql), 'bytes', os.path.getsize(out))

# the same statements split for the 128 KB argv limit of the server helper
NL = chr(10)


def write(name, lines):
    f = os.path.join(HERE, name)
    open(f, 'w', encoding='utf-8', newline=NL).write(NL.join(lines) + NL)
    assert os.path.getsize(f) < 120000, f
    print(name, os.path.getsize(f))


write('fix_20261125b_0.sql', sql[:3])          # SET NAMES + both backup tables
prev_u, prev_c = 0, 0
for n in sorted(parts):
    u, c = parts[n]
    body, chk = upd[prev_u:u], checks[prev_c:c]
    if n == 4:                                 # the book INSERT and revision bump are the last two statements of upd
        body, chk = body + upd[-2:], chk + checks[-1:]
    if n == max(parts):
        body = body + ['UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;']
    write('fix_20261125b_L%d.sql' % n, ['SET NAMES utf8mb4;', 'START TRANSACTION;'] + body + ['COMMIT;'] + chk)
    prev_u, prev_c = u, c
