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

function xmldb_report_moereports_upgrade($oldversion) {

    global $CFG, $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.
    if ($oldversion < 2017011803) {

        $table = new xmldb_table('moereports_reports_classes');
        if (!$dbman->table_exists($table)) {
            $dbman->install_one_table_from_xmldb_file($CFG->dirroot.'/report/moereports/db/install.xml', 'moereports_reports_classes');
        }

        $table = new xmldb_table('moereports_reports');
        if (!$dbman->table_exists($table)) {
            $dbman->install_one_table_from_xmldb_file($CFG->dirroot.'/report/moereports/db/install.xml', 'moereports_reports');
        }

        upgrade_plugin_savepoint(true, 2017011803, 'report', 'moereports');
    }
    if ($oldversion < 2017020800) {
        $table = new xmldb_table('moereports_reports');
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '32', true, true, false, 'none');
        $dbman->change_field_precision($table, $field);
        $field = new xmldb_field('region', XMLDB_TYPE_CHAR, '32', true, true, false, 'none');
        $dbman->change_field_precision($table, $field);
        upgrade_plugin_savepoint(true, 2017020800, 'report', 'moereports');
    }

    if ($oldversion < 2017022202) {
        $table = new xmldb_table('moereports_reports');
        $field = new xmldb_field('city');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field, $continue = true, $feedback = true);
        }
        upgrade_plugin_savepoint(true, 2017022203, 'report', 'moereports');

    }
    if ($oldversion < 2017052201) {

        $table = new xmldb_table('moereports_courseschool');
        if (!$dbman->table_exists($table)) {
            $dbman->install_one_table_from_xmldb_file($CFG->dirroot.'/report/moereports/db/install.xml', 'moereports_courseschool');
        }

        $table = new xmldb_table('moereports_acactivityschool');
        if (!$dbman->table_exists($table)) {
            $dbman->install_one_table_from_xmldb_file($CFG->dirroot.'/report/moereports/db/install.xml', 'moereports_acactivityschool');
        }

        $table = new xmldb_table('moereports_courseregin');
        if (!$dbman->table_exists($table)) {
            $dbman->install_one_table_from_xmldb_file($CFG->dirroot.'/report/moereports/db/install.xml', 'moereports_courseregin');
        }

        $table = new xmldb_table('moereports_activityregin');
        if (!$dbman->table_exists($table)) {
            $dbman->install_one_table_from_xmldb_file($CFG->dirroot.'/report/moereports/db/install.xml', 'moereports_activityregin');
        }
        upgrade_plugin_savepoint(true, 2017052201, 'report', 'moereports');
    }
    return true;
}