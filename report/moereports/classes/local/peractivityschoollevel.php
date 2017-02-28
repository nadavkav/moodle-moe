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
defined('MOODLE_INTERNAL') || die;
require_once('../../report/moereports/classes/local/reportsformoe.php');

class peractivityschoollevel extends moereport{

    public $region;
    public $scollsymbol;
    public $scollname;
    public $course;
    public $activityname;
    public $ninthgradesum;
    public $ninthgradetotal;
    public $tenthgradesum;
    public $tenthgradetotal;
    public $eleventhgradesum;
    public $eleventhgradetotal;
    public $twelfthgradesum;
    public $twelfthgradetotal;



    public function runreport() {
        global $DB, $USER;
        $usercontext = context_user::instance($USER->id);
        
        $results = array();
        $courses = $DB->get_records('course', array('enablecompletion' => '1'));
        $semels = $DB->get_records('moereports_reports', array(), '', 'symbol');
        foreach ($semels as $semelkey => $semelvalue) {
            if (strpos($USER->profile['Yeshuyot'], (string)$semelkey) !== false || is_siteadmin() || has_capability('report/moereport:viewall', $usercontext)) {
            foreach ($courses as $course) {
                $allactivity = $DB->get_records_sql('select * from mdl_course_modules where course = ? and completion = 1', array($course->id));
                foreach ($allactivity as $acti) {
                    for ($i = 9; $i < 13; $i++) {
                        $results[$semelkey][$course->id][$acti->id][$i] = 0;
                    }
                }
            }
            }
        }
        foreach ($courses as $course) {
            $completion = new completion_info($course);
            $participances = $completion->get_progress_all();
            foreach ($participances as $user) {
                $localuserinfo = get_complete_user_data('id', $user->id);
                $semel = $localuserinfo->profile['StudentMosad'];
                $makbila = $localuserinfo->profile['StudentKita'];
                foreach ($user->progress as $act) {
                    $activity = $act->coursemoduleid;
                    $cors = $course->id;
                    if (strpos($USER->profile['Yeshuyot'], $semel) !== false || is_siteadmin()|| has_capability('report/moereport:viewall', $usercontext)) {
                             $results[$semel][$cors][$activity][$makbila]++;
                    }
                }
            }
        }
        return $results;
    }

    public function displayreportfortemplates() {
        global $DB;
        $results = self::runreport();
        $resultintamplateformat = array();
        foreach ($results as $scoolkey => $scoolvalue) {
            foreach ($scoolvalue as $corskey => $corsvalue) {
                foreach ($corsvalue as $activitykey => $activityvalue) {
                    $onerecord = new peractivityschoollevel();
                    $onerecord->region = $DB->get_field('moereports_reports', 'region', array('symbol' => $scoolkey));
                    $onerecord->scollSymbol = $scoolkey;
                    $onerecord->scollName = $DB->get_field('moereports_reports', 'name', array('symbol' => $scoolkey));
                    // Geting the activity name through get_fast_modinfo.
                    $course = $DB->get_record('course', array('id' => $corskey));
                    $insinfo = get_fast_modinfo($course);
                    $onerecord->course = $DB->get_field('course_categories', 'name', array('id' => $course->category));
                    foreach ($insinfo->instances as $cactivity) {
                        foreach ($cactivity as $acti) {
                            if ($acti->id == $activitykey) {
                                $onerecord->activityname = $acti->name;
                            }
                        }
                    }
                    foreach ($activityvalue as $gradekey => $gradevalue) {
                        switch ($gradekey){
                            case 9:
                                $onerecord->ninthgradesum = $gradevalue;
                                $den = $DB->get_field('moereports_reports_classes', 'studentsnumber',
                                    array('class' => $gradekey, 'symbol' => $scoolkey));
                                if($den == 0){
                                    $onerecord->ninthgradetotal = "אין מידע";
                                } else {
                                $onerecord->ninthgradetotal = round(($gradevalue / $den * 100),2) . "%";
                                }
                                break;
                            case 10:
                                $onerecord->tenthgradesum = $gradevalue;
                                $den = $DB->get_field('moereports_reports_classes', 'studentsnumber',
                                    array('class' => $gradekey, 'symbol' => $scoolkey));
                                if($den == 0){
                                    $onerecord->tenthgradetotal = "אין מידע";
                                } else {
                                    $onerecord->tenthgradetotal = round(($gradevalue / $den * 100),2) . "%";
                                }
                                break;
                            case 11:
                                $onerecord->eleventhgradesum = $gradevalue;
                                $den = $DB->get_field('moereports_reports_classes', 'studentsnumber',
                                    array('class' => $gradekey, 'symbol' => $scoolkey));
                                if($den == 0){
                                    $onerecord->eleventhgradetotal = "אין מידע";
                                } else {
                                    $onerecord->eleventhgradetotal = round(($gradevalue / $den * 100),2) . "%";
                                }
                                break;
                            case 12:
                                $onerecord->twelfthgradesum = $gradevalue;
                                $den = $DB->get_field('moereports_reports_classes', 'studentsnumber',
                                    array('class' => $gradekey, 'symbol' => $scoolkey));
                                if($den == 0){
                                    $onerecord->twelfthgradetotal = "אין מידע";
                                } else {
                                    $onerecord->twelfthgradetotal = round(($gradevalue / $den * 100),2) . "%";
                                }
                                break;
                        }
                    }
                    $onerecord = $onerecord->to_std();
                    array_push($resultintamplateformat, $onerecord);
                }
            }
        }
        function cmp($a, $b)
        {
            $res = strcmp($a->region, $b->region);
            if ($res !== 0){
                return $res;
            }
            $res = strcmp($a->scollName, $b->scollName);
            if ($res !== 0){
                return $res;
            }
            return strcmp($a->course, $b->course);
        }
        usort($resultintamplateformat, "cmp");
        return $resultintamplateformat;
    }
}
