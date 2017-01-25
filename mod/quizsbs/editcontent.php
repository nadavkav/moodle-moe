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
use mod_quizsbs\local\additional_content;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/quizsbs/locallib.php');
require_once($CFG->dirroot . '/question/editlib.php');
require_once($CFG->dirroot . '/question/category_class.php');

// These params are only passed from page request to request while we stay on
// this page otherwise they would go in question_edit_setup.
$scrollpos = optional_param('scrollpos', '', PARAM_INT);
$id = optional_param('id', '', PARAM_INT);
$action = optional_param('action', 'edit', PARAM_ALPHA);

list($thispageurl, $contexts, $cmid, $cm, $quizsbs, $pagevars) = question_edit_setup('editq', '/mod/quizsbs/editcontent.php', true);

$defaultcategoryobj = question_make_default_categories($contexts->all());
$defaultcategory = $defaultcategoryobj->id . ',' . $defaultcategoryobj->contextid;

$quizsbshasattempts = quizsbs_has_attempts($quizsbs->id);

$PAGE->set_url($thispageurl);

// Get the course object and related bits.
$course = $DB->get_record('course', array('id' => $quizsbs->course), '*', MUST_EXIST);
$quizsbsobj = new quizsbs($quizsbs, $cm, $course);
$structure = $quizsbsobj->get_structure();

// You need mod/quizsbs:manage in addition to question capabilities to access this page.
require_capability('mod/quizsbs:manage', $contexts->lowest());

$additionalcontent = new additional_content($id);

$PAGE->set_pagelayout('incourse');
$PAGE->set_pagetype('mod-quizsbs-editcontent');

$output = $PAGE->get_renderer('mod_quizsbs', 'editcontent');

$PAGE->set_title(get_string('editingquizsbsx', 'quizsbs', format_string($quizsbs->name)));
$PAGE->set_heading($course->fullname);

switch ($action) {
    case 'edit':
    default:
        $content = $output->editcontent_page($quizsbsobj, $structure, $contexts, $thispageurl, $pagevars, $additionalcontent);
    break;
}

echo $OUTPUT->header();

echo $content;

echo $OUTPUT->footer();