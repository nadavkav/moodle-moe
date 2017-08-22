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
 * Library of functions used by the moeworksheets module.
 *
 * This contains functions that are called from within the moeworksheets module only
 * Functions that are also called by core Moodle are in {@link lib.php}
 * This script also loads the code in {@link questionlib.php} which holds
 * the module-indpendent code for handling questions and which in turn
 * initialises all the questiontype classes.
 *
 * @package    mod_moeworksheets
 * @copyright  1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/moeworksheets/lib.php');
require_once($CFG->dirroot . '/mod/moeworksheets/accessmanager.php');
require_once($CFG->dirroot . '/mod/moeworksheets/accessmanager_form.php');
require_once($CFG->dirroot . '/mod/moeworksheets/renderer.php');
require_once($CFG->dirroot . '/mod/moeworksheets/attemptlib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/eventslib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/questionlib.php');


/**
 * @var int We show the countdown timer if there is less than this amount of time left before the
 * the moeworksheets close date. (1 hour)
 */
define('moeworksheets_SHOW_TIME_BEFORE_DEADLINE', '3600');

/**
 * @var int If there are fewer than this many seconds left when the student submits
 * a page of the moeworksheets, then do not take them to the next page of the moeworksheets. Instead
 * close the moeworksheets immediately.
 */
define('moeworksheets_MIN_TIME_TO_CONTINUE', '2');

/**
 * @var int We show no image when user selects No image from dropdown menu in moeworksheets settings.
 */
define('moeworksheets_SHOWIMAGE_NONE', 0);

/**
 * @var int We show small image when user selects small image from dropdown menu in moeworksheets settings.
 */
define('moeworksheets_SHOWIMAGE_SMALL', 1);

/**
 * @var int We show Large image when user selects Large image from dropdown menu in moeworksheets settings.
 */
define('moeworksheets_SHOWIMAGE_LARGE', 2);


// Functions related to attempts ///////////////////////////////////////////////

/**
 * Creates an object to represent a new attempt at a moeworksheets
 *
 * Creates an attempt object to represent an attempt at the moeworksheets by the current
 * user starting at the current time. The ->id field is not set. The object is
 * NOT written to the database.
 *
 * @param object $moeworksheetsobj the moeworksheets object to create an attempt for.
 * @param int $attemptnumber the sequence number for the attempt.
 * @param object $lastattempt the previous attempt by this user, if any. Only needed
 *         if $attemptnumber > 1 and $moeworksheets->attemptonlast is true.
 * @param int $timenow the time the attempt was started at.
 * @param bool $ispreview whether this new attempt is a preview.
 * @param int $userid  the id of the user attempting this moeworksheets.
 *
 * @return object the newly created attempt object.
 */
function moeworksheets_create_attempt(moeworksheets $moeworksheetsobj, $attemptnumber, $lastattempt, $timenow, $ispreview = false, $userid = null) {
    global $USER;

    if ($userid === null) {
        $userid = $USER->id;
    }

    $moeworksheets = $moeworksheetsobj->get_moeworksheets();
    if ($moeworksheets->sumgrades < 0.000005 && $moeworksheets->grade > 0.000005) {
        throw new moodle_exception('cannotstartgradesmismatch', 'moeworksheets',
                new moodle_url('/mod/moeworksheets/view.php', array('q' => $moeworksheets->id)),
                    array('grade' => moeworksheets_format_grade($moeworksheets, $moeworksheets->grade)));
    }

    if ($attemptnumber == 1 || !$moeworksheets->attemptonlast) {
        // We are not building on last attempt so create a new attempt.
        $attempt = new stdClass();
        $attempt->moeworksheets = $moeworksheets->id;
        $attempt->userid = $userid;
        $attempt->preview = 0;
        $attempt->layout = '';
    } else {
        // Build on last attempt.
        if (empty($lastattempt)) {
            print_error('cannotfindprevattempt', 'moeworksheets');
        }
        $attempt = $lastattempt;
    }

    $attempt->attempt = $attemptnumber;
    $attempt->timestart = $timenow;
    $attempt->timefinish = 0;
    $attempt->timemodified = $timenow;
    $attempt->state = moeworksheets_attempt::IN_PROGRESS;
    $attempt->currentpage = 0;
    $attempt->sumgrades = null;

    // If this is a preview, mark it as such.
    if ($ispreview) {
        $attempt->preview = 1;
    }

    $timeclose = $moeworksheetsobj->get_access_manager($timenow)->get_end_time($attempt);
    if ($timeclose === false || $ispreview) {
        $attempt->timecheckstate = null;
    } else {
        $attempt->timecheckstate = $timeclose;
    }

    return $attempt;
}
/**
 * Start a normal, new, moeworksheets attempt.
 *
 * @param moeworksheets      $moeworksheetsobj            the moeworksheets object to start an attempt for.
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
function moeworksheets_start_new_attempt($moeworksheetsobj, $quba, $attempt, $attemptnumber, $timenow,
                                $questionids = array(), $forcedvariantsbyslot = array()) {

    // Usages for this user's previous moeworksheets attempts.
    $qubaids = new \mod_moeworksheets\question\qubaids_for_users_attempts(
            $moeworksheetsobj->get_moeworksheetsid(), $attempt->userid);

    // Fully load all the questions in this moeworksheets.
    $moeworksheetsobj->preload_questions();
    $moeworksheetsobj->load_questions();

    // First load all the non-random questions.
    $randomfound = false;
    $slot = 0;
    $questions = array();
    $maxmark = array();
    $page = array();
    foreach ($moeworksheetsobj->get_questions() as $questiondata) {
        $slot += 1;
        $maxmark[$slot] = $questiondata->maxmark;
        $page[$slot] = $questiondata->page;
        if ($questiondata->qtype == 'random') {
            $randomfound = true;
            continue;
        }
        if (!$moeworksheetsobj->get_moeworksheets()->shuffleanswers) {
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

        foreach ($moeworksheetsobj->get_questions() as $questiondata) {
            $slot += 1;
            if ($questiondata->qtype != 'random') {
                continue;
            }

            // Deal with fixed random choices for testing.
            if (isset($questionids[$quba->next_slot_number()])) {
                if ($randomloader->is_question_available($questiondata->category,
                        (bool) $questiondata->questiontext, $questionids[$quba->next_slot_number()])) {
                    $questions[$slot] = question_bank::load_question(
                            $questionids[$quba->next_slot_number()], $moeworksheetsobj->get_moeworksheets()->shuffleanswers);
                    continue;
                } else {
                    throw new coding_exception('Forced question id not available.');
                }
            }

            // Normal case, pick one at random.
            $questionid = $randomloader->get_next_question_id($questiondata->category,
                        (bool) $questiondata->questiontext);
            if ($questionid === null) {
                throw new moodle_exception('notenoughrandomquestions', 'moeworksheets',
                                           $moeworksheetsobj->view_url(), $questiondata);
            }

            $questions[$slot] = question_bank::load_question($questionid,
                    $moeworksheetsobj->get_moeworksheets()->shuffleanswers);
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
    $sections = $moeworksheetsobj->get_sections();
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
                if ($questionsonthispage && $questionsonthispage == $moeworksheetsobj->get_moeworksheets()->questionsperpage) {
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
function moeworksheets_start_attempt_built_on_last($quba, $attempt, $lastattempt) {
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
 * The save started question usage and moeworksheets attempt in db and log the started attempt.
 *
 * @param moeworksheets                       $moeworksheetsobj
 * @param question_usage_by_activity $quba
 * @param object                     $attempt
 * @return object                    attempt object with uniqueid and id set.
 */
function moeworksheets_attempt_save_started($moeworksheetsobj, $quba, $attempt) {
    global $DB;
    // Save the attempt in the database.
    question_engine::save_questions_usage_by_activity($quba);
    $attempt->uniqueid = $quba->get_id();
    $attempt->id = $DB->insert_record('moeworksheets_attempts', $attempt);

    // Params used by the events below.
    $params = array(
        'objectid' => $attempt->id,
        'relateduserid' => $attempt->userid,
        'courseid' => $moeworksheetsobj->get_courseid(),
        'context' => $moeworksheetsobj->get_context()
    );
    // Decide which event we are using.
    if ($attempt->preview) {
        $params['other'] = array(
            'moeworksheetsid' => $moeworksheetsobj->get_moeworksheetsid()
        );
        $event = \mod_moeworksheets\event\attempt_preview_started::create($params);
    } else {
        $event = \mod_moeworksheets\event\attempt_started::create($params);

    }

    // Trigger the event.
    $event->add_record_snapshot('moeworksheets', $moeworksheetsobj->get_moeworksheets());
    $event->add_record_snapshot('moeworksheets_attempts', $attempt);
    $event->trigger();

    return $attempt;
}

/**
 * Returns an unfinished attempt (if there is one) for the given
 * user on the given moeworksheets. This function does not return preview attempts.
 *
 * @param int $moeworksheetsid the id of the moeworksheets.
 * @param int $userid the id of the user.
 *
 * @return mixed the unfinished attempt if there is one, false if not.
 */
function moeworksheets_get_user_attempt_unfinished($moeworksheetsid, $userid) {
    $attempts = moeworksheets_get_user_attempts($moeworksheetsid, $userid, 'unfinished', true);
    if ($attempts) {
        return array_shift($attempts);
    } else {
        return false;
    }
}

/**
 * Delete a moeworksheets attempt.
 * @param mixed $attempt an integer attempt id or an attempt object
 *      (row of the moeworksheets_attempts table).
 * @param object $moeworksheets the moeworksheets object.
 */
function moeworksheets_delete_attempt($attempt, $moeworksheets) {
    global $DB;
    if (is_numeric($attempt)) {
        if (!$attempt = $DB->get_record('moeworksheets_attempts', array('id' => $attempt))) {
            return;
        }
    }

    if ($attempt->moeworksheets != $moeworksheets->id) {
        debugging("Trying to delete attempt $attempt->id which belongs to moeworksheets $attempt->moeworksheets " .
                "but was passed moeworksheets $moeworksheets->id.");
        return;
    }

    if (!isset($moeworksheets->cmid)) {
        $cm = get_coursemodule_from_instance('moeworksheets', $moeworksheets->id, $moeworksheets->course);
        $moeworksheets->cmid = $cm->id;
    }

    question_engine::delete_questions_usage_by_activity($attempt->uniqueid);
    $DB->delete_records('moeworksheets_attempts', array('id' => $attempt->id));

    // Log the deletion of the attempt if not a preview.
    if (!$attempt->preview) {
        $params = array(
            'objectid' => $attempt->id,
            'relateduserid' => $attempt->userid,
            'context' => context_module::instance($moeworksheets->cmid),
            'other' => array(
                'moeworksheetsid' => $moeworksheets->id
            )
        );
        $event = \mod_moeworksheets\event\attempt_deleted::create($params);
        $event->add_record_snapshot('moeworksheets_attempts', $attempt);
        $event->trigger();
    }

    // Search moeworksheets_attempts for other instances by this user.
    // If none, then delete record for this moeworksheets, this user from moeworksheets_grades
    // else recalculate best grade.
    $userid = $attempt->userid;
    if (!$DB->record_exists('moeworksheets_attempts', array('userid' => $userid, 'moeworksheets' => $moeworksheets->id))) {
        $DB->delete_records('moeworksheets_grades', array('userid' => $userid, 'moeworksheets' => $moeworksheets->id));
    } else {
        moeworksheets_save_best_grade($moeworksheets, $userid);
    }

    moeworksheets_update_grades($moeworksheets, $userid);
}

/**
 * Delete all the preview attempts at a moeworksheets, or possibly all the attempts belonging
 * to one user.
 * @param object $moeworksheets the moeworksheets object.
 * @param int $userid (optional) if given, only delete the previews belonging to this user.
 */
function moeworksheets_delete_previews($moeworksheets, $userid = null) {
    global $DB;
    $conditions = array('moeworksheets' => $moeworksheets->id, 'preview' => 1);
    if (!empty($userid)) {
        $conditions['userid'] = $userid;
    }
    $previewattempts = $DB->get_records('moeworksheets_attempts', $conditions);
    foreach ($previewattempts as $attempt) {
        moeworksheets_delete_attempt($attempt, $moeworksheets);
    }
}

/**
 * @param int $moeworksheetsid The moeworksheets id.
 * @return bool whether this moeworksheets has any (non-preview) attempts.
 */
function moeworksheets_has_attempts($moeworksheetsid) {
    global $DB;
    return $DB->record_exists('moeworksheets_attempts', array('moeworksheets' => $moeworksheetsid, 'preview' => 0));
}

// Functions to do with moeworksheets layout and pages //////////////////////////////////

/**
 * Repaginate the questions in a moeworksheets
 * @param int $moeworksheetsid the id of the moeworksheets to repaginate.
 * @param int $slotsperpage number of items to put on each page. 0 means unlimited.
 */
function moeworksheets_repaginate_questions($moeworksheetsid, $slotsperpage) {
    global $DB;
    $trans = $DB->start_delegated_transaction();

    $sections = $DB->get_records('moeworksheets_sections', array('moeworksheetsid' => $moeworksheetsid), 'firstslot ASC');
    $firstslots = array();
    foreach ($sections as $section) {
        if ((int)$section->firstslot === 1) {
            continue;
        }
        $firstslots[] = $section->firstslot;
    }

    $slots = $DB->get_records('moeworksheets_slots', array('moeworksheetsid' => $moeworksheetsid),
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
            $DB->set_field('moeworksheets_slots', 'page', $currentpage, array('id' => $slot->id));
        }
        $slotsonthispage += 1;
    }

    $trans->allow_commit();
}

// Functions to do with moeworksheets grades ////////////////////////////////////////////

/**
 * Convert the raw grade stored in $attempt into a grade out of the maximum
 * grade for this moeworksheets.
 *
 * @param float $rawgrade the unadjusted grade, fof example $attempt->sumgrades
 * @param object $moeworksheets the moeworksheets object. Only the fields grade, sumgrades and decimalpoints are used.
 * @param bool|string $format whether to format the results for display
 *      or 'question' to format a question grade (different number of decimal places.
 * @return float|string the rescaled grade, or null/the lang string 'notyetgraded'
 *      if the $grade is null.
 */
function moeworksheets_rescale_grade($rawgrade, $moeworksheets, $format = true) {
    if (is_null($rawgrade)) {
        $grade = null;
    } else if ($moeworksheets->sumgrades >= 0.000005) {
        $grade = $rawgrade * $moeworksheets->grade / $moeworksheets->sumgrades;
    } else {
        $grade = 0;
    }
    if ($format === 'question') {
        $grade = moeworksheets_format_question_grade($moeworksheets, $grade);
    } else if ($format) {
        $grade = moeworksheets_format_grade($moeworksheets, $grade);
    }
    return $grade;
}

/**
 * Get the feedback object for this grade on this moeworksheets.
 *
 * @param float $grade a grade on this moeworksheets.
 * @param object $moeworksheets the moeworksheets settings.
 * @return false|stdClass the record object or false if there is not feedback for the given grade
 * @since  Moodle 3.1
 */
function moeworksheets_feedback_record_for_grade($grade, $moeworksheets) {
    global $DB;

    // With CBM etc, it is possible to get -ve grades, which would then not match
    // any feedback. Therefore, we replace -ve grades with 0.
    $grade = max($grade, 0);

    $feedback = $DB->get_record_select('moeworksheets_feedback',
            'moeworksheetsid = ? AND mingrade <= ? AND ? < maxgrade', array($moeworksheets->id, $grade, $grade));

    return $feedback;
}

/**
 * Get the feedback text that should be show to a student who
 * got this grade on this moeworksheets. The feedback is processed ready for diplay.
 *
 * @param float $grade a grade on this moeworksheets.
 * @param object $moeworksheets the moeworksheets settings.
 * @param object $context the moeworksheets context.
 * @return string the comment that corresponds to this grade (empty string if there is not one.
 */
function moeworksheets_feedback_for_grade($grade, $moeworksheets, $context) {

    if (is_null($grade)) {
        return '';
    }

    $feedback = moeworksheets_feedback_record_for_grade($grade, $moeworksheets);

    if (empty($feedback->feedbacktext)) {
        return '';
    }

    // Clean the text, ready for display.
    $formatoptions = new stdClass();
    $formatoptions->noclean = true;
    $feedbacktext = file_rewrite_pluginfile_urls($feedback->feedbacktext, 'pluginfile.php',
            $context->id, 'mod_moeworksheets', 'feedback', $feedback->id);
    $feedbacktext = format_text($feedbacktext, $feedback->feedbacktextformat, $formatoptions);

    return $feedbacktext;
}

/**
 * @param object $moeworksheets the moeworksheets database row.
 * @return bool Whether this moeworksheets has any non-blank feedback text.
 */
function moeworksheets_has_feedback($moeworksheets) {
    global $DB;
    static $cache = array();
    if (!array_key_exists($moeworksheets->id, $cache)) {
        $cache[$moeworksheets->id] = moeworksheets_has_grades($moeworksheets) &&
                $DB->record_exists_select('moeworksheets_feedback', "moeworksheetsid = ? AND " .
                    $DB->sql_isnotempty('moeworksheets_feedback', 'feedbacktext', false, true),
                array($moeworksheets->id));
    }
    return $cache[$moeworksheets->id];
}

/**
 * Update the sumgrades field of the moeworksheets. This needs to be called whenever
 * the grading structure of the moeworksheets is changed. For example if a question is
 * added or removed, or a question weight is changed.
 *
 * You should call {@link moeworksheets_delete_previews()} before you call this function.
 *
 * @param object $moeworksheets a moeworksheets.
 */
function moeworksheets_update_sumgrades($moeworksheets) {
    global $DB;

    $sql = 'UPDATE {moeworksheets}
            SET sumgrades = COALESCE((
                SELECT SUM(maxmark)
                FROM {moeworksheets_slots}
                WHERE moeworksheetsid = {moeworksheets}.id
            ), 0)
            WHERE id = ?';
    $DB->execute($sql, array($moeworksheets->id));
    $moeworksheets->sumgrades = $DB->get_field('moeworksheets', 'sumgrades', array('id' => $moeworksheets->id));

    if ($moeworksheets->sumgrades < 0.000005 && moeworksheets_has_attempts($moeworksheets->id)) {
        // If the moeworksheets has been attempted, and the sumgrades has been
        // set to 0, then we must also set the maximum possible grade to 0, or
        // we will get a divide by zero error.
        moeworksheets_set_grade(0, $moeworksheets);
    }
}

/**
 * Update the sumgrades field of the attempts at a moeworksheets.
 *
 * @param object $moeworksheets a moeworksheets.
 */
function moeworksheets_update_all_attempt_sumgrades($moeworksheets) {
    global $DB;
    $dm = new question_engine_data_mapper();
    $timenow = time();

    $sql = "UPDATE {moeworksheets_attempts}
            SET
                timemodified = :timenow,
                sumgrades = (
                    {$dm->sum_usage_marks_subquery('uniqueid')}
                )
            WHERE moeworksheets = :moeworksheetsid AND state = :finishedstate";
    $DB->execute($sql, array('timenow' => $timenow, 'moeworksheetsid' => $moeworksheets->id,
            'finishedstate' => moeworksheets_attempt::FINISHED));
}

/**
 * The moeworksheets grade is the maximum that student's results are marked out of. When it
 * changes, the corresponding data in moeworksheets_grades and moeworksheets_feedback needs to be
 * rescaled. After calling this function, you probably need to call
 * moeworksheets_update_all_attempt_sumgrades, moeworksheets_update_all_final_grades and
 * moeworksheets_update_grades.
 *
 * @param float $newgrade the new maximum grade for the moeworksheets.
 * @param object $moeworksheets the moeworksheets we are updating. Passed by reference so its
 *      grade field can be updated too.
 * @return bool indicating success or failure.
 */
function moeworksheets_set_grade($newgrade, $moeworksheets) {
    global $DB;
    // This is potentially expensive, so only do it if necessary.
    if (abs($moeworksheets->grade - $newgrade) < 1e-7) {
        // Nothing to do.
        return true;
    }

    $oldgrade = $moeworksheets->grade;
    $moeworksheets->grade = $newgrade;

    // Use a transaction, so that on those databases that support it, this is safer.
    $transaction = $DB->start_delegated_transaction();

    // Update the moeworksheets table.
    $DB->set_field('moeworksheets', 'grade', $newgrade, array('id' => $moeworksheets->instance));

    if ($oldgrade < 1) {
        // If the old grade was zero, we cannot rescale, we have to recompute.
        // We also recompute if the old grade was too small to avoid underflow problems.
        moeworksheets_update_all_final_grades($moeworksheets);

    } else {
        // We can rescale the grades efficiently.
        $timemodified = time();
        $DB->execute("
                UPDATE {moeworksheets_grades}
                SET grade = ? * grade, timemodified = ?
                WHERE moeworksheets = ?
        ", array($newgrade/$oldgrade, $timemodified, $moeworksheets->id));
    }

    if ($oldgrade > 1e-7) {
        // Update the moeworksheets_feedback table.
        $factor = $newgrade/$oldgrade;
        $DB->execute("
                UPDATE {moeworksheets_feedback}
                SET mingrade = ? * mingrade, maxgrade = ? * maxgrade
                WHERE moeworksheetsid = ?
        ", array($factor, $factor, $moeworksheets->id));
    }

    // Update grade item and send all grades to gradebook.
    moeworksheets_grade_item_update($moeworksheets);
    moeworksheets_update_grades($moeworksheets);

    $transaction->allow_commit();
    return true;
}

/**
 * Save the overall grade for a user at a moeworksheets in the moeworksheets_grades table
 *
 * @param object $moeworksheets The moeworksheets for which the best grade is to be calculated and then saved.
 * @param int $userid The userid to calculate the grade for. Defaults to the current user.
 * @param array $attempts The attempts of this user. Useful if you are
 * looping through many users. Attempts can be fetched in one master query to
 * avoid repeated querying.
 * @return bool Indicates success or failure.
 */
function moeworksheets_save_best_grade($moeworksheets, $userid = null, $attempts = array()) {
    global $DB, $OUTPUT, $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    if (!$attempts) {
        // Get all the attempts made by the user.
        $attempts = moeworksheets_get_user_attempts($moeworksheets->id, $userid);
    }

    // Calculate the best grade.
    $bestgrade = moeworksheets_calculate_best_grade($moeworksheets, $attempts);
    $bestgrade = moeworksheets_rescale_grade($bestgrade, $moeworksheets, false);

    // Save the best grade in the database.
    if (is_null($bestgrade)) {
        $DB->delete_records('moeworksheets_grades', array('moeworksheets' => $moeworksheets->id, 'userid' => $userid));

    } else if ($grade = $DB->get_record('moeworksheets_grades',
            array('moeworksheets' => $moeworksheets->id, 'userid' => $userid))) {
        $grade->grade = $bestgrade;
        $grade->timemodified = time();
        $DB->update_record('moeworksheets_grades', $grade);

    } else {
        $grade = new stdClass();
        $grade->moeworksheets = $moeworksheets->id;
        $grade->userid = $userid;
        $grade->grade = $bestgrade;
        $grade->timemodified = time();
        $DB->insert_record('moeworksheets_grades', $grade);
    }

    moeworksheets_update_grades($moeworksheets, $userid);
}

/**
 * Calculate the overall grade for a moeworksheets given a number of attempts by a particular user.
 *
 * @param object $moeworksheets    the moeworksheets settings object.
 * @param array $attempts an array of all the user's attempts at this moeworksheets in order.
 * @return float          the overall grade
 */
function moeworksheets_calculate_best_grade($moeworksheets, $attempts) {

    switch ($moeworksheets->grademethod) {

        case moeworksheets_ATTEMPTFIRST:
            $firstattempt = reset($attempts);
            return $firstattempt->sumgrades;

        case moeworksheets_ATTEMPTLAST:
            $lastattempt = end($attempts);
            return $lastattempt->sumgrades;

        case moeworksheets_GRADEAVERAGE:
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

        case moeworksheets_GRADEHIGHEST:
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
 * Update the final grade at this moeworksheets for all students.
 *
 * This function is equivalent to calling moeworksheets_save_best_grade for all
 * users, but much more efficient.
 *
 * @param object $moeworksheets the moeworksheets settings.
 */
function moeworksheets_update_all_final_grades($moeworksheets) {
    global $DB;

    if (!$moeworksheets->sumgrades) {
        return;
    }

    $param = array('imoeworksheetsid' => $moeworksheets->id, 'istatefinished' => moeworksheets_attempt::FINISHED);
    $firstlastattemptjoin = "JOIN (
            SELECT
                imoeworksheetsa.userid,
                MIN(attempt) AS firstattempt,
                MAX(attempt) AS lastattempt

            FROM {moeworksheets_attempts} imoeworksheetsa

            WHERE
                imoeworksheetsa.state = :istatefinished AND
                imoeworksheetsa.preview = 0 AND
                imoeworksheetsa.moeworksheets = :imoeworksheetsid

            GROUP BY imoeworksheetsa.userid
        ) first_last_attempts ON first_last_attempts.userid = moeworksheetsa.userid";

    switch ($moeworksheets->grademethod) {
        case moeworksheets_ATTEMPTFIRST:
            // Because of the where clause, there will only be one row, but we
            // must still use an aggregate function.
            $select = 'MAX(moeworksheetsa.sumgrades)';
            $join = $firstlastattemptjoin;
            $where = 'moeworksheetsa.attempt = first_last_attempts.firstattempt AND';
            break;

        case moeworksheets_ATTEMPTLAST:
            // Because of the where clause, there will only be one row, but we
            // must still use an aggregate function.
            $select = 'MAX(moeworksheetsa.sumgrades)';
            $join = $firstlastattemptjoin;
            $where = 'moeworksheetsa.attempt = first_last_attempts.lastattempt AND';
            break;

        case moeworksheets_GRADEAVERAGE:
            $select = 'AVG(moeworksheetsa.sumgrades)';
            $join = '';
            $where = '';
            break;

        default:
        case moeworksheets_GRADEHIGHEST:
            $select = 'MAX(moeworksheetsa.sumgrades)';
            $join = '';
            $where = '';
            break;
    }

    if ($moeworksheets->sumgrades >= 0.000005) {
        $finalgrade = $select . ' * ' . ($moeworksheets->grade / $moeworksheets->sumgrades);
    } else {
        $finalgrade = '0';
    }
    $param['moeworksheetsid'] = $moeworksheets->id;
    $param['moeworksheetsid2'] = $moeworksheets->id;
    $param['moeworksheetsid3'] = $moeworksheets->id;
    $param['moeworksheetsid4'] = $moeworksheets->id;
    $param['statefinished'] = moeworksheets_attempt::FINISHED;
    $param['statefinished2'] = moeworksheets_attempt::FINISHED;
    $finalgradesubquery = "
            SELECT moeworksheetsa.userid, $finalgrade AS newgrade
            FROM {moeworksheets_attempts} moeworksheetsa
            $join
            WHERE
                $where
                moeworksheetsa.state = :statefinished AND
                moeworksheetsa.preview = 0 AND
                moeworksheetsa.moeworksheets = :moeworksheetsid3
            GROUP BY moeworksheetsa.userid";

    $changedgrades = $DB->get_records_sql("
            SELECT users.userid, qg.id, qg.grade, newgrades.newgrade

            FROM (
                SELECT userid
                FROM {moeworksheets_grades} qg
                WHERE moeworksheets = :moeworksheetsid
            UNION
                SELECT DISTINCT userid
                FROM {moeworksheets_attempts} moeworksheetsa2
                WHERE
                    moeworksheetsa2.state = :statefinished2 AND
                    moeworksheetsa2.preview = 0 AND
                    moeworksheetsa2.moeworksheets = :moeworksheetsid2
            ) users

            LEFT JOIN {moeworksheets_grades} qg ON qg.userid = users.userid AND qg.moeworksheets = :moeworksheetsid4

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
            $toinsert->moeworksheets = $moeworksheets->id;
            $toinsert->userid = $changedgrade->userid;
            $toinsert->timemodified = $timenow;
            $toinsert->grade = $changedgrade->newgrade;
            $DB->insert_record('moeworksheets_grades', $toinsert);

        } else {
            $toupdate = new stdClass();
            $toupdate->id = $changedgrade->id;
            $toupdate->grade = $changedgrade->newgrade;
            $toupdate->timemodified = $timenow;
            $DB->update_record('moeworksheets_grades', $toupdate);
        }
    }

    if (!empty($todelete)) {
        list($test, $params) = $DB->get_in_or_equal($todelete);
        $DB->delete_records_select('moeworksheets_grades', 'moeworksheets = ? AND userid ' . $test,
                array_merge(array($moeworksheets->id), $params));
    }
}

/**
 * Efficiently update check state time on all open attempts
 *
 * @param array $conditions optional restrictions on which attempts to update
 *                    Allowed conditions:
 *                      courseid => (array|int) attempts in given course(s)
 *                      userid   => (array|int) attempts for given user(s)
 *                      moeworksheetsid   => (array|int) attempts in given moeworksheets(s)
 *                      groupid  => (array|int) moeworksheetszes with some override for given group(s)
 *
 */
function moeworksheets_update_open_attempts(array $conditions) {
    global $DB;

    foreach ($conditions as &$value) {
        if (!is_array($value)) {
            $value = array($value);
        }
    }

    $params = array();
    $wheres = array("moeworksheetsa.state IN ('inprogress', 'overdue')");
    $iwheres = array("imoeworksheetsa.state IN ('inprogress', 'overdue')");

    if (isset($conditions['courseid'])) {
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['courseid'], SQL_PARAMS_NAMED, 'cid');
        $params = array_merge($params, $inparams);
        $wheres[] = "moeworksheetsa.moeworksheets IN (SELECT q.id FROM {moeworksheets} q WHERE q.course $incond)";
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['courseid'], SQL_PARAMS_NAMED, 'icid');
        $params = array_merge($params, $inparams);
        $iwheres[] = "imoeworksheetsa.moeworksheets IN (SELECT q.id FROM {moeworksheets} q WHERE q.course $incond)";
    }

    if (isset($conditions['userid'])) {
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['userid'], SQL_PARAMS_NAMED, 'uid');
        $params = array_merge($params, $inparams);
        $wheres[] = "moeworksheetsa.userid $incond";
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['userid'], SQL_PARAMS_NAMED, 'iuid');
        $params = array_merge($params, $inparams);
        $iwheres[] = "imoeworksheetsa.userid $incond";
    }

    if (isset($conditions['moeworksheetsid'])) {
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['moeworksheetsid'], SQL_PARAMS_NAMED, 'qid');
        $params = array_merge($params, $inparams);
        $wheres[] = "moeworksheetsa.moeworksheets $incond";
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['moeworksheetsid'], SQL_PARAMS_NAMED, 'iqid');
        $params = array_merge($params, $inparams);
        $iwheres[] = "imoeworksheetsa.moeworksheets $incond";
    }

    if (isset($conditions['groupid'])) {
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['groupid'], SQL_PARAMS_NAMED, 'gid');
        $params = array_merge($params, $inparams);
        $wheres[] = "moeworksheetsa.moeworksheets IN (SELECT qo.moeworksheets FROM {moeworksheets_overrides} qo WHERE qo.groupid $incond)";
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['groupid'], SQL_PARAMS_NAMED, 'igid');
        $params = array_merge($params, $inparams);
        $iwheres[] = "imoeworksheetsa.moeworksheets IN (SELECT qo.moeworksheets FROM {moeworksheets_overrides} qo WHERE qo.groupid $incond)";
    }

    // SQL to compute timeclose and timelimit for each attempt:
    $moeworksheetsausersql = moeworksheets_get_attempt_usertime_sql(
            implode("\n                AND ", $iwheres));

    // SQL to compute the new timecheckstate
    $timecheckstatesql = "
          CASE WHEN moeworksheetsauser.usertimelimit = 0 AND moeworksheetsauser.usertimeclose = 0 THEN NULL
               WHEN moeworksheetsauser.usertimelimit = 0 THEN moeworksheetsauser.usertimeclose
               WHEN moeworksheetsauser.usertimeclose = 0 THEN moeworksheetsa.timestart + moeworksheetsauser.usertimelimit
               WHEN moeworksheetsa.timestart + moeworksheetsauser.usertimelimit < moeworksheetsauser.usertimeclose THEN moeworksheetsa.timestart + moeworksheetsauser.usertimelimit
               ELSE moeworksheetsauser.usertimeclose END +
          CASE WHEN moeworksheetsa.state = 'overdue' THEN moeworksheets.graceperiod ELSE 0 END";

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
        $updatesql = "UPDATE {moeworksheets_attempts} moeworksheetsa
                        JOIN {moeworksheets} moeworksheets ON moeworksheets.id = moeworksheetsa.moeworksheets
                        JOIN ( $moeworksheetsausersql ) moeworksheetsauser ON moeworksheetsauser.id = moeworksheetsa.id
                         SET moeworksheetsa.timecheckstate = $timecheckstatesql
                       WHERE $attemptselect";
    } else if ($dbfamily == 'postgres') {
        $updatesql = "UPDATE {moeworksheets_attempts} moeworksheetsa
                         SET timecheckstate = $timecheckstatesql
                        FROM {moeworksheets} moeworksheets, ( $moeworksheetsausersql ) moeworksheetsauser
                       WHERE moeworksheets.id = moeworksheetsa.moeworksheets
                         AND moeworksheetsauser.id = moeworksheetsa.id
                         AND $attemptselect";
    } else if ($dbfamily == 'mssql') {
        $updatesql = "UPDATE moeworksheetsa
                         SET timecheckstate = $timecheckstatesql
                        FROM {moeworksheets_attempts} moeworksheetsa
                        JOIN {moeworksheets} moeworksheets ON moeworksheets.id = moeworksheetsa.moeworksheets
                        JOIN ( $moeworksheetsausersql ) moeworksheetsauser ON moeworksheetsauser.id = moeworksheetsa.id
                       WHERE $attemptselect";
    } else {
        // oracle, sqlite and others
        $updatesql = "UPDATE {moeworksheets_attempts} moeworksheetsa
                         SET timecheckstate = (
                           SELECT $timecheckstatesql
                             FROM {moeworksheets} moeworksheets, ( $moeworksheetsausersql ) moeworksheetsauser
                            WHERE moeworksheets.id = moeworksheetsa.moeworksheets
                              AND moeworksheetsauser.id = moeworksheetsa.id
                         )
                         WHERE $attemptselect";
    }

    $DB->execute($updatesql, $params);
}

/**
 * Returns SQL to compute timeclose and timelimit for every attempt, taking into account user and group overrides.
 *
 * @param string $redundantwhereclauses extra where clauses to add to the subquery
 *      for performance. These can use the table alias imoeworksheetsa for the moeworksheets attempts table.
 * @return string SQL select with columns attempt.id, usertimeclose, usertimelimit.
 */
function moeworksheets_get_attempt_usertime_sql($redundantwhereclauses = '') {
    if ($redundantwhereclauses) {
        $redundantwhereclauses = 'WHERE ' . $redundantwhereclauses;
    }
    // The multiple qgo JOINS are necessary because we want timeclose/timelimit = 0 (unlimited) to supercede
    // any other group override
    $moeworksheetsausersql = "
          SELECT imoeworksheetsa.id,
           COALESCE(MAX(quo.timeclose), MAX(qgo1.timeclose), MAX(qgo2.timeclose), imoeworksheets.timeclose) AS usertimeclose,
           COALESCE(MAX(quo.timelimit), MAX(qgo3.timelimit), MAX(qgo4.timelimit), imoeworksheets.timelimit) AS usertimelimit

           FROM {moeworksheets_attempts} imoeworksheetsa
           JOIN {moeworksheets} imoeworksheets ON imoeworksheets.id = imoeworksheetsa.moeworksheets
      LEFT JOIN {moeworksheets_overrides} quo ON quo.moeworksheets = imoeworksheetsa.moeworksheets AND quo.userid = imoeworksheetsa.userid
      LEFT JOIN {groups_members} gm ON gm.userid = imoeworksheetsa.userid
      LEFT JOIN {moeworksheets_overrides} qgo1 ON qgo1.moeworksheets = imoeworksheetsa.moeworksheets AND qgo1.groupid = gm.groupid AND qgo1.timeclose = 0
      LEFT JOIN {moeworksheets_overrides} qgo2 ON qgo2.moeworksheets = imoeworksheetsa.moeworksheets AND qgo2.groupid = gm.groupid AND qgo2.timeclose > 0
      LEFT JOIN {moeworksheets_overrides} qgo3 ON qgo3.moeworksheets = imoeworksheetsa.moeworksheets AND qgo3.groupid = gm.groupid AND qgo3.timelimit = 0
      LEFT JOIN {moeworksheets_overrides} qgo4 ON qgo4.moeworksheets = imoeworksheetsa.moeworksheets AND qgo4.groupid = gm.groupid AND qgo4.timelimit > 0
          $redundantwhereclauses
       GROUP BY imoeworksheetsa.id, imoeworksheets.id, imoeworksheets.timeclose, imoeworksheets.timelimit";
    return $moeworksheetsausersql;
}

/**
 * Return the attempt with the best grade for a moeworksheets
 *
 * Which attempt is the best depends on $moeworksheets->grademethod. If the grade
 * method is GRADEAVERAGE then this function simply returns the last attempt.
 * @return object         The attempt with the best grade
 * @param object $moeworksheets    The moeworksheets for which the best grade is to be calculated
 * @param array $attempts An array of all the attempts of the user at the moeworksheets
 */
function moeworksheets_calculate_best_attempt($moeworksheets, $attempts) {

    switch ($moeworksheets->grademethod) {

        case moeworksheets_ATTEMPTFIRST:
            foreach ($attempts as $attempt) {
                return $attempt;
            }
            break;

        case moeworksheets_GRADEAVERAGE: // We need to do something with it.
        case moeworksheets_ATTEMPTLAST:
            foreach ($attempts as $attempt) {
                $final = $attempt;
            }
            return $final;

        default:
        case moeworksheets_GRADEHIGHEST:
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
 * @return array int => lang string the options for calculating the moeworksheets grade
 *      from the individual attempt grades.
 */
function moeworksheets_get_grading_options() {
    return array(
        moeworksheets_GRADEHIGHEST => get_string('gradehighest', 'moeworksheets'),
        moeworksheets_GRADEAVERAGE => get_string('gradeaverage', 'moeworksheets'),
        moeworksheets_ATTEMPTFIRST => get_string('attemptfirst', 'moeworksheets'),
        moeworksheets_ATTEMPTLAST  => get_string('attemptlast', 'moeworksheets')
    );
}

/**
 * @param int $option one of the values moeworksheets_GRADEHIGHEST, moeworksheets_GRADEAVERAGE,
 *      moeworksheets_ATTEMPTFIRST or moeworksheets_ATTEMPTLAST.
 * @return the lang string for that option.
 */
function moeworksheets_get_grading_option_name($option) {
    $strings = moeworksheets_get_grading_options();
    return $strings[$option];
}

/**
 * @return array string => lang string the options for handling overdue moeworksheets
 *      attempts.
 */
function moeworksheets_get_overdue_handling_options() {
    return array(
        'autosubmit'  => get_string('overduehandlingautosubmit', 'moeworksheets'),
        'graceperiod' => get_string('overduehandlinggraceperiod', 'moeworksheets'),
        'autoabandon' => get_string('overduehandlingautoabandon', 'moeworksheets'),
    );
}

/**
 * Get the choices for what size user picture to show.
 * @return array string => lang string the options for whether to display the user's picture.
 */
function moeworksheets_get_user_image_options() {
    return array(
        moeworksheets_SHOWIMAGE_NONE  => get_string('shownoimage', 'moeworksheets'),
        moeworksheets_SHOWIMAGE_SMALL => get_string('showsmallimage', 'moeworksheets'),
        moeworksheets_SHOWIMAGE_LARGE => get_string('showlargeimage', 'moeworksheets'),
    );
}

/**
 * Get the choices to offer for the 'Questions per page' option.
 * @return array int => string.
 */
function moeworksheets_questions_per_page_options() {
    $pageoptions = array();
    $pageoptions[0] = get_string('neverallononepage', 'moeworksheets');
    $pageoptions[1] = get_string('everyquestion', 'moeworksheets');
    for ($i = 2; $i <= moeworksheets_MAX_QPP_OPTION; ++$i) {
        $pageoptions[$i] = get_string('everynquestions', 'moeworksheets', $i);
    }
    return $pageoptions;
}

/**
 * Get the human-readable name for a moeworksheets attempt state.
 * @param string $state one of the state constants like {@link moeworksheets_attempt::IN_PROGRESS}.
 * @return string The lang string to describe that state.
 */
function moeworksheets_attempt_state_name($state) {
    switch ($state) {
        case moeworksheets_attempt::IN_PROGRESS:
            return get_string('stateinprogress', 'moeworksheets');
        case moeworksheets_attempt::OVERDUE:
            return get_string('stateoverdue', 'moeworksheets');
        case moeworksheets_attempt::FINISHED:
            return get_string('statefinished', 'moeworksheets');
        case moeworksheets_attempt::ABANDONED:
            return get_string('stateabandoned', 'moeworksheets');
        default:
            throw new coding_exception('Unknown moeworksheets attempt state.');
    }
}

// Other moeworksheets functions ////////////////////////////////////////////////////////

/**
 * @param object $moeworksheets the moeworksheets.
 * @param int $cmid the course_module object for this moeworksheets.
 * @param object $question the question.
 * @param string $returnurl url to return to after action is done.
 * @param int $variant which question variant to preview (optional).
 * @return string html for a number of icons linked to action pages for a
 * question - preview and edit / view icons depending on user capabilities.
 */
function moeworksheets_question_action_icons($moeworksheets, $cmid, $question, $returnurl, $variant = null) {
    $html = moeworksheets_question_preview_button($moeworksheets, $question, false, $variant) . ' ' .
            moeworksheets_question_edit_button($cmid, $question, $returnurl);
    return $html;
}

/**
 * @param int $cmid the course_module.id for this moeworksheets.
 * @param object $question the question.
 * @param string $returnurl url to return to after action is done.
 * @param string $contentbeforeicon some HTML content to be added inside the link, before the icon.
 * @return the HTML for an edit icon, view icon, or nothing for a question
 *      (depending on permissions).
 */
function moeworksheets_question_edit_button($cmid, $question, $returnurl, $contentaftericon = '') {
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
 * @param object $moeworksheets the moeworksheets settings
 * @param object $question the question
 * @param int $variant which question variant to preview (optional).
 * @return moodle_url to preview this question with the options from this moeworksheets.
 */
function moeworksheets_question_preview_url($moeworksheets, $question, $variant = null) {
    // Get the appropriate display options.
    $displayoptions = mod_moeworksheets_display_options::make_from_moeworksheets($moeworksheets,
            mod_moeworksheets_display_options::DURING);

    $maxmark = null;
    if (isset($question->maxmark)) {
        $maxmark = $question->maxmark;
    }

    // Work out the correcte preview URL.
    return question_preview_url($question->id, $moeworksheets->preferredbehaviour,
            $maxmark, $displayoptions, $variant);
}

/**
 * @param object $moeworksheets the moeworksheets settings
 * @param object $question the question
 * @param bool $label if true, show the preview question label after the icon
 * @param int $variant which question variant to preview (optional).
 * @return the HTML for a preview question icon.
 */
function moeworksheets_question_preview_button($moeworksheets, $question, $label = false, $variant = null) {
    global $PAGE;
    if (!question_has_capability_on($question, 'use', $question->category)) {
        return '';
    }

    return $PAGE->get_renderer('mod_moeworksheets', 'edit')->question_preview_icon($moeworksheets, $question, $label, $variant);
}

/**
 * @param object $attempt the attempt.
 * @param object $context the moeworksheets context.
 * @return int whether flags should be shown/editable to the current user for this attempt.
 */
function moeworksheets_get_flag_option($attempt, $context) {
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
 * Work out what state this moeworksheets attempt is in - in the sense used by
 * moeworksheets_get_review_options, not in the sense of $attempt->state.
 * @param object $moeworksheets the moeworksheets settings
 * @param object $attempt the moeworksheets_attempt database row.
 * @return int one of the mod_moeworksheets_display_options::DURING,
 *      IMMEDIATELY_AFTER, LATER_WHILE_OPEN or AFTER_CLOSE constants.
 */
function moeworksheets_attempt_state($moeworksheets, $attempt) {
    if ($attempt->state == moeworksheets_attempt::IN_PROGRESS) {
        return mod_moeworksheets_display_options::DURING;
    } else if (time() < $attempt->timefinish + 120) {
        return mod_moeworksheets_display_options::IMMEDIATELY_AFTER;
    } else if (!$moeworksheets->timeclose || time() < $moeworksheets->timeclose) {
        return mod_moeworksheets_display_options::LATER_WHILE_OPEN;
    } else {
        return mod_moeworksheets_display_options::AFTER_CLOSE;
    }
}

/**
 * The the appropraite mod_moeworksheets_display_options object for this attempt at this
 * moeworksheets right now.
 *
 * @param object $moeworksheets the moeworksheets instance.
 * @param object $attempt the attempt in question.
 * @param $context the moeworksheets context.
 *
 * @return mod_moeworksheets_display_options
 */
function moeworksheets_get_review_options($moeworksheets, $attempt, $context) {
    $options = mod_moeworksheets_display_options::make_from_moeworksheets($moeworksheets, moeworksheets_attempt_state($moeworksheets, $attempt));

    $options->readonly = true;
    $options->flags = moeworksheets_get_flag_option($attempt, $context);
    if (!empty($attempt->id)) {
        $options->questionreviewlink = new moodle_url('/mod/moeworksheets/reviewquestion.php',
                array('attempt' => $attempt->id));
    }

    // Show a link to the comment box only for closed attempts.
    if (!empty($attempt->id) && $attempt->state == moeworksheets_attempt::FINISHED && !$attempt->preview &&
            !is_null($context) && has_capability('mod/moeworksheets:grade', $context)) {
        $options->manualcomment = question_display_options::VISIBLE;
        $options->manualcommentlink = new moodle_url('/mod/moeworksheets/comment.php',
                array('attempt' => $attempt->id));
    }

    if (!is_null($context) && !$attempt->preview &&
            has_capability('mod/moeworksheets:viewreports', $context) &&
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
 * Combines the review options from a number of different moeworksheets attempts.
 * Returns an array of two ojects, so the suggested way of calling this
 * funciton is:
 * list($someoptions, $alloptions) = moeworksheets_get_combined_reviewoptions(...)
 *
 * @param object $moeworksheets the moeworksheets instance.
 * @param array $attempts an array of attempt objects.
 *
 * @return array of two options objects, one showing which options are true for
 *          at least one of the attempts, the other showing which options are true
 *          for all attempts.
 */
function moeworksheets_get_combined_reviewoptions($moeworksheets, $attempts) {
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
        $attemptoptions = mod_moeworksheets_display_options::make_from_moeworksheets($moeworksheets,
                moeworksheets_attempt_state($moeworksheets, $attempt));
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
function moeworksheets_send_confirmation($recipient, $a) {

    // Add information about the recipient to $a.
    // Don't do idnumber. we want idnumber to be the submitter's idnumber.
    $a->username     = fullname($recipient);
    $a->userusername = $recipient->username;

    // Prepare the message.
    $eventdata = new stdClass();
    $eventdata->component         = 'mod_moeworksheets';
    $eventdata->name              = 'confirmation';
    $eventdata->notification      = 1;

    $eventdata->userfrom          = core_user::get_noreply_user();
    $eventdata->userto            = $recipient;
    $eventdata->subject           = get_string('emailconfirmsubject', 'moeworksheets', $a);
    $eventdata->fullmessage       = get_string('emailconfirmbody', 'moeworksheets', $a);
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml   = '';

    $eventdata->smallmessage      = get_string('emailconfirmsmall', 'moeworksheets', $a);
    $eventdata->contexturl        = $a->moeworksheetsurl;
    $eventdata->contexturlname    = $a->moeworksheetsname;

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
function moeworksheets_send_notification($recipient, $submitter, $a) {

    // Recipient info for template.
    $a->useridnumber = $recipient->idnumber;
    $a->username     = fullname($recipient);
    $a->userusername = $recipient->username;

    // Prepare the message.
    $eventdata = new stdClass();
    $eventdata->component         = 'mod_moeworksheets';
    $eventdata->name              = 'submission';
    $eventdata->notification      = 1;

    $eventdata->userfrom          = $submitter;
    $eventdata->userto            = $recipient;
    $eventdata->subject           = get_string('emailnotifysubject', 'moeworksheets', $a);
    $eventdata->fullmessage       = get_string('emailnotifybody', 'moeworksheets', $a);
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml   = '';

    $eventdata->smallmessage      = get_string('emailnotifysmall', 'moeworksheets', $a);
    $eventdata->contexturl        = $a->moeworksheetsreviewurl;
    $eventdata->contexturlname    = $a->moeworksheetsname;

    // ... and send it.
    return message_send($eventdata);
}

/**
 * Send all the requried messages when a moeworksheets attempt is submitted.
 *
 * @param object $course the course
 * @param object $moeworksheets the moeworksheets
 * @param object $attempt this attempt just finished
 * @param object $context the moeworksheets context
 * @param object $cm the coursemodule for this moeworksheets
 *
 * @return bool true if all necessary messages were sent successfully, else false.
 */
function moeworksheets_send_notification_messages($course, $moeworksheets, $attempt, $context, $cm) {
    global $CFG, $DB;

    // Do nothing if required objects not present.
    if (empty($course) or empty($moeworksheets) or empty($attempt) or empty($context)) {
        throw new coding_exception('$course, $moeworksheets, $attempt, $context and $cm must all be set.');
    }

    $submitter = $DB->get_record('user', array('id' => $attempt->userid), '*', MUST_EXIST);

    // Check for confirmation required.
    $sendconfirm = false;
    $notifyexcludeusers = '';
    if (has_capability('mod/moeworksheets:emailconfirmsubmission', $context, $submitter, false)) {
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
        // If the user is not in a group, and the moeworksheets is set to group mode,
        // then set $groups to a non-existant id so that only users with
        // 'moodle/site:accessallgroups' get notified.
        $groups = -1;
    } else {
        $groups = '';
    }
    $userstonotify = get_users_by_capability($context, 'mod/moeworksheets:emailnotifysubmission',
            $notifyfields, '', '', '', $groups, $notifyexcludeusers, false, false, true);

    if (empty($userstonotify) && !$sendconfirm) {
        return true; // Nothing to do.
    }

    $a = new stdClass();
    // Course info.
    $a->coursename      = $course->fullname;
    $a->courseshortname = $course->shortname;
    // moeworksheets info.
    $a->moeworksheetsname        = $moeworksheets->name;
    $a->moeworksheetsreporturl   = $CFG->wwwroot . '/mod/moeworksheets/report.php?id=' . $cm->id;
    $a->moeworksheetsreportlink  = '<a href="' . $a->moeworksheetsreporturl . '">' .
            format_string($moeworksheets->name) . ' report</a>';
    $a->moeworksheetsurl         = $CFG->wwwroot . '/mod/moeworksheets/view.php?id=' . $cm->id;
    $a->moeworksheetslink        = '<a href="' . $a->moeworksheetsurl . '">' . format_string($moeworksheets->name) . '</a>';
    // Attempt info.
    $a->submissiontime  = userdate($attempt->timefinish);
    $a->timetaken       = format_time($attempt->timefinish - $attempt->timestart);
    $a->moeworksheetsreviewurl   = $CFG->wwwroot . '/mod/moeworksheets/review.php?attempt=' . $attempt->id;
    $a->moeworksheetsreviewlink  = '<a href="' . $a->moeworksheetsreviewurl . '">' .
            format_string($moeworksheets->name) . ' review</a>';
    // Student who sat the moeworksheets info.
    $a->studentidnumber = $submitter->idnumber;
    $a->studentname     = fullname($submitter);
    $a->studentusername = $submitter->username;

    $allok = true;

    // Send notifications if required.
    if (!empty($userstonotify)) {
        foreach ($userstonotify as $recipient) {
            $allok = $allok && moeworksheets_send_notification($recipient, $submitter, $a);
        }
    }

    // Send confirmation if required. We send the student confirmation last, so
    // that if message sending is being intermittently buggy, which means we send
    // some but not all messages, and then try again later, then teachers may get
    // duplicate messages, but the student will always get exactly one.
    if ($sendconfirm) {
        $allok = $allok && moeworksheets_send_confirmation($submitter, $a);
    }

    return $allok;
}

/**
 * Send the notification message when a moeworksheets attempt becomes overdue.
 *
 * @param moeworksheets_attempt $attemptobj all the data about the moeworksheets attempt.
 */
function moeworksheets_send_overdue_message($attemptobj) {
    global $CFG, $DB;

    $submitter = $DB->get_record('user', array('id' => $attemptobj->get_userid()), '*', MUST_EXIST);

    if (!$attemptobj->has_capability('mod/moeworksheets:emailwarnoverdue', $submitter->id, false)) {
        return; // Message not required.
    }

    if (!$attemptobj->has_response_to_at_least_one_graded_question()) {
        return; // Message not required.
    }

    // Prepare lots of useful information that admins might want to include in
    // the email message.
    $moeworksheetsname = format_string($attemptobj->get_moeworksheets_name());

    $deadlines = array();
    if ($attemptobj->get_moeworksheets()->timelimit) {
        $deadlines[] = $attemptobj->get_attempt()->timestart + $attemptobj->get_moeworksheets()->timelimit;
    }
    if ($attemptobj->get_moeworksheets()->timeclose) {
        $deadlines[] = $attemptobj->get_moeworksheets()->timeclose;
    }
    $duedate = min($deadlines);
    $graceend = $duedate + $attemptobj->get_moeworksheets()->graceperiod;

    $a = new stdClass();
    // Course info.
    $a->coursename         = format_string($attemptobj->get_course()->fullname);
    $a->courseshortname    = format_string($attemptobj->get_course()->shortname);
    // moeworksheets info.
    $a->moeworksheetsname           = $moeworksheetsname;
    $a->moeworksheetsurl            = $attemptobj->view_url();
    $a->moeworksheetslink           = '<a href="' . $a->moeworksheetsurl . '">' . $moeworksheetsname . '</a>';
    // Attempt info.
    $a->attemptduedate     = userdate($duedate);
    $a->attemptgraceend    = userdate($graceend);
    $a->attemptsummaryurl  = $attemptobj->summary_url()->out(false);
    $a->attemptsummarylink = '<a href="' . $a->attemptsummaryurl . '">' . $moeworksheetsname . ' review</a>';
    // Student's info.
    $a->studentidnumber    = $submitter->idnumber;
    $a->studentname        = fullname($submitter);
    $a->studentusername    = $submitter->username;

    // Prepare the message.
    $eventdata = new stdClass();
    $eventdata->component         = 'mod_moeworksheets';
    $eventdata->name              = 'attempt_overdue';
    $eventdata->notification      = 1;

    $eventdata->userfrom          = core_user::get_noreply_user();
    $eventdata->userto            = $submitter;
    $eventdata->subject           = get_string('emailoverduesubject', 'moeworksheets', $a);
    $eventdata->fullmessage       = get_string('emailoverduebody', 'moeworksheets', $a);
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml   = '';

    $eventdata->smallmessage      = get_string('emailoverduesmall', 'moeworksheets', $a);
    $eventdata->contexturl        = $a->moeworksheetsurl;
    $eventdata->contexturlname    = $a->moeworksheetsname;

    // Send the message.
    return message_send($eventdata);
}

/**
 * Handle the moeworksheets_attempt_submitted event.
 *
 * This sends the confirmation and notification messages, if required.
 *
 * @param object $event the event object.
 */
function moeworksheets_attempt_submitted_handler($event) {
    global $DB;

    $course  = $DB->get_record('course', array('id' => $event->courseid));
    $attempt = $event->get_record_snapshot('moeworksheets_attempts', $event->objectid);
    $moeworksheets    = $event->get_record_snapshot('moeworksheets', $attempt->moeworksheets);
    $cm      = get_coursemodule_from_id('moeworksheets', $event->get_context()->instanceid, $event->courseid);

    if (!($course && $moeworksheets && $cm && $attempt)) {
        // Something has been deleted since the event was raised. Therefore, the
        // event is no longer relevant.
        return true;
    }

    // Update completion state.
    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) && ($moeworksheets->completionattemptsexhausted || $moeworksheets->completionpass)) {
        $completion->update_state($cm, COMPLETION_COMPLETE, $event->userid);
    }
    return moeworksheets_send_notification_messages($course, $moeworksheets, $attempt,
            context_module::instance($cm->id), $cm);
}

/**
 * Handle groups_member_added event
 *
 * @param object $event the event object.
 * @deprecated since 2.6, see {@link \mod_moeworksheets\group_observers::group_member_added()}.
 */
function moeworksheets_groups_member_added_handler($event) {
    debugging('moeworksheets_groups_member_added_handler() is deprecated, please use ' .
        '\mod_moeworksheets\group_observers::group_member_added() instead.', DEBUG_DEVELOPER);
    moeworksheets_update_open_attempts(array('userid'=>$event->userid, 'groupid'=>$event->groupid));
}

/**
 * Handle groups_member_removed event
 *
 * @param object $event the event object.
 * @deprecated since 2.6, see {@link \mod_moeworksheets\group_observers::group_member_removed()}.
 */
function moeworksheets_groups_member_removed_handler($event) {
    debugging('moeworksheets_groups_member_removed_handler() is deprecated, please use ' .
        '\mod_moeworksheets\group_observers::group_member_removed() instead.', DEBUG_DEVELOPER);
    moeworksheets_update_open_attempts(array('userid'=>$event->userid, 'groupid'=>$event->groupid));
}

/**
 * Handle groups_group_deleted event
 *
 * @param object $event the event object.
 * @deprecated since 2.6, see {@link \mod_moeworksheets\group_observers::group_deleted()}.
 */
function moeworksheets_groups_group_deleted_handler($event) {
    global $DB;
    debugging('moeworksheets_groups_group_deleted_handler() is deprecated, please use ' .
        '\mod_moeworksheets\group_observers::group_deleted() instead.', DEBUG_DEVELOPER);
    moeworksheets_process_group_deleted_in_course($event->courseid);
}

/**
 * Logic to happen when a/some group(s) has/have been deleted in a course.
 *
 * @param int $courseid The course ID.
 * @return void
 */
function moeworksheets_process_group_deleted_in_course($courseid) {
    global $DB;

    // It would be nice if we got the groupid that was deleted.
    // Instead, we just update all moeworksheetszes with orphaned group overrides.
    $sql = "SELECT o.id, o.moeworksheets
              FROM {moeworksheets_overrides} o
              JOIN {moeworksheets} moeworksheets ON moeworksheets.id = o.moeworksheets
         LEFT JOIN {groups} grp ON grp.id = o.groupid
             WHERE moeworksheets.course = :courseid
               AND o.groupid IS NOT NULL
               AND grp.id IS NULL";
    $params = array('courseid' => $courseid);
    $records = $DB->get_records_sql_menu($sql, $params);
    if (!$records) {
        return; // Nothing to do.
    }
    $DB->delete_records_list('moeworksheets_overrides', 'id', array_keys($records));
    moeworksheets_update_open_attempts(array('moeworksheetsid' => array_unique(array_values($records))));
}

/**
 * Handle groups_members_removed event
 *
 * @param object $event the event object.
 * @deprecated since 2.6, see {@link \mod_moeworksheets\group_observers::group_member_removed()}.
 */
function moeworksheets_groups_members_removed_handler($event) {
    debugging('moeworksheets_groups_members_removed_handler() is deprecated, please use ' .
        '\mod_moeworksheets\group_observers::group_member_removed() instead.', DEBUG_DEVELOPER);
    if ($event->userid == 0) {
        moeworksheets_update_open_attempts(array('courseid'=>$event->courseid));
    } else {
        moeworksheets_update_open_attempts(array('courseid'=>$event->courseid, 'userid'=>$event->userid));
    }
}

/**
 * Get the information about the standard moeworksheets JavaScript module.
 * @return array a standard jsmodule structure.
 */
function moeworksheets_get_js_module() {
    global $PAGE;

    return array(
        'name' => 'mod_moeworksheets',
        'fullpath' => '/mod/moeworksheets/module.js',
        'requires' => array('base', 'dom', 'event-delegate', 'event-key',
                'core_question_engine', 'moodle-core-formchangechecker'),
        'strings' => array(
            array('cancel', 'moodle'),
            array('flagged', 'question'),
            array('functiondisabledbysecuremode', 'moeworksheets'),
            array('startattempt', 'moeworksheets'),
            array('timesup', 'moeworksheets'),
            array('changesmadereallygoaway', 'moodle'),
        ),
    );
}


/**
 * An extension of question_display_options that includes the extra options used
 * by the moeworksheets.
 *
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_moeworksheets_display_options extends question_display_options {
    /**#@+
     * @var integer bits used to indicate various times in relation to a
     * moeworksheets attempt.
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
     * Set up the various options from the moeworksheets settings, and a time constant.
     * @param object $moeworksheets the moeworksheets settings.
     * @param int $one of the {@link DURING}, {@link IMMEDIATELY_AFTER},
     * {@link LATER_WHILE_OPEN} or {@link AFTER_CLOSE} constants.
     * @return mod_moeworksheets_display_options set up appropriately.
     */
    public static function make_from_moeworksheets($moeworksheets, $when) {
        $options = new self();

        $options->attempt = self::extract($moeworksheets->reviewattempt, $when, true, false);
        $options->correctness = self::extract($moeworksheets->reviewcorrectness, $when);
        $options->marks = self::extract($moeworksheets->reviewmarks, $when,
                self::MARK_AND_MAX, self::MAX_ONLY);
        $options->feedback = self::extract($moeworksheets->reviewspecificfeedback, $when);
        $options->generalfeedback = self::extract($moeworksheets->reviewgeneralfeedback, $when);
        $options->rightanswer = self::extract($moeworksheets->reviewrightanswer, $when);
        $options->overallfeedback = self::extract($moeworksheets->reviewoverallfeedback, $when);

        $options->numpartscorrect = $options->feedback;
        $options->manualcomment = $options->feedback;

        if ($moeworksheets->questiondecimalpoints != -1) {
            $options->markdp = $moeworksheets->questiondecimalpoints;
        } else {
            $options->markdp = $moeworksheets->decimalpoints;
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
 * a particular moeworksheets.
 *
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qubaids_for_moeworksheets extends qubaid_join {
    public function __construct($moeworksheetsid, $includepreviews = true, $onlyfinished = false) {
        $where = 'moeworksheetsa.moeworksheets = :moeworksheetsamoeworksheets';
        $params = array('moeworksheetsamoeworksheets' => $moeworksheetsid);

        if (!$includepreviews) {
            $where .= ' AND preview = 0';
        }

        if ($onlyfinished) {
            $where .= ' AND state == :statefinished';
            $params['statefinished'] = moeworksheets_attempt::FINISHED;
        }

        parent::__construct('{moeworksheets_attempts} moeworksheetsa', 'moeworksheetsa.uniqueid', $where, $params);
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
function moeworksheets_question_tostring($question, $showicon = false, $showquestiontext = true) {
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
function moeworksheets_require_question_use($questionid) {
    global $DB;
    $question = $DB->get_record('question', array('id' => $questionid), '*', MUST_EXIST);
    question_require_capability_on($question, 'use');
}

/**
 * Verify that the question exists, and the user has permission to use it.
 * @param object $moeworksheets the moeworksheets settings.
 * @param int $slot which question in the moeworksheets to test.
 * @return bool whether the user can use this question.
 */
function moeworksheets_has_question_use($moeworksheets, $slot) {
    global $DB;
    $question = $DB->get_record_sql("
            SELECT q.*
              FROM {moeworksheets_slots} slot
              JOIN {question} q ON q.id = slot.questionid
             WHERE slot.moeworksheetsid = ? AND slot.slot = ?", array($moeworksheets->id, $slot));
    if (!$question) {
        return false;
    }
    return question_has_capability_on($question, 'use');
}

/**
 * Add a question to a moeworksheets
 *
 * Adds a question to a moeworksheets by updating $moeworksheets as well as the
 * moeworksheets and moeworksheets_slots tables. It also adds a page break if required.
 * @param int $questionid The id of the question to be added
 * @param object $moeworksheets The extended moeworksheets object as used by edit.php
 *      This is updated by this function
 * @param int $page Which page in moeworksheets to add the question on. If 0 (default),
 *      add at the end
 * @param float $maxmark The maximum mark to set for this question. (Optional,
 *      defaults to question.defaultmark.
 * @return bool false if the question was already in the moeworksheets
 */
function moeworksheets_add_moeworksheets_question($questionid, $moeworksheets, $page = 0, $maxmark = null) {
    global $DB;
    $slots = $DB->get_records('moeworksheets_slots', array('moeworksheetsid' => $moeworksheets->id),
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
    $slot->moeworksheetsid = $moeworksheets->id;
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
                $DB->set_field('moeworksheets_slots', 'slot', $otherslot->slot + 1, array('id' => $otherslot->id));
            } else {
                $lastslotbefore = $otherslot->slot;
                break;
            }
        }
        $slot->slot = $lastslotbefore + 1;
        $slot->page = min($page, $maxpage + 1);

        $DB->execute("
                UPDATE {moeworksheets_sections}
                   SET firstslot = firstslot + 1
                 WHERE moeworksheetsid = ?
                   AND firstslot > ?
                 ORDER BY firstslot DESC
                ", array($moeworksheets->id, max($lastslotbefore, 1)));

    } else {
        $lastslot = end($slots);
        if ($lastslot) {
            $slot->slot = $lastslot->slot + 1;
        } else {
            $slot->slot = 1;
        }
        if ($moeworksheets->questionsperpage && $numonlastpage >= $moeworksheets->questionsperpage) {
            $slot->page = $maxpage + 1;
        } else {
            $slot->page = $maxpage;
        }
    }

    $DB->insert_record('moeworksheets_slots', $slot);
    $trans->allow_commit();
}

/**
 * Add a random question to the moeworksheets at a given point.
 * @param object $moeworksheets the moeworksheets settings.
 * @param int $addonpage the page on which to add the question.
 * @param int $categoryid the question category to add the question from.
 * @param int $number the number of random questions to add.
 * @param bool $includesubcategories whether to include questoins from subcategories.
 */
function moeworksheets_add_random_questions($moeworksheets, $addonpage, $categoryid, $number,
        $includesubcategories) {
    global $DB;

    $category = $DB->get_record('question_categories', array('id' => $categoryid));
    if (!$category) {
        print_error('invalidcategoryid', 'error');
    }

    $catcontext = context::instance_by_id($category->contextid);
    require_capability('moodle/question:useall', $catcontext);

    // Find existing random questions in this category that are
    // not used by any moeworksheets.
    if ($existingquestions = $DB->get_records_sql(
            "SELECT q.id, q.qtype FROM {question} q
            WHERE qtype = 'random'
                AND category = ?
                AND " . $DB->sql_compare_text('questiontext') . " = ?
                AND NOT EXISTS (
                        SELECT *
                          FROM {moeworksheets_slots}
                         WHERE questionid = q.id)
            ORDER BY id", array($category->id, ($includesubcategories ? '1' : '0')))) {
            // Take as many of these as needed.
        while (($existingquestion = array_shift($existingquestions)) && $number > 0) {
            moeworksheets_add_moeworksheets_question($existingquestion->id, $moeworksheets, $addonpage);
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
            print_error('cannotinsertrandomquestion', 'moeworksheets');
        }
        moeworksheets_add_moeworksheets_question($question->id, $moeworksheets, $addonpage);
    }
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $moeworksheets       moeworksheets object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.1
 */
function moeworksheets_view($moeworksheets, $course, $cm, $context) {

    $params = array(
        'objectid' => $moeworksheets->id,
        'context' => $context
    );

    $event = \mod_moeworksheets\event\course_module_viewed::create($params);
    $event->add_record_snapshot('moeworksheets', $moeworksheets);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

/**
 * Validate permissions for creating a new attempt and start a new preview attempt if required.
 *
 * @param  moeworksheets $moeworksheetsobj moeworksheets object
 * @param  moeworksheets_access_manager $accessmanager moeworksheets access manager
 * @param  bool $forcenew whether was required to start a new preview attempt
 * @param  int $page page to jump to in the attempt
 * @param  bool $redirect whether to redirect or throw exceptions (for web or ws usage)
 * @return array an array containing the attempt information, access error messages and the page to jump to in the attempt
 * @throws moodle_moeworksheets_exception
 * @since Moodle 3.1
 */
function moeworksheets_validate_new_attempt(moeworksheets $moeworksheetsobj, moeworksheets_access_manager $accessmanager, $forcenew, $page, $redirect) {
    global $DB, $USER;
    $timenow = time();

    if ($moeworksheetsobj->is_preview_user() && $forcenew) {
        $accessmanager->current_attempt_finished();
    }

    // Check capabilities.
    if (!$moeworksheetsobj->is_preview_user()) {
        $moeworksheetsobj->require_capability('mod/moeworksheets:attempt');
    }

    // Check to see if a new preview was requested.
    if ($moeworksheetsobj->is_preview_user() && $forcenew) {
        // To force the creation of a new preview, we mark the current attempt (if any)
        // as finished. It will then automatically be deleted below.
        $DB->set_field('moeworksheets_attempts', 'state', moeworksheets_attempt::FINISHED,
                array('moeworksheets' => $moeworksheetsobj->get_moeworksheetsid(), 'userid' => $USER->id));
    }

    // Look for an existing attempt.
    $attempts = moeworksheets_get_user_attempts($moeworksheetsobj->get_moeworksheetsid(), $USER->id, 'all', true);
    $lastattempt = end($attempts);

    $attemptnumber = null;
    // If an in-progress attempt exists, check password then redirect to it.
    if ($lastattempt && ($lastattempt->state == moeworksheets_attempt::IN_PROGRESS ||
            $lastattempt->state == moeworksheets_attempt::OVERDUE)) {
        $currentattemptid = $lastattempt->id;
        $messages = $accessmanager->prevent_access();

        // If the attempt is now overdue, deal with that.
        $moeworksheetsobj->create_attempt_object($lastattempt)->handle_if_time_expired($timenow, true);

        // And, if the attempt is now no longer in progress, redirect to the appropriate place.
        if ($lastattempt->state == moeworksheets_attempt::ABANDONED || $lastattempt->state == moeworksheets_attempt::FINISHED) {
            if ($redirect) {
                redirect($moeworksheetsobj->review_url($lastattempt->id));
            } else {
                throw new moodle_moeworksheets_exception($moeworksheetsobj, 'attemptalreadyclosed');
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
 * @param  moeworksheets $moeworksheetsobj moeworksheets object
 * @param  int $attemptnumber the attempt number
 * @param  object $lastattempt last attempt object
 * @return object the new attempt
 * @since  Moodle 3.1
 */
function moeworksheets_prepare_and_start_new_attempt(moeworksheets $moeworksheetsobj, $attemptnumber, $lastattempt) {
    global $DB, $USER;

    // Delete any previous preview attempts belonging to this user.
    moeworksheets_delete_previews($moeworksheetsobj->get_moeworksheets(), $USER->id);

    $quba = question_engine::make_questions_usage_by_activity('mod_moeworksheets', $moeworksheetsobj->get_context());
    $quba->set_preferred_behaviour($moeworksheetsobj->get_moeworksheets()->preferredbehaviour);

    // Create the new attempt and initialize the question sessions
    $timenow = time(); // Update time now, in case the server is running really slowly.
    $attempt = moeworksheets_create_attempt($moeworksheetsobj, $attemptnumber, $lastattempt, $timenow, $moeworksheetsobj->is_preview_user());

    if (!($moeworksheetsobj->get_moeworksheets()->attemptonlast && $lastattempt)) {
        $attempt = moeworksheets_start_new_attempt($moeworksheetsobj, $quba, $attempt, $attemptnumber, $timenow);
    } else {
        $attempt = moeworksheets_start_attempt_built_on_last($quba, $attempt, $lastattempt);
    }

    $transaction = $DB->start_delegated_transaction();

    $attempt = moeworksheets_attempt_save_started($moeworksheetsobj, $quba, $attempt);

    $transaction->allow_commit();

    return $attempt;
}
