#!/usr/bin/env python3
"""Схемы лекции «Лекция 1. Суть науки каббала» (lesson 1960, cmid 13413, курс 238).

С 27.09.2026 сборка идёт ОТ СВЕЖЕГО КОНТЕНТА ИЗ БД, а не от снимка: страницы урока правят
параллельно (клипы, редактор Moodle), поэтому скрипт
  1) снимает текущий contents страниц с prod (ssh web-18, только SELECT),
  2) находит в нём блоки <div class="kab-figure"> и по подписи «Схема N.» заменяет те,
     для которых есть schemas/schema<N>.html (остальное на странице не трогает),
  3) пишет pages/, upd_lesson1960.sql и rollback_lesson1960.sql.
UPDATE защищён от гонки: выполняется только если MD5(contents) на prod тот же, что был при снятии.

Что где:
  schemas/schema<N>.html  фрагмент схемы (1 — вёрстка на inline-стилях; 2 и 3 — <picture> с SVG)
  svg/make_svg.py         генератор чертежей → public/public/kab/img/l1/*.svg (заливаются на сервер)
  current/                контент страниц, снятый с prod последним запуском
  pages/                  итоговый контент
  orig/                   исторический снимок до первых схем (24.09.2026), в сборке не участвует

Запуск:
  python build.py                      # снять с prod, собрать
  python build.py --no-fetch           # собрать из current/ без ssh
  python build.py --only 2 3           # заменить только эти схемы
  python build.py --preview-dir DIR    # + preview.html (в DIR нужны fonts.css, fonts/ и l1/*.svg)
Применить:
  scp public/public/kab/img/l1/*.svg web-18:/sites/edu.kabacademy.com/public/public/kab/img/l1/
  scp upd_lesson1960.sql web-18:/tmp/ && ssh web-18 '/tmp/kab_moodle_sqlfile.sh /tmp/upd_lesson1960.sql'
"""
import argparse
import base64
import hashlib
import re
import subprocess
import time
from pathlib import Path

HERE = Path(__file__).parent
LESSON_ID = 1960
PAGES = {
    17879: 'Глава 1. Предмет: что изучает наука каббала',
    17880: 'Глава 2. Два пути: сверху вниз и снизу вверх',
    17881: 'Глава 3. Только реальное',
}
IMG_BASE = 'https://edu.kabacademy.com/kab/img/l1/'
FIGURE_OPEN = '<div class="kab-figure"'
CAPTION = re.compile(r'>Схема (\d)\.</span>')
# Правило заказчика (24.09.2026): слово «Рав» в текстах не употреблять — «Михаэль Лайтман».
RAV = re.compile(r'\bРав\b')
BSN = chr(92) + 'n'          # batch-вывод mysql отдаёт переводы строк литералом «\n»


def q(s: str) -> str:
    """SQL-литерал: экранируем бэкслеш и одинарную кавычку."""
    return "'" + s.replace('\\', '\\\\').replace("'", "\\'") + "'"


def load_schema(n: str) -> str | None:
    p = HERE / 'schemas' / f'schema{n}.html'
    if not p.exists():
        return None
    html = p.read_text(encoding='utf-8').strip()
    html = re.sub(r'^<!--.*?-->\s*', '', html, count=1, flags=re.S)   # комментарий-шапку в контент не тащим
    assert "'" not in html, f'schema{n}: одинарные кавычки ломают SQL-литерал и старые редакторы'
    return html


def fetch() -> None:
    """Снять текущий contents страниц с prod (TO_BASE64 — побайтно точно)."""
    (HERE / 'current').mkdir(exist_ok=True)
    for page_id in PAGES:
        sql = f'SELECT TO_BASE64(contents) FROM mdl_lesson_pages WHERE id={page_id} AND lessonid={LESSON_ID}'
        out = subprocess.run(['ssh', '-o', 'BatchMode=yes', 'web-18', f'/tmp/kab_moodle_sql2.sh "{sql}"'],
                             capture_output=True, text=True, encoding='utf-8', check=True).stdout
        body = out.split('\n', 1)[1].replace(BSN, '')
        data = base64.b64decode(re.sub(r'\s', '', body))
        (HERE / 'current' / f'page-{page_id}.html').write_bytes(data)
        print(f'fetched {page_id}: {len(data)} bytes, md5 {hashlib.md5(data).hexdigest()}')


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


def build(only: set | None, preview_dir: Path | None) -> None:
    (HERE / 'pages').mkdir(exist_ok=True)
    stamp = time.strftime('%Y%m%d')
    backup = f'_kab_bk{stamp}s_l1960_pages'          # s = schemas; у сессии клипов свои бэкапы без суффикса
    updates, preview = [], []
    for page_id, title in PAGES.items():
        raw = (HERE / 'current' / f'page-{page_id}.html').read_bytes()
        src = raw.decode('utf-8')
        out, replaced = src, []
        for start, end, n in reversed(figure_blocks(src)):
            frag = load_schema(n) if n and (only is None or n in only) else None
            if frag and out[start:end] != frag:
                out = out[:start] + frag + out[end:]
                replaced.append(n)
        out, nfix = RAV.subn('Михаэль Лайтман', out)
        out = out.replace('\r\n', '\n')              # клиент mysql всё равно выбросит CR из литерала
        print(f'page {page_id}: заменены схемы {sorted(replaced) or "—"}, «Рав»→ {nfix}, '
              f'{len(src)} → {len(out)} chars')
        (HERE / 'pages' / f'page-{page_id}.html').write_text(out, encoding='utf-8', newline='')
        if replaced or nfix:
            updates.append(
                f'UPDATE mdl_lesson_pages SET contents={q(out)}, timemodified=UNIX_TIMESTAMP() '
                f"WHERE id={page_id} AND lessonid={LESSON_ID} AND MD5(contents)='{hashlib.md5(raw).hexdigest()}';\n"
                f"SELECT {page_id} page, ROW_COUNT() updated_rows;"
            )
        preview.append(f'<section><h2>{title}</h2>\n{out}\n</section>')

    sql = [
        f'-- Лекция 1 (lesson {LESSON_ID}, cmid 13413, курс 238): обновление схем.',
        '-- Сгенерировано build.py от контента, снятого с prod; UPDATE сработает, только если',
        '-- страница с тех пор не менялась (MD5). updated_rows = 0 → пересобрать: python build.py',
        f'CREATE TABLE IF NOT EXISTS {backup} DEFAULT CHARSET=utf8mb4 '
        f'AS SELECT * FROM mdl_lesson_pages WHERE lessonid={LESSON_ID};',
        *updates,
        f'SELECT id, title, LENGTH(contents) len, MD5(contents) md5, '
        f"(LENGTH(contents)-LENGTH(REPLACE(contents,'kab-figure','')))/LENGTH('kab-figure') figures "
        f'FROM mdl_lesson_pages WHERE lessonid={LESSON_ID} ORDER BY id;',
    ]
    (HERE / 'upd_lesson1960.sql').write_text('\n'.join(sql) + '\n', encoding='utf-8', newline='\n')
    rollback = [
        f'-- Откат: contents страниц из {backup} (состояние перед этим обновлением схем).',
        f'UPDATE mdl_lesson_pages p JOIN {backup} b ON b.id=p.id '
        f'SET p.contents=b.contents, p.timemodified=b.timemodified WHERE p.lessonid={LESSON_ID};',
        f'SELECT id, title, LENGTH(contents) len FROM mdl_lesson_pages WHERE lessonid={LESSON_ID} ORDER BY id;',
    ]
    (HERE / 'rollback_lesson1960.sql').write_text('\n'.join(rollback) + '\n', encoding='utf-8', newline='\n')
    print(f'SQL: {len(updates)} UPDATE, бэкап {backup}')

    if preview_dir:
        preview_dir.mkdir(parents=True, exist_ok=True)
        html = PREVIEW_SHELL.replace('{{SECTIONS}}', '\n'.join(preview)).replace(IMG_BASE, 'l1/')
        (preview_dir / 'preview.html').write_text(html, encoding='utf-8', newline='\n')
        print('preview:', preview_dir / 'preview.html')


# Приближение обвязки Boost (font-size .9375rem, line-height 1.5, box-sizing) + Montserrat темы.
PREVIEW_SHELL = """<!doctype html>
<html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Лекция 1 — превью схем</title>
<link rel="stylesheet" href="fonts.css">
<style>
*,*::before,*::after{box-sizing:border-box}
body{margin:0;background:#f8f9fa;font-family:Montserrat,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;font-size:.9375rem;line-height:1.5;color:#1d2125}
main{max-width:830px;margin:0 auto;padding:24px 24px 60px;background:#fff}
section+section{border-top:1px solid #dee2e6;margin-top:40px;padding-top:24px}
h2{font-size:1.5rem;font-weight:700;margin:0 0 1rem;line-height:1.2}
h4{font-size:1.171875rem;font-weight:700;margin:1.6rem 0 .6rem;line-height:1.2}
p{margin:0 0 1rem}
blockquote{margin:0 0 1rem;padding:0 0 0 1rem;border-left:4px solid #dee2e6}
ul{margin:0 0 1rem;padding-left:1.5rem}
a{color:#0f6cbf}
img,video{max-width:100%}
@media (max-width:600px){main{padding:16px 12px 40px}}
</style></head><body><main>
{{SECTIONS}}
</main>
<script>
// ?w=330 — узкая колонка как на телефоне (headless-браузеры не дают окно уже ~500px).
var w = new URLSearchParams(location.search).get('w');
if (w) { var m = document.querySelector('main'); m.style.maxWidth = w + 'px'; m.style.padding = '16px 12px 40px'; }
</script>
</body></html>
"""


if __name__ == '__main__':
    ap = argparse.ArgumentParser()
    ap.add_argument('--no-fetch', action='store_true', help='не ходить на prod, собрать из current/')
    ap.add_argument('--only', nargs='*', default=None, help='номера схем для замены (по умолчанию все, что есть)')
    ap.add_argument('--preview-dir', type=Path, default=None,
                    help='куда положить preview.html (рядом нужны fonts.css, fonts/ и l1/*.svg)')
    args = ap.parse_args()
    if not args.no_fetch:
        fetch()
    build(set(args.only) if args.only is not None else None, args.preview_dir)
