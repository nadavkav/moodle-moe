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
require_once('../../config.php');

$url = new moodle_url('/report/moereports/classes_report.php');
$PAGE->set_url($url);

// Make sure that the user has permissions to manage moe.
require_login();

$context = context_system::instance();

$PAGE->set_context($context);

require_capability('moodle/site:config', $context);

$PAGE->set_title(get_string('classesinfo', 'report_moereports'));
$PAGE->set_heading(get_string('classesinfo', 'report_moereports'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('classesinfo', 'report_moereports'));

echo '<div id="reportstable"></div><button id="savereporttable">'.get_string("save", "report_moereports")."</button>";
global $DB;

$classes = $DB->get_records_sql('SELECT * FROM {moereports_reports_classes}');
$report = [];
$schools = $DB->get_fieldset_select('moereports_reports', 'symbol', '');
foreach ($classes as $key => $class) {
    // Set the fields.
    $tmp = [];
    $tmp[] = $class->id;
    $tmp[] = $class->symbol;
    $tmp[] = $class->class;
    $tmp[] = $class->studentsnumber;

    $report[] = $tmp;
}

$PAGE->requires->js_call_amd('report_moereports/classes_report', 'init', array($report, $schools));
$PAGE->requires->strings_for_js(array(
        'symbol',
        'class',
        'studentsnumber',
        'changessuccessfulsave',
        'changesnotsave',
    ), "report_moereports");
echo $OUTPUT->footer();