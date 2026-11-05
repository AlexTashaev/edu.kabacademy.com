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

namespace local_kabperson\source;

/**
 * Адаптер старого WP kabacademy: заказы WooCommerce (§5 спеки, этап 2).
 *
 * Читает блок payments у существующего /wp-json/kab/v1/person (mu-plugin kab-person-card.php
 * с версии 0.3) с параметром blocks=payments — без живых вызовов Moodle на стороне WP.
 * Токен — свой (KAB_PERSON_CARD_TOKEN_EDU в wp-config площадки), в настройках плагина.
 * Таймаут 4 с: не ответил — источник «error», остальная карточка показывается (§6).
 * После закрытия WP этот источник переключается на архив заказов (этап 6).
 *
 * @package    local_kabperson
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class kabacademy_wp {

    /** Таймаут ответа площадки, секунд (§2 спеки). */
    const TIMEOUT = 4;

    /** Статусы WooCommerce, которые считаем прошедшей оплатой (как WP-карточка 0.2). */
    const PAID_STATUSES = ['completed', 'processing', 'active'];

    /**
     * Включён и настроен ли источник.
     *
     * @return bool
     */
    public static function enabled(): bool {
        // Не записанную настройку читаем как «включено» (как enablekabshop).
        $enabled = get_config('local_kabperson', 'enablewp');
        if ($enabled !== false && empty($enabled)) {
            return false;
        }
        return trim((string) get_config('local_kabperson', 'wpbase')) !== ''
            && trim((string) get_config('local_kabperson', 'wptoken')) !== '';
    }

    /**
     * Заказы и идентичность WP по email (и по связи moodle_user_id, если по email не нашлось).
     *
     * @param string $email нормализованный email
     * @param int $moodleuserid id пользователя Moodle (0 — неизвестен)
     * @return array ['state' => ok|error|off, 'found' => bool, 'identity' => array|null,
     *     'orders' => array, 'donor' => [валюта => сумма]]
     */
    public static function fetch(string $email, int $moodleuserid = 0): array {
        global $CFG;
        $result = ['state' => 'off', 'found' => false, 'identity' => null, 'orders' => [], 'donor' => []];
        if (!self::enabled()) {
            return $result;
        }
        $result['state'] = 'error';

        $params = ['email' => $email, 'blocks' => 'payments'];
        if ($moodleuserid > 0) {
            $params['moodle_user_id'] = $moodleuserid;
        }
        $url = rtrim(trim((string) get_config('local_kabperson', 'wpbase')), '/') .
            '/wp-json/kab/v1/person?' . http_build_query($params, '', '&');

        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
        $curl->setHeader([
            'X-KAB-Token: ' . trim((string) get_config('local_kabperson', 'wptoken')),
            'Accept: application/json',
        ]);
        try {
            $body = $curl->get($url, [], [
                'CURLOPT_TIMEOUT'        => self::TIMEOUT,
                'CURLOPT_CONNECTTIMEOUT' => 3,
            ]);
        } catch (\Throwable $e) {
            return $result;
        }
        $code = (int) ($curl->get_info()['http_code'] ?? 0);
        if ($curl->get_errno() || $code !== 200) {
            return $result;
        }
        $data = json_decode((string) $body, true);
        if (!is_array($data) || !array_key_exists('found', $data)) {
            return $result;
        }
        $result['state'] = 'ok';
        if (empty($data['found'])) {
            return $result;
        }

        $id = (array) ($data['identity'] ?? []);
        $result['found'] = true;
        $result['identity'] = [
            'wp_user_id'          => (int) ($id['wp_user_id'] ?? 0),
            'name'                => isset($id['name']) ? (string) $id['name'] : null,
            'email'               => \core_text::strtolower(trim((string) ($id['email'] ?? ''))),
            'moodle_user_id'      => (int) ($id['moodle_user_id'] ?? 0),
            'registered_at'       => self::iso($id['registered_at'] ?? null),
            'matched_by'          => (string) ($id['matched_by'] ?? 'email'),
            'possible_duplicates' => array_values(array_map('strval', (array) ($id['possible_duplicates'] ?? []))),
        ];

        foreach ((array) ($data['payments']['orders'] ?? []) as $o) {
            if (!is_array($o)) {
                continue;
            }
            $amount = round((float) ($o['amount'] ?? 0), 2);
            $refunded = round((float) ($o['refunded'] ?? 0), 2);
            $currency = (string) ($o['currency'] ?? '');
            $status = (string) ($o['status'] ?? '');
            $isdonation = !empty($o['is_donation']);
            if ($amount > 0) {
                $type = (($o['type'] ?? '') === 'subscription') ? 'subscription' : 'paid';
            } else {
                $type = 'free';
            }
            $result['orders'][] = [
                'source'   => 'kabacademy_wp',
                'id'       => (int) ($o['id'] ?? 0),
                'number'   => (string) ($o['number'] ?? ''),
                'date'     => self::iso($o['date'] ?? null),
                'product'  => (string) ($o['product'] ?? ''),
                'kind'     => $isdonation ? 'donation' : ($amount > 0 ? 'course' : 'free'),
                'type'     => $type,
                'amount'   => $amount,
                'refunded' => $refunded,
                'currency' => $currency,
                'status'   => $status,
                'gateway'  => (string) ($o['method'] ?? ''),
            ];
            // Донор — только прошедшие пожертвования за вычетом возвратов (как WP-карточка 0.2).
            if ($isdonation && $amount > 0 && in_array($status, self::PAID_STATUSES, true) && $currency !== '') {
                $result['donor'][$currency] = round(($result['donor'][$currency] ?? 0) + $amount - $refunded, 2);
            }
        }
        return $result;
    }

    /**
     * Дата из WP («2026-07-29T19:01:00+03:00» или «2026-07-29 16:01:00» в UTC) → ISO 8601.
     *
     * @param mixed $value
     * @return string|null
     */
    protected static function iso($value): ?string {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }
        // user_registered у WP — UTC без пояса.
        $hastz = (bool) preg_match('/(Z|[+-]\d\d:?\d\d)$/', trim($value));
        $ts = strtotime(trim($value) . ($hastz ? '' : ' UTC'));
        return $ts ? date('c', $ts) : null;
    }
}
