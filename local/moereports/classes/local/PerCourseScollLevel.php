<?php
require_once ('reportsForMoe.php');

class PerCourseScollLevel extends moeReport{

    public $region;
    public $scollSymbol;
    public $scollName;
    public $city;
    public $course;
    public $ninthGradeSum=0;
    public $ninthGradeTotal="0%";
    public $tenthGradeSum=0;
    public $tenthGradeTotal="0%";
    public $eleventhGradeSum=0;
    public $eleventhGradeTotal="0%";
    public $twelfthGradeSum=0;
    public $twelfthGradeTotal="0%";

    function runReport(){
        global $DB;

        $results;
        $courses = $DB->get_records('course', array('enablecompletion'=>'1'));
        foreach ($courses as $course){
            $completion = new completion_info($course);
            $participances=$completion->get_progress_all();
            foreach ($participances  as $user){
                $semel = $DB->get_field("user_info_data", "data", array('userid' => "$user->id", 'fieldid' => '5'));
                $makbila = $DB->get_field("user_info_data", "data", array('userid' => "$user->id", 'fieldid' => '6'));
                foreach ($user->progress as $act){
                    $cors=$course->id;
                    if(!isset($results[$semel][$cors][$makbila]))
                        $results[$semel][$cors][$makbila]=1;
                        else
                            $results[$semel][$cors][$makbila]++;
                }
            }
        }
        return $results;
    }

    function displayReportForTemplates(){
        global $DB;
        $results = self::runReport();
        $resultInTamplateFormat=array();
        foreach ($results as $scoolkey => $scoolvalue) {
            foreach ($scoolvalue as $corskey => $corsvalue) {
                $oneRecord = new PerCourseScollLevel();
                $oneRecord->region = $DB->get_field('moereports_reports', 'region', array('symbol' => $scoolkey));
                $oneRecord->scollSymbol = $scoolkey;
                $oneRecord->scollName = $DB->get_field('moereports_reports', 'name', array('symbol' => $scoolkey));
                $oneRecord->city = $DB->get_field('moereports_reports', 'city', array('symbol' => $scoolkey));
                $oneRecord->course = $DB->get_field('course', 'fullname',array('id' => $corskey));
                foreach ($corsvalue as $gradekey => $gradevalue) {
                    switch ($gradekey){
                        case 9:
                            $oneRecord->ninthGradeSum=$gradevalue;
                            $oneRecord->ninthGradeTotal=(($gradevalue/$DB->get_field('moereports_reports_classes', 'studentsnumber', array('class'=>$gradekey ,'symbol'=>$scoolkey)))*100). "%";
                            break;
                        case 10:
                            $oneRecord->tenthGradeSum=$gradevalue;
                            $oneRecord->tenthGradeSum=(($gradevalue/$DB->get_field('moereports_reports_classes', 'studentsnumber', array('class'=>$gradekey ,'symbol'=>$scoolkey)))*100). "%";
                            break;
                        case 11:
                            $oneRecord->eleventhGradeSum=$gradevalue;
                            $oneRecord->eleventhGradeTotal=(($gradevalue/$DB->get_field('moereports_reports_classes', 'studentsnumber', array('class'=>$gradekey ,'symbol'=>$scoolkey)))*100). "%";
                            break;
                        case 12:
                            $oneRecord->twelfthGradeSum=$gradevalue;
                            $oneRecord->twelfthGradeTotal=(($gradevalue/$DB->get_field('moereports_reports_classes', 'studentsnumber', array('class'=>$gradekey ,'symbol'=>$scoolkey)))*100). "%";
                            break;
                    }
                }
                $oneRecord = $oneRecord ->to_std();
                array_push($resultInTamplateFormat, $oneRecord);
            }
        }
        return $resultInTamplateFormat;
    }
}