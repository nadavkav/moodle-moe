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
 * Library of functions for the moeworksheets module.
 *
 * This contains functions that are called also from outside the moeworksheets module
 * Functions that are only called by the moeworksheets module itself are in {@link locallib.php}
 *
 * @package    mod_moeworksheets
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/eventslib.php');
require_once($CFG->dirroot . '/calendar/lib.php');


/**#@+
 * Option controlling what options are offered on the moeworksheets settings form.
 */
define('moeworksheets_MAX_ATTEMPT_OPTION', 10);
define('moeworksheets_MAX_QPP_OPTION', 50);
define('moeworksheets_MAX_DECIMAL_OPTION', 5);
define('moeworksheets_MAX_Q_DECIMAL_OPTION', 7);
/**#@-*/

/**#@+
 * Options determining how the grades from individual attempts are combined to give
 * the overall grade for a user
 */
define('moeworksheets_GRADEHIGHEST', '1');
define('moeworksheets_GRADEAVERAGE', '2');
define('moeworksheets_ATTEMPTFIRST', '3');
define('moeworksheets_ATTEMPTLAST',  '4');
/**#@-*/

/**
 * @var int If start and end date for the moeworksheets are more than this many seconds apart
 * they will be represented by two separate events in the calendar
 */
define('moeworksheets_MAX_EVENT_LENGTH', 5*24*60*60); // 5 days.

/**#@+
 * Options for navigation method within moeworksheetszes.
 */
define('moeworksheets_NAVMETHOD_FREE', 'free');
define('moeworksheets_NAVMETHOD_SEQ',  'sequential');
/**#@-*/

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $moeworksheets the data that came from the form.
 * @return mixed the id of the new instance on success,
 *          false or a string error message on failure.
 */
function moeworksheets_add_instance($moeworksheets) {
    global $DB;
    $cmid = $moeworksheets->coursemodule;

    // Process the options from the form.
    $moeworksheets->created = time();
    $result = moeworksheets_process_options($moeworksheets);
    if ($result && is_string($result)) {
        return $result;
    }

    // Try to store it in the database.
    $moeworksheets->id = $DB->insert_record('moeworksheets', $moeworksheets);

    // Create the first section for this moeworksheets.
    $DB->insert_record('moeworksheets_sections', array('moeworksheetsid' => $moeworksheets->id,
            'firstslot' => 1, 'heading' => '', 'shufflequestions' => 0));

    // Do the processing required after an add or an update.
    moeworksheets_after_add_or_update($moeworksheets);

    return $moeworksheets->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $moeworksheets the data that came from the form.
 * @return mixed true on success, false or a string error message on failure.
 */
function moeworksheets_update_instance($moeworksheets, $mform) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/moeworksheets/locallib.php');

    // Process the options from the form.
    $result = moeworksheets_process_options($moeworksheets);
    if ($result && is_string($result)) {
        return $result;
    }

    // Get the current value, so we can see what changed.
    $oldmoeworksheets = $DB->get_record('moeworksheets', array('id' => $moeworksheets->instance));

    // We need two values from the existing DB record that are not in the form,
    // in some of the function calls below.
    $moeworksheets->sumgrades = $oldmoeworksheets->sumgrades;
    $moeworksheets->grade     = $oldmoeworksheets->grade;

    // Update the database.
    $moeworksheets->id = $moeworksheets->instance;
    $DB->update_record('moeworksheets', $moeworksheets);

    // Do the processing required after an add or an update.
    moeworksheets_after_add_or_update($moeworksheets);

    if ($oldmoeworksheets->grademethod != $moeworksheets->grademethod) {
        moeworksheets_update_all_final_grades($moeworksheets);
        moeworksheets_update_grades($moeworksheets);
    }

    $moeworksheetsdateschanged = $oldmoeworksheets->timelimit   != $moeworksheets->timelimit
                     || $oldmoeworksheets->timeclose   != $moeworksheets->timeclose
                     || $oldmoeworksheets->graceperiod != $moeworksheets->graceperiod;
    if ($moeworksheetsdateschanged) {
        moeworksheets_update_open_attempts(array('moeworksheetsid' => $moeworksheets->id));
    }

    // Delete any previous preview attempts.
    moeworksheets_delete_previews($moeworksheets);

    // Repaginate, if asked to.
    if (!empty($moeworksheets->repaginatenow)) {
        moeworksheets_repaginate_questions($moeworksheets->id, $moeworksheets->questionsperpage);
    }

    return true;
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id the id of the moeworksheets to delete.
 * @return bool success or failure.
 */
function moeworksheets_delete_instance($id) {
    global $DB;

    $moeworksheets = $DB->get_record('moeworksheets', array('id' => $id), '*', MUST_EXIST);

    moeworksheets_delete_all_attempts($moeworksheets);
    moeworksheets_delete_all_overrides($moeworksheets);

    // Look for random questions that may no longer be used when this moeworksheets is gone.
    $sql = "SELECT q.id
              FROM {moeworksheets_slots} slot
              JOIN {question} q ON q.id = slot.questionid
             WHERE slot.moeworksheetsid = ? AND q.qtype = ?";
    $questionids = $DB->get_fieldset_sql($sql, array($moeworksheets->id, 'random'));

    // We need to do this before we try and delete randoms, otherwise they would still be 'in use'.
    $DB->delete_records('moeworksheets_slots', array('moeworksheetsid' => $moeworksheets->id));
    $DB->delete_records('moeworksheets_sections', array('moeworksheetsid' => $moeworksheets->id));

    foreach ($questionids as $questionid) {
        question_delete_question($questionid);
    }

    $DB->delete_records('moeworksheets_feedback', array('moeworksheetsid' => $moeworksheets->id));

    moeworksheets_access_manager::delete_settings($moeworksheets);

    $events = $DB->get_records('event', array('modulename' => 'moeworksheets', 'instance' => $moeworksheets->id));
    foreach ($events as $event) {
        $event = calendar_event::load($event);
        $event->delete();
    }

    moeworksheets_grade_item_delete($moeworksheets);
    $DB->delete_records('moeworksheets', array('id' => $moeworksheets->id));

    return true;
}

/**
 * Deletes a moeworksheets override from the database and clears any corresponding calendar events
 *
 * @param object $moeworksheets The moeworksheets object.
 * @param int $overrideid The id of the override being deleted
 * @return bool true on success
 */
function moeworksheets_delete_override($moeworksheets, $overrideid) {
    global $DB;

    if (!isset($moeworksheets->cmid)) {
        $cm = get_coursemodule_from_instance('moeworksheets', $moeworksheets->id, $moeworksheets->course);
        $moeworksheets->cmid = $cm->id;
    }

    $override = $DB->get_record('moeworksheets_overrides', array('id' => $overrideid), '*', MUST_EXIST);

    // Delete the events.
    $events = $DB->get_records('event', array('modulename' => 'moeworksheets',
            'instance' => $moeworksheets->id, 'groupid' => (int)$override->groupid,
            'userid' => (int)$override->userid));
    foreach ($events as $event) {
        $eventold = calendar_event::load($event);
        $eventold->delete();
    }

    $DB->delete_records('moeworksheets_overrides', array('id' => $overrideid));

    // Set the common parameters for one of the events we will be triggering.
    $params = array(
        'objectid' => $override->id,
        'context' => context_module::instance($moeworksheets->cmid),
        'other' => array(
            'moeworksheetsid' => $override->moeworksheets
        )
    );
    // Determine which override deleted event to fire.
    if (!empty($override->userid)) {
        $params['relateduserid'] = $override->userid;
        $event = \mod_moeworksheets\event\user_override_deleted::create($params);
    } else {
        $params['other']['groupid'] = $override->groupid;
        $event = \mod_moeworksheets\event\group_override_deleted::create($params);
    }

    // Trigger the override deleted event.
    $event->add_record_snapshot('moeworksheets_overrides', $override);
    $event->trigger();

    return true;
}

/**
 * Deletes all moeworksheets overrides from the database and clears any corresponding calendar events
 *
 * @param object $moeworksheets The moeworksheets object.
 */
function moeworksheets_delete_all_overrides($moeworksheets) {
    global $DB;

    $overrides = $DB->get_records('moeworksheets_overrides', array('moeworksheets' => $moeworksheets->id), 'id');
    foreach ($overrides as $override) {
        moeworksheets_delete_override($moeworksheets, $override->id);
    }
}

/**
 * Updates a moeworksheets object with override information for a user.
 *
 * Algorithm:  For each moeworksheets setting, if there is a matching user-specific override,
 *   then use that otherwise, if there are group-specific overrides, return the most
 *   lenient combination of them.  If neither applies, leave the moeworksheets setting unchanged.
 *
 *   Special case: if there is more than one password that applies to the user, then
 *   moeworksheets->extrapasswords will contain an array of strings giving the remaining
 *   passwords.
 *
 * @param object $moeworksheets The moeworksheets object.
 * @param int $userid The userid.
 * @return object $moeworksheets The updated moeworksheets object.
 */
function moeworksheets_update_effective_access($moeworksheets, $userid) {
    global $DB;

    // Check for user override.
    $override = $DB->get_record('moeworksheets_overrides', array('moeworksheets' => $moeworksheets->id, 'userid' => $userid));

    if (!$override) {
        $override = new stdClass();
        $override->timeopen = null;
        $override->timeclose = null;
        $override->timelimit = null;
        $override->attempts = null;
        $override->password = null;
    }

    // Check for group overrides.
    $groupings = groups_get_user_groups($moeworksheets->course, $userid);

    if (!empty($groupings[0])) {
        // Select all overrides that apply to the User's groups.
        list($extra, $params) = $DB->get_in_or_equal(array_values($groupings[0]));
        $sql = "SELECT * FROM {moeworksheets_overrides}
                WHERE groupid $extra AND moeworksheets = ?";
        $params[] = $moeworksheets->id;
        $records = $DB->get_records_sql($sql, $params);

        // Combine the overrides.
        $opens = array();
        $closes = array();
        $limits = array();
        $attempts = array();
        $passwords = array();

        foreach ($records as $gpoverride) {
            if (isset($gpoverride->timeopen)) {
                $opens[] = $gpoverride->timeopen;
            }
            if (isset($gpoverride->timeclose)) {
                $closes[] = $gpoverride->timeclose;
            }
            if (isset($gpoverride->timelimit)) {
                $limits[] = $gpoverride->timelimit;
            }
            if (isset($gpoverride->attempts)) {
                $attempts[] = $gpoverride->attempts;
            }
            if (isset($gpoverride->password)) {
                $passwords[] = $gpoverride->password;
            }
        }
        // If there is a user override for a setting, ignore the group override.
        if (is_null($override->timeopen) && count($opens)) {
            $override->timeopen = min($opens);
        }
        if (is_null($override->timeclose) && count($closes)) {
            if (in_array(0, $closes)) {
                $override->timeclose = 0;
            } else {
                $override->timeclose = max($closes);
            }
        }
        if (is_null($override->timelimit) && count($limits)) {
            if (in_array(0, $limits)) {
                $override->timelimit = 0;
            } else {
                $override->timelimit = max($limits);
            }
        }
        if (is_null($override->attempts) && count($attempts)) {
            if (in_array(0, $attempts)) {
                $override->attempts = 0;
            } else {
                $override->attempts = max($attempts);
            }
        }
        if (is_null($override->password) && count($passwords)) {
            $override->password = array_shift($passwords);
            if (count($passwords)) {
                $override->extrapasswords = $passwords;
            }
        }

    }

    // Merge with moeworksheets defaults.
    $keys = array('timeopen', 'timeclose', 'timelimit', 'attempts', 'password', 'extrapasswords');
    foreach ($keys as $key) {
        if (isset($override->{$key})) {
            $moeworksheets->{$key} = $override->{$key};
        }
    }

    return $moeworksheets;
}

/**
 * Delete all the attempts belonging to a moeworksheets.
 *
 * @param object $moeworksheets The moeworksheets object.
 */
function moeworksheets_delete_all_attempts($moeworksheets) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/moeworksheets/locallib.php');
    question_engine::delete_questions_usage_by_activities(new qubaids_for_moeworksheets($moeworksheets->id));
    $DB->delete_records('moeworksheets_attempts', array('moeworksheets' => $moeworksheets->id));
    $DB->delete_records('moeworksheets_grades', array('moeworksheets' => $moeworksheets->id));
}

/**
 * Get the best current grade for a particular user in a moeworksheets.
 *
 * @param object $moeworksheets the moeworksheets settings.
 * @param int $userid the id of the user.
 * @return float the user's current grade for this moeworksheets, or null if this user does
 * not have a grade on this moeworksheets.
 */
function moeworksheets_get_best_grade($moeworksheets, $userid) {
    global $DB;
    $grade = $DB->get_field('moeworksheets_grades', 'grade',
            array('moeworksheets' => $moeworksheets->id, 'userid' => $userid));

    // Need to detect errors/no result, without catching 0 grades.
    if ($grade === false) {
        return null;
    }

    return $grade + 0; // Convert to number.
}

/**
 * Is this a graded moeworksheets? If this method returns true, you can assume that
 * $moeworksheets->grade and $moeworksheets->sumgrades are non-zero (for example, if you want to
 * divide by them).
 *
 * @param object $moeworksheets a row from the moeworksheets table.
 * @return bool whether this is a graded moeworksheets.
 */
function moeworksheets_has_grades($moeworksheets) {
    return $moeworksheets->grade >= 0.000005 && $moeworksheets->sumgrades >= 0.000005;
}

/**
 * Does this moeworksheets allow multiple tries?
 *
 * @return bool
 */
function moeworksheets_allows_multiple_tries($moeworksheets) {
    $bt = question_engine::get_behaviour_type($moeworksheets->preferredbehaviour);
    return $bt->allows_multiple_submitted_responses();
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $moeworksheets
 * @return object|null
 */
function moeworksheets_user_outline($course, $user, $mod, $moeworksheets) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    $grades = grade_get_grades($course->id, 'mod', 'moeworksheets', $moeworksheets->id, $user->id);

    if (empty($grades->items[0]->grades)) {
        return null;
    } else {
        $grade = reset($grades->items[0]->grades);
    }

    $result = new stdClass();
    $result->info = get_string('grade') . ': ' . $grade->str_long_grade;

    // Datesubmitted == time created. dategraded == time modified or time overridden
    // if grade was last modified by the user themselves use date graded. Otherwise use
    // date submitted.
    // TODO: move this copied & pasted code somewhere in the grades API. See MDL-26704.
    if ($grade->usermodified == $user->id || empty($grade->datesubmitted)) {
        $result->time = $grade->dategraded;
    } else {
        $result->time = $grade->datesubmitted;
    }

    return $result;
}

/**
 * Print a detailed representation of what a  user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $moeworksheets
 * @return bool
 */
function moeworksheets_user_complete($course, $user, $mod, $moeworksheets) {
    global $DB, $CFG, $OUTPUT;
    require_once($CFG->libdir . '/gradelib.php');
    require_once($CFG->dirroot . '/mod/moeworksheets/locallib.php');

    $grades = grade_get_grades($course->id, 'mod', 'moeworksheets', $moeworksheets->id, $user->id);
    if (!empty($grades->items[0]->grades)) {
        $grade = reset($grades->items[0]->grades);
        echo $OUTPUT->container(get_string('grade').': '.$grade->str_long_grade);
        if ($grade->str_feedback) {
            echo $OUTPUT->container(get_string('feedback').': '.$grade->str_feedback);
        }
    }

    if ($attempts = $DB->get_records('moeworksheets_attempts',
            array('userid' => $user->id, 'moeworksheets' => $moeworksheets->id), 'attempt')) {
        foreach ($attempts as $attempt) {
            echo get_string('attempt', 'moeworksheets', $attempt->attempt) . ': ';
            if ($attempt->state != moeworksheets_attempt::FINISHED) {
                echo moeworksheets_attempt_state_name($attempt->state);
            } else {
                echo moeworksheets_format_grade($moeworksheets, $attempt->sumgrades) . '/' .
                        moeworksheets_format_grade($moeworksheets, $moeworksheets->sumgrades);
            }
            echo ' - '.userdate($attempt->timemodified).'<br />';
        }
    } else {
        print_string('noattempts', 'moeworksheets');
    }

    return true;
}

/**
 * moeworksheets periodic clean-up tasks.
 */
function moeworksheets_cron() {
    global $CFG;

    require_once($CFG->dirroot . '/mod/moeworksheets/cronlib.php');
    mtrace('');

    $timenow = time();
    $overduehander = new mod_moeworksheets_overdue_attempt_updater();

    $processto = $timenow - get_config('moeworksheets', 'graceperiodmin');

    mtrace('  Looking for moeworksheets overdue moeworksheets attempts...');

    list($count, $moeworksheetscount) = $overduehander->update_overdue_attempts($timenow, $processto);

    mtrace('  Considered ' . $count . ' attempts in ' . $moeworksheetscount . ' moeworksheetszes.');

    // Run cron for our sub-plugin types.
    cron_execute_plugin_type('moeworksheets', 'moeworksheets reports');
    cron_execute_plugin_type('moeworksheetsaccess', 'moeworksheets access rules');

    return true;
}

/**
 * @param int|array $moeworksheetsids A moeworksheets ID, or an array of moeworksheets IDs.
 * @param int $userid the userid.
 * @param string $status 'all', 'finished' or 'unfinished' to control
 * @param bool $includepreviews
 * @return an array of all the user's attempts at this moeworksheets. Returns an empty
 *      array if there are none.
 */
function moeworksheets_get_user_attempts($moeworksheetsids, $userid, $status = 'finished', $includepreviews = false) {
    global $DB, $CFG;
    // TODO MDL-33071 it is very annoying to have to included all of locallib.php
    // just to get the moeworksheets_attempt::FINISHED constants, but I will try to sort
    // that out properly for Moodle 2.4. For now, I will just do a quick fix for
    // MDL-33048.
    require_once($CFG->dirroot . '/mod/moeworksheets/locallib.php');

    $params = array();
    switch ($status) {
        case 'all':
            $statuscondition = '';
            break;

        case 'finished':
            $statuscondition = ' AND state IN (:state1, :state2)';
            $params['state1'] = moeworksheets_attempt::FINISHED;
            $params['state2'] = moeworksheets_attempt::ABANDONED;
            break;

        case 'unfinished':
            $statuscondition = ' AND state IN (:state1, :state2)';
            $params['state1'] = moeworksheets_attempt::IN_PROGRESS;
            $params['state2'] = moeworksheets_attempt::OVERDUE;
            break;
    }

    $moeworksheetsids = (array) $moeworksheetsids;
    list($insql, $inparams) = $DB->get_in_or_equal($moeworksheetsids, SQL_PARAMS_NAMED);
    $params += $inparams;
    $params['userid'] = $userid;

    $previewclause = '';
    if (!$includepreviews) {
        $previewclause = ' AND preview = 0';
    }

    return $DB->get_records_select('moeworksheets_attempts',
            "moeworksheets $insql AND userid = :userid" . $previewclause . $statuscondition,
            $params, 'moeworksheets, attempt ASC');
}

/**
 * Return grade for given user or all users.
 *
 * @param int $moeworksheetsid id of moeworksheets
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none. These are raw grades. They should
 * be processed with moeworksheets_format_grade for display.
 */
function moeworksheets_get_user_grades($moeworksheets, $userid = 0) {
    global $CFG, $DB;

    $params = array($moeworksheets->id);
    $usertest = '';
    if ($userid) {
        $params[] = $userid;
        $usertest = 'AND u.id = ?';
    }
    return $DB->get_records_sql("
            SELECT
                u.id,
                u.id AS userid,
                qg.grade AS rawgrade,
                qg.timemodified AS dategraded,
                MAX(qa.timefinish) AS datesubmitted

            FROM {user} u
            JOIN {moeworksheets_grades} qg ON u.id = qg.userid
            JOIN {moeworksheets_attempts} qa ON qa.moeworksheets = qg.moeworksheets AND qa.userid = u.id

            WHERE qg.moeworksheets = ?
            $usertest
            GROUP BY u.id, qg.grade, qg.timemodified", $params);
}

/**
 * Round a grade to to the correct number of decimal places, and format it for display.
 *
 * @param object $moeworksheets The moeworksheets table row, only $moeworksheets->decimalpoints is used.
 * @param float $grade The grade to round.
 * @return float
 */
function moeworksheets_format_grade($moeworksheets, $grade) {
    if (is_null($grade)) {
        return get_string('notyetgraded', 'moeworksheets');
    }
    return format_float($grade, $moeworksheets->decimalpoints);
}

/**
 * Determine the correct number of decimal places required to format a grade.
 *
 * @param object $moeworksheets The moeworksheets table row, only $moeworksheets->decimalpoints is used.
 * @return integer
 */
function moeworksheets_get_grade_format($moeworksheets) {
    if (empty($moeworksheets->questiondecimalpoints)) {
        $moeworksheets->questiondecimalpoints = -1;
    }

    if ($moeworksheets->questiondecimalpoints == -1) {
        return $moeworksheets->decimalpoints;
    }

    return $moeworksheets->questiondecimalpoints;
}

/**
 * Round a grade to the correct number of decimal places, and format it for display.
 *
 * @param object $moeworksheets The moeworksheets table row, only $moeworksheets->decimalpoints is used.
 * @param float $grade The grade to round.
 * @return float
 */
function moeworksheets_format_question_grade($moeworksheets, $grade) {
    return format_float($grade, moeworksheets_get_grade_format($moeworksheets));
}

/**
 * Update grades in central gradebook
 *
 * @category grade
 * @param object $moeworksheets the moeworksheets settings.
 * @param int $userid specific user only, 0 means all users.
 * @param bool $nullifnone If a single user is specified and $nullifnone is true a grade item with a null rawgrade will be inserted
 */
function moeworksheets_update_grades($moeworksheets, $userid = 0, $nullifnone = true) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    if ($moeworksheets->grade == 0) {
        moeworksheets_grade_item_update($moeworksheets);

    } else if ($grades = moeworksheets_get_user_grades($moeworksheets, $userid)) {
        moeworksheets_grade_item_update($moeworksheets, $grades);

    } else if ($userid && $nullifnone) {
        $grade = new stdClass();
        $grade->userid = $userid;
        $grade->rawgrade = null;
        moeworksheets_grade_item_update($moeworksheets, $grade);

    } else {
        moeworksheets_grade_item_update($moeworksheets);
    }
}

/**
 * Create or update the grade item for given moeworksheets
 *
 * @category grade
 * @param object $moeworksheets object with extra cmidnumber
 * @param mixed $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function moeworksheets_grade_item_update($moeworksheets, $grades = null) {
    global $CFG, $OUTPUT;
    require_once($CFG->dirroot . '/mod/moeworksheets/locallib.php');
    require_once($CFG->libdir . '/gradelib.php');

    if (array_key_exists('cmidnumber', $moeworksheets)) { // May not be always present.
        $params = array('itemname' => $moeworksheets->name, 'idnumber' => $moeworksheets->cmidnumber);
    } else {
        $params = array('itemname' => $moeworksheets->name);
    }

    if ($moeworksheets->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $moeworksheets->grade;
        $params['grademin']  = 0;

    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    // What this is trying to do:
    // 1. If the moeworksheets is set to not show grades while the moeworksheets is still open,
    //    and is set to show grades after the moeworksheets is closed, then create the
    //    grade_item with a show-after date that is the moeworksheets close date.
    // 2. If the moeworksheets is set to not show grades at either of those times,
    //    create the grade_item as hidden.
    // 3. If the moeworksheets is set to show grades, create the grade_item visible.
    $openreviewoptions = mod_moeworksheets_display_options::make_from_moeworksheets($moeworksheets,
            mod_moeworksheets_display_options::LATER_WHILE_OPEN);
    $closedreviewoptions = mod_moeworksheets_display_options::make_from_moeworksheets($moeworksheets,
            mod_moeworksheets_display_options::AFTER_CLOSE);
    if ($openreviewoptions->marks < question_display_options::MARK_AND_MAX &&
            $closedreviewoptions->marks < question_display_options::MARK_AND_MAX) {
        $params['hidden'] = 1;

    } else if ($openreviewoptions->marks < question_display_options::MARK_AND_MAX &&
            $closedreviewoptions->marks >= question_display_options::MARK_AND_MAX) {
        if ($moeworksheets->timeclose) {
            $params['hidden'] = $moeworksheets->timeclose;
        } else {
            $params['hidden'] = 1;
        }

    } else {
        // Either
        // a) both open and closed enabled
        // b) open enabled, closed disabled - we can not "hide after",
        //    grades are kept visible even after closing.
        $params['hidden'] = 0;
    }

    if (!$params['hidden']) {
        // If the grade item is not hidden by the moeworksheets logic, then we need to
        // hide it if the moeworksheets is hidden from students.
        if (property_exists($moeworksheets, 'visible')) {
            // Saving the moeworksheets form, and cm not yet updated in the database.
            $params['hidden'] = !$moeworksheets->visible;
        } else {
            $cm = get_coursemodule_from_instance('moeworksheets', $moeworksheets->id);
            $params['hidden'] = !$cm->visible;
        }
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    $gradebook_grades = grade_get_grades($moeworksheets->course, 'mod', 'moeworksheets', $moeworksheets->id);
    if (!empty($gradebook_grades->items)) {
        $grade_item = $gradebook_grades->items[0];
        if ($grade_item->locked) {
            // NOTE: this is an extremely nasty hack! It is not a bug if this confirmation fails badly. --skodak.
            $confirm_regrade = optional_param('confirm_regrade', 0, PARAM_INT);
            if (!$confirm_regrade) {
                if (!AJAX_SCRIPT) {
                    $message = get_string('gradeitemislocked', 'grades');
                    $back_link = $CFG->wwwroot . '/mod/moeworksheets/report.php?q=' . $moeworksheets->id .
                            '&amp;mode=overview';
                    $regrade_link = qualified_me() . '&amp;confirm_regrade=1';
                    echo $OUTPUT->box_start('generalbox', 'notice');
                    echo '<p>'. $message .'</p>';
                    echo $OUTPUT->container_start('buttons');
                    echo $OUTPUT->single_button($regrade_link, get_string('regradeanyway', 'grades'));
                    echo $OUTPUT->single_button($back_link,  get_string('cancel'));
                    echo $OUTPUT->container_end();
                    echo $OUTPUT->box_end();
                }
                return GRADE_UPDATE_ITEM_LOCKED;
            }
        }
    }

    return grade_update('mod/moeworksheets', $moeworksheets->course, 'mod', 'moeworksheets', $moeworksheets->id, 0, $grades, $params);
}

/**
 * Delete grade item for given moeworksheets
 *
 * @category grade
 * @param object $moeworksheets object
 * @return object moeworksheets
 */
function moeworksheets_grade_item_delete($moeworksheets) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update('mod/moeworksheets', $moeworksheets->course, 'mod', 'moeworksheets', $moeworksheets->id, 0,
            null, array('deleted' => 1));
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every moeworksheets event in the site is checked, else
 * only moeworksheets events belonging to the course specified are checked.
 * This function is used, in its new format, by restore_refresh_events()
 *
 * @param int $courseid
 * @return bool
 */
function moeworksheets_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid == 0) {
        if (!$moeworksheetszes = $DB->get_records('moeworksheets')) {
            return true;
        }
    } else {
        if (!$moeworksheetszes = $DB->get_records('moeworksheets', array('course' => $courseid))) {
            return true;
        }
    }

    foreach ($moeworksheetszes as $moeworksheets) {
        moeworksheets_update_events($moeworksheets);
    }

    return true;
}

/**
 * Returns all moeworksheets graded users since a given time for specified moeworksheets
 */
function moeworksheets_get_recent_mod_activity(&$activities, &$index, $timestart,
        $courseid, $cmid, $userid = 0, $groupid = 0) {
    global $CFG, $USER, $DB;
    require_once($CFG->dirroot . '/mod/moeworksheets/locallib.php');

    $course = get_course($courseid);
    $modinfo = get_fast_modinfo($course);

    $cm = $modinfo->cms[$cmid];
    $moeworksheets = $DB->get_record('moeworksheets', array('id' => $cm->instance));

    if ($userid) {
        $userselect = "AND u.id = :userid";
        $params['userid'] = $userid;
    } else {
        $userselect = '';
    }

    if ($groupid) {
        $groupselect = 'AND gm.groupid = :groupid';
        $groupjoin   = 'JOIN {groups_members} gm ON  gm.userid=u.id';
        $params['groupid'] = $groupid;
    } else {
        $groupselect = '';
        $groupjoin   = '';
    }

    $params['timestart'] = $timestart;
    $params['moeworksheetsid'] = $moeworksheets->id;

    $ufields = user_picture::fields('u', null, 'useridagain');
    if (!$attempts = $DB->get_records_sql("
              SELECT qa.*,
                     {$ufields}
                FROM {moeworksheets_attempts} qa
                     JOIN {user} u ON u.id = qa.userid
                     $groupjoin
               WHERE qa.timefinish > :timestart
                 AND qa.moeworksheets = :moeworksheetsid
                 AND qa.preview = 0
                     $userselect
                     $groupselect
            ORDER BY qa.timefinish ASC", $params)) {
        return;
    }

    $context         = context_module::instance($cm->id);
    $accessallgroups = has_capability('moodle/site:accessallgroups', $context);
    $viewfullnames   = has_capability('moodle/site:viewfullnames', $context);
    $grader          = has_capability('mod/moeworksheets:viewreports', $context);
    $groupmode       = groups_get_activity_groupmode($cm, $course);

    $usersgroups = null;
    $aname = format_string($cm->name, true);
    foreach ($attempts as $attempt) {
        if ($attempt->userid != $USER->id) {
            if (!$grader) {
                // Grade permission required.
                continue;
            }

            if ($groupmode == SEPARATEGROUPS and !$accessallgroups) {
                $usersgroups = groups_get_all_groups($course->id,
                        $attempt->userid, $cm->groupingid);
                $usersgroups = array_keys($usersgroups);
                if (!array_intersect($usersgroups, $modinfo->get_groups($cm->groupingid))) {
                    continue;
                }
            }
        }

        $options = moeworksheets_get_review_options($moeworksheets, $attempt, $context);

        $tmpactivity = new stdClass();

        $tmpactivity->type       = 'moeworksheets';
        $tmpactivity->cmid       = $cm->id;
        $tmpactivity->name       = $aname;
        $tmpactivity->sectionnum = $cm->sectionnum;
        $tmpactivity->timestamp  = $attempt->timefinish;

        $tmpactivity->content = new stdClass();
        $tmpactivity->content->attemptid = $attempt->id;
        $tmpactivity->content->attempt   = $attempt->attempt;
        if (moeworksheets_has_grades($moeworksheets) && $options->marks >= question_display_options::MARK_AND_MAX) {
            $tmpactivity->content->sumgrades = moeworksheets_format_grade($moeworksheets, $attempt->sumgrades);
            $tmpactivity->content->maxgrade  = moeworksheets_format_grade($moeworksheets, $moeworksheets->sumgrades);
        } else {
            $tmpactivity->content->sumgrades = null;
            $tmpactivity->content->maxgrade  = null;
        }

        $tmpactivity->user = user_picture::unalias($attempt, null, 'useridagain');
        $tmpactivity->user->fullname  = fullname($tmpactivity->user, $viewfullnames);

        $activities[$index++] = $tmpactivity;
    }
}

function moeworksheets_print_recent_mod_activity($activity, $courseid, $detail, $modnames) {
    global $CFG, $OUTPUT;

    echo '<table border="0" cellpadding="3" cellspacing="0" class="forum-recent">';

    echo '<tr><td class="userpicture" valign="top">';
    echo $OUTPUT->user_picture($activity->user, array('courseid' => $courseid));
    echo '</td><td>';

    if ($detail) {
        $modname = $modnames[$activity->type];
        echo '<div class="title">';
        echo '<img src="' . $OUTPUT->pix_url('icon', $activity->type) . '" ' .
                'class="icon" alt="' . $modname . '" />';
        echo '<a href="' . $CFG->wwwroot . '/mod/moeworksheets/view.php?id=' .
                $activity->cmid . '">' . $activity->name . '</a>';
        echo '</div>';
    }

    echo '<div class="grade">';
    echo  get_string('attempt', 'moeworksheets', $activity->content->attempt);
    if (isset($activity->content->maxgrade)) {
        $grades = $activity->content->sumgrades . ' / ' . $activity->content->maxgrade;
        echo ': (<a href="' . $CFG->wwwroot . '/mod/moeworksheets/review.php?attempt=' .
                $activity->content->attemptid . '">' . $grades . '</a>)';
    }
    echo '</div>';

    echo '<div class="user">';
    echo '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $activity->user->id .
            '&amp;course=' . $courseid . '">' . $activity->user->fullname .
            '</a> - ' . userdate($activity->timestamp);
    echo '</div>';

    echo '</td></tr></table>';

    return;
}

/**
 * Pre-process the moeworksheets options form data, making any necessary adjustments.
 * Called by add/update instance in this file.
 *
 * @param object $moeworksheets The variables set on the form.
 */
function moeworksheets_process_options($moeworksheets) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/moeworksheets/locallib.php');
    require_once($CFG->libdir . '/questionlib.php');

    $moeworksheets->timemodified = time();

    // moeworksheets name.
    if (!empty($moeworksheets->name)) {
        $moeworksheets->name = trim($moeworksheets->name);
    }

    // Password field - different in form to stop browsers that remember passwords
    // getting confused.
    $moeworksheets->password = $moeworksheets->moeworksheetspassword;
    unset($moeworksheets->moeworksheetspassword);

    // moeworksheets feedback.
    if (isset($moeworksheets->feedbacktext)) {
        // Clean up the boundary text.
        for ($i = 0; $i < count($moeworksheets->feedbacktext); $i += 1) {
            if (empty($moeworksheets->feedbacktext[$i]['text'])) {
                $moeworksheets->feedbacktext[$i]['text'] = '';
            } else {
                $moeworksheets->feedbacktext[$i]['text'] = trim($moeworksheets->feedbacktext[$i]['text']);
            }
        }

        // Check the boundary value is a number or a percentage, and in range.
        $i = 0;
        while (!empty($moeworksheets->feedbackboundaries[$i])) {
            $boundary = trim($moeworksheets->feedbackboundaries[$i]);
            if (!is_numeric($boundary)) {
                if (strlen($boundary) > 0 && $boundary[strlen($boundary) - 1] == '%') {
                    $boundary = trim(substr($boundary, 0, -1));
                    if (is_numeric($boundary)) {
                        $boundary = $boundary * $moeworksheets->grade / 100.0;
                    } else {
                        return get_string('feedbackerrorboundaryformat', 'moeworksheets', $i + 1);
                    }
                }
            }
            if ($boundary <= 0 || $boundary >= $moeworksheets->grade) {
                return get_string('feedbackerrorboundaryoutofrange', 'moeworksheets', $i + 1);
            }
            if ($i > 0 && $boundary >= $moeworksheets->feedbackboundaries[$i - 1]) {
                return get_string('feedbackerrororder', 'moeworksheets', $i + 1);
            }
            $moeworksheets->feedbackboundaries[$i] = $boundary;
            $i += 1;
        }
        $numboundaries = $i;

        // Check there is nothing in the remaining unused fields.
        if (!empty($moeworksheets->feedbackboundaries)) {
            for ($i = $numboundaries; $i < count($moeworksheets->feedbackboundaries); $i += 1) {
                if (!empty($moeworksheets->feedbackboundaries[$i]) &&
                        trim($moeworksheets->feedbackboundaries[$i]) != '') {
                    return get_string('feedbackerrorjunkinboundary', 'moeworksheets', $i + 1);
                }
            }
        }
        for ($i = $numboundaries + 1; $i < count($moeworksheets->feedbacktext); $i += 1) {
            if (!empty($moeworksheets->feedbacktext[$i]['text']) &&
                    trim($moeworksheets->feedbacktext[$i]['text']) != '') {
                return get_string('feedbackerrorjunkinfeedback', 'moeworksheets', $i + 1);
            }
        }
        // Needs to be bigger than $moeworksheets->grade because of '<' test in moeworksheets_feedback_for_grade().
        $moeworksheets->feedbackboundaries[-1] = $moeworksheets->grade + 1;
        $moeworksheets->feedbackboundaries[$numboundaries] = 0;
        $moeworksheets->feedbackboundarycount = $numboundaries;
    } else {
        $moeworksheets->feedbackboundarycount = -1;
    }

    // Combing the individual settings into the review columns.
    $moeworksheets->reviewattempt = moeworksheets_review_option_form_to_db($moeworksheets, 'attempt');
    $moeworksheets->reviewcorrectness = moeworksheets_review_option_form_to_db($moeworksheets, 'correctness');
    $moeworksheets->reviewmarks = moeworksheets_review_option_form_to_db($moeworksheets, 'marks');
    $moeworksheets->reviewspecificfeedback = moeworksheets_review_option_form_to_db($moeworksheets, 'specificfeedback');
    $moeworksheets->reviewgeneralfeedback = moeworksheets_review_option_form_to_db($moeworksheets, 'generalfeedback');
    $moeworksheets->reviewrightanswer = moeworksheets_review_option_form_to_db($moeworksheets, 'rightanswer');
    $moeworksheets->reviewoverallfeedback = moeworksheets_review_option_form_to_db($moeworksheets, 'overallfeedback');
    $moeworksheets->reviewattempt |= mod_moeworksheets_display_options::DURING;
    $moeworksheets->reviewoverallfeedback &= ~mod_moeworksheets_display_options::DURING;
}

/**
 * Helper function for {@link moeworksheets_process_options()}.
 * @param object $fromform the sumbitted form date.
 * @param string $field one of the review option field names.
 */
function moeworksheets_review_option_form_to_db($fromform, $field) {
    static $times = array(
        'during' => mod_moeworksheets_display_options::DURING,
        'immediately' => mod_moeworksheets_display_options::IMMEDIATELY_AFTER,
        'open' => mod_moeworksheets_display_options::LATER_WHILE_OPEN,
        'closed' => mod_moeworksheets_display_options::AFTER_CLOSE,
    );

    $review = 0;
    foreach ($times as $whenname => $when) {
        $fieldname = $field . $whenname;
        if (isset($fromform->$fieldname)) {
            $review |= $when;
            unset($fromform->$fieldname);
        }
    }

    return $review;
}

/**
 * This function is called at the end of moeworksheets_add_instance
 * and moeworksheets_update_instance, to do the common processing.
 *
 * @param object $moeworksheets the moeworksheets object.
 */
function moeworksheets_after_add_or_update($moeworksheets) {
    global $DB;
    $cmid = $moeworksheets->coursemodule;

    // We need to use context now, so we need to make sure all needed info is already in db.
    $DB->set_field('course_modules', 'instance', $moeworksheets->id, array('id'=>$cmid));
    $context = context_module::instance($cmid);

    // Save the feedback.
    $DB->delete_records('moeworksheets_feedback', array('moeworksheetsid' => $moeworksheets->id));

    for ($i = 0; $i <= $moeworksheets->feedbackboundarycount; $i++) {
        $feedback = new stdClass();
        $feedback->moeworksheetsid = $moeworksheets->id;
        $feedback->feedbacktext = $moeworksheets->feedbacktext[$i]['text'];
        $feedback->feedbacktextformat = $moeworksheets->feedbacktext[$i]['format'];
        $feedback->mingrade = $moeworksheets->feedbackboundaries[$i];
        $feedback->maxgrade = $moeworksheets->feedbackboundaries[$i - 1];
        $feedback->id = $DB->insert_record('moeworksheets_feedback', $feedback);
        $feedbacktext = file_save_draft_area_files((int)$moeworksheets->feedbacktext[$i]['itemid'],
                $context->id, 'mod_moeworksheets', 'feedback', $feedback->id,
                array('subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0),
                $moeworksheets->feedbacktext[$i]['text']);
        $DB->set_field('moeworksheets_feedback', 'feedbacktext', $feedbacktext,
                array('id' => $feedback->id));
    }

    // Store any settings belonging to the access rules.
    moeworksheets_access_manager::save_settings($moeworksheets);

    // Update the events relating to this moeworksheets.
    moeworksheets_update_events($moeworksheets);

    // Update related grade item.
    moeworksheets_grade_item_update($moeworksheets);
}

/**
 * This function updates the events associated to the moeworksheets.
 * If $override is non-zero, then it updates only the events
 * associated with the specified override.
 *
 * @uses moeworksheets_MAX_EVENT_LENGTH
 * @param object $moeworksheets the moeworksheets object.
 * @param object optional $override limit to a specific override
 */
function moeworksheets_update_events($moeworksheets, $override = null) {
    global $DB;

    // Load the old events relating to this moeworksheets.
    $conds = array('modulename'=>'moeworksheets',
                   'instance'=>$moeworksheets->id);
    if (!empty($override)) {
        // Only load events for this override.
        $conds['groupid'] = isset($override->groupid)?  $override->groupid : 0;
        $conds['userid'] = isset($override->userid)?  $override->userid : 0;
    }
    $oldevents = $DB->get_records('event', $conds);

    // Now make a todo list of all that needs to be updated.
    if (empty($override)) {
        // We are updating the primary settings for the moeworksheets, so we
        // need to add all the overrides.
        $overrides = $DB->get_records('moeworksheets_overrides', array('moeworksheets' => $moeworksheets->id));
        // As well as the original moeworksheets (empty override).
        $overrides[] = new stdClass();
    } else {
        // Just do the one override.
        $overrides = array($override);
    }

    foreach ($overrides as $current) {
        $groupid   = isset($current->groupid)?  $current->groupid : 0;
        $userid    = isset($current->userid)? $current->userid : 0;
        $timeopen  = isset($current->timeopen)?  $current->timeopen : $moeworksheets->timeopen;
        $timeclose = isset($current->timeclose)? $current->timeclose : $moeworksheets->timeclose;

        // Only add open/close events for an override if they differ from the moeworksheets default.
        $addopen  = empty($current->id) || !empty($current->timeopen);
        $addclose = empty($current->id) || !empty($current->timeclose);

        if (!empty($moeworksheets->coursemodule)) {
            $cmid = $moeworksheets->coursemodule;
        } else {
            $cmid = get_coursemodule_from_instance('moeworksheets', $moeworksheets->id, $moeworksheets->course)->id;
        }

        $event = new stdClass();
        $event->description = format_module_intro('moeworksheets', $moeworksheets, $cmid);
        // Events module won't show user events when the courseid is nonzero.
        $event->courseid    = ($userid) ? 0 : $moeworksheets->course;
        $event->groupid     = $groupid;
        $event->userid      = $userid;
        $event->modulename  = 'moeworksheets';
        $event->instance    = $moeworksheets->id;
        $event->timestart   = $timeopen;
        $event->timeduration = max($timeclose - $timeopen, 0);
        $event->visible     = instance_is_visible('moeworksheets', $moeworksheets);
        $event->eventtype   = 'open';

        // Determine the event name.
        if ($groupid) {
            $params = new stdClass();
            $params->moeworksheets = $moeworksheets->name;
            $params->group = groups_get_group_name($groupid);
            if ($params->group === false) {
                // Group doesn't exist, just skip it.
                continue;
            }
            $eventname = get_string('overridegroupeventname', 'moeworksheets', $params);
        } else if ($userid) {
            $params = new stdClass();
            $params->moeworksheets = $moeworksheets->name;
            $eventname = get_string('overrideusereventname', 'moeworksheets', $params);
        } else {
            $eventname = $moeworksheets->name;
        }
        if ($addopen or $addclose) {
            if ($timeclose and $timeopen and $event->timeduration <= moeworksheets_MAX_EVENT_LENGTH) {
                // Single event for the whole moeworksheets.
                if ($oldevent = array_shift($oldevents)) {
                    $event->id = $oldevent->id;
                } else {
                    unset($event->id);
                }
                $event->name = $eventname;
                // The method calendar_event::create will reuse a db record if the id field is set.
                calendar_event::create($event);
            } else {
                // Separate start and end events.
                $event->timeduration  = 0;
                if ($timeopen && $addopen) {
                    if ($oldevent = array_shift($oldevents)) {
                        $event->id = $oldevent->id;
                    } else {
                        unset($event->id);
                    }
                    $event->name = $eventname.' ('.get_string('moeworksheetsopens', 'moeworksheets').')';
                    // The method calendar_event::create will reuse a db record if the id field is set.
                    calendar_event::create($event);
                }
                if ($timeclose && $addclose) {
                    if ($oldevent = array_shift($oldevents)) {
                        $event->id = $oldevent->id;
                    } else {
                        unset($event->id);
                    }
                    $event->name      = $eventname.' ('.get_string('moeworksheetscloses', 'moeworksheets').')';
                    $event->timestart = $timeclose;
                    $event->eventtype = 'close';
                    calendar_event::create($event);
                }
            }
        }
    }

    // Delete any leftover events.
    foreach ($oldevents as $badevent) {
        $badevent = calendar_event::load($badevent);
        $badevent->delete();
    }
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function moeworksheets_get_view_actions() {
    return array('view', 'view all', 'report', 'review');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function moeworksheets_get_post_actions() {
    return array('attempt', 'close attempt', 'preview', 'editquestions',
            'delete attempt', 'manualgrade');
}

/**
 * @param array $questionids of question ids.
 * @return bool whether any of these questions are used by any instance of this module.
 */
function moeworksheets_questions_in_use($questionids) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    list($test, $params) = $DB->get_in_or_equal($questionids);
    return $DB->record_exists_select('moeworksheets_slots',
            'questionid ' . $test, $params) || question_engine::questions_in_use(
            $questionids, new qubaid_join('{moeworksheets_attempts} moeworksheetsa',
            'moeworksheetsa.uniqueid', 'moeworksheetsa.preview = 0'));
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the moeworksheets.
 *
 * @param $mform the course reset form that is being built.
 */
function moeworksheets_reset_course_form_definition($mform) {
    $mform->addElement('header', 'moeworksheetsheader', get_string('modulenameplural', 'moeworksheets'));
    $mform->addElement('advcheckbox', 'reset_moeworksheets_attempts',
            get_string('removeallmoeworksheetsattempts', 'moeworksheets'));
    $mform->addElement('advcheckbox', 'reset_moeworksheets_user_overrides',
            get_string('removealluseroverrides', 'moeworksheets'));
    $mform->addElement('advcheckbox', 'reset_moeworksheets_group_overrides',
            get_string('removeallgroupoverrides', 'moeworksheets'));
}

/**
 * Course reset form defaults.
 * @return array the defaults.
 */
function moeworksheets_reset_course_form_defaults($course) {
    return array('reset_moeworksheets_attempts' => 1,
                 'reset_moeworksheets_group_overrides' => 1,
                 'reset_moeworksheets_user_overrides' => 1);
}

/**
 * Removes all grades from gradebook
 *
 * @param int $courseid
 * @param string optional type
 */
function moeworksheets_reset_gradebook($courseid, $type='') {
    global $CFG, $DB;

    $moeworksheetszes = $DB->get_records_sql("
            SELECT q.*, cm.idnumber as cmidnumber, q.course as courseid
            FROM {modules} m
            JOIN {course_modules} cm ON m.id = cm.module
            JOIN {moeworksheets} q ON cm.instance = q.id
            WHERE m.name = 'moeworksheets' AND cm.course = ?", array($courseid));

    foreach ($moeworksheetszes as $moeworksheets) {
        moeworksheets_grade_item_update($moeworksheets, 'reset');
    }
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * moeworksheets attempts for course $data->courseid, if $data->reset_moeworksheets_attempts is
 * set and true.
 *
 * Also, move the moeworksheets open and close dates, if the course start date is changing.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function moeworksheets_reset_userdata($data) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/questionlib.php');

    $componentstr = get_string('modulenameplural', 'moeworksheets');
    $status = array();

    // Delete attempts.
    if (!empty($data->reset_moeworksheets_attempts)) {
        question_engine::delete_questions_usage_by_activities(new qubaid_join(
                '{moeworksheets_attempts} moeworksheetsa JOIN {moeworksheets} moeworksheets ON moeworksheetsa.moeworksheets = moeworksheets.id',
                'moeworksheetsa.uniqueid', 'moeworksheets.course = :moeworksheetscourseid',
                array('moeworksheetscourseid' => $data->courseid)));

        $DB->delete_records_select('moeworksheets_attempts',
                'moeworksheets IN (SELECT id FROM {moeworksheets} WHERE course = ?)', array($data->courseid));
        $status[] = array(
            'component' => $componentstr,
            'item' => get_string('attemptsdeleted', 'moeworksheets'),
            'error' => false);

        // Remove all grades from gradebook.
        $DB->delete_records_select('moeworksheets_grades',
                'moeworksheets IN (SELECT id FROM {moeworksheets} WHERE course = ?)', array($data->courseid));
        if (empty($data->reset_gradebook_grades)) {
            moeworksheets_reset_gradebook($data->courseid);
        }
        $status[] = array(
            'component' => $componentstr,
            'item' => get_string('gradesdeleted', 'moeworksheets'),
            'error' => false);
    }

    // Remove user overrides.
    if (!empty($data->reset_moeworksheets_user_overrides)) {
        $DB->delete_records_select('moeworksheets_overrides',
                'moeworksheets IN (SELECT id FROM {moeworksheets} WHERE course = ?) AND userid IS NOT NULL', array($data->courseid));
        $status[] = array(
            'component' => $componentstr,
            'item' => get_string('useroverridesdeleted', 'moeworksheets'),
            'error' => false);
    }
    // Remove group overrides.
    if (!empty($data->reset_moeworksheets_group_overrides)) {
        $DB->delete_records_select('moeworksheets_overrides',
                'moeworksheets IN (SELECT id FROM {moeworksheets} WHERE course = ?) AND groupid IS NOT NULL', array($data->courseid));
        $status[] = array(
            'component' => $componentstr,
            'item' => get_string('groupoverridesdeleted', 'moeworksheets'),
            'error' => false);
    }

    // Updating dates - shift may be negative too.
    if ($data->timeshift) {
        $DB->execute("UPDATE {moeworksheets_overrides}
                         SET timeopen = timeopen + ?
                       WHERE moeworksheets IN (SELECT id FROM {moeworksheets} WHERE course = ?)
                         AND timeopen <> 0", array($data->timeshift, $data->courseid));
        $DB->execute("UPDATE {moeworksheets_overrides}
                         SET timeclose = timeclose + ?
                       WHERE moeworksheets IN (SELECT id FROM {moeworksheets} WHERE course = ?)
                         AND timeclose <> 0", array($data->timeshift, $data->courseid));

        shift_course_mod_dates('moeworksheets', array('timeopen', 'timeclose'),
                $data->timeshift, $data->courseid);

        $status[] = array(
            'component' => $componentstr,
            'item' => get_string('openclosedatesupdated', 'moeworksheets'),
            'error' => false);
    }

    return $status;
}

/**
 * Prints moeworksheets summaries on MyMoodle Page
 * @param arry $courses
 * @param array $htmlarray
 */
function moeworksheets_print_overview($courses, &$htmlarray) {
    global $USER, $CFG;
    // These next 6 Lines are constant in all modules (just change module name).
    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return array();
    }

    if (!$moeworksheetszes = get_all_instances_in_courses('moeworksheets', $courses)) {
        return;
    }

    // Get the moeworksheetszes attempts.
    $attemptsinfo = [];
    $moeworksheetsids = [];
    foreach ($moeworksheetszes as $moeworksheets) {
        $moeworksheetsids[] = $moeworksheets->id;
        $attemptsinfo[$moeworksheets->id] = ['count' => 0, 'hasfinished' => false];
    }
    $attempts = moeworksheets_get_user_attempts($moeworksheetsids, $USER->id);
    foreach ($attempts as $attempt) {
        $attemptsinfo[$attempt->moeworksheets]['count']++;
        $attemptsinfo[$attempt->moeworksheets]['hasfinished'] = true;
    }
    unset($attempts);

    // Fetch some language strings outside the main loop.
    $strmoeworksheets = get_string('modulename', 'moeworksheets');
    $strnoattempts = get_string('noattempts', 'moeworksheets');

    // We want to list moeworksheetszes that are currently available, and which have a close date.
    // This is the same as what the lesson does, and the dabate is in MDL-10568.
    $now = time();
    foreach ($moeworksheetszes as $moeworksheets) {
        if ($moeworksheets->timeclose >= $now && $moeworksheets->timeopen < $now) {
            $str = '';

            // Now provide more information depending on the uers's role.
            $context = context_module::instance($moeworksheets->coursemodule);
            if (has_capability('mod/moeworksheets:viewreports', $context)) {
                // For teacher-like people, show a summary of the number of student attempts.
                // The $moeworksheets objects returned by get_all_instances_in_course have the necessary $cm
                // fields set to make the following call work.
                $str .= '<div class="info">' . moeworksheets_num_attempt_summary($moeworksheets, $moeworksheets, true) . '</div>';

            } else if (has_any_capability(array('mod/moeworksheets:reviewmyattempts', 'mod/moeworksheets:attempt'), $context)) { // Student
                // For student-like people, tell them how many attempts they have made.

                if (isset($USER->id)) {
                    if ($attemptsinfo[$moeworksheets->id]['hasfinished']) {
                        // The student's last attempt is finished.
                        continue;
                    }

                    if ($attemptsinfo[$moeworksheets->id]['count'] > 0) {
                        $str .= '<div class="info">' .
                            get_string('numattemptsmade', 'moeworksheets', $attemptsinfo[$moeworksheets->id]['count']) . '</div>';
                    } else {
                        $str .= '<div class="info">' . $strnoattempts . '</div>';
                    }

                } else {
                    $str .= '<div class="info">' . $strnoattempts . '</div>';
                }

            } else {
                // For ayone else, there is no point listing this moeworksheets, so stop processing.
                continue;
            }

            // Give a link to the moeworksheets, and the deadline.
            $html = '<div class="moeworksheets overview">' .
                    '<div class="name">' . $strmoeworksheets . ': <a ' .
                    ($moeworksheets->visible ? '' : ' class="dimmed"') .
                    ' href="' . $CFG->wwwroot . '/mod/moeworksheets/view.php?id=' .
                    $moeworksheets->coursemodule . '">' .
                    $moeworksheets->name . '</a></div>';
            $html .= '<div class="info">' . get_string('moeworksheetscloseson', 'moeworksheets',
                    userdate($moeworksheets->timeclose)) . '</div>';
            $html .= $str;
            $html .= '</div>';
            if (empty($htmlarray[$moeworksheets->course]['moeworksheets'])) {
                $htmlarray[$moeworksheets->course]['moeworksheets'] = $html;
            } else {
                $htmlarray[$moeworksheets->course]['moeworksheets'] .= $html;
            }
        }
    }
}

/**
 * Return a textual summary of the number of attempts that have been made at a particular moeworksheets,
 * returns '' if no attempts have been made yet, unless $returnzero is passed as true.
 *
 * @param object $moeworksheets the moeworksheets object. Only $moeworksheets->id is used at the moment.
 * @param object $cm the cm object. Only $cm->course, $cm->groupmode and
 *      $cm->groupingid fields are used at the moment.
 * @param bool $returnzero if false (default), when no attempts have been
 *      made '' is returned instead of 'Attempts: 0'.
 * @param int $currentgroup if there is a concept of current group where this method is being called
 *         (e.g. a report) pass it in here. Default 0 which means no current group.
 * @return string a string like "Attempts: 123", "Attemtps 123 (45 from your groups)" or
 *          "Attemtps 123 (45 from this group)".
 */
function moeworksheets_num_attempt_summary($moeworksheets, $cm, $returnzero = false, $currentgroup = 0) {
    global $DB, $USER;
    $numattempts = $DB->count_records('moeworksheets_attempts', array('moeworksheets'=> $moeworksheets->id, 'preview'=>0));
    if ($numattempts || $returnzero) {
        if (groups_get_activity_groupmode($cm)) {
            $a = new stdClass();
            $a->total = $numattempts;
            if ($currentgroup) {
                $a->group = $DB->count_records_sql('SELECT COUNT(DISTINCT qa.id) FROM ' .
                        '{moeworksheets_attempts} qa JOIN ' .
                        '{groups_members} gm ON qa.userid = gm.userid ' .
                        'WHERE moeworksheets = ? AND preview = 0 AND groupid = ?',
                        array($moeworksheets->id, $currentgroup));
                return get_string('attemptsnumthisgroup', 'moeworksheets', $a);
            } else if ($groups = groups_get_all_groups($cm->course, $USER->id, $cm->groupingid)) {
                list($usql, $params) = $DB->get_in_or_equal(array_keys($groups));
                $a->group = $DB->count_records_sql('SELECT COUNT(DISTINCT qa.id) FROM ' .
                        '{moeworksheets_attempts} qa JOIN ' .
                        '{groups_members} gm ON qa.userid = gm.userid ' .
                        'WHERE moeworksheets = ? AND preview = 0 AND ' .
                        "groupid $usql", array_merge(array($moeworksheets->id), $params));
                return get_string('attemptsnumyourgroups', 'moeworksheets', $a);
            }
        }
        return get_string('attemptsnum', 'moeworksheets', $numattempts);
    }
    return '';
}

/**
 * Returns the same as {@link moeworksheets_num_attempt_summary()} but wrapped in a link
 * to the moeworksheets reports.
 *
 * @param object $moeworksheets the moeworksheets object. Only $moeworksheets->id is used at the moment.
 * @param object $cm the cm object. Only $cm->course, $cm->groupmode and
 *      $cm->groupingid fields are used at the moment.
 * @param object $context the moeworksheets context.
 * @param bool $returnzero if false (default), when no attempts have been made
 *      '' is returned instead of 'Attempts: 0'.
 * @param int $currentgroup if there is a concept of current group where this method is being called
 *         (e.g. a report) pass it in here. Default 0 which means no current group.
 * @return string HTML fragment for the link.
 */
function moeworksheets_attempt_summary_link_to_reports($moeworksheets, $cm, $context, $returnzero = false,
        $currentgroup = 0) {
    global $CFG;
    $summary = moeworksheets_num_attempt_summary($moeworksheets, $cm, $returnzero, $currentgroup);
    if (!$summary) {
        return '';
    }

    require_once($CFG->dirroot . '/mod/moeworksheets/report/reportlib.php');
    $url = new moodle_url('/mod/moeworksheets/report.php', array(
            'id' => $cm->id, 'mode' => moeworksheets_report_default_report($context)));
    return html_writer::link($url, $summary);
}

/**
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool True if moeworksheets supports feature
 */
function moeworksheets_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                    return true;
        case FEATURE_GROUPINGS:                 return true;
        case FEATURE_MOD_INTRO:                 return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:   return true;
        case FEATURE_COMPLETION_HAS_RULES:      return true;
        case FEATURE_GRADE_HAS_GRADE:           return true;
        case FEATURE_GRADE_OUTCOMES:            return true;
        case FEATURE_BACKUP_MOODLE2:            return true;
        case FEATURE_SHOW_DESCRIPTION:          return true;
        case FEATURE_CONTROLS_GRADE_VISIBILITY: return true;
        case FEATURE_USES_QUESTIONS:            return true;

        default: return null;
    }
}

/**
 * @return array all other caps used in module
 */
function moeworksheets_get_extra_capabilities() {
    global $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    $caps = question_get_all_capabilities();
    $caps[] = 'moodle/site:accessallgroups';
    return $caps;
}

/**
 * This function extends the settings navigation block for the site.
 *
 * It is safe to rely on PAGE here as we will only ever be within the module
 * context when this is called
 *
 * @param settings_navigation $settings
 * @param navigation_node $moeworksheetsnode
 * @return void
 */
function moeworksheets_extend_settings_navigation($settings, $moeworksheetsnode) {
    global $PAGE, $CFG;

    // Require {@link questionlib.php}
    // Included here as we only ever want to include this file if we really need to.
    require_once($CFG->libdir . '/questionlib.php');

    // We want to add these new nodes after the Edit settings node, and before the
    // Locally assigned roles node. Of course, both of those are controlled by capabilities.
    $keys = $moeworksheetsnode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false and array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    if (has_capability('mod/moeworksheets:manageoverrides', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/moeworksheets/overrides.php', array('cmid'=>$PAGE->cm->id));
        $node = navigation_node::create(get_string('groupoverrides', 'moeworksheets'),
                new moodle_url($url, array('mode'=>'group')),
                navigation_node::TYPE_SETTING, null, 'mod_moeworksheets_groupoverrides');
        $moeworksheetsnode->add_node($node, $beforekey);

        $node = navigation_node::create(get_string('useroverrides', 'moeworksheets'),
                new moodle_url($url, array('mode'=>'user')),
                navigation_node::TYPE_SETTING, null, 'mod_moeworksheets_useroverrides');
        $moeworksheetsnode->add_node($node, $beforekey);
    }

    if (has_capability('mod/moeworksheets:manage', $PAGE->cm->context)) {
        $node = navigation_node::create(get_string('editmoeworksheets', 'moeworksheets'),
                new moodle_url('/mod/moeworksheets/edit.php', array('cmid' => $PAGE->cm->id)),
                navigation_node::TYPE_SETTING, null, 'mod_moeworksheets_edit',
                new pix_icon('t/edit', ''));
        $moeworksheetsnode->add_node($node, $beforekey);
        $moeworksheetsnode->add_node(navigation_node::create(get_string('additionalcontentlist', 'moeworksheets'),
                new moodle_url('/mod/moeworksheets/additionalcontentlist.php', array('cmid' => $PAGE->cm->id)),
                navigation_node::TYPE_SETTING, null, 'mod_moeworksheets_additionalcontentlist',
                new pix_icon('t/edit', '')),$beforekey);
    }

    if (has_capability('mod/moeworksheets:preview', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/moeworksheets/startattempt.php',
                array('cmid'=>$PAGE->cm->id, 'sesskey'=>sesskey()));
        $node = navigation_node::create(get_string('preview', 'moeworksheets'), $url,
                navigation_node::TYPE_SETTING, null, 'mod_moeworksheets_preview',
                new pix_icon('i/preview', ''));
        $moeworksheetsnode->add_node($node, $beforekey);
    }

    if (has_any_capability(array('mod/moeworksheets:viewreports', 'mod/moeworksheets:grade'), $PAGE->cm->context)) {
        require_once($CFG->dirroot . '/mod/moeworksheets/report/reportlib.php');
        $reportlist = moeworksheets_report_list($PAGE->cm->context);

        $url = new moodle_url('/mod/moeworksheets/report.php',
                array('id' => $PAGE->cm->id, 'mode' => reset($reportlist)));
        $reportnode = $moeworksheetsnode->add_node(navigation_node::create(get_string('results', 'moeworksheets'), $url,
                navigation_node::TYPE_SETTING,
                null, null, new pix_icon('i/report', '')), $beforekey);

        foreach ($reportlist as $report) {
            $url = new moodle_url('/mod/moeworksheets/report.php',
                    array('id' => $PAGE->cm->id, 'mode' => $report));
            $reportnode->add_node(navigation_node::create(get_string($report, 'moeworksheets_'.$report), $url,
                    navigation_node::TYPE_SETTING,
                    null, 'moeworksheets_report_' . $report, new pix_icon('i/item', '')));
        }
    }

    question_extend_settings_navigation($moeworksheetsnode, $PAGE->cm->context)->trim_if_empty();
}

/**
 * Serves the moeworksheets files.
 *
 * @package  mod_moeworksheets
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - justsend the file
 */
function moeworksheets_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, false, $cm);

    if (!$moeworksheets = $DB->get_record('moeworksheets', array('id'=>$cm->instance))) {
        return false;
    }

    // The 'intro' area is served by pluginfile.php.
    $fileareas = array('feedback', 'content', 'app');
    if (!in_array($filearea, $fileareas)) {
        return false;
    }

    $feedbackid = (int)array_shift($args);
    if (!$feedback = $DB->get_record('moeworksheets_feedback', array('id'=>$feedbackid)) || in_array($filearea, $fileareas)) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_moeworksheets/$filearea/$feedbackid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    if($filearea == 'app') {
        send_stored_file($file, 0, 0, false, $options);
    } else {
        send_stored_file($file, 0, 0, true, $options);
    }
}

/**
 * Called via pluginfile.php -> question_pluginfile to serve files belonging to
 * a question in a question_attempt when that attempt is a moeworksheets attempt.
 *
 * @package  mod_moeworksheets
 * @category files
 * @param stdClass $course course settings object
 * @param stdClass $context context object
 * @param string $component the name of the component we are serving files for.
 * @param string $filearea the name of the file area.
 * @param int $qubaid the attempt usage id.
 * @param int $slot the id of a question in this moeworksheets attempt.
 * @param array $args the remaining bits of the file path.
 * @param bool $forcedownload whether the user must be forced to download the file.
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - justsend the file
 */
function moeworksheets_question_pluginfile($course, $context, $component,
        $filearea, $qubaid, $slot, $args, $forcedownload, array $options=array()) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/moeworksheets/locallib.php');

    $attemptobj = moeworksheets_attempt::create_from_usage_id($qubaid);
    require_login($attemptobj->get_course(), false, $attemptobj->get_cm());

    if ($attemptobj->is_own_attempt() && !$attemptobj->is_finished()) {
        // In the middle of an attempt.
        if (!$attemptobj->is_preview_user()) {
            $attemptobj->require_capability('mod/moeworksheets:attempt');
        }
        $isreviewing = false;

    } else {
        // Reviewing an attempt.
        $attemptobj->check_review_capability();
        $isreviewing = true;
    }

    if (!$attemptobj->check_file_access($slot, $isreviewing, $context->id,
            $component, $filearea, $args, $forcedownload)) {
        send_file_not_found();
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/$component/$filearea/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        send_file_not_found();
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function moeworksheets_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array(
        'mod-moeworksheets-*'       => get_string('page-mod-moeworksheets-x', 'moeworksheets'),
        'mod-moeworksheets-view'    => get_string('page-mod-moeworksheets-view', 'moeworksheets'),
        'mod-moeworksheets-attempt' => get_string('page-mod-moeworksheets-attempt', 'moeworksheets'),
        'mod-moeworksheets-summary' => get_string('page-mod-moeworksheets-summary', 'moeworksheets'),
        'mod-moeworksheets-review'  => get_string('page-mod-moeworksheets-review', 'moeworksheets'),
        'mod-moeworksheets-edit'    => get_string('page-mod-moeworksheets-edit', 'moeworksheets'),
        'mod-moeworksheets-report'  => get_string('page-mod-moeworksheets-report', 'moeworksheets'),
    );
    return $module_pagetype;
}

/**
 * @return the options for moeworksheets navigation.
 */
function moeworksheets_get_navigation_options() {
    return array(
        moeworksheets_NAVMETHOD_FREE => get_string('navmethod_free', 'moeworksheets'),
        moeworksheets_NAVMETHOD_SEQ  => get_string('navmethod_seq', 'moeworksheets')
    );
}

/**
 * Obtains the automatic completion state for this moeworksheets on any conditions
 * in moeworksheets settings, such as if all attempts are used or a certain grade is achieved.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not. (If no conditions, then return
 *   value depends on comparison type)
 */
function moeworksheets_get_completion_state($course, $cm, $userid, $type) {
    global $DB;
    global $CFG;

    $moeworksheets = $DB->get_record('moeworksheets', array('id' => $cm->instance), '*', MUST_EXIST);
    if (!$moeworksheets->completionattemptsexhausted && !$moeworksheets->completionpass) {
        return $type;
    }

    // Check if the user has used up all attempts.
    if ($moeworksheets->completionattemptsexhausted) {
        $attempts = moeworksheets_get_user_attempts($moeworksheets->id, $userid, 'finished', true);
        if ($attempts) {
            $lastfinishedattempt = end($attempts);
            $context = context_module::instance($cm->id);
            $moeworksheetsobj = moeworksheets::create($moeworksheets->id, $userid);
            $accessmanager = new moeworksheets_access_manager($moeworksheetsobj, time(),
                    has_capability('mod/moeworksheets:ignoretimelimits', $context, $userid, false));
            if ($accessmanager->is_finished(count($attempts), $lastfinishedattempt)) {
                return true;
            }
        }
    }

    // Check for passing grade.
    if ($moeworksheets->completionpass) {
        require_once($CFG->libdir . '/gradelib.php');
        $item = grade_item::fetch(array('courseid' => $course->id, 'itemtype' => 'mod',
                'itemmodule' => 'moeworksheets', 'iteminstance' => $cm->instance, 'outcomeid' => null));
        if ($item) {
            $grades = grade_grade::fetch_users_grades($item, array($userid), false);
            if (!empty($grades[$userid])) {
                return $grades[$userid]->is_passed($item);
            }
        }
    }
    return false;
}
