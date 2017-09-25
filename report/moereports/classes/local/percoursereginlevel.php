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
require_once("$CFG->dirroot/report/moereports/classes/local/region.php");

class percoursereginlevel extends moereport{

    public $region;
    public $course;
    public $eighthgradesum;
    public $eighthgradetotal;
    public $ninthgradesum;
    public $ninthgradetotal;
    public $tenthgradesum;
    public $tenthgradetotal;
    public $eleventhgradesum;
    public $eleventhgradetotal;
    public $twelfthgradesum;
    public $twelfthgradetotal;


    public function runreport() {
        global $DB;

        $results = array();
        $regions = array();
        $courses = $DB->get_records('course', array(
            'enablecompletion' => '1',
            'visible' => '1',
        ));
            $regionsobj = $DB->get_records_sql('select * from mdl_moereports_reports group by region');
        foreach ($regionsobj as $obj) {
                array_push($regions, $obj->region);
        }

        foreach ($regions as $region) {
            foreach ($courses as $course) {
                for ($i = 8; $i < 11; $i++) {
                    $results[$region][$course->category][$i] = 0;
                }
            }
        }
        foreach ($courses as $course) {
            $completion = new completion_info($course);
            $participances = $completion->get_progress_all();
            $activities = $completion->get_activities();
            foreach ($participances as $user) {
                $localuserinfo = get_complete_user_data('id', $user->id);
                $semel = isset($localuserinfo->profile['StudentMosad']) ? $localuserinfo->profile['StudentMosad'] : null;
                $regin = $DB->get_field('moereports_reports', 'region', array('symbol' => $semel));
                if ((isset($localuserinfo->profile['IsStudent']) && $localuserinfo->profile['IsStudent'] == "No") ||
                    array_search($regin, array_keys($regions)) === false) {
                    continue;
                }
                $makbila = $localuserinfo->profile['StudentKita'];
                if (!isset($semel) || $regin == false) {
                    continue;
                }
                foreach ($activities as $activity) {
                    if (array_key_exists($activity->id, $user->progress)) {
                        $thisprogress = $user->progress[$activity->id];
                        $state = $thisprogress->completionstate;
                    } else {
                        $state = COMPLETION_INCOMPLETE;
                    }

                    switch($state) {
                        case COMPLETION_COMPLETE :
                        case COMPLETION_COMPLETE_PASS :
                                $results[$regin][$course->category][$makbila]++;
                            break;
                        case COMPLETION_INCOMPLETE :
                        case COMPLETION_COMPLETE_FAIL :
                            break;
                    }
                }

            }
        }
        return $results;
    }

    public function cmp($a, $b) {
        $res = strcmp($a->region, $b->region);
        if ($res !== 0) {
            return $res;
        }

        return strcmp($a->course, $b->course);
    }

    public function displayreportfortemplates() {
        global $DB;
        $results = self::runreport();
        $resultintamplateformat = array();
        foreach ($results as $reginkey => $reginvalue) {
            foreach ($reginvalue as $corskey => $corsvalue) {
                $onerecord = new percoursereginlevel();
                $onerecord->region = $reginkey;
                $onerecord->course = $DB->get_field('course_categories', 'name', array('id' => $corskey));
                foreach ($corsvalue as $gradekey => $gradevalue) {
                    switch ($gradekey){
                        case 8:
                            $onerecord->eighthgradesum = $gradevalue;
                            $den = $DB->get_field_sql("select sum(studentsnumber)
                                                              from {moereports_reports_classes} where class = ? AND symbol
                                                              in (select symbol from mdl_moereports_reports where region = ?)",
                                                              array($gradekey, $reginkey));
                            if ($den == 0) {
                                 $onerecord->eighthgradetotal = get_string('notrelevant', 'report_moereport');
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
                                $onerecord->ninthgradetotal = get_string('notrelevant', 'report_moereport');
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
                                $onerecord->tenthgradetotal = get_string('notrelevant', 'report_moereport');
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

        usort($resultintamplateformat, "cmp");
        return $resultintamplateformat;
    }
}