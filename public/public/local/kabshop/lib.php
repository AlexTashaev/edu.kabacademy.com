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
 * Колбэки local_kabshop.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Отдача картинок товаров, обложек их курсов и фото преподавателей каталога. Картинки витрины
 * публичные — видят и гости.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function local_kabshop_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel != CONTEXT_SYSTEM || !in_array($filearea, ['image', 'teacherphotos', 'courseimage'], true)) {
        return false;
    }
    $itemid = (int)array_shift($args);
    if ($filearea === 'courseimage') {
        // Обложка курса товара без своей картинки (itemid — товар). Отдаём без входа, как картинки
        // витрины: при forcelogin ядро не отдаёт course/overviewfiles гостю. Только видимый товар
        // (скрытый — управляющему витриной); имя файла в адресе — для кэша, файл берём из курса.
        global $DB;
        $product = $DB->get_record('local_kabshop_product', ['id' => $itemid]);
        if (!$product || (empty($product->visible)
                && !has_capability('local/kabshop:manage', context_system::instance()))) {
            return false;
        }
        $file = \local_kabshop\catalog::course_image_file($product);
        if (!$file) {
            return false;
        }
        send_stored_file($file, DAYSECS, 0, $forcedownload, $options);
    }
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';
    $file = get_file_storage()->get_file($context->id, 'local_kabshop', $filearea, $itemid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }
    send_stored_file($file, DAYSECS, 0, $forcedownload, $options);
}

/**
 * Профиль пользователя: управляющему витриной — ссылка «Заказы витрины (N)» на все заказы этого
 * человека (раньше заказы из профиля было не найти, только поиском в списке заказов).
 *
 * @param \core_user\output\myprofile\tree $tree
 * @param stdClass $user
 * @param bool $iscurrentuser
 * @param stdClass|null $course
 * @return void
 */
function local_kabshop_myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $DB;
    try {
        if (!has_capability('local/kabshop:manage', context_system::instance())) {
            return;
        }
        $count = $DB->count_records('local_kabshop_purchase', ['userid' => $user->id]);
        $tree->add_node(new \core_user\output\myprofile\node('administration', 'local_kabshop_orders',
            get_string('profileorders', 'local_kabshop', $count), null,
            new moodle_url('/local/kabshop/orders.php', ['userid' => $user->id])));
    } catch (\Throwable $e) {
        // Профиль важнее ссылки.
        debugging('local_kabshop_myprofile_navigation: ' . $e->getMessage(), DEBUG_DEVELOPER);
    }
}
