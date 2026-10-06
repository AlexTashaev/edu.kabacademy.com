<?php
// Exercises the routing functions of sender.php (which form goes to which table) outside Moodle:
//   php tests/standalone/routing.php
if (PHP_SAPI !== 'cli') {
    die;
}
define('MOODLE_INTERNAL', 1);
$CFG = new stdClass();
$CFG->dirroot = __DIR__ . '/stub';
$CFG->libdir = __DIR__ . '/stub/lib';
require(__DIR__ . '/../../classes/sender.php');

use local_kabfeedbackgdoc\sender;

$fails = 0;
function check(string $what, $got, $want): void {
    global $fails;
    $ok = ($got === $want);
    if (!$ok) {
        $fails++;
    }
    echo ($ok ? 'ok   ' : 'FAIL ') . $what . ($ok ? '' : "\n     got:  " . var_export($got, true) .
        "\n     want: " . var_export($want, true)) . "\n";
}

$id = '1Csah7xxl8Q6gxfWWnZ9QVOrD9njvryMnsfM3mBMkMxo';
check('id from url', sender::spreadsheet_id("https://docs.google.com/spreadsheets/d/$id/edit?usp=sharing#gid=0"), $id);
check('bare id', sender::spreadsheet_id("  $id "), $id);
check('garbage', sender::spreadsheet_id('https://example.com/x'), '');
check('empty', sender::spreadsheet_id(''), '');

$routes = sender::routes(
    "# comment\r\n" .
    "12951 = https://docs.google.com/spreadsheets/d/$id/edit?usp=sharing#gid=0\r\n" .
    "\n" .
    " 13000=$id | Лист с ответами \n" .
    "bad line\n" .
    "0 = $id\n" .
    "77 = not-a-sheet\n"
);
check('routes', $routes, [
    12951 => ['spreadsheet' => $id, 'sheet' => ''],
    13000 => ['spreadsheet' => $id, 'sheet' => 'Лист с ответами'],
]);
check('routes empty', sender::routes(''), []);

$prod = (object)['cmids' => '', 'courseids' => '236,238', 'namepattern' => 'вопрос',
    'routes' => "12951 = $id", 'defaulttarget' => ''];
$q = 'Вопрос по теме урока 1 к вебинару с преподавателями';
check('236 question form -> default', sender::target_for_cm(13448, 236, $q, $prod),
    ['spreadsheet' => '', 'sheet' => '', 'layout' => 'questions']);
check('238 question form -> default', sender::target_for_cm(13407, 238, $q, $prod),
    ['spreadsheet' => '', 'sheet' => '', 'layout' => 'questions']);
check('study groups -> own table', sender::target_for_cm(12951, 236, 'Учебные группы', $prod),
    ['spreadsheet' => $id, 'sheet' => '', 'layout' => 'generic']);
check('other form in 236 -> none', sender::target_for_cm(12000, 236, 'Отзыв о курсе', $prod), null);
check('question form in other course -> none', sender::target_for_cm(500, 231, $q, $prod), null);
check('case-insensitive cyrillic', sender::target_for_cm(1, 236, 'ВОПРОС к вебинару', $prod) !== null, true);

$old = (object)['cmids' => '', 'courseids' => '238', 'namepattern' => 'вопрос'];
check('before the fix: 236 form not forwarded', sender::target_for_cm(13448, 236, $q, $old), null);

$byid = (object)['cmids' => '13407, 13448', 'courseids' => '', 'namepattern' => '',
    'defaulttarget' => "https://docs.google.com/spreadsheets/d/$id/edit"];
check('cmids filter hit', sender::target_for_cm(13448, 236, $q, $byid),
    ['spreadsheet' => $id, 'sheet' => '', 'layout' => 'questions']);
check('cmids filter miss', sender::target_for_cm(12951, 236, 'Учебные группы', $byid), null);

$all = (object)[];
check('no filters = everything', sender::target_for_cm(5, 1, 'x', $all),
    ['spreadsheet' => '', 'sheet' => '', 'layout' => 'questions']);
check('broken regex never matches', sender::matches_filters(1, 1, 'вопрос', (object)['namepattern' => '(']), false);

echo $fails ? "\n$fails FAILED\n" : "\nall passed\n";
exit($fails ? 1 : 0);
