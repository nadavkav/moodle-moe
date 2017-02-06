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

class peractivityreginlevel extends moeReport{

    public $region;
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
                $localuserinfo = get_complete_user_data('id', $user->id);
                $semel = $localuserinfo->profile['StudentMosad'];
                $regin = $DB->get_field('moereports_reports', 'region', array('symbol' => $semel));
                $makbila = $localuserinfo->profile['StudentKita'];
                foreach ($user->progress as $act) {
                    $activity = $act->coursemoduleid;
                    $cors = $course->id;
                    if (!isset($results[$regin][$cors][$activity][$makbila])) {
                        $results[$regin][$cors][$activity][$makbila] = 1;
                    } else {
                        $results[$regin][$cors][$activity][$makbila]++;
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
            foreach ($reginvalue as $corskey => $corsvalue) {
                foreach ($corsvalue as $activitykey => $activityvalue) {
                    $onerecord = new peractivityreginlevel();
                    $onerecord->region = $reginkey;
                    $onerecord->course = $DB->get_field('course', 'fullname', array('id' => $corskey));
                    // Geting the activity name through get_fast_modinfo
                    $course = $DB->get_record('course', array('id' => $corskey));
                    $insinfo = get_fast_modinfo($course);
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
                                $onerecord->ninthgradetotal = ($gradevalue / $DB->get_field_sql("select sum(studentsnumber)
                                                                from {moereports_reports_classes} where class = ? AND symbol
                                                                in (select symbol from mdl_moereports_reports where region = ?)",
                                                                array($gradekey, $reginkey)) * 100) . "%";

                                break;
                            case 10:
                                $onerecord->tenthgradesum = $gradevalue;
                                $onerecord->tenthgradesum = ($gradevalue / $DB->get_field_sql("select sum(studentsnumber)
                                                                from {moereports_reports_classes} where class = ? AND symbol
                                                                in (select symbol from mdl_moereports_reports where region = ?)",
                                                                array($gradekey, $reginkey)) * 100) . "%";
                                break;
                            case 11:
                                $onerecord->eleventhgradesum = $gradevalue;
                                $onerecord->eleventhgradetotal = ($gradevalue / $DB->get_field_sql("select sum(studentsnumber)
                                                                from {moereports_reports_classes} where class = ? AND symbol
                                                                in (select symbol from mdl_moereports_reports where region = ?)",
                                                                array($gradekey, $reginkey)) * 100) . "%";
                                break;
                            case 12:
                                $onerecord->twelfthgradesum = $gradevalue;
                                $onerecord->twelfthgradetotal = ($gradevalue / $DB->get_field_sql("select sum(studentsnumber)
                                                                from {moereports_reports_classes} where class = ? AND symbol
                                                                in (select symbol from mdl_moereports_reports where region = ?)",
                                                                array($gradekey, $reginkey)) * 100)."%";
                                break;

                        }
                    }
                    $onerecord = $onerecord->to_std();
                    array_push($resultintamplateformat, $onerecord);
                }
            }
        }
        return $resultintamplateformat;
    }


}

