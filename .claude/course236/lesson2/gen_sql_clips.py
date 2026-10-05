import os, re, time, binascii
os.chdir(r'C:\Users\tashaev\AppData\Local\Temp\kabsc2')
NOW = int(time.time())
BASE = 'https://files.kabbalahmedia.info/kabacademy/MAK/'
# archive uid -> filer clip name (all 16:9)
CLIPS = {'hAhjSri2': 'K5', 'Y8OsAcsO': 'K2', 'z4iWxFzZ': 'K3', 'IxILX00M': 'K1', 'MbJFs3rI': 'K4'}
PAGES = {17896: 'p_intro', 17894: 'p_ch1', 17895: 'p_ch2', 17897: 'p_ch3'}

def video(code):
    return ('<div class="kab-clip" style="border-left: 3px solid #4a90a4; background: #f2f8fa; padding: 12px 16px; margin: 20px 0;">\n'
            '<video class="nomediaplugin" controls="controls" controlslist="nodownload" preload="metadata" playsinline="playsinline" oncontextmenu="return false;" '
            'poster="%sLesson-02-clip-%s.jpg" style="display: block; width: 100%%; aspect-ratio: 16 / 9; background: #000;">'
            '<source src="%sLesson-02-clip-%s.mp4" type="video/mp4"></video>\n</div>' % (BASE, code, BASE, code))

def hx(s):
    return '0x' + binascii.hexlify(s.encode('utf-8')).decode()

sql = ['SET NAMES utf8mb4;', 'START TRANSACTION;']
box_re = re.compile(r'<div class="kab-clip-link"[^>]*>.*?</div>\n', re.S)
for pid, name in PAGES.items():
    src = open('l2html/%s.html' % name, encoding='utf-8').read()
    def repl(m):
        block = m.group(0)
        uid = re.search(r'lessons/cu/([A-Za-z0-9]+)', block).group(1)
        return video(CLIPS[uid]) + '\n'
    out, n = box_re.subn(repl, src)
    open('l2html/%s.html' % name, 'w', encoding='utf-8', newline='\n').write(out)
    print(name, 'replaced', n, 'boxes; remaining links:', out.count('lessons/cu/'))
    sql.append("UPDATE mdl_lesson_pages SET contents=%s, timemodified=%d WHERE id=%d AND lessonid=1963;" % (hx(out.strip() + '\n'), NOW, pid))
sql += ['COMMIT;', 'SELECT id,title,LENGTH(contents) clen FROM mdl_lesson_pages WHERE lessonid=1963 ORDER BY id;']
open('lesson2_clips.sql', 'w', encoding='utf-8', newline='\n').write('\n'.join(sql) + '\n')
print('sql bytes', os.path.getsize('lesson2_clips.sql'))
