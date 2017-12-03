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
 * Upgrade code for remote_backup_provider.
 *
 * @package    local_remote_backup_provider
 * @copyright  2015 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the local_usertours plugins.
 *
 * @param int $oldversion The old version of the local_usertours module
 * @return bool
 */
function xmldb_block_import_remote_course_upgrade($oldversion) {
    global $DB,$CFG;

    $dbman = $DB->get_manager();

    if ($oldversion < 2017073100) {
        // Define field sortorder to be added to usertours_tours.
        $table = new xmldb_table('import_remote_course_list');

        // Conditionally launch add field sortorder.
        if (!$dbman->table_exists($table)) {
            $dbman->install_one_table_from_xmldb_file($CFG->dirroot . '/blocks/import_remote_course/db/install.xml', 'import_remote_course_list');
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2017073100, 'block', 'import_remote_course');
    }

    if ($oldversion < 2017112700) {
    	$dbman = $DB->get_manager();

    	$table = new xmldb_table('import_remote_course_templat');
    	if (!$dbman->table_exists($table)) {
    		$dbman->install_one_table_from_xmldb_file($CFG->dirroot . '/blocks/import_remote_course/db/install.xml', 'import_remote_course_templat');
    	}
    	upgrade_plugin_savepoint(true, 2017112700, 'block', 'import_remote_course');
    }

    if ($oldversion < 2017112900) {
    	$dbman = $DB->get_manager();

    	$table = new xmldb_table('import_remote_course_list');
    	$field = new xmldb_field('change_log_link', XMLDB_TYPE_TEXT, '255', null, null, null, null, null);
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    	upgrade_plugin_savepoint(true, 2017112900, 'block', 'import_remote_course');

    }

    if ($oldversion < 2017112902) {
    	$dbman = $DB->get_manager();

    	$table = new xmldb_table('import_remote_course_actdata');
    	if (!$dbman->table_exists($table)) {
    		$dbman->install_one_table_from_xmldb_file($CFG->dirroot . '/blocks/import_remote_course/db/install.xml', 'import_remote_course_actdata');
    	}
    	upgrade_plugin_savepoint(true, 2017112902, 'block', 'import_remote_course');

    }

    if ($oldversion < 2017112903) {
    	$dbman = $DB->get_manager();

    	$table = new xmldb_table('import_remote_course_templat');
    	if (!$dbman->table_exists($table)) {
    		$dbman->install_one_table_from_xmldb_file($CFG->dirroot . '/blocks/import_remote_course/db/install.xml', 'import_remote_course_templat');
    	}
    	upgrade_plugin_savepoint(true, 2017112903, 'block', 'import_remote_course');

    }

    if ($oldversion < 2017120201) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('import_remote_course_actdata');
        $field = new xmldb_field('tamplate_id', XMLDB_TYPE_INTEGER, '10', null, true, false, 1, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'courseid');
        }
        $field = new xmldb_field('mod', XMLDB_TYPE_TEXT, '255', null, true, false, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'module');
        }
        $field = new xmldb_field('time_added', XMLDB_TYPE_INTEGER, '10', null, true, false, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'timecreated');
        }
        $field = new xmldb_field('type', XMLDB_TYPE_TEXT, '6', null, true, false, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('linktoremoteact', XMLDB_TYPE_TEXT, '255', null, false, false, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, true, false, time(), null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, true, false, 2, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $table = new xmldb_table('import_remote_course_notific');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $table = new xmldb_table('import_remote_course_templat');
        $field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, true, false, 2, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, true, false, 2, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('time_added', XMLDB_TYPE_INTEGER, '10', null, true, false, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'timecreated');
        }
        upgrade_block_savepoint(true, 2017120201, 'import_remote_course');
    }
    return true;
}
