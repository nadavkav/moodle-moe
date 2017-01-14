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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/quizsbs/locallib.php');
require_once($CFG->dirroot . '/question/editlib.php');

$cmid = required_param('cmid', PARAM_INT);
$id = optional_param('id', null, PARAM_INT);

list($quizsbs, $cm) = get_module_from_cmid($cmid);
$courseid = $cm->course;
require_login($courseid, false, $cm);
$context = context_module::instance($cmid);
$url = new moodle_url('/mod/quizsbs/connect.php', array(
    'cmid' => $cmid,
));
if ($id) {
   $url->param('id', $id);
}
require_capability('mod/quizsbs:manage', $context);
navigation_node::override_active_url($url);
$PAGE->set_url($url);
$PAGE->set_title(get_string('connectcontentsandsubject', 'quizsbs'));
// Get the course object and related bits.
$course = $DB->get_record('course', array('id' => $quizsbs->course), '*', MUST_EXIST);
$quizsbsobj = new quizsbs($quizsbs, $cm, $course);
$output = $PAGE->get_renderer('mod_quizsbs', 'connectcontents');
$PAGE->set_pagelayout('incourse');
$PAGE->set_pagetype('mod-quizsbs-editcontent');

$content = $output->content_list_page($quizsbsobj, $id);

echo $OUTPUT->header();

echo $content;

echo $OUTPUT->footer();