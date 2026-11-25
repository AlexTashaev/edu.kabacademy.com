<?php
/**
 * Кнопка «Чат-бот в Telegram» из гостиной (вводный раздел курса).
 *
 * Зачем: на странице «Спасибо» кнопка бота уводит на SendPulse deep-link с токеном, и
 * данные человека (имя, почта, телефон, страна, город, заказ, курс, результат опроса)
 * доезжают в переменные его контакта в боте. В гостиной такой кнопки не было — стояла
 * голая ссылка t.me, и контакт приходил в SendPulse пустым.
 *
 * Что делает: узнаёт человека по сессии Moodle (в гостиной он залогинен всегда) и уводит
 * на редиректор WP `kab_te_bot_go`, подписав переход. WP по подписи находит пользователя,
 * берёт его токен и ведёт дальше в бота — так же, как «Спасибо». В адрес кладём только
 * числовой id пользователя Moodle: почта и телефон наружу не идут, а подпись живёт
 * 10 минут (KAB_Telegram_Enrollment::BRIDGE_TTL).
 *
 * Ключи: приватный Ed25519 — `kab-bridge-secret.php` рядом с config.php (вне веб-корня,
 * в git не хранится), публичный — в настройках плагина на kabacademy.com (опция
 * kab_te_bridge_pubkey). Нет ключа или человек не залогинен — уходим на тот же редиректор
 * без подписи: он попробует WP-сессию, а в крайнем случае просто откроет бота.
 *
 * `?to=channel` — та же схема для ссылки «Канал потока в Telegram»: WP засчитывает переход
 * человеку (kab_te_channel_go) и уводит в канал из настроек плагина. Без параметра — бот.
 *
 * KAB patch (25.11.2026, local_kabtelegram): WordPress выключается. Как только в Moodle стоит
 * local_kabtelegram и в нём задан бот, весь переход идёт внутри Moodle — `go.php` узнаёт
 * человека по сессии, сам выдаёт токен и сам принимает вебхук SendPulse. До того — прежний
 * путь в WP ниже, без изменений.
 */

require_once(__DIR__ . '/../../config.php');

$targets = [
    'bot'     => 'kab_te_bot_go',
    'channel' => 'kab_te_channel_go',
];
$to = optional_param('to', 'bot', PARAM_ALPHA);
if (!isset($targets[$to])) {
    $to = 'bot';
}

if (core_component::get_component_directory('local_kabtelegram') !== null
        && trim((string) get_config('local_kabtelegram', 'botusername')) !== '') {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Location: ' . (new moodle_url('/local/kabtelegram/go.php', ['to' => $to, 'src' => 'tgbot']))->out(false), true, 302);
    exit;
}

$endpoint = 'https://kabacademy.com/wp-admin/admin-ajax.php?action=' . $targets[$to];

$keyfile = __DIR__ . '/../../kab-bridge-secret.php';
$key = is_readable($keyfile) ? trim((string) require($keyfile)) : '';

$dest = $endpoint;
if (strlen($key) === 128 && ctype_xdigit($key) && isloggedin() && !isguestuser()
        && function_exists('sodium_crypto_sign_detached')) {
    $mu = (int) $USER->id;
    $ts = time();
    $sig = sodium_crypto_sign_detached($mu . '|' . $ts, hex2bin($key));
    $dest .= '&mu=' . $mu . '&ts=' . $ts . '&sig=' . bin2hex($sig);
}

// Подписанная ссылка одноразовая по смыслу — ни прокси, ни браузер её кэшировать не должны.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Location: ' . $dest, true, 302);
exit;
