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
 * Ежедневная рассылка отчётов кассы секретарю (\local_kabshop\staffreports).
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_staff_reports extends \core\task\scheduled_task {

    /**
     * Название задачи.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_staffreports', 'local_kabshop');
    }

    /**
     * Разослать новые покупки.
     */
    public function execute() {
        \local_kabshop\staffreports::send(function(string $line): void {
            mtrace($line);
        });
    }
}
