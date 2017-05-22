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
require_once($CFG->dirroot . '/lib/dataformatlib.php');
global $DB, $PAGE, $OUTPUT;

$dataformat = optional_param('dataformat', null, PARAM_ALPHA);
$url = new moodle_url('/report/moereports/course_scoole_level.php');
$PAGE->set_url($url);

// Make sure that the user has permissions to manage moe.
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$usercontext = context_user::instance($USER->id);

$PAGE->set_title(get_string('per_course_scool_level', 'report_moereports'));
$PAGE->set_heading(get_string('per_course_scool_level', 'report_moereports'));
$PAGE->set_pagelayout('standard');


$data = new stdClass();
$data->results = array();

if(is_siteadmin()|| has_capability('report/moereport:viewall', $usercontext)){
    $data->results = $DB->get_records_sql('select * from mdl_moereports_courseschool');
    $data->results = array_values($data->results);
    foreach ($data->results as $rec){
        unset($rec->id);
    }
} else {
    $cond='where  scollsymbol in (';
    $scollsymbols = explode(',',$USER->profile['Yeshuyot']);
    foreach ($scollsymbols as $scollsymbol) {
        $cond = "$cond" . "$scollsymbol" . ",";
        }
    $cond = "$cond" . ")";
    $data->results = $DB->get_records_sql("select * from mdl_moereports_courseschool" . "$cond");
    $data->results = array_values($data->results);
    foreach ($data->results as $rec){
        unset($rec->id);
    }
}

$renderer = $PAGE->get_renderer('core');
$resulttable = $OUTPUT->render_from_template('report_moereports/course_scool_level', $data);

if ($dataformat != null){
    $columns = array(
        'region' => get_string('region','report_moereports'),
        'symbol' => get_string('symbol','report_moereports'),
        'name' => get_string('name','report_moereports'),
        'cors' => get_string('cors','report_moereports'),
        'makbila8' => get_string('makbila8','report_moereports'),
        'percents8' => get_string('percents8','report_moereports'),
        'makbila9' => get_string('makbila9','report_moereports'),
        'percents9' => get_string('percents9','report_moereports'),
        'makbila10' => get_string('makbila10','report_moereports'),
        'percents10' => get_string('percents10','report_moereports'),
    );
    download_as_dataformat('activity_in_region' . date('c') , $dataformat, $columns, $data->results);
}


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('per_course_scool_level', 'report_moereports'));
echo $OUTPUT->download_dataformat_selector(get_string('excelexp', 'report_moereports'), '/report/moereports/course_scoole_level.php', 'dataformat', array());

$PAGE->requires->js_call_amd('report_moereports/persistent_headers','init');

echo "$resulttable";
echo $OUTPUT->footer();

