#!/usr/bin/env python3
"""Собирает SQL, создающий страницу «Как здесь всё устроено» (аккордеон) в курсе 238 на prod.

Транзакция: mdl_page → mdl_course_modules (visible, автозавершение по просмотру) →
mdl_context модуля (чтобы сразу подвесить файлы) → mdl_files (три скриншота Zoom, физические
файлы уже лежат в filedir — переиспользуем contenthash из вводной лекции 17606 курса 236) →
sequence секции (сразу после форума «Объявления») → cacherev курса.

Применять: scp howitworks.sql web-18:/tmp/ && ssh web-18 '/tmp/kab_moodle_sql2.sh "$(cat /tmp/howitworks.sql)"'
"""
from pathlib import Path

HERE = Path(__file__).parent
COURSE, SECTION, MODULE_PAGE = 238, 1767, 15
COURSE_CTX_PATH = '/1/3/152712'          # контекст курса 238
AFTER_CMID = 13367                        # форум «Объявления»
NAME = 'Как здесь всё устроено'

# filename → (contenthash, filesize, mimetype)
FILES = {
    'zoom-zvuk.png':     ('f84c12aed1c14eda6e1bd9ae8756b973b1847af5', 10164, 'image/png'),
    'zoom-settings.png': ('6607fa6298dca28e3a0a64939796a627d90dbed1', 189795, 'image/png'),
    'zoom-panel.png':    ('f32be6afe1a589ccefe41649106ca2dcd933f334', 26798, 'image/png'),
}
EMPTY_SHA1 = 'da39a3ee5e6b4b0d3255bfef95601890afd80709'


def q(s: str) -> str:
    return "'" + s.replace('\\', '\\\\').replace("'", "\\'") + "'"


html = (HERE / 'page.html').read_text(encoding='utf-8').strip()

sql = [
    'START TRANSACTION;',
    f"INSERT INTO mdl_page (course,name,intro,introformat,content,contentformat,legacyfiles,legacyfileslast,display,displayoptions,revision,timemodified) "
    f"VALUES ({COURSE},{q(NAME)},'',1,{q(html)},1,0,NULL,5,{q('a:2:{s:10:\"printintro\";s:1:\"1\";s:17:\"printlastmodified\";s:1:\"0\";}')},1,UNIX_TIMESTAMP());",
    'SET @page := LAST_INSERT_ID();',
    "INSERT INTO mdl_course_modules (course,module,instance,section,idnumber,added,score,indent,visible,visibleoncoursepage,visibleold,groupmode,groupingid,completion,completiongradeitemnumber,completionview,completionexpected,completionpassgrade,showdescription,availability,deletioninprogress,downloadcontent,lang,enableaitools,enabledaiactions) "
    f"VALUES ({COURSE},{MODULE_PAGE},@page,{SECTION},'',UNIX_TIMESTAMP(),0,0,1,1,1,0,0,2,NULL,1,0,0,0,NULL,0,1,'',NULL,NULL);",
    'SET @cm := LAST_INSERT_ID();',
    'INSERT INTO mdl_context (contextlevel,instanceid,path,depth,locked) VALUES (70,@cm,NULL,0,0);',
    'SET @ctx := LAST_INSERT_ID();',
    f"UPDATE mdl_context SET path=CONCAT('{COURSE_CTX_PATH}/',@ctx), depth=4 WHERE id=@ctx;",
]

# Каталог области файлов + сами файлы. pathnamehash = sha1('/ctx/component/filearea/itemid' + filepath + filename)
sql.append(
    "INSERT INTO mdl_files (contenthash,pathnamehash,contextid,component,filearea,itemid,filepath,filename,userid,filesize,mimetype,status,source,author,license,timecreated,timemodified,sortorder,referencefileid) "
    f"VALUES ('{EMPTY_SHA1}',SHA1(CONCAT('/',@ctx,'/mod_page/content/0/.')),@ctx,'mod_page','content',0,'/','.',NULL,0,NULL,0,NULL,NULL,NULL,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),0,NULL);"
)
for fname, (chash, size, mime) in FILES.items():
    sql.append(
        "INSERT INTO mdl_files (contenthash,pathnamehash,contextid,component,filearea,itemid,filepath,filename,userid,filesize,mimetype,status,source,author,license,timecreated,timemodified,sortorder,referencefileid) "
        f"VALUES ('{chash}',SHA1(CONCAT('/',@ctx,'/mod_page/content/0/',{q(fname)})),@ctx,'mod_page','content',0,'/',{q(fname)},NULL,{size},'{mime}',0,{q(fname)},'МАК Онлайн','allrightsreserved',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),0,NULL);"
    )

sql += [
    f"UPDATE mdl_course_sections SET sequence=REPLACE(sequence,'{AFTER_CMID},',CONCAT('{AFTER_CMID},',@cm,',')), timemodified=UNIX_TIMESTAMP() WHERE id={SECTION} AND course={COURSE} AND sequence LIKE '{AFTER_CMID},%';",
    f"UPDATE mdl_course SET cacherev=UNIX_TIMESTAMP() WHERE id={COURSE};",
    'COMMIT;',
    "SELECT @page AS page_id, @cm AS cmid, @ctx AS contextid, (SELECT sequence FROM mdl_course_sections WHERE id=%d) AS sequence;" % SECTION,
]

(HERE / 'howitworks.sql').write_text('\n'.join(sql) + '\n', encoding='utf-8')
print('written', HERE / 'howitworks.sql', len(html), 'chars of html')
