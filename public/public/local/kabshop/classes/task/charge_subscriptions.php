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

namespace local_kabshop\task;

/**
 * Автосписание подписок, у которых подошёл срок.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class charge_subscriptions extends \core\task\scheduled_task {

    /**
     * Название задачи.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_charge', 'local_kabshop');
    }

    /**
     * Запуск.
     */
    public function execute() {
        [$ok, $failed] = \local_kabshop\subscriptions::charge_due();
        mtrace("local_kabshop: charged {$ok}, failed {$failed}");
    }
}
