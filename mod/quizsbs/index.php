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
 * This script lists all the instances of quizsbs in a particular course
 *
 * @package    mod_quizsbs
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once("../../config.php");
require_once("locallib.php");

$id = required_param('id', PARAM_INT);
$PAGE->set_url('/mod/quizsbs/index.php', array('id'=>$id));
if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}
$coursecontext = context_course::instance($id);
require_login($course);
$PAGE->set_pagelayout('incourse');

$params = array(
    'context' => $coursecontext
);
$event = \mod_quizsbs\event\course_module_instance_list_viewed::create($params);
$event->trigger();

// Print the header.
$strquizsbszes = get_string("modulenameplural", "quizsbs");
$streditquestions = '';
$editqcontexts = new question_edit_contexts($coursecontext);
if ($editqcontexts->have_one_edit_tab_cap('questions')) {
    $streditquestions =
            "<form target=\"_parent\" method=\"get\" action=\"$CFG->wwwroot/question/edit.php\">
               <div>
               <input type=\"hidden\" name=\"courseid\" value=\"$course->id\" />
               <input type=\"submit\" value=\"".get_string("editquestions", "quizsbs")."\" />
               </div>
             </form>";
}
$PAGE->navbar->add($strquizsbszes);
$PAGE->set_title($strquizsbszes);
$PAGE->set_button($streditquestions);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($strquizsbszes, 2);

// Get all the appropriate data.
if (!$quizsbszes = get_all_instances_in_course("quizsbs", $course)) {
    notice(get_string('thereareno', 'moodle', $strquizsbszes), "../../course/view.php?id=$course->id");
    die;
}

// Check if we need the closing date header.
$showclosingheader = false;
$showfeedback = false;
foreach ($quizsbszes as $quizsbs) {
    if ($quizsbs->timeclose!=0) {
        $showclosingheader=true;
    }
    if (quizsbs_has_feedback($quizsbs)) {
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
    array_push($headings, get_string('quizsbscloses', 'quizsbs'));
    array_push($align, 'left');
}

if (course_format_uses_sections($course->format)) {
    array_unshift($headings, get_string('sectionname', 'format_'.$course->format));
} else {
    array_unshift($headings, '');
}
array_unshift($align, 'center');

$showing = '';

if (has_capability('mod/quizsbs:viewreports', $coursecontext)) {
    array_push($headings, get_string('attempts', 'quizsbs'));
    array_push($align, 'left');
    $showing = 'stats';

} else if (has_any_capability(array('mod/quizsbs:reviewmyattempts', 'mod/quizsbs:attempt'),
        $coursecontext)) {
    array_push($headings, get_string('grade', 'quizsbs'));
    array_push($align, 'left');
    if ($showfeedback) {
        array_push($headings, get_string('feedback', 'quizsbs'));
        array_push($align, 'left');
    }
    $showing = 'grades';

    $grades = $DB->get_records_sql_menu('
            SELECT qg.quizsbs, qg.grade
            FROM {quizsbs_grades} qg
            JOIN {quizsbs} q ON q.id = qg.quizsbs
            WHERE q.course = ? AND qg.userid = ?',
            array($course->id, $USER->id));
}

$table = new html_table();
$table->head = $headings;
$table->align = $align;

// Populate the table with the list of instances.
$currentsection = '';
foreach ($quizsbszes as $quizsbs) {
    $cm = get_coursemodule_from_instance('quizsbs', $quizsbs->id);
    $context = context_module::instance($cm->id);
    $data = array();

    // Section number if necessary.
    $strsection = '';
    if ($quizsbs->section != $currentsection) {
        if ($quizsbs->section) {
            $strsection = $quizsbs->section;
            $strsection = get_section_name($course, $quizsbs->section);
        }
        if ($currentsection) {
            $learningtable->data[] = 'hr';
        }
        $currentsection = $quizsbs->section;
    }
    $data[] = $strsection;

    // Link to the instance.
    $class = '';
    if (!$quizsbs->visible) {
        $class = ' class="dimmed"';
    }
    $data[] = "<a$class href=\"view.php?id=$quizsbs->coursemodule\">" .
            format_string($quizsbs->name, true) . '</a>';

    // Close date.
    if ($quizsbs->timeclose) {
        $data[] = userdate($quizsbs->timeclose);
    } else if ($showclosingheader) {
        $data[] = '';
    }

    if ($showing == 'stats') {
        // The $quizsbs objects returned by get_all_instances_in_course have the necessary $cm
        // fields set to make the following call work.
        $data[] = quizsbs_attempt_summary_link_to_reports($quizsbs, $cm, $context);

    } else if ($showing == 'grades') {
        // Grade and feedback.
        $attempts = quizsbs_get_user_attempts($quizsbs->id, $USER->id, 'all');
        list($someoptions, $alloptions) = quizsbs_get_combined_reviewoptions(
                $quizsbs, $attempts);

        $grade = '';
        $feedback = '';
        if ($quizsbs->grade && array_key_exists($quizsbs->id, $grades)) {
            if ($alloptions->marks >= question_display_options::MARK_AND_MAX) {
                $a = new stdClass();
                $a->grade = quizsbs_format_grade($quizsbs, $grades[$quizsbs->id]);
                $a->maxgrade = quizsbs_format_grade($quizsbs, $quizsbs->grade);
                $grade = get_string('outofshort', 'quizsbs', $a);
            }
            if ($alloptions->overallfeedback) {
                $feedback = quizsbs_feedback_for_grade($grades[$quizsbs->id], $quizsbs, $context);
            }
        }
        $data[] = $grade;
        if ($showfeedback) {
            $data[] = $feedback;
        }
    }

    $table->data[] = $data;
} // End of loop over quizsbs instances.

// Display the table.
echo html_writer::table($table);

// Finish the page.
echo $OUTPUT->footer();
