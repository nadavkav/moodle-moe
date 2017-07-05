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
 * This script lists all the instances of moeworksheets in a particular course
 *
 * @package    mod_moeworksheets
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once("../../config.php");
require_once("locallib.php");

$id = required_param('id', PARAM_INT);
$PAGE->set_url('/mod/moeworksheets/index.php', array('id'=>$id));
if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}
$coursecontext = context_course::instance($id);
require_login($course);
$PAGE->set_pagelayout('incourse');

$params = array(
    'context' => $coursecontext
);
$event = \mod_moeworksheets\event\course_module_instance_list_viewed::create($params);
$event->trigger();

// Print the header.
$strmoeworksheetszes = get_string("modulenameplural", "moeworksheets");
$streditquestions = '';
$editqcontexts = new question_edit_contexts($coursecontext);
if ($editqcontexts->have_one_edit_tab_cap('questions')) {
    $streditquestions =
            "<form target=\"_parent\" method=\"get\" action=\"$CFG->wwwroot/question/edit.php\">
               <div>
               <input type=\"hidden\" name=\"courseid\" value=\"$course->id\" />
               <input type=\"submit\" value=\"".get_string("editquestions", "moeworksheets")."\" />
               </div>
             </form>";
}
$PAGE->navbar->add($strmoeworksheetszes);
$PAGE->set_title($strmoeworksheetszes);
$PAGE->set_button($streditquestions);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($strmoeworksheetszes, 2);

// Get all the appropriate data.
if (!$moeworksheetszes = get_all_instances_in_course("moeworksheets", $course)) {
    notice(get_string('thereareno', 'moodle', $strmoeworksheetszes), "../../course/view.php?id=$course->id");
    die;
}

// Check if we need the closing date header.
$showclosingheader = false;
$showfeedback = false;
foreach ($moeworksheetszes as $moeworksheets) {
    if ($moeworksheets->timeclose!=0) {
        $showclosingheader=true;
    }
    if (moeworksheets_has_feedback($moeworksheets)) {
        $showfeedback=true;
    }
    if ($showclosingheader && $showfeedback) {
        break;
    }
}

// Configure table for displaying the list of instances.
$headings = array(get_string('name'));
$align = array('left');

if ($showclosingheader) {
    array_push($headings, get_string('moeworksheetscloses', 'moeworksheets'));
    array_push($align, 'left');
}

if (course_format_uses_sections($course->format)) {
    array_unshift($headings, get_string('sectionname', 'format_'.$course->format));
} else {
    array_unshift($headings, '');
}
array_unshift($align, 'center');

$showing = '';

if (has_capability('mod/moeworksheets:viewreports', $coursecontext)) {
    array_push($headings, get_string('attempts', 'moeworksheets'));
    array_push($align, 'left');
    $showing = 'stats';

} else if (has_any_capability(array('mod/moeworksheets:reviewmyattempts', 'mod/moeworksheets:attempt'),
        $coursecontext)) {
    array_push($headings, get_string('grade', 'moeworksheets'));
    array_push($align, 'left');
    if ($showfeedback) {
        array_push($headings, get_string('feedback', 'moeworksheets'));
        array_push($align, 'left');
    }
    $showing = 'grades';

    $grades = $DB->get_records_sql_menu('
            SELECT qg.moeworksheets, qg.grade
            FROM {moeworksheets_grades} qg
            JOIN {moeworksheets} q ON q.id = qg.moeworksheets
            WHERE q.course = ? AND qg.userid = ?',
            array($course->id, $USER->id));
}

$table = new html_table();
$table->head = $headings;
$table->align = $align;

// Populate the table with the list of instances.
$currentsection = '';
foreach ($moeworksheetszes as $moeworksheets) {
    $cm = get_coursemodule_from_instance('moeworksheets', $moeworksheets->id);
    $context = context_module::instance($cm->id);
    $data = array();

    // Section number if necessary.
    $strsection = '';
    if ($moeworksheets->section != $currentsection) {
        if ($moeworksheets->section) {
            $strsection = $moeworksheets->section;
            $strsection = get_section_name($course, $moeworksheets->section);
        }
        if ($currentsection) {
            $learningtable->data[] = 'hr';
        }
        $currentsection = $moeworksheets->section;
    }
    $data[] = $strsection;

    // Link to the instance.
    $class = '';
    if (!$moeworksheets->visible) {
        $class = ' class="dimmed"';
    }
    $data[] = "<a$class href=\"view.php?id=$moeworksheets->coursemodule\">" .
            format_string($moeworksheets->name, true) . '</a>';

    // Close date.
    if ($moeworksheets->timeclose) {
        $data[] = userdate($moeworksheets->timeclose);
    } else if ($showclosingheader) {
        $data[] = '';
    }

    if ($showing == 'stats') {
        // The $moeworksheets objects returned by get_all_instances_in_course have the necessary $cm
        // fields set to make the following call work.
        $data[] = moeworksheets_attempt_summary_link_to_reports($moeworksheets, $cm, $context);

    } else if ($showing == 'grades') {
        // Grade and feedback.
        $attempts = moeworksheets_get_user_attempts($moeworksheets->id, $USER->id, 'all');
        list($someoptions, $alloptions) = moeworksheets_get_combined_reviewoptions(
                $moeworksheets, $attempts);

        $grade = '';
        $feedback = '';
        if ($moeworksheets->grade && array_key_exists($moeworksheets->id, $grades)) {
            if ($alloptions->marks >= question_display_options::MARK_AND_MAX) {
                $a = new stdClass();
                $a->grade = moeworksheets_format_grade($moeworksheets, $grades[$moeworksheets->id]);
                $a->maxgrade = moeworksheets_format_grade($moeworksheets, $moeworksheets->grade);
                $grade = get_string('outofshort', 'moeworksheets', $a);
            }
            if ($alloptions->overallfeedback) {
                $feedback = moeworksheets_feedback_for_grade($grades[$moeworksheets->id], $moeworksheets, $context);
            }
        }
        $data[] = $grade;
        if ($showfeedback) {
            $data[] = $feedback;
        }
    }

    $table->data[] = $data;
} // End of loop over moeworksheets instances.

// Display the table.
echo html_writer::table($table);

// Finish the page.
echo $OUTPUT->footer();
