"""Собирает insert_draft.sql: приветствие курса 236 как «черновик» в «Объявлениях».

Черновиков у форума Moodle нет, поэтому пост кладётся с датой начала показа
(timestart) 2030-01-01: студенты его не видят, cron не рассылает
(cron_task::get_unmailed_posts требует d.timestart < now). Админ открывает пост,
в «Период отображения» снимает «Отображать с» и сохраняет — пост появляется,
письма уходят с ближайшим cron (mailed=0, d.timestart попадает в окно).
"""
import re
from pathlib import Path

HERE = Path(__file__).parent
COURSE, FORUM, AUTHOR = 236, 3726, 12  # «Объявления» курса 236, автор «МАК Онлайн»
DRAFT_TIMESTART = 1893456000  # 2030-01-01
SUBJECT = 'Приветствуем вас на курсе «Классическая каббала. Часть первая»! С чего начать'


def q(x):
    return "'" + x.replace('\\', '\\\\').replace("'", "\\'").replace('\n', '\\n') + "'"


msg = (HERE / 'post.html').read_text(encoding='utf-8')
assert '<!--' not in msg, 'в тексте остались плейсхолдеры'
text = re.sub(r'<[^>]+>', ' ', msg)
words, chars = len(text.split()), len(re.sub(r'<[^>]+>', '', msg))

sql = f"""START TRANSACTION;
SET @now=UNIX_TIMESTAMP();
INSERT INTO mdl_forum_discussions (course,forum,name,firstpost,userid,groupid,assessed,timemodified,usermodified,timestart,timeend,pinned,timelocked)
VALUES ({COURSE},{FORUM},{q(SUBJECT)},0,{AUTHOR},-1,1,@now,{AUTHOR},{DRAFT_TIMESTART},0,0,0);
SET @d=LAST_INSERT_ID();
INSERT INTO mdl_forum_posts (discussion,parent,userid,created,modified,mailed,subject,message,messageformat,messagetrust,attachment,totalscore,mailnow,deleted,privatereplyto,wordcount,charcount)
VALUES (@d,0,{AUTHOR},@now,@now,0,{q(SUBJECT)},{q(msg)},1,0,'',0,0,0,0,{words},{chars});
UPDATE mdl_forum_discussions SET firstpost=LAST_INSERT_ID() WHERE id=@d;
COMMIT;
SELECT d.id,d.name,FROM_UNIXTIME(d.timestart) ts,p.id pid,p.mailed FROM mdl_forum_discussions d JOIN mdl_forum_posts p ON p.id=d.firstpost WHERE d.id=@d;
"""
(HERE / 'insert_draft.sql').write_text(sql, encoding='utf-8', newline='\n')
print('insert_draft.sql:', len(sql), 'bytes')
