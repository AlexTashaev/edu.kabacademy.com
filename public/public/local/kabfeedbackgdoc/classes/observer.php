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

namespace local_kabfeedbackgdoc;

/**
 * Event observer: queue an adhoc task for every submitted feedback response.
 *
 * The HTTP call is not made inline so that the student's submission never
 * waits on Google and failures are retried by cron.
 *
 * @package    local_kabfeedbackgdoc
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /**
     * Handle \mod_feedback\event\response_submitted.
     *
     * @param \mod_feedback\event\response_submitted $event
     * @return bool
     */
    public static function response_submitted(\mod_feedback\event\response_submitted $event): bool {
        try {
            $config = get_config('local_kabfeedbackgdoc');
            if (empty($config->enabled) || empty($config->webhookurl)) {
                return true;
            }
            $cmid = (int)($event->other['cmid'] ?? 0);
            if (!sender::is_watched($cmid, $config)) {
                return true;
            }

            $task = new task\send_response();
            $task->set_custom_data([
                'completedid' => (int)$event->objectid,
                'cmid'        => $cmid,
            ]);
            \core\task\manager::queue_adhoc_task($task);
        } catch (\Throwable $e) {
            debugging('local_kabfeedbackgdoc: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
        return true;
    }
}
