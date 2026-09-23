"""Сборка финальных скриншотов Zoom 7 для страницы «Как здесь всё устроено» (курс 238)."""
import sys
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

S = Path(__file__).parent
OUT = Path(sys.argv[1]) if len(sys.argv) > 1 else S / "out"
OUT.mkdir(exist_ok=True)
RED = (220, 30, 30)


def font(size):
    for name in ("arialbd.ttf", "arial.ttf"):
        try:
            return ImageFont.truetype(name, size)
        except OSError:
            pass
    return ImageFont.load_default()


def num(d, x, y, n, r=17, fs=22):
    d.ellipse((x - r, y - r, x + r, y + r), outline=RED, width=3, fill=(255, 255, 255))
    f = font(fs)
    t = str(n)
    bb = d.textbbox((0, 0), t, font=f)
    d.text((x - (bb[2] - bb[0]) / 2 - bb[0], y - (bb[3] - bb[1]) / 2 - bb[1]), t, fill=RED, font=f)


def arrow(d, x0, y0, x1, y1):
    import math
    d.line((x0, y0, x1, y1), fill=RED, width=5)
    ang = math.atan2(y1 - y0, x1 - x0)
    for s in (-1, 1):
        d.line((x1, y1, x1 - 22 * math.cos(ang + s * 0.45), y1 - 22 * math.sin(ang + s * 0.45)), fill=RED, width=5)


def avatar_placeholder(im, box):
    """Заменяет плитку с фото на нейтральный силуэт."""
    d = ImageDraw.Draw(im)
    x0, y0, x1, y1 = box
    d.rounded_rectangle(box, radius=8, fill=(58, 58, 60))
    cx, cy = (x0 + x1) // 2, (y0 + y1) // 2
    d.ellipse((cx - 16, cy - 30, cx + 16, cy + 2), fill=(170, 170, 175))
    d.pieslice((cx - 32, cy + 4, cx + 32, cy + 60), 180, 360, fill=(170, 170, 175))
    return im


def save(im, name, width=None):
    if width and im.width > width:
        im = im.resize((width, round(im.height * width / im.width)), Image.LANCZOS)
    im.save(OUT / name, optimize=True)
    print(name, im.size)


# 1. Панель вебинара с номерами 1–6 (вид участника из тестовой встречи + контекстное меню из своей).
base = Image.open(S / "m1_meeting.png").convert("RGB")
bg = base.getpixel((1000, 700))                              # цвет фона окна встречи
d = ImageDraw.Draw(base)
d.rectangle((0, 596, 474, 896), fill=bg)                     # убрать диалог проверки камеры
avatar_placeholder(base, (600, 427, 699, 527))
ctx = Image.open(S / "k2_ctx.png").convert("RGB").crop((662, 484, 943, 748))
base.paste(ctx, (662, 484))
d = ImageDraw.Draw(base)
num(d, 53, 890, 1); num(d, 145, 890, 2); num(d, 507, 890, 3); num(d, 615, 890, 4)
num(d, 630, 560, 5); num(d, 1130, 62, 6)
save(base, "zoom-panel.png", 900)

# 2. Меню «Вид».
v = Image.open(S / "k8_view.png").convert("RGB").crop((1150, 4, 1516, 452))
d = ImageDraw.Draw(v)
d.rectangle((1163 - 1150, 52 - 4, 1509 - 1150, 176 - 4), outline=RED, width=3)
save(v, "zoom-view.png")

# 3. Чат: верх панели (вкладки) + низ (кому и поле ввода).
c = Image.open(S / "k4_chat.png").convert("RGB")
top = c.crop((1306, 100, 1660, 182))
bot = c.crop((1306, 790, 1660, 948))
chat = Image.new("RGB", (top.width, top.height + bot.height + 6), (32, 32, 32))
chat.paste(top, (0, 0)); chat.paste(bot, (0, top.height + 6))
d = ImageDraw.Draw(chat)
d.rectangle((1, top.height + 8, chat.width - 2, top.height + 6 + 76), outline=RED, width=3)
save(chat, "zoom-chat.png")

# 4. Диалог «Подключить звук».
z = Image.open(S / "k6_984878.png").convert("RGB")
d = ImageDraw.Draw(z)
arrow(d, 110, 240, 150, 150)
arrow(d, 110, 240, 30, 262)
save(z, "zoom-zvuk.png")

# 5. Настройки → Конференции и вебинары → Подключение.
st = Image.open(S / "z6_meet1.png").convert("RGB")
d = ImageDraw.Draw(st)
d.rectangle((16, 236, 227, 272), outline=RED, width=3)
d.rectangle((252, 170, 782, 288), outline=RED, width=3)
save(st, "zoom-settings.png", 780)
