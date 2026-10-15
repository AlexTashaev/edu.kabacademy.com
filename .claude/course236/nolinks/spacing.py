# Book chapters «Лекции урока в записи» (course 236, lessons 3–6): drop our editorial note at the top and
# add space between the stacked players (feedback 15.10.2026). Input: "SELECT id, TO_BASE64(content)"; output: SQL.
import base64, binascii, re, sys, time
BS = chr(92)
rows = {}
for line in open(sys.argv[1], encoding='utf-8').read().splitlines()[1:]:
    parts = line.split('\t')
    try:
        rows[int(parts[0])] = base64.b64decode(parts[-1].replace(BS + 'n', '')).decode('utf-8')
    except Exception:
        continue
NOTE = re.compile(r'<div class="kab-quote">Лекци[^<]*ядро урока на него не опирается\.</div>\n')
DET = '<details class="kab-details">'
DET_SPACED = '<details class="kab-details" style="margin-bottom: 40px;">'
NOW = int(time.time())
sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
for cid, html in sorted(rows.items()):
    new, n = NOTE.subn('', html)
    assert n == 1, (cid, 'note', n)
    k = new.count(DET)
    assert k >= 3 and DET_SPACED not in new, (cid, k)
    new = new.replace(DET, DET_SPACED)
    print(cid, 'note removed, players spaced:', k)
    sql.append("UPDATE mdl_book_chapters SET content=0x%s, timemodified=%d WHERE id=%d;" % (binascii.hexlify(new.encode('utf-8')).decode(), NOW, cid))
sql.append("UPDATE mdl_book SET revision=revision+1, timemodified=%d WHERE id IN (968, 970, 971, 972);" % NOW)
sql.append("UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;")
sql.append('COMMIT;')
sql.append("SELECT id, bookid, LENGTH(content) len, LOCATE('ядро урока', content) note, (LENGTH(content)-LENGTH(REPLACE(content,'margin-bottom: 40px','')))/19 spaced FROM mdl_book_chapters WHERE id IN (%s);" % ','.join(map(str, sorted(rows))))
open(sys.argv[2], 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
