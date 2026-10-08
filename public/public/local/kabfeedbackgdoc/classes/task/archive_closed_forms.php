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
 * Scheduled task: once a questions form has closed, its rows leave the time tabs
 * of the teachers' table for the archive tab.
 *
 * A form is closed when its "allow answers until" or its "available until" date
 * restriction has passed; the rows move only after the archive hour of that day
 * (sender::archive_at()), because the evening webinar is held after the form has
 * closed. Archived forms are remembered in the plugin config; a form that is
 * opened again is forgotten there, so it is archived again when it closes next.
 * A failed request is simply retried on the next run.
 *
 * @package    local_kabfeedbackgdoc
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class archive_closed_forms extends \core\task\scheduled_task {

    /**
     * Task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('taskarchive', 'local_kabfeedbackgdoc');
    }

    /**
     * Execute.
     */
    public function execute(): void {
        $config = get_config('local_kabfeedbackgdoc');
        if (empty($config->enabled) || empty($config->webhookurl)) {
            return;
        }
        $archived = sender::archived_cmids($config);
        $now = time();
        $changed = false;
        foreach (sender::forwarded_forms($config) as $form) {
            if ($form->target === null || $form->target['layout'] !== sender::LAYOUT_QUESTIONS) {
                continue;
            }
            $cmid = (int)$form->cmid;
            $closed = sender::closed_at($form);
            $isarchived = in_array($cmid, $archived, true);
            if ($closed && $now >= sender::archive_at($closed, $config)) {
                if ($isarchived) {
                    continue;
                }
                if ((int)$form->responses > 0) {
                    $result = sender::post_archive($form->target, $cmid, (string)$form->name);
                    mtrace("local_kabfeedbackgdoc: cmid {$cmid} closed " . userdate($closed) .
                        " -> HTTP {$result['code']} {$result['body']}");
                    if (!$result['ok']) {
                        continue; // Next hour, then.
                    }
                } else {
                    mtrace("local_kabfeedbackgdoc: cmid {$cmid} closed without responses, nothing to archive");
                }
                $archived[] = $cmid;
                $changed = true;
            } else if ($isarchived) {
                mtrace("local_kabfeedbackgdoc: cmid {$cmid} is open again, it will be archived when it closes next");
                $archived = array_values(array_diff($archived, [$cmid]));
                $changed = true;
            }
        }
        if ($changed) {
            sender::set_archived($archived);
        }
    }
}
