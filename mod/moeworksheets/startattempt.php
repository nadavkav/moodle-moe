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
 * This script deals with starting a new attempt at a moeworksheets.
 *
 * Normally, it will end up redirecting to attempt.php - unless a password form is displayed.
 *
 * This code used to be at the top of attempt.php, if you are looking for CVS history.
 *
 * @package   mod_moeworksheets
 * @copyright 2009 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/moeworksheets/locallib.php');

// Get submitted parameters.
$id = required_param('cmid', PARAM_INT); // Course module id
$forcenew = optional_param('forcenew', false, PARAM_BOOL); // Used to force a new preview
$page = optional_param('page', -1, PARAM_INT); // Page to jump to in the attempt.
$additionalcontent = optional_param('additional', null, PARAM_INT);

if($additionalcontent) {
    $additionalcontent = "&additional=".$additionalcontent;
}

if (!$cm = get_coursemodule_from_id('moeworksheets', $id)) {
    print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error("coursemisconf");
}

$moeworksheetsobj = moeworksheets::create($cm->instance, $USER->id);
// This script should only ever be posted to, so set page URL to the view page.
$PAGE->set_url($moeworksheetsobj->view_url());

// Check login and sesskey.
require_login($moeworksheetsobj->get_course(), false, $moeworksheetsobj->get_cm());
require_sesskey();
$PAGE->set_heading($moeworksheetsobj->get_course()->fullname);

// If no questions have been set up yet redirect to edit.php or display an error.
if (!$moeworksheetsobj->has_questions()) {
    if ($moeworksheetsobj->has_capability('mod/moeworksheets:manage')) {
        redirect($moeworksheetsobj->edit_url());
    } else {
        print_error('cannotstartnoquestions', 'moeworksheets', $moeworksheetsobj->view_url());
    }
}

// Create an object to manage all the other (non-roles) access rules.
$timenow = time();
$accessmanager = $moeworksheetsobj->get_access_manager($timenow);

// Validate permissions for creating a new attempt and start a new preview attempt if required.
list($currentattemptid, $attemptnumber, $lastattempt, $messages, $page) =
    moeworksheets_validate_new_attempt($moeworksheetsobj, $accessmanager, $forcenew, $page, true);

// Check access.
if (!$moeworksheetsobj->is_preview_user() && $messages) {
    $output = $PAGE->get_renderer('mod_moeworksheets');
    print_error('attempterror', 'moeworksheets', $moeworksheetsobj->view_url(),
            $output->access_messages($messages));
}

if ($accessmanager->is_preflight_check_required($currentattemptid)) {
    // Need to do some checks before allowing the user to continue.
    $mform = $accessmanager->get_preflight_check_form(
            $moeworksheetsobj->start_attempt_url($page), $currentattemptid);

    if ($mform->is_cancelled()) {
        $accessmanager->back_to_view_page($PAGE->get_renderer('mod_moeworksheets'));

    } else if (!$mform->get_data()) {

        // Form not submitted successfully, re-display it and stop.
        $PAGE->set_url($moeworksheetsobj->start_attempt_url($page));
        $PAGE->set_title($moeworksheetsobj->get_moeworksheets_name());
        $accessmanager->setup_attempt_page($PAGE);
        $output = $PAGE->get_renderer('mod_moeworksheets');
        if (empty($moeworksheetsobj->get_moeworksheets()->showblocks)) {
            $PAGE->blocks->show_only_fake_blocks();
        }

        echo $output->start_attempt_page($moeworksheetsobj, $mform);
        die();
    }

    // Pre-flight check passed.
    $accessmanager->notify_preflight_check_passed($currentattemptid);
}
if ($currentattemptid) {
    if ($lastattempt->state == moeworksheets_attempt::OVERDUE) {
        redirect($moeworksheetsobj->summary_url($lastattempt->id));
    } else {
        redirect($moeworksheetsobj->attempt_url($currentattemptid, $page).$additionalcontent);
    }
}

$attempt = moeworksheets_prepare_and_start_new_attempt($moeworksheetsobj, $attemptnumber, $lastattempt);

// Redirect to the attempt page.
redirect($moeworksheetsobj->attempt_url($attempt->id, $page).$additionalcontent);
