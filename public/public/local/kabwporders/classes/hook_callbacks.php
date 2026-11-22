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

namespace local_kabwporders;

/**
 * Обработчики хуков ядра.
 *
 * @package    local_kabwporders
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    /**
     * Пункт «Прежние оплаты» в меню пользователя — только тем, у кого такие оплаты есть.
     *
     * Меню строится на каждой странице, поэтому ответ «есть ли оплаты» запоминается в сессии:
     * один запрос к реестру на сессию. Любая ошибка здесь меню не ломает — пункт просто не появится.
     *
     * @param \core_user\hook\extend_user_menu $hook
     */
    public static function extend_user_menu(\core_user\hook\extend_user_menu $hook): void {
        global $USER, $SESSION;

        try {
            if (!isloggedin() || isguestuser()) {
                return;
            }
            // Не сохранённую настройку читаем как «включено».
            $enabled = get_config('local_kabwporders', 'usermenu');
            if ($enabled !== false && empty($enabled)) {
                return;
            }
            $userid = (int) $USER->id;
            if (!isset($SESSION->local_kabwporders_has) || !is_array($SESSION->local_kabwporders_has)) {
                $SESSION->local_kabwporders_has = [];
            }
            if (!array_key_exists($userid, $SESSION->local_kabwporders_has)) {
                $SESSION->local_kabwporders_has[$userid] = api::has_orders($userid, (string) ($USER->email ?? ''));
            }
            if (!$SESSION->local_kabwporders_has[$userid]) {
                return;
            }
            $item = new \stdClass();
            $item->itemtype = 'link';
            $item->url = new \moodle_url('/local/kabwporders/my.php');
            $item->title = get_string('my_menu', 'local_kabwporders');
            $item->titleidentifier = 'my_menu,local_kabwporders';
            $hook->add_navitem($item);
        } catch (\Throwable $e) {
            return;
        }
    }
}
