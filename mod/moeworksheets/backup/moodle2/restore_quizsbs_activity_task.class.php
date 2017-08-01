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

require_once($CFG->dirroot . '/mod/moeworksheets/backup/moodle2/restore_moeworksheets_stepslib.php');


/**
 * moeworksheets restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 *
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_moeworksheets_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // moeworksheets only has one structure step.
        $this->add_step(new restore_moeworksheets_activity_structure_step('moeworksheets_structure', 'moeworksheets.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    public static function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('moeworksheets', array('intro'), 'moeworksheets');
        $contents[] = new restore_decode_content('moeworksheets_feedback',
                array('feedbacktext'), 'moeworksheets_feedback');
        $contents[] = new restore_decode_content('moeworksheets_additionalcont', array('content'),'moeworksheets_additionalcont');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    public static function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('moeworksheetsVIEWBYID',
                '/mod/moeworksheets/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('moeworksheetsVIEWBYQ',
                '/mod/moeworksheets/view.php?q=$1', 'moeworksheets');
        $rules[] = new restore_decode_rule('moeworksheetsINDEX',
                '/mod/moeworksheets/index.php?id=$1', 'course');

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * moeworksheets logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    public static function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('moeworksheets', 'add',
                'view.php?id={course_module}', '{moeworksheets}');
        $rules[] = new restore_log_rule('moeworksheets', 'update',
                'view.php?id={course_module}', '{moeworksheets}');
        $rules[] = new restore_log_rule('moeworksheets', 'view',
                'view.php?id={course_module}', '{moeworksheets}');
        $rules[] = new restore_log_rule('moeworksheets', 'preview',
                'view.php?id={course_module}', '{moeworksheets}');
        $rules[] = new restore_log_rule('moeworksheets', 'report',
                'report.php?id={course_module}', '{moeworksheets}');
        $rules[] = new restore_log_rule('moeworksheets', 'editquestions',
                'view.php?id={course_module}', '{moeworksheets}');
        $rules[] = new restore_log_rule('moeworksheets', 'delete attempt',
                'report.php?id={course_module}', '[oldattempt]');
        $rules[] = new restore_log_rule('moeworksheets', 'edit override',
                'overrideedit.php?id={moeworksheets_override}', '{moeworksheets}');
        $rules[] = new restore_log_rule('moeworksheets', 'delete override',
                'overrides.php.php?cmid={course_module}', '{moeworksheets}');
        $rules[] = new restore_log_rule('moeworksheets', 'addcategory',
                'view.php?id={course_module}', '{question_category}');
        $rules[] = new restore_log_rule('moeworksheets', 'view summary',
                'summary.php?attempt={moeworksheets_attempt}', '{moeworksheets}');
        $rules[] = new restore_log_rule('moeworksheets', 'manualgrade',
                'comment.php?attempt={moeworksheets_attempt}&question={question}', '{moeworksheets}');
        $rules[] = new restore_log_rule('moeworksheets', 'manualgrading',
                'report.php?mode=grading&q={moeworksheets}', '{moeworksheets}');
        // All the ones calling to review.php have two rules to handle both old and new urls
        // in any case they are always converted to new urls on restore.
        // TODO: In Moodle 2.x (x >= 5) kill the old rules.
        // Note we are using the 'moeworksheets_attempt' mapping because that is the
        // one containing the moeworksheets_attempt->ids old an new for moeworksheets-attempt.
        $rules[] = new restore_log_rule('moeworksheets', 'attempt',
                'review.php?id={course_module}&attempt={moeworksheets_attempt}', '{moeworksheets}',
                null, null, 'review.php?attempt={moeworksheets_attempt}');
        $rules[] = new restore_log_rule('moeworksheets', 'attempt',
                'review.php?attempt={moeworksheets_attempt}', '{moeworksheets}',
                null, null, 'review.php?attempt={moeworksheets_attempt}');
        // Old an new for moeworksheets-submit.
        $rules[] = new restore_log_rule('moeworksheets', 'submit',
                'review.php?id={course_module}&attempt={moeworksheets_attempt}', '{moeworksheets}',
                null, null, 'review.php?attempt={moeworksheets_attempt}');
        $rules[] = new restore_log_rule('moeworksheets', 'submit',
                'review.php?attempt={moeworksheets_attempt}', '{moeworksheets}');
        // Old an new for moeworksheets-review.
        $rules[] = new restore_log_rule('moeworksheets', 'review',
                'review.php?id={course_module}&attempt={moeworksheets_attempt}', '{moeworksheets}',
                null, null, 'review.php?attempt={moeworksheets_attempt}');
        $rules[] = new restore_log_rule('moeworksheets', 'review',
                'review.php?attempt={moeworksheets_attempt}', '{moeworksheets}');
        // Old an new for moeworksheets-start attemp.
        $rules[] = new restore_log_rule('moeworksheets', 'start attempt',
                'review.php?id={course_module}&attempt={moeworksheets_attempt}', '{moeworksheets}',
                null, null, 'review.php?attempt={moeworksheets_attempt}');
        $rules[] = new restore_log_rule('moeworksheets', 'start attempt',
                'review.php?attempt={moeworksheets_attempt}', '{moeworksheets}');
        // Old an new for moeworksheets-close attemp.
        $rules[] = new restore_log_rule('moeworksheets', 'close attempt',
                'review.php?id={course_module}&attempt={moeworksheets_attempt}', '{moeworksheets}',
                null, null, 'review.php?attempt={moeworksheets_attempt}');
        $rules[] = new restore_log_rule('moeworksheets', 'close attempt',
                'review.php?attempt={moeworksheets_attempt}', '{moeworksheets}');
        // Old an new for moeworksheets-continue attempt.
        $rules[] = new restore_log_rule('moeworksheets', 'continue attempt',
                'review.php?id={course_module}&attempt={moeworksheets_attempt}', '{moeworksheets}',
                null, null, 'review.php?attempt={moeworksheets_attempt}');
        $rules[] = new restore_log_rule('moeworksheets', 'continue attempt',
                'review.php?attempt={moeworksheets_attempt}', '{moeworksheets}');
        // Old an new for moeworksheets-continue attemp.
        $rules[] = new restore_log_rule('moeworksheets', 'continue attemp',
                'review.php?id={course_module}&attempt={moeworksheets_attempt}', '{moeworksheets}',
                null, 'continue attempt', 'review.php?attempt={moeworksheets_attempt}');
        $rules[] = new restore_log_rule('moeworksheets', 'continue attemp',
                'review.php?attempt={moeworksheets_attempt}', '{moeworksheets}',
                null, 'continue attempt');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    public static function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('moeworksheets', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
