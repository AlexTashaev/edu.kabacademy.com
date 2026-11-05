<?php
/**
 * Страница чата для МОБИЛЬНОГО ПРИЛОЖЕНИЯ Moodle (SPEC_moodle_app_chat, п.15).
 *
 * Нативное приложение не рендерит футер сайта → обычный инжект виджета не работает.
 * Пункт меню приложения (tool_mobile | custommenuitems) открывает эту страницу:
 *   Техподдержка | https://edu.kabacademy.com/local/kabchat/app.php | embedded | ru
 *
 * Идентификация: страница живёт ВНУТРИ Moodle → если webview приложения донёс сессию
 * (авто-логин приложения), HMAC считается на сервере и клиент идентифицирован — тот же
 * контакт, что на сайте. Без сессии — анонимный чат с pre-chat формой (бот подхватит
 * email из формы/текста). require_login НЕ зовём: страница полезна в обоих режимах.
 */

// Наш вывод — целиком свой (без темы Moodle); фрейм-заголовки снимаем: страница
// открывается в iframe приложения (метод embedded), SAMEORIGIN его блокирует.
require(__DIR__ . '/../../config.php');

@header_remove('X-Frame-Options');
@header('Content-Security-Policy: frame-ancestors *');
@header('Content-Type: text/html; charset=utf-8');

$base  = get_config('local_kabchat', 'base_url');
$token = get_config('local_kabchat', 'website_token');
$hmac  = get_config('local_kabchat', 'hmac_token');

if (empty($base) || empty($token)) {
    echo 'Чат не настроен.';
    exit;
}

// HMAC-идентификация — только живая сессия реального пользователя.
$identify = '';
if (isloggedin() && !isguestuser() && !empty($hmac) && !empty($USER->email)) {
    $email = $USER->email;
    $identify = 'window.$chatwoot.setUser(' . json_encode($email) . ', {'
        . 'email: ' . json_encode($email) . ','
        . 'name: ' . json_encode(fullname($USER)) . ','
        . 'identifier_hash: ' . json_encode(hash_hmac('sha256', $email, $hmac))
        . '});';
}

$base_js  = json_encode($base);
$token_js = json_encode($token);
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Поддержка</title>
<style>
  body { margin: 0; font-family: -apple-system, Segoe UI, Roboto, sans-serif; background: #f5f6f8; }
  #kab-fallback { display: flex; flex-direction: column; align-items: center; justify-content: center;
    min-height: 60vh; gap: 14px; color: #555; padding: 24px; text-align: center; }
  #kab-open { border: 0; background: #1f93ff; color: #fff; border-radius: 10px;
    padding: 12px 22px; font-size: 16px; cursor: pointer; display: none; }
</style>
</head>
<body>
<div id="kab-fallback">
  <div id="kab-status">Открываем чат поддержки…</div>
  <button id="kab-open">Открыть чат</button>
</div>
<script>
  window.chatwootSettings = { locale: 'ru', hideMessageBubble: true, showUnreadMessagesDialog: false };
  (function (d, t) {
    var BASE_URL = <?php echo $base_js; ?>;
    var g = d.createElement(t), s = d.getElementsByTagName(t)[0];
    g.src = BASE_URL + '/packs/js/sdk.js'; g.async = true;
    s.parentNode.insertBefore(g, s);
    g.onload = function () {
      window.chatwootSDK.run({ websiteToken: <?php echo $token_js; ?>, baseUrl: BASE_URL });
    };
    g.onerror = function () {
      d.getElementById('kab-status').textContent = 'Не удалось загрузить чат. Проверьте интернет и потяните страницу вниз, чтобы обновить.';
    };
  })(document, 'script');
  window.addEventListener('chatwoot:ready', function () {
    <?php echo $identify; ?>
    window.$chatwoot.toggle('open'); // сразу развёрнутый чат (на мобильном — во весь экран)
    var btn = document.getElementById('kab-open');
    btn.style.display = 'inline-block';
    document.getElementById('kab-status').textContent = 'Чат закрыт.';
    btn.onclick = function () { window.$chatwoot.toggle('open'); };
  });
</script>
</body>
</html>
