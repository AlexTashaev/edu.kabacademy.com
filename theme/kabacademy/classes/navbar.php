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

namespace theme_kabacademy;

use moodle_url;

/**
 * Шапка темы (вариант «Б», 19.11.2026): кнопка «Поддержать академию».
 *
 * @package    theme_kabacademy
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navbar {

    /** Адрес кнопки по умолчанию — нынешняя ссылка «Сделай вклад» из меню сайта. */
    const DONATE_URL = 'https://kabacademy.com/donation/step/lp-donationsubscr-new/';

    /**
     * Кнопка «Поддержать академию» для output.navbar_plugin_output. Только на страницах этой темы:
     * колбэк render_navbar_output зовётся на всём сайте, в том числе под Boost.
     *
     * @param \renderer_base $renderer
     * @return string
     */
    public static function donate_button(\renderer_base $renderer): string {
        global $PAGE;
        // $PAGE->theme — магическое свойство: без isset().
        $theme = $PAGE->theme;
        if (!$theme || $theme->name !== 'kabacademy') {
            return '';
        }
        $url = get_config('theme_kabacademy', 'donateurl');
        $url = trim($url === false ? self::DONATE_URL : (string) $url);
        if ($url === '') {
            return '';
        }
        $site = (new moodle_url('/'))->out(false);
        if (str_starts_with($url, '/')) {
            $url = (new moodle_url($url))->out(false);
        }
        return $renderer->render_from_template('theme_kabacademy/donate_button', [
            'url' => $url,
            'external' => stripos($url, $site) !== 0,
        ]);
    }
}
