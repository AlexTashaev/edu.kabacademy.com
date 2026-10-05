import os, time, binascii
os.chdir(r'C:\Users\tashaev\AppData\Local\Temp\kabsc2')
NOW = int(time.time())

def hx(s):
    return '0x' + binascii.hexlify(s.encode('utf-8')).decode()

def f(name):
    return open(name, encoding='utf-8').read().strip() + '\n'

def q(s):
    return "'" + s.replace(chr(92), chr(92) * 2).replace("'", "''") + "'"

# Dates (UTC epoch; Israel = UTC+3 in October 2026)
T_OPEN = 1791522000      # 2026-10-09 08:00 Israel — lesson content opens
T_SEMINAR = 1791867600   # 2026-10-13 08:00 Israel — seminar forum opens
T_EXP_L = 1791925140     # 2026-10-13 23:59 Israel — lesson + otklik expected
T_EXP_Q = 1792011540     # 2026-10-14 23:59 Israel — quiz expected
T_FB_END = 1792080000    # 2026-10-15 19:00 Israel — feedback closes (before webinar 3)

sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']

# ---- lesson 1963 ----
sql.append("UPDATE mdl_lesson SET name='Лекция 2. Развитие желаний', available=%d, activitylink=13469, timemodified=%d WHERE id=1963;" % (T_OPEN, NOW))
pages = {
    17892: ('*', 'l2html/p_map.html'),
    17893: ('Содержание', 'l2html/p_toc.html'),
    17896: ('О чём этот урок и почему развитие идёт ступенями', 'l2html/p_intro.html'),
    17894: ('Глава 1. Как устроено наполнение', 'l2html/p_ch1.html'),
    17895: ('Глава 2. Кто управляет желаниями', 'l2html/p_ch2.html'),
}
for pid, (title, fn) in pages.items():
    sql.append("UPDATE mdl_lesson_pages SET title=%s, contents=%s, timemodified=%d WHERE id=%d AND lessonid=1963;" % (q(title), hx(f(fn)), NOW, pid))
# new page: chapter 3 after 17895
sql.append("INSERT INTO mdl_lesson_pages (lessonid,prevpageid,nextpageid,qtype,qoption,layout,display,timecreated,timemodified,title,contents,contentsformat) "
           "SELECT lessonid,17895,0,qtype,qoption,layout,display,%d,%d,%s,%s,contentsformat FROM mdl_lesson_pages WHERE id=17895;" % (NOW, NOW, q('Глава 3. Желание нейтрально'), hx(f('l2html/p_ch3.html'))))
sql.append("SET @np = LAST_INSERT_ID();")
sql.append("UPDATE mdl_lesson_pages SET nextpageid=@np WHERE id=17895;")
# chapter 2 (17895): second answer becomes 'Продолжить' -> next page
sql.append("UPDATE mdl_lesson_answers SET answer='Продолжить', jumpto=-1, timemodified=%d WHERE id=37226 AND pageid=17895;" % NOW)
sql.append("INSERT INTO mdl_lesson_answers (lessonid,pageid,jumpto,grade,score,flags,timecreated,timemodified,answer,answerformat,response,responseformat) VALUES "
           "(1963,@np,-40,0,0,0,%d,%d,'Вернуться',0,NULL,0),(1963,@np,-9,0,0,0,%d,%d,'Завершить',0,NULL,0);" % (NOW, NOW, NOW, NOW))

# ---- quiz 1236 + questions ----
sql.append("UPDATE mdl_quiz SET name='Проверьте себя: Тест по уроку 2', timemodified=%d WHERE id=1236;" % NOW)
sql.append("UPDATE mdl_grade_items SET itemname='Проверьте себя: Тест по уроку 2', timemodified=%d WHERE id=5362 AND iteminstance=1236;" % NOW)
# source questions (course 238 quiz 1233) -> target question ids (copies of lesson-1 questions)
src_q = [68736, 68737, 68738, 68739, 68740, 68741]
src_ans = {68736: [283944, 283945, 283946, 283947], 68737: [283948, 283949, 283950, 283951], 68738: [283952, 283953, 283954, 283955],
           68739: [283956, 283957, 283958, 283959], 68740: [283960, 283961, 283962, 283963], 68741: [283964, 283965, 283966, 283967]}
src_names = {68736: 'У2-01 Где живёт наслаждение', 68737: 'У2-02 Что происходит с наполненным желанием', 68738: 'У2-03 Почему желания растут',
             68739: 'У2-04 Откуда берутся желания', 68740: 'У2-05 Что делает желание эгоистичным', 68741: 'У2-06 Чем отличается последнее желание'}
dst_q = {68736: [68758, 68759], 68737: [68760], 68738: [68761, 68762], 68739: [68763], 68740: [68764], 68741: [68765]}
dst_ans = {68758: [284032, 284033, 284034, 284035], 68759: [284036, 284037, 284038, 284039], 68760: [284040, 284041, 284042, 284043],
           68761: [284044, 284045, 284046, 284047], 68762: [284048, 284049, 284050, 284051], 68763: [284052, 284053, 284054, 284055],
           68764: [284056, 284057, 284058, 284059], 68765: [284060, 284061, 284062, 284063]}
for sq in src_q:
    qt = f('l2b/t5_%d_qt64.html' % sq)
    for dq in dst_q[sq]:
        sql.append("UPDATE mdl_question SET name=%s, questiontext=%s, timemodified=%d WHERE id=%d;" % (q(src_names[sq]), hx(qt), NOW, dq))
        for sa, da in zip(src_ans[sq], dst_ans[dq]):
            a = f('l2b/t6_%d_a64.html' % sa)
            fb = f('l2b/t6_%d_f64.html' % sa)
            sql.append("UPDATE mdl_question_answers SET answer=%s, feedback=%s WHERE id=%d AND question=%d;" % (hx(a), hx(fb), da, dq))

# ---- pages ----
sql.append("UPDATE mdl_page SET name='Резюме урока 2', content=%s, timemodified=%d WHERE id=2483;" % (hx(f('l2b/t2_2466_c64.html')), NOW))
sql.append("UPDATE mdl_page SET name='Запись вебинара 2 (архив)', content=%s, timemodified=%d WHERE id=2485;" % (hx(f('l2html/page_record.html')), NOW))

# ---- forums ----
sql.append("UPDATE mdl_forum SET name='Отклик недели 2: вопрос на выбор', intro=%s, timemodified=%d WHERE id=3899;" % (hx(f('l2html/forum_otklik.html')), NOW))
sql.append("UPDATE mdl_forum SET name='Семинар урока 2 (в записи)', timemodified=%d WHERE id=3900;" % NOW)
sql.append("UPDATE mdl_forum SET name='Форум: обсуждение темы Урока 2', timemodified=%d WHERE id=3901;" % NOW)
sql.append("UPDATE mdl_grade_items SET itemname='Форум: обсуждение темы Урока 2 за весь форум', timemodified=%d WHERE id=5363 AND iteminstance=3901;" % NOW)

# ---- feedback ----
sql.append("UPDATE mdl_feedback SET name='Вопрос по теме урока 2 к вебинару с преподавателями', intro=%s, timemodified=%d WHERE id=35;" % (hx(f('l2html/feedback_intro.html')), NOW))
sql.append("UPDATE mdl_feedback_item SET name='Ваш вопрос по теме урока 2' WHERE id=171 AND feedback=35;")

# ---- course modules: dates & availability ----
def avail(op, t, uid):
    return q('{"op":"&","c":[{"type":"date","d":"%s","t":%d,"nodeUID":%d}],"showc":[true]}' % (op, t, uid))
sql.append("UPDATE mdl_course_modules SET completionexpected=%d WHERE id IN (13466,13469);" % T_EXP_L)
sql.append("UPDATE mdl_course_modules SET completionexpected=%d WHERE id=13467;" % T_EXP_Q)
sql.append("UPDATE mdl_course_modules SET availability=%s WHERE id=13470;" % avail('<', T_FB_END, 1788791814720))
sql.append("UPDATE mdl_course_modules SET availability=%s WHERE id=13471;" % avail('>=', T_SEMINAR, 1790871362904))
sql.append("UPDATE mdl_course_modules SET availability=%s WHERE id=13481;" % avail('>=', T_OPEN, 1788788141539))
sql.append("UPDATE mdl_course_sections SET availability=%s, timemodified=%d WHERE id=1791;" % (avail('>=', T_OPEN, 1789038899420), NOW))
sql.append("UPDATE mdl_course_sections SET availability=%s, timemodified=%d WHERE id=1792;" % (avail('>=', T_OPEN, 1789038924677), NOW))
sql.append("UPDATE mdl_course_sections SET availability=%s, timemodified=%d WHERE id=1793;" % (avail('>=', T_OPEN, 1789038952197), NOW))

# ---- calendar events ----
sql.append("UPDATE mdl_event SET name='Отклик недели 2: вопрос на выбор должно быть выполнено', timestart=%d, timesort=%d, timemodified=%d WHERE id=22882 AND instance=3899;" % (T_EXP_L, T_EXP_L, NOW))
sql.append("UPDATE mdl_event SET name='Лекция 2. Развитие желаний должно быть выполнено', timestart=%d, timesort=%d, timemodified=%d WHERE id=22880 AND instance=1963;" % (T_EXP_L, T_EXP_L, NOW))
sql.append("UPDATE mdl_event SET name='Лекция 2. Развитие желаний открывается', timestart=%d, timesort=%d, timemodified=%d WHERE id=22879 AND instance=1963;" % (T_OPEN, T_OPEN, NOW))
sql.append("UPDATE mdl_event SET name='Проверьте себя: Тест по уроку 2 должно быть выполнено', timestart=%d, timesort=%d, timemodified=%d WHERE id=22881 AND instance=1236;" % (T_EXP_Q, T_EXP_Q, NOW))

# ---- course cache ----
sql.append("UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;")
sql.append('COMMIT;')
sql.append("SELECT @np AS new_ch3_pageid;")

open('lesson2.sql', 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
print('statements:', len(sql), 'bytes:', os.path.getsize('lesson2.sql'))
