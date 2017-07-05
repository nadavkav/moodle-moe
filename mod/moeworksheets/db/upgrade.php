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
 * Upgrade script for the moeworksheets module.
 *
 * @package    mod_moeworksheets
 * @copyright  2006 Eloy Lafuente (stronk7)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * moeworksheets module upgrade function.
 * @param string $oldversion the version we are upgrading from.
 */
function xmldb_moeworksheets_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014052800) {

        // Define field completionattemptsexhausted to be added to moeworksheets.
        $table = new xmldb_table('moeworksheets');
        $field = new xmldb_field('completionattemptsexhausted', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'showblocks');

        // Conditionally launch add field completionattemptsexhausted.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // moeworksheets savepoint reached.
        upgrade_mod_savepoint(true, 2014052800, 'moeworksheets');
    }

    if ($oldversion < 2014052801) {
        // Define field completionpass to be added to moeworksheets.
        $table = new xmldb_table('moeworksheets');
        $field = new xmldb_field('completionpass', XMLDB_TYPE_INTEGER, '1', null, null, null, 0, 'completionattemptsexhausted');

        // Conditionally launch add field completionpass.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // moeworksheets savepoint reached.
        upgrade_mod_savepoint(true, 2014052801, 'moeworksheets');
    }

    // Moodle v2.8.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2015030500) {
        // Define field requireprevious to be added to moeworksheets_slots.
        $table = new xmldb_table('moeworksheets_slots');
        $field = new xmldb_field('requireprevious', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'page');

        // Conditionally launch add field page.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // moeworksheets savepoint reached.
        upgrade_mod_savepoint(true, 2015030500, 'moeworksheets');
    }

    if ($oldversion < 2015030900) {
        // Define field canredoquestions to be added to moeworksheets.
        $table = new xmldb_table('moeworksheets');
        $field = new xmldb_field('canredoquestions', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'preferredbehaviour');

        // Conditionally launch add field completionpass.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // moeworksheets savepoint reached.
        upgrade_mod_savepoint(true, 2015030900, 'moeworksheets');
    }

    if ($oldversion < 2015032300) {

        // Define table moeworksheets_sections to be created.
        $table = new xmldb_table('moeworksheets_sections');

        // Adding fields to table moeworksheets_sections.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('moeworksheetsid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('firstslot', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('heading', XMLDB_TYPE_CHAR, '1333', null, null, null, null);
        $table->add_field('shufflequestions', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table moeworksheets_sections.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('moeworksheetsid', XMLDB_KEY_FOREIGN, array('moeworksheetsid'), 'moeworksheets', array('id'));

        // Adding indexes to table moeworksheets_sections.
        $table->add_index('moeworksheetsid-firstslot', XMLDB_INDEX_UNIQUE, array('moeworksheetsid', 'firstslot'));

        // Conditionally launch create table for moeworksheets_sections.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // moeworksheets savepoint reached.
        upgrade_mod_savepoint(true, 2015032300, 'moeworksheets');
    }

    if ($oldversion < 2015032301) {

        // Create a section for each moeworksheets.
        $DB->execute("
                INSERT INTO {moeworksheets_sections}
                            (moeworksheetsid, firstslot, heading, shufflequestions)
                     SELECT  id,     1,         ?,       shufflequestions
                       FROM {moeworksheets}
                ", array(''));

        // moeworksheets savepoint reached.
        upgrade_mod_savepoint(true, 2015032301, 'moeworksheets');
    }

    if ($oldversion < 2015032302) {

        // Define field shufflequestions to be dropped from moeworksheets.
        $table = new xmldb_table('moeworksheets');
        $field = new xmldb_field('shufflequestions');

        // Conditionally launch drop field shufflequestions.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // moeworksheets savepoint reached.
        upgrade_mod_savepoint(true, 2015032302, 'moeworksheets');
    }

    if ($oldversion < 2015032303) {

        // Drop corresponding admin settings.
        unset_config('shufflequestions', 'moeworksheets');
        unset_config('shufflequestions_adv', 'moeworksheets');

        // moeworksheets savepoint reached.
        upgrade_mod_savepoint(true, 2015032303, 'moeworksheets');
    }

    // Moodle v2.9.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v3.0.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2016032600) {
        // Update moeworksheets_sections to repair moeworksheetszes what were broken by MDL-53507.
        $problemmoeworksheetszes = $DB->get_records_sql("
                SELECT moeworksheetsid, MIN(firstslot) AS firstsectionfirstslot
                FROM {moeworksheets_sections}
                GROUP BY moeworksheetsid
                HAVING MIN(firstslot) > 1");

        if ($problemmoeworksheetszes) {
            $pbar = new progress_bar('upgrademoeworksheetsfirstsection', 500, true);
            $total = count($problemmoeworksheetszes);
            $done = 0;
            foreach ($problemmoeworksheetszes as $problemmoeworksheets) {
                $DB->set_field('moeworksheets_sections', 'firstslot', 1,
                        array('moeworksheetsid' => $problemmoeworksheets->moeworksheetsid,
                        'firstslot' => $problemmoeworksheets->firstsectionfirstslot));
                $done += 1;
                $pbar->update($done, $total, "Fixing moeworksheets layouts - {$done}/{$total}.");
            }
        }

        // moeworksheets savepoint reached.
        upgrade_mod_savepoint(true, 2016032600, 'moeworksheets');
    }

    // Moodle v3.1.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2016121903) {
        if ($dbman->table_exists('moeworksheets_additionalcont')) {
            $table = new xmldb_table('moeworksheets_additionalcont');
            $dbman->drop_table($table);
        }
        if ($dbman->table_exists('moeworksheets_additionalcont')) {
            $table = new xmldb_table('moeworksheets_additionalcont');
            $dbman->drop_table($table);
        }
        $dbman->install_one_table_from_xmldb_file(__DIR__ . '/install.xml', 'moeworksheets_additionalcont');
        $dbman->install_one_table_from_xmldb_file(__DIR__ . '/install.xml', 'moeworksheets_additionalcont');
        $table = new xmldb_table('moeworksheets_slots');
        $field = new xmldb_field('additionalcontentid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, false, null, null);
        $key = new xmldb_key('additionalcontentid', XMLDB_KEY_FOREIGN, array('additionalcontentid'), 'moeworksheets_additionalcont', array('id'));

        if(!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $dbman->add_key($table, $key);
        }

        upgrade_mod_savepoint(true, 2016121903, 'moeworksheets');
    }

    if($oldversion < 2017010400) {
        $table = new xmldb_table('moeworksheets_subject');
        if(!$dbman->table_exists($table)) {
            $dbman->install_one_table_from_xmldb_file(__DIR__ . '/install.xml', 'moeworksheets_subject');
        }
        $table = new xmldb_table('moeworksheets_additionalcont');
        $field = new xmldb_field('subjectid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, false, null, null);
        $key = new xmldb_key('subject', XMLDB_KEY_FOREIGN, array('subjectid'), 'moeworksheets_subject', array('id'));
        if(!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $dbman->add_key($table, $key);
        }

        upgrade_mod_savepoint(true, 2017010400, 'moeworksheets');
    }

    if ($oldversion < 2017012503){
        $table = new xmldb_table('moeworksheets_subject');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $table = new xmldb_table('moeworksheets_slots');
        $key = new xmldb_key('additionalcontentid', XMLDB_KEY_FOREIGN, array('additionalcontentid'), 'moeworksheets_additionalcont', array('id'));
        $field = new xmldb_field('additionalcontentid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_key($table, $key);
            $dbman->drop_field($table, $field);
        }
        $table = new xmldb_table('moeworksheets_additionalcont');
        $index = new xmldb_index('pageinquiz', XMLDB_INDEX_UNIQUE, array('moeworksheetsid', 'subjectid'));
        $dbman->add_index($table, $index);

        upgrade_mod_savepoint(true, 2017012503, 'moeworksheets');
    }

    return true;
}
