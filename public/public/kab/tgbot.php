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
 */

require_once(__DIR__ . '/../../config.php');

$endpoint = 'https://kabacademy.com/wp-admin/admin-ajax.php?action=kab_te_bot_go';

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
