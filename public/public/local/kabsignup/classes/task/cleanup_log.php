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

namespace local_kabsignup\task;

/**
 * Daily cleanup of the signup log.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_log extends \core\task\scheduled_task {

    /**
     * Task name for the admin UI.
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskcleanup', 'local_kabsignup');
    }

    /**
     * Drop rows older than the retention period.
     */
    public function execute() {
        \local_kabsignup\manager::cleanup();
    }
}
