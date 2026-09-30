#!/usr/bin/env python3
"""Схемы дизайнера в уроках 1 и 2 — замена моих схем 1–5 во всех местах, где они стоят.

Цели (30.09.2026):
  l1962 — лекция 1, курс 236 (основной), lesson 1962 / cmid 13459: главы 17888 (схема 1), 17889 (схемы 2, 3)
  l1960 — лекция 1, курс 238 (рабочий), lesson 1960 / cmid 13413: главы 17879 (схема 1), 17880 (схемы 2, 3)
  b962  — урок 2, курс 238, книга 962 / cmid 13389: главы 2258 (схема 4), 2257 (схема 5)
Урока 2 в курсе 236 на 30.09 нет.

Как работает (как lecture1/build.py): для каждой цели снимает текущий контент с prod (ssh, только
SELECT TO_BASE64), находит блоки <div class="kab-figure"> и по подписи «Схема N.» заменяет на
schemas/schema<N>.html; остальное на странице (текст, видео) не трогает. UPDATE защищён
MD5(содержимого): если страницу успели изменить, обновится 0 строк — пересобрать.

Запуск:   python build.py [--target l1962 l1960 b962] [--only 1 2 3 4 5] [--no-fetch]
Картинки: python make_assets.py → public/public/kab/img/schemes/ → залить на сервер ДО применения SQL.
Применить: scp upd_<цель>.sql web-18:/tmp/ && ssh web-18 '/tmp/kab_moodle_sqlfile.sh /tmp/upd_<цель>.sql'
"""
import argparse
import base64
import hashlib
import re
import subprocess
import time
from pathlib import Path

HERE = Path(__file__).parent
TARGETS = {
    'l1962': {'table': 'mdl_lesson_pages', 'parent': 'lessonid', 'parent_id': 1962, 'field': 'contents',
              'touch': 'mdl_lesson', 'note': 'лекция 1, курс 236 (основной), cmid 13459',
              'items': {17888: 'Глава 1', 17889: 'Глава 2'}},
    'l1960': {'table': 'mdl_lesson_pages', 'parent': 'lessonid', 'parent_id': 1960, 'field': 'contents',
              'touch': 'mdl_lesson', 'note': 'лекция 1, курс 238, cmid 13413',
              'items': {17879: 'Глава 1', 17880: 'Глава 2'}},
    'b962': {'table': 'mdl_book_chapters', 'parent': 'bookid', 'parent_id': 962, 'field': 'content',
             'touch': 'mdl_book', 'note': 'урок 2, курс 238, книга 962, cmid 13389',
             'items': {2258: 'Вводная глава', 2257: 'Глава 1'}},
}
FIGURE_OPEN = '<div class="kab-figure"'
CAPTION = re.compile(r'>Схема (\d)\.</span>')
RAV = re.compile(r'\bРав\b')          # правило владельца 24.09.2026: «Михаэль Лайтман», не «Рав»
BSN = chr(92) + 'n'                  # batch-вывод mysql отдаёт переводы строк литералом «\n»


def q(s: str) -> str:
    return "'" + s.replace('\\', '\\\\').replace("'", "\\'") + "'"


def load_schema(n: str) -> str | None:
    p = HERE / 'schemas' / f'schema{n}.html'
    if not p.exists():
        return None
    html = re.sub(r'^<!--.*?-->\s*', '', p.read_text(encoding='utf-8').strip(), count=1, flags=re.S)
    assert "'" not in html, f'schema{n}: одинарные кавычки ломают SQL-литерал'
    return html


def sql_select(query: str) -> str:
    return subprocess.run(['ssh', '-o', 'BatchMode=yes', 'web-18', f'/tmp/kab_moodle_sql2.sh "{query}"'],
                          capture_output=True, text=True, encoding='utf-8', check=True).stdout


def fetch(name: str) -> None:
    t = TARGETS[name]
    d = HERE / 'current' / name
    d.mkdir(parents=True, exist_ok=True)
    for item in t['items']:
        out = sql_select(f"SELECT TO_BASE64({t['field']}) FROM {t['table']} WHERE id={item} AND {t['parent']}={t['parent_id']}")
        body = out.split('\n', 1)[1].replace(BSN, '')
        assert body.strip(), f'{name}: запись {item} не найдена'
        data = base64.b64decode(re.sub(r'\s', '', body))
        (d / f'{item}.html').write_bytes(data)
        print(f'  fetched {item}: {len(data)} bytes, md5 {hashlib.md5(data).hexdigest()}')


def figure_blocks(html: str):
    """[(start, end, N)] для каждого блока kab-figure; конец ищем по балансу <div>."""
    blocks, pos = [], 0
    while (start := html.find(FIGURE_OPEN, pos)) != -1:
        depth, i = 0, start
        for m in re.finditer(r'<div\b|</div>', html[start:]):
            depth += 1 if m.group(0) != '</div>' else -1
            if depth == 0:
                i = start + m.end()
                break
        assert i > start, 'незакрытый блок kab-figure'
        cap = CAPTION.search(html, start, i)
        blocks.append((start, i, cap.group(1) if cap else None))
        pos = i
    return blocks


def build(name: str, only: set | None) -> None:
    t = TARGETS[name]
    (HERE / 'pages' / name).mkdir(parents=True, exist_ok=True)
    backup = f'_kab_bk{time.strftime("%Y%m%d")}d_{name}'          # d = designer
    updates = []
    for item, title in t['items'].items():
        raw = (HERE / 'current' / name / f'{item}.html').read_bytes()
        src = raw.decode('utf-8')
        out, replaced = src, []
        for start, end, n in reversed(figure_blocks(src)):
            frag = load_schema(n) if n and (only is None or n in only) else None
            if frag and out[start:end] != frag:
                out = out[:start] + frag + out[end:]
                replaced.append(n)
        out, nfix = RAV.subn('Михаэль Лайтман', out)
        out = out.replace('\r\n', '\n')              # клиент mysql всё равно выбросит CR из литерала
        print(f'  {item} ({title}): схемы {sorted(replaced) or "—"}, «Рав»→ {nfix}, {len(src)} → {len(out)} chars')
        (HERE / 'pages' / name / f'{item}.html').write_text(out, encoding='utf-8', newline='')
        if replaced or nfix:
            updates.append(
                f"UPDATE {t['table']} SET {t['field']}={q(out)}, timemodified=UNIX_TIMESTAMP() "
                f"WHERE id={item} AND {t['parent']}={t['parent_id']} AND MD5({t['field']})='{hashlib.md5(raw).hexdigest()}';\n"
                f'SELECT {item} item, ROW_COUNT() updated_rows;'
            )
    ids = ','.join(str(i) for i in t['items'])
    sql = [
        f"-- Схемы дизайнера: {t['note']}. Сгенерировано designer_schemes/build.py от контента с prod.",
        '-- UPDATE сработает, только если запись с тех пор не менялась (MD5); updated_rows = 0 → пересобрать.',
        f"CREATE TABLE IF NOT EXISTS {backup} DEFAULT CHARSET=utf8mb4 AS SELECT * FROM {t['table']} "
        f"WHERE {t['parent']}={t['parent_id']};",
        *updates,
        f"UPDATE {t['touch']} SET timemodified=UNIX_TIMESTAMP() WHERE id={t['parent_id']};",
        f"SELECT id, LENGTH({t['field']}) len, MD5({t['field']}) md5, "
        f"(LENGTH({t['field']})-LENGTH(REPLACE({t['field']},'/kab/img/schemes/','')))/LENGTH('/kab/img/schemes/') designer_refs "
        f"FROM {t['table']} WHERE id IN ({ids}) ORDER BY id;",
    ]
    (HERE / f'upd_{name}.sql').write_text('\n'.join(sql) + '\n', encoding='utf-8', newline='\n')
    rollback = [
        f"-- Откат схем дизайнера ({t['note']}): содержимое из {backup}.",
        f"UPDATE {t['table']} x JOIN {backup} b ON b.id=x.id SET x.{t['field']}=b.{t['field']}, "
        f"x.timemodified=b.timemodified WHERE x.id IN ({ids});",
        f"SELECT id, LENGTH({t['field']}) len FROM {t['table']} WHERE id IN ({ids}) ORDER BY id;",
    ]
    (HERE / f'rollback_{name}.sql').write_text('\n'.join(rollback) + '\n', encoding='utf-8', newline='\n')
    print(f'  SQL: {len(updates)} UPDATE → upd_{name}.sql, бэкап {backup}')


if __name__ == '__main__':
    ap = argparse.ArgumentParser()
    ap.add_argument('--target', nargs='*', default=list(TARGETS), choices=list(TARGETS))
    ap.add_argument('--only', nargs='*', default=None, help='номера схем для замены (по умолчанию все)')
    ap.add_argument('--no-fetch', action='store_true')
    args = ap.parse_args()
    for name in args.target:
        print(f"{name} — {TARGETS[name]['note']}:")
        if not args.no_fetch:
            fetch(name)
        build(name, set(args.only) if args.only is not None else None)
