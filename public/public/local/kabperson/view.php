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
 * Dashboard App (этап 2): Chatwoot открывает страницу без email и присылает контакт через
 * postMessage (событие appContext). Страница принимает его только от origin'ов из настройки
 * frameancestors и переходит на себя же с ?email=&contact_id= (и &unverified=1, если адрес
 * назван в чате, а не подтверждён входом на сайт) — дальше обычный гейт. Перенос слушателя
 * из person-card-view.html (репо kabacademy.com).
 *
 * @package    local_kabperson
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:ignore moodle.Files.RequireLogin.Missing -- гейт вместо логина, см. докблок.
require_once(__DIR__ . '/../../config.php');

$email      = optional_param('email', '', PARAM_RAW_TRIMMED);
$contactid  = optional_param('contact_id', 0, PARAM_INT);
$refresh    = optional_param('refresh', 0, PARAM_BOOL);
$format     = optional_param('format', 'html', PARAM_ALPHA);
$unverified = optional_param('unverified', 0, PARAM_BOOL);
$noemail    = optional_param('noemail', 0, PARAM_BOOL); // Dashboard App: у контакта нет email.

// Кто может встроить страницу в iframe. Пусто — только сам Moodle ('self'): в 0.1 без
// настройки заголовка не было вовсе, и страницу мог встроить любой сайт.
$fa = trim((string) get_config('local_kabperson', 'frameancestors'));
@header('Content-Security-Policy: frame-ancestors ' . ($fa !== '' ? $fa : "'self'"));
if ($fa !== '' && function_exists('header_remove')) {
    // CSP frame-ancestors главнее, но старый X-Frame-Options от прокси не должен мешать.
    header_remove('X-Frame-Options');
}
// Origin'ы, от которых принимаем контакт через postMessage: точные https?://host[:port]
// из той же настройки (маски и 'self' сюда не годятся).
$msgorigins = [];
foreach (preg_split('/\s+/', $fa, -1, PREG_SPLIT_NO_EMPTY) as $src) {
    if (preg_match('~^https?://[A-Za-z0-9.-]+(:\d+)?$~', $src)) {
        $msgorigins[] = $src;
    }
}

/**
 * Слушатель Dashboard App: контакт из Chatwoot (postMessage appContext) → переход на эту же
 * страницу с ?email=&contact_id=[&unverified=1]. Сообщения — только от $origins.
 * Встраивается и в страницы ошибок: оператор переключил диалог — карточка догонит.
 *
 * @param string[] $origins разрешённые origin'ы (из frameancestors)
 * @param string $email email, по которому страница уже открыта ('' — ещё ни по какому)
 * @param int $contactid id контакта, по которому страница уже открыта
 * @return string <script> или '' (встраивание не настроено)
 */
function local_kabperson_listener(array $origins, string $email, int $contactid): string {
    if (!$origins) {
        return '';
    }
    $cfg = json_encode([
        'origins' => array_values($origins),
        'email'   => core_text::strtolower(trim($email)),
        'contact' => $contactid,
        'self'    => (new moodle_url('/local/kabperson/view.php'))->out(false),
        'noemail' => get_string('nocontactemail', 'local_kabperson'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
    return <<<HTML
<script>
(function () {
  var C = {$cfg};
  if (window.parent === window) { return; }
  function open(c) {
    var own = String(c.email || '').trim();
    var alt = String((c.custom_attributes || {}).kab_alt_email || '').trim();
    var email = own || alt;
    var cid = parseInt(c.id, 10) || 0;
    if (!email) {
      // Переключили на диалог без email — карточку прежнего человека не оставляем.
      if (C.email !== '') {
        var s = new URL(C.self);
        s.searchParams.set('noemail', '1');
        window.location.replace(s.toString());
        return;
      }
      var n = document.getElementById('kabp-wait');
      if (n) { n.textContent = C.noemail; }
      return;
    }
    // Основной email у контакта есть только у подтверждённых сайтом (identifier);
    // адрес из чата (kab_alt_email) — не подтверждён.
    var unverified = !own || !c.identifier;
    if (email.toLowerCase() === C.email && cid === C.contact) { return; }
    var u = new URL(C.self);
    u.searchParams.set('email', email);
    if (cid) { u.searchParams.set('contact_id', String(cid)); }
    if (unverified) { u.searchParams.set('unverified', '1'); }
    window.location.replace(u.toString());
  }
  window.addEventListener('message', function (e) {
    if (C.origins.indexOf(e.origin) === -1) { return; }
    var d = e.data;
    if (typeof d === 'string') { try { d = JSON.parse(d); } catch (_) { return; } }
    var c = d && d.data && d.data.contact;
    if (c && typeof c === 'object') { open(c); }
  });
  // Chatwoot шлёт контакт сам при загрузке iframe; попросим ещё раз — на случай, если
  // сообщение ушло раньше, чем повесился слушатель. Только разрешённым origin'ам.
  C.origins.forEach(function (o) {
    try { window.parent.postMessage('chatwoot-dashboard-app:fetch-info', o); } catch (_) {}
  });
})();
</script>
HTML;
}

/**
 * Ответ с ошибкой и выход.
 *
 * @param int $status HTTP-код
 * @param string $code машинный код ошибки
 * @param string $format html|json
 */
function local_kabperson_fail(int $status, string $code, string $format): void {
    global $msgorigins, $email, $contactid;
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
            '<div>' . s((string) $email) . '</div>' .
            s($message) . ' (' . s($code) . ')' .
            local_kabperson_listener((array) $msgorigins, (string) $email, (int) $contactid) . '</body>';
    }
    exit;
}

$syscontext = context_system::instance();
$bypass = isloggedin() && !isguestuser() && has_capability('local/kabperson:view', $syscontext);

// Без email анонимная HTML-страница — пустая оболочка: форма и ожидание контакта из
// Chatwoot, без данных и без обращения к гейту. JSON без email — ошибка, как раньше.
$shell = ($email === '' && $format !== 'json');

if (!$bypass && !$shell) {
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
        'relateduserid' => !empty($card['identity']['moodle_user_id']) ? (int) $card['identity']['moodle_user_id'] : null,
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

/**
 * Сигнал дубля / расхождения → подпись для оператора.
 *
 * @param string $signal например same_name:1,2 или wp:order_email_mismatch
 * @return string
 */
function local_kabperson_signal(string $signal): string {
    [$key, $arg] = array_pad(explode(':', $signal, 2), 2, '');
    if ($key === 'wp') {
        // Сигналы самой WP-карточки: wp:no_moodle_link, wp:order_email_mismatch.
        $key = 'wp_' . $arg;
        $arg = '';
    }
    $sm = get_string_manager();
    if ($sm->string_exists('sig_' . $key, 'local_kabperson')) {
        return get_string('sig_' . $key, 'local_kabperson', $arg);
    }
    return $signal;
}

/** Сколько строк курсов и оплат показывать сразу; остальное — под «ещё N». */
const LOCAL_KABPERSON_LIST_LIMIT = 10;

/**
 * CSS-класс строки заказа: ждёт — warn, не прошёл — bad (api::order_state).
 *
 * @param array $order
 * @return string
 */
function local_kabperson_status_class(array $order): string {
    return ['pending' => 'warn', 'failed' => 'bad'][\local_kabperson\api::order_state($order)] ?? '';
}

/**
 * ISO-дата → «сегодня», «вчера», «5 дн назад», «3 мес назад», «2 г назад» (или «—»).
 *
 * @param string|null $iso
 * @return string
 */
function local_kabperson_ago(?string $iso): string {
    $ts = $iso ? strtotime($iso) : false;
    if (!$ts) {
        return get_string('nothing', 'local_kabperson');
    }
    $days = (int) floor((time() - $ts) / DAYSECS);
    if ($days <= 0) {
        return get_string('ago_today', 'local_kabperson');
    }
    if ($days === 1) {
        return get_string('ago_yesterday', 'local_kabperson');
    }
    if ($days < 30) {
        return get_string('ago_days', 'local_kabperson', $days);
    }
    if ($days < 365) {
        return get_string('ago_months', 'local_kabperson', (int) floor($days / 30));
    }
    return get_string('ago_years', 'local_kabperson', (int) floor($days / 365));
}

$str = function (string $id, $a = null): string {
    return s(get_string($id, 'local_kabperson', $a));
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
input[type=email]{padding:.3rem .5rem;border:1px solid #ccc;border-radius:4px;min-width:16rem;max-width:100%}
button,a.btn{padding:.3rem .7rem;border:1px solid #bbb;border-radius:4px;background:#f4f4f4;color:#222;cursor:pointer;text-decoration:none;font-size:.85rem}
.muted{color:#777}.ok{color:#1a7f37}.warn{color:#b35900}.bad{color:#b3261e}
tr.bad td{color:#b3261e}tr.warn td{color:#b35900}
.note{background:#fff4e5;border:1px solid #f0c890;border-radius:6px;padding:.5rem .7rem;margin:0 0 .75rem;color:#7a4100}
.chips{margin-top:1rem;font-size:.8rem;color:#777}
.chips span{display:inline-block;border:1px solid #e0e0e0;border-radius:10px;padding:.05rem .5rem;margin-right:.25rem}
.kv td:first-child{width:12rem;color:#555;background:#fafafa}
details summary{cursor:pointer;color:#1f6feb;font-size:.85rem;margin:.4rem 0}
.summary{border:1px solid #e3e3e3;border-radius:8px;padding:.7rem .9rem;margin:0 0 .5rem;background:#fafbfc}
h2.who{border:0;margin:0;padding:0;font-size:1.05rem}
.badge{display:inline-block;font-size:.75rem;font-weight:600;padding:.1rem .55rem;border-radius:1rem;margin:.3rem .3rem 0 0}
.b-dup{background:#ffebe9;color:#cf222e}.b-donor{background:#e6f4ea;color:#1a7f37}
.facts{display:flex;flex-wrap:wrap;gap:.6rem 1.6rem;margin:.6rem 0 .1rem}
.fact .k{font-size:.7rem;color:#777;text-transform:uppercase;letter-spacing:.03em}
.fact .v{font-size:1.05rem;font-weight:700;line-height:1.25}.fact .v small{font-size:.8rem;font-weight:400;color:#777}
</style></head><body>';

echo '<h1>' . $str('viewtitle') . '</h1>';
if ($unverified && $email !== '') {
    echo '<div class="note">' . $str('unverified') . '</div>';
}
echo '<form method="get" action="' . s($selfurl->out(false)) . '">';
echo '<label>' . $str('emaillabel') . ' <input type="email" name="email" value="' . s($email) . '" required></label>';
if ($contactid) {
    echo '<input type="hidden" name="contact_id" value="' . (int) $contactid . '">';
}
if ($unverified) {
    echo '<input type="hidden" name="unverified" value="1">';
}
echo '<button type="submit">' . $str('show') . '</button>';
echo '<button type="submit" name="refresh" value="1">' . $str('refresh') . '</button>';
if ($email !== '') {
    echo '<a class="btn" href="' . s($jsonurl->out(false)) . '">' . $str('jsonlink') . '</a>';
}
echo '</form>';

if ($card === null && $msgorigins) {
    echo '<p id="kabp-wait" class="' . ($noemail ? 'warn' : 'muted') . '">' .
        $str($noemail ? 'nocontactemail' : 'waitingcontact') . '</p>';
}

if ($card !== null) {
    $id = $card['identity'] ?? null;
    $found = !empty($card['found']);

    // ---- Сводка сверху: что оператору важно за 2 секунды (как у WP-карточки 0.2). ----
    if ($id) {
        $badges = '';
        if (!$found) {
            $badges .= '<span class="badge b-dup">' . $str('notinmoodle') . '</span>';
        } else if ($id['status'] !== 'active') {
            $badges .= '<span class="badge b-dup">' .
                $str(['suspended' => 'statussuspended', 'deleted' => 'statusdeleted'][$id['status']] ?? 'statusactive') .
                '</span>';
        }
        foreach ($id['possible_duplicates'] as $signal) {
            $badges .= '<span class="badge b-dup">⚠ ' . s(local_kabperson_signal($signal)) . '</span>';
        }
        foreach ((array) ($card['payments']['donor_total'] ?? []) as $cur => $sum) {
            $badges .= '<span class="badge b-donor">' . $str('donorbadge', $sum . ' ' . $cur) . '</span>';
        }

        $facts = [];
        if ($found) {
            $learning = array_values(array_filter($card['learning']['courses'], function ($c) {
                return $c['role'] !== 'teacher';
            }));
            $teaching = count($card['learning']['courses']) - count($learning);
            $passed = count(array_filter($learning, function ($c) {
                return $c['completed'] === true;
            }));
            $v = '<span class="ok">' . $passed . '</span>/' . count($learning) . ' <small>' . $str('sum_passed') . '</small>';
            if ($teaching) {
                $v .= ' <small>· ' . $str('sum_teaching', $teaching) . '</small>';
            }
            $facts[] = [$str('sum_courses'), $v];

            // «Сейчас учит» — последний посещённый курс из ещё не пройденных с активной записью.
            $current = array_values(array_filter($learning, function ($c) {
                return $c['enrol_status'] === 'active' && $c['completed'] !== true;
            }));
            usort($current, function ($a, $b) {
                return strtotime((string) $b['last_access']) <=> strtotime((string) $a['last_access']);
            });
            if ($current && $current[0]['last_access']) {
                $c = $current[0];
                $name = core_text::strlen($c['name']) > 40 ? core_text::substr($c['name'], 0, 40) . '…' : $c['name'];
                $v = '<span title="' . s($c['name']) . '">' . s($name) . '</span> <small>' .
                    ($c['progress_pct'] !== null ? (int) $c['progress_pct'] . '%' : '') .
                    (count($current) > 1 ? ' (+' . (count($current) - 1) . ')' : '') . '</small>';
            } else {
                $v = '<small>' . $str('nothing') . '</small>';
            }
            $facts[] = [$str('sum_current'), $v];
            $facts[] = [$str('sum_lastseen'), '<small>' . s(local_kabperson_ago($id['last_login'])) . '</small>'];
        }
        if (isset($card['payments']['counts'])) {
            $n = $card['payments']['counts'];
            $v = (int) $n['paid'] . ' <small>' . $str('sum_paid') . '</small>';
            if ($n['pending']) {
                $v .= ' <small>·</small> <span class="warn">' . (int) $n['pending'] . '</span> <small>' . $str('sum_pending') . '</small>';
            }
            if ($n['failed']) {
                $v .= ' <small>·</small> <span class="muted">' . (int) $n['failed'] . '</span> <small>' . $str('sum_failed') . '</small>';
            }
            $facts[] = [$str('sum_payments'), $v];
        }
        $facts[] = [$str('sum_since'), '<small>' . s($id['registered_at'] ? substr($id['registered_at'], 0, 10) : '—') . '</small>'];
        $mid = (int) $id['moodle_user_id'];
        $moodlecell = $mid ? '<a href="' . s((new moodle_url('/user/profile.php', ['id' => $mid]))->out(false)) .
            '" target="_blank" rel="noopener">' . $mid . ' ↗</a>' : '—';
        $facts[] = [$str('sum_ids'), '<small>' . ((int) $id['wp_user_id'] ?: '—') . ' / ' . $moodlecell . '</small>'];

        echo '<div class="summary"><h2 class="who">' . s($id['name'] ?? $id['email']) . '</h2>';
        echo '<div class="muted">' . s($id['email']) . '</div><div>' . $badges . '</div><div class="facts">';
        foreach ($facts as [$k, $v]) {
            echo '<div class="fact"><div class="k">' . $k . '</div><div class="v">' . $v . '</div></div>';
        }
        echo '</div></div>';
    }

    if (!$found) {
        echo '<p class="warn">' . $str('notfound') . '</p>';
        if ($id) {
            // Нет в Moodle, но есть в старом WP: показываем, что знаем, и его оплаты.
            echo '<p>' . $str('wponly') . '</p><table class="kv">';
            echo '<tr><td>' . $str('viewtitle') . '</td><td><b>' . s($id['name'] ?? '—') . '</b> ' .
                '<span class="muted">wp id ' . (int) $id['wp_user_id'] . '</span></td></tr>';
            echo '<tr><td>' . $str('emaillabel') . '</td><td>' . s(implode(', ', $id['emails'])) . '</td></tr>';
            echo '<tr><td>' . $str('registered') . '</td><td>' . s(local_kabperson_dt($id['registered_at'])) . '</td></tr>';
            echo '</table>';
        }
    } else {
        $statusclass = ['active' => 'ok', 'suspended' => 'warn', 'deleted' => 'bad'][$id['status']] ?? '';
        $statusstr = ['active' => 'statusactive', 'suspended' => 'statussuspended', 'deleted' => 'statusdeleted'];
        // Подробности о человеке — свёрнуты: главное уже в сводке.
        echo '<details><summary>' . $str('identitydetails') . '</summary><table class="kv">';
        $ids = 'moodle id ' . (int) $id['moodle_user_id'];
        if (!empty($id['wp_user_id'])) {
            $ids .= ' · wp id ' . (int) $id['wp_user_id'];
        }
        echo '<tr><td>' . $str('viewtitle') . '</td><td><b>' . s($id['name'] ?? '—') . '</b> ' .
            '<span class="' . $statusclass . '">(' . $str($statusstr[$id['status']] ?? 'statusactive') . ')</span> ' .
            '<span class="muted">' . s($ids) . '</span></td></tr>';
        echo '<tr><td>' . $str('emaillabel') . '</td><td>' . s(implode(', ', $id['emails'])) . '</td></tr>';
        echo '<tr><td>' . $str('registered') . '</td><td>' . s(local_kabperson_dt($id['registered_at'])) . '</td></tr>';
        echo '<tr><td>' . $str('lastlogin') . '</td><td>' . s(local_kabperson_dt($id['last_login'])) . '</td></tr>';
        echo '<tr><td>' . $str('telegram') . '</td><td>' . ($id['telegram_linked'] ? '✓' : '—') . '</td></tr>';
        if (!empty($id['possible_duplicates'])) {
            echo '<tr><td>' . $str('duplicates') . '</td><td class="warn">' .
                s(implode('; ', array_map('local_kabperson_signal', $id['possible_duplicates']))) . '</td></tr>';
        }
        echo '</table></details>';

        $lr = $card['learning'];
        echo '<h2>' . $str('sec_learning') . '</h2>';
        if (empty($lr['courses'])) {
            echo '<p class="muted">' . $str('nothing') . '</p>';
        } else {
            // Свежие сверху; больше LIST_LIMIT — остальное под «ещё N».
            $courses = $lr['courses'];
            usort($courses, function ($a, $b) {
                return strtotime((string) $b['last_access']) <=> strtotime((string) $a['last_access']);
            });
            $coursestable = function (array $rows) use ($str): string {
                $h = '<table><tr><th>' . $str('col_course') . '</th><th>' . $str('col_role') . '</th><th>' .
                    $str('col_enrolstatus') . '</th><th>' . $str('col_progress') . '</th><th>' .
                    $str('col_lastaccess') . '</th><th>' . $str('col_badges') . '</th></tr>';
                foreach ($rows as $c) {
                    $role = $c['role_label'] ?? $c['role'] ?? '—';
                    if (!empty($c['restrictions'])) {
                        $role .= ' · ' . implode(', ', $c['restrictions']);
                    }
                    $badges = $c['badges'] ? implode(', ', array_column($c['badges'], 'name')) : '—';
                    $progress = $c['progress_pct'] === null ? '—' : $c['progress_pct'] . '%';
                    if ($c['completed'] === true) {
                        $progress .= ' ✓';
                    }
                    $h .= '<tr><td>' . s($c['name']) . ' <span class="muted">#' . (int) $c['course_id'] . '</span></td><td>' .
                        s((string) $role) . '</td><td>' . s($c['enrol_status']) . '</td><td>' . s($progress) . '</td><td>' .
                        s(local_kabperson_dt($c['last_access'])) . '</td><td>' . s($badges) . '</td></tr>';
                }
                return $h . '</table>';
            };
            echo $coursestable(array_slice($courses, 0, LOCAL_KABPERSON_LIST_LIMIT));
            if (count($courses) > LOCAL_KABPERSON_LIST_LIMIT) {
                echo '<details><summary>' . $str('morecourses', count($courses) - LOCAL_KABPERSON_LIST_LIMIT) .
                    '</summary>' . $coursestable(array_slice($courses, LOCAL_KABPERSON_LIST_LIMIT)) . '</details>';
            }
        }
        if (!empty($lr['cohorts'])) {
            echo '<p><b>' . $str('cohorts') . ':</b> ' . s(implode(', ', $lr['cohorts'])) . '</p>';
        }
        if (!empty($lr['site_badges'])) {
            echo '<p><b>' . $str('sitebadges') . ':</b> ' .
                s(implode(', ', array_column($lr['site_badges'], 'name'))) . '</p>';
        }
    }

    if (isset($card['payments'])) {
        $pm = $card['payments'];
        echo '<h2>' . $str('sec_payments') . '</h2>';

        // Оплаты и ожидающие — на виду; не прошедшие и бесплатные записи — свёрнуты (§3 спеки,
        // как у WP-карточки 0.2).
        $shown = [];
        $failed = [];
        $free = [];
        foreach ($pm['orders'] as $o) {
            $state = \local_kabperson\api::order_state($o);
            if ($state === 'free') {
                $free[] = $o;
            } else if ($state === 'failed') {
                $failed[] = $o;
            } else {
                $shown[] = $o;
            }
        }
        $orderstable = function (array $rows) use ($str): string {
            $h = '<table><tr><th>' . $str('col_source') . '</th><th>#</th><th>' . $str('col_date') . '</th><th>' .
                $str('col_product') . '</th><th>' . $str('col_kind') . '</th><th>' . $str('col_amount') .
                '</th><th>' . $str('col_status') . '</th><th>' . $str('col_gateway') . '</th></tr>';
            foreach ($rows as $o) {
                $cls = local_kabperson_status_class($o);
                $num = !empty($o['number']) ? (string) $o['number'] : (string) (int) $o['id'];
                $amount = $o['amount'] . ' ' . $o['currency'];
                if (!empty($o['refunded'])) {
                    $amount .= ' (' . get_string('refundednote', 'local_kabperson', $o['refunded']) . ')';
                }
                $h .= '<tr' . ($cls ? ' class="' . $cls . '"' : '') . '><td>' . $str('src_' . $o['source']) .
                    '</td><td>' . s($num) . '</td><td>' . s(local_kabperson_dt($o['date'])) . '</td><td>' .
                    s($o['product']) . '</td><td>' . s($o['kind'] . ' · ' . $o['type']) . '</td><td>' .
                    s($amount) . '</td><td>' . s($o['status']) . '</td><td>' . s($o['gateway'] ?: '—') . '</td></tr>';
            }
            return $h . '</table>';
        };

        echo '<h3>' . $str('orders') . '</h3>';
        echo $shown ? $orderstable(array_slice($shown, 0, LOCAL_KABPERSON_LIST_LIMIT)) : '<p class="muted">' . $str('nopaid') . '</p>';
        if (count($shown) > LOCAL_KABPERSON_LIST_LIMIT) {
            echo '<details><summary>' . $str('moreorders', count($shown) - LOCAL_KABPERSON_LIST_LIMIT) . '</summary>' .
                $orderstable(array_slice($shown, LOCAL_KABPERSON_LIST_LIMIT)) . '</details>';
        }
        if ($failed) {
            echo '<details><summary>' . $str('failedorders', count($failed)) . '</summary>' . $orderstable($failed) . '</details>';
        }
        if ($free) {
            echo '<details><summary>' . $str('freerecords', count($free)) . '</summary>' . $orderstable($free) . '</details>';
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

echo local_kabperson_listener($msgorigins, $email, $contactid);
echo '</body></html>';
