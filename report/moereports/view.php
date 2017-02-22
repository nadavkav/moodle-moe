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

    // Set the fields.
    $tmp = [];
    $tmp[] = $school->id;
    $tmp[] = $school->symbol;
    $tmp[] = $school->region;
    $tmp[] = $school->name;

    $report[] = $tmp;
}
$PAGE->requires->js_call_amd('report_moereports/reports', 'init', array($report));
$PAGE->requires->strings_for_js(array('symbol', 'name', 'region'), "report_moereports");
echo $OUTPUT->footer();