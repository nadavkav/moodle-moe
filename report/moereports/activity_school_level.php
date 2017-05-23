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
ini_set("memory_limit", "-1");
global $DB, $PAGE, $OUTPUT;
$region = optional_param('region', '', PARAM_TEXT);
$dataformat = optional_param('dataformat', null, PARAM_ALPHA);

$url = new moodle_url('/report/moereports/activity_school_level.php');
$PAGE->set_url($url);

// Make sure that the user has permissions to manage moe.
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title(get_string('per_activity_school_level', 'report_moereports'));
$PAGE->set_heading(get_string('per_activity_school_level', 'report_moereports'));
$PAGE->set_pagelayout('standard');

$data = new stdClass();
$data->results = array();

if ((is_siteadmin()|| has_capability('report/moereport:viewall', $usercontext)) &&  $dataformat == null){ //admin view
    if(empty($region)){
        $regions = new \stdClass();
        $regions->name = array();
        $regionsnames = $DB->get_records_sql('select region from {moereports_reports} group by region');
        foreach ($regionsnames as $name) {
            $regions->name[] = $name->region;
        }
        $selectregion = $OUTPUT->render_from_template('report_moereports/regionslist', $regions);
    } else {
        $_SESSION['regionselected'] = $region;
        $sql = 'select * from mdl_moereports_acactivityschool where region=:region';
        $data->results = $DB->get_records_sql($sql, array('region' => "$region"));
        $data->results = array_values($data->results);
        foreach ($data->results as $rec) {
            unset($rec->id);
        }
    }
} elseif ($dataformat == null){ //user view
    $cond = 'where  scollsymbol in (';
    $scollsymbols = explode(',', $USER->profile['Yeshuyot']);
    foreach ($scollsymbols as $scollsymbol) {
        $cond = "$cond" . "$scollsymbol" . ",";
    }
    $cond = "$cond" . ")";
    $data->results = $DB->get_records_sql("select * from mdl_moereports_acactivityschool" . "$cond");
    $data->results = array_values($data->results);
    foreach ($data->results as $rec) {
        unset($rec->id);
    }
} else { //admin download
    $region = $_SESSION['regionselected'];
    $sql = 'select * from mdl_moereports_acactivityschool where region=:region';
    $data->results = $DB->get_records_sql($sql, array('region' => "$region"));
    $data->results = array_values($data->results);
    foreach ($data->results as $rec) {
        unset($rec->id);
    }
}
if ($dataformat != null) {
    $columns = array(
        'region' => get_string('region', 'report_moereports'),
        'symbol' => get_string('symbol', 'report_moereports'),
        'name' => get_string('name', 'report_moereports'),
        'cors' => get_string('cors', 'report_moereports'),
        'activity' => get_string('activity', 'report_moereports'),
        'makbila8' => get_string('makbila8', 'report_moereports'),
        'percents8' => get_string('percents8', 'report_moereports'),
        'makbila9' => get_string('makbila9', 'report_moereports'),
        'percents9' => get_string('percents9', 'report_moereports'),
        'makbila10' => get_string('makbila10', 'report_moereports'),
        'percents10' => get_string('percents10', 'report_moereports'),
    );
    download_as_dataformat('activity_in_region' . date('c') , $dataformat, $columns, $data->results);
}


$renderer = $PAGE->get_renderer('core');
$resulttable = $OUTPUT->render_from_template('report_moereports/scool_level', $data);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('per_activity_school_level', 'report_moereports'));
if (isset($selectregion)){
    echo $selectregion;
} else {
    echo $OUTPUT->download_dataformat_selector(get_string('excelexp', 'report_moereports'), '/report/moereports/activity_school_level.php', 'dataformat', array());
    echo $resulttable;
}

$PAGE->requires->js_call_amd('report_moereports/persistent_headers', 'init');
$PAGE->requires->js_call_amd('report_moereports/ecxelexport', 'init');
echo $OUTPUT->footer();

