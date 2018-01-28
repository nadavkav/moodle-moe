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
require_once("$CFG->dirroot/report/moereports/classes/local/reportsformoe.php");

class percourseschoollevel extends moereport{

    public $region;
    public $scollsymbol;
    public $scollname;
    public $course;
    public $eighthgradesum;
    public $eighthgradetotal;
    public $ninthgradesum;
    public $ninthgradetotal;
    public $tenthgradesum;
    public $tenthgradetotal;



    public function runreport() {
        global $DB;

        $results = array();
        $courses = $DB->get_records_sql('select c.* from {course} c inner join {course_categories} cc on c.category=cc.id where
            c.enablecompletion=1 and c.visible=1 and cc.visible=1');

        $semels = $DB->get_records('moereports_reports', array(), '', 'symbol');
        // Set 0 to all cat in all schools
        foreach ($semels as $semelkey => $semelvalue) {
            foreach ($courses as $course) {
                for ($i = 8; $i < 11; $i++) {
                    $results[$semelkey][$course->category][$i] = 0;
                }
            }
        }

        foreach ($courses as $course) {
            $completion = new completion_info($course);
            $participances = $completion->get_progress_all();
            $activities = $completion->get_activities();
            $completionusers = array();
            foreach ($participances as $user) {
                $localuserinfo = get_complete_user_data('id', $user->id);
                if ((isset($localuserinfo->profile['IsStudent']) && $localuserinfo->profile['IsStudent'] == "No") ||
                    isset($localuserinfo->profile['StudentMosad']) && array_search($localuserinfo->profile['StudentMosad'], array_keys($semels)) === false) {
                    continue;
                }
                $semel = $localuserinfo->profile['StudentMosad'];
                $makbila = $localuserinfo->profile['StudentKita'];
                foreach ($activities as $activity) {
                    if (array_key_exists($activity->id, $user->progress) && !array_search($user->id, $completionusers)) {
                        $thisprogress = $user->progress[$activity->id];
                        $state = $thisprogress->completionstate;
                    } else {
                        $state = COMPLETION_INCOMPLETE;
                    }

                    switch($state) {
                        case COMPLETION_COMPLETE :
                        case COMPLETION_COMPLETE_PASS :
                            if (! empty($semel) && ! empty($makbila)) {
                                $completionusers[] = $user->id;
                                $results[$semel][$course->category][$makbila] ++;
                            }
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

    public function displayreportfortemplates() {
        global $DB;
        $results = self::runreport();
        $resultintamplateformat = array();
        foreach ($results as $scoolkey => $scoolvalue) {
            foreach ($scoolvalue as $corskey => $corsvalue) {

                $onerecord = new percourseschoollevel();
                $onerecord->region = $DB->get_field('moereports_reports', 'region', array('symbol' => $scoolkey));
                if (empty($onerecord->region)) {
                    continue;
                }
                $onerecord->scollsymbol = $scoolkey;
                $onerecord->scollname = $DB->get_field('moereports_reports', 'name', array('symbol' => $scoolkey));
                $onerecord->course = $DB->get_field('course_categories', 'name', array('id' => $corskey));
                foreach ($corsvalue as $gradekey => $gradevalue) {
                    switch ($gradekey){
                        case 8:
                            $onerecord->eighthgradesum = $gradevalue;
                            $den = $DB->get_field('moereports_reports_classes', 'studentsnumber',
                                array('class' => $gradekey, 'symbol' => $scoolkey));
                            if ($den == 0) {
                                $onerecord->eighthgradetotal = get_string('notrelevant', 'report_moereports');
                            } elseif ($den < $gradevalue) {
                                $onerecord->eighthgradetotal = get_string('rungtotal', 'report_moereport');
                            } else {
                                $onerecord->eighthgradetotal = round(($gradevalue / $den * 100) , 2) . "%";
                            }
                            break;
                        case 9:
                            $onerecord->ninthgradesum = $gradevalue;
                            $den = $DB->get_field('moereports_reports_classes', 'studentsnumber',
                                array('class' => $gradekey, 'symbol' => $scoolkey));
                            if ($den == 0) {
                                $onerecord->ninthgradetotal = get_string('notrelevant', 'report_moereports');
                            } elseif ($den < $gradevalue) {
                                $onerecord->ninthgradetotal = get_string('rungtotal', 'report_moereport');
                            } else {
                                $onerecord->ninthgradetotal = round(($gradevalue / $den * 100), 2) . "%";
                            }
                            break;
                        case 10:
                            $onerecord->tenthgradesum = $gradevalue;
                            $den = $DB->get_field('moereports_reports_classes', 'studentsnumber',
                                array('class' => $gradekey, 'symbol' => $scoolkey));
                            if ($den == 0) {
                                $onerecord->tenthgradetotal = get_string('notrelevant', 'report_moereports');
                            } elseif ($den < $gradevalue) {
                                $onerecord->tenthgradetotal = get_string('rungtotal', 'report_moereport');
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
        function cmp($a, $b) {
            $res = strcmp($a->region, $b->region);
            if ($res !== 0) {
                return $res;
            }
            $res = strcmp($a->scollname, $b->scollname);
            if ($res !== 0) {
                return $res;
            }
            return strcmp($a->course, $b->course);
        }
        usort($resultintamplateformat, "cmp");
        return $resultintamplateformat;
    }


}