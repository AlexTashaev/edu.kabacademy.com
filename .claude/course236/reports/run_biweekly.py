"""Таблица «Курс 236 — вебинары, архив, баллы» раз в две недели: собрать и разослать письмом.

Запускает задача приложения Claude «Отчёт по вебинарам 236 — раз в 2 недели» (по воскресеньям в 11:00).
Пора ли отправлять, скрипт решает сам: письмо уходит, если с прошлой очередной отправки прошло
не меньше 13 дней (пропущенный запуск догонит следующий), и больше не уходит после отчёта,
собранного позже последнего вебинара курса (урок 15, 07.01.2027).

    python run_biweekly.py [--force] [--to a@x,b@y] [--dry-run]

Последняя строка вывода — итог: ПРОПУСК / ОТПРАВЛЕНО / КУРС ЗАВЕРШЁН / DRY-RUN; ошибка — код ≠ 0.
--force   — отправить, не глядя на интервал (внеочередная рассылка; считается очередной);
--to      — другим адресатам (проверка); такая отправка интервал не сдвигает;
--dry-run — собрать xlsx и письмо (preview.html рядом со скриптом), не отправлять.

Рядом со скриптом должны лежать webinars_report.py и last_sent.txt (дата прошлой очередной
отправки); xlsx с ПДн ложится в родительскую папку (вне репо). На сервер файл не пишется:
письмо целиком уходит JSON-ом на stdin send_report_mail.php на web-18 (SendGrid ключом Moodle).
"""
import argparse
import base64
import json
import os
import re
import subprocess
import sys
from datetime import date, datetime, timedelta
from pathlib import Path
from zoneinfo import ZoneInfo

HERE = Path(__file__).resolve().parent
OUTDIR = HERE.parent
STATE = HERE / "last_sent.txt"
RECIPIENTS = ["alext@kab.co.il", "israelsh@kab.co.il"]
EVERY_DAYS = 13  # не 14: запуск, сдвинутый на день, не должен выбивать цикл
LAST_WEBINAR = date(2027, 1, 7)  # вебинар по уроку 15 — последний в курсе 236
TZ = ZoneInfo("Asia/Jerusalem")
MAILER = ["ssh", "-o", "BatchMode=yes", "web-18",
          "php82 /sites/edu.kabacademy.com/public/kab-tools/webinars_report/send_report_mail.php"]
NOISE = re.compile(r"post-quantum|store now, decrypt later|may need to be upgraded|openssh\.com/pq")


def summary(xlsx):
    from openpyxl import load_workbook
    wb = load_workbook(xlsx, read_only=True)
    head = next(wb["Студенты"].iter_rows(max_row=1, values_only=True))
    dates = {int(m.group(1)): m.group(2) for h in head if h and (m := re.match(r"Вебинар (\d+) \((\d\d\.\d\d)\)", h))}
    rows = list(wb["Сводка"].iter_rows(values_only=True))
    total = rows[0][1]
    webinars = [r for r in rows[3:] if r and isinstance(r[0], str) and r[0].startswith("Вебинар ")]
    wb.close()
    return total, dates, webinars


def compose(xlsx, stamp):
    total, dates, webinars = summary(xlsx)
    td = 'style="padding:4px 10px;border:1px solid #ddd;text-align:right"'
    th = 'style="padding:4px 10px;border:1px solid #ddd;background:#f3f3f3;text-align:left"'
    trs = []
    for r in webinars:
        n = int(r[0].split()[1])
        sessions = " / ".join(str(x) for x in r[2:5])
        trs.append(f"<tr><td {td.replace('right', 'left')}>{n}</td><td {td}>{dates.get(n, '')}</td><td {td}>{r[1]}</td>"
                   f"<td {td}>{sessions}</td><td {td}>{r[7]}</td><td {td}>{r[9]}</td></tr>")
    body = f"""<div style="font:14px/1.45 Arial,sans-serif;color:#222">
<p>Отчёт по курсу 236 «Классическая каббала. Часть первая (Осень 2026)» на {stamp:%d.%m.%Y %H:%M} (время Израиля).
Таблица по каждому студенту — во вложении; что значат столбцы — на первом листе «Как читать».</p>
<p>Студентов в курсе: <b>{total}</b>.</p>
<table style="border-collapse:collapse;font-size:13px">
<tr><th {th}>Вебинар</th><th {th}>Дата</th><th {th}>Был в Zoom</th><th {th}>Сессии 8:00 / 17:00 / 20:00</th>
<th {th}>Открывали запись</th><th {th}>Сдали тест урока</th></tr>
{''.join(trs)}
</table>
<p style="color:#666;font-size:12px">«Был в Zoom» — по отчёту Zoom; человек, зашедший под чужим именем и почтой, может не попасть
(такие — на листе «Zoom без привязки»). «Открывали запись» — открывали страницу «Запись вебинара N (архив)»;
досмотрел ли ролик, Moodle не видит. Отчёт Zoom за вечерние сессии появляется на следующий день.</p>
<p style="color:#666;font-size:12px">В файле персональные данные студентов — не пересылать за пределы команды.
Автоотчёт раз в две недели до конца курса (последний — после вебинара по уроку 15, 07.01.2027).</p>
</div>"""
    return f"Курс 236: вебинары, записи, тесты — на {stamp:%d.%m.%Y}", body


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--force", action="store_true")
    ap.add_argument("--to")
    ap.add_argument("--dry-run", action="store_true")
    args = ap.parse_args()

    now = datetime.now(TZ)
    today = now.date()
    last = date.fromisoformat(STATE.read_text(encoding="utf-8").strip()) if STATE.exists() else None
    regular = not args.to
    if regular and not args.force and not args.dry_run and last:
        if last >= LAST_WEBINAR + timedelta(days=2):
            print(f"КУРС ЗАВЕРШЁН: финальный отчёт отправлен {last:%d.%m.%Y}, больше не отправлять")
            return
        if (today - last).days < EVERY_DAYS:
            print(f"ПРОПУСК: прошлый отчёт {last:%d.%m.%Y}, следующий — не раньше "
                  f"{last + timedelta(days=EVERY_DAYS):%d.%m.%Y}")
            return

    gen = subprocess.run([sys.executable, str(HERE / "webinars_report.py"), "-o", str(OUTDIR)],
                         capture_output=True, text=True, encoding="utf-8",
                         env={**os.environ, "PYTHONIOENCODING": "utf-8"})
    out = [ln for ln in gen.stdout.splitlines() if not NOISE.search(ln)]
    if gen.returncode or not out:
        err = "\n".join(ln for ln in gen.stderr.splitlines() if not NOISE.search(ln))
        raise SystemExit(f"ОШИБКА: генератор упал (код {gen.returncode})\n{err[-2000:]}")
    xlsx = Path(out[0].strip())
    print("\n".join(out))

    subject, body = compose(xlsx, now)
    to = [a.strip() for a in args.to.split(",")] if args.to else RECIPIENTS
    if args.dry_run:
        (HERE / "preview.html").write_text(body, encoding="utf-8")
        print(f"DRY-RUN: {xlsx.name}, тема «{subject}», адресаты {', '.join(to)}; письмо — preview.html")
        return

    mail = {
        "to": to,
        "subject": subject,
        "html": body,
        "from_name": "edu.kabacademy.com — отчёт по курсу 236",
        "attachments": [{
            "filename": xlsx.name,
            "type": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "content": base64.b64encode(xlsx.read_bytes()).decode("ascii"),
        }],
    }
    res = subprocess.run(MAILER, input=json.dumps(mail, ensure_ascii=False), capture_output=True,
                         text=True, encoding="utf-8")
    msg = "\n".join(ln for ln in (res.stdout + res.stderr).splitlines() if not NOISE.search(ln)).strip()
    if res.returncode:
        raise SystemExit(f"ОШИБКА: письмо не ушло (код {res.returncode}): {msg}")
    if regular:
        STATE.write_text(today.isoformat(), encoding="utf-8")
    print(f"ОТПРАВЛЕНО: {', '.join(to)} — «{xlsx.name}»; {msg}")


if __name__ == "__main__":
    main()
