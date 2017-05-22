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
use report_moereports\local\region;
require_once("$CFG->dirroot/report/moereports/classes/local/reportsformoe.php");


class peractivityreginlevel extends moeReport{

    public $region;
    public $course;
    public $activityname;
    public $eighthgradesum;
    public $eighthgradetotal;
    public $ninthgradesum;
    public $ninthgradetotal;
    public $tenthgradesum;
    public $tenthgradetotal;


    public function runreport() {
        global $DB;

        $results = array();
        $regions = array();
        $courses = $DB->get_records('course', array('enablecompletion' => '1'));

        // Get all regins
            $regionsobj = $DB->get_records_sql('select * from mdl_moereports_reports group by region');
            foreach ($regionsobj as $obj) {
                array_push($regions, $obj->region);
            }


        // Create zero array for report view for all activitys in all courses for each region
        foreach ($regions as $region) {
            foreach ($courses as $course) {
                $allactivity = $DB->get_records_sql('select * from mdl_course_modules where course = ?', array($course->id));
                foreach ($allactivity as $acti) {
                    for ($i = 8; $i < 11; $i++) {
                        $results[$region][$course->category][$acti->id][$i] = 0;
                    }
                }
            }
        }

        foreach ($courses as $course) {
            $completion = new completion_info($course);
            $participances = $completion->get_progress_all();
            $activities=  $completion->get_activities();
            foreach ($participances as $user) {
                $localuserinfo = get_complete_user_data('id', $user->id);
                $semel = isset($localuserinfo->profile['StudentMosad']) ? $localuserinfo->profile['StudentMosad'] : null;
                $kita = isset($localuserinfo->profile['StudentKita']) ? $localuserinfo->profile['StudentKita'] : null;
                $regin = $DB->get_field('moereports_reports', 'region', array(
                    'symbol' => $semel
                ));
                if ($regin == false || $kita == null) {
                    continue;
                }
                if ((isset($localuserinfo->profile['IsStudent']) && $localuserinfo->profile['IsStudent'] == "No") || array_search($regin, array_keys($regions)) === false) {
                    continue;
                }
                foreach ($activities as $activity) {
                    if (array_key_exists($activity->id, $user->progress)) {
                        $thisprogress = $user->progress[$activity->id];
                        $state = $thisprogress->completionstate;
                    } else {
                        $state = COMPLETION_INCOMPLETE;
                    }

                    switch ($state) {
                        case COMPLETION_COMPLETE:
                        case COMPLETION_COMPLETE_PASS:
                            $results[$regin][$course->category][$activity->id][$kita] ++;
                            break;
                        case COMPLETION_INCOMPLETE:
                        case COMPLETION_COMPLETE_FAIL:
                            break;
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
        foreach ($results as $reginkey => $reginvalue) {
            foreach ($reginvalue as $categorykey => $categoryvalue) {
                $corsesincat = $DB->get_records_sql("select * from mdl_course where category =? AND enablecompletion = 1", array($categorykey));
                foreach ($corsesincat as $corskey) {
                    foreach ($categoryvalue as $activitykey => $activityvalue) {
                        $onerecord = new peractivityreginlevel();
                        $onerecord->region = $reginkey;
                        // Geting the activity name through get_fast_modinfo
                        $insinfo = get_fast_modinfo($corskey);
                        $onerecord->course = $DB->get_field('course_categories', 'name', array('id' => $categorykey));
                        foreach ($insinfo->instances as $cactivity) {
                            foreach ($cactivity as $acti) {
                                if ($acti->completion == 0){
                                    continue;
                                }
                                if ($acti->id == $activitykey) {
                                    $onerecord->activityname = $acti->name;
                                }
                            }
                        }
                        if ($onerecord->activityname == null) {
                             continue;
                        }
                        foreach ($activityvalue as $gradekey => $gradevalue) {
                            switch ($gradekey){
                                case 8:
                                    $onerecord->eighthgradesum = $gradevalue;
                                    $den = $DB->get_field_sql("select sum(studentsnumber)
                                                                from {moereports_reports_classes} where class = ? AND symbol
                                                                in (select symbol from mdl_moereports_reports where region = ?)",
                                        array($gradekey, $reginkey));
                                    if ($den == 0) {
                                        $onerecord->eighthgradetotal = "אין מידע";
                                    } else {
                                        $onerecord->eighthgradetotal = round(($gradevalue / $den * 100), 2) . "%";
                                    }
                                    break;
                                case 9:
                                    $onerecord->ninthgradesum = $gradevalue;
                                    $den = $DB->get_field_sql("select sum(studentsnumber)
                                                                from {moereports_reports_classes} where class = ? AND symbol
                                                                in (select symbol from mdl_moereports_reports where region = ?)",
                                                                array($gradekey, $reginkey));
                                    if ($den == 0) {
                                        $onerecord->ninthgradetotal = "אין מידע";
                                    } else {
                                        $onerecord->ninthgradetotal = round(($gradevalue / $den * 100), 2) . "%";
                                    }
                                    break;
                                case 10:
                                    $onerecord->tenthgradesum = $gradevalue;
                                    $den = $DB->get_field_sql("select sum(studentsnumber)
                                                                    from {moereports_reports_classes} where class = ? AND symbol
                                                                    in (select symbol from mdl_moereports_reports where region = ?)",
                                                                    array($gradekey, $reginkey));
                                    if ($den == 0) {
                                        $onerecord->tenthgradetotal = "אין מידע";
                                    } else {
                                        $onerecord->tenthgradetotal = round(($gradevalue / $den * 100), 2) . "%";
                                    }
                                    break;
                            }
                        }
                        $onerecord = $onerecord->to_std();
                        array_push($resultintamplateformat, $onerecord);
                    }
                }
            }
        }
        function cmp($a, $b) {
            $res = strcmp($a->region, $b->region);
            if ($res !== 0) {
                return $res;
            }
            $res = strcmp($a->course, $b->course);
            if ($res !== 0) {
                return $res;
            }
            return strcmp($a->activityname, $b->activityname);
        }
        usort($resultintamplateformat, "cmp");
        return $resultintamplateformat;

    }


}

