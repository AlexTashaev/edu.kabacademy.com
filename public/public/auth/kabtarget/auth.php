<?php
/**
 * KAB: сохраняем адрес назначения при уходе на внешнюю форму входа.
 *
 * Задача. У сайта включён alternateloginurl — Moodle отправляет гостя на форму
 * входа kabacademy.com. При этом адрес, куда человек шёл (например,
 * /course/section.php?id=1763), никуда не передаётся: Moodle держит его только
 * в своей сессии ($SESSION->wantsurl), а после входа через SSO Edwiser сессия
 * пересоздаётся и wantsurl теряется. В итоге человек, пришедший по ссылке из
 * письма, после входа попадает в «Мои курсы» вместо нужного места.
 *
 * Решение. loginpage_hook() вызывается в login/index.php раньше редиректа на
 * alternateloginurl (строка ~92 против ~328) и уже видит $SESSION->wantsurl —
 * здесь мы и дописываем адрес назначения к URL формы входа параметром
 * kab_target. Плагин magic-link на стороне WordPress кладёт его в письмо и
 * после входа доводит человека до этого адреса через SSO.
 *
 * Плагин НЕ аутентифицирует пользователей: user_login() всегда возвращает false.
 *
 * @package    auth_kabtarget
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');

class auth_plugin_kabtarget extends auth_plugin_base {

    public function __construct() {
        $this->authtype = 'kabtarget';
    }

    /**
     * Аутентификацией не занимаемся.
     */
    public function user_login($username, $password) {
        return false;
    }

    /**
     * Перед редиректом на внешнюю форму входа добавляем адрес назначения.
     */
    public function loginpage_hook() {
        global $CFG, $SESSION;

        if (empty($CFG->alternateloginurl) || empty($SESSION->wantsurl)) {
            return;
        }

        // ?loginredirect=0 — штатный обход внешней формы (нужен админам).
        // Ядро запоминает флаг в $SESSION->loginredirect (login/index.php ~325):
        // сама форма постит на /login/index.php уже без параметра, и без этой
        // проверки хук уводил бы POST с логином и паролем на внешнюю форму
        // раньше, чем ядро успеет проверить пароль.
        if (!optional_param('loginredirect', 1, PARAM_BOOL)
                || (isset($SESSION->loginredirect) && !$SESSION->loginredirect)) {
            return;
        }

        // Попытка входа по паролю через локальную форму — отдаём её ядру.
        if (!empty($_POST['username']) && isset($_POST['password'])) {
            return;
        }

        $target = (string) $SESSION->wantsurl;

        // Не уводим обратно на саму форму входа и не выпускаем за пределы Moodle.
        if (strpos($target, $CFG->alternateloginurl) === 0
            || strpos($target, $CFG->wwwroot) !== 0) {
            return;
        }

        $url = new moodle_url($CFG->alternateloginurl, array('kab_target' => $target));
        redirect($url->out(false));
    }

    public function prevent_local_passwords() {
        return true;
    }

    public function is_internal() {
        return false;
    }

    public function can_change_password() {
        return false;
    }

    public function can_reset_password() {
        return false;
    }
}
