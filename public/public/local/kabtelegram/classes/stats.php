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

namespace local_kabtelegram;

/**
 * Funnel statistics: every click is a row (WordPress kept daily counters in an
 * option plus per-user meta; here both views are queries over the same rows).
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stats {

    /**
     * Record an event. Never throws: statistics must not break a redirect.
     *
     * @param string $event bot_click | channel_click | lesson_click | bot_linked | channel_members
     * @param string $source session | anonymous | webhook | cron
     * @param string $place src= of the link, or the chat
     * @param int $userid
     * @param int $value
     */
    public static function track(string $event, string $source, string $place = '', int $userid = 0, int $value = 0): void {
        global $DB;
        try {
            $DB->insert_record('local_kabtelegram_event', (object)[
                'event' => \core_text::substr($event, 0, 32),
                'source' => \core_text::substr($source, 0, 20),
                'place' => \core_text::substr($place, 0, 64),
                'userid' => $userid,
                'value' => $value,
                'timecreated' => time(),
            ]);
        } catch (\Throwable $e) {
            debugging('local_kabtelegram: event not recorded: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    /**
     * Daily counts for the last $days days, by "event:source" (and the last
     * channel member count of the day), dates in the site timezone.
     *
     * @param int $days
     * @return array [array of 'Y-m-d' => [column => value], string[] columns]
     */
    public static function daily(int $days): array {
        global $DB;
        $tz = \core_date::get_server_timezone_object();
        $start = new \DateTime('today', $tz);
        $start->modify('-' . max(0, $days - 1) . ' days');
        $from = $start->getTimestamp();

        $rows = [];
        $columns = [];
        $records = $DB->get_recordset_select('local_kabtelegram_event', 'timecreated >= :from', ['from' => $from],
            'timecreated ASC, id ASC', 'id, event, source, value, timecreated');
        foreach ($records as $r) {
            $day = (new \DateTime('@' . $r->timecreated))->setTimezone($tz)->format('Y-m-d');
            if ($r->event === 'channel_members') {
                $col = 'channel_members';
                $rows[$day][$col] = (int)$r->value; // The last snapshot of the day wins.
            } else {
                $col = $r->event . ($r->source !== '' && $r->event !== 'bot_linked' ? ':' . $r->source : '');
                $rows[$day][$col] = ($rows[$day][$col] ?? 0) + 1;
            }
            $columns[$col] = true;
        }
        $records->close();

        // Every day of the period, empty ones too.
        $out = [];
        for ($d = clone $start, $i = 0; $i < $days; $i++, $d->modify('+1 day')) {
            $key = $d->format('Y-m-d');
            $out[$key] = $rows[$key] ?? [];
        }
        $columns = array_keys($columns);
        sort($columns);
        return [$out, $columns];
    }

    /**
     * People enrolled in a course since a date: how many of them went to the bot,
     * linked it, went to the channel (the WordPress "wp kab-te-stats --since --product").
     *
     * @param int $courseid
     * @param int $since timestamp
     * @return array people, bot_click, bot_linked, channel_click, lesson_click
     */
    public static function cohort(int $courseid, int $since): array {
        global $DB;
        $params = ['courseid' => $courseid, 'since' => $since, 'since2' => $since, 'since3' => $since,
            'since4' => $since, 'since5' => $since];
        $sql = "SELECT COUNT(DISTINCT p.userid) AS people,
                       COUNT(DISTINCT bc.userid) AS bot_click,
                       COUNT(DISTINCT bl.userid) AS bot_linked,
                       COUNT(DISTINCT cc.userid) AS channel_click,
                       COUNT(DISTINCT lc.userid) AS lesson_click
                  FROM (SELECT DISTINCT ue.userid
                          FROM {user_enrolments} ue
                          JOIN {enrol} e ON e.id = ue.enrolid
                         WHERE e.courseid = :courseid AND ue.timecreated >= :since) p
             LEFT JOIN {local_kabtelegram_event} bc
                       ON bc.userid = p.userid AND bc.event = 'bot_click' AND bc.timecreated >= :since2
             LEFT JOIN {local_kabtelegram_event} bl
                       ON bl.userid = p.userid AND bl.event = 'bot_linked' AND bl.timecreated >= :since3
             LEFT JOIN {local_kabtelegram_event} cc
                       ON cc.userid = p.userid AND cc.event = 'channel_click' AND cc.timecreated >= :since4
             LEFT JOIN {local_kabtelegram_event} lc
                       ON lc.userid = p.userid AND lc.event = 'lesson_click' AND lc.timecreated >= :since5";
        $row = $DB->get_record_sql($sql, $params);
        return [
            'people' => (int)($row->people ?? 0),
            'bot_click' => (int)($row->bot_click ?? 0),
            'bot_linked' => (int)($row->bot_linked ?? 0),
            'channel_click' => (int)($row->channel_click ?? 0),
            'lesson_click' => (int)($row->lesson_click ?? 0),
        ];
    }
}
