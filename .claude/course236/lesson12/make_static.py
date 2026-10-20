# -*- coding: utf-8 -*-
"""Static fallback frames for the designer's animated schemes (as for lesson 3):
the WebP frame with the most "ink" (non-background pixels) -> <code>-static.png.

Usage: python -I make_static.py L04-01 L04-02 ...   (reads Downloads/schemes/webp, writes ./schemes)
"""
import os
import sys
from PIL import Image, ImageChops, ImageSequence

SRC = os.path.expanduser(r'~/Downloads/schemes/webp')
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'schemes')
os.makedirs(OUT, exist_ok=True)

for code in sys.argv[1:]:
    im = Image.open(os.path.join(SRC, code + '.webp'))
    frames = [f.convert('RGB') for f in ImageSequence.Iterator(im)]
    bg = frames[0].getpixel((2, 2))
    flat = Image.new('RGB', frames[0].size, bg)

    def ink(f):
        diff = ImageChops.difference(f, flat).convert('L').point(lambda v: 255 if v > 24 else 0)
        return diff.histogram()[255]

    best = max(range(len(frames)), key=lambda i: ink(frames[i]))
    frames[best].save(os.path.join(OUT, code + '-static.png'), optimize=True)
    print(code, im.size, 'frames', len(frames), 'best', best)
