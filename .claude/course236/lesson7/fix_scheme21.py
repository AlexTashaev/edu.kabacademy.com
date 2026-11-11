# -*- coding: utf-8 -*-
"""Scheme 21 (L07-03): remove the caption «одна стрелка наружу» under «ВЫБОР СРЕДЫ» from the designer's animation
(owner's doc «правки лекций», repeated 11.11.2026 with a screenshot).

The file is an animated WebP of 49 full-size lossy keyframes with no blending, so the edit is surgical: only the frames
where the caption is visible are decoded, patched and re-encoded; all other frames keep their original bytes. The patch
is the same rectangle taken from frame 0, where the scheme is still an empty grid, so the background is exact.

Usage: python -I fix_scheme21.py
  reads  ~/Downloads/schemes/webp/L07-03.designer.webp (the designer's file; created from L07-03.webp on the first run)
  writes ~/Downloads/schemes/webp/L07-03.webp, then run: python -I make_static.py L07-03
"""
import hashlib
import io
import os
import shutil
import struct
import sys
from PIL import Image, ImageChops

SRC_DIR = os.path.expanduser(r'~/Downloads/schemes/webp')
OUT = os.path.join(SRC_DIR, 'L07-03.webp')
ORIG = os.path.join(SRC_DIR, 'L07-03.designer.webp')
ORIG_MD5 = 'ef81120cf02b647c44c51038e3a656cc'
BOX = (408, 359, 592, 384)   # the caption: text rows 365–375, x 415–583, with a margin; «ВЫБОР СРЕДЫ» ends at y=350, the box «СРЕДА» starts at y=399
QUALITY = 90

if not os.path.exists(ORIG):
    shutil.copyfile(OUT, ORIG)
data = open(ORIG, 'rb').read()
assert hashlib.md5(data).hexdigest() == ORIG_MD5, 'not the designer file'


def chunks(buf, pos, end):
    while pos < end:
        cc, size = buf[pos:pos + 4], struct.unpack('<I', buf[pos + 4:pos + 8])[0]
        yield cc, buf[pos + 8:pos + 8 + size]
        pos += 8 + size + (size & 1)


def chunk(cc, payload):
    return cc + struct.pack('<I', len(payload)) + payload + (b'\0' if len(payload) & 1 else b'')


def decode(vp8):
    """A bare 'VP8 ' chunk is a valid simple WebP file once wrapped in RIFF."""
    body = b'WEBP' + chunk(b'VP8 ', vp8)
    return Image.open(io.BytesIO(b'RIFF' + struct.pack('<I', len(body)) + body)).convert('RGB')


def encode(img):
    buf = io.BytesIO()
    img.save(buf, 'WEBP', quality=QUALITY, method=6)
    subs = list(chunks(buf.getvalue(), 12, len(buf.getvalue())))
    assert [cc for cc, _ in subs] == [b'VP8 '], subs
    return subs[0][1]


top = list(chunks(data, 12, len(data)))
frames = [(i, payload) for i, (cc, payload) in enumerate(top) if cc == b'ANMF']
assert len(frames) == 49
clean = None
patched = []
for n, (i, payload) in enumerate(frames):
    head, subs = payload[:16], list(chunks(payload, 16, len(payload)))
    assert [cc for cc, _ in subs] == [b'VP8 '] and head[:6] == b'\0' * 6, n   # full frame at (0, 0), lossy, no alpha
    img = decode(subs[0][1])
    assert img.size == (800, 509)
    if n == 0:
        clean = img.crop(BOX)
        continue
    diff = ImageChops.difference(img.crop(BOX), clean).convert('L').getextrema()[1]
    if diff <= 12:   # compression noise only: the caption is not on this frame
        continue
    img.paste(clean, BOX[:2])
    top[i] = (b'ANMF', head + chunk(b'VP8 ', encode(img)))
    patched.append(n)

body = b'WEBP' + b''.join(chunk(cc, payload) for cc, payload in top)
out = b'RIFF' + struct.pack('<I', len(body)) + body
open(OUT, 'wb').write(out)
sys.stdout.reconfigure(encoding='utf-8')
print('patched frames', patched, 'bytes', len(data), '->', len(out), 'md5', hashlib.md5(out).hexdigest())

# self-check: same frame count and timings, the caption box equals the clean grid on every frame
a, b = Image.open(ORIG), Image.open(OUT)
assert a.n_frames == b.n_frames == 49
worst = 0
for n in range(49):
    a.seek(n); b.seek(n)
    fa, fb = a.convert('RGB'), b.convert('RGB')
    assert a.info.get('duration') == b.info.get('duration'), n
    worst = max(worst, ImageChops.difference(fb.crop(BOX), clean).convert('L').getextrema()[1])
    if n not in patched:
        assert ImageChops.difference(fa, fb).getbbox() is None, n
print('max difference from the clean grid inside the box:', worst)
