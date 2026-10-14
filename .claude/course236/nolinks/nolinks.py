# Remove links to kabbalahmedia.info from book chapters of course 236 (owner 14.10.2026: no archive links for students).
# Input: mysql batch output of "SELECT id, TO_BASE64(content)"; output: nolinks_apply.sql
import base64, binascii, re, sys, time
BS = chr(92)
rows = {}
for line in open(sys.argv[1], encoding='utf-8').read().splitlines()[1:]:
    cid, b = line.split('\t')
    rows[int(cid)] = base64.b64decode(b.replace(BS + 'n', '')).decode('utf-8')
LINK_P = re.compile(r'\n?<p><a class="kab-link" href="https://kabbalahmedia\.info/[^"]*"[^>]*>Открыть в архиве</a></p>')
HREF = re.compile(r'href="https?://(www\.|cdn\.)?kabbalahmedia\.info')
NOW = int(time.time())
sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
for cid, html in sorted(rows.items()):
    new, n = LINK_P.subn('', html)
    assert n == 1, (cid, n)
    assert not HREF.search(new), cid
    print(cid, 'removed', n, len(html), '->', len(new))
    sql.append("UPDATE mdl_book_chapters SET content=0x%s, timemodified=%d WHERE id=%d;" % (binascii.hexlify(new.encode('utf-8')).decode(), NOW, cid))
# chapters that were only links to articles on kabbalahmedia (last chapters of books 971 and 972)
sql.append("DELETE FROM mdl_book_chapters WHERE id IN (2292, 2299) AND bookid IN (971, 972);")
sql.append("UPDATE mdl_book SET revision=revision+1, timemodified=%d WHERE id IN (968, 970, 971, 972);" % NOW)
sql.append("UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;")
sql.append('COMMIT;')
sql.append("SELECT c.id, c.bookid, c.pagenum, c.title, c.content REGEXP 'href=\"https?://(www[.]|cdn[.])?kabbalahmedia[.]info' bad FROM mdl_book_chapters c JOIN mdl_book b ON b.id=c.bookid WHERE b.course=236 AND b.id IN (968,970,971,972) ORDER BY c.bookid, c.pagenum;")
open(sys.argv[2], 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
