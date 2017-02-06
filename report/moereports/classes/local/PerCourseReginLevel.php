<?php
require_once ('../../report/moereports/classes/local/reportsForMoe.php');

class PerCourseReginLevel extends moeReport{

    public $region;
    public $course;
    public $ninthgradesum=0;
    public $ninthgradetotal="0%";
    public $tenthgradesum=0;
    public $tenthgradetotal="0%";
    public $eleventhgradesum=0;
    public $eleventhgradetotal="0%";
    public $twelfthgradesum=0;
    public $twelfthgradetotal="0%";

    function runreport(){
        global $DB;

        $results;
        $courses = $DB->get_records('course', array('enablecompletion'=>'1'));
        foreach ($courses as $course){
            $completion = new completion_info($course);
            $participances=$completion->get_progress_all();
            foreach ($participances  as $user){
                $semel = $DB->get_field("user_info_data", "data", array('userid' => "$user->id", 'fieldid' => '5'));
                $regin = $DB->get_field('moereports_reports', 'region', array('symbol' => $semel));
                $makbila = $DB->get_field("user_info_data", "data", array('userid' => "$user->id", 'fieldid' => '6'));
                foreach ($user->progress as $act){
                    $cors=$course->id;
                    if(!isset($results[$regin][$cors][$makbila]))
                        $results[$regin][$cors][$makbila]=1;
                        else
                            $results[$regin][$cors][$makbila]++;
                }
            }
        }
        return $results;
    }

    function displayreportfortemplates(){
        global $DB;
        $results = self::runreport();
        $resultintamplateformat=array();
        foreach ($results as $reginkey => $reginvalue) {
            foreach ($reginvalue as  $corskey => $corsvalue) {
                $oneRecord = new PerCourseReginLevel();
                $oneRecord->region = $reginkey;
                $oneRecord->course = $DB->get_field('course', 'fullname',array('id' => $corskey));
                foreach ($corsvalue as $gradekey => $gradevalue) {
                    switch ($gradekey){
                        case 9:
                            $oneRecord->ninthgradesum=$gradevalue;
                            $oneRecord->ninthgradetotal=($gradevalue/$DB->get_field_sql("select sum(studentsnumber) from {moereports_reports_classes} where class = ? AND symbol in (select symbol from mdl_moereports_reports where region = ?)",array($gradekey,$reginkey))*100)."%";
                            break;
                        case 10:
                            $oneRecord->tenthgradesum=$gradevalue;
                            $oneRecord->tenthgradesum=($gradevalue/$DB->get_field_sql("select sum(studentsnumber) from {moereports_reports_classes} where class = ? AND symbol in (select symbol from mdl_moereports_reports where region = ?)",array($gradekey,$reginkey))*100)."%";
                            break;
                        case 11:
                            $oneRecord->eleventhgradesum=$gradevalue;
                            $oneRecord->eleventhgradetotal=($gradevalue/$DB->get_field_sql("select sum(studentsnumber) from {moereports_reports_classes} where class = ? AND symbol in (select symbol from mdl_moereports_reports where region = ?)",array($gradekey,$reginkey))*100)."%";
                            break;
                        case 12:
                            $oneRecord->twelfthgradesum=$gradevalue;
                            $oneRecord->twelfthgradetotal=($gradevalue/$DB->get_field_sql("select sum(studentsnumber) from {moereports_reports_classes} where class = ? AND symbol in (select symbol from mdl_moereports_reports where region = ?)",array($gradekey,$reginkey))*100)."%";
                            break;
                    }
                }
                $oneRecord = $oneRecord ->to_std();
                array_push($resultintamplateformat, $oneRecord);
            }
        }
        return $resultintamplateformat;
    }
}