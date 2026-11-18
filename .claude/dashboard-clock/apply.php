<?php
/**
 * Часы в HTML-блоке «Время» личного кабинета: ставит разметку из clock.html вместо
 * стороннего виджета worldtimebuddy или вместо предыдущей версии наших часов.
 * Спецификация — SPEC.md рядом.
 *
 * Обычный PHP + mysqli. Moodle НЕ загружается: dataroot для CLI не писабелен (см. CLAUDE.md).
 *
 *   php apply.php --config=/путь/к/config.php check
 *       показать, что будет изменено (ничего не пишет)
 *   php apply.php --config=/путь/к/config.php --tag=ГГГГММДД apply
 *       бэкап текущего содержимого в _kab_bk<tag>_block_instances + замена
 *   php apply.php --config=/путь/к/config.php --tag=ГГГГММДД rollback
 *       вернуть содержимое из бэкапа с этим тегом
 */

const MARKER_THIRD_PARTY = 'worldtimebuddy';
const MARKER_OURS = 'class="kab-clock"';
const THIRD_PARTY_RE = '~<span class="wtb-ew-v1".*?</script></span>~s';
const OURS_RE = '~<div class="kab-clock".*?</script>~s';
// Пояснение под часами ссылалось на английские подписи старого виджета.
const CAPTION_OLD = 'Эти часы показывают изр. вр. (Israel) и ваше (Your Time).';
const CAPTION_NEW = 'Эти часы показывают израильское время и ваше местное.';

$opts = getopt('', ['config:', 'tag:']);
$mode = $argv[$argc - 1] ?? '';
if (empty($opts['config']) || !in_array($mode, ['check', 'apply', 'rollback'], true)) {
    fwrite(STDERR, "usage: php apply.php --config=/path/config.php [--tag=YYYYMMDD] check|apply|rollback\n");
    exit(2);
}
$backup = null;
if ($mode !== 'check') {
    if (empty($opts['tag']) || !preg_match('/^\d{8}$/', $opts['tag'])) {
        fwrite(STDERR, "apply/rollback need --tag=YYYYMMDD (names the backup table)\n");
        exit(2);
    }
    $backup = '_kab_bk' . $opts['tag'] . '_block_instances';
}

$cfgsrc = file_get_contents($opts['config']);
if ($cfgsrc === false) {
    fwrite(STDERR, "cannot read config\n");
    exit(2);
}
$cfg = [];
foreach (['dbhost', 'dbname', 'dbuser', 'dbpass', 'prefix'] as $k) {
    if (!preg_match('/CFG->' . $k . '\s*=\s*[\'"]([^\'"]*)[\'"]/', $cfgsrc, $m)) {
        fwrite(STDERR, "config: $k not found\n");
        exit(2);
    }
    $cfg[$k] = $m[1];
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass'], $cfg['dbname']);
$db->set_charset('utf8mb4');
$table = $cfg['prefix'] . 'block_instances';

if ($mode === 'rollback') {
    $db->begin_transaction();
    $db->query("UPDATE `$table` bi JOIN `$backup` b ON b.id = bi.id
                   SET bi.configdata = b.configdata, bi.timemodified = b.timemodified");
    $n = $db->affected_rows;
    $db->commit();
    echo "rollback from $backup: restored rows: $n\n";
    exit(0);
}

$clock = file_get_contents(__DIR__ . '/clock.html');
$clock = $clock === false ? '' : trim($clock);
if (strpos($clock, '<div ' . MARKER_OURS) !== 0 || substr($clock, -9) !== '</script>'
        || substr_count($clock, '</script>') !== 1
        || preg_match('~(?:src|href)\s*=|https?:~i', $clock)) {
    fwrite(STDERR, "clock.html: must be one kab-clock div followed by one script, with no external references\n");
    exit(2);
}

// Только блоки HTML на странице личного кабинета. Блоки в курсах не трогаем.
$res = $db->query("SELECT id, configdata FROM `$table`
                    WHERE blockname = 'html' AND pagetypepattern = 'my-index' ORDER BY id");
$targets = [];
$uptodate = 0;
while ($row = $res->fetch_assoc()) {
    $raw = base64_decode($row['configdata'], true);
    if ($raw === false) {
        continue;
    }
    $third = strpos($raw, MARKER_THIRD_PARTY) !== false;
    $ours = strpos($raw, MARKER_OURS) !== false;
    if (!$third && !$ours) {
        continue;
    }
    $obj = @unserialize($raw, ['allowed_classes' => ['stdClass']]);
    if (!($obj instanceof stdClass) || !isset($obj->text) || !is_string($obj->text)) {
        fwrite(STDERR, "block {$row['id']}: unexpected config structure, abort\n");
        exit(1);
    }
    $count = 0;
    // Замена через callback: в clock.html не должно интерпретироваться ничего вроде $1.
    $newtext = preg_replace_callback($third ? THIRD_PARTY_RE : OURS_RE, static fn () => $clock, $obj->text, 1, $count);
    if ($count !== 1 || $newtext === null || strpos($newtext, MARKER_THIRD_PARTY) !== false) {
        fwrite(STDERR, "block {$row['id']}: clock markup not matched exactly once, abort\n");
        exit(1);
    }
    $captions = 0;
    $newtext = str_replace(CAPTION_OLD, CAPTION_NEW, $newtext, $captions);
    if ($newtext === $obj->text) {
        $uptodate++;
        continue;
    }
    $new = clone $obj;
    $new->text = $newtext;
    $targets[] = [
        'id' => (int) $row['id'],
        'old' => $row['configdata'],
        'new' => base64_encode(serialize($new)),
        'from' => $third ? 'third-party widget' : 'previous clock',
        'captions' => $captions,
        'oldlen' => strlen($obj->text),
        'newlen' => strlen($newtext),
    ];
}

echo 'targets: ' . count($targets) . ", already up to date: $uptodate\n";
foreach ($targets as $t) {
    echo "  block {$t['id']}: {$t['from']}, text {$t['oldlen']} -> {$t['newlen']} bytes, caption replaced: {$t['captions']}\n";
}
if (!$targets) {
    echo "nothing to do\n";
    exit(0);
}
if ($mode === 'check') {
    echo "check only, nothing written\n";
    exit(0);
}

// --- apply ---
$ids = implode(',', array_column($targets, 'id'));
$db->query("CREATE TABLE IF NOT EXISTS `$backup` LIKE `$table`");
$db->query("INSERT INTO `$backup` SELECT * FROM `$table` t
             WHERE t.id IN ($ids) AND t.id NOT IN (SELECT id FROM (SELECT id FROM `$backup`) x)");
// Бэкап обязан содержать текущее (до замены) содержимое каждой цели.
$res = $db->query("SELECT id, configdata FROM `$backup` WHERE id IN ($ids)");
$bk = [];
while ($row = $res->fetch_assoc()) {
    $bk[(int) $row['id']] = $row['configdata'];
}
foreach ($targets as $t) {
    if (!isset($bk[$t['id']]) || $bk[$t['id']] !== $t['old']) {
        fwrite(STDERR, "backup mismatch for block {$t['id']} in $backup (tag already used?), abort before any update\n");
        exit(1);
    }
}
echo 'backup ok: ' . count($bk) . " rows in $backup\n";

$now = time();
$db->begin_transaction();
$stmt = $db->prepare("UPDATE `$table` SET configdata = ?, timemodified = ? WHERE id = ? AND configdata = ?");
foreach ($targets as $t) {
    $stmt->bind_param('siis', $t['new'], $now, $t['id'], $t['old']);
    $stmt->execute();
    if ($stmt->affected_rows !== 1) {
        $db->rollback();
        fwrite(STDERR, "block {$t['id']}: changed concurrently, transaction rolled back\n");
        exit(1);
    }
}
$db->commit();

// Контроль: всё читается обратно, стоит именно текущая разметка, стороннего виджета нет.
$res = $db->query("SELECT id, configdata FROM `$table` WHERE id IN ($ids)");
$ok = 0;
while ($row = $res->fetch_assoc()) {
    $obj = @unserialize(base64_decode($row['configdata'], true), ['allowed_classes' => ['stdClass']]);
    if ($obj instanceof stdClass && strpos($obj->text, MARKER_THIRD_PARTY) === false && strpos($obj->text, $clock) !== false) {
        $ok++;
    }
}
echo 'updated: ' . count($targets) . ", verified: $ok\n";
exit($ok === count($targets) ? 0 : 1);
