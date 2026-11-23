<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_kabdarkmode;

/**
 * Настройки, значок в шапке и скрипты переключателя оформления.
 *
 * Выбор хранится только в браузере (localStorage, ключ kabdm-mode). Скрипт в <head> ставит
 * data-bs-theme и data-kabdm-mode на <html> до отрисовки, скрипт в подвале обслуживает меню
 * и пересчитывает режим «Авто» по часам устройства.
 *
 * @package    local_kabdarkmode
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ui {
    /** @var string[] Режимы в порядке пунктов меню. */
    public const MODES = ['light', 'dark', 'auto'];

    /** @var string Ключ localStorage. */
    public const STORAGE_KEY = 'kabdm-mode';

    /** @var string[] Значки (24×24, цвет — currentColor). */
    private const ICONS = [
        'light' => '<circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M4.6 4.6l1.4 1.4M18 18l1.4 1.4'
            . 'M2.5 12h2M19.5 12h2M4.6 19.4 6 18M18 6l1.4-1.4"/>',
        'dark' => '<path d="M20.5 14.3A8.5 8.5 0 1 1 9.7 3.5a6.8 6.8 0 0 0 10.8 10.8z"/>',
        'auto' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 3.5a8.5 8.5 0 0 1 0 17z" fill="currentColor"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
    ];

    /**
     * Включён ли переключатель.
     *
     * @return bool
     */
    public static function enabled(): bool {
        global $CFG;
        if (during_initial_install() || !empty($CFG->upgraderunning)) {
            return false;
        }
        $value = get_config('local_kabdarkmode', 'enabled');
        // Настройки ещё не сохранены — значение по умолчанию: включён.
        return $value === false ? true : (bool) $value;
    }

    /**
     * Режим тех, кто ещё не выбирал.
     *
     * @return string light|dark|auto
     */
    public static function default_mode(): string {
        $value = get_config('local_kabdarkmode', 'defaultmode');
        return in_array($value, self::MODES, true) ? $value : 'light';
    }

    /**
     * Час из настроек (0–23) или значение по умолчанию.
     *
     * @param string $name darkfrom|darkto
     * @param int $default
     * @return int
     */
    public static function hour(string $name, int $default): int {
        $value = get_config('local_kabdarkmode', $name);
        if ($value === false || $value === '' || !is_numeric($value)) {
            return $default;
        }
        $value = (int) $value;
        return ($value >= 0 && $value <= 23) ? $value : $default;
    }

    /**
     * Настройки для скриптов.
     *
     * @return array{def: string, from: int, to: int}
     */
    public static function config(): array {
        return [
            'def' => self::default_mode(),
            'from' => self::hour('darkfrom', 20),
            'to' => self::hour('darkto', 7),
        ];
    }

    /**
     * Подпись режима «Авто»: когда включается тёмное.
     *
     * @return string
     */
    public static function auto_hint(): string {
        $cfg = self::config();
        if ($cfg['from'] === $cfg['to']) {
            return get_string('auto_never', 'local_kabdarkmode');
        }
        return get_string('auto_hint', 'local_kabdarkmode', (object) [
            'from' => $cfg['from'] . ':00',
            'to' => $cfg['to'] . ':00',
        ]);
    }

    /**
     * SVG-значок.
     *
     * @param string $name light|dark|auto|check
     * @param string $class CSS-классы
     * @return string
     */
    private static function icon(string $name, string $class): string {
        return '<svg class="' . $class . '" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"'
            . ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . self::ICONS[$name] . '</svg>';
    }

    /**
     * Скрипт для <head>: ставит тему на <html> до отрисовки страницы.
     *
     * Держим его коротким: он стоит перед <meta charset> ядра.
     *
     * @return string
     */
    public static function head_html(): string {
        $cfg = self::config();
        $js = '(function(r,c){var m,t,n,h;try{m=localStorage.getItem("' . self::STORAGE_KEY . '")}catch(e){}'
            . 'if(m!=="light"&&m!=="dark"&&m!=="auto")m=c.def;t=m;'
            . 'if(m==="auto"){n=new Date();h=n.getHours()+n.getMinutes()/60;'
            . 't=c.from!==c.to&&(c.from<c.to?h>=c.from&&h<c.to:h>=c.from||h<c.to)?"dark":"light"}'
            . 'r.setAttribute("data-bs-theme",t);r.setAttribute("data-kabdm-mode",m)})'
            . '(document.documentElement,' . json_encode($cfg) . ');';
        return '<script>' . $js . '</script>' . "\n";
    }

    /**
     * Значок и меню для шапки.
     *
     * Какой значок виден и у какого пункта галка — решает CSS по data-kabdm-mode на <html>,
     * поэтому серверу выбор пользователя знать не нужно.
     *
     * @return string
     */
    public static function navbar_html(): string {
        $id = \html_writer::random_id('kabdm-toggle');
        $title = get_string('appearance', 'local_kabdarkmode');

        $icons = '';
        foreach (self::MODES as $mode) {
            $icons .= self::icon($mode, 'icon kabdm-i kabdm-i-' . $mode);
        }

        $items = '';
        foreach (self::MODES as $mode) {
            $text = '<span class="kabdm-item-text">' . s(get_string('mode_' . $mode, 'local_kabdarkmode'));
            if ($mode === 'auto') {
                $text .= '<small class="kabdm-item-hint">' . s(self::auto_hint()) . '</small>';
            }
            $text .= '</span>';
            $items .= '<button type="button" class="dropdown-item kabdm-item" role="menuitemradio" aria-checked="false"'
                . ' data-kabdm-set="' . $mode . '">'
                . self::icon($mode, 'kabdm-item-icon') . $text . self::icon('check', 'kabdm-item-check')
                . '</button>';
        }

        return '<div class="kabdm nav-item dropdown d-flex align-items-center">'
            . '<button type="button" id="' . $id . '" class="nav-link kabdm-toggle icon-no-margin"'
            . ' data-bs-toggle="dropdown" data-kabdm-toggle aria-haspopup="true" aria-expanded="false"'
            . ' title="' . s($title) . '" aria-label="' . s($title) . '">' . $icons . '</button>'
            . '<div class="dropdown-menu dropdown-menu-end kabdm-menu" role="menu" aria-labelledby="' . $id . '">'
            . '<div class="dropdown-header kabdm-menu-header">' . s($title) . '</div>'
            . $items
            . '</div></div>';
    }

    /**
     * Скрипт меню для подвала страницы.
     *
     * @return string
     */
    public static function footer_html(): string {
        $cfg = self::config();
        $cfg['key'] = self::STORAGE_KEY;
        $cfg['labels'] = [];
        foreach (self::MODES as $mode) {
            $cfg['labels'][$mode] = get_string('toggle_label', 'local_kabdarkmode',
                get_string('mode_' . $mode, 'local_kabdarkmode'));
        }
        $js = <<<'JS'
(function (cfg) {
    var MODES = ['light', 'dark', 'auto'];
    var root = document.documentElement;
    var chosen = null; // Выбор на случай, если localStorage недоступен.
    var meta = document.querySelector('meta[name="theme-color"]');
    var metaLight = meta ? meta.getAttribute('content') : null;
    var switchTimer = null;

    function stored() {
        try {
            var value = window.localStorage.getItem(cfg.key);
            return MODES.indexOf(value) >= 0 ? value : null;
        } catch (e) {
            return null;
        }
    }

    function current() {
        return stored() || chosen || cfg.def;
    }

    function isNight(date) {
        if (cfg.from === cfg.to) {
            return false;
        }
        var h = date.getHours() + date.getMinutes() / 60;
        return cfg.from < cfg.to ? (h >= cfg.from && h < cfg.to) : (h >= cfg.from || h < cfg.to);
    }

    function resolve(mode) {
        if (mode === 'auto') {
            return isNight(new Date()) ? 'dark' : 'light';
        }
        return mode;
    }

    function apply(animate) {
        var mode = current();
        var theme = resolve(mode);
        if (animate && root.getAttribute('data-bs-theme') !== theme) {
            // Плавная смена цветов только на момент переключения.
            root.classList.add('kabdm-switching');
            clearTimeout(switchTimer);
            switchTimer = setTimeout(function () {
                root.classList.remove('kabdm-switching');
            }, 400);
        }
        if (root.getAttribute('data-bs-theme') !== theme) {
            root.setAttribute('data-bs-theme', theme);
        }
        if (root.getAttribute('data-kabdm-mode') !== mode) {
            root.setAttribute('data-kabdm-mode', mode);
        }
        if (meta) {
            meta.setAttribute('content', theme === 'dark' ? '#161d24' : metaLight);
        }
        var i;
        var items = document.querySelectorAll('[data-kabdm-set]');
        for (i = 0; i < items.length; i++) {
            items[i].setAttribute('aria-checked', items[i].getAttribute('data-kabdm-set') === mode ? 'true' : 'false');
        }
        var toggles = document.querySelectorAll('[data-kabdm-toggle]');
        for (i = 0; i < toggles.length; i++) {
            toggles[i].setAttribute('aria-label', cfg.labels[mode]);
            toggles[i].setAttribute('title', cfg.labels[mode]);
        }
    }

    function choose(mode) {
        if (MODES.indexOf(mode) < 0) {
            return;
        }
        chosen = mode;
        try {
            window.localStorage.setItem(cfg.key, mode);
        } catch (e) {
            // Приватный режим: выбор живёт до перезагрузки страницы.
        }
        apply(true);
    }

    document.addEventListener('click', function (e) {
        var item = e.target && e.target.closest ? e.target.closest('[data-kabdm-set]') : null;
        if (!item) {
            return;
        }
        e.preventDefault();
        choose(item.getAttribute('data-kabdm-set'));
    });

    // «Авто»: раз в минуту и при возвращении на вкладку (телефон проснулся утром).
    setInterval(function () {
        apply(true);
    }, 60000);
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            apply(true);
        }
    });
    window.addEventListener('pageshow', function () {
        apply(false);
    });
    // Выбор в соседней вкладке.
    window.addEventListener('storage', function (e) {
        if (e.key === cfg.key) {
            apply(true);
        }
    });

    apply(false);
})
JS;
        return '<script>' . $js . '(' . json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . ');</script>';
    }
}
