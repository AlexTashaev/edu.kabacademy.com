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

namespace local_kabtelegram\task;

use local_kabtelegram\webhook;

/**
 * Retry writing the variables into a contact after SendPulse failed. A failure
 * throws, so that the task manager retries it later with a growing delay.
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sync_contact extends \core\task\adhoc_task {

    /**
     * Task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('tasksynccontact', 'local_kabtelegram');
    }

    /**
     * Run.
     */
    public function execute() {
        global $DB;
        $data = $this->get_custom_data();
        $link = $DB->get_record('local_kabtelegram_contact', ['id' => (int)($data->linkid ?? 0)]);
        if (!$link) {
            return;
        }
        $result = webhook::sync($link);
        mtrace('local_kabtelegram sync_contact ' . $link->id . ': ' . $result);
        if ($result === 'failed') {
            throw new \moodle_exception('errorsync', 'local_kabtelegram', '', $link->contactid);
        }
    }
}
