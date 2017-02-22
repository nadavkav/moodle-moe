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
require_once('../../report/moereports/classes/local/percourseschoollevel.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/modinfolib.php');
$download   = optional_param('download', '', PARAM_ALPHA);
global $DB, $PAGE, $OUTPUT;

$url = new moodle_url('/report/moereports/course_scoole_level.php');
$PAGE->set_url($url);

if ($download !== '') {
    $url->param('download', $download);
}
// Make sure that the user has permissions to manage moe.
require_login();

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_title(get_string('per_course_scool_level', 'report_moereports'));
$PAGE->set_heading(get_string('per_course_scool_level', 'report_moereports'));
$PAGE->set_pagelayout('standard');

$results = new percourseschoollevel();
$data = new stdClass();
$data->results = $results->displayreportfortemplates();
$data->url="$url" . "?download=xls";
$renderer = $PAGE->get_renderer('core');
$resulttable = $OUTPUT->render_from_template('report_moereports/course_scool_level', $data);

//print spreadsheet if one is asked for:
if ($download == "xls" ) {
    require_once("$CFG->libdir/excellib.class.php");
    $date= date("Ymd");
    /// Calculate file name
    $filename = "$date"."_report";
    /// Creating a workbook
    $workbook = new MoodleExcelWorkbook("-");
    /// Send HTTP headers
    $workbook->send($filename);
    /// Creating the first worksheet
    // assigning by reference gives this: Strict standards: Only variables should be assigned by reference in /data_1/www/html/moodle/moodle/mod/choicegroup/report.php on line 157
    // removed the ampersand.
    $myxls = $workbook->add_worksheet("one");
    /// Print names of all the fields
    $myxls->write_string(0,0,get_string("region", 'report_moereports'));
    $myxls->write_string(0,1,get_string("symbol", 'report_moereports'));
    $myxls->write_string(0,2,get_string("name", 'report_moereports'));
    $myxls->write_string(0,3,get_string("city", 'report_moereports'));
    $myxls->write_string(0,4,get_string("cors", 'report_moereports'));
    $myxls->write_string(0,5,get_string("makbila9", 'report_moereports'));
    $myxls->write_string(0,6,get_string("percents9", 'report_moereports'));
    $myxls->write_string(0,7,get_string("makbila10", 'report_moereports'));
    $myxls->write_string(0,8,get_string("percents10", 'report_moereports'));
    $myxls->write_string(0,9,get_string("makbila11", 'report_moereports'));
    $myxls->write_string(0,10,get_string("percents11", 'report_moereports'));
    $myxls->write_string(0,11,get_string("makbila12", 'report_moereports'));
    $myxls->write_string(0,12,get_string("percents12", 'report_moereports'));


    /// generate the data for the body of the spreadsheet
    $i=0;
    $row=1;
    foreach ($data->results as $onerec){
        $myxls->write_string($row, 0, $onerec->region);
        $myxls->write_string($row, 1, $onerec->scollSymbol);
        $myxls->write_string($row, 2, $onerec->scollName);
        $myxls->write_string($row, 3, $onerec->city);
        $myxls->write_string($row, 4, $onerec->course);
        $myxls->write_string($row, 5, $onerec->ninthgradesum);
        $myxls->write_string($row, 6, $onerec->ninthgradetotal);
        $myxls->write_string($row, 7, $onerec->tenthgradesum);
        $myxls->write_string($row, 8, $onerec->tenthgradetotal);
        $myxls->write_string($row, 9, $onerec->eleventhgradesum);
        $myxls->write_string($row, 10, $onerec->eleventhgradetotal);
        $myxls->write_string($row, 11, $onerec->twelfthgradesum);
        $myxls->write_string($row, 12, $onerec->twelfthgradetotal);
        $row++;

    }
     
    /// Close the workbook
    $workbook->close();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('per_course_scool_level', 'report_moereports'));
$PAGE->requires->js_call_amd('report_moereports/persistent_headers','init');

echo "$resulttable";
echo $OUTPUT->footer();

