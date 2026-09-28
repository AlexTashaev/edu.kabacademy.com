#!/usr/bin/env python3
"""Анимированные схемы 2 и 3 лекции 1 — те же чертежи из make_svg.py, но с CSS-кадрами.

Геометрия и подписи берутся из make_svg.py (функции schema2_wide и др.): здесь только класс
AnimSvg, который превращает точки подключения at / pulse / draw / mover в анимацию.

Анимация живёт внутри SVG (CSS @keyframes), поэтому работает в обычном <img>: без скриптов,
в мобильном приложении, переживает редактор Moodle. Ограничения <img>: нет кликов и наведения,
старт — при загрузке картинки, поэтому сюжет зациклен с паузой на готовом чертеже.
Если анимация отключена («уменьшить движение», печать, старый браузер) — виден готовый чертёж:
скрытое состояние задано только в ключевых кадрах.

Сюжет схемы 2 (27 с): огонёк спускается по оси нисхождения (зажигаются миры), идёт по оси развития
(появляются четыре природы), останавливается в точке «Наше время», поднимается по тем же ступеням.
Сюжет схемы 3 (23 с): ярусы встают снизу вверх, от каждого вправо выезжает полоса уровня.

С 28.09.2026 эти файлы стоят в уроке: /kab/img/l1/anim/schema{2,3}{,-m}.svg (lesson 1960 в курсе 238
и lesson 1962 в курсе 236). При правке чертежа поднять ?v=N во фрагментах schemas/schema{2,3}.html.
Запуск:  python make_svg_anim.py [--out DIR]   (по умолчанию public/public/kab/img/l1/anim)
"""
import argparse
import sys
from contextlib import contextmanager
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))
import make_svg as M                                                     # noqa: E402
from make_svg import f                                                   # noqa: E402

HIDDEN = {'fade': 'opacity:0', 'rise': 'opacity:0;transform:translateY(9px)',
          'pop': 'opacity:0;transform:scale(.35)', 'grow': 'transform:scaleX(0)'}
SHOWN = {'fade': 'opacity:1', 'rise': 'opacity:1;transform:none',
         'pop': 'opacity:1;transform:none', 'grow': 'transform:none'}
ORIGIN = {'rise': 'center', 'pop': 'center', 'grow': 'left center'}


class AnimSvg(M.Svg):
    def __init__(self, w, h, label, show_w=None, period=None):
        super().__init__(w, h, label, show_w, period)
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

    def draw(self, x1, y1, x2, y2, stroke, sw, t0=0.0, dur=0.0):
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


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--out', type=Path, default=M.OUT / 'anim')
    M.check_layout()
    M.build_all(ap.parse_args().out, AnimSvg)


if __name__ == '__main__':
    main()
