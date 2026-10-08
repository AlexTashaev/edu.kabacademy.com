"""Курс 236 (ОК-1 Осень-26): таблица студентов — вебинары, архив, тесты, баллы опыта.

Запуск (локальная сессия с ssh-алиасом web-18):
    python webinars_report.py -o "<папка вне репо>"

Выгрузка содержит ПДн — файл .xlsx класть только вне репозитория.
Столбцы появляются только для вебинаров, которые уже начались, — перезапуск
после очередного вебинара сам добавит новые.

Откуда что берётся:
- сессия и минуты — отчёт Zoom (mdl_zoom_meeting_participants); пользователя
  к участнику привязывает сам mod_zoom (по email, затем по похожести имени),
  непривязанные — на листе «Zoom без привязки»;
- сессия 8:00 / 17:00 / 20:00 — ближайший слот к началу сессии по Израилю;
- «вход из Moodle» — нажал «Войти» на странице вебинара (оценка zoom = 100),
  но в отчёте Zoom не найден (зашёл под другим именем или не дошёл);
- архив — открывал страницу «Запись вебинара N (архив)»: сами ролики на
  YouTube/Drive, их просмотр Moodle не видит;
- баллы опыта — блок «Опыт!» (block_xp, «капли живой воды»).
"""
import argparse
import json
import re
import subprocess
from collections import defaultdict
from datetime import datetime, timezone
from pathlib import Path
from zoneinfo import ZoneInfo

from openpyxl import Workbook
from openpyxl.styles import Alignment, Font, PatternFill
from openpyxl.utils import get_column_letter

COURSE = 236
TZ = ZoneInfo("Asia/Jerusalem")
SLOTS = (8, 17, 20)  # часы вебинаров по Израилю
ADMINS = {2}  # аккаунт администратора записан студентом — не считать
# у модераторов форума бывает и роль «студент» — в таблицу студентов не брать
STAFF_ROLES = {"teacher", "editingteacher", "manager", "forummoderator", "moderator_assist", "koordinator"}

# урок -> (zoom id, cmid «Запись вебинара N (архив)», quiz id)
LESSONS = {
    1: (435, 13440, 1235), 2: (408, 13481, 1236), 3: (407, 13490, 1237),
    4: (406, 13507, 1238), 5: (405, 13523, 1239), 6: (404, 13539, 1240),
    7: (403, 13554, 1241), 8: (402, 13569, 1242), 9: (401, 13584, 1243),
    10: (400, 13599, 1244), 11: (399, 13614, 1245), 12: (398, 13629, 1246),
    13: (397, 13644, 1247), 14: (396, 13659, 1248), 15: (395, 13674, 1249),
}
SQL_CMD = ["ssh", "web-18", '/tmp/kab_moodle_sql2.sh "$(cat)"']


def ids(xs):
    return ",".join(str(x) for x in xs)


ZOOMS = ids(v[0] for v in LESSONS.values())
ARCHIVES = ids(v[1] for v in LESSONS.values())
QUIZZES = ids(v[2] for v in LESSONS.values())

QUERIES = {
    "users": f"""
SELECT u.id, u.firstname, u.lastname, u.email,
 (SELECT GROUP_CONCAT(DISTINCT r.shortname) FROM mdl_role_assignments ra
   JOIN mdl_role r ON r.id = ra.roleid
   JOIN mdl_context cx ON cx.id = ra.contextid AND cx.contextlevel = 50 AND cx.instanceid = {COURSE}
  WHERE ra.userid = u.id) roles,
 MIN(ue.status) status,
 (SELECT timeaccess FROM mdl_user_lastaccess la WHERE la.userid = u.id AND la.courseid = {COURSE}) lastaccess,
 (SELECT xp FROM mdl_block_xp x WHERE x.userid = u.id AND x.courseid = {COURSE}) xp
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid AND e.courseid = {COURSE}
WHERE u.deleted = 0
GROUP BY u.id""",
    "zooms": f"SELECT id, name, start_time FROM mdl_zoom WHERE id IN ({ZOOMS})",
    "participants": f"""
SELECT d.zoomid, d.start_time, p.userid, p.name, p.join_time, p.leave_time
FROM mdl_zoom_meeting_participants p
JOIN mdl_zoom_meeting_details d ON d.id = p.detailsid
WHERE d.zoomid IN ({ZOOMS})""",
    "grades": f"""
SELECT gi.itemmodule, gi.iteminstance, g.userid, g.finalgrade
FROM mdl_grade_items gi JOIN mdl_grade_grades g ON g.itemid = gi.id
WHERE gi.courseid = {COURSE} AND g.finalgrade IS NOT NULL
  AND ((gi.itemmodule = 'zoom' AND gi.iteminstance IN ({ZOOMS}))
    OR (gi.itemmodule = 'quiz' AND gi.iteminstance IN ({QUIZZES})))""",
    "views": f"""
SELECT contextinstanceid, userid, COUNT(*) n
FROM mdl_logstore_standard_log
WHERE contextlevel = 70 AND contextinstanceid IN ({ARCHIVES})
  AND eventname LIKE '%mod_page%course_module_viewed'
GROUP BY contextinstanceid, userid""",
    "xpconfig": f"SELECT levelsdata FROM mdl_block_xp_config WHERE courseid = {COURSE}",
}


def unescape(s):
    """Поле из batch-вывода mysql: NULL и экранирование \\\\, \\t, \\n."""
    if s == "NULL":
        return None
    return re.sub(r"\\(.)", lambda m: {"t": "\t", "n": "\n", "0": "\0"}.get(m.group(1), m.group(1)), s)


def query(sql):
    out = subprocess.run(SQL_CMD, input=sql, capture_output=True, text=True, encoding="utf-8", check=True).stdout
    lines = out.rstrip("\n").split("\n")
    if not lines or not lines[0]:
        return []
    head = lines[0].split("\t")
    return [dict(zip(head, map(unescape, ln.split("\t")))) for ln in lines[1:]]


def slot_of(ts):
    t = datetime.fromtimestamp(ts, TZ)
    h = t.hour + t.minute / 60
    return min(SLOTS, key=lambda s: abs(s - h))


def union_minutes(intervals):
    total, end = 0, None
    for a, b in sorted(intervals):
        if end is None or a > end:
            total += b - a
            end = b
        elif b > end:
            total += b - end
            end = b
    return round(total / 60)


def level_of(xp, thresholds):
    return sum(1 for t in thresholds if xp >= t) or 1


def date_str(ts):
    return datetime.fromtimestamp(ts, TZ).strftime("%d.%m.%Y") if ts else ""


def build(data, outdir):
    now = datetime.now(timezone.utc).timestamp()
    zooms = {int(z["id"]): z for z in data["zooms"]}
    lessons = [n for n, (zid, _, _) in LESSONS.items() if zid in zooms and int(zooms[zid]["start_time"]) <= now]

    students = {}
    for u in data["users"]:
        uid = int(u["id"])
        roles = set((u["roles"] or "").split(","))
        if "student" not in roles or roles & STAFF_ROLES or uid in ADMINS:
            continue
        students[uid] = u

    # участие: (урок, uid) -> {слот: [интервалы]}; непривязанные — по имени
    att = defaultdict(lambda: defaultdict(list))
    stray = defaultdict(lambda: defaultdict(list))
    zoom_lesson = {v[0]: n for n, v in LESSONS.items()}
    for p in data["participants"]:
        n = zoom_lesson[int(p["zoomid"])]
        s = slot_of(int(p["start_time"]))
        iv = (int(p["join_time"]), int(p["leave_time"]))
        if p["userid"]:
            att[(n, int(p["userid"]))][s].append(iv)
        else:
            stray[(n, s)][(p["name"] or "").strip()].append(iv)

    joined, quiz = set(), {}
    quiz_lesson = {v[2]: n for n, v in LESSONS.items()}
    for g in data["grades"]:
        inst, uid = int(g["iteminstance"]), int(g["userid"])
        if g["itemmodule"] == "zoom":
            joined.add((zoom_lesson[inst], uid))
        else:
            quiz[(quiz_lesson[inst], uid)] = float(g["finalgrade"])

    archive_lesson = {v[1]: n for n, v in LESSONS.items()}
    views = {(archive_lesson[int(v["contextinstanceid"])], int(v["userid"])): int(v["n"]) for v in data["views"]}

    levels = json.loads(data["xpconfig"][0]["levelsdata"])
    thresholds = levels["xp"]

    wb = Workbook()
    ws = wb.active
    ws.title = "Студенты"
    head = ["Фамилия Имя", "Email"]
    for n in lessons:
        d = datetime.fromtimestamp(int(zooms[LESSONS[n][0]]["start_time"]), TZ).strftime("%d.%m")
        head += [f"Вебинар {n} ({d}): сессия", f"Вебинар {n}: минут", f"Архив {n}: открывал"]
    head += ["Вебинаров посещено"]
    head += [f"Тест {n} (из 10)" for n in lessons]
    head += ["Баллы опыта (капли)", "Уровень", "Последний вход в курс"]
    ws.append(head)

    green = PatternFill("solid", fgColor="D9EAD3")
    yellow = PatternFill("solid", fgColor="FFF2CC")
    rows = []
    stats = defaultdict(int)
    for uid, u in students.items():
        name = f"{u['lastname']} {u['firstname']}".strip()
        row, fills, visited = [name, u["email"]], [], 0
        for n in lessons:
            slots = att.get((n, uid))
            if slots:
                label = " + ".join(f"{s}:00" for s in sorted(slots))
                mins = union_minutes([iv for ivs in slots.values() for iv in ivs])
                visited += 1
                stats[f"w{n}"] += 1
                for s in slots:
                    stats[f"w{n}s{s}"] += 1
                fill = green
            elif (n, uid) in joined:
                label, mins, fill = "вход из Moodle", None, yellow
                stats[f"w{n}j"] += 1
            else:
                label, mins, fill = "", None, None
            arch = "да" if (n, uid) in views else ""
            if arch:
                stats[f"a{n}"] += 1
                if not slots:
                    stats[f"a{n}only"] += 1
            fills += [(len(row), fill), (len(row) + 1, fill), (len(row) + 2, green if arch else None)]
            row += [label, mins, arch]
        row.append(visited)
        for n in lessons:
            q = quiz.get((n, uid))
            row.append(round(q, 1) if q is not None else None)
            if q is not None:
                stats[f"q{n}"] += 1
        xp = int(u["xp"]) if u["xp"] else 0
        row += [xp, level_of(xp, thresholds), date_str(int(u["lastaccess"])) if u["lastaccess"] else ""]
        rows.append((xp, visited, row, fills))

    rows.sort(key=lambda r: (-r[0], -r[1], r[2][0]))
    for i, (_, _, row, fills) in enumerate(rows, start=2):
        ws.append(row)
        for col, fill in fills:
            if fill:
                ws.cell(i, col + 1).fill = fill

    hdr = Font(bold=True)
    for c in ws[1]:
        c.font = hdr
        c.alignment = Alignment(wrap_text=True, vertical="top")
    ws.row_dimensions[1].height = 45
    ws.freeze_panes = "C2"
    ws.auto_filter.ref = ws.dimensions
    widths = [32, 30] + [16, 9, 9] * len(lessons) + [11] + [9] * len(lessons) + [11, 9, 13]
    for i, w in enumerate(widths, start=1):
        ws.column_dimensions[get_column_letter(i)].width = w

    # Сводка
    sm = wb.create_sheet("Сводка")
    total = len(students)
    sm.append(["Студентов в курсе (активная запись, роль «студент»)", total])
    sm.append([])
    sm.append(["Вебинар", "Был (по отчёту Zoom)", *[f"сессия {s}:00" for s in SLOTS],
               "Нажал «Войти», в отчёте нет", "Zoom: участников без привязки к Moodle",
               "Открывали архив", "Из них не были на вебинаре", "Сдали тест урока"])
    for n in lessons:
        unmatched = sum(len(names) for (ln, _), names in stray.items() if ln == n)
        sm.append([f"Вебинар {n}", stats[f"w{n}"], *[stats[f"w{n}s{s}"] for s in SLOTS],
                   stats[f"w{n}j"], unmatched, stats[f"a{n}"], stats[f"a{n}only"], stats[f"q{n}"]])
    for c in sm[3]:
        c.font = hdr
        c.alignment = Alignment(wrap_text=True, vertical="top")
    sm.column_dimensions["A"].width = 30
    for col in "BCDEFGHIJ":
        sm.column_dimensions[col].width = 14
    xps = [r[0] for r in rows]
    sm.append([])
    sm.append(["Баллы опыта: набрали хоть что-то", sum(1 for x in xps if x > 0)])
    sm.append(["Баллы опыта: максимум", max(xps) if xps else 0])
    sm.append(["Баллы опыта: медиана среди набравших",
               sorted(x for x in xps if x > 0)[len([x for x in xps if x > 0]) // 2] if any(xps) else 0])

    # Непривязанные участники Zoom
    st = wb.create_sheet("Zoom без привязки")
    st.append(["Вебинар", "Сессия", "Имя в Zoom", "Минут"])
    for (n, s), names in sorted(stray.items()):
        for nm, ivs in sorted(names.items(), key=lambda kv: kv[0].lower()):
            st.append([f"Вебинар {n}", f"{s}:00", nm, union_minutes(ivs)])
    for c in st[1]:
        c.font = hdr
    st.column_dimensions["A"].width = 12
    st.column_dimensions["C"].width = 36
    st.freeze_panes = "A2"
    st.auto_filter.ref = st.dimensions

    # Как читать
    lg = wb.create_sheet("Как читать")
    stamp = datetime.now(TZ).strftime("%d.%m.%Y %H:%M")
    for line in [
        f"Выгрузка из edu.kabacademy.com, курс {COURSE}, на {stamp} (время Израиля).",
        "",
        "Вебинар · сессия — в какой сессии (8:00 / 17:00 / 20:00) студент был по отчёту Zoom.",
        "Вебинар · минут — сколько минут был в Zoom (перезаходы склеены, пересечения не задваиваются).",
        "«вход из Moodle» — нажал «Войти» на странице вебинара, но в отчёте Zoom его нет:",
        "   зашёл под другим именем/почтой (тогда он на листе «Zoom без привязки») или не дошёл.",
        "Архив · открывал — открывал страницу «Запись вебинара N (архив)». Сами ролики на YouTube/Drive,",
        "   досмотрел ли — Moodle не знает; это лучшее, что видно.",
        "Тест — оценка за «Проверьте себя: тест по уроку N», из 10.",
        "Баллы опыта — «капли живой воды» блока «Опыт!» за любую активность в курсе; уровень по ним же.",
        "   Капли даются только за действия внутри Moodle. Был на вебинаре, а баллов 0 и «Последний вход» пуст —",
        "   ни разу не входил в Moodle: попал в Zoom по прямой ссылке, отчёт Zoom узнал его по email.",
        "Сортировка — по баллам опыта, затем по числу вебинаров.",
        "",
        "Отчёт Zoom приходит в Moodle с задержкой: сегодняшние сессии появляются после их окончания.",
    ]:
        lg.append([line])
    lg.column_dimensions["A"].width = 110

    wb.move_sheet("Как читать", offset=-3)
    wb.active = 1
    fname = Path(outdir) / f"Курс {COURSE} — вебинары, архив, баллы ({datetime.now(TZ):%Y-%m-%d %H%M}).xlsx"
    wb.save(fname)
    return fname, stats, total, lessons


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("-o", "--outdir", default=".")
    args = ap.parse_args()
    data = {k: query(sql) for k, sql in QUERIES.items()}
    fname, stats, total, lessons = build(data, args.outdir)
    print(fname)
    print("студентов:", total)
    for n in lessons:
        print(f"вебинар {n}:", {k: v for k, v in stats.items() if re.match(rf"[wajq]{n}(\D|$)", k)})


if __name__ == "__main__":
    main()
