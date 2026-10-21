<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Auth plugin "Email link login".
 *
 * The plugin class exists for one thing: the button on the login page that
 * leads to auth/kabmagic/request.php. It never authenticates anybody by
 * password and is never assigned to users as their auth method; the actual
 * sign-in happens in auth/kabmagic/login.php through complete_user_login().
 *
 * @package    auth_kabmagic
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');

/**
 * Auth plugin class.
 */
class auth_plugin_kabmagic extends auth_plugin_base {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'kabmagic';
        $this->config = get_config('auth_kabmagic');
    }

    /**
     * Password login is never handled here.
     *
     * @param string $username
     * @param string $password
     * @return bool always false
     */
    public function user_login($username, $password) {
        return false;
    }

    /**
     * Not an internal (password-storing) method.
     *
     * @return bool
     */
    public function is_internal() {
        return false;
    }

    /**
     * Passwords stay where they are: this plugin owns no users.
     *
     * @return bool
     */
    public function prevent_local_passwords() {
        return false;
    }

    /**
     * No password changes through this plugin.
     *
     * @return bool
     */
    public function can_change_password() {
        return false;
    }

    /**
     * No password resets through this plugin.
     *
     * @return bool
     */
    public function can_reset_password() {
        return false;
    }

    /**
     * No self-registration.
     *
     * @return bool
     */
    public function can_signup() {
        return false;
    }

    /**
     * No account confirmation.
     *
     * @return bool
     */
    public function can_confirm() {
        return false;
    }

    /**
     * Admins cannot pick this plugin as somebody's auth method.
     *
     * @return bool
     */
    public function can_be_manually_set() {
        return false;
    }

    /**
     * Nothing to sync.
     *
     * @return bool
     */
    public function is_synchronised_with_external() {
        return false;
    }

    /**
     * Works out of the box.
     *
     * @return bool
     */
    public function is_configured() {
        return true;
    }

    /**
     * The button on the login page.
     *
     * @param string $wantsurl where to go after the login
     * @return array
     */
    public function loginpage_idp_list($wantsurl) {
        $params = [];
        if (!empty($wantsurl)) {
            $params['wantsurl'] = $wantsurl;
        }
        return [[
            'url'  => new moodle_url('/auth/kabmagic/request.php', $params),
            'name' => get_string('loginbutton', 'auth_kabmagic'),
            'icon' => new pix_icon('icon', '', 'auth_kabmagic'),
        ]];
    }
}
