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
 * @package    mod_moeworksheets
 * @subpackage backup-moodle2
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Define all the backup steps that will be used by the backup_moeworksheets_activity_task
 *
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_moeworksheets_activity_structure_step extends backup_questions_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $moeworksheets = new backup_nested_element('moeworksheets', array('id'), array(
            'name', 'intro', 'introformat', 'timeopen', 'timeclose', 'timelimit',
            'overduehandling', 'graceperiod', 'preferredbehaviour', 'canredoquestions', 'attempts_number',
            'attemptonlast', 'grademethod', 'decimalpoints', 'questiondecimalpoints',
            'reviewattempt', 'reviewcorrectness', 'reviewmarks',
            'reviewspecificfeedback', 'reviewgeneralfeedback',
            'reviewrightanswer', 'reviewoverallfeedback',
            'questionsperpage', 'navmethod', 'shuffleanswers',
            'sumgrades', 'grade', 'timecreated',
            'timemodified', 'password', 'subnet', 'browsersecurity',
            'delay1', 'delay2', 'showuserpicture', 'showblocks', 'completionattemptsexhausted', 'completionpass'));

        // Define elements for access rule subplugin settings.
        $this->add_subplugin_structure('moeworksheetsaccess', $moeworksheets, true);

        $qinstances = new backup_nested_element('question_instances');

        $qinstance = new backup_nested_element('question_instance', array('id'), array(
            'slot', 'page', 'requireprevious', 'questionid', 'maxmark'));

        $additionalcontents = new backup_nested_element('additional_contents');
        $additionalcontent = new backup_nested_element('additional_content', array('id'), array(
            'name',
            'createdate',
            'modifieddate',
            'type',
            'subjectid',
        ));

        $qestioncontents = new backup_nested_element('questioncontents');
        $qestioncontent = new backup_nested_element('questioncontent', array('id'), array(
            'content',
            'type',
        ));

        $sections = new backup_nested_element('sections');

        $section = new backup_nested_element('section', array('id'), array(
            'firstslot', 'heading', 'shufflequestions'));

        $feedbacks = new backup_nested_element('feedbacks');

        $feedback = new backup_nested_element('feedback', array('id'), array(
            'feedbacktext', 'feedbacktextformat', 'mingrade', 'maxgrade'));

        $overrides = new backup_nested_element('overrides');

        $override = new backup_nested_element('override', array('id'), array(
            'userid', 'groupid', 'timeopen', 'timeclose',
            'timelimit', 'attempts', 'password'));

        $grades = new backup_nested_element('grades');

        $grade = new backup_nested_element('grade', array('id'), array(
            'userid', 'gradeval', 'timemodified'));

        $attempts = new backup_nested_element('attempts');

        $attempt = new backup_nested_element('attempt', array('id'), array(
            'userid', 'attemptnum', 'uniqueid', 'layout', 'currentpage', 'preview',
            'state', 'timestart', 'timefinish', 'timemodified', 'timecheckstate', 'sumgrades'));

        // This module is using questions, so produce the related question states and sessions
        // attaching them to the $attempt element based in 'uniqueid' matching.
        $this->add_question_usages($attempt, 'uniqueid');

        // Define elements for access rule subplugin attempt data.
        $this->add_subplugin_structure('moeworksheetsaccess', $attempt, true);

        // Build the tree.
        $moeworksheets->add_child($qinstances);
        $qinstances->add_child($qinstance);

        $moeworksheets->add_child($additionalcontents);
        $additionalcontents->add_child($additionalcontent);

        $additionalcontent->add_child($qestioncontents);
        $qestioncontents->add_child($qestioncontent);

        $moeworksheets->add_child($sections);
        $sections->add_child($section);

        $moeworksheets->add_child($feedbacks);
        $feedbacks->add_child($feedback);

        $moeworksheets->add_child($overrides);
        $overrides->add_child($override);

        $moeworksheets->add_child($grades);
        $grades->add_child($grade);

        $moeworksheets->add_child($attempts);
        $attempts->add_child($attempt);

        // Define sources.
        $moeworksheets->set_source_table('moeworksheets', array('id' => backup::VAR_ACTIVITYID));

        $qinstance->set_source_table('moeworksheets_slots',
                array('moeworksheetsid' => backup::VAR_PARENTID));

        $additionalcontent->set_source_table('moeworksheets_additionalcont', array(
            'moeworksheetsid' => backup::VAR_PARENTID,
        ));

        $qestioncontent->set_source_table('moeworksheets_additionalcont', array(
           'additionalcontentid' => backup::VAR_PARENTID,
        ));

        $section->set_source_table('moeworksheets_sections',
                array('moeworksheetsid' => backup::VAR_PARENTID));

        $feedback->set_source_table('moeworksheets_feedback',
                array('moeworksheetsid' => backup::VAR_PARENTID));

        // moeworksheets overrides to backup are different depending of user info.
        $overrideparams = array('moeworksheets' => backup::VAR_PARENTID);
        if (!$userinfo) { //  Without userinfo, skip user overrides.
            $overrideparams['userid'] = backup_helper::is_sqlparam(null);

        }
        $override->set_source_table('moeworksheets_overrides', $overrideparams);

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $grade->set_source_table('moeworksheets_grades', array('moeworksheets' => backup::VAR_PARENTID));
            $attempt->set_source_sql('
                    SELECT *
                    FROM {moeworksheets_attempts}
                    WHERE moeworksheets = :moeworksheets AND preview = 0',
                    array('moeworksheets' => backup::VAR_PARENTID));
        }

        // Define source alias.
        $moeworksheets->set_source_alias('attempts', 'attempts_number');
        $grade->set_source_alias('grade', 'gradeval');
        $attempt->set_source_alias('attempt', 'attemptnum');

        // Define id annotations.
        $qinstance->annotate_ids('question', 'questionid');
        $override->annotate_ids('user', 'userid');
        $override->annotate_ids('group', 'groupid');
        $grade->annotate_ids('user', 'userid');
        $attempt->annotate_ids('user', 'userid');

        // Define file annotations.
        $moeworksheets->annotate_files('mod_moeworksheets', 'intro', null); // This file area hasn't itemid.
        $feedback->annotate_files('mod_moeworksheets', 'feedback', 'id');
        $additionalcontent->annotate_files('mod_moeworksheets', 'app', 'id');
        $qestioncontent->annotate_files('mod_moeworksheets', 'content', 'id');

        // Return the root element (moeworksheets), wrapped into standard activity structure.
        return $this->prepare_activity_structure($moeworksheets);
    }
}
