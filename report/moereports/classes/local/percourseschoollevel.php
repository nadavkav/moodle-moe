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

class percourseschoollevel extends moereport{

    public $region;
    public $scollsymbol;
    public $scollname;
    public $city;
    public $course;
    public $ninthgradesum;
    public $ninthgradetotal;
    public $tenthgradesum;
    public $tenthgradetotal;
    public $eleventhgradesum;
    public $eleventhgradetotal;
    public $twelfthgradesum;
    public $twelfthgradetotal;

    public function __construct() {
        $this->ninthgradesum = 0;
        $this->ninthgradetotal = "0%";
        $this->tenthgradesum = 0;
        $this->tenthgradetotal = "0%";
        $this->eleventhgradesum = 0;
        $this->eleventhgradetotal = "0%";
        $this->twelfthgradesum = 0;
        $this->twelfthgradetotal = "0%";
    }

    public function runreport() {
        global $DB;

        $results;
        $courses = $DB->get_records('course', array('enablecompletion' => '1'));
        foreach ($courses as $course) {
            $completion = new completion_info($course);
            $participances = $completion->get_progress_all();
            foreach ($participances as $user) {
                $semel = $DB->get_field("user_info_data", "data", array('userid' => "$user->id", 'fieldid' => '5'));
                $makbila = $DB->get_field("user_info_data", "data", array('userid' => "$user->id", 'fieldid' => '6'));
                foreach ($user->progress as $act) {
                    $cors = $course->id;
                    if (!isset($results[$semel][$cors][$makbila])) {
                        $results[$semel][$cors][$makbila] = 1;
                    } else {
                            $results[$semel][$cors][$makbila]++;
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
                $onerecord->scollSymbol = $scoolkey;
                $onerecord->scollName = $DB->get_field('moereports_reports', 'name', array('symbol' => $scoolkey));
                $onerecord->city = $DB->get_field('moereports_reports', 'city', array('symbol' => $scoolkey));
                $onerecord->course = $DB->get_field('course', 'fullname', array('id' => $corskey));
                foreach ($corsvalue as $gradekey => $gradevalue) {
                    switch ($gradekey){
                        case 9:
                            $onerecord->ninthgradesum = $gradevalue;
                            $onerecord->ninthgradetotal = (($gradevalue / $DB->get_field('moereports_reports_classes', 'studentsnumber',
                                array('class' => $gradekey, 'symbol' => $scoolkey))) * 100). "%";
                            break;
                        case 10:
                            $onerecord->tenthgradesum = $gradevalue;
                            $onerecord->tenthgradesum = (($gradevalue / $DB->get_field('moereports_reports_classes', 'studentsnumber',
                                array('class' => $gradekey, 'symbol' => $scoolkey))) * 100). "%";
                            break;
                        case 11:
                            $onerecord->eleventhgradesum = $gradevalue;
                            $onerecord->eleventhgradetotal = (($gradevalue / $DB->get_field('moereports_reports_classes', 'studentsnumber',
                                array('class' => $gradekey, 'symbol' => $scoolkey))) * 100). "%";
                            break;
                        case 12:
                            $onerecord->twelfthgradesum = $gradevalue;
                            $onerecord->twelfthgradetotal = (($gradevalue / $DB->get_field('moereports_reports_classes', 'studentsnumber',
                                array('class' => $gradekey, 'symbol' => $scoolkey))) * 100). "%";
                            break;
                    }
                }
                $onerecord = $onerecord->to_std();
                array_push($resultintamplateformat, $onerecord);
            }
        }
        return $resultintamplateformat;
    }
}