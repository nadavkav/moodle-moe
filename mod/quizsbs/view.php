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
 * This page is the entry page into the quizsbs UI. Displays information about the
 * quizsbs to students and teachers, and lets students see their previous attempts.
 *
 * @package   mod_quizsbs
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/mod/quizsbs/locallib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/course/format/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or ...
$q = optional_param('q',  0, PARAM_INT);  // quizsbs ID.

if ($id) {
    if (!$cm = get_coursemodule_from_id('quizsbs', $id)) {
        print_error('invalidcoursemodule');
    }
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('coursemisconf');
    }
} else {
    if (!$quizsbs = $DB->get_record('quizsbs', array('id' => $q))) {
        print_error('invalidquizsbsid', 'quizsbs');
    }
    if (!$course = $DB->get_record('course', array('id' => $quizsbs->course))) {
        print_error('invalidcourseid');
    }
    if (!$cm = get_coursemodule_from_instance("quizsbs", $quizsbs->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

// Check login and get context.
require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/quizsbs:view', $context);

// Cache some other capabilities we use several times.
$canattempt = has_capability('mod/quizsbs:attempt', $context);
$canreviewmine = has_capability('mod/quizsbs:reviewmyattempts', $context);
$canpreview = has_capability('mod/quizsbs:preview', $context);

// Create an object to manage all the other (non-roles) access rules.
$timenow = time();
$quizsbsobj = quizsbs::create($cm->instance, $USER->id);
$accessmanager = new quizsbs_access_manager($quizsbsobj, $timenow,
        has_capability('mod/quizsbs:ignoretimelimits', $context, null, false));
$quizsbs = $quizsbsobj->get_quizsbs();

// Trigger course_module_viewed event and completion.
quizsbs_view($quizsbs, $course, $cm, $context);

// Initialize $PAGE, compute blocks.
$PAGE->set_url('/mod/quizsbs/view.php', array('id' => $cm->id));

// Create view object which collects all the information the renderer will need.
$viewobj = new mod_quizsbs_view_object();
$viewobj->accessmanager = $accessmanager;
$viewobj->canreviewmine = $canreviewmine;

// Get this user's attempts.
$attempts = quizsbs_get_user_attempts($quizsbs->id, $USER->id, 'finished', true);
$lastfinishedattempt = end($attempts);
$unfinished = false;
$unfinishedattemptid = null;
if ($unfinishedattempt = quizsbs_get_user_attempt_unfinished($quizsbs->id, $USER->id)) {
    $attempts[] = $unfinishedattempt;

    // If the attempt is now overdue, deal with that - and pass isonline = false.
    // We want the student notified in this case.
    $quizsbsobj->create_attempt_object($unfinishedattempt)->handle_if_time_expired(time(), false);

    $unfinished = $unfinishedattempt->state == quizsbs_attempt::IN_PROGRESS ||
            $unfinishedattempt->state == quizsbs_attempt::OVERDUE;
    if (!$unfinished) {
        $lastfinishedattempt = $unfinishedattempt;
    }
    $unfinishedattemptid = $unfinishedattempt->id;
    $unfinishedattempt = null; // To make it clear we do not use this again.
}
$numattempts = count($attempts);

$viewobj->attempts = $attempts;
$viewobj->attemptobjs = array();
foreach ($attempts as $attempt) {
    $viewobj->attemptobjs[] = new quizsbs_attempt($attempt, $quizsbs, $cm, $course, false);
}

// Work out the final grade, checking whether it was overridden in the gradebook.
if (!$canpreview) {
    $mygrade = quizsbs_get_best_grade($quizsbs, $USER->id);
} else if ($lastfinishedattempt) {
    // Users who can preview the quizsbs don't get a proper grade, so work out a
    // plausible value to display instead, so the page looks right.
    $mygrade = quizsbs_rescale_grade($lastfinishedattempt->sumgrades, $quizsbs, false);
} else {
    $mygrade = null;
}

$mygradeoverridden = false;
$gradebookfeedback = '';

$grading_info = grade_get_grades($course->id, 'mod', 'quizsbs', $quizsbs->id, $USER->id);
if (!empty($grading_info->items)) {
    $item = $grading_info->items[0];
    if (isset($item->grades[$USER->id])) {
        $grade = $item->grades[$USER->id];

        if ($grade->overridden) {
            $mygrade = $grade->grade + 0; // Convert to number.
            $mygradeoverridden = true;
        }
        if (!empty($grade->str_feedback)) {
            $gradebookfeedback = $grade->str_feedback;
        }
    }
}

$title = $course->shortname . ': ' . format_string($quizsbs->name);
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$output = $PAGE->get_renderer('mod_quizsbs');

// Print table with existing attempts.
if ($attempts) {
    // Work out which columns we need, taking account what data is available in each attempt.
    list($someoptions, $alloptions) = quizsbs_get_combined_reviewoptions($quizsbs, $attempts);

    $viewobj->attemptcolumn  = $quizsbs->attempts != 1;

    $viewobj->gradecolumn    = $someoptions->marks >= question_display_options::MARK_AND_MAX &&
            quizsbs_has_grades($quizsbs);
    $viewobj->markcolumn     = $viewobj->gradecolumn && ($quizsbs->grade != $quizsbs->sumgrades);
    $viewobj->overallstats   = $lastfinishedattempt && $alloptions->marks >= question_display_options::MARK_AND_MAX;

    $viewobj->feedbackcolumn = quizsbs_has_feedback($quizsbs) && $alloptions->overallfeedback;
}

$viewobj->timenow = $timenow;
$viewobj->numattempts = $numattempts;
$viewobj->mygrade = $mygrade;
$viewobj->moreattempts = $unfinished ||
        !$accessmanager->is_finished($numattempts, $lastfinishedattempt);
$viewobj->mygradeoverridden = $mygradeoverridden;
$viewobj->gradebookfeedback = $gradebookfeedback;
$viewobj->lastfinishedattempt = $lastfinishedattempt;
$viewobj->canedit = has_capability('mod/quizsbs:manage', $context);
$viewobj->editurl = new moodle_url('/mod/quizsbs/edit.php', array('cmid' => $cm->id));
$viewobj->backtocourseurl = new moodle_url('/course/view.php', array('id' => $course->id));
$viewobj->startattempturl = $quizsbsobj->start_attempt_url();

if ($accessmanager->is_preflight_check_required($unfinishedattemptid)) {
    $viewobj->preflightcheckform = $accessmanager->get_preflight_check_form(
            $viewobj->startattempturl, $unfinishedattemptid);
}
$viewobj->popuprequired = $accessmanager->attempt_must_be_in_popup();
$viewobj->popupoptions = $accessmanager->get_popup_options();

// Display information about this quizsbs.
$viewobj->infomessages = $viewobj->accessmanager->describe_rules();
if ($quizsbs->attempts != 1) {
    $viewobj->infomessages[] = get_string('gradingmethod', 'quizsbs',
            quizsbs_get_grading_option_name($quizsbs->grademethod));
}

// Determine wheter a start attempt button should be displayed.
$viewobj->quizsbshasquestions = $quizsbsobj->has_questions();
$viewobj->preventmessages = array();
if (!$viewobj->quizsbshasquestions) {
    $viewobj->buttontext = '';

} else {
    if ($unfinished) {
        if ($canattempt) {
            $viewobj->buttontext = get_string('continueattemptquizsbs', 'quizsbs');
        } else if ($canpreview) {
            $viewobj->buttontext = get_string('continuepreview', 'quizsbs');
        }

    } else {
        if ($canattempt) {
            $viewobj->preventmessages = $viewobj->accessmanager->prevent_new_attempt(
                    $viewobj->numattempts, $viewobj->lastfinishedattempt);
            if ($viewobj->preventmessages) {
                $viewobj->buttontext = '';
            } else if ($viewobj->numattempts == 0) {
                $viewobj->buttontext = get_string('attemptquizsbsnow', 'quizsbs');
            } else {
                $viewobj->buttontext = get_string('reattemptquizsbs', 'quizsbs');
            }

        } else if ($canpreview) {
            $viewobj->buttontext = get_string('previewquizsbsnow', 'quizsbs');
        }
    }

    // If, so far, we think a button should be printed, so check if they will be
    // allowed to access it.
    if ($viewobj->buttontext) {
        if (!$viewobj->moreattempts) {
            $viewobj->buttontext = '';
        } else if ($canattempt
                && $viewobj->preventmessages = $viewobj->accessmanager->prevent_access()) {
            $viewobj->buttontext = '';
        }
    }
}

$viewobj->showbacktocourse = ($viewobj->buttontext === '' &&
        course_get_format($course)->has_view_page());

echo $OUTPUT->header();

if (isguestuser()) {
    // Guests can't do a quizsbs, so offer them a choice of logging in or going back.
    echo $output->view_page_guest($course, $quizsbs, $cm, $context, $viewobj->infomessages);
} else if (!isguestuser() && !($canattempt || $canpreview
          || $viewobj->canreviewmine)) {
    // If they are not enrolled in this course in a good enough role, tell them to enrol.
    echo $output->view_page_notenrolled($course, $quizsbs, $cm, $context, $viewobj->infomessages);
} else {
    echo $output->view_page($course, $quizsbs, $cm, $context, $viewobj);
}

echo $OUTPUT->footer();
