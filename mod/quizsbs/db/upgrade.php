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
 * Upgrade script for the quizsbs module.
 *
 * @package    mod_quizsbs
 * @copyright  2006 Eloy Lafuente (stronk7)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * quizsbs module upgrade function.
 * @param string $oldversion the version we are upgrading from.
 */
function xmldb_quizsbs_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014052800) {

        // Define field completionattemptsexhausted to be added to quizsbs.
        $table = new xmldb_table('quizsbs');
        $field = new xmldb_field('completionattemptsexhausted', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'showblocks');

        // Conditionally launch add field completionattemptsexhausted.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // quizsbs savepoint reached.
        upgrade_mod_savepoint(true, 2014052800, 'quizsbs');
    }

    if ($oldversion < 2014052801) {
        // Define field completionpass to be added to quizsbs.
        $table = new xmldb_table('quizsbs');
        $field = new xmldb_field('completionpass', XMLDB_TYPE_INTEGER, '1', null, null, null, 0, 'completionattemptsexhausted');

        // Conditionally launch add field completionpass.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // quizsbs savepoint reached.
        upgrade_mod_savepoint(true, 2014052801, 'quizsbs');
    }

    // Moodle v2.8.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2015030500) {
        // Define field requireprevious to be added to quizsbs_slots.
        $table = new xmldb_table('quizsbs_slots');
        $field = new xmldb_field('requireprevious', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'page');

        // Conditionally launch add field page.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // quizsbs savepoint reached.
        upgrade_mod_savepoint(true, 2015030500, 'quizsbs');
    }

    if ($oldversion < 2015030900) {
        // Define field canredoquestions to be added to quizsbs.
        $table = new xmldb_table('quizsbs');
        $field = new xmldb_field('canredoquestions', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'preferredbehaviour');

        // Conditionally launch add field completionpass.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // quizsbs savepoint reached.
        upgrade_mod_savepoint(true, 2015030900, 'quizsbs');
    }

    if ($oldversion < 2015032300) {

        // Define table quizsbs_sections to be created.
        $table = new xmldb_table('quizsbs_sections');

        // Adding fields to table quizsbs_sections.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('quizsbsid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('firstslot', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('heading', XMLDB_TYPE_CHAR, '1333', null, null, null, null);
        $table->add_field('shufflequestions', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table quizsbs_sections.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('quizsbsid', XMLDB_KEY_FOREIGN, array('quizsbsid'), 'quizsbs', array('id'));

        // Adding indexes to table quizsbs_sections.
        $table->add_index('quizsbsid-firstslot', XMLDB_INDEX_UNIQUE, array('quizsbsid', 'firstslot'));

        // Conditionally launch create table for quizsbs_sections.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // quizsbs savepoint reached.
        upgrade_mod_savepoint(true, 2015032300, 'quizsbs');
    }

    if ($oldversion < 2015032301) {

        // Create a section for each quizsbs.
        $DB->execute("
                INSERT INTO {quizsbs_sections}
                            (quizsbsid, firstslot, heading, shufflequestions)
                     SELECT  id,     1,         ?,       shufflequestions
                       FROM {quizsbs}
                ", array(''));

        // quizsbs savepoint reached.
        upgrade_mod_savepoint(true, 2015032301, 'quizsbs');
    }

    if ($oldversion < 2015032302) {

        // Define field shufflequestions to be dropped from quizsbs.
        $table = new xmldb_table('quizsbs');
        $field = new xmldb_field('shufflequestions');

        // Conditionally launch drop field shufflequestions.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // quizsbs savepoint reached.
        upgrade_mod_savepoint(true, 2015032302, 'quizsbs');
    }

    if ($oldversion < 2015032303) {

        // Drop corresponding admin settings.
        unset_config('shufflequestions', 'quizsbs');
        unset_config('shufflequestions_adv', 'quizsbs');

        // quizsbs savepoint reached.
        upgrade_mod_savepoint(true, 2015032303, 'quizsbs');
    }

    // Moodle v2.9.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v3.0.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2016032600) {
        // Update quizsbs_sections to repair quizsbszes what were broken by MDL-53507.
        $problemquizsbszes = $DB->get_records_sql("
                SELECT quizsbsid, MIN(firstslot) AS firstsectionfirstslot
                FROM {quizsbs_sections}
                GROUP BY quizsbsid
                HAVING MIN(firstslot) > 1");

        if ($problemquizsbszes) {
            $pbar = new progress_bar('upgradequizsbsfirstsection', 500, true);
            $total = count($problemquizsbszes);
            $done = 0;
            foreach ($problemquizsbszes as $problemquizsbs) {
                $DB->set_field('quizsbs_sections', 'firstslot', 1,
                        array('quizsbsid' => $problemquizsbs->quizsbsid,
                        'firstslot' => $problemquizsbs->firstsectionfirstslot));
                $done += 1;
                $pbar->update($done, $total, "Fixing quizsbs layouts - {$done}/{$total}.");
            }
        }

        // quizsbs savepoint reached.
        upgrade_mod_savepoint(true, 2016032600, 'quizsbs');
    }

    // Moodle v3.1.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2016121903) {
        if ($dbman->table_exists('quizsbs_additional_content')) {
            $table = new xmldb_table('quizsbs_additional_content');
            $dbman->drop_table($table);
        }
        if ($dbman->table_exists('quizsbs_question_content')) {
            $table = new xmldb_table('quizsbs_question_content');
            $dbman->drop_table($table);
        }
        $dbman->install_one_table_from_xmldb_file(__DIR__ . '/install.xml', 'quizsbs_additional_content');
        $dbman->install_one_table_from_xmldb_file(__DIR__ . '/install.xml', 'quizsbs_question_content');
        $table = new xmldb_table('quizsbs_slots');
        $field = new xmldb_field('additionalcontentid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, false, null, null);
        $key = new xmldb_key('additionalcontentid', XMLDB_KEY_FOREIGN, array('additionalcontentid'), 'quizsbs_additional_content', array('id'));

        if(!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $dbman->add_key($table, $key);
        }

        upgrade_mod_savepoint(true, 2016121903, 'quizsbs');
    }

    if($oldversion < 2017010400) {
        $table = new xmldb_table('quizsbs_subject');
        if(!$dbman->table_exists($table)) {
            $dbman->install_one_table_from_xmldb_file(__DIR__ . '/install.xml', 'quizsbs_subject');
        }
        $table = new xmldb_table('quizsbs_additional_content');
        $field = new xmldb_field('subjectid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, false, null, null);
        $key = new xmldb_key('subject', XMLDB_KEY_FOREIGN, array('subjectid'), 'quizsbs_subject', array('id'));
        if(!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $dbman->add_key($table, $key);
        }

        upgrade_mod_savepoint(true, 2017010400, 'quizsbs');
    }

    if ($oldversion < 2017012503){
        $table = new xmldb_table('quizsbs_subject');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $table = new xmldb_table('quizsbs_slots');
        $key = new xmldb_key('additionalcontentid', XMLDB_KEY_FOREIGN, array('additionalcontentid'), 'quizsbs_additional_content', array('id'));
        $field = new xmldb_field('additionalcontentid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_key($table, $key);
            $dbman->drop_field($table, $field);
        }
        $table = new xmldb_table('quizsbs_additional_content');
        $index = new xmldb_index('pageinquiz', XMLDB_INDEX_UNIQUE, array('quizsbsid', 'subjectid'));
        $dbman->add_index($table, $index);

        upgrade_mod_savepoint(true, 2017012503, 'quizsbs');
    }

    return true;
}
