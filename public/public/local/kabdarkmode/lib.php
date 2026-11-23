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

/**
 * Колбэки local_kabdarkmode.
 *
 * @package    local_kabdarkmode
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Значок «Оформление» в шапке, рядом с сообщениями и уведомлениями.
 *
 * @param renderer_base $renderer
 * @return string
 */
function local_kabdarkmode_render_navbar_output(\renderer_base $renderer): string {
    if (!\local_kabdarkmode\ui::enabled()) {
        return '';
    }
    return \local_kabdarkmode\ui::navbar_html();
}
