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

namespace local_kabperson;

/**
 * PII-гейт страницы оператора — перенос kab_person_view_gate из kab-person-card.php 0.2 (WP).
 *
 * Карточка без логина отдаётся только по email, у которого в Chatwoot есть контакт
 * с диалогом (человек реально писал в чат). Перебор базы невозможен: посторонний
 * email → 403, всё под rate-limit по IP, Chatwoot недоступен / не настроен → 503
 * (fail-closed). Вердикт кешируется на 10 минут, чтобы «обновить» не дёргал Chatwoot.
 *
 * @package    local_kabperson
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gate {

    /** Запросов с одного IP за окно (как у WP-карточки: оператор открывает штучно). */
    const RL_LIMIT = 30;

    /**
     * Проверка гейта.
     *
     * @param string $email искомый email (как пришёл)
     * @param int $contactid id контакта Chatwoot от Dashboard App (0 — нет): нужен анонимам,
     *     у которых основного email в Chatwoot нет, а адрес из чата лежит в kab_alt_email
     * @return array ['ok' => bool, 'status' => int HTTP, 'error' => string код ошибки]
     */
    public static function check(string $email, int $contactid = 0): array {
        $base = trim((string) get_config('local_kabperson', 'chatwootbase'));
        $token = trim((string) get_config('local_kabperson', 'chatwoottoken'));
        $account = (int) get_config('local_kabperson', 'chatwootaccount');
        if ($base === '' || $token === '' || $account <= 0) {
            return ['ok' => false, 'status' => 503, 'error' => 'not_configured'];
        }

        // Rate-limit: RL_LIMIT запросов / 5 мин с IP (окно скользит, как у WP transient).
        $rl = \cache::make('local_kabperson', 'ratelimit');
        $rlkey = md5(getremoteaddr());
        $n = (int) $rl->get($rlkey);
        if ($n >= self::RL_LIMIT) {
            return ['ok' => false, 'status' => 429, 'error' => 'rate_limited'];
        }
        $rl->set($rlkey, $n + 1);

        $email = \core_text::strtolower(trim($email));
        if ($email === '' || !validate_email($email)) {
            return ['ok' => false, 'status' => 400, 'error' => 'bad_email'];
        }

        $cache = \cache::make('local_kabperson', 'gate');
        $apibase = rtrim($base, '/');

        // Аноним: основного email в Chatwoot нет, адрес из чата — в атрибуте kab_alt_email.
        // Пускаем, если именно у ЭТОГО контакта такой адрес (основной или из чата) и есть
        // диалог. Поиск контактов custom-атрибуты не ищет, поэтому id передаёт Dashboard App.
        if ($contactid > 0) {
            $ckey = 'c' . md5($contactid . '|' . $email);
            $cc = $cache->get($ckey);
            if ($cc === 'yes') {
                return ['ok' => true, 'status' => 200, 'error' => ''];
            }
            if ($cc !== 'no') {
                $contact = self::get_json("{$apibase}/api/v1/accounts/{$account}/contacts/{$contactid}", $token);
                if ($contact === null) {
                    return ['ok' => false, 'status' => 503, 'error' => 'chatwoot_unavailable'];
                }
                $payload = $contact['payload'] ?? [];
                $own = \core_text::strtolower((string) ($payload['email'] ?? ''));
                $alt = \core_text::strtolower(trim((string) ($payload['custom_attributes']['kab_alt_email'] ?? '')));
                $okc = false;
                if ($own === $email || $alt === $email) {
                    $okc = self::has_conversation($apibase, $account, $contactid, $token);
                }
                $cache->set($ckey, $okc ? 'yes' : 'no');
                if ($okc) {
                    return ['ok' => true, 'status' => 200, 'error' => ''];
                }
            }
            // Не совпало — проверяем обычным путём по основному email.
        }

        $ekey = 'e' . md5($email);
        $cached = $cache->get($ekey);
        if ($cached === 'yes') {
            return ['ok' => true, 'status' => 200, 'error' => ''];
        }
        if ($cached === 'no') {
            return ['ok' => false, 'status' => 403, 'error' => 'not_a_contact'];
        }
        $found = self::get_json("{$apibase}/api/v1/accounts/{$account}/contacts/search?q=" . rawurlencode($email), $token);
        if ($found === null) {
            return ['ok' => false, 'status' => 503, 'error' => 'chatwoot_unavailable'];
        }
        $ok = false;
        foreach (($found['payload'] ?? []) as $c) {
            if (\core_text::strtolower((string) ($c['email'] ?? '')) === $email) {
                // Контакт есть; требуем хотя бы один диалог (счётчик диалогов поиск
                // отдаёт не всегда — проверяем списком диалогов контакта).
                $cid = (int) ($c['id'] ?? 0);
                if ($cid && self::has_conversation($apibase, $account, $cid, $token)) {
                    $ok = true;
                    break;
                }
            }
        }
        $cache->set($ekey, $ok ? 'yes' : 'no');
        if (!$ok) {
            return ['ok' => false, 'status' => 403, 'error' => 'not_a_contact'];
        }
        return ['ok' => true, 'status' => 200, 'error' => ''];
    }

    /**
     * Есть ли у контакта хотя бы один диалог.
     *
     * @param string $apibase
     * @param int $account
     * @param int $contactid
     * @param string $token
     * @return bool
     */
    protected static function has_conversation(string $apibase, int $account, int $contactid, string $token): bool {
        $resp = self::get_json("{$apibase}/api/v1/accounts/{$account}/contacts/{$contactid}/conversations", $token);
        return !empty($resp['payload']);
    }

    /**
     * GET к API Chatwoot: JSON-массив при HTTP 200, иначе null.
     *
     * @param string $url
     * @param string $token
     * @return array|null
     */
    protected static function get_json(string $url, string $token): ?array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
        $curl->setHeader(['api_access_token: ' . $token]);
        $body = $curl->get($url, [], ['CURLOPT_TIMEOUT' => 8, 'CURLOPT_CONNECTTIMEOUT' => 5]);
        $code = (int) ($curl->get_info()['http_code'] ?? 0);
        if ($code !== 200) {
            return null;
        }
        $data = json_decode((string) $body, true);
        return is_array($data) ? $data : null;
    }
}
