<?php

require_once ('../../config.php');

$url = new moodle_url('/local/moereports/classes_report.php');
$PAGE->set_url($url);

// Make sure that the user has permissions to manage moe.
require_login();

$context = context_system::instance();

$PAGE->set_context($context);

require_capability('moodle/site:config', $context);

$PAGE->set_title(get_string('classesreports', 'local_moereports'));
$PAGE->set_heading(get_string('classesreports', 'local_moereports'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('classesreports', 'local_moereports'));

echo '<div id="reportstable"></div><button id="savereporttable">'.get_string("save", "local_moereports")."</button>";
global $DB;

$classes = $DB->get_records_sql('SELECT * FROM {moereports_reports_classes}');
$report = [];
$schools = $DB->get_fieldset_select('moereports_reports','symbol','');
foreach ($classes as $key => $class) {
    //set the fields
    $tmp = [];
    $tmp[] = $class->id;
    $tmp[] = $class->symbol;
    $tmp[] = $class->class;
    $tmp[] = $class->studentsnumber;

    $report[] = $tmp;
}

$PAGE->requires->js_call_amd('local_moereports/classes_report', 'init', array($report,$schools));
$PAGE->requires->strings_for_js(array('symbol','class','studentsnumber'), "local_moereports");
echo $OUTPUT->footer();