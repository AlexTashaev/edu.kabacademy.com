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

    // Блоки каталога, как на главной kabacademy.com. Пока не сохранены — \local_kabshop\blocks::DEFAULTS.
    $page = new admin_settingpage('local_kabshop_blocks', get_string('blockssettings', 'local_kabshop'));
    $bd = \local_kabshop\blocks::DEFAULTS;
    $page->add(new admin_setting_heading('local_kabshop/about_heading', get_string('setting_about', 'local_kabshop'), ''));
    $page->add(new admin_setting_configtext('local_kabshop/about_title', get_string('setting_about_title', 'local_kabshop'),
        '', $bd['about_title'], PARAM_TEXT));
    $page->add(new admin_setting_confightmleditor('local_kabshop/about_text', get_string('setting_about_text', 'local_kabshop'),
        get_string('setting_about_text_desc', 'local_kabshop'), $bd['about_text']));
    $page->add(new admin_setting_configtext('local_kabshop/about_url', get_string('setting_about_url', 'local_kabshop'),
        get_string('setting_about_url_desc', 'local_kabshop'), $bd['about_url'], PARAM_URL));
    $page->add(new admin_setting_heading('local_kabshop/teachers_heading', get_string('setting_teachers', 'local_kabshop'), ''));
    $page->add(new admin_setting_configtext('local_kabshop/teachers_title', get_string('setting_teachers_title', 'local_kabshop'),
        '', $bd['teachers_title'], PARAM_TEXT));
    $page->add(new admin_setting_configtextarea('local_kabshop/teachers', get_string('setting_teachers_list', 'local_kabshop'),
        get_string('setting_teachers_list_desc', 'local_kabshop'), $bd['teachers'], PARAM_RAW, 80, 20));
    $page->add(new admin_setting_configstoredfile('local_kabshop/teacherphotos',
        get_string('setting_teacherphotos', 'local_kabshop'), get_string('setting_teacherphotos_desc', 'local_kabshop'),
        'teacherphotos', 0, ['maxfiles' => 30, 'accepted_types' => ['web_image']]));
    $ADMIN->add('localplugins', $page);

    // Окно «Безвозмездная помощь» при бесплатной записи — как попап корзины WordPress.
    $page = new admin_settingpage('local_kabshop_freedonation', get_string('freedonationsettings', 'local_kabshop'));
    $choices = [0 => get_string('freedonation_off', 'local_kabshop')];
    if ($ADMIN->fulltree) {
        try {
            $recs = $DB->get_records_select('local_kabshop_product',
                "kind = 'donation' AND customamount = 1 AND accountid > 0", null, 'sortorder, id',
                'id, name, currency, visible');
        } catch (dml_exception $e) {
            $recs = []; // Таблиц ещё нет — плагин ставится.
        }
        foreach ($recs as $r) {
            $choices[$r->id] = format_string($r->name) . ' (' . $r->currency . ')'
                . ($r->visible ? '' : ' — ' . get_string('hidden', 'local_kabshop'));
        }
    }
    $page->add(new admin_setting_configselect(
        'local_kabshop/freedonation_product',
        get_string('setting_freedonation_product', 'local_kabshop'),
        get_string('setting_freedonation_product_desc', 'local_kabshop'),
        0,
        $choices
    ));
    $page->add(new admin_setting_confightmleditor(
        'local_kabshop/freedonation_text',
        get_string('setting_freedonation_text', 'local_kabshop'),
        get_string('setting_freedonation_text_desc', 'local_kabshop'),
        get_string('freedonation_text_default', 'local_kabshop')
    ));
    $page->add(new admin_setting_configtext(
        'local_kabshop/freedonation_presets',
        get_string('setting_freedonation_presets', 'local_kabshop'),
        get_string('setting_freedonation_presets_desc', 'local_kabshop'),
        \local_kabshop\catalog::FREEDONATION_DEFAULTS['presets'],
        PARAM_TEXT
    ));
    $page->add(new admin_setting_configtext(
        'local_kabshop/freedonation_default',
        get_string('setting_freedonation_default', 'local_kabshop'),
        get_string('setting_freedonation_default_desc', 'local_kabshop'),
        \local_kabshop\catalog::FREEDONATION_DEFAULTS['default'],
        PARAM_TEXT
    ));
    $ADMIN->add('localplugins', $page);
}
