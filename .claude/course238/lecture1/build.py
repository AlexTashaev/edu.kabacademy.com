#!/usr/bin/env python3
"""Схемы 1–3 в лекции «Лекция 1. Суть науки каббала» (lesson 1960, cmid 13413, курс 238).

Что где:
  orig/page-<id>.html     контент страниц урока до вставки схем (снят с prod 24.09.2026)
  schemas/schema<N>.html  фрагменты-схемы: только inline-стили, без SVG (переживают TinyMCE и purifier)
  pages/page-<id>.html    итоговый контент — плейсхолдеры «Схема N. … заказ дизайнеру» заменены фрагментами
  upd_lesson1960.sql      бэкап страниц в _kab_bk20260924_lesson1960 + UPDATE mdl_lesson_pages
  rollback_lesson1960.sql возврат contents из бэкапа
  preview.html            локальный превью (--preview-dir DIR: рядом должны лежать fonts.css + fonts/)

Применять на prod:
  scp upd_lesson1960.sql web-18:/tmp/ && ssh web-18 '/tmp/kab_moodle_sqlfile.sh /tmp/upd_lesson1960.sql'

Повторный запуск build.py безопасен: источник всегда orig/, а не pages/.
"""
import argparse
import re
import shutil
from pathlib import Path

HERE = Path(__file__).parent
LESSON_ID = 1960
BACKUP_TABLE = '_kab_bk20260924_lesson1960'
PAGES = {
    17879: 'Глава 1. Предмет: что изучает наука каббала',
    17880: 'Глава 2. Два пути: сверху вниз и снизу вверх',
    17881: 'Глава 3. Только реальное',
}
# Плейсхолдер дизайнера: бежевый пунктирный блок «Схема N. …» с TODO-комментарием, без вложенных div.
PLACEHOLDER = re.compile(
    r'<div style="border: 1px dashed #b8a06a;[^"]*">\s*'
    r'<p style="margin: 0;"><strong>Схема (\d)\.</strong>.*?</div>',
    re.S,
)


def q(s: str) -> str:
    """SQL-литерал: экранируем бэкслеш и одинарную кавычку."""
    return "'" + s.replace('\\', '\\\\').replace("'", "\\'") + "'"


def load_schema(n: str) -> str:
    html = (HERE / 'schemas' / f'schema{n}.html').read_text(encoding='utf-8').strip()
    # Комментарий-шапку фрагмента в контент не тащим.
    html = re.sub(r'^<!--.*?-->\s*', '', html, count=1, flags=re.S)
    assert "'" not in html, f'schema{n}: одинарные кавычки ломают SQL-литерал и старые редакторы'
    return html


def build(preview_dir: Path | None) -> None:
    (HERE / 'pages').mkdir(exist_ok=True)
    updates = []
    preview_sections = []
    for page_id, title in PAGES.items():
        # newline='' — не трогаем переводы строк оригинала (в БД контент с CRLF).
        src = (HERE / 'orig' / f'page-{page_id}.html').read_text(encoding='utf-8', newline='')
        eol = '\r\n' if '\r\n' in src else '\n'
        found = []

        def repl(m: re.Match) -> str:
            found.append(m.group(1))
            return load_schema(m.group(1)).replace('\n', eol)

        out = PLACEHOLDER.sub(repl, src)
        print(f'page {page_id}: схемы {found or "—"}, {len(src)} → {len(out)} chars')
        (HERE / 'pages' / f'page-{page_id}.html').write_text(out, encoding='utf-8', newline='')
        if found:
            updates.append(
                f'UPDATE mdl_lesson_pages SET contents={q(out)}, timemodified=UNIX_TIMESTAMP() '
                f'WHERE id={page_id} AND lessonid={LESSON_ID};'
            )
        preview_sections.append(f'<section><h2>{title}</h2>\n{out}\n</section>')

    sql = [
        f'-- Лекция 1 (lesson {LESSON_ID}, cmid 13413, курс 238): схемы 1–3 вместо плейсхолдеров дизайнера.',
        '-- Сгенерировано build.py; откат — rollback_lesson1960.sql.',
        'START TRANSACTION;',
        # Явный utf8mb4: у базы дефолт latin1, без него бэкап падает на кириллице.
        f'CREATE TABLE IF NOT EXISTS {BACKUP_TABLE} DEFAULT CHARSET=utf8mb4 '
        f'AS SELECT * FROM mdl_lesson_pages WHERE lessonid={LESSON_ID};',
        *updates,
        f'UPDATE mdl_lesson SET timemodified=UNIX_TIMESTAMP() WHERE id={LESSON_ID};',
        'COMMIT;',
        f'SELECT id, title, LENGTH(contents) len, '
        f"(LENGTH(contents)-LENGTH(REPLACE(contents,'kab-figure','')))/LENGTH('kab-figure') figures, "
        f"(LENGTH(contents)-LENGTH(REPLACE(contents,'заказ дизайнеру','')))/LENGTH('заказ дизайнеру') todo "
        f'FROM mdl_lesson_pages WHERE lessonid={LESSON_ID} ORDER BY id;',
    ]
    (HERE / 'upd_lesson1960.sql').write_text('\n'.join(sql) + '\n', encoding='utf-8', newline='\n')

    rollback = [
        f'-- Откат схем лекции 1: contents страниц из {BACKUP_TABLE}.',
        f'UPDATE mdl_lesson_pages p JOIN {BACKUP_TABLE} b ON b.id=p.id '
        f'SET p.contents=b.contents, p.timemodified=b.timemodified WHERE p.lessonid={LESSON_ID};',
        f'SELECT id, title, LENGTH(contents) len FROM mdl_lesson_pages WHERE lessonid={LESSON_ID} ORDER BY id;',
    ]
    (HERE / 'rollback_lesson1960.sql').write_text('\n'.join(rollback) + '\n', encoding='utf-8', newline='\n')

    if preview_dir:
        preview_dir.mkdir(parents=True, exist_ok=True)
        html = PREVIEW_SHELL.replace('{{SECTIONS}}', '\n'.join(preview_sections))
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
    ap.add_argument('--preview-dir', type=Path, default=None,
                    help='куда положить preview.html (рядом нужны fonts.css и fonts/)')
    args = ap.parse_args()
    build(args.preview_dir)
