# -*- coding: utf-8 -*-
"""Owner's corrections of 20.10.2026 to lessons 7–10 (doc «правки лекций», 10k4RXRMkcJ_69HfHRxa40fCY_eeBJf5OWOBSlrE3VVI)
plus the same «О чём этот урок» duplicate heading removed in lessons 6, 11, 12.

The generators build_lN.py are already patched and their html/ regenerated. This script runs them again (no --sql),
takes the changed pages and writes targeted, guarded UPDATEs: every row is updated only if its current content still
equals what the generator produced before (md5 of the file in git HEAD), so anything edited in the UI since is left alone
and reported by the check queries. lessonN.sql must NOT be re-run: it inserts pages and book chapters.

Usage: python -I fix_20261020.py  -> fix_20261020.sql (backup + guarded updates + checks)
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

# (lesson, page key) -> changed page html; 'resume' -> mdl_page; 'title:ch2' -> title only
CHANGED = {6: ['intro'], 7: ['intro', 'ch2', 'ch3'], 8: ['toc', 'intro', 'ch1', 'resume'], 9: ['toc', 'intro', 'title:ch2'],
           10: ['toc', 'intro', 'ch2', 'resume'], 11: ['intro'], 12: ['intro']}
QUIZ_FIX = {8: ['У8-05', 'У8-06'], 10: ['У10-06']}   # question names whose text/answers/feedback changed


def hx(s):
    return '0x' + binascii.hexlify(s.encode('utf-8')).decode()


def q(s):
    return "'" + s.replace(chr(92), chr(92) * 2).replace("'", "''") + "'"


def old_md5(rel):
    """md5 of the file as committed in git HEAD (what the generator wrote before today's patch)."""
    data = subprocess.run(['git', 'show', 'HEAD:' + rel.replace(os.sep, '/')], cwd=GIT, capture_output=True, check=True).stdout
    return hashlib.md5(data).hexdigest()


NOW = int(time.time())
bk_pages, bk_page, bk_q = [], [], []
sql, checks = ['SET NAMES utf8mb4;'], []
for n, keys in CHANGED.items():
    g = runpy.run_path(os.path.join(ROOT, 'lesson%d' % n, 'build_l%d.py' % n), run_name='build')
    ids, pages, lesson = g['PAGE_IDS'], g['PAGES'], g['LESSON']
    titles = {'toc': 'Содержание', 'intro': g['INTRO']}
    titles.update(('ch%d' % i, t) for i, t in enumerate(g['CHAPTERS'], 1))
    for key in keys:
        if key == 'resume':
            pid, rel = g['PAGE_RESUME'], '.claude/course236/lesson%d/html/page_resume.html' % n
            new, old = g['resume'], old_md5(rel)
            assert hashlib.md5(new.encode('utf-8')).hexdigest() != old, (n, key)
            bk_page.append(pid)
            sql.append("UPDATE mdl_page SET content=%s, timemodified=%d WHERE id=%d AND MD5(content)='%s';" % (hx(new), NOW, pid, old))
            checks.append("SELECT 'L%d resume' what, id, MD5(content)='%s' ok FROM mdl_page WHERE id=%d;" % (n, hashlib.md5(new.encode('utf-8')).hexdigest(), pid))
        elif key.startswith('title:'):
            k = key[6:]; pid = ids[k]
            old_title = {'ch2': 'Глава 2. Одно действие — два поступка'}[k]
            bk_pages.append(pid)
            sql.append("UPDATE mdl_lesson_pages SET title=%s, timemodified=%d WHERE id=%d AND lessonid=%d AND title=%s;" % (q(titles[k]), NOW, pid, lesson, q(old_title)))
            checks.append("SELECT 'L%d %s title' what, id, title=%s ok FROM mdl_lesson_pages WHERE id=%d;" % (n, k, q(titles[k]), pid))
        else:
            pid, rel = ids[key], '.claude/course236/lesson%d/html/p_%s.html' % (n, key)
            new, old = pages['p_' + key], old_md5(rel)
            assert hashlib.md5(new.encode('utf-8')).hexdigest() != old, (n, key)
            bk_pages.append(pid)
            sql.append("UPDATE mdl_lesson_pages SET title=%s, contents=%s, timemodified=%d WHERE id=%d AND lessonid=%d AND MD5(contents)='%s';"
                       % (q(titles[key]), hx(new), NOW, pid, lesson, old))
            checks.append("SELECT 'L%d %s' what, id, MD5(contents)='%s' ok, title FROM mdl_lesson_pages WHERE id=%d;" % (n, key, hashlib.md5(new.encode('utf-8')).hexdigest(), pid))
    for name in QUIZ_FIX.get(n, []):
        (qname, text, answers, single), (qids, aidss) = next(x for x in zip(g['QUESTIONS'], g['QIDS']) if x[0][0].startswith(name))
        for qid, aids in zip(qids, aidss):
            bk_q.append(qid)
            sql.append("UPDATE mdl_question SET name=%s, questiontext=%s, timemodified=%d WHERE id=%d;" % (q(qname), hx('<p>%s</p>\n' % text), NOW, qid))
            for (atext, frac, fb), aid in zip(answers, aids):
                sql.append("UPDATE mdl_question_answers SET answer=%s, feedback=%s WHERE id=%d AND question=%d;" % (hx('<p>%s</p>\n' % atext), hx('<p>%s</p>\n' % fb), aid, qid))
            checks.append("SELECT 'L%d %s' what, q.id, q.name, GROUP_CONCAT(LEFT(a.answer,40) ORDER BY a.id SEPARATOR ' | ') answers FROM mdl_question q JOIN mdl_question_answers a ON a.question=q.id WHERE q.id=%d;" % (n, name, qid))
    sql.append("UPDATE mdl_lesson SET timemodified=%d WHERE id=%d;" % (NOW, lesson))
sql.append("UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;")
backup = ["CREATE TABLE _kab_bk20261020_fix_pages AS SELECT * FROM mdl_lesson_pages WHERE id IN (%s);" % ','.join(map(str, bk_pages)),
          "CREATE TABLE _kab_bk20261020_fix_page AS SELECT * FROM mdl_page WHERE id IN (%s);" % ','.join(map(str, bk_page)),
          "CREATE TABLE _kab_bk20261020_fix_question AS SELECT * FROM mdl_question WHERE id IN (%s);" % ','.join(map(str, bk_q)),
          "CREATE TABLE _kab_bk20261020_fix_qanswers AS SELECT * FROM mdl_question_answers WHERE question IN (%s);" % ','.join(map(str, bk_q))]
out = os.path.join(HERE, 'fix_20261020.sql')
open(out, 'w', encoding='utf-8', newline='\n').write('\n'.join(backup + ['START TRANSACTION;'] + sql + ['COMMIT;'] + checks) + '\n')
print('pages', bk_pages, 'page', bk_page, 'questions', bk_q, '\nstatements', len(sql), 'bytes', os.path.getsize(out))
