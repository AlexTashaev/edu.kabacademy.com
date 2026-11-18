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
 * Upgrade steps.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade the plugin.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_kabsignup_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026110900) {
        $table = new xmldb_table('local_kabsignup_log');
        $field = new xmldb_field('flags', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '', 'status');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2026110900, 'local', 'kabsignup');
    }

    if ($oldversion < 2026111800) {
        // 1.3.0: ad and quiz parameters, persona, event id, product in the log.
        $table = new xmldb_table('local_kabsignup_log');
        $fields = [
            new xmldb_field('attribution', XMLDB_TYPE_TEXT, null, null, null, null, null, 'ip'),
            new xmldb_field('quizscore', XMLDB_TYPE_INTEGER, '5', null, null, null, null, 'attribution'),
            new xmldb_field('persona', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, '', 'quizscore'),
            new xmldb_field('eventid', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, '', 'persona'),
            new xmldb_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'eventid'),
        ];
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        $index = new xmldb_index('campaign', XMLDB_INDEX_NOTUNIQUE, ['campaign']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Campaigns move from the "slug|URL" setting to a table of their own.
        $table = new xmldb_table('local_kabsignup_campaign');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('slug', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, '');
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('visible', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('capi', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('thankyouurl', XMLDB_TYPE_CHAR, '500', null, XMLDB_NOTNULL, null, '');
        $table->add_field('thankyoufallback', XMLDB_TYPE_CHAR, '500', null, XMLDB_NOTNULL, null, '');
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        $table->add_field('intro', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('cardtitle', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        $table->add_field('cardnote', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        $table->add_field('cardimage', XMLDB_TYPE_CHAR, '500', null, XMLDB_NOTNULL, null, '');
        $table->add_field('pricelabel', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, '');
        $table->add_field('buttonlabel', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '');
        $table->add_field('belowbutton', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        $table->add_field('theme', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('slug', XMLDB_INDEX_UNIQUE, ['slug']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $old = (string)get_config('local_kabsignup', 'campaigns');
        foreach (\local_kabsignup\manager::parse_campaigns($old) as $slug => $url) {
            if (!$DB->record_exists('local_kabsignup_campaign', ['slug' => $slug])) {
                $DB->insert_record('local_kabsignup_campaign', (object)[
                    'slug' => $slug,
                    'name' => $slug,
                    'thankyouurl' => \core_text::substr($url, 0, 500),
                    'timecreated' => time(),
                    'timemodified' => time(),
                ]);
            }
        }
        unset_config('campaigns', 'local_kabsignup');

        upgrade_plugin_savepoint(true, 2026111800, 'local', 'kabsignup');
    }

    return true;
}
