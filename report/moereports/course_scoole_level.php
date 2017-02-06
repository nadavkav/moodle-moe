<?php
require_once ('../../config.php');
require_once ('../../report/moereports/classes/local/PerCourseScollLevel.php');

require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/modinfolib.php');

$url = new moodle_url('/report/moereports/course_scoole_level.php');
$PAGE->set_url($url);

// Make sure that the user has permissions to manage moe.
require_login();

$context = context_system::instance();
$PAGE->set_context($context);

require_capability('moodle/site:config', $context);

$PAGE->set_title(get_string('per_course_scool_level', 'report_moereports'));
$PAGE->set_heading(get_string('per_course_scool_level', 'report_moereports'));
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('per_course_scool_level', 'report_moereports'));


global $DB, $PAGE,$OUTPUT;

$results = new PerCourseScollLevel();
$data = new stdClass();
$data->results = $results->displayreportfortemplates();

$renderer = $PAGE->get_renderer('core');

$result_table=$OUTPUT->render_from_template('report_moereports/course_scool_level',$data);

echo "$result_table";
echo $OUTPUT->footer();

