<?php
/**
 * Кнопка «Чат-бот в Telegram» из гостиной (вводный раздел курса).
 *
 * Зачем: на странице «Спасибо» кнопка бота уводит на SendPulse deep-link с токеном,
 * и данные человека (имя, почта, телефон, страна, город, заказ, курс, результат опроса)
 * доезжают в переменные его контакта в боте. В гостиной такой кнопки не было — стояла
 * голая ссылка t.me, и контакт приходил в SendPulse пустым.
 *
 * Что делает: узнаёт человека по сессии Moodle (в гостиной он залогинен всегда) и уводит
 * на редиректор WP `kab_te_bot_go`, подписав переход HMAC-ом на общем секрете. WP по
 * подписи находит пользователя, берёт его токен и ведёт дальше в бота — так же, как
 * «Спасибо». В адрес кладём только числовой id пользователя Moodle: почта и телефон
 * наружу не идут, а подпись живёт 10 минут (см. KAB_Telegram_Enrollment::BRIDGE_TTL).
 *
 * Секрет: `kab-bridge-secret.php` рядом с config.php — вне веб-корня, в git не хранится.
 * Тот же ключ лежит в настройках плагина (опция kab_te_bridge_secret) на kabacademy.com.
 * Нет секрета или человек не залогинен — уходим на тот же редиректор без подписи: он
 * попробует WP-сессию, а в крайнем случае просто откроет бота.
 */

require_once(__DIR__ . '/../../config.php');

$endpoint = 'https://kabacademy.com/wp-admin/admin-ajax.php?action=kab_te_bot_go';

$secretfile = __DIR__ . '/../../kab-bridge-secret.php';
$secret = is_readable($secretfile) ? trim((string) require($secretfile)) : '';

$dest = $endpoint;
if ($secret !== '' && isloggedin() && !isguestuser()) {
    $mu = (int) $USER->id;
    $ts = time();
    $dest .= '&mu=' . $mu . '&ts=' . $ts . '&sig=' . hash_hmac('sha256', $mu . '|' . $ts, $secret);
}

// Подписанная ссылка одноразовая по смыслу — ни прокси, ни браузер её кэшировать не должны.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Location: ' . $dest, true, 302);
exit;
