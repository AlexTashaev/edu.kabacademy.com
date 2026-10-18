import sys, csv, time
sys.stdout.reconfigure(encoding="utf-8")
from datetime import datetime, timedelta, timezone
from zoneinfo import ZoneInfo
TZ = ZoneInfo("Asia/Jerusalem")
rows = [r for r in csv.DictReader(open("dates_raw.tsv", encoding="utf-8"), delimiter="\t") if r["course"] == "236"]
NOW = int(time.time())
todo = []
for r in rows:
    ce = int(r["completionexpected"] or 0)
    if not ce: continue
    d = datetime.fromtimestamp(ce, timezone.utc).astimezone(TZ)
    if d.weekday() != 1: continue  # Tuesday only
    assert (d.hour, d.minute) == (23, 59), r
    w = (d + timedelta(days=1)).date()
    new = int(datetime(w.year, w.month, w.day, 23, 59, tzinfo=TZ).timestamp())
    todo.append((int(r["cmid"]), r["modname"], int(r["instance"]), ce, new, r["name"]))
ids = ",".join(str(t[0]) for t in todo)
sql = [
 "CREATE TABLE _kab_bk20261018_wed_cm AS SELECT id, course, completionexpected FROM mdl_course_modules WHERE course=236 AND id IN (%s);" % ids,
 "CREATE TABLE _kab_bk20261018_wed_event AS SELECT e.* FROM mdl_event e WHERE e.courseid=236 AND e.eventtype='expectcompletionon' AND (" +
   " OR ".join("(e.modulename='%s' AND e.instance=%d)" % (m, i) for _, m, i, _, _, _ in todo) + ");",
]
for cm, m, i, old, new, name in todo:
    sql.append("UPDATE mdl_course_modules SET completionexpected=%d WHERE id=%d AND course=236 AND completionexpected=%d;" % (new, cm, old))
    sql.append("UPDATE mdl_event SET timestart=%d, timesort=%d, timemodified=%d WHERE courseid=236 AND modulename='%s' AND instance=%d AND eventtype='expectcompletionon' AND timestart=%d;" % (new, new, NOW, m, i, old))
sql.append("UPDATE mdl_course SET cacherev=GREATEST(cacherev+1, %d) WHERE id=236;" % NOW)
open("wed.sql", "w", encoding="utf-8", newline="\n").write("\n".join(sql) + "\n")
for cm, m, i, old, new, name in todo:
    f = lambda t: datetime.fromtimestamp(t, timezone.utc).astimezone(TZ).strftime("%a %d.%m %H:%M")
    print(cm, m, name[:40], f(old), "->", f(new))
print(len(todo), "items")
