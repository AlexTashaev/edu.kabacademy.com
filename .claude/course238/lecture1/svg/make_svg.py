#!/usr/bin/env python3
"""Схемы 2 и 3 лекции 1 как векторные чертежи (SVG с встроенным подмножеством Montserrat).

Зачем SVG-файлы, а не вёрстка: чертёж «три оси» и пирамида — это рисунки, а не списки;
<img> переживает редактор Moodle, purifier и мобильное приложение без потерь.
На каждый чертёж два файла: широкий (десктоп) и узкий «-m» (телефон) — их переключает <picture>.

Источники (архив kabbalahmedia.info и материалы курса):
  схема 2 — чертёж «три оси» Михаэля Лайтмана, вебинар «Путь достижения Высшей цели», 18.08.2019
            (юнит ZVy2HNMx, чертежи pic01–pic11), урок «Суть науки каббала» 04.09.2005 (OvROX6Uz),
            слайд 8 презентации вебинара «Урок 1» («Три состояния · три оси развития»);
  схема 3 — слайд 9 той же презентации («Пирамида желаний»), пирамида из «02 Развитие желаний»,
            таблица «Эпоха / Уровень / Доминирующее желание» из урока 2.
Подписи ступеней — словами текста лекции: названия миров и «125 ступеней» из чертежей архива
сюда сознательно не вынесены (в тексте механика ступеней отложена до урока 4).

Запуск:  python make_svg.py            → public/public/kab/img/l1/schema{2,3}{,-m}.svg
Шрифты берутся из _fonts/ (в git не лежат), при отсутствии скачиваются с edu.kabacademy.com/kab/fonts/.
"""
import base64
import io
import urllib.request
from pathlib import Path

from fontTools import subset
from fontTools.ttLib import TTFont

HERE = Path(__file__).parent
REPO = HERE.parents[3]
OUT = REPO / 'public' / 'public' / 'kab' / 'img' / 'l1'
FONT_DIR = HERE / '_fonts'
FONT_URL = 'https://edu.kabacademy.com/kab/fonts/Montserrat-{}.woff2'
WEIGHTS = {500: 'Medium', 700: 'Bold'}

NAVY, BLUE, PURPLE = '#3C5D90', '#52B0D8', '#A42BB9'
GRAY, LINE, HAIR = '#7A7A7A', '#C9DDEA', '#D9E8F2'
TIERS = ['#A42BB9', '#DCEDF6', '#A9D3E8', '#52B0D8', '#3C5D90']       # сверху вниз
TIER_TEXT = ['#fff', NAVY, NAVY, '#fff', '#fff']


# ---------------------------------------------------------------- шрифты
class Face:
    def __init__(self, weight: int):
        FONT_DIR.mkdir(exist_ok=True)
        self.path = FONT_DIR / f'Montserrat-{WEIGHTS[weight]}.woff2'
        if not self.path.exists():
            urllib.request.urlretrieve(FONT_URL.format(WEIGHTS[weight]), self.path)
        self.weight = weight
        tt = TTFont(self.path)
        self.cmap, self.hmtx, self.upm = tt.getBestCmap(), tt['hmtx'].metrics, tt['head'].unitsPerEm

    def has(self, ch: str) -> bool:
        return ord(ch) in self.cmap

    def width(self, s: str, size: float, ls: float = 0) -> float:
        adv = sum(self.hmtx[self.cmap[ord(c)]][0] if ord(c) in self.cmap else self.upm * .6 for c in s)
        return adv * size / self.upm + ls * len(s)

    def embed(self, chars: set) -> str:
        opts = subset.Options()
        opts.flavor = 'woff2'
        opts.layout_features = ['kern']
        opts.notdef_outline = True
        tt = TTFont(self.path)
        sub = subset.Subsetter(opts)
        sub.populate(text=''.join(sorted(chars)))
        sub.subset(tt)
        tt.flavor = 'woff2'
        buf = io.BytesIO()
        tt.save(buf)
        b64 = base64.b64encode(buf.getvalue()).decode()
        return (f"@font-face{{font-family:KabM;font-weight:{self.weight};"
                f"src:url(data:font/woff2;base64,{b64}) format('woff2')}}")


FACES = {w: Face(w) for w in WEIGHTS}


# ---------------------------------------------------------------- примитивы
def f(v: float) -> str:
    return f'{v:.2f}'.rstrip('0').rstrip('.')


class Svg:
    def __init__(self, w: int, h: int, label: str, show_w: int | None = None):
        self.w, self.h, self.label = w, h, label
        self.show_w = show_w or w            # «родной» размер картинки на странице
        self.body, self.used = [], {500: set(), 700: set()}

    def add(self, s: str):
        self.body.append(s)

    def text(self, x, y, s, size, weight=500, fill=NAVY, anchor='start', ls=0.0, rotate=None):
        self.used[weight].update(s)
        a = f' text-anchor="{anchor}"' if anchor != 'start' else ''
        l = f' letter-spacing="{f(ls)}"' if ls else ''
        r = f' transform="rotate({rotate} {f(x)} {f(y)})"' if rotate is not None else ''
        esc = s.replace('&', '&amp;').replace('<', '&lt;')
        self.add(f'<text x="{f(x)}" y="{f(y)}" font-size="{f(size)}" font-weight="{weight}" '
                 f'fill="{fill}"{a}{l}{r}>{esc}</text>')

    def line(self, x1, y1, x2, y2, stroke, sw=2.0, dash=None):
        d = f' stroke-dasharray="{dash}"' if dash else ''
        self.add(f'<line x1="{f(x1)}" y1="{f(y1)}" x2="{f(x2)}" y2="{f(y2)}" stroke="{stroke}" '
                 f'stroke-width="{f(sw)}" stroke-linecap="round"{d}/>')

    def path(self, d, stroke, sw=3.0, fill='none'):
        self.add(f'<path d="{d}" stroke="{stroke}" stroke-width="{f(sw)}" fill="{fill}" '
                 f'stroke-linecap="round" stroke-linejoin="round"/>')

    def poly(self, pts, fill, stroke=None, sw=0.0):
        p = ' '.join(f'{f(x)},{f(y)}' for x, y in pts)
        s = f' stroke="{stroke}" stroke-width="{f(sw)}" stroke-linejoin="round"' if stroke else ''
        self.add(f'<polygon points="{p}" fill="{fill}"{s}/>')

    def circle(self, x, y, r, fill, stroke=None, sw=0.0):
        s = f' stroke="{stroke}" stroke-width="{f(sw)}"' if stroke else ''
        self.add(f'<circle cx="{f(x)}" cy="{f(y)}" r="{f(r)}" fill="{fill}"{s}/>')

    def rect(self, x, y, w, h, fill, rx=0.0):
        self.add(f'<rect x="{f(x)}" y="{f(y)}" width="{f(w)}" height="{f(h)}" rx="{f(rx)}" fill="{fill}"/>')

    def chevron(self, x, y, direction, stroke, s=6.0, sw=2.2):
        pts = {'down': [(-s, -s * .6), (0, s * .55), (s, -s * .6)],
               'up': [(-s, s * .6), (0, -s * .55), (s, s * .6)],
               'right': [(-s * .6, -s), (s * .55, 0), (-s * .6, s)]}[direction]
        d = 'M' + ' L'.join(f'{f(x + dx)},{f(y + dy)}' for dx, dy in pts)
        self.path(d, stroke, sw)

    def badge(self, x, y, n, fill, r=11.0, size=12.5):
        self.circle(x, y, r, fill)
        self.text(x, y + size * .36, str(n), size, 700, '#fff', 'middle')

    def label_on_line(self, x, y, s, size, fill=NAVY, pad=10.0):
        w = FACES[500].width(s, size)
        self.rect(x - w / 2 - pad, y - size * .85, w + pad * 2, size * 1.7, '#fff')
        self.text(x, y + size * .35, s, size, 500, fill, 'middle')

    def infinity(self, x, y, size, fill):
        if FACES[700].has('∞'):
            self.text(x, y + size * .34, '∞', size, 700, fill, 'middle')
        else:                                   # знака нет в шрифте — рисуем лемнискату
            a = size * .52
            d = (f'M{f(x)},{f(y)} C{f(x + a * .45)},{f(y - a * .7)} {f(x + a)},{f(y - a * .55)} {f(x + a)},{f(y)} '
                 f'S{f(x + a * .45)},{f(y + a * .7)} {f(x)},{f(y)} '
                 f'S{f(x - a)},{f(y - a * .55)} {f(x - a)},{f(y)} S{f(x - a * .45)},{f(y + a * .7)} {f(x)},{f(y)}Z')
            self.path(d, fill, size * .13)

    def render(self) -> str:
        css = ''.join(FACES[w].embed(ch) for w, ch in self.used.items() if ch)
        hh = self.h * self.show_w / self.w
        return (f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {self.w} {self.h}" '
                f'width="{f(self.show_w)}" height="{f(hh)}" role="img" aria-label="{self.label}" '
                f'font-family="KabM, Montserrat, Arial, sans-serif">'
                f'<title>{self.label}</title><style>{css}</style>' + ''.join(self.body) + '</svg>\n')


# ---------------------------------------------------------------- схема 2 · три оси
STEPS = ['слито с источником', 'начинает отделяться', 'ощущает себя отдельным', 'не чувствует источник']
EVOLUTION = ['неживое', 'растительное', 'животное', 'человек']
ALT2 = ('Схема 2. Три оси развития: нисхождение от источника сверху вниз, '
        'развитие в нашем мире, подъём снизу вверх по тем же ступеням')


def three_axes(g: Svg, lx, rx, top, bot, rad, steps_y, sw=3.0):
    """П-образный контур: ось 1 вниз (голубая), ось 2 вправо (синяя), ось 3 вверх (фиолетовая)."""
    g.path(f'M{f(lx)},{f(top)} V{f(bot - rad)} Q{f(lx)},{f(bot)} {f(lx + rad)},{f(bot)}', BLUE, sw)
    g.path(f'M{f(lx + rad)},{f(bot)} H{f(rx - rad)}', NAVY, sw)
    g.path(f'M{f(rx - rad)},{f(bot)} Q{f(rx)},{f(bot)} {f(rx)},{f(bot - rad)} V{f(top + 13)}', PURPLE, sw)
    g.poly([(rx, top + 1), (rx - 7, top + 15), (rx + 7, top + 15)], PURPLE, PURPLE, 1.5)
    for y in steps_y:                                        # одни и те же ступени на обеих осях
        g.line(lx + 14, y, rx - 14, y, LINE, 1.3, '2 6')
        g.line(lx - 7, y, lx + 7, y, NAVY, 2.4)
        g.line(rx - 7, y, rx + 7, y, NAVY, 2.4)


def schema2_wide() -> Svg:
    g = Svg(760, 400, ALT2)
    lx, rx, top, bot, rad, cx = 190, 570, 58, 302, 24, 380
    steps_y = [114, 158, 202, 246]
    g.rect(lx - 36, 14, rx - lx + 72, 44, NAVY, 22)
    g.infinity(lx, 36, 21, '#fff')
    g.infinity(rx, 36, 21, '#fff')
    g.text(cx, 41.5, 'Источник — сила отдачи', 15.5, 700, '#fff', 'middle')
    three_axes(g, lx, rx, top, bot, rad, steps_y)
    g.text(cx, 88, 'ОДНИ И ТЕ ЖЕ СТУПЕНИ', 10.5, 700, GRAY, 'middle', 1.6)
    for y, s in zip(steps_y, STEPS):
        g.label_on_line(cx, y, s, 13)
    for y in (136, 180, 224, 270):
        g.chevron(lx, y, 'down', BLUE)
        g.chevron(rx, y, 'up', PURPLE)
    g.badge(lx, 86, 1, BLUE)
    g.badge(rx, 92, 3, PURPLE)
    g.badge(lx + 44, bot, 2, NAVY)
    ex = [286, 366, 444, 504]
    for x, s in zip(ex, EVOLUTION):
        g.line(x, bot - 6, x, bot + 6, NAVY, 2.4)
        g.text(x, bot + 25, s, 11.5, 500, GRAY, 'middle')
    for x in (326, 405, 474):
        g.chevron(x, bot, 'right', NAVY)
    g.circle(536, bot, 8.5, '#fff', PURPLE, 2.6)
    g.circle(536, bot, 3.6, PURPLE)
    g.text(536, bot - 17, 'мы здесь', 12.5, 700, PURPLE, 'middle')
    # подписи осей
    g.text(lx - 28, 168, 'Нисхождение', 16, 700, NAVY, 'end')
    g.text(lx - 28, 187, 'сверху вниз', 12.5, 700, BLUE, 'end')
    g.text(lx - 28, 208, 'без участия желания,', 12, 500, GRAY, 'end')
    g.text(lx - 28, 224, 'по закону', 12, 500, GRAY, 'end')
    g.text(rx + 28, 168, 'Подъём', 16, 700, NAVY)
    g.text(rx + 28, 187, 'снизу вверх', 12.5, 700, PURPLE)
    g.text(rx + 28, 208, 'по тем же ступеням,', 12, 500, GRAY)
    g.text(rx + 28, 224, 'только своим усилием', 12, 500, GRAY)
    g.text(cx, 364, 'Развитие в нашем мире', 16, 700, NAVY, 'middle')
    g.text(cx, 383, 'эволюция природы и история человечества', 12, 500, GRAY, 'middle')
    return g


def schema2_narrow() -> Svg:
    g = Svg(340, 474, ALT2, show_w=400)
    lx, rx, top, bot, rad, cx = 46, 294, 56, 296, 20, 170
    steps_y = [110, 152, 194, 236]
    g.rect(14, 14, 312, 42, NAVY, 21)
    g.infinity(lx, 35, 18, '#fff')
    g.infinity(rx, 35, 18, '#fff')
    g.text(cx, 40, 'Источник — сила отдачи', 13.5, 700, '#fff', 'middle')
    three_axes(g, lx, rx, top, bot, rad, steps_y, 2.8)
    g.text(cx, 88, 'ОДНИ И ТЕ ЖЕ СТУПЕНИ', 9.5, 700, GRAY, 'middle', 1.4)
    for y, s in zip(steps_y, STEPS):
        g.label_on_line(cx, y, s, 13, pad=8)
    for y in (131, 173, 215, 258):
        g.chevron(lx, y, 'down', BLUE, 5.5)
        g.chevron(rx, y, 'up', PURPLE, 5.5)
    g.badge(lx, 82, 1, BLUE, 10, 11.5)
    g.badge(rx, 88, 3, PURPLE, 10, 11.5)
    g.badge(lx + 38, bot, 2, NAVY, 10, 11.5)
    for x in (118, 152, 186, 220):
        g.line(x, bot - 5.5, x, bot + 5.5, NAVY, 2.2)
    g.circle(252, bot, 8, '#fff', PURPLE, 2.5)
    g.circle(252, bot, 3.4, PURPLE)
    g.text(252, bot - 16, 'мы здесь', 12.5, 700, PURPLE, 'middle')
    arrow = ' → ' if FACES[500].has('→') else ' · '
    g.text(cx, bot + 25, arrow.join(EVOLUTION), 11, 500, GRAY, 'middle')
    rows = [(1, BLUE, 'Нисхождение — сверху вниз', 'без участия желания, по закону'),
            (2, NAVY, 'Развитие в нашем мире', 'эволюция природы и история человечества'),
            (3, PURPLE, 'Подъём — снизу вверх', 'по тем же ступеням, только своим усилием')]
    for k, (n, col, title, sub) in enumerate(rows):
        y = 356 + k * 42
        g.badge(28, y, n, col, 10, 11.5)
        g.text(48, y - 1, title, 14, 700, NAVY)
        g.text(48, y + 15.5, sub, 12, 500, GRAY)
    return g


# ---------------------------------------------------------------- схема 3 · пирамида желаний
# (заголовок, эпоха, уровень природы) — сверху вниз
PYRAMID = [('Вопрос о смысле', 'наше время — уже у сотен миллионов', 'не закрывается ничем из перечисленного'),
           ('Знание', 'Новое время, XV–XX века', 'человеческий уровень'),
           ('Почёт и власть', 'Средневековье, V–XV века', 'животный уровень'),
           ('Богатство', 'Древние цивилизации, до V века', 'растительный уровень'),
           ('Телесные желания', 'первобытность · пища, кров, семья', 'неживой уровень')]
MARKS = ['?', '4', '3', '2', '1']
ALT3 = ('Схема 3. Пирамида желаний: телесные желания, богатство, почёт и власть, знание '
        'и над ними вопрос о смысле')


def pyramid(g: Svg, cx, apex_y, base_y, half, gap, num_size):
    """Пять ярусов; возвращает [(y_середины, правый_край_на_середине)] сверху вниз."""
    th = (base_y - apex_y) / 5
    hw = lambda y: (y - apex_y) / (base_y - apex_y) * half
    mids = []
    for k in range(5):
        y0 = apex_y + th * k + (gap / 2 if k else 0)
        y1 = apex_y + th * (k + 1) - (gap / 2 if k < 4 else 0)
        col = TIERS[k]
        if k == 0:
            g.poly([(cx, y0 + 3), (cx + hw(y1), y1), (cx - hw(y1), y1)], col, col, 3)
            g.text(cx, y1 - th * .2, MARKS[k], num_size * 1.12, 700, TIER_TEXT[k], 'middle')
        else:
            g.poly([(cx - hw(y0), y0), (cx + hw(y0), y0), (cx + hw(y1), y1), (cx - hw(y1), y1)], col, col, 3)
            g.text(cx, (y0 + y1) / 2 + num_size * .36, MARKS[k], num_size, 700, TIER_TEXT[k], 'middle')
        mid = (y0 + y1) / 2 + (th * .16 if k == 0 else 0)
        mids.append((mid, cx + hw(mid)))
    return mids


def growth_arrow(g: Svg, x, y_from, y_to, size):
    g.line(x, y_from, x, y_to + 12, LINE, 2)
    g.poly([(x, y_to), (x - 5.5, y_to + 13), (x + 5.5, y_to + 13)], LINE, LINE, 1.5)
    g.text(x - 8, (y_from + y_to) / 2, 'желание растёт', size, 500, GRAY, 'middle', .5, rotate=-90)


def schema3_wide() -> Svg:
    g = Svg(800, 372, ALT3)
    growth_arrow(g, 30, 346, 40, 11.5)
    mids = pyramid(g, 236, 36, 346, 178, 7, 15.5)
    tx = 452
    g.text(tx, 20, 'ЖЕЛАНИЕ · УРОВЕНЬ ПРИРОДЫ · ЭПОХА', 10, 700, GRAY, 'start', 1.5)
    for k, ((mid, edge), (title, epoch, level)) in enumerate(zip(mids, PYRAMID)):
        g.line(edge + 9, mid, tx - 14, mid, HAIR, 1.5)
        g.circle(edge + 9, mid, 2.6, LINE)
        g.text(tx, mid - 3, title, 16, 700, PURPLE if k == 0 else NAVY)
        if k:                                   # уровень природы — плашкой рядом с заголовком
            px = tx + FACES[700].width(title, 16) + 12
            pw = FACES[500].width(level, 11) + 18
            g.rect(px, mid - 17.5, pw, 20, '#EEF6FB', 10)
            g.text(px + 9, mid - 3.6, level, 11, 500, NAVY)
        g.text(tx, mid + 16, epoch, 12.5, 500, GRAY)
    return g


def schema3_narrow() -> Svg:
    g = Svg(340, 530, ALT3, show_w=400)
    growth_arrow(g, 22, 214, 24, 10.5)
    pyramid(g, 178, 14, 214, 130, 6, 13)
    for k in range(5):                                  # список — по росту желания: 1, 2, 3, 4, ?
        i = 4 - k
        title, epoch, level = PYRAMID[i]
        y = 246 + k * 57
        g.add(f'<rect x="16" y="{f(y)}" width="26" height="26" rx="7" fill="{TIERS[i]}"/>')
        g.text(29, y + 17.5, MARKS[i], 12.5, 700, TIER_TEXT[i], 'middle')
        g.text(54, y + 12, title, 14.5, 700, PURPLE if i == 0 else NAVY)
        g.text(54, y + 28.5, epoch, 12, 500, GRAY)
        g.text(54, y + 44, level, 12, 500, GRAY)
    return g


# ---------------------------------------------------------------- сборка
def main():
    OUT.mkdir(parents=True, exist_ok=True)
    for name, g in (('schema2', schema2_wide()), ('schema2-m', schema2_narrow()),
                    ('schema3', schema3_wide()), ('schema3-m', schema3_narrow())):
        svg = g.render()
        (OUT / f'{name}.svg').write_text(svg, encoding='utf-8', newline='\n')
        print(f'{name}.svg  {g.w}x{g.h}  {len(svg.encode()) / 1024:.1f} KB')
    print('∞ в шрифте:', FACES[700].has('∞'), '| → в шрифте:', FACES[500].has('→'))
    # контроль ширины подписей пирамиды на десктопе
    for title, epoch, level in PYRAMID[1:]:
        w = FACES[500].width(f'{epoch} · {level}', 12.5)
        print(f'  {w:6.1f}px  {epoch} · {level}')


if __name__ == '__main__':
    main()
