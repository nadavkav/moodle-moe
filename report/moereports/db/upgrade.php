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
    return true;
}