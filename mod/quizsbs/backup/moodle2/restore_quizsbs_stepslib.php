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
 * @package    mod_quizsbs
 * @subpackage backup-moodle2
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Structure step to restore one quizsbs activity
 *
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_quizsbs_activity_structure_step extends restore_questions_activity_structure_step {

    /**
     * @var bool tracks whether the quizsbs contains at least one section. Before
     * Moodle 2.9 quizsbs sections did not exist, so if the file being restored
     * did not contain any, we need to create one in {@link after_execute()}.
     */
    protected $sectioncreated = false;

    /**
     * @var bool when restoring old quizsbszes (2.8 or before) this records the
     * shufflequestionsoption quizsbs option which has moved to the quizsbs_sections table.
     */
    protected $legacyshufflequestionsoption = false;

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $quizsbs = new restore_path_element('quizsbs', '/activity/quizsbs');
        $paths[] = $quizsbs;

        // A chance for access subplugings to set up their quizsbs data.
        $this->add_subplugin_structure('quizsbsaccess', $quizsbs);

        $paths[] = new restore_path_element('quizsbs_question_instance',
                '/activity/quizsbs/question_instances/question_instance');
        $paths[] = new restore_path_element('quizsbs_additional_content', '/activity/quizsbs/additional_contents/additional_content');
        $paths[] = new restore_path_element('quizsbs_question_content', '/activity/quizsbs/additional_contents/additional_content/questioncontents/questioncontent');
        $paths[] = new restore_path_element('quizsbs_section', '/activity/quizsbs/sections/section');
        $paths[] = new restore_path_element('quizsbs_feedback', '/activity/quizsbs/feedbacks/feedback');
        $paths[] = new restore_path_element('quizsbs_override', '/activity/quizsbs/overrides/override');

        if ($userinfo) {
            $paths[] = new restore_path_element('quizsbs_grade', '/activity/quizsbs/grades/grade');

            if ($this->task->get_old_moduleversion() > 2011010100) {
                // Restoring from a version 2.1 dev or later.
                // Process the new-style attempt data.
                $quizsbsattempt = new restore_path_element('quizsbs_attempt',
                        '/activity/quizsbs/attempts/attempt');
                $paths[] = $quizsbsattempt;

                // Add states and sessions.
                $this->add_question_usages($quizsbsattempt, $paths);

                // A chance for access subplugings to set up their attempt data.
                $this->add_subplugin_structure('quizsbsaccess', $quizsbsattempt);

            } else {
                // Restoring from a version 2.0.x+ or earlier.
                // Upgrade the legacy attempt data.
                $quizsbsattempt = new restore_path_element('quizsbs_attempt_legacy',
                        '/activity/quizsbs/attempts/attempt',
                        true);
                $paths[] = $quizsbsattempt;
                $this->add_legacy_question_attempt_data($quizsbsattempt, $paths);
            }
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    protected function process_quizsbs($data) {
        global $CFG, $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        if (property_exists($data, 'questions')) {
            // Needed by {@link process_quizsbs_attempt_legacy}, in which case it will be present.
            $this->oldquizsbslayout = $data->questions;
        }

        // The setting quizsbs->attempts can come both in data->attempts and
        // data->attempts_number, handle both. MDL-26229.
        if (isset($data->attempts_number)) {
            $data->attempts = $data->attempts_number;
            unset($data->attempts_number);
        }

        // The old optionflags and penaltyscheme from 2.0 need to be mapped to
        // the new preferredbehaviour. See MDL-20636.
        if (!isset($data->preferredbehaviour)) {
            if (empty($data->optionflags)) {
                $data->preferredbehaviour = 'deferredfeedback';
            } else if (empty($data->penaltyscheme)) {
                $data->preferredbehaviour = 'adaptivenopenalty';
            } else {
                $data->preferredbehaviour = 'adaptive';
            }
            unset($data->optionflags);
            unset($data->penaltyscheme);
        }

        // The old review column from 2.0 need to be split into the seven new
        // review columns. See MDL-20636.
        if (isset($data->review)) {
            require_once($CFG->dirroot . '/mod/quizsbs/locallib.php');

            if (!defined('quizsbs_OLD_IMMEDIATELY')) {
                define('quizsbs_OLD_IMMEDIATELY', 0x3c003f);
                define('quizsbs_OLD_OPEN',        0x3c00fc0);
                define('quizsbs_OLD_CLOSED',      0x3c03f000);

                define('quizsbs_OLD_RESPONSES',        1*0x1041);
                define('quizsbs_OLD_SCORES',           2*0x1041);
                define('quizsbs_OLD_FEEDBACK',         4*0x1041);
                define('quizsbs_OLD_ANSWERS',          8*0x1041);
                define('quizsbs_OLD_SOLUTIONS',       16*0x1041);
                define('quizsbs_OLD_GENERALFEEDBACK', 32*0x1041);
                define('quizsbs_OLD_OVERALLFEEDBACK',  1*0x4440000);
            }

            $oldreview = $data->review;

            $data->reviewattempt =
                    mod_quizsbs_display_options::DURING |
                    ($oldreview & quizsbs_OLD_IMMEDIATELY & quizsbs_OLD_RESPONSES ?
                            mod_quizsbs_display_options::IMMEDIATELY_AFTER : 0) |
                    ($oldreview & quizsbs_OLD_OPEN & quizsbs_OLD_RESPONSES ?
                            mod_quizsbs_display_options::LATER_WHILE_OPEN : 0) |
                    ($oldreview & quizsbs_OLD_CLOSED & quizsbs_OLD_RESPONSES ?
                            mod_quizsbs_display_options::AFTER_CLOSE : 0);

            $data->reviewcorrectness =
                    mod_quizsbs_display_options::DURING |
                    ($oldreview & quizsbs_OLD_IMMEDIATELY & quizsbs_OLD_SCORES ?
                            mod_quizsbs_display_options::IMMEDIATELY_AFTER : 0) |
                    ($oldreview & quizsbs_OLD_OPEN & quizsbs_OLD_SCORES ?
                            mod_quizsbs_display_options::LATER_WHILE_OPEN : 0) |
                    ($oldreview & quizsbs_OLD_CLOSED & quizsbs_OLD_SCORES ?
                            mod_quizsbs_display_options::AFTER_CLOSE : 0);

            $data->reviewmarks =
                    mod_quizsbs_display_options::DURING |
                    ($oldreview & quizsbs_OLD_IMMEDIATELY & quizsbs_OLD_SCORES ?
                            mod_quizsbs_display_options::IMMEDIATELY_AFTER : 0) |
                    ($oldreview & quizsbs_OLD_OPEN & quizsbs_OLD_SCORES ?
                            mod_quizsbs_display_options::LATER_WHILE_OPEN : 0) |
                    ($oldreview & quizsbs_OLD_CLOSED & quizsbs_OLD_SCORES ?
                            mod_quizsbs_display_options::AFTER_CLOSE : 0);

            $data->reviewspecificfeedback =
                    ($oldreview & quizsbs_OLD_IMMEDIATELY & quizsbs_OLD_FEEDBACK ?
                            mod_quizsbs_display_options::DURING : 0) |
                    ($oldreview & quizsbs_OLD_IMMEDIATELY & quizsbs_OLD_FEEDBACK ?
                            mod_quizsbs_display_options::IMMEDIATELY_AFTER : 0) |
                    ($oldreview & quizsbs_OLD_OPEN & quizsbs_OLD_FEEDBACK ?
                            mod_quizsbs_display_options::LATER_WHILE_OPEN : 0) |
                    ($oldreview & quizsbs_OLD_CLOSED & quizsbs_OLD_FEEDBACK ?
                            mod_quizsbs_display_options::AFTER_CLOSE : 0);

            $data->reviewgeneralfeedback =
                    ($oldreview & quizsbs_OLD_IMMEDIATELY & quizsbs_OLD_GENERALFEEDBACK ?
                            mod_quizsbs_display_options::DURING : 0) |
                    ($oldreview & quizsbs_OLD_IMMEDIATELY & quizsbs_OLD_GENERALFEEDBACK ?
                            mod_quizsbs_display_options::IMMEDIATELY_AFTER : 0) |
                    ($oldreview & quizsbs_OLD_OPEN & quizsbs_OLD_GENERALFEEDBACK ?
                            mod_quizsbs_display_options::LATER_WHILE_OPEN : 0) |
                    ($oldreview & quizsbs_OLD_CLOSED & quizsbs_OLD_GENERALFEEDBACK ?
                            mod_quizsbs_display_options::AFTER_CLOSE : 0);

            $data->reviewrightanswer =
                    ($oldreview & quizsbs_OLD_IMMEDIATELY & quizsbs_OLD_ANSWERS ?
                            mod_quizsbs_display_options::DURING : 0) |
                    ($oldreview & quizsbs_OLD_IMMEDIATELY & quizsbs_OLD_ANSWERS ?
                            mod_quizsbs_display_options::IMMEDIATELY_AFTER : 0) |
                    ($oldreview & quizsbs_OLD_OPEN & quizsbs_OLD_ANSWERS ?
                            mod_quizsbs_display_options::LATER_WHILE_OPEN : 0) |
                    ($oldreview & quizsbs_OLD_CLOSED & quizsbs_OLD_ANSWERS ?
                            mod_quizsbs_display_options::AFTER_CLOSE : 0);

            $data->reviewoverallfeedback =
                    0 |
                    ($oldreview & quizsbs_OLD_IMMEDIATELY & quizsbs_OLD_OVERALLFEEDBACK ?
                            mod_quizsbs_display_options::IMMEDIATELY_AFTER : 0) |
                    ($oldreview & quizsbs_OLD_OPEN & quizsbs_OLD_OVERALLFEEDBACK ?
                            mod_quizsbs_display_options::LATER_WHILE_OPEN : 0) |
                    ($oldreview & quizsbs_OLD_CLOSED & quizsbs_OLD_OVERALLFEEDBACK ?
                            mod_quizsbs_display_options::AFTER_CLOSE : 0);
        }

        // The old popup column from from <= 2.1 need to be mapped to
        // the new browsersecurity. See MDL-29627.
        if (!isset($data->browsersecurity)) {
            if (empty($data->popup)) {
                $data->browsersecurity = '-';
            } else if ($data->popup == 1) {
                $data->browsersecurity = 'securewindow';
            } else if ($data->popup == 2) {
                $data->browsersecurity = 'safebrowser';
            } else {
                $data->preferredbehaviour = '-';
            }
            unset($data->popup);
        }

        if (!isset($data->overduehandling)) {
            $data->overduehandling = get_config('quizsbs', 'overduehandling');
        }

        // Insert the quizsbs record.
        $newitemid = $DB->insert_record('quizsbs', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    protected function process_quizsbs_question_instance($data) {
        global $DB;

        $data = (object)$data;

        // Backwards compatibility for old field names (MDL-43670).
        if (!isset($data->questionid) && isset($data->question)) {
            $data->questionid = $data->question;
        }
        if (!isset($data->maxmark) && isset($data->grade)) {
            $data->maxmark = $data->grade;
        }

        if (!property_exists($data, 'slot')) {
            $page = 1;
            $slot = 1;
            foreach (explode(',', $this->oldquizsbslayout) as $item) {
                if ($item == 0) {
                    $page += 1;
                    continue;
                }
                if ($item == $data->questionid) {
                    $data->slot = $slot;
                    $data->page = $page;
                    break;
                }
                $slot += 1;
            }
        }

        if (!property_exists($data, 'slot')) {
            // There was a question_instance in the backup file for a question
            // that was not acutally in the quizsbs. Drop it.
            $this->log('question ' . $data->questionid . ' was associated with quizsbs ' .
                    $this->get_new_parentid('quizsbs') . ' but not actually used. ' .
                    'The instance has been ignored.', backup::LOG_INFO);
            return;
        }

        $data->quizsbsid = $this->get_new_parentid('quizsbs');
        $data->questionid = $this->get_mappingid('question', $data->questionid);

        $DB->insert_record('quizsbs_slots', $data);
    }

    protected function process_quizsbs_additional_content($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->quizsbsid = $this->get_new_parentid('quizsbs');
        $newid = $DB->insert_record('quizsbs_additional_content', $data);
        $this->set_mapping('quizsbs_additional_content', $oldid, $newid, true);
    }

    protected function process_quizsbs_question_content($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->additionalcontentid = $this->get_new_parentid('quizsbs_additional_content');
        $newitemid = $DB->insert_record('quizsbs_question_content', $data);
        $this->set_mapping('quizsbs_question_content', $oldid, $newitemid, true);
    }

    protected function process_quizsbs_section($data) {
        global $DB;

        $data = (object) $data;
        $data->quizsbsid = $this->get_new_parentid('quizsbs');
        $newitemid = $DB->insert_record('quizsbs_sections', $data);
        $this->sectioncreated = true;
    }

    protected function process_quizsbs_feedback($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->quizsbsid = $this->get_new_parentid('quizsbs');

        $newitemid = $DB->insert_record('quizsbs_feedback', $data);
        $this->set_mapping('quizsbs_feedback', $oldid, $newitemid, true); // Has related files.
    }

    protected function process_quizsbs_override($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Based on userinfo, we'll restore user overides or no.
        $userinfo = $this->get_setting_value('userinfo');

        // Skip user overrides if we are not restoring userinfo.
        if (!$userinfo && !is_null($data->userid)) {
            return;
        }

        $data->quizsbs = $this->get_new_parentid('quizsbs');

        if ($data->userid !== null) {
            $data->userid = $this->get_mappingid('user', $data->userid);
        }

        if ($data->groupid !== null) {
            $data->groupid = $this->get_mappingid('group', $data->groupid);
        }

        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);

        $newitemid = $DB->insert_record('quizsbs_overrides', $data);

        // Add mapping, restore of logs needs it.
        $this->set_mapping('quizsbs_override', $oldid, $newitemid);
    }

    protected function process_quizsbs_grade($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->quizsbs = $this->get_new_parentid('quizsbs');

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->grade = $data->gradeval;

        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $DB->insert_record('quizsbs_grades', $data);
    }

    protected function process_quizsbs_attempt($data) {
        $data = (object)$data;

        $data->quizsbs = $this->get_new_parentid('quizsbs');
        $data->attempt = $data->attemptnum;

        $data->userid = $this->get_mappingid('user', $data->userid);

        $data->timestart = $this->apply_date_offset($data->timestart);
        $data->timefinish = $this->apply_date_offset($data->timefinish);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        if (!empty($data->timecheckstate)) {
            $data->timecheckstate = $this->apply_date_offset($data->timecheckstate);
        } else {
            $data->timecheckstate = 0;
        }

        // Deals with up-grading pre-2.3 back-ups to 2.3+.
        if (!isset($data->state)) {
            if ($data->timefinish > 0) {
                $data->state = 'finished';
            } else {
                $data->state = 'inprogress';
            }
        }

        // The data is actually inserted into the database later in inform_new_usage_id.
        $this->currentquizsbsattempt = clone($data);
    }

    protected function process_quizsbs_attempt_legacy($data) {
        global $DB;

        $this->process_quizsbs_attempt($data);

        $quizsbs = $DB->get_record('quizsbs', array('id' => $this->get_new_parentid('quizsbs')));
        $quizsbs->oldquestions = $this->oldquizsbslayout;
        $this->process_legacy_quizsbs_attempt_data($data, $quizsbs);
    }

    protected function inform_new_usage_id($newusageid) {
        global $DB;

        $data = $this->currentquizsbsattempt;

        $oldid = $data->id;
        $data->uniqueid = $newusageid;

        $newitemid = $DB->insert_record('quizsbs_attempts', $data);

        // Save quizsbs_attempt->id mapping, because logs use it.
        $this->set_mapping('quizsbs_attempt', $oldid, $newitemid, false);
    }

    protected function after_execute() {
        global $DB;

        parent::after_execute();
        // Add quizsbs related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_quizsbs', 'intro', null);
        // Add feedback related files, matching by itemname = 'quizsbs_feedback'.
        $this->add_related_files('mod_quizsbs', 'feedback', 'quizsbs_feedback');

        $this->add_related_files('mod_quizsbs', 'app', 'quizsbs_additional_content');

        $this->add_related_files('mod_quizsbs', 'content', 'quizsbs_additional_content');

        if (!$this->sectioncreated) {
            $DB->insert_record('quizsbs_sections', array(
                    'quizsbsid' => $this->get_new_parentid('quizsbs'),
                    'firstslot' => 1, 'heading' => '',
                    'shufflequestions' => $this->legacyshufflequestionsoption));
        }
    }
}
