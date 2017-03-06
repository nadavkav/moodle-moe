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
namespace report_moereports\output;

use report_moereports\local\region;
use report_moereports\local\school;

/**
 *
 * @author avi
 *
 */
class activity_school_renderer extends \plugin_renderer_base
{
    public function display_report($context, $region, $download){
        global $DB, $USER;
        ini_set('memory_limit', '8192M');

        $schoollevelaccess = $DB->get_field('config', 'value', array('name' => 'schools_level_access'));
        $schoollevelaccess = explode(',', $schoollevelaccess);
        $schools = array();
        if (!isset($USER->profile['Yeshuyot']) || !isset($USER->profile['SimpleRole'])){
            return ;
        }
        $roles = explode(',', $USER->profile['SimpleRole']);
        $mosdot = explode(',', $USER->profile['Yeshuyot']);
        $rolesinschools = array();
        foreach ($roles as $key => $role) {
            $rolesinschools[$role][] = $mosdot[$key];
        }
        $allcourses = $DB->get_records('course', array('enablecompletion' => '1'));
        if(is_siteadmin($USER->id) || has_capability('report/moereport:viewall', $context)){
            if(empty($region)){
                 $regions = new \stdClass();
                 $regions->name = array();
                 $regionsnames = $DB->get_records_sql('select region from {moereports_reports} group by region');
                 foreach ($regionsnames as $name) {
                     $regions->name[] = $name->region;
                 }
                 return $this->render_from_template('report_moereports/regionslist', $regions);
            }else {
                $region = new region($region);
                $schools = $region->get_schools();
            }
        } else {
            foreach ($rolesinschools as $role => $symboles) {
                if(in_array($role, $schoollevelaccess)){
                    foreach ($symboles as $symbole){
                        $schools[] = new school($symbole);
                    }
                }
            }
        }

        foreach ($schools as $school) {
            $region = new region($school->get_region());
            $students = $school->get_students();
            foreach ($allcourses as $course) {
                $completion = new \completion_info($course);
                $activities=  $completion->get_activities();
                if(!empty($students)){
                    $studentcomplete = $completion->get_progress_all('u.id in ('. implode(',', array_keys($students)) . ')');
                    foreach ($studentcomplete as $user) {
                        $userinfo = get_complete_user_data('id', $user->id);
                        foreach ($activities as $activity) {
                            if (array_key_exists($activity->id,$user->progress)) {
                                $thisprogress=$user->progress[$activity->id];
                                $state=$thisprogress->completionstate;
                            } else {
                                $state=COMPLETION_INCOMPLETE;
                                if(!isset($data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$userinfo->profile['StudentKita']])){
                                    $data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$userinfo->profile['StudentKita']] = 0;
                                }
                            }
                            switch($state) {
                                case COMPLETION_COMPLETE :
                                case COMPLETION_COMPLETE_PASS :
                                    if(!isset($data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$userinfo->profile['StudentKita']])){
                                        $data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$userinfo->profile['StudentKita']] = 1;
                                    } else {
                                        $data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$userinfo->profile['StudentKita']]++;
                                    }
                                    break;
                                case COMPLETION_INCOMPLETE :
                                case COMPLETION_COMPLETE_FAIL :
                                    break;
                            }
                        }
                    }
                } else {
                    foreach ($activities as $activity){
                        foreach ($school->get_levels() as $level => $value){
                            $data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$level] = 0;
                        }
                    }
                }
            }
        }

        $rows = new \stdClass();
        $rows->url = new \moodle_url($this->page->url);
        $rows->url->params(array(
            'region' => $region->get_name(),
            'download' => 'xls',
        ));
        $rows->url = $rows->url->raw_out();
        foreach ($schools as $school) {
            foreach ($allcourses as $course) {
                $completion = new \completion_info($course);
                $activities=  $completion->get_activities();
                $insinfo = get_fast_modinfo($course->id);
                    foreach ($activities as $key => $activity) {
                        if($activity->completion == 0){
                            continue;
                        }
                        $row = new \stdClass();
                        $category = $DB->get_field('course_categories', 'name', array(
                            'id' => $course->category
                        ));
                        $row->region = $school->get_region();
                        $row->symbol = $school->get_symbol();
                        $row->name = $school->get_name();
                        $row->category = $category;
                        $row->activity = $activity->name;
                        foreach ($school->get_levels() as $level => $value) {
                            if (isset($data[$row->region][$row->symbol][$course->category][$key][$level])) {
                                $row->{'count' . $level} = $data[$row->region][$row->symbol][$course->category][$activity->id][$level];
                            } else {
                                $row->{'count' . $level} = 0;
                            }
                            if ($value != 0) {
                                $row->{'counterprcent' . $level} = round($row->{'count' . $level} / $value * 100, 2) . '%';
                            } else {
                                $row->{'counterprcent' . $level} = get_string('noinformation', 'report_moereports');
                            }
                        }
                        $rows->results[] = $row;
                    }
            }
        }
        //print spreadsheet if one is asked for:
        if ($download == "xls" ) {
            require_once("$CFG->libdir/excellib.class.php");
            $date= date("Ymd");
            /// Calculate file name
            $filename = "$date"."_report";
            /// Creating a workbook
            $workbook = new \MoodleExcelWorkbook("-");
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
            $myxls->write_string(0,3,get_string("cors", 'report_moereports'));
            $myxls->write_string(0,4,get_string("activity", 'report_moereports'));
            $myxls->write_string(0,5,get_string("makbila8", 'report_moereports'));
            $myxls->write_string(0,6,get_string("percents8", 'report_moereports'));
            $myxls->write_string(0,7,get_string("makbila9", 'report_moereports'));
            $myxls->write_string(0,8,get_string("percents9", 'report_moereports'));
            $myxls->write_string(0,9,get_string("makbila10", 'report_moereports'));
            $myxls->write_string(0,10,get_string("percents10", 'report_moereports'));

            /// generate the data for the body of the spreadsheet
            $i=0;
            $row=1;
            foreach ($rows as $onerec){
                $myxls->write_string($row, 0, $onerec->region);
                $myxls->write_string($row, 1, $onerec->symbol);
                $myxls->write_string($row, 2, $onerec->name);
                $myxls->write_string($row, 3, $onerec->category);
                $myxls->write_string($row, 4, $onerec->activity);
                $myxls->write_string($row, 5, $onerec->count8);
                $myxls->write_string($row, 6, $onerec->counterprcent8);
                $myxls->write_string($row, 7, $onerec->count9);
                $myxls->write_string($row, 8, $onerec->counterprcent9);
                $myxls->write_string($row, 9, $onerec->count10);
                $myxls->write_string($row, 10, $onerec->counterprcent10);
                $row++;
            }

            /// Close the workbook
            $workbook->close();
            exit;
        }
        return $this->render_from_template('report_moereports/scool_level', $rows);
    }
}

