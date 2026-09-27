#!/usr/bin/env python3
"""Схемы 2 и 3 лекции 1 как векторные чертежи (SVG с встроенным подмножеством Montserrat).

Композиция — по двум картинкам, которые выбрал преподаватель (27.09.2026):
  схема 2 — «Замысел творения»: нисхождение в материальный мир справа (миры Адам Кадмон, Ацилут,
            Брия, Ецира, Асия), развитие материального мира по нижней оси справа налево (четыре
            природы и их желания, 6000 лет), подъём по духовным ступеням слева (125 ступеней),
            отметка «Наше время» в левом нижнем углу;
  схема 3 — «Пирамида желаний»: подписи внутри ярусов, вправо от каждого яруса полоса
            «… уровень желаний» со значком природы, знак ∞ над вершиной.
Стиль — курса: палитра темы kabacademy, Montserrat. Цвета четырёх природ идут от синего к
фиолетовому и одинаковы в обеих схемах. От себя добавлено: эпохи истории в полосах пирамиды
(в тексте лекции пирамида показана «снаружи, как история»), пунктир «одни и те же ступени»
между осями, подписи «без участия желания» / «только своим усилием».

Те же чертежи в архиве kabbalahmedia.info: «три оси» — вебинар 18.08.2019 (юнит ZVy2HNMx),
урок «Суть науки каббала» 04.09.2005 (OvROX6Uz).

Зачем SVG-файлы, а не вёрстка: <img> переживает редактор Moodle, purifier и мобильное приложение.
На каждый чертёж два файла: широкий и узкий «-m» (телефон) — их переключает <picture>.

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

# Четыре природы снизу вверх + духовный уровень. Один и тот же цвет в схемах 2 и 3.
# (Стандартные цвета МАК со слайдов: '#E0322B', '#0B8A43', '#F59C1A', '#1565C0', '#2AA6C9'.)
LEVELS = [
    # ключ,    цвет,      природа,        уровень,        желание,            уточнение,                 эпоха,                 годы
    ('stone', '#3C5D90', 'Неживая',      'Неживой',      'Телесные желания', 'пища, кров, секс, семья', 'первобытность',       'до 4 тыс. до н. э.'),
    ('tree',  '#52B0D8', 'Растительная', 'Растительный', 'Богатство',        'деньги, накопление',      'древние цивилизации', 'до V века'),
    ('paw',   '#7C8FD9', 'Животная',     'Животный',     'Почёт и власть',   'признание',               'средневековье',       'V–XV века'),
    ('human', '#9466CC', 'Человеческая', 'Человеческий', 'Знание',           'науки, понимание',        'Новое время',         'XV–XX века'),
    ('inf',   '#A42BB9', 'Духовная',     'Духовный',     'Вопрос о смысле',  'не закрывается ничем',    'наше время',          'вопрос стал массовым'),
]
WORLDS = ['Адам Кадмон', 'Ацилут', 'Брия', 'Ецира', 'Асия']


def tint(hex_color: str, t: float) -> str:
    """Смешать цвет с белым: t — доля белого."""
    r, g, b = (int(hex_color[i:i + 2], 16) for i in (1, 3, 5))
    return '#%02X%02X%02X' % tuple(round(c + (255 - c) * t) for c in (r, g, b))


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

    def text(self, x, y, s, size, weight=500, fill=NAVY, anchor='start', ls=0.0, rotate=None, opacity=None):
        self.used[weight].update(s)
        a = f' text-anchor="{anchor}"' if anchor != 'start' else ''
        l = f' letter-spacing="{f(ls)}"' if ls else ''
        r = f' transform="rotate({rotate} {f(x)} {f(y)})"' if rotate is not None else ''
        o = f' fill-opacity="{f(opacity)}"' if opacity is not None else ''
        esc = s.replace('&', '&amp;').replace('<', '&lt;')
        self.add(f'<text x="{f(x)}" y="{f(y)}" font-size="{f(size)}" font-weight="{weight}" '
                 f'fill="{fill}"{a}{l}{r}{o}>{esc}</text>')

    def block(self, x, y, lines, anchor='start', mask=False, pad=8.0):
        """Несколько строк [(текст, кегль, насыщенность, цвет, трекинг)]; y — базовая линия первой."""
        if mask:                                 # белая подложка, чтобы пунктир не шёл сквозь текст
            ws = [FACES[w].width(s, size, ls) for s, size, w, _, ls in lines]
            width, top = max(ws), y - lines[0][1] * .9
            height = sum(l[1] * 1.32 for l in lines)
            x0 = {'start': x, 'end': x - width, 'middle': x - width / 2}[anchor]
            self.rect(x0 - pad, top - pad * .5, width + pad * 2, height + pad, '#fff')
        for k, (s, size, weight, fill, ls) in enumerate(lines):
            if k:
                y += size * 1.32
            self.text(x, y, s, size, weight, fill, anchor, ls)

    def line(self, x1, y1, x2, y2, stroke, sw=2.0, dash=None):
        d = f' stroke-dasharray="{dash}"' if dash else ''
        self.add(f'<line x1="{f(x1)}" y1="{f(y1)}" x2="{f(x2)}" y2="{f(y2)}" stroke="{stroke}" '
                 f'stroke-width="{f(sw)}" stroke-linecap="round"{d}/>')

    def path(self, d, stroke, sw=3.0, fill='none', dash=None):
        da = f' stroke-dasharray="{dash}"' if dash else ''
        self.add(f'<path d="{d}" stroke="{stroke}" stroke-width="{f(sw)}" fill="{fill}" '
                 f'stroke-linecap="round" stroke-linejoin="round"{da}/>')

    def poly(self, pts, fill, stroke=None, sw=0.0):
        p = ' '.join(f'{f(x)},{f(y)}' for x, y in pts)
        s = f' stroke="{stroke}" stroke-width="{f(sw)}" stroke-linejoin="round"' if stroke else ''
        self.add(f'<polygon points="{p}" fill="{fill}"{s}/>')

    def circle(self, x, y, r, fill, stroke=None, sw=0.0, dash=None):
        s = f' stroke="{stroke}" stroke-width="{f(sw)}"' if stroke else ''
        d = f' stroke-dasharray="{dash}" stroke-linecap="round"' if dash else ''
        self.add(f'<circle cx="{f(x)}" cy="{f(y)}" r="{f(r)}" fill="{fill}"{s}{d}/>')

    def ellipse(self, x, y, rx, ry, fill):
        self.add(f'<ellipse cx="{f(x)}" cy="{f(y)}" rx="{f(rx)}" ry="{f(ry)}" fill="{fill}"/>')

    def rect(self, x, y, w, h, fill, rx=0.0):
        self.add(f'<rect x="{f(x)}" y="{f(y)}" width="{f(w)}" height="{f(h)}" rx="{f(rx)}" fill="{fill}"/>')

    def arrowhead(self, x, y, direction, fill, s=7.5):
        """Остриё в точке (x, y)."""
        pts = {'down': [(0, 0), (-s, -s * 1.9), (s, -s * 1.9)],
               'up': [(0, 0), (-s, s * 1.9), (s, s * 1.9)],
               'left': [(0, 0), (s * 1.9, -s), (s * 1.9, s)]}[direction]
        self.poly([(x + dx, y + dy) for dx, dy in pts], fill, fill, 1.5)

    def infinity(self, x, y, size, fill, opacity=None):
        self.text(x, y + size * .34, '∞', size, 700, fill, 'middle', opacity=opacity)

    def icon(self, kind, x, y, r, col):
        """Значок природы внутри круга радиуса r."""
        k = r / 22
        if kind == 'stone':
            self.ellipse(x - 6.5 * k, y + 5 * k, 7.5 * k, 5 * k, col)
            self.ellipse(x + 7.5 * k, y + 6 * k, 6 * k, 4.2 * k, col)
            self.ellipse(x + 1.5 * k, y - 5 * k, 6.5 * k, 4.8 * k, col)
        elif kind == 'tree':
            self.circle(x, y - 4.5 * k, 9 * k, col)
            self.rect(x - 2 * k, y + 2 * k, 4 * k, 11 * k, col, 1.5 * k)
        elif kind == 'paw':
            self.ellipse(x, y + 5 * k, 7.5 * k, 6 * k, col)
            for dx, dy, rr in ((-8.8, -2, 3), (-3.3, -8, 3.3), (3.3, -8, 3.3), (8.8, -2, 3)):
                self.circle(x + dx * k, y + dy * k, rr * k, col)
        elif kind == 'human':
            self.circle(x, y - 7.5 * k, 4.8 * k, col)
            self.path(f'M{f(x - 8.5 * k)},{f(y + 12 * k)} V{f(y + 6 * k)} Q{f(x - 8.5 * k)},{f(y - .5 * k)} '
                      f'{f(x)},{f(y - .5 * k)} Q{f(x + 8.5 * k)},{f(y - .5 * k)} {f(x + 8.5 * k)},{f(y + 6 * k)} '
                      f'V{f(y + 12 * k)} Z', col, 0, col)
        else:
            self.infinity(x, y - 1 * k, 24 * k, col)

    def render(self) -> str:
        css = ''.join(FACES[w].embed(ch) for w, ch in self.used.items() if ch)
        hh = self.h * self.show_w / self.w
        return (f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {self.w} {self.h}" '
                f'width="{f(self.show_w)}" height="{f(hh)}" role="img" aria-label="{self.label}" '
                f'font-family="KabM, Montserrat, Arial, sans-serif">'
                f'<title>{self.label}</title><style>{css}</style>' + ''.join(self.body) + '</svg>\n')


# ---------------------------------------------------------------- схема 2 · замысел творения
ALT2 = ('Схема 2. Замысел творения: нисхождение в материальный мир через пять миров, '
        'развитие материального мира и подъём по духовным ступеням')


def axes(g: Svg, lx, rx, top, bot, sw, tick, dot):
    """Три оси: нисхождение справа (голубая), развитие справа налево (синяя), подъём слева (фиолетовая).
    Возвращает координаты y четырёх делений между пятью мирами."""
    step = (bot - top) / 5
    ys = [top + step * k for k in range(1, 5)]
    for y in ys:                                             # одни и те же ступени на обеих осях
        g.line(lx + tick + 6, y, rx - tick - 6, y, HAIR, 1.3, '2 6')
    g.line(rx, top, rx, bot - dot - 14, BLUE, sw)
    g.arrowhead(rx, bot - dot - 1, 'down', BLUE)
    g.line(rx, bot, lx + dot + 16, bot, NAVY, sw)
    g.arrowhead(lx + dot + 2, bot, 'left', NAVY)
    g.line(lx, bot, lx, top + 14, PURPLE, sw)
    g.arrowhead(lx, top, 'up', PURPLE)
    for y in ys:
        g.line(lx - tick, y, lx + tick, y, PURPLE, sw * .8)
        g.line(rx - tick, y, rx + tick, y, BLUE, sw * .8)
    g.circle(rx, top, dot, BLUE)
    g.circle(rx, bot, dot, NAVY)
    return ys, step


def our_time(g: Svg, x, y, dot, ring, lx, ly, size, sub_size):
    """Отметка «Наше время»: точка в углу, пунктирный круг, выноска к подписи."""
    w = FACES[700].width('Наше время', size)
    g.rect(lx - 6, ly - size, w + 14, size * 1.2 + sub_size * 1.9, '#fff')
    g.circle(x, y, ring, 'none', PURPLE, 1.6, '0.1 5.2')
    g.path(f'M{f(x + ring * .62)},{f(y - ring * .8)} L{f(lx - 4)},{f(ly + 7)} H{f(lx + w + 6)}',
           PURPLE, 1.6, 'none', '0.1 5.2')
    g.text(lx, ly, 'Наше время', size, 700, PURPLE)
    g.text(lx, ly + 7 + sub_size * 1.35, 'с 1995 года', sub_size, 500, GRAY)
    g.circle(x, y, dot, PURPLE)


def schema2_wide() -> Svg:
    g = Svg(800, 506, ALT2)
    lx, rx, top, bot = 132, 668, 78, 372
    g.text(400, 30, 'ЗАМЫСЕЛ ТВОРЕНИЯ', 17, 700, NAVY, 'middle', 2.4)
    g.infinity(lx, 54, 21, PURPLE)
    g.infinity(rx, 54, 21, BLUE)
    ys, step = axes(g, lx, rx, top, bot, 3.2, 8, 6.5)
    for k, name in enumerate(WORLDS):                        # миры — между делениями оси нисхождения
        y = top + step * (k + .5)
        words = name.upper().split()
        if len(words) == 2:
            g.text(rx + 22, y - 2, words[0], 11, 700, NAVY, 'start', 1.1)
            g.text(rx + 22, y + 12, words[1], 11, 700, NAVY, 'start', 1.1)
        else:
            g.text(rx + 22, y + 4, words[0], 11, 700, NAVY, 'start', 1.1)
    g.text(lx - 22, 104, '125', 23, 700, PURPLE, 'end')
    g.text(lx - 22, 121, 'ступеней', 12, 500, GRAY, 'end')
    g.block(lx + 24, 101, [('Подъём по духовным', 15.5, 700, NAVY, 0), ('ступеням', 15.5, 700, NAVY, 0),
                           ('только своим усилием', 12, 500, GRAY, 0)], mask=True)
    g.block(rx - 24, 101, [('Нисхождение', 15.5, 700, NAVY, 0), ('в материальный мир', 15.5, 700, NAVY, 0),
                           ('без участия желания', 12, 500, GRAY, 0)], 'end', mask=True)
    our_time(g, lx, bot, 7.5, 33, 204, 274, 20, 12.5)
    xs = [588, 472, 356, 240]                                # развитие идёт справа налево
    for x, (kind, col, nature, _, desire, *_rest) in zip(xs, LEVELS):
        g.rect(x - 60, bot - 56, 120, 30, '#fff')
        g.text(x, bot - 43, nature.upper(), 9.5, 700, col, 'middle', .9)
        g.text(x, bot - 31, 'ПРИРОДА', 9.5, 700, col, 'middle', .9)
        g.circle(x, bot, 22, col, '#fff', 3)
        g.icon(kind, x, bot, 22, '#fff')
        g.text(x, bot + 43, desire.replace(' желания', ''), 13.5, 700, col, 'middle')
    g.path('M140,430 q0,13 13,13 H647 q13,0 13,-13', LINE, 1.8, 'none', '0.1 5.4')
    g.text(400, 474, '6000 лет', 21, 700, GRAY, 'middle')
    g.text(400, 495, 'РАЗВИТИЕ МАТЕРИАЛЬНОГО МИРА', 12.5, 700, NAVY, 'middle', 1.3)
    return g


def schema2_narrow() -> Svg:
    g = Svg(340, 626, ALT2, show_w=400)
    lx, rx, top, bot = 50, 290, 66, 304
    g.text(170, 24, 'ЗАМЫСЕЛ ТВОРЕНИЯ', 13.5, 700, NAVY, 'middle', 1.8)
    g.infinity(lx, 46, 17, PURPLE)
    g.infinity(rx, 46, 17, BLUE)
    ys, step = axes(g, lx, rx, top, bot, 2.8, 6.5, 5.5)
    for k, name in enumerate(WORLDS):
        y = top + step * (k + .5)
        w = FACES[700].width(name.upper(), 9.5, .8)
        g.rect(rx - 16 - w - 4, y - 8, w + 8, 16, '#fff')
        g.text(rx - 16, y + 3.5, name.upper(), 9.5, 700, NAVY, 'end', .8)
    g.rect(lx + 12, 78, 66, 30, '#fff')
    g.text(lx + 16, 94, '125', 17, 700, PURPLE)
    g.text(lx + 16, 107, 'ступеней', 10.5, 500, GRAY)
    our_time(g, lx, bot, 6, 25, 96, 226, 15.5, 11)
    xs = [250, 200, 150, 102]
    for x, (kind, col, *_rest) in zip(xs, LEVELS):
        g.circle(x, bot, 15.5, col, '#fff', 2.5)
        g.icon(kind, x, bot, 15.5, '#fff')
    g.path('M58,332 q0,11 11,11 H271 q11,0 11,-11', LINE, 1.6, 'none', '0.1 5')
    g.text(170, 366, '6000 лет', 16, 700, GRAY, 'middle')
    g.text(170, 383, 'РАЗВИТИЕ МАТЕРИАЛЬНОГО МИРА', 10, 700, NAVY, 'middle', 1.1)
    # четыре природы — четыре желания
    g.text(18, 411, 'ЧЕТЫРЕ ПРИРОДЫ И ИХ ЖЕЛАНИЯ', 9.5, 700, GRAY, 'start', 1.3)
    for k, (kind, col, nature, _, desire, *_rest) in enumerate(LEVELS[:4]):
        x, y = 30 + (k % 2) * 158, 436 + (k // 2) * 40
        g.circle(x, y, 12.5, col)
        g.icon(kind, x, y, 12.5, '#fff')
        g.text(x + 20, y - 1.5, nature, 12.5, 700, col)
        g.text(x + 20, y + 13, desire.lower(), 11.5, 500, GRAY)
    # три оси
    rows = [('down', BLUE, 'Нисхождение в материальный мир', 'через пять миров, без участия желания'),
            ('left', NAVY, 'Развитие материального мира', 'от телесных желаний до знания'),
            ('up', PURPLE, 'Подъём по духовным ступеням', '125 ступеней — только своим усилием')]
    for k, (d, col, title, sub) in enumerate(rows):
        y = 522 + k * 39
        if d == 'left':
            g.line(37, y, 25, y, col, 2.6)
            g.arrowhead(19, y, 'left', col, 4.6)
        else:
            y0, y1 = (y - 9, y + 3) if d == 'down' else (y + 9, y - 3)
            g.line(30, y0, 30, y1, col, 2.6)
            g.arrowhead(30, y + 9 if d == 'down' else y - 9, d, col, 4.6)
        g.text(50, y - 1.5, title, 13, 700, NAVY)
        g.text(50, y + 13.5, sub, 11.5, 500, GRAY)
    return g


# ---------------------------------------------------------------- схема 3 · пирамида желаний
ALT3 = ('Схема 3. Пирамида желаний: телесные желания, богатство, почёт и власть, знание '
        'и над ними вопрос о смысле; у каждой ступени — уровень желаний и эпоха')


def pyramid(g: Svg, cx, apex_y, base_y, half, gap, band_to=None):
    """Пять ярусов снизу вверх по LEVELS; при band_to — полосы уровней вправо.
    Возвращает [(y0, y1, середина, правый край на середине)] снизу вверх."""
    th = (base_y - apex_y) / 5
    hw = lambda y: (y - apex_y) / (base_y - apex_y) * half
    out = []
    for k in range(5):                                       # k = 0 — нижний ярус
        y0 = base_y - th * (k + 1) + (gap / 2 if k < 4 else 0)
        y1 = base_y - th * k - (gap / 2 if k else 0)
        mid = (y0 + y1) / 2
        out.append((y0, y1, mid, cx + hw(mid)))
        if band_to:
            g.rect(cx, y0, band_to - cx, y1 - y0, tint(LEVELS[k][1], .80), (y1 - y0) / 2)
    for k, (y0, y1, mid, _) in enumerate(out):
        col = LEVELS[k][1]
        if k == 4:
            g.poly([(cx, y0 + 3), (cx + hw(y1), y1), (cx - hw(y1), y1)], col, col, 3)
        else:
            g.poly([(cx - hw(y0), y0), (cx + hw(y0), y0), (cx + hw(y1), y1), (cx - hw(y1), y1)], col, col, 3)
    return out


def schema3_wide() -> Svg:
    g = Svg(800, 418, ALT3)
    cx, right = 236, 784
    g.infinity(cx, 22, 30, PURPLE)
    tiers = pyramid(g, cx, 46, 404, 216, 6, band_to=right)
    for k, ((y0, y1, mid, edge), (kind, col, _, level, desire, detail, epoch, years)) in enumerate(zip(tiers, LEVELS)):
        r = (y1 - y0) / 2
        g.circle(right - r, mid, r - 3, col)
        g.icon(kind, right - r, mid, r - 3, '#fff')
        lab = right - 2 * r - 14
        if k < 2:                                # внизу полоса короткая — подпись в три строки, как на слайде
            for i, s in enumerate((level.upper(), 'УРОВЕНЬ', 'ЖЕЛАНИЙ')):
                g.text(lab, mid - 9 + i * 13.5, s, 11, 700, col, 'end', .7)
        else:
            g.text(lab, mid - 2, level.upper(), 11, 700, col, 'end', .7)
            g.text(lab, mid + 12, 'УРОВЕНЬ ЖЕЛАНИЙ', 11, 700, col, 'end', .7)
        g.text(edge + 24, mid - 2, epoch, 13, 700, NAVY)
        g.text(edge + 24, mid + 13, years, 11.5, 500, GRAY)
        if k == 0:
            g.text(cx, mid - 1, desire.upper(), 13.5, 700, '#fff', 'middle', .6)
            g.text(cx, mid + 15, detail, 11.5, 500, '#fff', 'middle', opacity=.92)
        elif k == 4:
            g.text(cx, y1 - 20.5, 'ВОПРОС', 8.6, 700, '#fff', 'middle', .2)
            g.text(cx, y1 - 9.5, 'О СМЫСЛЕ', 8.6, 700, '#fff', 'middle', .2)
        else:
            g.text(cx, mid + 5, desire.upper(), 13.5, 700, '#fff', 'middle', .6)
    return g


def schema3_narrow() -> Svg:
    g = Svg(340, 566, ALT3, show_w=400)
    cx = 170
    g.infinity(cx, 17, 24, PURPLE)
    tiers = pyramid(g, cx, 36, 250, 154, 5)
    for k, ((y0, y1, mid, _), lv) in enumerate(zip(tiers, LEVELS)):
        if k == 4:
            g.text(cx, y1 - 9, '?', 15, 700, '#fff', 'middle')
        else:
            g.text(cx, mid + 4, lv[4].upper(), 11, 700, '#fff', 'middle', .5)
    for k, (kind, col, _, level, desire, detail, epoch, years) in enumerate(LEVELS):
        y = 292 + k * 56
        g.circle(30, y, 14, col)
        g.icon(kind, 30, y, 14, '#fff')
        g.text(54, y - 8, desire, 14.5, 700, col)
        g.text(54, y + 8, f'{level.lower()} уровень желаний', 12, 500, NAVY)
        g.text(54, y + 23.5, f'{epoch}, {years}' if k < 4 else f'{epoch} — {years}', 12, 500, GRAY)
    return g


# ---------------------------------------------------------------- сборка
def main():
    OUT.mkdir(parents=True, exist_ok=True)
    for name, g in (('schema2', schema2_wide()), ('schema2-m', schema2_narrow()),
                    ('schema3', schema3_wide()), ('schema3-m', schema3_narrow())):
        svg = g.render()
        (OUT / f'{name}.svg').write_text(svg, encoding='utf-8', newline='\n')
        print(f'{name}.svg  viewBox {g.w}x{g.h}  на странице {f(g.show_w)}x{f(g.h * g.show_w / g.w)}  '
              f'{len(svg.encode()) / 1024:.1f} KB')


if __name__ == '__main__':
    main()
