# Значки курсов (Moodle Badges)

Единая серия значков в стиле kabacademy.com: градиентный диск (небесный `#52B0D8` → синий `#3C5D90` → фиолетовый `#A42BB9`), белые линейные иконки, без подписей.

| Файл | Курс | Иконка |
|---|---|---|
| `badge_part1` | Классическая каббала · Часть 1 | дерево с логотипа kabacademy.com (`svg-symbols.svg#logo-icon-white`) |
| `badge_part2` | Классическая каббала · Часть 2 | сфирот в кольце со стрелой снизу — мотив прежнего значка |
| `badge_essay` | Работа с первоисточниками / Эссе | лист и перо |
| `badge_worlds` | Спецкурс «Система мироздания» | четыре мира + луч |
| `badge_book` | Тайны Вечной Книги | книга под лучами |

Сборка: `python gen.py` пишет `badge_*.svg` и `preview.html`; PNG 512×512 с прозрачным фоном — headless Edge
(`msedge --headless=new --default-background-color=00000000 --window-size=512,512 --screenshot=… badge_x.svg`).
В Moodle грузить `png/badge_*.png` через форму значка (Badges → Edit details → Image); SVG форма не принимает.
