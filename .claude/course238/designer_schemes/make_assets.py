#!/usr/bin/env python3
"""Схемы дизайнера → файлы для сайта (/kab/img/schemes/).

Дизайнер прислал 27 анимированных схем для уроков 1–12 (30.09.2026) в трёх форматах: WebP, APNG, GIF.
На сайт кладём два файла на схему:
  <код>.webp        — анимация как есть (самый лёгкий формат, понимают все современные браузеры
                      и мобильное приложение Moodle);
  <код>-static.png  — неподвижный кадр с полной картинкой: для «уменьшить движение» и для браузеров
                      без WebP. Берётся самый «заполненный» кадр APNG (у анимаций первый кадр пустой).
GIF не нужен: он в 2–4 раза тяжелее и беднее по цвету.

Запуск:  python make_assets.py [--src DIR] [--codes L01-01 L01-02 …]
По умолчанию — схемы уроков 1 и 2 из C:\\Users\\tashaev\\Downloads\\schemes.
"""
import argparse
import shutil
from pathlib import Path

from PIL import Image

HERE = Path(__file__).parent
REPO = HERE.parents[2]
OUT = REPO / 'public' / 'public' / 'kab' / 'img' / 'schemes'
DEFAULT_SRC = Path(r'C:\Users\tashaev\Downloads\schemes')
DEFAULT_CODES = ['L01-01', 'L01-02', 'L02-01', 'L02-02']


def fullest_frame(apng: Path) -> Image.Image:
    """Кадр, где больше всего нарисовано (анимации начинаются с пустой сетки и в конце гаснут)."""
    im = Image.open(apng)
    best, best_ink = None, -1
    for i in range(im.n_frames):
        im.seek(i)
        frame = im.convert('RGB')
        small = frame.convert('L').resize((200, max(1, frame.height // 4)))
        ink = sum(1 for v in small.get_flattened_data() if v < 235) if hasattr(small, 'get_flattened_data') \
            else sum(1 for v in small.getdata() if v < 235)
        if ink > best_ink:
            best, best_ink = frame.copy(), ink
    return best


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--src', type=Path, default=DEFAULT_SRC)
    ap.add_argument('--codes', nargs='*', default=DEFAULT_CODES)
    args = ap.parse_args()
    OUT.mkdir(parents=True, exist_ok=True)
    for code in args.codes:
        webp, apng = args.src / 'webp' / f'{code}.webp', args.src / 'apng' / f'{code}.png'
        shutil.copyfile(webp, OUT / f'{code}.webp')
        frame = fullest_frame(apng)
        # Картинки плоские, 256 цветов достаточно (APNG дизайнера и так палитровый).
        frame.quantize(colors=256, method=Image.Quantize.MEDIANCUT, dither=Image.Dither.NONE) \
             .save(OUT / f'{code}-static.png', optimize=True)
        w, h = Image.open(webp).size
        print(f'{code}: {w}x{h}  webp {webp.stat().st_size / 1024:.0f} KB, '
              f'static {(OUT / f"{code}-static.png").stat().st_size / 1024:.0f} KB')


if __name__ == '__main__':
    main()
