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
 * Adhoc task: send every response of one feedback activity again, oldest first.
 *
 * Used to fill a table with the responses given before the form was forwarded,
 * and to catch up after a wrong setting. The script skips the rows it already
 * has, so running it twice does no harm.
 *
 * @package    local_kabfeedbackgdoc
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class resend_form extends \core\task\adhoc_task {

    /**
     * Task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('taskresend', 'local_kabfeedbackgdoc');
    }

    /**
     * Execute.
     */
    public function execute(): void {
        $data = $this->get_custom_data();
        $cmid = (int)($data->cmid ?? 0);
        $cm = $cmid ? get_coursemodule_from_id('feedback', $cmid) : false;
        if (!$cm) {
            mtrace("local_kabfeedbackgdoc: feedback cmid {$cmid} not found, skipping");
            return;
        }

        $written = 0;
        $duplicates = 0;
        foreach (array_chunk(sender::completed_ids((int)$cm->instance), sender::BATCH_SIZE) as $ids) {
            $payloads = array_filter(array_map([sender::class, 'build_payload'], $ids));
            if (empty($payloads)) {
                continue;
            }
            $result = sender::post_batch($payloads);
            mtrace("local_kabfeedbackgdoc: cmid {$cmid}, " . count($payloads) .
                " responses -> HTTP {$result['code']} {$result['body']}");
            if (!$result['ok']) {
                throw new \moodle_exception('error', 'moodle', '', null,
                    "local_kabfeedbackgdoc: webhook failed (HTTP {$result['code']}): {$result['body']}");
            }
            $written += (int)($result['json']['written'] ?? 0);
            $duplicates += (int)($result['json']['duplicates'] ?? 0);
        }
        mtrace("local_kabfeedbackgdoc: cmid {$cmid} re-sent, {$written} new rows, {$duplicates} already in the table");
    }
}
