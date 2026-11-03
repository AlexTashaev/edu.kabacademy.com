<?php
// Админ-настройки плагина: Site administration → Plugins → Local plugins → KAB Support Chat.
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_kabchat', get_string('pluginname', 'local_kabchat'));
    $ADMIN->add('localplugins', $settings);

    // Адрес Chatwoot (напр. https://chat.edu.kabacademy.com)
    $settings->add(new admin_setting_configtext(
        'local_kabchat/base_url',
        'Chatwoot base URL',
        'Адрес развёрнутого Chatwoot, напр. https://chat.edu.kabacademy.com',
        '',
        PARAM_URL
    ));

    // Website token из инбокса Chatwoot
    $settings->add(new admin_setting_configtext(
        'local_kabchat/website_token',
        'Website token',
        'Из настроек website-инбокса Chatwoot (тот же инбокс, что на WP — один контакт на оба сайта)',
        '',
        PARAM_RAW_TRIMMED
    ));

    // HMAC-секрет (Identity Validation) — тот же, что в wp-config WP
    $settings->add(new admin_setting_configpasswordunmask(
        'local_kabchat/hmac_token',
        'HMAC secret (Identity Validation)',
        'Секрет подписи идентификатора из Chatwoot (Inbox → Configuration → Identity Validation). Тот же, что у WP-плагина.',
        ''
    ));
}
