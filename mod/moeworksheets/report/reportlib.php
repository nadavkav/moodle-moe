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
 * Helper functions for the moeworksheets reports.
 *
 * @package   mod_moeworksheets
 * @copyright 2008 Jamie Pratt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/moeworksheets/lib.php');
require_once($CFG->libdir . '/filelib.php');

/**
 * Takes an array of objects and constructs a multidimensional array keyed by
 * the keys it finds on the object.
 * @param array $datum an array of objects with properties on the object
 * including the keys passed as the next param.
 * @param array $keys Array of strings with the names of the properties on the
 * objects in datum that you want to index the multidimensional array by.
 * @param bool $keysunique If there is not only one object for each
 * combination of keys you are using you should set $keysunique to true.
 * Otherwise all the object will be added to a zero based array. So the array
 * returned will have count($keys) + 1 indexs.
 * @return array multidimensional array properly indexed.
 */
function moeworksheets_report_index_by_keys($datum, $keys, $keysunique = true) {
    if (!$datum) {
        return array();
    }
    $key = array_shift($keys);
    $datumkeyed = array();
    foreach ($datum as $data) {
        if ($keys || !$keysunique) {
            $datumkeyed[$data->{$key}][]= $data;
        } else {
            $datumkeyed[$data->{$key}]= $data;
        }
    }
    if ($keys) {
        foreach ($datumkeyed as $datakey => $datakeyed) {
            $datumkeyed[$datakey] = moeworksheets_report_index_by_keys($datakeyed, $keys, $keysunique);
        }
    }
    return $datumkeyed;
}

function moeworksheets_report_unindex($datum) {
    if (!$datum) {
        return $datum;
    }
    $datumunkeyed = array();
    foreach ($datum as $value) {
        if (is_array($value)) {
            $datumunkeyed = array_merge($datumunkeyed, moeworksheets_report_unindex($value));
        } else {
            $datumunkeyed[] = $value;
        }
    }
    return $datumunkeyed;
}

/**
 * Are there any questions in this moeworksheets?
 * @param int $moeworksheetsid the moeworksheets id.
 */
function moeworksheets_has_questions($moeworksheetsid) {
    global $DB;
    return $DB->record_exists('moeworksheets_slots', array('moeworksheetsid' => $moeworksheetsid));
}

/**
 * Get the slots of real questions (not descriptions) in this moeworksheets, in order.
 * @param object $moeworksheets the moeworksheets.
 * @return array of slot => $question object with fields
 *      ->slot, ->id, ->maxmark, ->number, ->length.
 */
function moeworksheets_report_get_significant_questions($moeworksheets) {
    global $DB;

    $qsbyslot = $DB->get_records_sql("
            SELECT slot.slot,
                   q.id,
                   q.length,
                   slot.maxmark

              FROM {question} q
              JOIN {moeworksheets_slots} slot ON slot.questionid = q.id

             WHERE slot.moeworksheetsid = ?
               AND q.length > 0

          ORDER BY slot.slot", array($moeworksheets->id));

    $number = 1;
    foreach ($qsbyslot as $question) {
        $question->number = $number;
        $number += $question->length;
    }

    return $qsbyslot;
}

/**
 * @param object $moeworksheets the moeworksheets settings.
 * @return bool whether, for this moeworksheets, it is possible to filter attempts to show
 *      only those that gave the final grade.
 */
function moeworksheets_report_can_filter_only_graded($moeworksheets) {
    return $moeworksheets->attempts != 1 && $moeworksheets->grademethod != moeworksheets_GRADEAVERAGE;
}

/**
 * This is a wrapper for {@link moeworksheets_report_grade_method_sql} that takes the whole moeworksheets object instead of just the grading method
 * as a param. See definition for {@link moeworksheets_report_grade_method_sql} below.
 *
 * @param object $moeworksheets
 * @param string $moeworksheetsattemptsalias sql alias for 'moeworksheets_attempts' table
 * @return string sql to test if this is an attempt that will contribute towards the grade of the user
 */
function moeworksheets_report_qm_filter_select($moeworksheets, $moeworksheetsattemptsalias = 'moeworksheetsa') {
    if ($moeworksheets->attempts == 1) {
        // This moeworksheets only allows one attempt.
        return '';
    }
    return moeworksheets_report_grade_method_sql($moeworksheets->grademethod, $moeworksheetsattemptsalias);
}

/**
 * Given a moeworksheets grading method return sql to test if this is an
 * attempt that will be contribute towards the grade of the user. Or return an
 * empty string if the grading method is moeworksheets_GRADEAVERAGE and thus all attempts
 * contribute to final grade.
 *
 * @param string $grademethod moeworksheets grading method.
 * @param string $moeworksheetsattemptsalias sql alias for 'moeworksheets_attempts' table
 * @return string sql to test if this is an attempt that will contribute towards the graded of the user
 */
function moeworksheets_report_grade_method_sql($grademethod, $moeworksheetsattemptsalias = 'moeworksheetsa') {
    switch ($grademethod) {
        case moeworksheets_GRADEHIGHEST :
            return "($moeworksheetsattemptsalias.state = 'finished' AND NOT EXISTS (
                           SELECT 1 FROM {moeworksheets_attempts} qa2
                            WHERE qa2.moeworksheets = $moeworksheetsattemptsalias.moeworksheets AND
                                qa2.userid = $moeworksheetsattemptsalias.userid AND
                                 qa2.state = 'finished' AND (
                COALESCE(qa2.sumgrades, 0) > COALESCE($moeworksheetsattemptsalias.sumgrades, 0) OR
               (COALESCE(qa2.sumgrades, 0) = COALESCE($moeworksheetsattemptsalias.sumgrades, 0) AND qa2.attempt < $moeworksheetsattemptsalias.attempt)
                                )))";

        case moeworksheets_GRADEAVERAGE :
            return '';

        case moeworksheets_ATTEMPTFIRST :
            return "($moeworksheetsattemptsalias.state = 'finished' AND NOT EXISTS (
                           SELECT 1 FROM {moeworksheets_attempts} qa2
                            WHERE qa2.moeworksheets = $moeworksheetsattemptsalias.moeworksheets AND
                                qa2.userid = $moeworksheetsattemptsalias.userid AND
                                 qa2.state = 'finished' AND
                               qa2.attempt < $moeworksheetsattemptsalias.attempt))";

        case moeworksheets_ATTEMPTLAST :
            return "($moeworksheetsattemptsalias.state = 'finished' AND NOT EXISTS (
                           SELECT 1 FROM {moeworksheets_attempts} qa2
                            WHERE qa2.moeworksheets = $moeworksheetsattemptsalias.moeworksheets AND
                                qa2.userid = $moeworksheetsattemptsalias.userid AND
                                 qa2.state = 'finished' AND
                               qa2.attempt > $moeworksheetsattemptsalias.attempt))";
    }
}

/**
 * Get the number of students whose score was in a particular band for this moeworksheets.
 * @param number $bandwidth the width of each band.
 * @param int $bands the number of bands
 * @param int $moeworksheetsid the moeworksheets id.
 * @param array $userids list of user ids.
 * @return array band number => number of users with scores in that band.
 */
function moeworksheets_report_grade_bands($bandwidth, $bands, $moeworksheetsid, $userids = array()) {
    global $DB;
    if (!is_int($bands)) {
        debugging('$bands passed to moeworksheets_report_grade_bands must be an integer. (' .
                gettype($bands) . ' passed.)', DEBUG_DEVELOPER);
        $bands = (int) $bands;
    }

    if ($userids) {
        list($usql, $params) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'u');
        $usql = "qg.userid $usql AND";
    } else {
        $usql = '';
        $params = array();
    }
    $sql = "
SELECT band, COUNT(1)

FROM (
    SELECT FLOOR(qg.grade / :bandwidth) AS band
      FROM {moeworksheets_grades} qg
     WHERE $usql qg.moeworksheets = :moeworksheetsid
) subquery

GROUP BY
    band

ORDER BY
    band";

    $params['moeworksheetsid'] = $moeworksheetsid;
    $params['bandwidth'] = $bandwidth;

    $data = $DB->get_records_sql_menu($sql, $params);

    // We need to create array elements with values 0 at indexes where there is no element.
    $data = $data + array_fill(0, $bands + 1, 0);
    ksort($data);

    // Place the maximum (perfect grade) into the last band i.e. make last
    // band for example 9 <= g <=10 (where 10 is the perfect grade) rather than
    // just 9 <= g <10.
    $data[$bands - 1] += $data[$bands];
    unset($data[$bands]);

    return $data;
}

function moeworksheets_report_highlighting_grading_method($moeworksheets, $qmsubselect, $qmfilter) {
    if ($moeworksheets->attempts == 1) {
        return '<p>' . get_string('onlyoneattemptallowed', 'moeworksheets_overview') . '</p>';

    } else if (!$qmsubselect) {
        return '<p>' . get_string('allattemptscontributetograde', 'moeworksheets_overview') . '</p>';

    } else if ($qmfilter) {
        return '<p>' . get_string('showinggraded', 'moeworksheets_overview') . '</p>';

    } else {
        return '<p>' . get_string('showinggradedandungraded', 'moeworksheets_overview',
                '<span class="gradedattempt">' . moeworksheets_get_grading_option_name($moeworksheets->grademethod) .
                '</span>') . '</p>';
    }
}

/**
 * Get the feedback text for a grade on this moeworksheets. The feedback is
 * processed ready for display.
 *
 * @param float $grade a grade on this moeworksheets.
 * @param int $moeworksheetsid the id of the moeworksheets object.
 * @return string the comment that corresponds to this grade (empty string if there is not one.
 */
function moeworksheets_report_feedback_for_grade($grade, $moeworksheetsid, $context) {
    global $DB;

    static $feedbackcache = array();

    if (!isset($feedbackcache[$moeworksheetsid])) {
        $feedbackcache[$moeworksheetsid] = $DB->get_records('moeworksheets_feedback', array('moeworksheetsid' => $moeworksheetsid));
    }

    // With CBM etc, it is possible to get -ve grades, which would then not match
    // any feedback. Therefore, we replace -ve grades with 0.
    $grade = max($grade, 0);

    $feedbacks = $feedbackcache[$moeworksheetsid];
    $feedbackid = 0;
    $feedbacktext = '';
    $feedbacktextformat = FORMAT_MOODLE;
    foreach ($feedbacks as $feedback) {
        if ($feedback->mingrade <= $grade && $grade < $feedback->maxgrade) {
            $feedbackid = $feedback->id;
            $feedbacktext = $feedback->feedbacktext;
            $feedbacktextformat = $feedback->feedbacktextformat;
            break;
        }
    }

    // Clean the text, ready for display.
    $formatoptions = new stdClass();
    $formatoptions->noclean = true;
    $feedbacktext = file_rewrite_pluginfile_urls($feedbacktext, 'pluginfile.php',
            $context->id, 'mod_moeworksheets', 'feedback', $feedbackid);
    $feedbacktext = format_text($feedbacktext, $feedbacktextformat, $formatoptions);

    return $feedbacktext;
}

/**
 * Format a number as a percentage out of $moeworksheets->sumgrades
 * @param number $rawgrade the mark to format.
 * @param object $moeworksheets the moeworksheets settings
 * @param bool $round whether to round the results ot $moeworksheets->decimalpoints.
 */
function moeworksheets_report_scale_summarks_as_percentage($rawmark, $moeworksheets, $round = true) {
    if ($moeworksheets->sumgrades == 0) {
        return '';
    }
    if (!is_numeric($rawmark)) {
        return $rawmark;
    }

    $mark = $rawmark * 100 / $moeworksheets->sumgrades;
    if ($round) {
        $mark = moeworksheets_format_grade($moeworksheets, $mark);
    }
    return $mark . '%';
}

/**
 * Returns an array of reports to which the current user has access to.
 * @return array reports are ordered as they should be for display in tabs.
 */
function moeworksheets_report_list($context) {
    global $DB;
    static $reportlist = null;
    if (!is_null($reportlist)) {
        return $reportlist;
    }

    $reports = $DB->get_records('moeworksheets_reports', null, 'displayorder DESC', 'name, capability');
    $reportdirs = core_component::get_plugin_list('moeworksheets');

    // Order the reports tab in descending order of displayorder.
    $reportcaps = array();
    foreach ($reports as $key => $report) {
        if (array_key_exists($report->name, $reportdirs)) {
            $reportcaps[$report->name] = $report->capability;
        }
    }

    // Add any other reports, which are on disc but not in the DB, on the end.
    foreach ($reportdirs as $reportname => $notused) {
        if (!isset($reportcaps[$reportname])) {
            $reportcaps[$reportname] = null;
        }
    }
    $reportlist = array();
    foreach ($reportcaps as $name => $capability) {
        if (empty($capability)) {
            $capability = 'mod/moeworksheets:viewreports';
        }
        if (has_capability($capability, $context)) {
            $reportlist[] = $name;
        }
    }
    return $reportlist;
}

/**
 * Create a filename for use when downloading data from a moeworksheets report. It is
 * expected that this will be passed to flexible_table::is_downloading, which
 * cleans the filename of bad characters and adds the file extension.
 * @param string $report the type of report.
 * @param string $courseshortname the course shortname.
 * @param string $moeworksheetsname the moeworksheets name.
 * @return string the filename.
 */
function moeworksheets_report_download_filename($report, $courseshortname, $moeworksheetsname) {
    return $courseshortname . '-' . format_string($moeworksheetsname, true) . '-' . $report;
}

/**
 * Get the default report for the current user.
 * @param object $context the moeworksheets context.
 */
function moeworksheets_report_default_report($context) {
    $reports = moeworksheets_report_list($context);
    return reset($reports);
}

/**
 * Generate a message saying that this moeworksheets has no questions, with a button to
 * go to the edit page, if the user has the right capability.
 * @param object $moeworksheets the moeworksheets settings.
 * @param object $cm the course_module object.
 * @param object $context the moeworksheets context.
 * @return string HTML to output.
 */
function moeworksheets_no_questions_message($moeworksheets, $cm, $context) {
    global $OUTPUT;

    $output = '';
    $output .= $OUTPUT->notification(get_string('noquestions', 'moeworksheets'));
    if (has_capability('mod/moeworksheets:manage', $context)) {
        $output .= $OUTPUT->single_button(new moodle_url('/mod/moeworksheets/edit.php',
        array('cmid' => $cm->id)), get_string('editmoeworksheets', 'moeworksheets'), 'get');
    }

    return $output;
}

/**
 * Should the grades be displayed in this report. That depends on the moeworksheets
 * display options, and whether the moeworksheets is graded.
 * @param object $moeworksheets the moeworksheets settings.
 * @param context $context the moeworksheets context.
 * @return bool
 */
function moeworksheets_report_should_show_grades($moeworksheets, context $context) {
    if ($moeworksheets->timeclose && time() > $moeworksheets->timeclose) {
        $when = mod_moeworksheets_display_options::AFTER_CLOSE;
    } else {
        $when = mod_moeworksheets_display_options::LATER_WHILE_OPEN;
    }
    $reviewoptions = mod_moeworksheets_display_options::make_from_moeworksheets($moeworksheets, $when);

    return moeworksheets_has_grades($moeworksheets) &&
            ($reviewoptions->marks >= question_display_options::MARK_AND_MAX ||
            has_capability('moodle/grade:viewhidden', $context));
}
