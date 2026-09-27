#!/usr/bin/env python3
"""Анимированные варианты схем 2 и 3 (прототип, 27.09.2026). На prod не выложены.

Анимация живёт внутри SVG (CSS @keyframes), поэтому работает в обычном <img>: без скриптов,
в мобильном приложении, переживает редактор Moodle. Ограничения <img>: нет кликов и наведения,
старт — при загрузке картинки, поэтому сюжет зациклен с длинной паузой на готовом чертеже.
Если анимация отключена (настройка «уменьшить движение», печать, старый браузер) — виден
готовый чертёж целиком: скрытое состояние задано только в ключевых кадрах.

Сюжет схемы 2: огонёк спускается по оси нисхождения (зажигаются миры), идёт по оси развития
(появляются четыре природы), останавливается в точке «Наше время», поднимается по тем же
ступеням (пунктир связывает ступени двух осей).
Сюжет схемы 3: ярусы встают снизу вверх, от каждого вправо выезжает полоса уровня.

Запуск:  python make_svg_anim.py [--out DIR]   (по умолчанию DIR = svg/_anim, в git не лежит)
"""
import argparse
import sys
from contextlib import contextmanager
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))
import make_svg as M                                                     # noqa: E402
from make_svg import BLUE, GRAY, HAIR, LEVELS, LINE, NAVY, PURPLE, WORLDS, f, tint  # noqa: E402

HIDDEN = {'fade': 'opacity:0', 'rise': 'opacity:0;transform:translateY(9px)',
          'pop': 'opacity:0;transform:scale(.35)', 'grow': 'transform:scaleX(0)'}
SHOWN = {'fade': 'opacity:1', 'rise': 'opacity:1;transform:none',
         'pop': 'opacity:1;transform:none', 'grow': 'transform:none'}
ORIGIN = {'rise': 'center', 'pop': 'center', 'grow': 'left center'}


class ASvg(M.Svg):
    def __init__(self, w, h, label, period, show_w=None):
        super().__init__(w, h, label, show_w)
        self.T, self.css, self.n = period, [], 0

    def pct(self, t: float) -> str:
        return f(max(0.0, min(100.0, t / self.T * 100))) + '%'

    def _rule(self, frames: str, extra: str = '', ease: str = 'cubic-bezier(.2,.7,.3,1)') -> str:
        self.n += 1
        name = f'k{self.n}'
        self.css.append(f'@keyframes {name}{{{frames}}}.{name}{{animation:{name} {f(self.T)}s {ease} infinite both;{extra}}}')
        return name

    def appear(self, t0: float, kind: str = 'fade', dur: float = .6) -> str:
        head = '0%' if t0 <= 0 else f'0%,{self.pct(t0)}'
        frames = f'{head}{{{HIDDEN[kind]}}}{self.pct(t0 + dur)},100%{{{SHOWN[kind]}}}'
        extra = f'transform-box:fill-box;transform-origin:{ORIGIN[kind]}' if kind in ORIGIN else ''
        return self._rule(frames, extra)

    @contextmanager
    def at(self, t0: float, kind: str = 'fade', dur: float = .6):
        self.add(f'<g class="{self.appear(t0, kind, dur)}">')
        yield
        self.add('</g>')

    def draw(self, x1, y1, x2, y2, stroke, sw, t0, dur):
        """Линия, которая прорисовывается от (x1, y1) к (x2, y2)."""
        length = ((x2 - x1) ** 2 + (y2 - y1) ** 2) ** .5
        name = self._rule(f'0%,{self.pct(t0)}{{stroke-dashoffset:{f(length)}}}'
                          f'{self.pct(t0 + dur)},100%{{stroke-dashoffset:0}}', ease='linear')
        self.add(f'<line class="{name}" x1="{f(x1)}" y1="{f(y1)}" x2="{f(x2)}" y2="{f(y2)}" stroke="{stroke}" '
                 f'stroke-width="{f(sw)}" stroke-dasharray="{f(length)}"/>')

    def mover(self, points, r=5.0):
        """Огонёк: points = [(t, x, y, цвет)], между точками движется равномерно."""
        t_in, t_out = points[0][0], points[-1][0]
        pos = [f'0%,{self.pct(t_in)}{{transform:translate({f(points[0][1])}px,{f(points[0][2])}px);opacity:0}}',
               f'{self.pct(t_in + .3)}{{opacity:1}}']
        col = [f'0%,{self.pct(t_in)}{{fill:{points[0][3]}}}']
        for t, x, y, c in points[1:]:
            pos.append(f'{self.pct(t)}{{transform:translate({f(x)}px,{f(y)}px);opacity:1}}')
            col.append(f'{self.pct(t)}{{fill:{c}}}')
        pos.append(f'{self.pct(t_out + .7)},100%{{transform:translate({f(points[-1][1])}px,{f(points[-1][2])}px);opacity:0}}')
        col.append(f'100%{{fill:{points[-1][3]}}}')
        move = self._rule(''.join(pos), ease='linear')
        tone = self._rule(''.join(col), ease='linear')
        self.add(f'<g class="{move}" opacity="0"><circle class="{tone}" r="{f(r * 2.3)}" fill-opacity=".22"/>'
                 f'<circle class="{tone}" r="{f(r)}" stroke="#fff" stroke-width="2"/></g>')

    @contextmanager
    def pulse(self, period=2.6):
        self.n += 1
        name = f'p{self.n}'
        self.css.append(f'@keyframes {name}{{0%,100%{{transform:scale(1);opacity:1}}50%{{transform:scale(1.14);opacity:.5}}}}'
                        f'.{name}{{animation:{name} {f(period)}s ease-in-out infinite;transform-box:fill-box;transform-origin:center}}')
        self.add(f'<g class="{name}">')
        yield
        self.add('</g>')

    def render(self) -> str:
        fonts = ''.join(M.FACES[w].embed(ch) for w, ch in self.used.items() if ch)
        loop = (f'@keyframes loop{{0%{{opacity:0}}1.5%,96.5%{{opacity:1}}100%{{opacity:0}}}}'
                f'.loop{{animation:loop {f(self.T)}s linear infinite both}}')
        calm = '@media (prefers-reduced-motion:reduce){*{animation:none!important}}'
        hh = self.h * self.show_w / self.w
        return (f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {self.w} {self.h}" '
                f'width="{f(self.show_w)}" height="{f(hh)}" role="img" aria-label="{self.label}" '
                f'font-family="KabM, Montserrat, Arial, sans-serif">'
                f'<title>{self.label}</title><style>{fonts}{loop}{"".join(self.css)}{calm}</style>'
                f'<g class="loop">' + ''.join(self.body) + '</g></svg>\n')


# ---------------------------------------------------------------- схема 2 · замысел творения
def our_time(g: ASvg, t0, x, y, dot, ring, lx, ly, size, sub_size):
    w = M.FACES[700].width('Наше время', size)
    with g.at(t0, 'pop', .5):
        with g.pulse():
            g.circle(x, y, ring, 'none', PURPLE, 1.6, '0.1 5.2')
    with g.at(t0 + .5, 'fade', .8):
        g.rect(lx - 6, ly - size, w + 14, size * 1.2 + sub_size * 1.9, '#fff')
        g.path(f'M{f(x + ring * .62)},{f(y - ring * .8)} L{f(lx - 4)},{f(ly + 7)} H{f(lx + w + 6)}',
               PURPLE, 1.6, 'none', '0.1 5.2')
        g.text(lx, ly, 'Наше время', size, 700, PURPLE)
        g.text(lx, ly + 7 + sub_size * 1.35, 'с 1995 года', sub_size, 500, GRAY)
    with g.at(t0, 'pop', .5):
        g.circle(x, y, dot, PURPLE)


def three_axes(g: ASvg, lx, rx, top, bot, sw, tick, dot, D, E, A):
    """Оси с прорисовкой. D, E, A — (начало, конец) нисхождения, развития и подъёма, сек."""
    step = (bot - top) / 5
    ys = [top + step * k for k in range(1, 5)]
    down = lambda y: D[0] + (y - top) / (bot - top) * (D[1] - D[0])
    up = lambda y: A[0] + (bot - y) / (bot - top) * (A[1] - A[0])
    for y in ys:                                  # пунктир «те же ступени» загорается на подъёме
        with g.at(up(y), 'fade', .9):
            g.line(lx + tick + 6, y, rx - tick - 6, y, HAIR, 1.3, '2 6')
    g.draw(rx, top, rx, bot - dot - 14, BLUE, sw, *_span(D))
    with g.at(D[1] - .25, 'pop', .4):
        g.arrowhead(rx, bot - dot - 1, 'down', BLUE)
    g.draw(rx, bot, lx + dot + 16, bot, NAVY, sw, *_span(E))
    with g.at(E[1] - .25, 'pop', .4):
        g.arrowhead(lx + dot + 2, bot, 'left', NAVY)
    g.draw(lx, bot, lx, top + 14, PURPLE, sw, *_span(A))
    with g.at(A[1] - .25, 'pop', .4):
        g.arrowhead(lx, top, 'up', PURPLE)
    for y in ys:
        with g.at(down(y), 'fade', .4):
            g.line(rx - tick, y, rx + tick, y, BLUE, sw * .8)
        with g.at(up(y), 'fade', .4):
            g.line(lx - tick, y, lx + tick, y, PURPLE, sw * .8)
    with g.at(D[0] - .3, 'pop', .4):
        g.circle(rx, top, dot, BLUE)
    with g.at(D[1], 'pop', .4):
        g.circle(rx, bot, dot, NAVY)
    return step, down, up


def _span(p):
    return p[0], p[1] - p[0]


def schema2_wide() -> ASvg:
    g = ASvg(800, 506, M.ALT2, 27)
    lx, rx, top, bot = 132, 668, 78, 372
    D, E, N, A = (1.0, 5.6), (6.0, 13.0), 13.3, (15.6, 21.8)
    g.text(400, 30, 'ЗАМЫСЕЛ ТВОРЕНИЯ', 17, 700, NAVY, 'middle', 2.4)
    g.infinity(lx, 54, 21, PURPLE)
    g.infinity(rx, 54, 21, BLUE)
    step, down, up = three_axes(g, lx, rx, top, bot, 3.2, 8, 6.5, D, E, A)
    for k, name in enumerate(WORLDS):
        y = top + step * (k + .5)
        words = name.upper().split()
        with g.at(down(y), 'fade', .7):
            if len(words) == 2:
                g.text(rx + 22, y - 2, words[0], 11, 700, NAVY, 'start', 1.1)
                g.text(rx + 22, y + 12, words[1], 11, 700, NAVY, 'start', 1.1)
            else:
                g.text(rx + 22, y + 4, words[0], 11, 700, NAVY, 'start', 1.1)
    with g.at(A[0] + 1.2, 'fade', .8):
        g.text(lx - 22, 104, '125', 23, 700, PURPLE, 'end')
        g.text(lx - 22, 121, 'ступеней', 12, 500, GRAY, 'end')
    with g.at(A[0], 'fade', .8):
        g.block(lx + 24, 101, [('Подъём по духовным', 15.5, 700, NAVY, 0), ('ступеням', 15.5, 700, NAVY, 0),
                               ('только своим усилием', 12, 500, GRAY, 0)], mask=True)
    with g.at(D[0] - .4, 'fade', .8):
        g.block(rx - 24, 101, [('Нисхождение', 15.5, 700, NAVY, 0), ('в материальный мир', 15.5, 700, NAVY, 0),
                               ('без участия желания', 12, 500, GRAY, 0)], 'end', mask=True)
    our_time(g, N, lx, bot, 7.5, 33, 204, 274, 20, 12.5)
    xs = [588, 472, 356, 240]
    for x, (kind, col, nature, _, desire, *_rest) in zip(xs, LEVELS):
        t = E[0] + (rx - x) / (rx - lx) * (E[1] - E[0])
        with g.at(t + .25, 'rise', .6):
            g.rect(x - 60, bot - 56, 120, 30, '#fff')
            g.text(x, bot - 43, nature.upper(), 9.5, 700, col, 'middle', .9)
            g.text(x, bot - 31, 'ПРИРОДА', 9.5, 700, col, 'middle', .9)
            g.text(x, bot + 43, desire.replace(' желания', ''), 13.5, 700, col, 'middle')
        with g.at(t, 'pop', .5):
            g.circle(x, bot, 22, col, '#fff', 3)
            g.icon(kind, x, bot, 22, '#fff')
    with g.at(E[0] + .5, 'fade', 1.2):
        g.path('M140,430 q0,13 13,13 H647 q13,0 13,-13', LINE, 1.8, 'none', '0.1 5.4')
        g.text(400, 474, '6000 лет', 21, 700, GRAY, 'middle')
        g.text(400, 495, 'РАЗВИТИЕ МАТЕРИАЛЬНОГО МИРА', 12.5, 700, NAVY, 'middle', 1.3)
    g.mover([(D[0], rx, top, BLUE), (D[1], rx, bot, BLUE), (E[0], rx, bot, NAVY), (E[1], lx, bot, NAVY),
             (A[0], lx, bot, PURPLE), (A[1], lx, top, PURPLE)], 5.5)
    return g


def schema2_narrow() -> ASvg:
    g = ASvg(340, 626, M.ALT2, 27, show_w=400)
    lx, rx, top, bot = 50, 290, 66, 304
    D, E, N, A = (1.0, 5.6), (6.0, 13.0), 13.3, (15.6, 21.8)
    g.text(170, 24, 'ЗАМЫСЕЛ ТВОРЕНИЯ', 13.5, 700, NAVY, 'middle', 1.8)
    g.infinity(lx, 46, 17, PURPLE)
    g.infinity(rx, 46, 17, BLUE)
    step, down, up = three_axes(g, lx, rx, top, bot, 2.8, 6.5, 5.5, D, E, A)
    for k, name in enumerate(WORLDS):
        y = top + step * (k + .5)
        w = M.FACES[700].width(name.upper(), 9.5, .8)
        with g.at(down(y), 'fade', .7):
            g.rect(rx - 16 - w - 4, y - 8, w + 8, 16, '#fff')
            g.text(rx - 16, y + 3.5, name.upper(), 9.5, 700, NAVY, 'end', .8)
    with g.at(A[0] + 1.2, 'fade', .8):
        g.rect(lx + 12, 78, 66, 30, '#fff')
        g.text(lx + 16, 94, '125', 17, 700, PURPLE)
        g.text(lx + 16, 107, 'ступеней', 10.5, 500, GRAY)
    our_time(g, N, lx, bot, 6, 25, 96, 226, 15.5, 11)
    xs = [250, 200, 150, 102]
    times = [E[0] + (rx - x) / (rx - lx) * (E[1] - E[0]) for x in xs]
    for x, t, (kind, col, *_rest) in zip(xs, times, LEVELS):
        with g.at(t, 'pop', .5):
            g.circle(x, bot, 15.5, col, '#fff', 2.5)
            g.icon(kind, x, bot, 15.5, '#fff')
    with g.at(E[0] + .5, 'fade', 1.2):
        g.path('M58,332 q0,11 11,11 H271 q11,0 11,-11', LINE, 1.6, 'none', '0.1 5')
        g.text(170, 366, '6000 лет', 16, 700, GRAY, 'middle')
        g.text(170, 383, 'РАЗВИТИЕ МАТЕРИАЛЬНОГО МИРА', 10, 700, NAVY, 'middle', 1.1)
    with g.at(E[0], 'fade', .8):
        g.text(18, 411, 'ЧЕТЫРЕ ПРИРОДЫ И ИХ ЖЕЛАНИЯ', 9.5, 700, GRAY, 'start', 1.3)
    for k, (t, (kind, col, nature, _, desire, *_rest)) in enumerate(zip(times, LEVELS[:4])):
        x, y = 30 + (k % 2) * 158, 436 + (k // 2) * 40
        with g.at(t + .2, 'rise', .6):
            g.circle(x, y, 12.5, col)
            g.icon(kind, x, y, 12.5, '#fff')
            g.text(x + 20, y - 1.5, nature, 12.5, 700, col)
            g.text(x + 20, y + 13, desire.lower(), 11.5, 500, GRAY)
    rows = [('down', BLUE, D[0], 'Нисхождение в материальный мир', 'через пять миров, без участия желания'),
            ('left', NAVY, E[0], 'Развитие материального мира', 'от телесных желаний до знания'),
            ('up', PURPLE, A[0], 'Подъём по духовным ступеням', '125 ступеней — только своим усилием')]
    for k, (d, col, t, title, sub) in enumerate(rows):
        y = 522 + k * 39
        with g.at(t, 'rise', .7):
            if d == 'left':
                g.line(37, y, 25, y, col, 2.6)
                g.arrowhead(19, y, 'left', col, 4.6)
            else:
                y0, y1 = (y - 9, y + 3) if d == 'down' else (y + 9, y - 3)
                g.line(30, y0, 30, y1, col, 2.6)
                g.arrowhead(30, y + 9 if d == 'down' else y - 9, d, col, 4.6)
            g.text(50, y - 1.5, title, 13, 700, NAVY)
            g.text(50, y + 13.5, sub, 11.5, 500, GRAY)
    g.mover([(D[0], rx, top, BLUE), (D[1], rx, bot, BLUE), (E[0], rx, bot, NAVY), (E[1], lx, bot, NAVY),
             (A[0], lx, bot, PURPLE), (A[1], lx, top, PURPLE)], 4.6)
    return g


# ---------------------------------------------------------------- схема 3 · пирамида желаний
def tiers_geometry(cx, apex_y, base_y, half, gap):
    th = (base_y - apex_y) / 5
    hw = lambda y: (y - apex_y) / (base_y - apex_y) * half
    out = []
    for k in range(5):
        y0 = base_y - th * (k + 1) + (gap / 2 if k < 4 else 0)
        y1 = base_y - th * k - (gap / 2 if k else 0)
        out.append((y0, y1, (y0 + y1) / 2, hw))
    return out


def tier_shape(g, k, cx, y0, y1, hw):
    col = LEVELS[k][1]
    if k == 4:
        g.poly([(cx, y0 + 3), (cx + hw(y1), y1), (cx - hw(y1), y1)], col, col, 3)
    else:
        g.poly([(cx - hw(y0), y0), (cx + hw(y0), y0), (cx + hw(y1), y1), (cx - hw(y1), y1)], col, col, 3)


def schema3_wide() -> ASvg:
    g = ASvg(800, 418, M.ALT3, 23)
    cx, right, t0, dt = 236, 784, .7, 2.1
    tiers = tiers_geometry(cx, 46, 404, 216, 6)
    for k, (y0, y1, mid, hw) in enumerate(tiers):             # полосы — под пирамидой
        with g.at(t0 + k * dt + .5, 'grow', .8):
            g.rect(cx, y0, right - cx, y1 - y0, tint(LEVELS[k][1], .80), (y1 - y0) / 2)
    for k, ((y0, y1, mid, hw), (kind, col, _, level, desire, detail, epoch, years)) in enumerate(zip(tiers, LEVELS)):
        t, r, edge = t0 + k * dt, (y1 - y0) / 2, cx + hw(mid)
        lab = right - 2 * r - 14
        with g.at(t, 'rise', .7):
            tier_shape(g, k, cx, y0, y1, hw)
            if k == 0:
                g.text(cx, mid - 1, desire.upper(), 13.5, 700, '#fff', 'middle', .6)
                g.text(cx, mid + 15, detail, 11.5, 500, '#fff', 'middle', opacity=.92)
            elif k == 4:
                g.text(cx, y1 - 20.5, 'ВОПРОС', 8.6, 700, '#fff', 'middle', .2)
                g.text(cx, y1 - 9.5, 'О СМЫСЛЕ', 8.6, 700, '#fff', 'middle', .2)
            else:
                g.text(cx, mid + 5, desire.upper(), 13.5, 700, '#fff', 'middle', .6)
        with g.at(t + 1.0, 'fade', .7):
            g.text(edge + 24, mid - 2, epoch, 13, 700, NAVY)
            g.text(edge + 24, mid + 13, years, 11.5, 500, GRAY)
            if k < 2:
                for i, s in enumerate((level.upper(), 'УРОВЕНЬ', 'ЖЕЛАНИЙ')):
                    g.text(lab, mid - 9 + i * 13.5, s, 11, 700, col, 'end', .7)
            else:
                g.text(lab, mid - 2, level.upper(), 11, 700, col, 'end', .7)
                g.text(lab, mid + 12, 'УРОВЕНЬ ЖЕЛАНИЙ', 11, 700, col, 'end', .7)
        with g.at(t + 1.2, 'pop', .5):
            g.circle(right - r, mid, r - 3, col)
            g.icon(kind, right - r, mid, r - 3, '#fff')
    with g.at(t0 + 5 * dt, 'pop', .6):
        with g.pulse(3.2):
            g.infinity(cx, 22, 30, PURPLE)
    return g


def schema3_narrow() -> ASvg:
    g = ASvg(340, 566, M.ALT3, 23, show_w=400)
    cx, t0, dt = 170, .7, 2.1
    tiers = tiers_geometry(cx, 36, 250, 154, 5)
    for k, ((y0, y1, mid, hw), lv) in enumerate(zip(tiers, LEVELS)):
        with g.at(t0 + k * dt, 'rise', .7):
            tier_shape(g, k, cx, y0, y1, hw)
            if k == 4:
                g.text(cx, y1 - 9, '?', 15, 700, '#fff', 'middle')
            else:
                g.text(cx, mid + 4, lv[4].upper(), 11, 700, '#fff', 'middle', .5)
    for k, (kind, col, _, level, desire, detail, epoch, years) in enumerate(LEVELS):
        y = 292 + k * 56
        with g.at(t0 + k * dt + .6, 'rise', .7):
            g.circle(30, y, 14, col)
            g.icon(kind, 30, y, 14, '#fff')
            g.text(54, y - 8, desire, 14.5, 700, col)
            g.text(54, y + 8, f'{level.lower()} уровень желаний', 12, 500, NAVY)
            g.text(54, y + 23.5, f'{epoch}, {years}' if k < 4 else f'{epoch} — {years}', 12, 500, GRAY)
    with g.at(t0 + 5 * dt, 'pop', .6):
        with g.pulse(3.2):
            g.infinity(cx, 17, 24, PURPLE)
    return g


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--out', type=Path, default=Path(__file__).parent / '_anim')
    out = ap.parse_args().out
    out.mkdir(parents=True, exist_ok=True)
    for name, g in (('schema2', schema2_wide()), ('schema2-m', schema2_narrow()),
                    ('schema3', schema3_wide()), ('schema3-m', schema3_narrow())):
        svg = g.render()
        (out / f'{name}.svg').write_text(svg, encoding='utf-8', newline='\n')
        print(f'{name}.svg  цикл {f(g.T)} с  {len(svg.encode()) / 1024:.1f} KB  ключевых кадров {len(g.css)}')


if __name__ == '__main__':
    main()
