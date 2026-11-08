<?php
/**
 * Письмо через SendGrid тем же ключом, которым шлёт Moodle (mdl_config.smtppass), — как
 * kab-tools/repeat_students_report.php. Для отчётов, которые собираются не на сервере:
 * письмо целиком (вложения в base64) приходит JSON-ом на stdin, на диск сервера ничего не пишется.
 *
 *   ssh web-18 'php82 /sites/edu.kabacademy.com/public/kab-tools/webinars_report/send_report_mail.php' < mail.json
 *
 * mail.json: {"to": ["a@x"], "subject": "...", "html": "...", "from_name": "...",
 *             "attachments": [{"filename": "...", "type": "...", "content": "<base64>"}]}
 *
 * Отправитель — admin@kabacademy.com, категория SendGrid «moodle-report».
 * Ключ не печатается. Код возврата 0 — SendGrid принял письмо (2xx).
 */

const MOODLE_CONFIG = '/sites/edu.kabacademy.com/public/config.php';
const MAIL_FROM     = 'admin@kabacademy.com';
const CATEGORY      = 'moodle-report';

function fail( string $msg ): void {
	fwrite( STDERR, 'ОШИБКА: ' . $msg . "\n" );
	exit( 1 );
}

$mail = json_decode( (string) stream_get_contents( STDIN ), true );
if ( ! is_array( $mail ) ) {
	fail( 'на stdin не JSON' );
}
$to = $mail['to'] ?? [];
if ( ! $to || ! is_array( $to ) ) {
	fail( 'нет адресатов' );
}
foreach ( $to as $r ) {
	if ( ! filter_var( $r, FILTER_VALIDATE_EMAIL ) ) {
		fail( "плохой адрес: {$r}" );
	}
}
if ( '' === trim( (string) ( $mail['subject'] ?? '' ) ) ) {
	fail( 'нет темы' );
}

$src = file_get_contents( MOODLE_CONFIG );
if ( false === $src ) {
	fail( 'не читается ' . MOODLE_CONFIG );
}
$cfg = [];
foreach ( [ 'dbhost', 'dbname', 'dbuser', 'dbpass', 'prefix' ] as $key ) {
	if ( ! preg_match( "/\\\$CFG->{$key}\\s*=\\s*'((?:[^'\\\\]|\\\\.)*)'/", $src, $m ) ) {
		fail( "в config.php не найден {$key}" );
	}
	$cfg[ $key ] = stripcslashes( $m[1] );
}

mysqli_report( MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );
try {
	$db  = new mysqli( $cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass'], $cfg['dbname'] );
	$row = $db->query( "SELECT value FROM {$cfg['prefix']}config WHERE name = 'smtppass'" )->fetch_row();
	$db->close();
} catch ( Throwable $e ) {
	fail( 'БД: ' . $e->getMessage() );
}
$key = $row[0] ?? '';
if ( '' === $key ) {
	fail( 'smtppass не найден в mdl_config' );
}

$payload = [
	'personalizations' => [ [ 'to' => array_map( static fn( $r ) => [ 'email' => $r ], $to ) ] ],
	'from'             => [ 'email' => MAIL_FROM, 'name' => (string) ( $mail['from_name'] ?? 'edu.kabacademy.com' ) ],
	'subject'          => (string) $mail['subject'],
	'content'          => [ [ 'type' => 'text/html', 'value' => (string) ( $mail['html'] ?? '' ) ?: ' ' ] ],
	'categories'       => [ CATEGORY ],
];
foreach ( $mail['attachments'] ?? [] as $a ) {
	if ( empty( $a['content'] ) || empty( $a['filename'] ) ) {
		fail( 'вложение без content или filename' );
	}
	$payload['attachments'][] = [
		'content'     => $a['content'],
		'type'        => $a['type'] ?? 'application/octet-stream',
		'filename'    => $a['filename'],
		'disposition' => 'attachment',
	];
}

$ch = curl_init( 'https://api.sendgrid.com/v3/mail/send' );
curl_setopt_array( $ch, [
	CURLOPT_POST           => true,
	CURLOPT_POSTFIELDS     => json_encode( $payload, JSON_UNESCAPED_UNICODE ),
	CURLOPT_HTTPHEADER     => [ 'Authorization: Bearer ' . $key, 'Content-Type: application/json' ],
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_TIMEOUT        => 60,
] );
$resp = curl_exec( $ch );
$code = curl_getinfo( $ch, CURLINFO_RESPONSE_CODE );
curl_close( $ch );
if ( $code < 200 || $code >= 300 ) {
	fail( "SendGrid HTTP {$code}: " . substr( (string) $resp, 0, 300 ) );
}
echo 'SendGrid HTTP ' . $code . ': ' . implode( ', ', $to ) . "\n";
