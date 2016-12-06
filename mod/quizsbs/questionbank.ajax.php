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
 * Ajax script to update the contents of the question bank dialogue.
 *
 * @package    mod_quizsbs
 * @copyright  2014 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/quizsbs/locallib.php');
require_once($CFG->dirroot . '/question/editlib.php');

list($thispageurl, $contexts, $cmid, $cm, $quizsbs, $pagevars) =
        question_edit_setup('editq', '/mod/quizsbs/edit.php', true);

// Get the course object and related bits.
$course = $DB->get_record('course', array('id' => $quizsbs->course), '*', MUST_EXIST);
require_capability('mod/quizsbs:manage', $contexts->lowest());

// Create quizsbs question bank view.
$questionbank = new mod_quizsbs\question\bank\custom_view($contexts, $thispageurl, $course, $cm, $quizsbs);
$questionbank->set_quizsbs_has_attempts(quizsbs_has_attempts($quizsbs->id));

// Output.
$output = $PAGE->get_renderer('mod_quizsbs', 'edit');
$contents = $output->question_bank_contents($questionbank, $pagevars);
echo json_encode(array(
    'status'   => 'OK',
    'contents' => $contents,
));
