"""Names, texts, dates and visibility for lessons 4–15 of course 236 after build.js.

Usage: python gen_sql.py 4 5 6 ...  -> lessons.sql (run with /tmp/kab_moodle_sqlfile.sh on web-18).
Reads the current layout of each lesson from the DB over ssh (read-only query).
"""
import binascii
import os
import subprocess
import sys
import time
from datetime import datetime, timedelta
from zoneinfo import ZoneInfo

HERE = os.path.dirname(os.path.abspath(__file__))
TZ = ZoneInfo('Asia/Jerusalem')
NOW = int(time.time())
COURSE = 236
SECTION_IDS = {n: 1747 - n for n in range(4, 16)}  # lesson 4 = section 1743 ... lesson 15 = 1732
SUB_NAMES = ['ВЕБИНАР', 'Как устроен урок', '🟢 ОСНОВА',
             '📒 Дополнительно – если есть время на этой неделе', '💠 Факультатив – когда угодно']
# Section-level availability nodeUIDs, as in lesson 2 (sections 1791–1793) and its modules.
UID_OSNOVA, UID_DOP, UID_FAKT = 1789038899420, 1789038924677, 1789038952197
UID_RECORD, UID_FB, UID_SEMINAR = 1788788141539, 1788791814720, 1790871362904


def hx(s):
    return '0x' + binascii.hexlify(s.encode('utf-8')).decode()


def q(s):
    return "'" + s.replace('\\', '\\\\').replace("'", "''") + "'"


def remote_sql(sql):
    """Run a read-only query on web-18 and return rows as lists of strings."""
    tmp = os.path.join(HERE, '_query.sql')
    with open(tmp, 'w', encoding='utf-8', newline='\n') as fh:
        fh.write(sql)
    subprocess.run(['scp', '-q', tmp, 'web-18:/tmp/kab_l415_query.sql'], check=True, stderr=subprocess.DEVNULL)
    out = subprocess.run(['ssh', 'web-18', '/tmp/kab_moodle_sqlfile.sh /tmp/kab_l415_query.sql'],
                         check=True, capture_output=True).stdout.decode('utf-8')
    os.remove(tmp)
    return [line.split('\t') for line in out.splitlines()[1:]]


def ts(dt):
    return int(dt.timestamp())


def dates(n, section_open):
    """Lesson N opens Friday 08:00 Israel time = 09.10.2026 + 7*(N-2) days."""
    day = datetime(2026, 10, 9, 8, 0, tzinfo=TZ) + timedelta(days=7 * (n - 2))
    return {
        # The lesson section itself is already date-locked; never open the content earlier than that.
        'open': max(ts(day), section_open),
        'seminar': ts(day + timedelta(days=4)),
        'expected': ts((day + timedelta(days=5)).replace(hour=23, minute=59)),
        'fb_end': ts((day + timedelta(days=6)).replace(hour=19)),
    }


def avail(op, t, uid):
    return q('{"op":"&","c":[{"type":"date","d":"%s","t":%d,"nodeUID":%d}],"showc":[true]}' % (op, t, uid))


def layout(n):
    """Subsection section ids and their module lists for lesson N, in page order."""
    sid = SECTION_IDS[n]
    rows = remote_sql(
        "SELECT s.name, s.availability, sub.id, sub.sequence, s.sequence "
        "FROM mdl_course_sections s "
        "JOIN mdl_course_modules cm ON FIND_IN_SET(cm.id, s.sequence) "
        "JOIN mdl_modules m ON m.id = cm.module AND m.name = 'subsection' "
        "JOIN mdl_course_sections sub ON sub.component = 'mod_subsection' AND sub.itemid = cm.instance "
        "WHERE s.id = %d ORDER BY FIND_IN_SET(cm.id, s.sequence);" % sid)
    title = rows[0][0].split('. ', 1)[1]
    section_open = int(rows[0][1].split('"t":')[1].split(',')[0])
    subs = [(int(r[2]), [int(x) for x in r[3].split(',') if x]) for r in rows]
    assert len(subs) == 5, (n, subs)
    return title, section_open, subs


def modtypes(cmids):
    rows = remote_sql("SELECT cm.id, m.name, cm.instance FROM mdl_course_modules cm "
                      "JOIN mdl_modules m ON m.id = cm.module WHERE cm.id IN (%s);" % ','.join(map(str, cmids)))
    return {int(r[0]): (r[1], int(r[2])) for r in rows}


PLACEHOLDER = '<div class="kab-page">\n<p><em>Материал урока готовится.</em></p>\n</div>\n'
OTKLIK_INTRO = (
    '<div class="kab-text">\n'
    '<div class="kab-quote">Напишите своими словами ответ на любой из вопросов.</div>\n'
    '<div class="kab-card">\n'
    '<div class="kab-note">Отклики читаем мы и из них собираем блок вопросов для вебинара следующей недели.</div>\n'
    '</div>\n</div>\n')


def lesson_sql(n):
    title, section_open, subs = layout(n)
    d = dates(n, section_open)
    web, how, osnova, dop, fakt = subs
    types = modtypes([c for _, cms in subs for c in cms])

    def find(cms, mod, nth=0):
        hits = [c for c in cms if types[c][0] == mod]
        return hits[nth] if len(hits) > nth else None

    record = find(web[1], 'page')
    label = find(how[1], 'label')
    lesson = find(osnova[1], 'lesson')
    quiz = find(osnova[1], 'quiz')
    resume = find(osnova[1], 'page')
    otklik = find(osnova[1], 'forum', 0)
    feedback = find(osnova[1], 'feedback')
    seminar = find(osnova[1], 'forum', 1) if n <= 5 else None
    diary = find(dop[1], 'url')
    forum = find(dop[1], 'forum')
    fakt_page = find(fakt[1], 'page')
    inst = {c: types[c][1] for c in types}

    s = ['-- Lesson %d. %s (section %d), opens %s' % (
        n, title, SECTION_IDS[n], datetime.fromtimestamp(d['open'], TZ).isoformat())]
    up = s.append

    # Subsections: names as in lesson 2, content opens with the lesson.
    for (secid, _), name in zip(subs, SUB_NAMES):
        up("UPDATE mdl_course_sections SET name=%s, timemodified=%d WHERE id=%d;" % (q(name), NOW, secid))
    for (secid, _), uid in zip((osnova, dop, fakt), (UID_OSNOVA, UID_DOP, UID_FAKT)):
        up("UPDATE mdl_course_sections SET availability=%s WHERE id=%d;" % (avail('>=', d['open'], uid), secid))

    # ВЕБИНАР: recording page for this lesson.
    up("UPDATE mdl_page SET name=%s, content=REPLACE(content, %s, %s), timemodified=%d WHERE id=%d;" % (
        q('Запись вебинара %d (архив)' % n), q('Вебинар 2 «Развитие желаний»'),
        q('Вебинар %d «%s»' % (n, title)), NOW, inst[record]))
    up("UPDATE mdl_course_modules SET availability=%s WHERE id=%d;" % (avail('>=', d['open'], UID_RECORD), record))

    # Как устроен урок.
    up("UPDATE mdl_label SET name='Как устроен этот урок', timemodified=%d WHERE id=%d;" % (NOW, inst[label]))

    # ОСНОВА. Lesson-specific content does not exist yet: lecture, test and summary stay hidden.
    lname = 'Лекция %d. %s' % (n, title)
    up("UPDATE mdl_lesson SET name=%s, available=%d, activitylink=%d, timemodified=%d WHERE id=%d;" % (
        q(lname), d['open'], otklik, NOW, inst[lesson]))
    up("UPDATE mdl_lesson_pages SET contents=%s, timemodified=%d, title=CASE "
       "WHEN title LIKE 'Глава 1.%%' THEN 'Глава 1' WHEN title LIKE 'Глава 2.%%' THEN 'Глава 2' "
       "WHEN title LIKE 'Глава 3.%%' THEN 'Глава 3' WHEN title LIKE 'О чём%%' THEN 'О чём этот урок' "
       "ELSE title END WHERE lessonid=%d;" % (hx(PLACEHOLDER), NOW, inst[lesson]))
    qname = 'Проверьте себя: Тест по уроку %d' % n
    up("UPDATE mdl_quiz SET name=%s, timemodified=%d WHERE id=%d;" % (q(qname), NOW, inst[quiz]))
    up("UPDATE mdl_grade_items SET itemname=%s, timemodified=%d WHERE courseid=%d AND itemmodule='quiz' AND iteminstance=%d;" % (
        q(qname), NOW, COURSE, inst[quiz]))
    up("UPDATE mdl_page SET name=%s, content=%s, timemodified=%d WHERE id=%d;" % (
        q('Резюме урока %d' % n), hx(PLACEHOLDER), NOW, inst[resume]))
    up("UPDATE mdl_course_modules SET visible=0, visibleold=0 WHERE id IN (%d,%d,%d);" % (lesson, quiz, resume))

    # Отклик недели: questions are posted as discussions by the teacher, intro is generic.
    up("UPDATE mdl_forum SET name=%s, intro=%s, timemodified=%d WHERE id=%d;" % (
        q('Отклик недели %d: вопрос на выбор' % n), hx(OTKLIK_INTRO), NOW, inst[otklik]))
    up("UPDATE mdl_course_modules SET completionexpected=%d WHERE id IN (%d,%d,%d);" % (
        d['expected'], lesson, quiz, otklik))

    # Вопрос к вебинару (feedback; local_kabfeedbackgdoc picks it up by the word «Вопрос»).
    up("UPDATE mdl_feedback SET name=%s, timemodified=%d WHERE id=%d;" % (
        q('Вопрос по теме урока %d к вебинару с преподавателями' % n), NOW, inst[feedback]))
    up("UPDATE mdl_feedback_item SET name=%s WHERE feedback=%d AND typ='textarea';" % (
        q('Ваш вопрос по теме урока %d' % n), inst[feedback]))
    up("UPDATE mdl_course_modules SET availability=%s WHERE id=%d;" % (avail('<', d['fb_end'], UID_FB), feedback))

    if seminar:
        up("UPDATE mdl_forum SET name=%s, timemodified=%d WHERE id=%d;" % (
            q('Семинар урока %d (в записи)' % n), NOW, inst[seminar]))
        up("UPDATE mdl_course_modules SET availability=%s WHERE id=%d;" % (
            avail('>=', d['seminar'], UID_SEMINAR), seminar))

    # Дополнительно и Факультатив.
    up("UPDATE mdl_url SET name='Мой Дневник', timemodified=%d WHERE id=%d;" % (NOW, inst[diary]))
    fname = 'Форум: обсуждение темы Урока %d' % n
    up("UPDATE mdl_forum SET name=%s, timemodified=%d WHERE id=%d;" % (q(fname), NOW, inst[forum]))
    up("UPDATE mdl_grade_items SET itemname=%s, timemodified=%d WHERE courseid=%d AND itemmodule='forum' AND iteminstance=%d;" % (
        q(fname + ' за весь форум'), NOW, COURSE, inst[forum]))
    up("UPDATE mdl_page SET name='Тематические дни', timemodified=%d WHERE id=%d;" % (NOW, inst[fakt_page]))

    # Calendar: copies carry lesson-2 names and dates.
    for cm, mod, name in ((lesson, 'lesson', lname), (quiz, 'quiz', qname),
                          (otklik, 'forum', 'Отклик недели %d: вопрос на выбор' % n)):
        up("UPDATE mdl_event SET name=%s, timestart=%d, timesort=%d, visible=%d, timemodified=%d "
           "WHERE courseid=%d AND modulename='%s' AND instance=%d AND eventtype='expectcompletionon';" % (
               q(name + ' должно быть выполнено'), d['expected'], d['expected'], 0 if cm in (lesson, quiz) else 1,
               NOW, COURSE, mod, inst[cm]))
    up("UPDATE mdl_event SET name=%s, timestart=%d, timesort=%d, visible=0, timemodified=%d "
       "WHERE courseid=%d AND modulename='lesson' AND instance=%d AND eventtype='open';" % (
           q(lname + ' открывается'), d['open'], d['open'], NOW, COURSE, inst[lesson]))
    return s


def main():
    lessons = [int(a) for a in sys.argv[1:]]
    sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
    for n in lessons:
        assert 4 <= n <= 15, 'lessons 1–3 are out of scope'
        sql += lesson_sql(n)
    sql += ['UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=%d;' % COURSE, 'COMMIT;']
    out = os.path.join(HERE, 'lessons.sql')
    with open(out, 'w', encoding='utf-8', newline='\n') as fh:
        fh.write('\n'.join(sql) + '\n')
    print('lessons', lessons, 'statements', len(sql))


if __name__ == '__main__':
    main()
