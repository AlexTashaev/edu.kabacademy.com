<?php
// ⚠️ SCAFFOLD. Регистрация WS-функций local_kabchat.
// После апгрейда плагина (Site admin → Notifications) функцию нужно добавить
// в существующий external service, к которому привязан токен Edwiser (id=5,
// EdwiserBridgeServices) — тогда тот же токен сможет её вызывать.
defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_kabchat_get_user_course_roles' => [
        'classname'    => 'local_kabchat\external\get_user_course_roles',
        'methodname'   => 'execute',
        'description'  => 'Read-only: роли пользователя по курсам (Person Card чата поддержки).',
        'type'         => 'read',
        'ajax'         => false,
        'capabilities' => 'moodle/role:review',
    ],
];
