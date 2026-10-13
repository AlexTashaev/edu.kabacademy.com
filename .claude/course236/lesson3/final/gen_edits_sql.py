# -*- coding: utf-8 -*-
"""Targeted SQL for the teacher's edits: four lesson pages + résumé page (no inserts)."""
import os, time, binascii
os.chdir(r'C:\Users\tashaev\AppData\Local\Temp\kabsc2')
NOW = int(time.time())
def hx(s):
    return '0x' + binascii.hexlify(s.encode('utf-8')).decode()
def rd(fn):
    return open(fn, encoding='utf-8').read()
pages = {17900: 'l3f/p_ch1.html', 17901: 'l3f/p_ch3.html', 17902: 'l3f/p_ch4.html', 17976: 'l3f/p_ch5.html'}
sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
for pid, fn in pages.items():
    html = rd(fn)
    assert 'Lesson-03-clip-02' not in html and 'Lesson-03-clip-K1' not in html, fn
    sql.append("UPDATE mdl_lesson_pages SET contents=%s, timemodified=%d WHERE id=%d AND lessonid=1964;" % (hx(html), NOW, pid))
res = rd('l3f/page_resume.html')
assert 'эгоистические желания' in res
sql.append("UPDATE mdl_page SET content=%s, timemodified=%d WHERE id=2487;" % (hx(res), NOW))
sql += ['COMMIT;', "SELECT id,title,LENGTH(contents) clen,timemodified FROM mdl_lesson_pages WHERE id IN (17900,17901,17902,17976); SELECT id,LENGTH(content) FROM mdl_page WHERE id=2487;"]
open('lesson3_edits.sql', 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
print('ok', os.path.getsize('lesson3_edits.sql'))
