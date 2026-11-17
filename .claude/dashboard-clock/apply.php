<?php
/**
 * Замена виджета часов worldtimebuddy в HTML-блоке «Время» личного кабинета на встроенные часы.
 * Спецификация — SPEC.md рядом.
 *
 * Обычный PHP + mysqli. Moodle НЕ загружается: dataroot для CLI не писабелен (см. CLAUDE.md).
 *
 *   php apply.php --config=/путь/к/config.php check      показать, что будет изменено (ничего не пишет)
 *   php apply.php --config=/путь/к/config.php apply      бэкап в таблицу + замена
 *   php apply.php --config=/путь/к/config.php rollback   вернуть содержимое из бэкапа
 */

const BACKUP_TABLE = '_kab_bk20261117_block_instances';
const MARKER = 'worldtimebuddy';
const WIDGET_RE = '~<span class="wtb-ew-v1".*?</script></span>~s';

$opts = getopt('', ['config:']);
$mode = $argv[$argc - 1] ?? '';
if (empty($opts['config']) || !in_array($mode, ['check', 'apply', 'rollback'], true)) {
    fwrite(STDERR, "usage: php apply.php --config=/path/config.php check|apply|rollback\n");
    exit(2);
}

$cfgsrc = file_get_contents($opts['config']);
if ($cfgsrc === false) {
    fwrite(STDERR, "cannot read config\n");
    exit(2);
}
$cfg = [];
foreach (['dbhost', 'dbname', 'dbuser', 'dbpass', 'prefix'] as $k) {
    if (preg_match('/CFG->' . $k . '\s*=\s*[\'"]([^\'"]*)[\'"]/', $cfgsrc, $m)) {
        $cfg[$k] = $m[1];
    }
}
foreach (['dbhost', 'dbname', 'dbuser', 'dbpass', 'prefix'] as $k) {
    if (!isset($cfg[$k])) {
        fwrite(STDERR, "config: $k not found\n");
        exit(2);
    }
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass'], $cfg['dbname']);
$db->set_charset('utf8mb4');
$table = $cfg['prefix'] . 'block_instances';

if ($mode === 'rollback') {
    $db->begin_transaction();
    $db->query("UPDATE `$table` bi JOIN `" . BACKUP_TABLE . "` b ON b.id = bi.id
                   SET bi.configdata = b.configdata, bi.timemodified = b.timemodified");
    $n = $db->affected_rows;
    $db->commit();
    echo "rollback: restored rows: $n\n";
    exit(0);
}

$clock = file_get_contents(__DIR__ . '/clock.html');
if ($clock === false || strpos($clock, MARKER) !== false || preg_match('~(?:src|href)\s*=|https?:~i', $clock)) {
    fwrite(STDERR, "clock.html missing or references an external resource\n");
    exit(2);
}
$clock = trim($clock);

// Только блоки HTML на странице личного кабинета. Блоки в курсах не трогаем.
$res = $db->query("SELECT id, configdata, timemodified FROM `$table`
                    WHERE blockname = 'html' AND pagetypepattern = 'my-index' ORDER BY id");
$targets = [];
while ($row = $res->fetch_assoc()) {
    $raw = base64_decode($row['configdata'], true);
    if ($raw === false || strpos($raw, MARKER) === false) {
        continue;
    }
    $obj = @unserialize($raw, ['allowed_classes' => ['stdClass']]);
    if (!($obj instanceof stdClass) || !isset($obj->text) || !is_string($obj->text)) {
        fwrite(STDERR, "block {$row['id']}: unexpected config structure, abort\n");
        exit(1);
    }
    $count = 0;
    $newtext = preg_replace(WIDGET_RE, $clock, $obj->text, 1, $count);
    if ($count !== 1 || $newtext === null || strpos($newtext, MARKER) !== false) {
        fwrite(STDERR, "block {$row['id']}: widget markup not matched exactly once, abort\n");
        exit(1);
    }
    $new = clone $obj;
    $new->text = $newtext;
    $targets[] = [
        'id' => (int) $row['id'],
        'old' => $row['configdata'],
        'new' => base64_encode(serialize($new)),
        'oldlen' => strlen($obj->text),
        'newlen' => strlen($newtext),
    ];
}

echo 'targets: ' . count($targets) . "\n";
foreach ($targets as $t) {
    echo "  block {$t['id']}: text {$t['oldlen']} -> {$t['newlen']} bytes, old md5 " . md5($t['old']) . "\n";
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
$db->query("CREATE TABLE IF NOT EXISTS `" . BACKUP_TABLE . "` LIKE `$table`");
$db->query("INSERT INTO `" . BACKUP_TABLE . "` SELECT * FROM `$table` t
             WHERE t.id IN ($ids) AND t.id NOT IN (SELECT id FROM (SELECT id FROM `" . BACKUP_TABLE . "`) x)");
// Бэкап обязан содержать исходное (ещё с виджетом) содержимое каждой цели.
$res = $db->query("SELECT id, configdata FROM `" . BACKUP_TABLE . "` WHERE id IN ($ids)");
$bk = [];
while ($row = $res->fetch_assoc()) {
    $bk[(int) $row['id']] = $row['configdata'];
}
foreach ($targets as $t) {
    if (!isset($bk[$t['id']]) || $bk[$t['id']] !== $t['old']) {
        fwrite(STDERR, "backup mismatch for block {$t['id']}, abort before any update\n");
        exit(1);
    }
}
echo 'backup ok: ' . count($bk) . ' rows in ' . BACKUP_TABLE . "\n";

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

// Контроль: всё читается обратно и виджета больше нет.
$res = $db->query("SELECT id, configdata FROM `$table` WHERE id IN ($ids)");
$ok = 0;
while ($row = $res->fetch_assoc()) {
    $obj = @unserialize(base64_decode($row['configdata'], true), ['allowed_classes' => ['stdClass']]);
    if ($obj instanceof stdClass && strpos($obj->text, MARKER) === false && strpos($obj->text, 'kab-clock-il') !== false) {
        $ok++;
    }
}
echo "updated: " . count($targets) . ", verified: $ok\n";
exit($ok === count($targets) ? 0 : 1);
