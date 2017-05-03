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
 * Library of functions used by the quizsbs module.
 *
 * This contains functions that are called from within the quizsbs module only
 * Functions that are also called by core Moodle are in {@link lib.php}
 * This script also loads the code in {@link questionlib.php} which holds
 * the module-indpendent code for handling questions and which in turn
 * initialises all the questiontype classes.
 *
 * @package    mod_quizsbs
 * @copyright  1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quizsbs/lib.php');
require_once($CFG->dirroot . '/mod/quizsbs/accessmanager.php');
require_once($CFG->dirroot . '/mod/quizsbs/accessmanager_form.php');
require_once($CFG->dirroot . '/mod/quizsbs/renderer.php');
require_once($CFG->dirroot . '/mod/quizsbs/attemptlib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/eventslib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/questionlib.php');


/**
 * @var int We show the countdown timer if there is less than this amount of time left before the
 * the quizsbs close date. (1 hour)
 */
define('quizsbs_SHOW_TIME_BEFORE_DEADLINE', '3600');

/**
 * @var int If there are fewer than this many seconds left when the student submits
 * a page of the quizsbs, then do not take them to the next page of the quizsbs. Instead
 * close the quizsbs immediately.
 */
define('quizsbs_MIN_TIME_TO_CONTINUE', '2');

/**
 * @var int We show no image when user selects No image from dropdown menu in quizsbs settings.
 */
define('quizsbs_SHOWIMAGE_NONE', 0);

/**
 * @var int We show small image when user selects small image from dropdown menu in quizsbs settings.
 */
define('quizsbs_SHOWIMAGE_SMALL', 1);

/**
 * @var int We show Large image when user selects Large image from dropdown menu in quizsbs settings.
 */
define('quizsbs_SHOWIMAGE_LARGE', 2);


// Functions related to attempts ///////////////////////////////////////////////

/**
 * Creates an object to represent a new attempt at a quizsbs
 *
 * Creates an attempt object to represent an attempt at the quizsbs by the current
 * user starting at the current time. The ->id field is not set. The object is
 * NOT written to the database.
 *
 * @param object $quizsbsobj the quizsbs object to create an attempt for.
 * @param int $attemptnumber the sequence number for the attempt.
 * @param object $lastattempt the previous attempt by this user, if any. Only needed
 *         if $attemptnumber > 1 and $quizsbs->attemptonlast is true.
 * @param int $timenow the time the attempt was started at.
 * @param bool $ispreview whether this new attempt is a preview.
 * @param int $userid  the id of the user attempting this quizsbs.
 *
 * @return object the newly created attempt object.
 */
function quizsbs_create_attempt(quizsbs $quizsbsobj, $attemptnumber, $lastattempt, $timenow, $ispreview = false, $userid = null) {
    global $USER;

    if ($userid === null) {
        $userid = $USER->id;
    }

    $quizsbs = $quizsbsobj->get_quizsbs();
    if ($quizsbs->sumgrades < 0.000005 && $quizsbs->grade > 0.000005) {
        throw new moodle_exception('cannotstartgradesmismatch', 'quizsbs',
                new moodle_url('/mod/quizsbs/view.php', array('q' => $quizsbs->id)),
                    array('grade' => quizsbs_format_grade($quizsbs, $quizsbs->grade)));
    }

    if ($attemptnumber == 1 || !$quizsbs->attemptonlast) {
        // We are not building on last attempt so create a new attempt.
        $attempt = new stdClass();
        $attempt->quizsbs = $quizsbs->id;
        $attempt->userid = $userid;
        $attempt->preview = 0;
        $attempt->layout = '';
    } else {
        // Build on last attempt.
        if (empty($lastattempt)) {
            print_error('cannotfindprevattempt', 'quizsbs');
        }
        $attempt = $lastattempt;
    }

    $attempt->attempt = $attemptnumber;
    $attempt->timestart = $timenow;
    $attempt->timefinish = 0;
    $attempt->timemodified = $timenow;
    $attempt->state = quizsbs_attempt::IN_PROGRESS;
    $attempt->currentpage = 0;
    $attempt->sumgrades = null;

    // If this is a preview, mark it as such.
    if ($ispreview) {
        $attempt->preview = 1;
    }

    $timeclose = $quizsbsobj->get_access_manager($timenow)->get_end_time($attempt);
    if ($timeclose === false || $ispreview) {
        $attempt->timecheckstate = null;
    } else {
        $attempt->timecheckstate = $timeclose;
    }

    return $attempt;
}
/**
 * Start a normal, new, quizsbs attempt.
 *
 * @param quizsbs      $quizsbsobj            the quizsbs object to start an attempt for.
 * @param question_usage_by_activity $quba
 * @param object    $attempt
 * @param integer   $attemptnumber      starting from 1
 * @param integer   $timenow            the attempt start time
 * @param array     $questionids        slot number => question id. Used for random questions, to force the choice
 *                                        of a particular actual question. Intended for testing purposes only.
 * @param array     $forcedvariantsbyslot slot number => variant. Used for questions with variants,
 *                                          to force the choice of a particular variant. Intended for testing
 *                                          purposes only.
 * @throws moodle_exception
 * @return object   modified attempt object
 */
function quizsbs_start_new_attempt($quizsbsobj, $quba, $attempt, $attemptnumber, $timenow,
                                $questionids = array(), $forcedvariantsbyslot = array()) {

    // Usages for this user's previous quizsbs attempts.
    $qubaids = new \mod_quizsbs\question\qubaids_for_users_attempts(
            $quizsbsobj->get_quizsbsid(), $attempt->userid);

    // Fully load all the questions in this quizsbs.
    $quizsbsobj->preload_questions();
    $quizsbsobj->load_questions();

    // First load all the non-random questions.
    $randomfound = false;
    $slot = 0;
    $questions = array();
    $maxmark = array();
    $page = array();
    foreach ($quizsbsobj->get_questions() as $questiondata) {
        $slot += 1;
        $maxmark[$slot] = $questiondata->maxmark;
        $page[$slot] = $questiondata->page;
        if ($questiondata->qtype == 'random') {
            $randomfound = true;
            continue;
        }
        if (!$quizsbsobj->get_quizsbs()->shuffleanswers) {
            $questiondata->options->shuffleanswers = false;
        }
        $questions[$slot] = question_bank::make_question($questiondata);
    }

    // Then find a question to go in place of each random question.
    if ($randomfound) {
        $slot = 0;
        $usedquestionids = array();
        foreach ($questions as $question) {
            if (isset($usedquestions[$question->id])) {
                $usedquestionids[$question->id] += 1;
            } else {
                $usedquestionids[$question->id] = 1;
            }
        }
        $randomloader = new \core_question\bank\random_question_loader($qubaids, $usedquestionids);

        foreach ($quizsbsobj->get_questions() as $questiondata) {
            $slot += 1;
            if ($questiondata->qtype != 'random') {
                continue;
            }

            // Deal with fixed random choices for testing.
            if (isset($questionids[$quba->next_slot_number()])) {
                if ($randomloader->is_question_available($questiondata->category,
                        (bool) $questiondata->questiontext, $questionids[$quba->next_slot_number()])) {
                    $questions[$slot] = question_bank::load_question(
                            $questionids[$quba->next_slot_number()], $quizsbsobj->get_quizsbs()->shuffleanswers);
                    continue;
                } else {
                    throw new coding_exception('Forced question id not available.');
                }
            }

            // Normal case, pick one at random.
            $questionid = $randomloader->get_next_question_id($questiondata->category,
                        (bool) $questiondata->questiontext);
            if ($questionid === null) {
                throw new moodle_exception('notenoughrandomquestions', 'quizsbs',
                                           $quizsbsobj->view_url(), $questiondata);
            }

            $questions[$slot] = question_bank::load_question($questionid,
                    $quizsbsobj->get_quizsbs()->shuffleanswers);
        }
    }

    // Finally add them all to the usage.
    ksort($questions);
    foreach ($questions as $slot => $question) {
        $newslot = $quba->add_question($question, $maxmark[$slot]);
        if ($newslot != $slot) {
            throw new coding_exception('Slot numbers have got confused.');
        }
    }

    // Start all the questions.
    $variantstrategy = new core_question\engine\variants\least_used_strategy($quba, $qubaids);

    if (!empty($forcedvariantsbyslot)) {
        $forcedvariantsbyseed = question_variant_forced_choices_selection_strategy::prepare_forced_choices_array(
            $forcedvariantsbyslot, $quba);
        $variantstrategy = new question_variant_forced_choices_selection_strategy(
            $forcedvariantsbyseed, $variantstrategy);
    }

    $quba->start_all_questions($variantstrategy, $timenow);

    // Work out the attempt layout.
    $sections = $quizsbsobj->get_sections();
    foreach ($sections as $i => $section) {
        if (isset($sections[$i + 1])) {
            $sections[$i]->lastslot = $sections[$i + 1]->firstslot - 1;
        } else {
            $sections[$i]->lastslot = count($questions);
        }
    }

    $layout = array();
    foreach ($sections as $section) {
        if ($section->shufflequestions) {
            $questionsinthissection = array();
            for ($slot = $section->firstslot; $slot <= $section->lastslot; $slot += 1) {
                $questionsinthissection[] = $slot;
            }
            shuffle($questionsinthissection);
            $questionsonthispage = 0;
            foreach ($questionsinthissection as $slot) {
                if ($questionsonthispage && $questionsonthispage == $quizsbsobj->get_quizsbs()->questionsperpage) {
                    $layout[] = 0;
                    $questionsonthispage = 0;
                }
                $layout[] = $slot;
                $questionsonthispage += 1;
            }

        } else {
            $currentpage = $page[$section->firstslot];
            for ($slot = $section->firstslot; $slot <= $section->lastslot; $slot += 1) {
                if ($currentpage !== null && $page[$slot] != $currentpage) {
                    $layout[] = 0;
                }
                $layout[] = $slot;
                $currentpage = $page[$slot];
            }
        }

        // Each section ends with a page break.
        $layout[] = 0;
    }
    $attempt->layout = implode(',', $layout);

    return $attempt;
}

/**
 * Start a subsequent new attempt, in each attempt builds on last mode.
 *
 * @param question_usage_by_activity    $quba         this question usage
 * @param object                        $attempt      this attempt
 * @param object                        $lastattempt  last attempt
 * @return object                       modified attempt object
 *
 */
function quizsbs_start_attempt_built_on_last($quba, $attempt, $lastattempt) {
    $oldquba = question_engine::load_questions_usage_by_activity($lastattempt->uniqueid);

    $oldnumberstonew = array();
    foreach ($oldquba->get_attempt_iterator() as $oldslot => $oldqa) {
        $newslot = $quba->add_question($oldqa->get_question(), $oldqa->get_max_mark());

        $quba->start_question_based_on($newslot, $oldqa);

        $oldnumberstonew[$oldslot] = $newslot;
    }

    // Update attempt layout.
    $newlayout = array();
    foreach (explode(',', $lastattempt->layout) as $oldslot) {
        if ($oldslot != 0) {
            $newlayout[] = $oldnumberstonew[$oldslot];
        } else {
            $newlayout[] = 0;
        }
    }
    $attempt->layout = implode(',', $newlayout);
    return $attempt;
}

/**
 * The save started question usage and quizsbs attempt in db and log the started attempt.
 *
 * @param quizsbs                       $quizsbsobj
 * @param question_usage_by_activity $quba
 * @param object                     $attempt
 * @return object                    attempt object with uniqueid and id set.
 */
function quizsbs_attempt_save_started($quizsbsobj, $quba, $attempt) {
    global $DB;
    // Save the attempt in the database.
    question_engine::save_questions_usage_by_activity($quba);
    $attempt->uniqueid = $quba->get_id();
    $attempt->id = $DB->insert_record('quizsbs_attempts', $attempt);

    // Params used by the events below.
    $params = array(
        'objectid' => $attempt->id,
        'relateduserid' => $attempt->userid,
        'courseid' => $quizsbsobj->get_courseid(),
        'context' => $quizsbsobj->get_context()
    );
    // Decide which event we are using.
    if ($attempt->preview) {
        $params['other'] = array(
            'quizsbsid' => $quizsbsobj->get_quizsbsid()
        );
        $event = \mod_quizsbs\event\attempt_preview_started::create($params);
    } else {
        $event = \mod_quizsbs\event\attempt_started::create($params);

    }

    // Trigger the event.
    $event->add_record_snapshot('quizsbs', $quizsbsobj->get_quizsbs());
    $event->add_record_snapshot('quizsbs_attempts', $attempt);
    $event->trigger();

    return $attempt;
}

/**
 * Returns an unfinished attempt (if there is one) for the given
 * user on the given quizsbs. This function does not return preview attempts.
 *
 * @param int $quizsbsid the id of the quizsbs.
 * @param int $userid the id of the user.
 *
 * @return mixed the unfinished attempt if there is one, false if not.
 */
function quizsbs_get_user_attempt_unfinished($quizsbsid, $userid) {
    $attempts = quizsbs_get_user_attempts($quizsbsid, $userid, 'unfinished', true);
    if ($attempts) {
        return array_shift($attempts);
    } else {
        return false;
    }
}

/**
 * Delete a quizsbs attempt.
 * @param mixed $attempt an integer attempt id or an attempt object
 *      (row of the quizsbs_attempts table).
 * @param object $quizsbs the quizsbs object.
 */
function quizsbs_delete_attempt($attempt, $quizsbs) {
    global $DB;
    if (is_numeric($attempt)) {
        if (!$attempt = $DB->get_record('quizsbs_attempts', array('id' => $attempt))) {
            return;
        }
    }

    if ($attempt->quizsbs != $quizsbs->id) {
        debugging("Trying to delete attempt $attempt->id which belongs to quizsbs $attempt->quizsbs " .
                "but was passed quizsbs $quizsbs->id.");
        return;
    }

    if (!isset($quizsbs->cmid)) {
        $cm = get_coursemodule_from_instance('quizsbs', $quizsbs->id, $quizsbs->course);
        $quizsbs->cmid = $cm->id;
    }

    question_engine::delete_questions_usage_by_activity($attempt->uniqueid);
    $DB->delete_records('quizsbs_attempts', array('id' => $attempt->id));

    // Log the deletion of the attempt if not a preview.
    if (!$attempt->preview) {
        $params = array(
            'objectid' => $attempt->id,
            'relateduserid' => $attempt->userid,
            'context' => context_module::instance($quizsbs->cmid),
            'other' => array(
                'quizsbsid' => $quizsbs->id
            )
        );
        $event = \mod_quizsbs\event\attempt_deleted::create($params);
        $event->add_record_snapshot('quizsbs_attempts', $attempt);
        $event->trigger();
    }

    // Search quizsbs_attempts for other instances by this user.
    // If none, then delete record for this quizsbs, this user from quizsbs_grades
    // else recalculate best grade.
    $userid = $attempt->userid;
    if (!$DB->record_exists('quizsbs_attempts', array('userid' => $userid, 'quizsbs' => $quizsbs->id))) {
        $DB->delete_records('quizsbs_grades', array('userid' => $userid, 'quizsbs' => $quizsbs->id));
    } else {
        quizsbs_save_best_grade($quizsbs, $userid);
    }

    quizsbs_update_grades($quizsbs, $userid);
}

/**
 * Delete all the preview attempts at a quizsbs, or possibly all the attempts belonging
 * to one user.
 * @param object $quizsbs the quizsbs object.
 * @param int $userid (optional) if given, only delete the previews belonging to this user.
 */
function quizsbs_delete_previews($quizsbs, $userid = null) {
    global $DB;
    $conditions = array('quizsbs' => $quizsbs->id, 'preview' => 1);
    if (!empty($userid)) {
        $conditions['userid'] = $userid;
    }
    $previewattempts = $DB->get_records('quizsbs_attempts', $conditions);
    foreach ($previewattempts as $attempt) {
        quizsbs_delete_attempt($attempt, $quizsbs);
    }
}

/**
 * @param int $quizsbsid The quizsbs id.
 * @return bool whether this quizsbs has any (non-preview) attempts.
 */
function quizsbs_has_attempts($quizsbsid) {
    global $DB;
    return $DB->record_exists('quizsbs_attempts', array('quizsbs' => $quizsbsid, 'preview' => 0));
}

// Functions to do with quizsbs layout and pages //////////////////////////////////

/**
 * Repaginate the questions in a quizsbs
 * @param int $quizsbsid the id of the quizsbs to repaginate.
 * @param int $slotsperpage number of items to put on each page. 0 means unlimited.
 */
function quizsbs_repaginate_questions($quizsbsid, $slotsperpage) {
    global $DB;
    $trans = $DB->start_delegated_transaction();

    $sections = $DB->get_records('quizsbs_sections', array('quizsbsid' => $quizsbsid), 'firstslot ASC');
    $firstslots = array();
    foreach ($sections as $section) {
        if ((int)$section->firstslot === 1) {
            continue;
        }
        $firstslots[] = $section->firstslot;
    }

    $slots = $DB->get_records('quizsbs_slots', array('quizsbsid' => $quizsbsid),
            'slot');
    $currentpage = 1;
    $slotsonthispage = 0;
    foreach ($slots as $slot) {
        if (($firstslots && in_array($slot->slot, $firstslots)) ||
            ($slotsonthispage && $slotsonthispage == $slotsperpage)) {
            $currentpage += 1;
            $slotsonthispage = 0;
        }
        if ($slot->page != $currentpage) {
            $DB->set_field('quizsbs_slots', 'page', $currentpage, array('id' => $slot->id));
        }
        $slotsonthispage += 1;
    }

    $trans->allow_commit();
}

// Functions to do with quizsbs grades ////////////////////////////////////////////

/**
 * Convert the raw grade stored in $attempt into a grade out of the maximum
 * grade for this quizsbs.
 *
 * @param float $rawgrade the unadjusted grade, fof example $attempt->sumgrades
 * @param object $quizsbs the quizsbs object. Only the fields grade, sumgrades and decimalpoints are used.
 * @param bool|string $format whether to format the results for display
 *      or 'question' to format a question grade (different number of decimal places.
 * @return float|string the rescaled grade, or null/the lang string 'notyetgraded'
 *      if the $grade is null.
 */
function quizsbs_rescale_grade($rawgrade, $quizsbs, $format = true) {
    if (is_null($rawgrade)) {
        $grade = null;
    } else if ($quizsbs->sumgrades >= 0.000005) {
        $grade = $rawgrade * $quizsbs->grade / $quizsbs->sumgrades;
    } else {
        $grade = 0;
    }
    if ($format === 'question') {
        $grade = quizsbs_format_question_grade($quizsbs, $grade);
    } else if ($format) {
        $grade = quizsbs_format_grade($quizsbs, $grade);
    }
    return $grade;
}

/**
 * Get the feedback object for this grade on this quizsbs.
 *
 * @param float $grade a grade on this quizsbs.
 * @param object $quizsbs the quizsbs settings.
 * @return false|stdClass the record object or false if there is not feedback for the given grade
 * @since  Moodle 3.1
 */
function quizsbs_feedback_record_for_grade($grade, $quizsbs) {
    global $DB;

    // With CBM etc, it is possible to get -ve grades, which would then not match
    // any feedback. Therefore, we replace -ve grades with 0.
    $grade = max($grade, 0);

    $feedback = $DB->get_record_select('quizsbs_feedback',
            'quizsbsid = ? AND mingrade <= ? AND ? < maxgrade', array($quizsbs->id, $grade, $grade));

    return $feedback;
}

/**
 * Get the feedback text that should be show to a student who
 * got this grade on this quizsbs. The feedback is processed ready for diplay.
 *
 * @param float $grade a grade on this quizsbs.
 * @param object $quizsbs the quizsbs settings.
 * @param object $context the quizsbs context.
 * @return string the comment that corresponds to this grade (empty string if there is not one.
 */
function quizsbs_feedback_for_grade($grade, $quizsbs, $context) {

    if (is_null($grade)) {
        return '';
    }

    $feedback = quizsbs_feedback_record_for_grade($grade, $quizsbs);

    if (empty($feedback->feedbacktext)) {
        return '';
    }

    // Clean the text, ready for display.
    $formatoptions = new stdClass();
    $formatoptions->noclean = true;
    $feedbacktext = file_rewrite_pluginfile_urls($feedback->feedbacktext, 'pluginfile.php',
            $context->id, 'mod_quizsbs', 'feedback', $feedback->id);
    $feedbacktext = format_text($feedbacktext, $feedback->feedbacktextformat, $formatoptions);

    return $feedbacktext;
}

/**
 * @param object $quizsbs the quizsbs database row.
 * @return bool Whether this quizsbs has any non-blank feedback text.
 */
function quizsbs_has_feedback($quizsbs) {
    global $DB;
    static $cache = array();
    if (!array_key_exists($quizsbs->id, $cache)) {
        $cache[$quizsbs->id] = quizsbs_has_grades($quizsbs) &&
                $DB->record_exists_select('quizsbs_feedback', "quizsbsid = ? AND " .
                    $DB->sql_isnotempty('quizsbs_feedback', 'feedbacktext', false, true),
                array($quizsbs->id));
    }
    return $cache[$quizsbs->id];
}

/**
 * Update the sumgrades field of the quizsbs. This needs to be called whenever
 * the grading structure of the quizsbs is changed. For example if a question is
 * added or removed, or a question weight is changed.
 *
 * You should call {@link quizsbs_delete_previews()} before you call this function.
 *
 * @param object $quizsbs a quizsbs.
 */
function quizsbs_update_sumgrades($quizsbs) {
    global $DB;

    $sql = 'UPDATE {quizsbs}
            SET sumgrades = COALESCE((
                SELECT SUM(maxmark)
                FROM {quizsbs_slots}
                WHERE quizsbsid = {quizsbs}.id
            ), 0)
            WHERE id = ?';
    $DB->execute($sql, array($quizsbs->id));
    $quizsbs->sumgrades = $DB->get_field('quizsbs', 'sumgrades', array('id' => $quizsbs->id));

    if ($quizsbs->sumgrades < 0.000005 && quizsbs_has_attempts($quizsbs->id)) {
        // If the quizsbs has been attempted, and the sumgrades has been
        // set to 0, then we must also set the maximum possible grade to 0, or
        // we will get a divide by zero error.
        quizsbs_set_grade(0, $quizsbs);
    }
}

/**
 * Update the sumgrades field of the attempts at a quizsbs.
 *
 * @param object $quizsbs a quizsbs.
 */
function quizsbs_update_all_attempt_sumgrades($quizsbs) {
    global $DB;
    $dm = new question_engine_data_mapper();
    $timenow = time();

    $sql = "UPDATE {quizsbs_attempts}
            SET
                timemodified = :timenow,
                sumgrades = (
                    {$dm->sum_usage_marks_subquery('uniqueid')}
                )
            WHERE quizsbs = :quizsbsid AND state = :finishedstate";
    $DB->execute($sql, array('timenow' => $timenow, 'quizsbsid' => $quizsbs->id,
            'finishedstate' => quizsbs_attempt::FINISHED));
}

/**
 * The quizsbs grade is the maximum that student's results are marked out of. When it
 * changes, the corresponding data in quizsbs_grades and quizsbs_feedback needs to be
 * rescaled. After calling this function, you probably need to call
 * quizsbs_update_all_attempt_sumgrades, quizsbs_update_all_final_grades and
 * quizsbs_update_grades.
 *
 * @param float $newgrade the new maximum grade for the quizsbs.
 * @param object $quizsbs the quizsbs we are updating. Passed by reference so its
 *      grade field can be updated too.
 * @return bool indicating success or failure.
 */
function quizsbs_set_grade($newgrade, $quizsbs) {
    global $DB;
    // This is potentially expensive, so only do it if necessary.
    if (abs($quizsbs->grade - $newgrade) < 1e-7) {
        // Nothing to do.
        return true;
    }

    $oldgrade = $quizsbs->grade;
    $quizsbs->grade = $newgrade;

    // Use a transaction, so that on those databases that support it, this is safer.
    $transaction = $DB->start_delegated_transaction();

    // Update the quizsbs table.
    $DB->set_field('quizsbs', 'grade', $newgrade, array('id' => $quizsbs->instance));

    if ($oldgrade < 1) {
        // If the old grade was zero, we cannot rescale, we have to recompute.
        // We also recompute if the old grade was too small to avoid underflow problems.
        quizsbs_update_all_final_grades($quizsbs);

    } else {
        // We can rescale the grades efficiently.
        $timemodified = time();
        $DB->execute("
                UPDATE {quizsbs_grades}
                SET grade = ? * grade, timemodified = ?
                WHERE quizsbs = ?
        ", array($newgrade/$oldgrade, $timemodified, $quizsbs->id));
    }

    if ($oldgrade > 1e-7) {
        // Update the quizsbs_feedback table.
        $factor = $newgrade/$oldgrade;
        $DB->execute("
                UPDATE {quizsbs_feedback}
                SET mingrade = ? * mingrade, maxgrade = ? * maxgrade
                WHERE quizsbsid = ?
        ", array($factor, $factor, $quizsbs->id));
    }

    // Update grade item and send all grades to gradebook.
    quizsbs_grade_item_update($quizsbs);
    quizsbs_update_grades($quizsbs);

    $transaction->allow_commit();
    return true;
}

/**
 * Save the overall grade for a user at a quizsbs in the quizsbs_grades table
 *
 * @param object $quizsbs The quizsbs for which the best grade is to be calculated and then saved.
 * @param int $userid The userid to calculate the grade for. Defaults to the current user.
 * @param array $attempts The attempts of this user. Useful if you are
 * looping through many users. Attempts can be fetched in one master query to
 * avoid repeated querying.
 * @return bool Indicates success or failure.
 */
function quizsbs_save_best_grade($quizsbs, $userid = null, $attempts = array()) {
    global $DB, $OUTPUT, $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    if (!$attempts) {
        // Get all the attempts made by the user.
        $attempts = quizsbs_get_user_attempts($quizsbs->id, $userid);
    }

    // Calculate the best grade.
    $bestgrade = quizsbs_calculate_best_grade($quizsbs, $attempts);
    $bestgrade = quizsbs_rescale_grade($bestgrade, $quizsbs, false);

    // Save the best grade in the database.
    if (is_null($bestgrade)) {
        $DB->delete_records('quizsbs_grades', array('quizsbs' => $quizsbs->id, 'userid' => $userid));

    } else if ($grade = $DB->get_record('quizsbs_grades',
            array('quizsbs' => $quizsbs->id, 'userid' => $userid))) {
        $grade->grade = $bestgrade;
        $grade->timemodified = time();
        $DB->update_record('quizsbs_grades', $grade);

    } else {
        $grade = new stdClass();
        $grade->quizsbs = $quizsbs->id;
        $grade->userid = $userid;
        $grade->grade = $bestgrade;
        $grade->timemodified = time();
        $DB->insert_record('quizsbs_grades', $grade);
    }

    quizsbs_update_grades($quizsbs, $userid);
}

/**
 * Calculate the overall grade for a quizsbs given a number of attempts by a particular user.
 *
 * @param object $quizsbs    the quizsbs settings object.
 * @param array $attempts an array of all the user's attempts at this quizsbs in order.
 * @return float          the overall grade
 */
function quizsbs_calculate_best_grade($quizsbs, $attempts) {

    switch ($quizsbs->grademethod) {

        case quizsbs_ATTEMPTFIRST:
            $firstattempt = reset($attempts);
            return $firstattempt->sumgrades;

        case quizsbs_ATTEMPTLAST:
            $lastattempt = end($attempts);
            return $lastattempt->sumgrades;

        case quizsbs_GRADEAVERAGE:
            $sum = 0;
            $count = 0;
            foreach ($attempts as $attempt) {
                if (!is_null($attempt->sumgrades)) {
                    $sum += $attempt->sumgrades;
                    $count++;
                }
            }
            if ($count == 0) {
                return null;
            }
            return $sum / $count;

        case quizsbs_GRADEHIGHEST:
        default:
            $max = null;
            foreach ($attempts as $attempt) {
                if ($attempt->sumgrades > $max) {
                    $max = $attempt->sumgrades;
                }
            }
            return $max;
    }
}

/**
 * Update the final grade at this quizsbs for all students.
 *
 * This function is equivalent to calling quizsbs_save_best_grade for all
 * users, but much more efficient.
 *
 * @param object $quizsbs the quizsbs settings.
 */
function quizsbs_update_all_final_grades($quizsbs) {
    global $DB;

    if (!$quizsbs->sumgrades) {
        return;
    }

    $param = array('iquizsbsid' => $quizsbs->id, 'istatefinished' => quizsbs_attempt::FINISHED);
    $firstlastattemptjoin = "JOIN (
            SELECT
                iquizsbsa.userid,
                MIN(attempt) AS firstattempt,
                MAX(attempt) AS lastattempt

            FROM {quizsbs_attempts} iquizsbsa

            WHERE
                iquizsbsa.state = :istatefinished AND
                iquizsbsa.preview = 0 AND
                iquizsbsa.quizsbs = :iquizsbsid

            GROUP BY iquizsbsa.userid
        ) first_last_attempts ON first_last_attempts.userid = quizsbsa.userid";

    switch ($quizsbs->grademethod) {
        case quizsbs_ATTEMPTFIRST:
            // Because of the where clause, there will only be one row, but we
            // must still use an aggregate function.
            $select = 'MAX(quizsbsa.sumgrades)';
            $join = $firstlastattemptjoin;
            $where = 'quizsbsa.attempt = first_last_attempts.firstattempt AND';
            break;

        case quizsbs_ATTEMPTLAST:
            // Because of the where clause, there will only be one row, but we
            // must still use an aggregate function.
            $select = 'MAX(quizsbsa.sumgrades)';
            $join = $firstlastattemptjoin;
            $where = 'quizsbsa.attempt = first_last_attempts.lastattempt AND';
            break;

        case quizsbs_GRADEAVERAGE:
            $select = 'AVG(quizsbsa.sumgrades)';
            $join = '';
            $where = '';
            break;

        default:
        case quizsbs_GRADEHIGHEST:
            $select = 'MAX(quizsbsa.sumgrades)';
            $join = '';
            $where = '';
            break;
    }

    if ($quizsbs->sumgrades >= 0.000005) {
        $finalgrade = $select . ' * ' . ($quizsbs->grade / $quizsbs->sumgrades);
    } else {
        $finalgrade = '0';
    }
    $param['quizsbsid'] = $quizsbs->id;
    $param['quizsbsid2'] = $quizsbs->id;
    $param['quizsbsid3'] = $quizsbs->id;
    $param['quizsbsid4'] = $quizsbs->id;
    $param['statefinished'] = quizsbs_attempt::FINISHED;
    $param['statefinished2'] = quizsbs_attempt::FINISHED;
    $finalgradesubquery = "
            SELECT quizsbsa.userid, $finalgrade AS newgrade
            FROM {quizsbs_attempts} quizsbsa
            $join
            WHERE
                $where
                quizsbsa.state = :statefinished AND
                quizsbsa.preview = 0 AND
                quizsbsa.quizsbs = :quizsbsid3
            GROUP BY quizsbsa.userid";

    $changedgrades = $DB->get_records_sql("
            SELECT users.userid, qg.id, qg.grade, newgrades.newgrade

            FROM (
                SELECT userid
                FROM {quizsbs_grades} qg
                WHERE quizsbs = :quizsbsid
            UNION
                SELECT DISTINCT userid
                FROM {quizsbs_attempts} quizsbsa2
                WHERE
                    quizsbsa2.state = :statefinished2 AND
                    quizsbsa2.preview = 0 AND
                    quizsbsa2.quizsbs = :quizsbsid2
            ) users

            LEFT JOIN {quizsbs_grades} qg ON qg.userid = users.userid AND qg.quizsbs = :quizsbsid4

            LEFT JOIN (
                $finalgradesubquery
            ) newgrades ON newgrades.userid = users.userid

            WHERE
                ABS(newgrades.newgrade - qg.grade) > 0.000005 OR
                ((newgrades.newgrade IS NULL OR qg.grade IS NULL) AND NOT
                          (newgrades.newgrade IS NULL AND qg.grade IS NULL))",
                // The mess on the previous line is detecting where the value is
                // NULL in one column, and NOT NULL in the other, but SQL does
                // not have an XOR operator, and MS SQL server can't cope with
                // (newgrades.newgrade IS NULL) <> (qg.grade IS NULL).
            $param);

    $timenow = time();
    $todelete = array();
    foreach ($changedgrades as $changedgrade) {

        if (is_null($changedgrade->newgrade)) {
            $todelete[] = $changedgrade->userid;

        } else if (is_null($changedgrade->grade)) {
            $toinsert = new stdClass();
            $toinsert->quizsbs = $quizsbs->id;
            $toinsert->userid = $changedgrade->userid;
            $toinsert->timemodified = $timenow;
            $toinsert->grade = $changedgrade->newgrade;
            $DB->insert_record('quizsbs_grades', $toinsert);

        } else {
            $toupdate = new stdClass();
            $toupdate->id = $changedgrade->id;
            $toupdate->grade = $changedgrade->newgrade;
            $toupdate->timemodified = $timenow;
            $DB->update_record('quizsbs_grades', $toupdate);
        }
    }

    if (!empty($todelete)) {
        list($test, $params) = $DB->get_in_or_equal($todelete);
        $DB->delete_records_select('quizsbs_grades', 'quizsbs = ? AND userid ' . $test,
                array_merge(array($quizsbs->id), $params));
    }
}

/**
 * Efficiently update check state time on all open attempts
 *
 * @param array $conditions optional restrictions on which attempts to update
 *                    Allowed conditions:
 *                      courseid => (array|int) attempts in given course(s)
 *                      userid   => (array|int) attempts for given user(s)
 *                      quizsbsid   => (array|int) attempts in given quizsbs(s)
 *                      groupid  => (array|int) quizsbszes with some override for given group(s)
 *
 */
function quizsbs_update_open_attempts(array $conditions) {
    global $DB;

    foreach ($conditions as &$value) {
        if (!is_array($value)) {
            $value = array($value);
        }
    }

    $params = array();
    $wheres = array("quizsbsa.state IN ('inprogress', 'overdue')");
    $iwheres = array("iquizsbsa.state IN ('inprogress', 'overdue')");

    if (isset($conditions['courseid'])) {
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['courseid'], SQL_PARAMS_NAMED, 'cid');
        $params = array_merge($params, $inparams);
        $wheres[] = "quizsbsa.quizsbs IN (SELECT q.id FROM {quizsbs} q WHERE q.course $incond)";
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['courseid'], SQL_PARAMS_NAMED, 'icid');
        $params = array_merge($params, $inparams);
        $iwheres[] = "iquizsbsa.quizsbs IN (SELECT q.id FROM {quizsbs} q WHERE q.course $incond)";
    }

    if (isset($conditions['userid'])) {
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['userid'], SQL_PARAMS_NAMED, 'uid');
        $params = array_merge($params, $inparams);
        $wheres[] = "quizsbsa.userid $incond";
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['userid'], SQL_PARAMS_NAMED, 'iuid');
        $params = array_merge($params, $inparams);
        $iwheres[] = "iquizsbsa.userid $incond";
    }

    if (isset($conditions['quizsbsid'])) {
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['quizsbsid'], SQL_PARAMS_NAMED, 'qid');
        $params = array_merge($params, $inparams);
        $wheres[] = "quizsbsa.quizsbs $incond";
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['quizsbsid'], SQL_PARAMS_NAMED, 'iqid');
        $params = array_merge($params, $inparams);
        $iwheres[] = "iquizsbsa.quizsbs $incond";
    }

    if (isset($conditions['groupid'])) {
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['groupid'], SQL_PARAMS_NAMED, 'gid');
        $params = array_merge($params, $inparams);
        $wheres[] = "quizsbsa.quizsbs IN (SELECT qo.quizsbs FROM {quizsbs_overrides} qo WHERE qo.groupid $incond)";
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['groupid'], SQL_PARAMS_NAMED, 'igid');
        $params = array_merge($params, $inparams);
        $iwheres[] = "iquizsbsa.quizsbs IN (SELECT qo.quizsbs FROM {quizsbs_overrides} qo WHERE qo.groupid $incond)";
    }

    // SQL to compute timeclose and timelimit for each attempt:
    $quizsbsausersql = quizsbs_get_attempt_usertime_sql(
            implode("\n                AND ", $iwheres));

    // SQL to compute the new timecheckstate
    $timecheckstatesql = "
          CASE WHEN quizsbsauser.usertimelimit = 0 AND quizsbsauser.usertimeclose = 0 THEN NULL
               WHEN quizsbsauser.usertimelimit = 0 THEN quizsbsauser.usertimeclose
               WHEN quizsbsauser.usertimeclose = 0 THEN quizsbsa.timestart + quizsbsauser.usertimelimit
               WHEN quizsbsa.timestart + quizsbsauser.usertimelimit < quizsbsauser.usertimeclose THEN quizsbsa.timestart + quizsbsauser.usertimelimit
               ELSE quizsbsauser.usertimeclose END +
          CASE WHEN quizsbsa.state = 'overdue' THEN quizsbs.graceperiod ELSE 0 END";

    // SQL to select which attempts to process
    $attemptselect = implode("\n                         AND ", $wheres);

   /*
    * Each database handles updates with inner joins differently:
    *  - mysql does not allow a FROM clause
    *  - postgres and mssql allow FROM but handle table aliases differently
    *  - oracle requires a subquery
    *
    * Different code for each database.
    */

    $dbfamily = $DB->get_dbfamily();
    if ($dbfamily == 'mysql') {
        $updatesql = "UPDATE {quizsbs_attempts} quizsbsa
                        JOIN {quizsbs} quizsbs ON quizsbs.id = quizsbsa.quizsbs
                        JOIN ( $quizsbsausersql ) quizsbsauser ON quizsbsauser.id = quizsbsa.id
                         SET quizsbsa.timecheckstate = $timecheckstatesql
                       WHERE $attemptselect";
    } else if ($dbfamily == 'postgres') {
        $updatesql = "UPDATE {quizsbs_attempts} quizsbsa
                         SET timecheckstate = $timecheckstatesql
                        FROM {quizsbs} quizsbs, ( $quizsbsausersql ) quizsbsauser
                       WHERE quizsbs.id = quizsbsa.quizsbs
                         AND quizsbsauser.id = quizsbsa.id
                         AND $attemptselect";
    } else if ($dbfamily == 'mssql') {
        $updatesql = "UPDATE quizsbsa
                         SET timecheckstate = $timecheckstatesql
                        FROM {quizsbs_attempts} quizsbsa
                        JOIN {quizsbs} quizsbs ON quizsbs.id = quizsbsa.quizsbs
                        JOIN ( $quizsbsausersql ) quizsbsauser ON quizsbsauser.id = quizsbsa.id
                       WHERE $attemptselect";
    } else {
        // oracle, sqlite and others
        $updatesql = "UPDATE {quizsbs_attempts} quizsbsa
                         SET timecheckstate = (
                           SELECT $timecheckstatesql
                             FROM {quizsbs} quizsbs, ( $quizsbsausersql ) quizsbsauser
                            WHERE quizsbs.id = quizsbsa.quizsbs
                              AND quizsbsauser.id = quizsbsa.id
                         )
                         WHERE $attemptselect";
    }

    $DB->execute($updatesql, $params);
}

/**
 * Returns SQL to compute timeclose and timelimit for every attempt, taking into account user and group overrides.
 *
 * @param string $redundantwhereclauses extra where clauses to add to the subquery
 *      for performance. These can use the table alias iquizsbsa for the quizsbs attempts table.
 * @return string SQL select with columns attempt.id, usertimeclose, usertimelimit.
 */
function quizsbs_get_attempt_usertime_sql($redundantwhereclauses = '') {
    if ($redundantwhereclauses) {
        $redundantwhereclauses = 'WHERE ' . $redundantwhereclauses;
    }
    // The multiple qgo JOINS are necessary because we want timeclose/timelimit = 0 (unlimited) to supercede
    // any other group override
    $quizsbsausersql = "
          SELECT iquizsbsa.id,
           COALESCE(MAX(quo.timeclose), MAX(qgo1.timeclose), MAX(qgo2.timeclose), iquizsbs.timeclose) AS usertimeclose,
           COALESCE(MAX(quo.timelimit), MAX(qgo3.timelimit), MAX(qgo4.timelimit), iquizsbs.timelimit) AS usertimelimit

           FROM {quizsbs_attempts} iquizsbsa
           JOIN {quizsbs} iquizsbs ON iquizsbs.id = iquizsbsa.quizsbs
      LEFT JOIN {quizsbs_overrides} quo ON quo.quizsbs = iquizsbsa.quizsbs AND quo.userid = iquizsbsa.userid
      LEFT JOIN {groups_members} gm ON gm.userid = iquizsbsa.userid
      LEFT JOIN {quizsbs_overrides} qgo1 ON qgo1.quizsbs = iquizsbsa.quizsbs AND qgo1.groupid = gm.groupid AND qgo1.timeclose = 0
      LEFT JOIN {quizsbs_overrides} qgo2 ON qgo2.quizsbs = iquizsbsa.quizsbs AND qgo2.groupid = gm.groupid AND qgo2.timeclose > 0
      LEFT JOIN {quizsbs_overrides} qgo3 ON qgo3.quizsbs = iquizsbsa.quizsbs AND qgo3.groupid = gm.groupid AND qgo3.timelimit = 0
      LEFT JOIN {quizsbs_overrides} qgo4 ON qgo4.quizsbs = iquizsbsa.quizsbs AND qgo4.groupid = gm.groupid AND qgo4.timelimit > 0
          $redundantwhereclauses
       GROUP BY iquizsbsa.id, iquizsbs.id, iquizsbs.timeclose, iquizsbs.timelimit";
    return $quizsbsausersql;
}

/**
 * Return the attempt with the best grade for a quizsbs
 *
 * Which attempt is the best depends on $quizsbs->grademethod. If the grade
 * method is GRADEAVERAGE then this function simply returns the last attempt.
 * @return object         The attempt with the best grade
 * @param object $quizsbs    The quizsbs for which the best grade is to be calculated
 * @param array $attempts An array of all the attempts of the user at the quizsbs
 */
function quizsbs_calculate_best_attempt($quizsbs, $attempts) {

    switch ($quizsbs->grademethod) {

        case quizsbs_ATTEMPTFIRST:
            foreach ($attempts as $attempt) {
                return $attempt;
            }
            break;

        case quizsbs_GRADEAVERAGE: // We need to do something with it.
        case quizsbs_ATTEMPTLAST:
            foreach ($attempts as $attempt) {
                $final = $attempt;
            }
            return $final;

        default:
        case quizsbs_GRADEHIGHEST:
            $max = -1;
            foreach ($attempts as $attempt) {
                if ($attempt->sumgrades > $max) {
                    $max = $attempt->sumgrades;
                    $maxattempt = $attempt;
                }
            }
            return $maxattempt;
    }
}

/**
 * @return array int => lang string the options for calculating the quizsbs grade
 *      from the individual attempt grades.
 */
function quizsbs_get_grading_options() {
    return array(
        quizsbs_GRADEHIGHEST => get_string('gradehighest', 'quizsbs'),
        quizsbs_GRADEAVERAGE => get_string('gradeaverage', 'quizsbs'),
        quizsbs_ATTEMPTFIRST => get_string('attemptfirst', 'quizsbs'),
        quizsbs_ATTEMPTLAST  => get_string('attemptlast', 'quizsbs')
    );
}

/**
 * @param int $option one of the values quizsbs_GRADEHIGHEST, quizsbs_GRADEAVERAGE,
 *      quizsbs_ATTEMPTFIRST or quizsbs_ATTEMPTLAST.
 * @return the lang string for that option.
 */
function quizsbs_get_grading_option_name($option) {
    $strings = quizsbs_get_grading_options();
    return $strings[$option];
}

/**
 * @return array string => lang string the options for handling overdue quizsbs
 *      attempts.
 */
function quizsbs_get_overdue_handling_options() {
    return array(
        'autosubmit'  => get_string('overduehandlingautosubmit', 'quizsbs'),
        'graceperiod' => get_string('overduehandlinggraceperiod', 'quizsbs'),
        'autoabandon' => get_string('overduehandlingautoabandon', 'quizsbs'),
    );
}

/**
 * Get the choices for what size user picture to show.
 * @return array string => lang string the options for whether to display the user's picture.
 */
function quizsbs_get_user_image_options() {
    return array(
        quizsbs_SHOWIMAGE_NONE  => get_string('shownoimage', 'quizsbs'),
        quizsbs_SHOWIMAGE_SMALL => get_string('showsmallimage', 'quizsbs'),
        quizsbs_SHOWIMAGE_LARGE => get_string('showlargeimage', 'quizsbs'),
    );
}

/**
 * Get the choices to offer for the 'Questions per page' option.
 * @return array int => string.
 */
function quizsbs_questions_per_page_options() {
    $pageoptions = array();
    $pageoptions[0] = get_string('neverallononepage', 'quizsbs');
    $pageoptions[1] = get_string('everyquestion', 'quizsbs');
    for ($i = 2; $i <= quizsbs_MAX_QPP_OPTION; ++$i) {
        $pageoptions[$i] = get_string('everynquestions', 'quizsbs', $i);
    }
    return $pageoptions;
}

/**
 * Get the human-readable name for a quizsbs attempt state.
 * @param string $state one of the state constants like {@link quizsbs_attempt::IN_PROGRESS}.
 * @return string The lang string to describe that state.
 */
function quizsbs_attempt_state_name($state) {
    switch ($state) {
        case quizsbs_attempt::IN_PROGRESS:
            return get_string('stateinprogress', 'quizsbs');
        case quizsbs_attempt::OVERDUE:
            return get_string('stateoverdue', 'quizsbs');
        case quizsbs_attempt::FINISHED:
            return get_string('statefinished', 'quizsbs');
        case quizsbs_attempt::ABANDONED:
            return get_string('stateabandoned', 'quizsbs');
        default:
            throw new coding_exception('Unknown quizsbs attempt state.');
    }
}

// Other quizsbs functions ////////////////////////////////////////////////////////

/**
 * @param object $quizsbs the quizsbs.
 * @param int $cmid the course_module object for this quizsbs.
 * @param object $question the question.
 * @param string $returnurl url to return to after action is done.
 * @param int $variant which question variant to preview (optional).
 * @return string html for a number of icons linked to action pages for a
 * question - preview and edit / view icons depending on user capabilities.
 */
function quizsbs_question_action_icons($quizsbs, $cmid, $question, $returnurl, $variant = null) {
    $html = quizsbs_question_preview_button($quizsbs, $question, false, $variant) . ' ' .
            quizsbs_question_edit_button($cmid, $question, $returnurl);
    return $html;
}

/**
 * @param int $cmid the course_module.id for this quizsbs.
 * @param object $question the question.
 * @param string $returnurl url to return to after action is done.
 * @param string $contentbeforeicon some HTML content to be added inside the link, before the icon.
 * @return the HTML for an edit icon, view icon, or nothing for a question
 *      (depending on permissions).
 */
function quizsbs_question_edit_button($cmid, $question, $returnurl, $contentaftericon = '') {
    global $CFG, $OUTPUT;

    // Minor efficiency saving. Only get strings once, even if there are a lot of icons on one page.
    static $stredit = null;
    static $strview = null;
    if ($stredit === null) {
        $stredit = get_string('edit');
        $strview = get_string('view');
    }

    // What sort of icon should we show?
    $action = '';
    if (!empty($question->id) &&
            (question_has_capability_on($question, 'edit', $question->category) ||
                    question_has_capability_on($question, 'move', $question->category))) {
        $action = $stredit;
        $icon = '/t/edit';
    } else if (!empty($question->id) &&
            question_has_capability_on($question, 'view', $question->category)) {
        $action = $strview;
        $icon = '/i/info';
    }

    // Build the icon.
    if ($action) {
        if ($returnurl instanceof moodle_url) {
            $returnurl = $returnurl->out_as_local_url(false);
        }
        $questionparams = array('returnurl' => $returnurl, 'cmid' => $cmid, 'id' => $question->id);
        $questionurl = new moodle_url("$CFG->wwwroot/question/question.php", $questionparams);
        return '<a title="' . $action . '" href="' . $questionurl->out() . '" class="questioneditbutton"><img src="' .
                $OUTPUT->pix_url($icon) . '" alt="' . $action . '" />' . $contentaftericon .
                '</a>';
    } else if ($contentaftericon) {
        return '<span class="questioneditbutton">' . $contentaftericon . '</span>';
    } else {
        return '';
    }
}

/**
 * @param object $quizsbs the quizsbs settings
 * @param object $question the question
 * @param int $variant which question variant to preview (optional).
 * @return moodle_url to preview this question with the options from this quizsbs.
 */
function quizsbs_question_preview_url($quizsbs, $question, $variant = null) {
    // Get the appropriate display options.
    $displayoptions = mod_quizsbs_display_options::make_from_quizsbs($quizsbs,
            mod_quizsbs_display_options::DURING);

    $maxmark = null;
    if (isset($question->maxmark)) {
        $maxmark = $question->maxmark;
    }

    // Work out the correcte preview URL.
    return question_preview_url($question->id, $quizsbs->preferredbehaviour,
            $maxmark, $displayoptions, $variant);
}

/**
 * @param object $quizsbs the quizsbs settings
 * @param object $question the question
 * @param bool $label if true, show the preview question label after the icon
 * @param int $variant which question variant to preview (optional).
 * @return the HTML for a preview question icon.
 */
function quizsbs_question_preview_button($quizsbs, $question, $label = false, $variant = null) {
    global $PAGE;
    if (!question_has_capability_on($question, 'use', $question->category)) {
        return '';
    }

    return $PAGE->get_renderer('mod_quizsbs', 'edit')->question_preview_icon($quizsbs, $question, $label, $variant);
}

/**
 * @param object $attempt the attempt.
 * @param object $context the quizsbs context.
 * @return int whether flags should be shown/editable to the current user for this attempt.
 */
function quizsbs_get_flag_option($attempt, $context) {
    global $USER;
    if (!has_capability('moodle/question:flag', $context)) {
        return question_display_options::HIDDEN;
    } else if ($attempt->userid == $USER->id) {
        return question_display_options::EDITABLE;
    } else {
        return question_display_options::VISIBLE;
    }
}

/**
 * Work out what state this quizsbs attempt is in - in the sense used by
 * quizsbs_get_review_options, not in the sense of $attempt->state.
 * @param object $quizsbs the quizsbs settings
 * @param object $attempt the quizsbs_attempt database row.
 * @return int one of the mod_quizsbs_display_options::DURING,
 *      IMMEDIATELY_AFTER, LATER_WHILE_OPEN or AFTER_CLOSE constants.
 */
function quizsbs_attempt_state($quizsbs, $attempt) {
    if ($attempt->state == quizsbs_attempt::IN_PROGRESS) {
        return mod_quizsbs_display_options::DURING;
    } else if (time() < $attempt->timefinish + 120) {
        return mod_quizsbs_display_options::IMMEDIATELY_AFTER;
    } else if (!$quizsbs->timeclose || time() < $quizsbs->timeclose) {
        return mod_quizsbs_display_options::LATER_WHILE_OPEN;
    } else {
        return mod_quizsbs_display_options::AFTER_CLOSE;
    }
}

/**
 * The the appropraite mod_quizsbs_display_options object for this attempt at this
 * quizsbs right now.
 *
 * @param object $quizsbs the quizsbs instance.
 * @param object $attempt the attempt in question.
 * @param $context the quizsbs context.
 *
 * @return mod_quizsbs_display_options
 */
function quizsbs_get_review_options($quizsbs, $attempt, $context) {
    $options = mod_quizsbs_display_options::make_from_quizsbs($quizsbs, quizsbs_attempt_state($quizsbs, $attempt));

    $options->readonly = true;
    $options->flags = quizsbs_get_flag_option($attempt, $context);
    if (!empty($attempt->id)) {
        $options->questionreviewlink = new moodle_url('/mod/quizsbs/reviewquestion.php',
                array('attempt' => $attempt->id));
    }

    // Show a link to the comment box only for closed attempts.
    if (!empty($attempt->id) && $attempt->state == quizsbs_attempt::FINISHED && !$attempt->preview &&
            !is_null($context) && has_capability('mod/quizsbs:grade', $context)) {
        $options->manualcomment = question_display_options::VISIBLE;
        $options->manualcommentlink = new moodle_url('/mod/quizsbs/comment.php',
                array('attempt' => $attempt->id));
    }

    if (!is_null($context) && !$attempt->preview &&
            has_capability('mod/quizsbs:viewreports', $context) &&
            has_capability('moodle/grade:viewhidden', $context)) {
        // People who can see reports and hidden grades should be shown everything,
        // except during preview when teachers want to see what students see.
        $options->attempt = question_display_options::VISIBLE;
        $options->correctness = question_display_options::VISIBLE;
        $options->marks = question_display_options::MARK_AND_MAX;
        $options->feedback = question_display_options::VISIBLE;
        $options->numpartscorrect = question_display_options::VISIBLE;
        $options->manualcomment = question_display_options::VISIBLE;
        $options->generalfeedback = question_display_options::VISIBLE;
        $options->rightanswer = question_display_options::VISIBLE;
        $options->overallfeedback = question_display_options::VISIBLE;
        $options->history = question_display_options::VISIBLE;

    }

    return $options;
}

/**
 * Combines the review options from a number of different quizsbs attempts.
 * Returns an array of two ojects, so the suggested way of calling this
 * funciton is:
 * list($someoptions, $alloptions) = quizsbs_get_combined_reviewoptions(...)
 *
 * @param object $quizsbs the quizsbs instance.
 * @param array $attempts an array of attempt objects.
 *
 * @return array of two options objects, one showing which options are true for
 *          at least one of the attempts, the other showing which options are true
 *          for all attempts.
 */
function quizsbs_get_combined_reviewoptions($quizsbs, $attempts) {
    $fields = array('feedback', 'generalfeedback', 'rightanswer', 'overallfeedback');
    $someoptions = new stdClass();
    $alloptions = new stdClass();
    foreach ($fields as $field) {
        $someoptions->$field = false;
        $alloptions->$field = true;
    }
    $someoptions->marks = question_display_options::HIDDEN;
    $alloptions->marks = question_display_options::MARK_AND_MAX;

    // This shouldn't happen, but we need to prevent reveal information.
    if (empty($attempts)) {
        return array($someoptions, $someoptions);
    }

    foreach ($attempts as $attempt) {
        $attemptoptions = mod_quizsbs_display_options::make_from_quizsbs($quizsbs,
                quizsbs_attempt_state($quizsbs, $attempt));
        foreach ($fields as $field) {
            $someoptions->$field = $someoptions->$field || $attemptoptions->$field;
            $alloptions->$field = $alloptions->$field && $attemptoptions->$field;
        }
        $someoptions->marks = max($someoptions->marks, $attemptoptions->marks);
        $alloptions->marks = min($alloptions->marks, $attemptoptions->marks);
    }
    return array($someoptions, $alloptions);
}

// Functions for sending notification messages /////////////////////////////////

/**
 * Sends a confirmation message to the student confirming that the attempt was processed.
 *
 * @param object $a lots of useful information that can be used in the message
 *      subject and body.
 *
 * @return int|false as for {@link message_send()}.
 */
function quizsbs_send_confirmation($recipient, $a) {

    // Add information about the recipient to $a.
    // Don't do idnumber. we want idnumber to be the submitter's idnumber.
    $a->username     = fullname($recipient);
    $a->userusername = $recipient->username;

    // Prepare the message.
    $eventdata = new stdClass();
    $eventdata->component         = 'mod_quizsbs';
    $eventdata->name              = 'confirmation';
    $eventdata->notification      = 1;

    $eventdata->userfrom          = core_user::get_noreply_user();
    $eventdata->userto            = $recipient;
    $eventdata->subject           = get_string('emailconfirmsubject', 'quizsbs', $a);
    $eventdata->fullmessage       = get_string('emailconfirmbody', 'quizsbs', $a);
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml   = '';

    $eventdata->smallmessage      = get_string('emailconfirmsmall', 'quizsbs', $a);
    $eventdata->contexturl        = $a->quizsbsurl;
    $eventdata->contexturlname    = $a->quizsbsname;

    // ... and send it.
    return message_send($eventdata);
}

/**
 * Sends notification messages to the interested parties that assign the role capability
 *
 * @param object $recipient user object of the intended recipient
 * @param object $a associative array of replaceable fields for the templates
 *
 * @return int|false as for {@link message_send()}.
 */
function quizsbs_send_notification($recipient, $submitter, $a) {

    // Recipient info for template.
    $a->useridnumber = $recipient->idnumber;
    $a->username     = fullname($recipient);
    $a->userusername = $recipient->username;

    // Prepare the message.
    $eventdata = new stdClass();
    $eventdata->component         = 'mod_quizsbs';
    $eventdata->name              = 'submission';
    $eventdata->notification      = 1;

    $eventdata->userfrom          = $submitter;
    $eventdata->userto            = $recipient;
    $eventdata->subject           = get_string('emailnotifysubject', 'quizsbs', $a);
    $eventdata->fullmessage       = get_string('emailnotifybody', 'quizsbs', $a);
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml   = '';

    $eventdata->smallmessage      = get_string('emailnotifysmall', 'quizsbs', $a);
    $eventdata->contexturl        = $a->quizsbsreviewurl;
    $eventdata->contexturlname    = $a->quizsbsname;

    // ... and send it.
    return message_send($eventdata);
}

/**
 * Send all the requried messages when a quizsbs attempt is submitted.
 *
 * @param object $course the course
 * @param object $quizsbs the quizsbs
 * @param object $attempt this attempt just finished
 * @param object $context the quizsbs context
 * @param object $cm the coursemodule for this quizsbs
 *
 * @return bool true if all necessary messages were sent successfully, else false.
 */
function quizsbs_send_notification_messages($course, $quizsbs, $attempt, $context, $cm) {
    global $CFG, $DB;

    // Do nothing if required objects not present.
    if (empty($course) or empty($quizsbs) or empty($attempt) or empty($context)) {
        throw new coding_exception('$course, $quizsbs, $attempt, $context and $cm must all be set.');
    }

    $submitter = $DB->get_record('user', array('id' => $attempt->userid), '*', MUST_EXIST);

    // Check for confirmation required.
    $sendconfirm = false;
    $notifyexcludeusers = '';
    if (has_capability('mod/quizsbs:emailconfirmsubmission', $context, $submitter, false)) {
        $notifyexcludeusers = $submitter->id;
        $sendconfirm = true;
    }

    // Check for notifications required.
    $notifyfields = 'u.id, u.username, u.idnumber, u.email, u.emailstop, u.lang,
            u.timezone, u.mailformat, u.maildisplay, u.auth, u.suspended, u.deleted, ';
    $notifyfields .= get_all_user_name_fields(true, 'u');
    $groups = groups_get_all_groups($course->id, $submitter->id, $cm->groupingid);
    if (is_array($groups) && count($groups) > 0) {
        $groups = array_keys($groups);
    } else if (groups_get_activity_groupmode($cm, $course) != NOGROUPS) {
        // If the user is not in a group, and the quizsbs is set to group mode,
        // then set $groups to a non-existant id so that only users with
        // 'moodle/site:accessallgroups' get notified.
        $groups = -1;
    } else {
        $groups = '';
    }
    $userstonotify = get_users_by_capability($context, 'mod/quizsbs:emailnotifysubmission',
            $notifyfields, '', '', '', $groups, $notifyexcludeusers, false, false, true);

    if (empty($userstonotify) && !$sendconfirm) {
        return true; // Nothing to do.
    }

    $a = new stdClass();
    // Course info.
    $a->coursename      = $course->fullname;
    $a->courseshortname = $course->shortname;
    // quizsbs info.
    $a->quizsbsname        = $quizsbs->name;
    $a->quizsbsreporturl   = $CFG->wwwroot . '/mod/quizsbs/report.php?id=' . $cm->id;
    $a->quizsbsreportlink  = '<a href="' . $a->quizsbsreporturl . '">' .
            format_string($quizsbs->name) . ' report</a>';
    $a->quizsbsurl         = $CFG->wwwroot . '/mod/quizsbs/view.php?id=' . $cm->id;
    $a->quizsbslink        = '<a href="' . $a->quizsbsurl . '">' . format_string($quizsbs->name) . '</a>';
    // Attempt info.
    $a->submissiontime  = userdate($attempt->timefinish);
    $a->timetaken       = format_time($attempt->timefinish - $attempt->timestart);
    $a->quizsbsreviewurl   = $CFG->wwwroot . '/mod/quizsbs/review.php?attempt=' . $attempt->id;
    $a->quizsbsreviewlink  = '<a href="' . $a->quizsbsreviewurl . '">' .
            format_string($quizsbs->name) . ' review</a>';
    // Student who sat the quizsbs info.
    $a->studentidnumber = $submitter->idnumber;
    $a->studentname     = fullname($submitter);
    $a->studentusername = $submitter->username;

    $allok = true;

    // Send notifications if required.
    if (!empty($userstonotify)) {
        foreach ($userstonotify as $recipient) {
            $allok = $allok && quizsbs_send_notification($recipient, $submitter, $a);
        }
    }

    // Send confirmation if required. We send the student confirmation last, so
    // that if message sending is being intermittently buggy, which means we send
    // some but not all messages, and then try again later, then teachers may get
    // duplicate messages, but the student will always get exactly one.
    if ($sendconfirm) {
        $allok = $allok && quizsbs_send_confirmation($submitter, $a);
    }

    return $allok;
}

/**
 * Send the notification message when a quizsbs attempt becomes overdue.
 *
 * @param quizsbs_attempt $attemptobj all the data about the quizsbs attempt.
 */
function quizsbs_send_overdue_message($attemptobj) {
    global $CFG, $DB;

    $submitter = $DB->get_record('user', array('id' => $attemptobj->get_userid()), '*', MUST_EXIST);

    if (!$attemptobj->has_capability('mod/quizsbs:emailwarnoverdue', $submitter->id, false)) {
        return; // Message not required.
    }

    if (!$attemptobj->has_response_to_at_least_one_graded_question()) {
        return; // Message not required.
    }

    // Prepare lots of useful information that admins might want to include in
    // the email message.
    $quizsbsname = format_string($attemptobj->get_quizsbs_name());

    $deadlines = array();
    if ($attemptobj->get_quizsbs()->timelimit) {
        $deadlines[] = $attemptobj->get_attempt()->timestart + $attemptobj->get_quizsbs()->timelimit;
    }
    if ($attemptobj->get_quizsbs()->timeclose) {
        $deadlines[] = $attemptobj->get_quizsbs()->timeclose;
    }
    $duedate = min($deadlines);
    $graceend = $duedate + $attemptobj->get_quizsbs()->graceperiod;

    $a = new stdClass();
    // Course info.
    $a->coursename         = format_string($attemptobj->get_course()->fullname);
    $a->courseshortname    = format_string($attemptobj->get_course()->shortname);
    // quizsbs info.
    $a->quizsbsname           = $quizsbsname;
    $a->quizsbsurl            = $attemptobj->view_url();
    $a->quizsbslink           = '<a href="' . $a->quizsbsurl . '">' . $quizsbsname . '</a>';
    // Attempt info.
    $a->attemptduedate     = userdate($duedate);
    $a->attemptgraceend    = userdate($graceend);
    $a->attemptsummaryurl  = $attemptobj->summary_url()->out(false);
    $a->attemptsummarylink = '<a href="' . $a->attemptsummaryurl . '">' . $quizsbsname . ' review</a>';
    // Student's info.
    $a->studentidnumber    = $submitter->idnumber;
    $a->studentname        = fullname($submitter);
    $a->studentusername    = $submitter->username;

    // Prepare the message.
    $eventdata = new stdClass();
    $eventdata->component         = 'mod_quizsbs';
    $eventdata->name              = 'attempt_overdue';
    $eventdata->notification      = 1;

    $eventdata->userfrom          = core_user::get_noreply_user();
    $eventdata->userto            = $submitter;
    $eventdata->subject           = get_string('emailoverduesubject', 'quizsbs', $a);
    $eventdata->fullmessage       = get_string('emailoverduebody', 'quizsbs', $a);
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml   = '';

    $eventdata->smallmessage      = get_string('emailoverduesmall', 'quizsbs', $a);
    $eventdata->contexturl        = $a->quizsbsurl;
    $eventdata->contexturlname    = $a->quizsbsname;

    // Send the message.
    return message_send($eventdata);
}

/**
 * Handle the quizsbs_attempt_submitted event.
 *
 * This sends the confirmation and notification messages, if required.
 *
 * @param object $event the event object.
 */
function quizsbs_attempt_submitted_handler($event) {
    global $DB;

    $course  = $DB->get_record('course', array('id' => $event->courseid));
    $attempt = $event->get_record_snapshot('quizsbs_attempts', $event->objectid);
    $quizsbs    = $event->get_record_snapshot('quizsbs', $attempt->quizsbs);
    $cm      = get_coursemodule_from_id('quizsbs', $event->get_context()->instanceid, $event->courseid);

    if (!($course && $quizsbs && $cm && $attempt)) {
        // Something has been deleted since the event was raised. Therefore, the
        // event is no longer relevant.
        return true;
    }

    // Update completion state.
    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) && ($quizsbs->completionattemptsexhausted || $quizsbs->completionpass)) {
        $completion->update_state($cm, COMPLETION_COMPLETE, $event->userid);
    }
    return quizsbs_send_notification_messages($course, $quizsbs, $attempt,
            context_module::instance($cm->id), $cm);
}

/**
 * Handle groups_member_added event
 *
 * @param object $event the event object.
 * @deprecated since 2.6, see {@link \mod_quizsbs\group_observers::group_member_added()}.
 */
function quizsbs_groups_member_added_handler($event) {
    debugging('quizsbs_groups_member_added_handler() is deprecated, please use ' .
        '\mod_quizsbs\group_observers::group_member_added() instead.', DEBUG_DEVELOPER);
    quizsbs_update_open_attempts(array('userid'=>$event->userid, 'groupid'=>$event->groupid));
}

/**
 * Handle groups_member_removed event
 *
 * @param object $event the event object.
 * @deprecated since 2.6, see {@link \mod_quizsbs\group_observers::group_member_removed()}.
 */
function quizsbs_groups_member_removed_handler($event) {
    debugging('quizsbs_groups_member_removed_handler() is deprecated, please use ' .
        '\mod_quizsbs\group_observers::group_member_removed() instead.', DEBUG_DEVELOPER);
    quizsbs_update_open_attempts(array('userid'=>$event->userid, 'groupid'=>$event->groupid));
}

/**
 * Handle groups_group_deleted event
 *
 * @param object $event the event object.
 * @deprecated since 2.6, see {@link \mod_quizsbs\group_observers::group_deleted()}.
 */
function quizsbs_groups_group_deleted_handler($event) {
    global $DB;
    debugging('quizsbs_groups_group_deleted_handler() is deprecated, please use ' .
        '\mod_quizsbs\group_observers::group_deleted() instead.', DEBUG_DEVELOPER);
    quizsbs_process_group_deleted_in_course($event->courseid);
}

/**
 * Logic to happen when a/some group(s) has/have been deleted in a course.
 *
 * @param int $courseid The course ID.
 * @return void
 */
function quizsbs_process_group_deleted_in_course($courseid) {
    global $DB;

    // It would be nice if we got the groupid that was deleted.
    // Instead, we just update all quizsbszes with orphaned group overrides.
    $sql = "SELECT o.id, o.quizsbs
              FROM {quizsbs_overrides} o
              JOIN {quizsbs} quizsbs ON quizsbs.id = o.quizsbs
         LEFT JOIN {groups} grp ON grp.id = o.groupid
             WHERE quizsbs.course = :courseid
               AND o.groupid IS NOT NULL
               AND grp.id IS NULL";
    $params = array('courseid' => $courseid);
    $records = $DB->get_records_sql_menu($sql, $params);
    if (!$records) {
        return; // Nothing to do.
    }
    $DB->delete_records_list('quizsbs_overrides', 'id', array_keys($records));
    quizsbs_update_open_attempts(array('quizsbsid' => array_unique(array_values($records))));
}

/**
 * Handle groups_members_removed event
 *
 * @param object $event the event object.
 * @deprecated since 2.6, see {@link \mod_quizsbs\group_observers::group_member_removed()}.
 */
function quizsbs_groups_members_removed_handler($event) {
    debugging('quizsbs_groups_members_removed_handler() is deprecated, please use ' .
        '\mod_quizsbs\group_observers::group_member_removed() instead.', DEBUG_DEVELOPER);
    if ($event->userid == 0) {
        quizsbs_update_open_attempts(array('courseid'=>$event->courseid));
    } else {
        quizsbs_update_open_attempts(array('courseid'=>$event->courseid, 'userid'=>$event->userid));
    }
}

/**
 * Get the information about the standard quizsbs JavaScript module.
 * @return array a standard jsmodule structure.
 */
function quizsbs_get_js_module() {
    global $PAGE;

    return array(
        'name' => 'mod_quizsbs',
        'fullpath' => '/mod/quizsbs/module.js',
        'requires' => array('base', 'dom', 'event-delegate', 'event-key',
                'core_question_engine', 'moodle-core-formchangechecker'),
        'strings' => array(
            array('cancel', 'moodle'),
            array('flagged', 'question'),
            array('functiondisabledbysecuremode', 'quizsbs'),
            array('startattempt', 'quizsbs'),
            array('timesup', 'quizsbs'),
            array('changesmadereallygoaway', 'moodle'),
        ),
    );
}


/**
 * An extension of question_display_options that includes the extra options used
 * by the quizsbs.
 *
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_quizsbs_display_options extends question_display_options {
    /**#@+
     * @var integer bits used to indicate various times in relation to a
     * quizsbs attempt.
     */
    const DURING =            0x10000;
    const IMMEDIATELY_AFTER = 0x01000;
    const LATER_WHILE_OPEN =  0x00100;
    const AFTER_CLOSE =       0x00010;
    /**#@-*/

    /**
     * @var boolean if this is false, then the student is not allowed to review
     * anything about the attempt.
     */
    public $attempt = true;

    /**
     * @var boolean if this is false, then the student is not allowed to review
     * anything about the attempt.
     */
    public $overallfeedback = self::VISIBLE;

    /**
     * Set up the various options from the quizsbs settings, and a time constant.
     * @param object $quizsbs the quizsbs settings.
     * @param int $one of the {@link DURING}, {@link IMMEDIATELY_AFTER},
     * {@link LATER_WHILE_OPEN} or {@link AFTER_CLOSE} constants.
     * @return mod_quizsbs_display_options set up appropriately.
     */
    public static function make_from_quizsbs($quizsbs, $when) {
        $options = new self();

        $options->attempt = self::extract($quizsbs->reviewattempt, $when, true, false);
        $options->correctness = self::extract($quizsbs->reviewcorrectness, $when);
        $options->marks = self::extract($quizsbs->reviewmarks, $when,
                self::MARK_AND_MAX, self::MAX_ONLY);
        $options->feedback = self::extract($quizsbs->reviewspecificfeedback, $when);
        $options->generalfeedback = self::extract($quizsbs->reviewgeneralfeedback, $when);
        $options->rightanswer = self::extract($quizsbs->reviewrightanswer, $when);
        $options->overallfeedback = self::extract($quizsbs->reviewoverallfeedback, $when);

        $options->numpartscorrect = $options->feedback;
        $options->manualcomment = $options->feedback;

        if ($quizsbs->questiondecimalpoints != -1) {
            $options->markdp = $quizsbs->questiondecimalpoints;
        } else {
            $options->markdp = $quizsbs->decimalpoints;
        }

        return $options;
    }

    protected static function extract($bitmask, $bit,
            $whenset = self::VISIBLE, $whennotset = self::HIDDEN) {
        if ($bitmask & $bit) {
            return $whenset;
        } else {
            return $whennotset;
        }
    }
}


/**
 * A {@link qubaid_condition} for finding all the question usages belonging to
 * a particular quizsbs.
 *
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qubaids_for_quizsbs extends qubaid_join {
    public function __construct($quizsbsid, $includepreviews = true, $onlyfinished = false) {
        $where = 'quizsbsa.quizsbs = :quizsbsaquizsbs';
        $params = array('quizsbsaquizsbs' => $quizsbsid);

        if (!$includepreviews) {
            $where .= ' AND preview = 0';
        }

        if ($onlyfinished) {
            $where .= ' AND state == :statefinished';
            $params['statefinished'] = quizsbs_attempt::FINISHED;
        }

        parent::__construct('{quizsbs_attempts} quizsbsa', 'quizsbsa.uniqueid', $where, $params);
    }
}

/**
 * Creates a textual representation of a question for display.
 *
 * @param object $question A question object from the database questions table
 * @param bool $showicon If true, show the question's icon with the question. False by default.
 * @param bool $showquestiontext If true (default), show question text after question name.
 *       If false, show only question name.
 * @return string
 */
function quizsbs_question_tostring($question, $showicon = false, $showquestiontext = true) {
    $result = '';

    $name = shorten_text(format_string($question->name), 200);
    if ($showicon) {
        $name .= print_question_icon($question) . ' ' . $name;
    }
    $result .= html_writer::span($name, 'questionname');

    if ($showquestiontext) {
        $questiontext = question_utils::to_plain_text($question->questiontext,
                $question->questiontextformat, array('noclean' => true, 'para' => false));
        $questiontext = shorten_text($questiontext, 200);
        if ($questiontext) {
            $result .= ' ' . html_writer::span(s($questiontext), 'questiontext');
        }
    }

    return $result;
}

/**
 * Verify that the question exists, and the user has permission to use it.
 * Does not return. Throws an exception if the question cannot be used.
 * @param int $questionid The id of the question.
 */
function quizsbs_require_question_use($questionid) {
    global $DB;
    $question = $DB->get_record('question', array('id' => $questionid), '*', MUST_EXIST);
    question_require_capability_on($question, 'use');
}

/**
 * Verify that the question exists, and the user has permission to use it.
 * @param object $quizsbs the quizsbs settings.
 * @param int $slot which question in the quizsbs to test.
 * @return bool whether the user can use this question.
 */
function quizsbs_has_question_use($quizsbs, $slot) {
    global $DB;
    $question = $DB->get_record_sql("
            SELECT q.*
              FROM {quizsbs_slots} slot
              JOIN {question} q ON q.id = slot.questionid
             WHERE slot.quizsbsid = ? AND slot.slot = ?", array($quizsbs->id, $slot));
    if (!$question) {
        return false;
    }
    return question_has_capability_on($question, 'use');
}

/**
 * Add a question to a quizsbs
 *
 * Adds a question to a quizsbs by updating $quizsbs as well as the
 * quizsbs and quizsbs_slots tables. It also adds a page break if required.
 * @param int $questionid The id of the question to be added
 * @param object $quizsbs The extended quizsbs object as used by edit.php
 *      This is updated by this function
 * @param int $page Which page in quizsbs to add the question on. If 0 (default),
 *      add at the end
 * @param float $maxmark The maximum mark to set for this question. (Optional,
 *      defaults to question.defaultmark.
 * @return bool false if the question was already in the quizsbs
 */
function quizsbs_add_quizsbs_question($questionid, $quizsbs, $page = 0, $maxmark = null) {
    global $DB;
    $slots = $DB->get_records('quizsbs_slots', array('quizsbsid' => $quizsbs->id),
            'slot', 'questionid, slot, page, id');
    if (array_key_exists($questionid, $slots)) {
        return false;
    }

    $trans = $DB->start_delegated_transaction();

    $maxpage = 1;
    $numonlastpage = 0;
    foreach ($slots as $slot) {
        if ($slot->page > $maxpage) {
            $maxpage = $slot->page;
            $numonlastpage = 1;
        } else {
            $numonlastpage += 1;
        }
    }

    // Add the new question instance.
    $slot = new stdClass();
    $slot->quizsbsid = $quizsbs->id;
    $slot->questionid = $questionid;

    if ($maxmark !== null) {
        $slot->maxmark = $maxmark;
    } else {
        $slot->maxmark = $DB->get_field('question', 'defaultmark', array('id' => $questionid));
    }

    if (is_int($page) && $page >= 1) {
        // Adding on a given page.
        $lastslotbefore = 0;
        foreach (array_reverse($slots) as $otherslot) {
            if ($otherslot->page > $page) {
                $DB->set_field('quizsbs_slots', 'slot', $otherslot->slot + 1, array('id' => $otherslot->id));
            } else {
                $lastslotbefore = $otherslot->slot;
                break;
            }
        }
        $slot->slot = $lastslotbefore + 1;
        $slot->page = min($page, $maxpage + 1);

        $DB->execute("
                UPDATE {quizsbs_sections}
                   SET firstslot = firstslot + 1
                 WHERE quizsbsid = ?
                   AND firstslot > ?
                 ORDER BY firstslot DESC
                ", array($quizsbs->id, max($lastslotbefore, 1)));

    } else {
        $lastslot = end($slots);
        if ($lastslot) {
            $slot->slot = $lastslot->slot + 1;
        } else {
            $slot->slot = 1;
        }
        if ($quizsbs->questionsperpage && $numonlastpage >= $quizsbs->questionsperpage) {
            $slot->page = $maxpage + 1;
        } else {
            $slot->page = $maxpage;
        }
    }

    $DB->insert_record('quizsbs_slots', $slot);
    $trans->allow_commit();
}

/**
 * Add a random question to the quizsbs at a given point.
 * @param object $quizsbs the quizsbs settings.
 * @param int $addonpage the page on which to add the question.
 * @param int $categoryid the question category to add the question from.
 * @param int $number the number of random questions to add.
 * @param bool $includesubcategories whether to include questoins from subcategories.
 */
function quizsbs_add_random_questions($quizsbs, $addonpage, $categoryid, $number,
        $includesubcategories) {
    global $DB;

    $category = $DB->get_record('question_categories', array('id' => $categoryid));
    if (!$category) {
        print_error('invalidcategoryid', 'error');
    }

    $catcontext = context::instance_by_id($category->contextid);
    require_capability('moodle/question:useall', $catcontext);

    // Find existing random questions in this category that are
    // not used by any quizsbs.
    if ($existingquestions = $DB->get_records_sql(
            "SELECT q.id, q.qtype FROM {question} q
            WHERE qtype = 'random'
                AND category = ?
                AND " . $DB->sql_compare_text('questiontext') . " = ?
                AND NOT EXISTS (
                        SELECT *
                          FROM {quizsbs_slots}
                         WHERE questionid = q.id)
            ORDER BY id", array($category->id, ($includesubcategories ? '1' : '0')))) {
            // Take as many of these as needed.
        while (($existingquestion = array_shift($existingquestions)) && $number > 0) {
            quizsbs_add_quizsbs_question($existingquestion->id, $quizsbs, $addonpage);
            $number -= 1;
        }
    }

    if ($number <= 0) {
        return;
    }

    // More random questions are needed, create them.
    for ($i = 0; $i < $number; $i += 1) {
        $form = new stdClass();
        $form->questiontext = array('text' => ($includesubcategories ? '1' : '0'), 'format' => 0);
        $form->category = $category->id . ',' . $category->contextid;
        $form->defaultmark = 1;
        $form->hidden = 1;
        $form->stamp = make_unique_id_code(); // Set the unique code (not to be changed).
        $question = new stdClass();
        $question->qtype = 'random';
        $question = question_bank::get_qtype('random')->save_question($question, $form);
        if (!isset($question->id)) {
            print_error('cannotinsertrandomquestion', 'quizsbs');
        }
        quizsbs_add_quizsbs_question($question->id, $quizsbs, $addonpage);
    }
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $quizsbs       quizsbs object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.1
 */
function quizsbs_view($quizsbs, $course, $cm, $context) {

    $params = array(
        'objectid' => $quizsbs->id,
        'context' => $context
    );

    $event = \mod_quizsbs\event\course_module_viewed::create($params);
    $event->add_record_snapshot('quizsbs', $quizsbs);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

/**
 * Validate permissions for creating a new attempt and start a new preview attempt if required.
 *
 * @param  quizsbs $quizsbsobj quizsbs object
 * @param  quizsbs_access_manager $accessmanager quizsbs access manager
 * @param  bool $forcenew whether was required to start a new preview attempt
 * @param  int $page page to jump to in the attempt
 * @param  bool $redirect whether to redirect or throw exceptions (for web or ws usage)
 * @return array an array containing the attempt information, access error messages and the page to jump to in the attempt
 * @throws moodle_quizsbs_exception
 * @since Moodle 3.1
 */
function quizsbs_validate_new_attempt(quizsbs $quizsbsobj, quizsbs_access_manager $accessmanager, $forcenew, $page, $redirect) {
    global $DB, $USER;
    $timenow = time();

    if ($quizsbsobj->is_preview_user() && $forcenew) {
        $accessmanager->current_attempt_finished();
    }

    // Check capabilities.
    if (!$quizsbsobj->is_preview_user()) {
        $quizsbsobj->require_capability('mod/quizsbs:attempt');
    }

    // Check to see if a new preview was requested.
    if ($quizsbsobj->is_preview_user() && $forcenew) {
        // To force the creation of a new preview, we mark the current attempt (if any)
        // as finished. It will then automatically be deleted below.
        $DB->set_field('quizsbs_attempts', 'state', quizsbs_attempt::FINISHED,
                array('quizsbs' => $quizsbsobj->get_quizsbsid(), 'userid' => $USER->id));
    }

    // Look for an existing attempt.
    $attempts = quizsbs_get_user_attempts($quizsbsobj->get_quizsbsid(), $USER->id, 'all', true);
    $lastattempt = end($attempts);

    $attemptnumber = null;
    // If an in-progress attempt exists, check password then redirect to it.
    if ($lastattempt && ($lastattempt->state == quizsbs_attempt::IN_PROGRESS ||
            $lastattempt->state == quizsbs_attempt::OVERDUE)) {
        $currentattemptid = $lastattempt->id;
        $messages = $accessmanager->prevent_access();

        // If the attempt is now overdue, deal with that.
        $quizsbsobj->create_attempt_object($lastattempt)->handle_if_time_expired($timenow, true);

        // And, if the attempt is now no longer in progress, redirect to the appropriate place.
        if ($lastattempt->state == quizsbs_attempt::ABANDONED || $lastattempt->state == quizsbs_attempt::FINISHED) {
            if ($redirect) {
                redirect($quizsbsobj->review_url($lastattempt->id));
            } else {
                throw new moodle_quizsbs_exception($quizsbsobj, 'attemptalreadyclosed');
            }
        }

        // If the page number was not explicitly in the URL, go to the current page.
        if ($page == -1) {
            $page = $lastattempt->currentpage;
        }

    } else {
        while ($lastattempt && $lastattempt->preview) {
            $lastattempt = array_pop($attempts);
        }

        // Get number for the next or unfinished attempt.
        if ($lastattempt) {
            $attemptnumber = $lastattempt->attempt + 1;
        } else {
            $lastattempt = false;
            $attemptnumber = 1;
        }
        $currentattemptid = null;

        $messages = $accessmanager->prevent_access() +
            $accessmanager->prevent_new_attempt(count($attempts), $lastattempt);

        if ($page == -1) {
            $page = 0;
        }
    }
    return array($currentattemptid, $attemptnumber, $lastattempt, $messages, $page);
}

/**
 * Prepare and start a new attempt deleting the previous preview attempts.
 *
 * @param  quizsbs $quizsbsobj quizsbs object
 * @param  int $attemptnumber the attempt number
 * @param  object $lastattempt last attempt object
 * @return object the new attempt
 * @since  Moodle 3.1
 */
function quizsbs_prepare_and_start_new_attempt(quizsbs $quizsbsobj, $attemptnumber, $lastattempt) {
    global $DB, $USER;

    // Delete any previous preview attempts belonging to this user.
    quizsbs_delete_previews($quizsbsobj->get_quizsbs(), $USER->id);

    $quba = question_engine::make_questions_usage_by_activity('mod_quizsbs', $quizsbsobj->get_context());
    $quba->set_preferred_behaviour($quizsbsobj->get_quizsbs()->preferredbehaviour);

    // Create the new attempt and initialize the question sessions
    $timenow = time(); // Update time now, in case the server is running really slowly.
    $attempt = quizsbs_create_attempt($quizsbsobj, $attemptnumber, $lastattempt, $timenow, $quizsbsobj->is_preview_user());

    if (!($quizsbsobj->get_quizsbs()->attemptonlast && $lastattempt)) {
        $attempt = quizsbs_start_new_attempt($quizsbsobj, $quba, $attempt, $attemptnumber, $timenow);
    } else {
        $attempt = quizsbs_start_attempt_built_on_last($quba, $attempt, $lastattempt);
    }

    $transaction = $DB->start_delegated_transaction();

    $attempt = quizsbs_attempt_save_started($quizsbsobj, $quba, $attempt);

    $transaction->allow_commit();

    return $attempt;
}
