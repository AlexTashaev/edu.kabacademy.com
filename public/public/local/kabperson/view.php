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
 * Страница оператора: карточка человека по email.
 *
 * БЕЗ require_login — страницу открывает Dashboard App Chatwoot в iframe, у браузера
 * оператора нет сессии Moodle (§8 спеки). Вместо логина — PII-гейт как у WP-карточки:
 * email должен быть контактом чата с диалогом + rate-limit 30 запросов / 5 мин по IP,
 * fail-closed. Залогиненный с правом local/kabperson:view проходит без гейта
 * (админ, учебный отдел). ?format=json — карточка контрактом §9, ?refresh=1 — мимо кэша.
 *
 * @package    local_kabperson
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:ignore moodle.Files.RequireLogin.Missing -- гейт вместо логина, см. докблок.
require_once(__DIR__ . '/../../config.php');

$email     = optional_param('email', '', PARAM_RAW_TRIMMED);
$contactid = optional_param('contact_id', 0, PARAM_INT);
$refresh   = optional_param('refresh', 0, PARAM_BOOL);
$format    = optional_param('format', 'html', PARAM_ALPHA);

$fa = trim((string) get_config('local_kabperson', 'frameancestors'));
if ($fa !== '') {
    @header('Content-Security-Policy: frame-ancestors ' . $fa);
}

/**
 * Ответ с ошибкой и выход.
 *
 * @param int $status HTTP-код
 * @param string $code машинный код ошибки
 * @param string $format html|json
 */
function local_kabperson_fail(int $status, string $code, string $format): void {
    http_response_code($status);
    $message = get_string('err_' . [
        'not_configured'       => 'notconfigured',
        'rate_limited'         => 'ratelimited',
        'bad_email'            => 'bademail',
        'not_a_contact'        => 'notacontact',
        'chatwoot_unavailable' => 'chatwoot',
    ][$code], 'local_kabperson');
    if ($format === 'json') {
        @header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $code, 'message' => $message], JSON_UNESCAPED_UNICODE);
    } else {
        @header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><meta charset="utf-8"><title>' . $status . '</title>' .
            '<body style="font:14px/1.5 system-ui;margin:2rem;color:#555">' .
            s($message) . ' (' . s($code) . ')</body>';
    }
    exit;
}

$syscontext = context_system::instance();
$bypass = isloggedin() && !isguestuser() && has_capability('local/kabperson:view', $syscontext);

if (!$bypass) {
    if ($email === '') {
        local_kabperson_fail(400, 'bad_email', $format);
    }
    $g = \local_kabperson\gate::check($email, $contactid);
    if (!$g['ok']) {
        local_kabperson_fail($g['status'], $g['error'], $format);
    }
}

$card = null;
if ($email !== '') {
    $card = \local_kabperson\api::person($email, $refresh);
    \local_kabperson\event\card_viewed::create([
        'context'       => $syscontext,
        'relateduserid' => $card['identity']['moodle_user_id'] ?? null,
        'other'         => [
            'email' => core_text::strtolower(trim($email)),
            'via'   => 'view',
            'gated' => $bypass ? 0 : 1,
        ],
    ])->trigger();
}

if ($format === 'json') {
    @header('Content-Type: application/json; charset=utf-8');
    echo json_encode($card, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

// ---------- HTML ----------

/**
 * ISO-дата → «ГГГГ-ММ-ДД ЧЧ:ММ» для таблиц (или «—»).
 *
 * @param string|null $iso
 * @return string
 */
function local_kabperson_dt(?string $iso): string {
    if (!$iso) {
        return get_string('nothing', 'local_kabperson');
    }
    return str_replace('T', ' ', substr($iso, 0, 16));
}

$str = function (string $id): string {
    return s(get_string($id, 'local_kabperson'));
};

@header('Content-Type: text/html; charset=utf-8');
$selfurl = new moodle_url('/local/kabperson/view.php');
$jsonparams = ['email' => $email, 'format' => 'json'];
if ($contactid) {
    $jsonparams['contact_id'] = $contactid;
}
$jsonurl = new moodle_url('/local/kabperson/view.php', $jsonparams);

echo '<!doctype html><html lang="ru"><head><meta charset="utf-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
echo '<title>' . $str('viewtitle') . '</title><style>
body{font:14px/1.45 system-ui,-apple-system,"Segoe UI",sans-serif;color:#222;margin:1rem;background:#fff}
h1{font-size:1.1rem;margin:0 0 .75rem}
h2{font-size:.95rem;margin:1.25rem 0 .4rem;border-bottom:1px solid #ddd;padding-bottom:.2rem}
h3{font-size:.85rem;margin:.9rem 0 .3rem;color:#444}
table{border-collapse:collapse;width:100%;margin:.25rem 0}
th,td{border:1px solid #e3e3e3;padding:.3rem .5rem;text-align:left;vertical-align:top}
th{background:#f7f7f7;font-weight:600;white-space:nowrap}
form{margin:0 0 .75rem;display:flex;gap:.4rem;flex-wrap:wrap;align-items:center}
input[type=email]{padding:.3rem .5rem;border:1px solid #ccc;border-radius:4px;min-width:16rem}
button,a.btn{padding:.3rem .7rem;border:1px solid #bbb;border-radius:4px;background:#f4f4f4;color:#222;cursor:pointer;text-decoration:none;font-size:.85rem}
.muted{color:#777}.ok{color:#1a7f37}.warn{color:#b35900}.bad{color:#b3261e}
.chips{margin-top:1rem;font-size:.8rem;color:#777}
.chips span{display:inline-block;border:1px solid #e0e0e0;border-radius:10px;padding:.05rem .5rem;margin-right:.25rem}
.kv td:first-child{width:12rem;color:#555;background:#fafafa}
</style></head><body>';

echo '<h1>' . $str('viewtitle') . '</h1>';
echo '<form method="get" action="' . s($selfurl->out(false)) . '">';
echo '<label>' . $str('emaillabel') . ' <input type="email" name="email" value="' . s($email) . '" required></label>';
if ($contactid) {
    echo '<input type="hidden" name="contact_id" value="' . (int) $contactid . '">';
}
echo '<button type="submit">' . $str('show') . '</button>';
echo '<button type="submit" name="refresh" value="1">' . $str('refresh') . '</button>';
if ($email !== '') {
    echo '<a class="btn" href="' . s($jsonurl->out(false)) . '">' . $str('jsonlink') . '</a>';
}
echo '</form>';

if ($card !== null) {
    if (empty($card['found'])) {
        echo '<p class="warn">' . $str('notfound') . '</p>';
    } else {
        $id = $card['identity'];
        $statusclass = ['active' => 'ok', 'suspended' => 'warn', 'deleted' => 'bad'][$id['status']] ?? '';
        $statusstr = ['active' => 'statusactive', 'suspended' => 'statussuspended', 'deleted' => 'statusdeleted'];
        echo '<h2>' . $str('sec_identity') . '</h2><table class="kv">';
        echo '<tr><td>' . $str('viewtitle') . '</td><td><b>' . s($id['name'] ?? '—') . '</b> ' .
            '<span class="' . $statusclass . '">(' . $str($statusstr[$id['status']] ?? 'statusactive') . ')</span> ' .
            '<span class="muted">moodle id ' . (int) $id['moodle_user_id'] . '</span></td></tr>';
        echo '<tr><td>' . $str('emaillabel') . '</td><td>' . s(implode(', ', $id['emails'])) . '</td></tr>';
        echo '<tr><td>' . $str('registered') . '</td><td>' . s(local_kabperson_dt($id['registered_at'])) . '</td></tr>';
        echo '<tr><td>' . $str('lastlogin') . '</td><td>' . s(local_kabperson_dt($id['last_login'])) . '</td></tr>';
        echo '<tr><td>' . $str('telegram') . '</td><td>' . ($id['telegram_linked'] ? '✓' : '—') . '</td></tr>';
        if (!empty($id['possible_duplicates'])) {
            echo '<tr><td>' . $str('duplicates') . '</td><td class="warn">' .
                s(implode('; ', $id['possible_duplicates'])) . '</td></tr>';
        }
        echo '</table>';

        $lr = $card['learning'];
        echo '<h2>' . $str('sec_learning') . '</h2>';
        if (empty($lr['courses'])) {
            echo '<p class="muted">' . $str('nothing') . '</p>';
        } else {
            echo '<table><tr><th>' . $str('col_course') . '</th><th>' . $str('col_role') . '</th><th>' .
                $str('col_enrolstatus') . '</th><th>' . $str('col_progress') . '</th><th>' .
                $str('col_lastaccess') . '</th><th>' . $str('col_badges') . '</th></tr>';
            foreach ($lr['courses'] as $c) {
                $role = $c['role_label'] ?? $c['role'] ?? '—';
                if (!empty($c['restrictions'])) {
                    $role .= ' · ' . implode(', ', $c['restrictions']);
                }
                $badges = $c['badges'] ? implode(', ', array_column($c['badges'], 'name')) : '—';
                $progress = $c['progress_pct'] === null ? '—' : $c['progress_pct'] . '%';
                if ($c['completed'] === true) {
                    $progress .= ' ✓';
                }
                echo '<tr><td>' . s($c['name']) . ' <span class="muted">#' . (int) $c['course_id'] . '</span></td><td>' .
                    s((string) $role) . '</td><td>' . s($c['enrol_status']) . '</td><td>' . s($progress) . '</td><td>' .
                    s(local_kabperson_dt($c['last_access'])) . '</td><td>' . s($badges) . '</td></tr>';
            }
            echo '</table>';
        }
        if (!empty($lr['cohorts'])) {
            echo '<p><b>' . $str('cohorts') . ':</b> ' . s(implode(', ', $lr['cohorts'])) . '</p>';
        }
        if (!empty($lr['site_badges'])) {
            echo '<p><b>' . $str('sitebadges') . ':</b> ' .
                s(implode(', ', array_column($lr['site_badges'], 'name'))) . '</p>';
        }

        $pm = $card['payments'];
        echo '<h2>' . $str('sec_payments') . '</h2>';
        echo '<h3>' . $str('orders') . '</h3>';
        if (empty($pm['orders'])) {
            echo '<p class="muted">' . $str('nothing') . '</p>';
        } else {
            echo '<table><tr><th>#</th><th>' . $str('col_date') . '</th><th>' . $str('col_product') . '</th><th>' .
                $str('col_kind') . '</th><th>' . $str('col_amount') . '</th><th>' . $str('col_status') .
                '</th><th>' . $str('col_gateway') . '</th></tr>';
            foreach ($pm['orders'] as $o) {
                $cls = $o['status'] === 'ok' ? '' : ' class="bad"';
                echo '<tr' . $cls . '><td>' . (int) $o['id'] . '</td><td>' . s(local_kabperson_dt($o['date'])) .
                    '</td><td>' . s($o['product']) . '</td><td>' . s($o['kind'] . ' · ' . $o['type']) . '</td><td>' .
                    s($o['amount'] . ' ' . $o['currency']) . '</td><td>' . s($o['status']) . '</td><td>' .
                    s($o['gateway'] ?: '—') . '</td></tr>';
            }
            echo '</table>';
        }
        if (!empty($pm['subscriptions'])) {
            echo '<h3>' . $str('subscriptions') . '</h3>';
            echo '<table><tr><th>' . $str('col_product') . '</th><th>' . $str('col_status') . '</th><th>' .
                $str('col_amount') . '</th><th>' . $str('nextcharge') . '</th><th>' . $str('failcount') .
                '</th><th>' . $str('lasterror') . '</th></tr>';
            foreach ($pm['subscriptions'] as $sb) {
                $cls = $sb['status'] === 'active' ? 'ok' : 'muted';
                echo '<tr><td>' . s($sb['product']) . '</td><td class="' . $cls . '">' . s($sb['status']) .
                    '</td><td>' . s($sb['amount'] . ' ' . $sb['currency'] . ' / ' . $sb['months'] . ' мес.') .
                    '</td><td>' . s(local_kabperson_dt($sb['next_charge'])) . '</td><td>' .
                    ((int) $sb['fail_count'] ?: '—') . '</td><td>' . s($sb['last_error'] ?: '—') . '</td></tr>';
            }
            echo '</table>';
        }
        if (!empty($pm['unfinished'])) {
            echo '<h3>' . $str('unfinished') . '</h3>';
            echo '<table><tr><th>' . $str('col_date') . '</th><th>' . $str('col_product') . '</th><th>' .
                $str('col_amount') . '</th><th>' . $str('col_status') . '</th></tr>';
            foreach ($pm['unfinished'] as $u) {
                echo '<tr><td>' . s(local_kabperson_dt($u['started_at'])) . '</td><td>' . s($u['product']) .
                    '</td><td>' . s($u['amount'] . ' ' . $u['currency']) . '</td><td class="warn">' .
                    s($u['status'] . ' · ' . $u['method']) . '</td></tr>';
            }
            echo '</table>';
        }
        $donor = (array) $pm['donor_total'];
        if ($donor) {
            $parts = [];
            foreach ($donor as $cur => $sum) {
                $parts[] = $sum . ' ' . $cur;
            }
            echo '<p><b>' . $str('donortotal') . ':</b> ' . s(implode(', ', $parts)) . '</p>';
        }
    }

    echo '<div class="chips">';
    foreach ($card['sources'] as $src => $state) {
        $cls = ['ok' => 'ok', 'error' => 'bad', 'off' => 'muted'][$state] ?? 'muted';
        echo '<span class="' . $cls . '">' . s($src . ': ' . $state) . '</span>';
    }
    echo ' · ' . $str('generatedat') . ' ' . s(local_kabperson_dt($card['generated_at']));
    if (!empty($card['cached'])) {
        echo ' <span class="muted">(' . $str('fromcache') . ')</span>';
    }
    echo '</div>';
}

echo '</body></html>';
