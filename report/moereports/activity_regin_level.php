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
require_once("../../config.php");
require_once("../../report/moereports/classes/local/peractivityreginlevel.php");
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/modinfolib.php');
global $DB, $PAGE, $OUTPUT;
$download   = optional_param('download', '', PARAM_ALPHA);

$url = new moodle_url('/report/moereports/activity_regin_level.php');
$PAGE->set_url($url);

if ($download !== '') {
    $url->param('download', $download);
}
// Make sure that the user has permissions to manage moe.
require_login();

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_title(get_string('per_activity_regin_level', 'report_moereports'));
$PAGE->set_heading(get_string('per_activity_regin_level', 'report_moereports'));
$PAGE->set_pagelayout('standard');

if (is_siteadmin() || has_capability('report/moereport:viewall', $context)) {
    $data = $DB->get_records_sql('select * from mdl_moereports_activityregin');
} else {
    $cond='where region in (';
    $useryeshuyot = explode(',', $USER->profile['Yeshuyot']);
    foreach ($useryeshuyot as $yeshut) {
        $region = $DB->get_field('moereports_reports', 'region', array("symbol" => $yeshut));
        if (!array_search($region, $regions)) {
            array_push($regions, $region);
            $cond = "$cond . $region . ,";
        }

    }
    $cond = "$cond . )";
    $data = $DB->get_records_sql("select * from mdl_moereports_activityregin . $cond");
}
$renderer = $PAGE->get_renderer('core');
$resulttable = $OUTPUT->render_from_template('report_moereports/activity_regin_level', $data);



//print spreadsheet if one is asked for:
// if ($download == "xls" ) {
//     require_once("$CFG->libdir/excellib.class.php");
//     $date= date("Ymd");
//     /// Calculate file name
//     $filename = "$date"."_report";
//     /// Creating a workbook
//     $workbook = new MoodleExcelWorkbook("-");
//     /// Send HTTP headers
//     $workbook->send($filename);
//     /// Creating the first worksheet
//     // assigning by reference gives this: Strict standards: Only variables should be assigned by reference in /data_1/www/html/moodle/moodle/mod/choicegroup/report.php on line 157
//     // removed the ampersand.
//     $myxls = $workbook->add_worksheet("one");
//     /// Print names of all the fields
//     $myxls->write_string(0,0,get_string("region", 'report_moereports'));
//     $myxls->write_string(0,1,get_string("cors", 'report_moereports'));
//     $myxls->write_string(0,2,get_string("activity", 'report_moereports'));
//     $myxls->write_string(0,3,get_string("makbila8", 'report_moereports'));
//     $myxls->write_string(0,4,get_string("percents8", 'report_moereports'));
//     $myxls->write_string(0,5,get_string("makbila9", 'report_moereports'));
//     $myxls->write_string(0,6,get_string("percents9", 'report_moereports'));
//     $myxls->write_string(0,7,get_string("makbila10", 'report_moereports'));
//     $myxls->write_string(0,8,get_string("percents10", 'report_moereports'));


//     /// generate the data for the body of the spreadsheet
//     $row=1;
//     foreach ($data->results as $onerec){
//         $myxls->write_string($row, 0, $onerec->region);
//         $myxls->write_string($row, 1, $onerec->course);
//         $myxls->write_string($row, 2, $onerec->activityname);
//         $myxls->write_string($row, 3, $onerec->eighthgradesum);
//         $myxls->write_string($row, 4, $onerec->eighthgradetotal);
//         $myxls->write_string($row, 5, $onerec->ninthgradesum);
//         $myxls->write_string($row, 6, $onerec->ninthgradetotal);
//         $myxls->write_string($row, 7, $onerec->tenthgradesum);
//         $myxls->write_string($row, 8, $onerec->tenthgradetotal);

//         $row++;

//     }

//     /// Close the workbook
//     $workbook->close();
//     exit;
// }

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('per_activity_regin_level', 'report_moereports'));

echo $resulttable;


$PAGE->requires->js_call_amd('report_moereports/persistent_headers','init');

echo $OUTPUT->footer();

