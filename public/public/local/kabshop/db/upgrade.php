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
 * Обновления схемы local_kabshop.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Обновление схемы.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_kabshop_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026101901) {
        // Сумма на выбор: флаг и готовые суммы у товара, таблица выбранных сумм.
        $table = new xmldb_table('local_kabshop_product');
        $field = new xmldb_field('customamount', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'pricelabel');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('presets', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'customamount');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('local_kabshop_intent');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('amount', XMLDB_TYPE_NUMBER, '12, 2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('currency', XMLDB_TYPE_CHAR, '3', null, XMLDB_NOTNULL, null, 'USD');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('productid', XMLDB_INDEX_NOTUNIQUE, ['productid']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026101901, 'local', 'kabshop');
    }

    if ($oldversion < 2026102200) {
        // Автосписание: регулярность у товара и таблица подписок.
        $table = new xmldb_table('local_kabshop_product');
        $field = new xmldb_field('recurmonths', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'presets');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('local_kabshop_sub');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('gateway', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('accountid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('firstpaymentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lastpaymentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('amount', XMLDB_TYPE_NUMBER, '12, 2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('currency', XMLDB_TYPE_CHAR, '3', null, XMLDB_NOTNULL, null, null);
        $table->add_field('months', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'active');
        $table->add_field('nextcharge', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('failcount', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('lastattempt', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('lasterror', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('status_next', XMLDB_INDEX_NOTUNIQUE, ['status', 'nextcharge']);
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026102200, 'local', 'kabshop');
    }

    if ($oldversion < 2026102300) {
        // Ручная выдача / отзыв и купоны.
        $table = new xmldb_table('local_kabshop_purchase');
        $fields = [
            new xmldb_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'ok', 'timecreated'),
            new xmldb_field('timerevoked', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'status'),
            new xmldb_field('grantedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timerevoked'),
            new xmldb_field('note', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'grantedby'),
            new xmldb_field('couponid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'note'),
            new xmldb_field('amountoff', XMLDB_TYPE_NUMBER, '12, 2', null, XMLDB_NOTNULL, null, '0', 'couponid'),
        ];
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        $table = new xmldb_table('local_kabshop_intent');
        $fields = [
            new xmldb_field('couponid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'currency'),
            new xmldb_field('amountoff', XMLDB_TYPE_NUMBER, '12, 2', null, XMLDB_NOTNULL, null, '0', 'couponid'),
        ];
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        $table = new xmldb_table('local_kabshop_coupon');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('code', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null);
        $table->add_field('kind', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'percent');
        $table->add_field('value', XMLDB_TYPE_NUMBER, '12, 2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('currency', XMLDB_TYPE_CHAR, '3', null, null, null, null);
        $table->add_field('productids', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('validfrom', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('validto', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('maxuses', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('peruser', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('uses', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('note', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('code', XMLDB_INDEX_UNIQUE, ['code']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_kabshop_coupon_use');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('couponid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('purchaseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('amountoff', XMLDB_TYPE_NUMBER, '12, 2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('currency', XMLDB_TYPE_CHAR, '3', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('coupon_user', XMLDB_INDEX_NOTUNIQUE, ['couponid', 'userid']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026102300, 'local', 'kabshop');
    }

    if ($oldversion < 2026111700) {
        $table = new xmldb_table('local_kabshop_product');
        $field = new xmldb_field('requirecourses', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'courseid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2026111700, 'local', 'kabshop');
    }

    if ($oldversion < 2026111701) {
        // SKU товара для кассы и данные жертвователя для квитанции (статья 46).
        $taxtype = fn(?string $after) => new xmldb_field('taxtype', XMLDB_TYPE_CHAR, '1', null, null, null, null, $after);
        $taxid = fn() => new xmldb_field('taxid', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'taxtype');
        $fields = [
            'local_kabshop_product' => [new xmldb_field('sku', XMLDB_TYPE_CHAR, '64', null, null, null, null, 'accountid')],
            'local_kabshop_purchase' => [$taxtype('amountoff'), $taxid()],
            'local_kabshop_intent' => [new xmldb_field('taxcountry', XMLDB_TYPE_CHAR, '2', null, null, null, null, 'amountoff'),
                $taxtype('taxcountry'), $taxid()],
            'local_kabshop_sub' => [$taxtype('lasterror'), $taxid()],
        ];
        foreach ($fields as $tablename => $list) {
            $table = new xmldb_table($tablename);
            foreach ($list as $field) {
                if (!$dbman->field_exists($table, $field)) {
                    $dbman->add_field($table, $field);
                }
            }
        }
        upgrade_plugin_savepoint(true, 2026111701, 'local', 'kabshop');
    }

    if ($oldversion < 2026111800) {
        // Купон для списка адресов e-mail и рассылка кода на них.
        $table = new xmldb_table('local_kabshop_coupon');
        $field = new xmldb_field('emailonly', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'enabled');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $table = new xmldb_table('local_kabshop_coupon_email');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('couponid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('email', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timesent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('coupon_email', XMLDB_INDEX_UNIQUE, ['couponid', 'email']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2026111800, 'local', 'kabshop');
    }

    if ($oldversion < 2026111901) {
        // Раздел в стиле kabacademy.com: иконка, вертикальная подпись, свёрнут при открытии каталога.
        $table = new xmldb_table('local_kabshop_section');
        foreach ([
            new xmldb_field('icon', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'visible'),
            new xmldb_field('sidelabel', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'icon'),
            new xmldb_field('collapsed', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'sidelabel'),
        ] as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_plugin_savepoint(true, 2026111901, 'local', 'kabshop');
    }

    if ($oldversion < 2026111904) {
        // Вторая цена товара в другой валюте (рубли через Robokassa) вместо товара-пары.
        $table = new xmldb_table('local_kabshop_product');
        foreach ([
            new xmldb_field('altcurrency', XMLDB_TYPE_CHAR, '3', null, null, null, null, 'accountid'),
            new xmldb_field('altprice', XMLDB_TYPE_NUMBER, '12, 2', null, XMLDB_NOTNULL, null, '0', 'altcurrency'),
            new xmldb_field('altpresets', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'altprice'),
            new xmldb_field('altaccountid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'altpresets'),
        ] as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_plugin_savepoint(true, 2026111904, 'local', 'kabshop');
    }

    return true;
}
