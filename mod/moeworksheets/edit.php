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
 * Page to edit moeworksheetszes
 *
 * This page generally has two columns:
 * The right column lists all available questions in a chosen category and
 * allows them to be edited or more to be added. This column is only there if
 * the moeworksheets does not already have student attempts
 * The left column lists all questions that have been added to the current moeworksheets.
 * The lecturer can add questions from the right hand list to the moeworksheets or remove them
 *
 * The script also processes a number of actions:
 * Actions affecting a moeworksheets:
 * up and down  Changes the order of questions and page breaks
 * addquestion  Adds a single question to the moeworksheets
 * add          Adds several selected questions to the moeworksheets
 * addrandom    Adds a certain number of random questions to the moeworksheets
 * repaginate   Re-paginates the moeworksheets
 * delete       Removes a question from the moeworksheets
 * savechanges  Saves the order and grades for questions in the moeworksheets
 *
 * @package    mod_moeworksheets
 * @copyright  1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/moeworksheets/locallib.php');
require_once($CFG->dirroot . '/mod/moeworksheets/addrandomform.php');
require_once($CFG->dirroot . '/question/editlib.php');
require_once($CFG->dirroot . '/question/category_class.php');

// These params are only passed from page request to request while we stay on
// this page otherwise they would go in question_edit_setup.
$scrollpos = optional_param('scrollpos', '', PARAM_INT);

list($thispageurl, $contexts, $cmid, $cm, $moeworksheets, $pagevars) = question_edit_setup('editq', '/mod/moeworksheets/edit.php', true);

$defaultcategoryobj = question_make_default_categories($contexts->all());
$defaultcategory = $defaultcategoryobj->id . ',' . $defaultcategoryobj->contextid;

$moeworksheetshasattempts = moeworksheets_has_attempts($moeworksheets->id);

$PAGE->set_url($thispageurl);

// Get the course object and related bits.
$course = $DB->get_record('course', array('id' => $moeworksheets->course), '*', MUST_EXIST);
$moeworksheetsobj = new moeworksheets($moeworksheets, $cm, $course);
$structure = $moeworksheetsobj->get_structure();

// You need mod/moeworksheets:manage in addition to question capabilities to access this page.
require_capability('mod/moeworksheets:manage', $contexts->lowest());

// Log this visit.
$params = array(
    'courseid' => $course->id,
    'context' => $contexts->lowest(),
    'other' => array(
        'moeworksheetsid' => $moeworksheets->id
    )
);
$event = \mod_moeworksheets\event\edit_page_viewed::create($params);
$event->trigger();

// Process commands ============================================================.

// Get the list of question ids had their check-boxes ticked.
$selectedslots = array();
$params = (array) data_submitted();
foreach ($params as $key => $value) {
    if (preg_match('!^s([0-9]+)$!', $key, $matches)) {
        $selectedslots[] = $matches[1];
    }
}

$afteractionurl = new moodle_url($thispageurl);
if ($scrollpos) {
    $afteractionurl->param('scrollpos', $scrollpos);
}

if (optional_param('repaginate', false, PARAM_BOOL) && confirm_sesskey()) {
    // Re-paginate the moeworksheets.
    $structure->check_can_be_edited();
    $questionsperpage = optional_param('questionsperpage', $moeworksheets->questionsperpage, PARAM_INT);
    moeworksheets_repaginate_questions($moeworksheets->id, $questionsperpage );
    moeworksheets_delete_previews($moeworksheets);
    redirect($afteractionurl);
}

if (($addquestion = optional_param('addquestion', 0, PARAM_INT)) && confirm_sesskey()) {
    // Add a single question to the current moeworksheets.
    $structure->check_can_be_edited();
    moeworksheets_require_question_use($addquestion);
    $addonpage = optional_param('addonpage', 0, PARAM_INT);
    moeworksheets_add_moeworksheets_question($addquestion, $moeworksheets, $addonpage);
    moeworksheets_delete_previews($moeworksheets);
    moeworksheets_update_sumgrades($moeworksheets);
    $thispageurl->param('lastchanged', $addquestion);
    redirect($afteractionurl);
}

if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $structure->check_can_be_edited();
    $addonpage = optional_param('addonpage', 0, PARAM_INT);
    // Add selected questions to the current moeworksheets.
    $rawdata = (array) data_submitted();
    foreach ($rawdata as $key => $value) { // Parse input for question ids.
        if (preg_match('!^q([0-9]+)$!', $key, $matches)) {
            $key = $matches[1];
            moeworksheets_require_question_use($key);
            moeworksheets_add_moeworksheets_question($key, $moeworksheets, $addonpage);
        }
    }
    moeworksheets_delete_previews($moeworksheets);
    moeworksheets_update_sumgrades($moeworksheets);
    redirect($afteractionurl);
}

if ($addsectionatpage = optional_param('addsectionatpage', false, PARAM_INT)) {
    // Add a section to the moeworksheets.
    $structure->check_can_be_edited();
    $structure->add_section_heading($addsectionatpage);
    moeworksheets_delete_previews($moeworksheets);
    redirect($afteractionurl);
}

if ((optional_param('addrandom', false, PARAM_BOOL)) && confirm_sesskey()) {
    // Add random questions to the moeworksheets.
    $structure->check_can_be_edited();
    $recurse = optional_param('recurse', 0, PARAM_BOOL);
    $addonpage = optional_param('addonpage', 0, PARAM_INT);
    $categoryid = required_param('categoryid', PARAM_INT);
    $randomcount = required_param('randomcount', PARAM_INT);
    moeworksheets_add_random_questions($moeworksheets, $addonpage, $categoryid, $randomcount, $recurse);

    moeworksheets_delete_previews($moeworksheets);
    moeworksheets_update_sumgrades($moeworksheets);
    redirect($afteractionurl);
}

if (optional_param('savechanges', false, PARAM_BOOL) && confirm_sesskey()) {

    // If rescaling is required save the new maximum.
    $maxgrade = unformat_float(optional_param('maxgrade', -1, PARAM_RAW));
    if ($maxgrade >= 0) {
        moeworksheets_set_grade($maxgrade, $moeworksheets);
        moeworksheets_update_all_final_grades($moeworksheets);
        moeworksheets_update_grades($moeworksheets, 0, true);
    }

    redirect($afteractionurl);
}

// Get the question bank view.
$questionbank = new mod_moeworksheets\question\bank\custom_view($contexts, $thispageurl, $course, $cm, $moeworksheets);
$questionbank->set_moeworksheets_has_attempts($moeworksheetshasattempts);
$questionbank->process_actions($thispageurl, $cm);

// End of process commands =====================================================.

$PAGE->set_pagelayout('incourse');
$PAGE->set_pagetype('mod-moeworksheets-edit');

$output = $PAGE->get_renderer('mod_moeworksheets', 'edit');

$PAGE->set_title(get_string('editingmoeworksheetsx', 'moeworksheets', format_string($moeworksheets->name)));
$PAGE->set_heading($course->fullname);
$node = $PAGE->settingsnav->find('mod_moeworksheets_edit', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}
echo $OUTPUT->header();

// Initialise the JavaScript.
$moeworksheetseditconfig = new stdClass();
$moeworksheetseditconfig->url = $thispageurl->out(true, array('qbanktool' => '0'));
$moeworksheetseditconfig->dialoglisteners = array();
$numberoflisteners = $DB->get_field_sql("
    SELECT COALESCE(MAX(page), 1)
      FROM {moeworksheets_slots}
     WHERE moeworksheetsid = ?", array($moeworksheets->id));

for ($pageiter = 1; $pageiter <= $numberoflisteners; $pageiter++) {
    $moeworksheetseditconfig->dialoglisteners[] = 'addrandomdialoglaunch_' . $pageiter;
}

$PAGE->requires->data_for_js('moeworksheets_edit_config', $moeworksheetseditconfig);
$PAGE->requires->js('/question/qengine.js');

// Questions wrapper start.
echo html_writer::start_tag('div', array('class' => 'mod-moeworksheets-edit-content'));

echo $output->edit_page($moeworksheetsobj, $structure, $contexts, $thispageurl, $pagevars);

// Questions wrapper end.
echo html_writer::end_tag('div');
echo $OUTPUT->footer();
