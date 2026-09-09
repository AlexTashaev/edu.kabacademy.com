#!/usr/bin/env python3
"""Обновляет содержимое страницы «Как здесь всё устроено» (page 2474, cmid 13433, курс 238).

Делает бэкап текущего content в таблицу _kab_bk_howitworks, пишет новый HTML из page.html,
бампает revision и чистит записи mdl_files, оставшиеся от первой версии (скриншоты теперь
статические, лежат в wwwroot /kab/img/, а не в файловом хранилище Moodle).

Применять: scp upd_howitworks.sql web-18:/tmp/ && ssh web-18 '/tmp/kab_moodle_sql2.sh "$(cat /tmp/upd_howitworks.sql)"'
"""
from pathlib import Path

HERE = Path(__file__).parent
PAGE_ID, CMID, CTX, COURSE = 2474, 13433, 152818, 238


def q(s: str) -> str:
    return "'" + s.replace('\\', '\\\\').replace("'", "\\'") + "'"


html = (HERE / 'page.html').read_text(encoding='utf-8').strip()

sql = [
    'START TRANSACTION;',
    f'CREATE TABLE IF NOT EXISTS _kab_bk_howitworks (id INT AUTO_INCREMENT PRIMARY KEY, ts INT, content LONGTEXT);',
    f'INSERT INTO _kab_bk_howitworks (ts, content) SELECT UNIX_TIMESTAMP(), content FROM mdl_page WHERE id={PAGE_ID};',
    f'UPDATE mdl_page SET content={q(html)}, revision=revision+1, timemodified=UNIX_TIMESTAMP() WHERE id={PAGE_ID};',
    # Скриншоты больше не хранятся в Moodle: убираем записи первой версии.
    f"DELETE FROM mdl_files WHERE contextid={CTX} AND component='mod_page' AND filearea='content';",
    f'UPDATE mdl_course SET cacherev=UNIX_TIMESTAMP() WHERE id={COURSE};',
    'COMMIT;',
    f'SELECT id, name, revision, LENGTH(content) len FROM mdl_page WHERE id={PAGE_ID};',
    f'SELECT COUNT(*) files_left FROM mdl_files WHERE contextid={CTX};',
]

(HERE / 'upd_howitworks.sql').write_text('\n'.join(sql) + '\n', encoding='utf-8')
print('written', HERE / 'upd_howitworks.sql', len(html), 'chars of html')
