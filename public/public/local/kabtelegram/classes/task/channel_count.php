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

use local_kabtelegram\manager;
use local_kabtelegram\stats;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

/**
 * Hourly: number of members of the course channel (Bot API getChatMemberCount;
 * for a public channel the bot need not be an admin — checked by WordPress on
 * 14.09.2026). Who exactly joined cannot be seen this way.
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class channel_count extends \core\task\scheduled_task {

    /**
     * Task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskchannelcount', 'local_kabtelegram');
    }

    /**
     * Run.
     */
    public function execute() {
        $config = manager::config();
        if ($config->channelchat === '' || $config->channelbottoken === '') {
            return;
        }
        $curl = new \curl();
        $body = $curl->get($config->tgapibase . '/bot' . $config->channelbottoken . '/getChatMemberCount',
            ['chat_id' => $config->channelchat], ['CURLOPT_CONNECTTIMEOUT' => 5, 'CURLOPT_TIMEOUT' => 15]);
        $json = json_decode((string)$body, true);
        if (empty($json['ok']) || !isset($json['result']) || !is_numeric($json['result'])) {
            $reason = $curl->get_errno() ? 'curl error ' . $curl->get_errno()
                : (is_array($json) && isset($json['description']) ? (string)$json['description']
                    : 'HTTP ' . (int)($curl->get_info()['http_code'] ?? 0));
            mtrace('local_kabtelegram channel_count: ' . $reason);
            return;
        }
        stats::track('channel_members', 'cron', $config->channelchat, 0, (int)$json['result']);
        mtrace('local_kabtelegram channel_count: ' . $config->channelchat . ' = ' . (int)$json['result']);
    }
}
