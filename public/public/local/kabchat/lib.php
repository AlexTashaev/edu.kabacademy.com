<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Вставляет виджет Chatwoot и идентификацию залогиненного пользователя в футер Moodle.
 *
 * Moodle при рендере футера вызывает функции с именем local_<plugin>_before_footer()
 * и выводит то, что они вернут. HMAC считается на сервере, секрет браузеру не виден.
 *
 * @return string HTML для вставки перед футером (или пустая строка, если не настроено)
 */
function local_kabchat_before_footer() {
    global $USER;

    $base  = get_config('local_kabchat', 'base_url');
    $token = get_config('local_kabchat', 'website_token');
    if (empty($base) || empty($token)) {
        return ''; // не настроено — плагин «спит»
    }

    // В chatwoot:ready: HMAC-идентификация (только залогиненный, не гость) + атрибуты «откуда пишет» (всегда).
    $ready = '';
    $hmac = get_config('local_kabchat', 'hmac_token');
    if (isloggedin() && !isguestuser() && !empty($hmac) && !empty($USER->email)) {
        $email = $USER->email;
        $hash  = hash_hmac('sha256', $email, $hmac);
        $name  = fullname($USER);
        $ready .=
            "window.\$chatwoot.setUser(" . json_encode($email) . ", {" .
            "  email: " . json_encode($email) . "," .
            "  name: " . json_encode($name) . "," .
            "  identifier_hash: " . json_encode($hash) .
            "});";
    }
    // «Источник + страница + след пути» — оператору видно, откуда пишет, где сейчас
    // и как перемещался. Трекинг клиентский, ошибки заглушены — страницу не ломает.
    $track = <<<'JS'
(function(){
  var KAB_HEARTBEAT_MS=60000;
  function kabTrack(){
    if(!(window.$chatwoot&&window.$chatwoot.setCustomAttributes))return;
    try{
      var now=new Date();
      var hm=('0'+now.getHours()).slice(-2)+':'+('0'+now.getMinutes()).slice(-2);
      var arr=[];try{arr=JSON.parse(sessionStorage.getItem('kab_journey')||'[]')}catch(e){}
      var cur={t:hm,p:(document.title||'').slice(0,60),u:location.pathname};
      var last=arr[arr.length-1];
      if(!last||last.u!==cur.u){arr.push(cur);if(arr.length>12)arr=arr.slice(-12);try{sessionStorage.setItem('kab_journey',JSON.stringify(arr))}catch(e){}}
      var journey=arr.map(function(x){return x.t+' '+x.p}).join('  →  ');
      window.$chatwoot.setCustomAttributes({kab_source:KAB_SRC,kab_page:document.title,kab_url:location.href,kab_last_seen:now.toISOString(),kab_journey:journey});
    }catch(e){}
  }
  kabTrack();
  try{
    var _p=history.pushState;history.pushState=function(){var r=_p.apply(this,arguments);kabTrack();return r};
    var _r=history.replaceState;history.replaceState=function(){var r=_r.apply(this,arguments);kabTrack();return r};
    window.addEventListener('popstate',kabTrack);
  }catch(e){}
  // Chatwoot пишет эти атрибуты в ДИАЛОГ, а не в контакт: пока посетитель не отправил
  // первое сообщение, диалога нет и setCustomAttributes молча теряется. Отдельного
  // события «диалог создан» SDK не отдаёт, поэтому переотправляем: на событиях виджета,
  // при возврате во вкладку и фоновым heartbeat'ом — так атрибуты долетают, даже если
  // посетитель написал и остался на той же странице. Повторы безопасны: вызов
  // идемпотентен, а journey не задваивается (дедуп по pathname внутри kabTrack).
  try{
    ['chatwoot:opened','chatwoot:closed','chatwoot:on-message'].forEach(function(ev){
      window.addEventListener(ev,kabTrack);
    });
    window.addEventListener('focus',kabTrack);
    document.addEventListener('visibilitychange',function(){
      if(document.visibilityState!=='hidden')kabTrack();
    });
    // heartbeat держит kab_last_seen живым, пока посетитель читает одну страницу
    setInterval(function(){
      if(document.visibilityState!=='hidden')kabTrack();
    },KAB_HEARTBEAT_MS);
  }catch(e){}
})();
JS;
    $ready .= 'var KAB_SRC=' . json_encode('Moodle') . ';' . $track;
    $identify = "window.addEventListener('chatwoot:ready', function () { " . $ready . " });";

    $base_js  = json_encode($base);
    $token_js = json_encode($token);

    return "
<script>
  (function (d, t) {
    var BASE_URL = {$base_js};
    var g = d.createElement(t), s = d.getElementsByTagName(t)[0];
    g.src = BASE_URL + '/packs/js/sdk.js'; g.async = true;
    s.parentNode.insertBefore(g, s);
    g.onload = function () {
      window.chatwootSDK.run({ websiteToken: {$token_js}, baseUrl: BASE_URL });
    };
  })(document, 'script');
  {$identify}
</script>";
}
