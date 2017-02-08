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
 * This script deals with starting a new attempt at a quizsbs.
 *
 * Normally, it will end up redirecting to attempt.php - unless a password form is displayed.
 *
 * This code used to be at the top of attempt.php, if you are looking for CVS history.
 *
 * @package   mod_quizsbs
 * @copyright 2009 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/quizsbs/locallib.php');

// Get submitted parameters.
$id = required_param('cmid', PARAM_INT); // Course module id
$forcenew = optional_param('forcenew', false, PARAM_BOOL); // Used to force a new preview
$page = optional_param('page', -1, PARAM_INT); // Page to jump to in the attempt.
$additionalcontent = optional_param('additional', null, PARAM_INT);

if($additionalcontent) {
    $additionalcontent = "&additional=".$additionalcontent;
}

if (!$cm = get_coursemodule_from_id('quizsbs', $id)) {
    print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error("coursemisconf");
}

$quizsbsobj = quizsbs::create($cm->instance, $USER->id);
// This script should only ever be posted to, so set page URL to the view page.
$PAGE->set_url($quizsbsobj->view_url());

// Check login and sesskey.
require_login($quizsbsobj->get_course(), false, $quizsbsobj->get_cm());
require_sesskey();
$PAGE->set_heading($quizsbsobj->get_course()->fullname);

// If no questions have been set up yet redirect to edit.php or display an error.
if (!$quizsbsobj->has_questions()) {
    if ($quizsbsobj->has_capability('mod/quizsbs:manage')) {
        redirect($quizsbsobj->edit_url());
    } else {
        print_error('cannotstartnoquestions', 'quizsbs', $quizsbsobj->view_url());
    }
}

// Create an object to manage all the other (non-roles) access rules.
$timenow = time();
$accessmanager = $quizsbsobj->get_access_manager($timenow);

// Validate permissions for creating a new attempt and start a new preview attempt if required.
list($currentattemptid, $attemptnumber, $lastattempt, $messages, $page) =
    quizsbs_validate_new_attempt($quizsbsobj, $accessmanager, $forcenew, $page, true);

// Check access.
if (!$quizsbsobj->is_preview_user() && $messages) {
    $output = $PAGE->get_renderer('mod_quizsbs');
    print_error('attempterror', 'quizsbs', $quizsbsobj->view_url(),
            $output->access_messages($messages));
}

if ($accessmanager->is_preflight_check_required($currentattemptid)) {
    // Need to do some checks before allowing the user to continue.
    $mform = $accessmanager->get_preflight_check_form(
            $quizsbsobj->start_attempt_url($page), $currentattemptid);

    if ($mform->is_cancelled()) {
        $accessmanager->back_to_view_page($PAGE->get_renderer('mod_quizsbs'));

    } else if (!$mform->get_data()) {

        // Form not submitted successfully, re-display it and stop.
        $PAGE->set_url($quizsbsobj->start_attempt_url($page));
        $PAGE->set_title($quizsbsobj->get_quizsbs_name());
        $accessmanager->setup_attempt_page($PAGE);
        $output = $PAGE->get_renderer('mod_quizsbs');
        if (empty($quizsbsobj->get_quizsbs()->showblocks)) {
            $PAGE->blocks->show_only_fake_blocks();
        }

        echo $output->start_attempt_page($quizsbsobj, $mform);
        die();
    }

    // Pre-flight check passed.
    $accessmanager->notify_preflight_check_passed($currentattemptid);
}
if ($currentattemptid) {
    if ($lastattempt->state == quizsbs_attempt::OVERDUE) {
        redirect($quizsbsobj->summary_url($lastattempt->id));
    } else {
        redirect($quizsbsobj->attempt_url($currentattemptid, $page).$additionalcontent);
    }
}

$attempt = quizsbs_prepare_and_start_new_attempt($quizsbsobj, $attemptnumber, $lastattempt);

// Redirect to the attempt page.
redirect($quizsbsobj->attempt_url($attempt->id, $page).$additionalcontent);
