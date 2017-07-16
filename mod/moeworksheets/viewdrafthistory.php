<?php
use local_notes\local\notes;
use local_notes\local\notes_form;

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
 * This page prints a list of all draft version
 *
 * It is used either by the student whose attempts this is, after the attempt,
 * or by a teacher reviewing another's attempt during or afterwards.
 *
 * @package   mod_moeworksheets
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/moeworksheets/locallib.php');
require_once($CFG->dirroot . '/mod/moeworksheets/report/reportlib.php');
global $OUTPUT;
$attemptid = required_param('attempt', PARAM_INT);


$url = new moodle_url('/mod/moeworksheets/viewdrafthistory.php', array('attempt'=>$attemptid));

$PAGE->set_url($url);

$attemptobj = moeworksheets_attempt::create($attemptid);
$draftid = notes::getnoteid('mod/moeworksheets/attempt', $attemptobj->get_attemptid());
$drafthistorytable = notes::getallnoteversions($draftid);

foreach ($drafthistorytable as $row) {
    $row->created_time = gmdate("Y-m-d\ H:i:s",$row->created_time);
    $row->shortcontent = substr(strip_tags($row->content), 0, 10) . '...';
}

// Check login.
require_login($attemptobj->get_course(), false, $attemptobj->get_cm());
$attemptobj->check_review_capability();
$data = new stdClass();


$data->results = $drafthistorytable;
$data->results = array_values($data->results);
$renderer = $PAGE->get_renderer('mod_moeworksheets');
// Set up the page view.
$PAGE->set_title(get_string('ahowdrafthistort', 'mod_moeworksheets'));
$PAGE->set_heading(get_string('ahowdrafthistort', 'mod_moeworksheets'));
$PAGE->requires->js_call_amd('mod_moeworksheets/drafthistorypage','init');
echo $OUTPUT->header();
echo $renderer->render_from_template('mod_moeworksheets/showdrafthistory', $data);
echo $OUTPUT->footer();

