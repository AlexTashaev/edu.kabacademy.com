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
 * Пункт «Витрина» в администрировании.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig || has_capability('local/kabshop:manage', context_system::instance())) {
    $ADMIN->add('courses', new admin_externalpage(
        'local_kabshop_manage',
        get_string('manage', 'local_kabshop'),
        new moodle_url('/local/kabshop/manage.php'),
        'local/kabshop:manage'
    ));
    $ADMIN->add('courses', new admin_externalpage(
        'local_kabshop_orders',
        get_string('orders', 'local_kabshop'),
        new moodle_url('/local/kabshop/orders.php'),
        'local/kabshop:manage'
    ));
    $ADMIN->add('courses', new admin_externalpage(
        'local_kabshop_coupons',
        get_string('coupons', 'local_kabshop'),
        new moodle_url('/local/kabshop/coupons.php'),
        'local/kabshop:manage'
    ));
}

if ($hassiteconfig) {
    // Продавцы и контакты юридического блока витрины. Пока не сохранены — действуют значения
    // по умолчанию из \local_kabshop\legal::DEFAULTS.
    $page = new admin_settingpage('local_kabshop_legal', get_string('legalsettings', 'local_kabshop'));
    $defaults = \local_kabshop\legal::DEFAULTS;
    foreach (['main', 'ru'] as $market) {
        $page->add(new admin_setting_configtextarea(
            'local_kabshop/seller_' . $market,
            get_string('setting_seller_' . $market, 'local_kabshop'),
            get_string('setting_seller_desc', 'local_kabshop'),
            $defaults['seller_' . $market],
            PARAM_TEXT
        ));
        $page->add(new admin_setting_configtext(
            'local_kabshop/email_' . $market,
            get_string('setting_email_' . $market, 'local_kabshop'),
            '',
            $defaults['email_' . $market],
            PARAM_EMAIL
        ));
        $page->add(new admin_setting_configtext(
            'local_kabshop/phone_' . $market,
            get_string('setting_phone_' . $market, 'local_kabshop'),
            '',
            $defaults['phone_' . $market],
            PARAM_TEXT
        ));
    }
    $ADMIN->add('localplugins', $page);
}
