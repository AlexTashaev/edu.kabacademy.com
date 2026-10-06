import os, re, time, binascii
os.chdir(r'C:\Users\tashaev\AppData\Local\Temp\kabsc2')
NOW = int(time.time())

def hx(s):
    return '0x' + binascii.hexlify(s.encode('utf-8')).decode()

def rd(fn):
    return open(fn, encoding='utf-8').read()

def wr(fn, s):
    open(fn, 'w', encoding='utf-8', newline='\n').write(s)

def must_replace(s, old, new, label):
    assert s.count(old) == 1, 'expected exactly one occurrence for %s, got %d' % (label, s.count(old))
    return s.replace(old, new)

# ---- TOC page 17893 ----
toc = rd('l2html/p_toc.html')
toc = must_replace(toc, '<p>Вводная часть и три главы: как устроено наполнение, кто управляет желаниями, желание нейтрально. 15–20 минут чтения.</p>\n', '', 'toc description')
toc = must_replace(toc, '<p>Три вопроса. Держите их в голове, пока читаете.</p>', '<p>Три вопроса для размышления.</p>', 'toc three questions')
wr('l2html/p_toc.html', toc)

# ---- intro page 17896 ----
intro = rd('l2html/p_intro.html')
start = intro.index('<h4>О чём этот урок</h4>')
end = intro.index('<h4>Четыре уровня природы')
new_intro = '''<h4>О чём этот урок</h4>
<p>У любого нашего влечения — от базового голода до жажды признания — есть общие свойства. Они одинаковы для ребёнка и взрослого, для пещерного человека и нашего современника.</p>
<p>Изучив эти свойства, вы получите не просто набор сведений, а рабочую оптику. Многое из того, что раньше списывалось на усталость, характер или невезение, окажется лишь механикой работы желания.</p>
<p>Предупредим сразу, чтобы избежать завышенных ожиданий: <strong>эта оптика даёт понимание, а не «ремонтный комплект».</strong> Станет ясно, почему очередная покупка не приносит счастья. Но рецепта, который заставит её радовать, вы здесь не найдёте.</p>
<h4>Развитие идёт ступенями</h4>
<p>Мы не рождаемся сразу двадцатилетними. Сначала нам год, потом два, потом три — и каждая новая ступень включает в себя предыдущие.</p>
<p>В школе одиннадцать классов, а не один класс длиной в одиннадцать лет. И дело не в удобстве расписания: каждый следующий уровень вбирает в себя предыдущий и добавляет нечто новое. Пропустить ступень нельзя — следующей просто не на чем будет стоять.</p>
<p>Желания растут точно так же. Внутри человеческой жизни они сменяют друг друга по порядку: базовые нужды (пища, секс, семья); затем богатство; следом почёт и власть; далее знание; и, наконец, то, что обнаруживается за пределами знания.</p>
<p>Переход между ними — <strong>качественный, а не количественный.</strong> Желание богатства невозможно насытить едой. Жажду почёта не утолить деньгами. Стремление к знанию не закрыть властью. Сколько ни добавляй на своей ступени, следующая от этого не откроется.</p>
'''
old_block = intro[start:end]
assert 'Lesson-02-clip-K5' in old_block, 'K5 clip expected inside replaced block'
intro = intro[:start] + new_intro + intro[end:]
assert 'Lesson-02-clip-K5' not in intro
wr('l2html/p_intro.html', intro)

# ---- summary page 2483 ----
res = rd('l2b/t2_2466_c64.html')
res = must_replace(res, 'Наслаждение живёт в промежутке между нехваткой и наполнением; наполненное желание исчезает.', 'Наслаждение существует в промежутке между нехваткой и наполнением; наполненное желание исчезает.', 'res1')
res = must_replace(res, 'Желания не под контролем человека и приходят от окружения.', 'Желания не контролируются человеком и приходят от окружения.', 'res3')
res = must_replace(res, 'Действуют на желание только наслаждение и страдание; уменьшенное желание меньше ранит.', 'Влияют на желание только наслаждение и страдание; уменьшенное желание меньше ранит.', 'res4')
res = must_replace(res, 'Одно желание не подчиняется этим свойствам, потому что не наполняется получением, — оно и привело вас сюда.', 'Развитие желания произошло, когда желание не подчинилось, потому что не наполнилось получением, — оно и привело вас сюда.', 'res8')
wr('l2html/page_resume.html', res)

sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
sql.append("UPDATE mdl_lesson_pages SET contents=%s, timemodified=%d WHERE id=17893 AND lessonid=1963;" % (hx(toc.strip() + '\n'), NOW))
sql.append("UPDATE mdl_lesson_pages SET contents=%s, timemodified=%d WHERE id=17896 AND lessonid=1963;" % (hx(intro.strip() + '\n'), NOW))
sql.append("UPDATE mdl_page SET content=%s, timemodified=%d WHERE id=2483;" % (hx(res.strip() + '\n'), NOW))
sql.append("UPDATE mdl_question SET questiontext=%s, name='У2-01 Где существует наслаждение', timemodified=%d WHERE id IN (68758,68759);" % (hx('<p>Где существует наслаждение?</p>\n'), NOW))
sql.append("UPDATE mdl_question SET questiontext=%s, name='У2-06 Чем отличается заключительное желание', timemodified=%d WHERE id=68765;" % (hx('<p>Чем заключительное желание отличается от всех предыдущих?</p>\n'), NOW))
sql += ['COMMIT;', "SELECT id,title,LENGTH(contents) clen,timemodified FROM mdl_lesson_pages WHERE id IN (17893,17896); SELECT id,name,LEFT(questiontext,70) q FROM mdl_question WHERE id IN (68758,68759,68765); SELECT id,LENGTH(content) FROM mdl_page WHERE id=2483;"]
wr('lesson2_edits.sql', '\n'.join(sql) + '\n')
print('ok', os.path.getsize('lesson2_edits.sql'))
