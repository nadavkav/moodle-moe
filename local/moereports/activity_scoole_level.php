<?php
require_once ('../../config.php');
require_once ('../moereports/classes/reportsForMoe.php');

require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/modinfolib.php');

$url = new moodle_url('/local/moereports/activity_scoole_level.php');
$PAGE->set_url($url);

// Make sure that the user has permissions to manage moe.
require_login();

$context = context_system::instance();
$PAGE->set_context($context);

require_capability('moodle/site:config', $context);

$PAGE->set_title(get_string('school_task', 'local_moereports'));
$PAGE->set_heading(get_string('school_task', 'local_moereports'));
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('school_task', 'local_moereports'));


global $DB, $PAGE,$OUTPUT;

$results = new PerActivityScollLevel();
$data = new stdClass();
$data->results = $results->displayReportForTemplates();

$renderer = $PAGE->get_renderer('core');

$result_table=$OUTPUT->render_from_template('local_moereports/scool_level',$data);

echo "$result_table";
echo $OUTPUT->footer();

