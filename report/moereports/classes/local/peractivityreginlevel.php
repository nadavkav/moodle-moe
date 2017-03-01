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



    public function runreport() {
        global $DB, $USER;
        $usercontext = context_user::instance($USER->id);

        $results = array();
        $regions = array();
        $courses = $DB->get_records('course', array('enablecompletion' => '1'));

        // Get all regins for current user
        if(is_siteadmin() || has_capability('report/moereport:viewall', $usercontext)){
            $regionsobj = $DB->get_records_sql('select * from mdl_moereports_reports group by region');
            foreach ($regionsobj as $obj){
                array_push($regions, $obj->region);
            }
        } else {
            $useryeshuyot= explode(',',$USER->profile['Yeshuyot']);
            foreach ($useryeshuyot as $yeshut){
                $region = $DB->get_field('moereports_reports', 'region', array("symbol" => $yeshut));
                if (!array_search($region, $regions)){
                    array_push($regions, $region);
                }

            }
        }

        // Create zero array for report view for all activitys in all courses for each region
        foreach ($regions as $region) {
            foreach ($courses as $course) {
                $allactivity = $DB->get_records_sql('select * from mdl_course_modules where course = ? and completion = 1', array($course->id));
                foreach ($allactivity as $acti) {
                    for ($i = 8; $i < 13; $i++) {
                        $results[$region][$course->id][$acti->id][$i] = 0;
                    }
                }
            }
        }

        foreach ($courses as $course) {
            $completion = new completion_info($course);
            $participances = $completion->get_progress_all();
            foreach ($participances as $user) {
                $localuserinfo = get_complete_user_data('id', $user->id);
                $semel = isset($localuserinfo->profile['StudentMosad']) ? $localuserinfo->profile['StudentMosad'] : null;
                $regin = $DB->get_field('moereports_reports', 'region', array('symbol' => $semel));
                if($regin == false){
                    continue;
                }
                $kita = $localuserinfo->profile['StudentKita'];
                foreach ($user->progress as $act) {
                    $activity = $act->coursemoduleid;
                    $cors = $course->id;
                    $localuserinfo = get_complete_user_data('id', $user->id);
                    if ((isset($localuserinfo->profile['StudentMosad']) && isset($USER->profile['Yeshuyot']) && ($localuserinfo->profile['StudentMosad'] != $USER->profile['Yeshuyot'])) && !(is_siteadmin()||has_capability('report/moereport:viewall', $usercontext))) {
                        continue;
                    } else {
                        if(!isset($results[$regin][$cors][$activity][$kita])){
                            for ($i = 8; $i < 13; $i++) {
                                if(!isset($results[$regin][$cors][$activity][$i])){
                                    $results[$regin][$cors][$activity][$i] = 0;
                                }
                            }
                        }
                        $results[$regin][$cors][$activity][$kita]++;
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
                    // Geting the activity name through get_fast_modinfo
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
                    if($onerecord->activityname == null){
                        continue;
                    }
                    foreach ($activityvalue as $gradekey => $gradevalue) {
                        switch ($gradekey){
                            case 9:
                                $onerecord->ninthgradesum = $gradevalue;
                                $den = $DB->get_field_sql("select sum(studentsnumber)
                                                                from {moereports_reports_classes} where class = ? AND symbol
                                                                in (select symbol from mdl_moereports_reports where region = ?)",
                                                                array($gradekey, $reginkey));
                                if ($den == 0){
                                    $onerecord->ninthgradetotal = "אין מידע";
                                } else {
                                    $onerecord->ninthgradetotal = round(($gradevalue / $den * 100),2) . "%";
                                }
                                break;
                            case 10:
                                $onerecord->tenthgradesum = $gradevalue;
                                $den = $DB->get_field_sql("select sum(studentsnumber)
                                                                from {moereports_reports_classes} where class = ? AND symbol
                                                                in (select symbol from mdl_moereports_reports where region = ?)",
                                                                array($gradekey, $reginkey));
                                if ($den == 0){
                                    $onerecord->tenthgradetotal = "אין מידע";
                                } else {
                                    $onerecord->tenthgradetotal = round(($gradevalue / $den * 100),2) . "%";
                                }
                                break;

                            case 11:
                                $onerecord->eleventhgradesum = $gradevalue;
                                $den = $DB->get_field_sql("select sum(studentsnumber)
                                                                from {moereports_reports_classes} where class = ? AND symbol
                                                                in (select symbol from mdl_moereports_reports where region = ?)",
                                                                array($gradekey, $reginkey));
                                if ($den == 0){
                                    $onerecord->eleventhgradetotal = "אין מידע";
                                } else {
                                    $onerecord->eleventhgradetotal = round(($gradevalue / $den * 100),2) . "%";
                                }
                                break;
                            case 12:
                                $onerecord->twelfthgradesum = $gradevalue;
                                $den = $DB->get_field_sql("select sum(studentsnumber)
                                                                from {moereports_reports_classes} where class = ? AND symbol
                                                                in (select symbol from mdl_moereports_reports where region = ?)",
                                    array($gradekey, $reginkey));
                                if ($den == 0){
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
            $res = strcmp($a->course, $b->course);
            if ($res !== 0){
                return $res;
            }
            return strcmp($a->activityname, $b->activityname);
        }
        usort($resultintamplateformat, "cmp");
        return $resultintamplateformat;

    }


}

