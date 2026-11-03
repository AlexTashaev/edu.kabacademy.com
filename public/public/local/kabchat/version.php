<?php
// ⚠️ SCAFFOLD. Moodle local-плагин: виджет Chatwoot + HMAC-идентификация.
// Куда класть в продакшене (на web-18): /sites/edu.kabacademy.com/public/public/local/kabchat/
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_kabchat';
$plugin->version   = 2026062100;      // YYYYMMDDXX — поднять при изменениях (добавлена WS local_kabchat_get_user_course_roles)
$plugin->requires  = 2022112800;      // Moodle 4.1+
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = '0.1-scaffold';
