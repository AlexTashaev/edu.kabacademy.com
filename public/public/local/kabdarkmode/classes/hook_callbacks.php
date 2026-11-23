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

use core\hook\output\before_footer_html_generation;
use core\hook\output\before_standard_head_html_generation;

/**
 * Хуки ядра: скрипт темы в <head> и скрипт меню в подвале.
 *
 * @package    local_kabdarkmode
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Ставит data-bs-theme на <html> до отрисовки страницы — без белой вспышки.
     *
     * @param before_standard_head_html_generation $hook
     */
    public static function before_standard_head_html_generation(before_standard_head_html_generation $hook): void {
        if (!ui::enabled()) {
            return;
        }
        $hook->add_html(ui::head_html());
    }

    /**
     * Меню переключателя и пересчёт режима «Авто».
     *
     * @param before_footer_html_generation $hook
     */
    public static function before_footer_html_generation(before_footer_html_generation $hook): void {
        if (!ui::enabled()) {
            return;
        }
        $hook->add_html(ui::footer_html());
    }
}
