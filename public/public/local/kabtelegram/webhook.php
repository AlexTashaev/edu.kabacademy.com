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

/**
 * SendPulse chatbot webhook (events new_subscriber, incoming_message):
 *   POST /local/kabtelegram/webhook.php?key=<secret>   (or header X-Webhook-Secret)
 * GET with the right key answers {"status":"ok"} — to check the address.
 *
 * Replaces /wp-json/kab-te/v1/sp-webhook of WordPress.
 *
 * @package    local_kabtelegram
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_MOODLE_COOKIES', true);
define('NO_DEBUG_DISPLAY', true);

// phpcs:ignore moodle.Files.RequireLogin.Missing -- called by SendPulse, authorised by the secret.
require(__DIR__ . '/../../config.php');

use local_kabtelegram\webhook;

/**
 * Answer JSON and stop.
 *
 * @param int $code
 * @param array $body
 */
function local_kabtelegram_webhook_reply(int $code, array $body): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body);
    exit;
}

$given = optional_param('key', '', PARAM_RAW_TRIMMED);
if ($given === '' && !empty($_SERVER['HTTP_X_WEBHOOK_SECRET'])) {
    $given = trim((string)$_SERVER['HTTP_X_WEBHOOK_SECRET']);
}
if (!webhook::authorised($given)) {
    local_kabtelegram_webhook_reply(403, ['error' => 'unauthorized']);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    local_kabtelegram_webhook_reply(200, ['status' => 'ok', 'plugin' => 'local_kabtelegram']);
}

$raw = file_get_contents('php://input');
$events = webhook::events(json_decode((string)$raw, true));
if ($events === null) {
    local_kabtelegram_webhook_reply(400, ['error' => 'bad_format']);
}

$results = [];
foreach (array_slice($events, 0, 50) as $event) {
    try {
        $results[] = webhook::process($event);
    } catch (\Throwable $e) {
        // One broken event must not cost the others; SendPulse gets 200 anyway.
        error_log('local_kabtelegram webhook: ' . $e->getMessage());
        $results[] = 'error';
    }
}
local_kabtelegram_webhook_reply(200, [
    'ok' => true,
    'synced' => in_array('synced', $results, true) || in_array('unchanged', $results, true),
    'results' => $results,
]);
