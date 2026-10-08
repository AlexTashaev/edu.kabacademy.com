<?php
// Exercises sender::post() against a scripted stand-in for Moodle's curl class, outside Moodle:
//   php tests/standalone/delivery.php
if (PHP_SAPI !== 'cli') {
    die;
}
define('MOODLE_INTERNAL', 1);
$CFG = new stdClass();
$CFG->dirroot = __DIR__ . '/stub';
$CFG->libdir = __DIR__ . '/stub/lib';
$CFG->wwwroot = 'https://edu.example';

class curl {
    /** @var array[] answers still to give, in order */
    public static $script = [];
    /** @var string[] requests made */
    public static $log = [];
    public $error = '';
    private $cur = ['code' => 0, 'body' => ''];

    public function setHeader($h) {
    }
    public function post($url, $params = '', $options = []) {
        return $this->next('POST', $url);
    }
    public function get($url, $params = [], $options = []) {
        return $this->next('GET', $url);
    }
    private function next($method, $url) {
        self::$log[] = $method . ' ' . parse_url($url, PHP_URL_HOST);
        $this->cur = array_shift(self::$script) ?? ['code' => 0, 'body' => '', 'errno' => 7, 'error' => 'script is over'];
        $this->error = $this->cur['error'] ?? '';
        return $this->cur['body'] ?? '';
    }
    public function get_info() {
        return ['http_code' => $this->cur['code'], 'redirect_url' => $this->cur['to'] ?? ''];
    }
    public function get_errno() {
        return $this->cur['errno'] ?? 0;
    }
    public function get_raw_response() {
        return [];
    }
}

if (!function_exists('mb_substr')) {
    // The PHP on this workstation has no mbstring; the server's has.
    function mb_substr($s, $start, $length = null) {
        return implode('', array_slice(preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY), $start, $length));
    }
}

$config = (object)['webhookurl' => 'https://script.google.com/macros/s/X/exec', 'secret' => 's'];
function get_config($plugin) {
    global $config;
    return $config;
}

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

const ECHO_URL = 'https://script.googleusercontent.com/macros/echo?user_content_key=k';
const EXEC_URL = 'https://script.google.com/macros/s/X/exec';
$posted = ['code' => 302, 'to' => ECHO_URL];
$written = ['code' => 200, 'body' => '{"ok":true,"written":1,"duplicates":0,"tables":["T (Moodle)"]}'];
$duplicate = ['code' => 200, 'body' => '{"ok":true,"written":0,"duplicates":1,"tables":["T (Moodle)"],"duplicate":true}'];
$ping = ['code' => 200, 'body' => '{"ok":true,"ping":"local_kabfeedbackgdoc","version":5}'];
$lost = [['code' => 302, 'to' => ECHO_URL], ['code' => 302, 'to' => EXEC_URL], ['code' => 302, 'to' => ECHO_URL], $ping];
$gone = [['code' => 302, 'to' => ECHO_URL], ['code' => 404, 'body' => '<html><body>Not   Found</body></html>']];

function run(array $script): array {
    curl::$script = $script;
    curl::$log = [];
    $r = sender::post(['event' => 'feedback_response', 'completedid' => 1]);
    return [$r['ok'], $r['tries'], count(curl::$log), count(curl::$script), $r['body']];
}

check('delivered at once', run([$posted, $written]),
    [true, 1, 2, 0, $written['body']]);
check('answer lost, asked again, the row was there', run(array_merge($lost, [$posted, $duplicate])),
    [true, 2, 6, 0, $duplicate['body'] . ' (try 2)']);
check('404 of the content host twice, then delivered', run(array_merge($gone, $gone, [$posted, $written])),
    [true, 3, 6, 0, $written['body'] . ' (try 3)']);
check('lost three times: the task will fail', run(array_merge($lost, $lost, $lost, [$posted, $written])),
    [false, 3, 12, 2, 'not the answer of doPost(): ' . $ping['body'] . ' (try 3)']);
check('refused by the script: not asked again',
    run([$posted, ['code' => 200, 'body' => '{"ok":false,"error":"forbidden"}'], $posted, $written]),
    [false, 1, 2, 2, '{"ok":false,"error":"forbidden"}']);
check('login page instead of the script', run(array_fill(0, 3,
    ['code' => 200, 'body' => "<html><title>Sign in</title>\n<body>Sign  in to continue</body></html>"])),
    [false, 3, 3, 0, 'not JSON: Sign in Sign in to continue (try 3)']);
check('timeout, then delivered', run([['code' => 0, 'errno' => 28, 'error' => 'Operation timed out'], $posted, $written]),
    [true, 2, 3, 0, $written['body'] . ' (try 2)']);

curl::$script = [['code' => 302, 'to' => ECHO_URL], $ping];
curl::$log = [];
$r = sender::ping();
check('ping', [$r['ok'], $r['json']['version'], curl::$log], [true, 5, ['GET script.google.com', 'GET script.googleusercontent.com']]);

$config = (object)['webhookurl' => ''];
$config = (object)['webhookurl' => 'https://script.google.com/macros/s/X/exec', 'secret' => 's'];
curl::$script = [$posted, ['code' => 200, 'body' => '{"ok":true,"moved":12,"from":{"8:00":8},"table":"T (Moodle)"}']];
curl::$log = [];
$r = sender::post_archive(['spreadsheet' => '', 'sheet' => '', 'layout' => 'questions'], 13448, 'Вопрос');
check('archive request: the answer of doPost() carries "moved" instead of "written"', [$r['ok'], $r['tries'], $r['json']['moved']],
    [true, 1, 12]);
curl::$script = [$posted, $ping];
check('archive request answered with the ping is not a delivery', sender::post_archive(['spreadsheet' => '', 'sheet' => '',
    'layout' => 'questions'], 13448, 'Вопрос')['ok'], false);

$config = (object)['webhookurl' => ''];
check('not configured: nothing is sent', [run([$posted, $written])[0], count(curl::$log)], [false, 0]);

echo $fails ? "\n$fails FAILED\n" : "\nall passed\n";
exit($fails ? 1 : 0);
