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
function xmldb_local_remote_backup_provider_upgrade($oldversion) {
    global $DB,$CFG;

    $dbman = $DB->get_manager();

    if ($oldversion < 2017073001) {
        // Define field sortorder to be added to usertours_tours.
        $table = new xmldb_table('remote_backup_provider_subsc');

        // Conditionally launch add field sortorder.
        if (!$dbman->table_exists($table)) {
            $dbman->install_one_table_from_xmldb_file($CFG->dirroot . '/local/remote_backup_provider/db/install.xml', 'remote_backup_provider_subsc');
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2017073001, 'local', 'remote_backup_provider');
    }

    return true;
}
