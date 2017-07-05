<?php
use mod_moeworksheets\form\editsubject;

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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/moeworksheets/locallib.php');
require_once($CFG->dirroot . '/question/editlib.php');

$cmid = required_param('cmid', PARAM_INT);
$id = optional_param('id', null, PARAM_INT);
$action = optional_param('action', 'view', PARAM_ALPHA);

list($moeworksheets, $cm) = get_module_from_cmid($cmid);
$courseid = $cm->course;
require_login($courseid, false, $cm);
$context = context_module::instance($cmid);
$url = new moodle_url('/mod/moeworksheets/editsubject.php', array(
    'cmid' => $cmid,
    'action' => $action,
));

// You need mod/moeworksheets:manage in addition to question capabilities to access this page.
require_capability('mod/moeworksheets:manage', $context);
navigation_node::override_active_url($url);
if($id){
    $url->param('id', $id);
}
$PAGE->set_url($url);

$PAGE->set_title(get_string('editsubject', 'moeworksheets'));

// Get the course object and related bits.
$course = $DB->get_record('course', array('id' => $moeworksheets->course), '*', MUST_EXIST);
$moeworksheetsobj = new moeworksheets($moeworksheets, $cm, $course);
$output = $PAGE->get_renderer('mod_moeworksheets', 'editsubject');
$PAGE->set_pagelayout('incourse');
$PAGE->set_pagetype('mod-moeworksheets-editsubject');
switch ($action) {
    case 'edit':
        $content = $output->editsubject_page($moeworksheetsobj, $url, $id);
        break;
    case 'connect':
        $content = $output->connecttosubject_page($moeworksheetsobj, $url, $id);
        break;
    case 'delete':
        $content =  $output->deletesubject_page($moeworksheetsobj, $url);
        break;
    case 'view':
    default:
        $content = $output->listsubject_page($moeworksheetsobj, $url);
        break;
}

$PAGE->set_heading($course->fullname);
$PAGE->set_cm($cm, $course, $moeworksheets);
$PAGE->set_title(get_string('editingmoeworksheetsx', 'moeworksheets', format_string($moeworksheets->name)));
echo $OUTPUT->header();

echo $content;

echo $OUTPUT->footer();