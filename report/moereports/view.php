<?php
require_once ('../../config.php');

$url = new moodle_url('/reports/moereports/view.php');

$PAGE->set_url($url);

// Make sure that the user has permissions to manage moe.
require_login();

$context = context_system::instance();

$PAGE->set_context($context);

require_capability('moodle/site:config', $context);

$PAGE->set_title(get_string('reports', 'report_moereports'));
$PAGE->set_heading(get_string('reports', 'report_moereports'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reports', 'report_moereports'));

echo '<div id="reportstable"></div><button id="savereporttable">'.get_string("save", "report_moereports")."</button>";
global $DB;

$schools = $DB->get_records_sql('SELECT * FROM {moereports_reports}');
$report = [];

foreach ($schools as $key => $school) {

    //set the fields
    $tmp = [];
    $tmp[] = $school->id;
    $tmp[] = $school->symbol;
    $tmp[] = $school->region;
    $tmp[] = $school->name;
    $tmp[] = $school->city; 

    
    $report[] = $tmp;
}
$PAGE->requires->js_call_amd('report_moereports/reports', 'init', array($report));
$PAGE->requires->strings_for_js(array('symbol', 'name', 'region','city'), "report_moereports");
echo $OUTPUT->footer();