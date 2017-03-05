<?php
use report_moereports\local\region;

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
require_once('../../report/moereports/classes/local/region.php');

class percoursereginlevel extends moereport{

    public $region;
    public $course;
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
        if(is_siteadmin()|| has_capability('report/moereport:viewall', $usercontext)){
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



        foreach ($regions as $region) {
            foreach ($courses as $course) {
                for ($i = 9; $i < 13; $i++) {
                    $results[$region][$course->category][$i] = 0;
                }
            }
        }
        
        
        foreach ($courses as $course) {
            $completion = new completion_info($course);
            $participances = $completion->get_progress_all();
            foreach ($participances as $user) {
                if (isset($localuserinfo->profile['StudentMosad']) && !in_array(region::get_name_by_scool_symbol($localuserinfo->profile['StudentMosad']), $regions)){
                    continue;
                }
                $localuserinfo = get_complete_user_data('id', $user->id);
                $semel = isset($localuserinfo->profile['StudentMosad']) ? $localuserinfo->profile['StudentMosad'] : null;
                $regin = $DB->get_field('moereports_reports', 'region', array('symbol' => $semel));
                $makbila = $localuserinfo->profile['StudentKita'];
                if(!isset($semel) || $regin == false){
                    continue;
                }
                foreach ($user->progress as $act) {
                            $results[$regin][$course->category][$makbila]++;
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
                $onerecord = new percoursereginlevel();
                $onerecord->region = $reginkey;
                $onerecord->course = $DB->get_field('course_categories', 'name', array('id' => $corskey));
                foreach ($corsvalue as $gradekey => $gradevalue) {
                    switch ($gradekey){
                        case 9:
                            $onerecord->ninthgradesum = $gradevalue;
                            $den = $DB->get_field_sql("select sum(studentsnumber)
                                                            from {moereports_reports_classes} where class = ? AND symbol
                                                            in (select symbol from mdl_moereports_reports where region = ?)",
                                                            array($gradekey, $reginkey));
                            if ($den == 0){
                                $onerecord->ninthgradetotal = "אין מידע";
                            }else{
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
                            }else{
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
                            }else{
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
                            }else{
                                $onerecord->twelfthgradetotal = round(($gradevalue / $den * 100),2) . "%";
                            }
                            break;
                    }
                }
                $onerecord = $onerecord->to_std();
                array_push($resultintamplateformat, $onerecord);
            }
        }
        function cmp($a, $b)
        {
            $res = strcmp($a->region, $b->region);
            if ($res !== 0){
                return $res;
            }

            return strcmp($a->course, $b->course);
        }
        usort($resultintamplateformat, "cmp");
        return $resultintamplateformat;
    }
}