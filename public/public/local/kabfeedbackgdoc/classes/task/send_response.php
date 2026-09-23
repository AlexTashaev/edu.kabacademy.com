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

namespace local_kabfeedbackgdoc\task;

use local_kabfeedbackgdoc\sender;

/**
 * Adhoc task: build the payload for one feedback response and POST it.
 *
 * Throwing makes the task manager retry with back-off, so temporary
 * Google outages do not lose responses.
 *
 * @package    local_kabfeedbackgdoc
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_response extends \core\task\adhoc_task {

    /**
     * Task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('taskname', 'local_kabfeedbackgdoc');
    }

    /**
     * Execute.
     */
    public function execute(): void {
        $data = $this->get_custom_data();
        $completedid = (int)($data->completedid ?? 0);
        if (!$completedid) {
            return;
        }

        $payload = sender::build_payload($completedid);
        if ($payload === null) {
            // Response was deleted before we got to it, or the activity is gone. Nothing to retry.
            mtrace("local_kabfeedbackgdoc: completed {$completedid} not found, skipping");
            return;
        }

        $result = sender::post($payload);
        mtrace("local_kabfeedbackgdoc: completed {$completedid} -> HTTP {$result['code']} {$result['body']}");
        if (!$result['ok']) {
            throw new \moodle_exception('error', 'moodle', '', null,
                "local_kabfeedbackgdoc: webhook failed (HTTP {$result['code']}): {$result['body']}");
        }
    }
}
