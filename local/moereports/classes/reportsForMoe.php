<?php
abstract class  moeReport{
    
   //function  displayReportForTemplates();
   
   public function to_std():\stdClass {
       $obj = new \stdClass();
       $vars = get_object_vars($this);
       foreach ($vars as $key => $value) {
           $obj->{$key} = $value;
       }
       return $obj;
   }
    
}

class PerActivityScollLevel extends moeReport{
    
    public $region;
    public $scollSymbol;
    public $scollName;
    public $city;
    public $course;
    public $activityNAme;
    public $ninthGradeSum=0;
    public $ninthGradeTotal=0;
    public $tenthGradeSum=0;
    public $tenthGradeTotal=0;
    public $eleventhGradeSum=0;
    public $eleventhGradeTotal=0;
    public $twelfthGradeSum=0;
    public $twelfthGradeTotal=0;
    
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
                $activity = $act->coursemoduleid;
                $cors=$course->id;
                if(!isset($results[$semel][$cors][$activity][$makbila]))
                $results[$semel][$cors][$activity][$makbila]=1;
                else 
                    $results[$semel][$cors][$activity][$makbila]++;   
            }
         }
       }
       return $results;
    }
    
    function displayReportForTemplates(){
        global $DB;
        $results = self::runReport();
        $resultInTamplateFormat=array();
        $resultsCounter=0;
    foreach ($results as $scoolkey => $scoolvalue) {
        foreach ($scoolvalue as $corskey => $corsvalue) {
            foreach ($corsvalue as $activitykey => $activityvalue) {
                $oneRecord = new PerActivityScollLevel();
                $oneRecord->region = $DB->get_field('moereports_reports', 'region', array('symbol' => $scoolkey));
                $oneRecord->scollSymbol = $scoolkey;
                $oneRecord->scollName = $DB->get_field('moereports_reports', 'name', array('symbol' => $scoolkey));
                $oneRecord->city = $DB->get_field('moereports_reports', 'city', array('symbol' => $scoolkey));
                $oneRecord->course = $DB->get_field('course', 'fullname',array('id' => $corskey));
                //geting the activity name through get_fast_modinfo
                $course=$DB->get_record('course',array('id'=> $corskey));
                $insinfo = get_fast_modinfo($course);
                foreach ($insinfo->instances as $Cactivity){
                    foreach ($Cactivity as $acti)
                        if ($acti->id == $activitykey)
                            $oneRecord -> activityNAme = $acti->name;
                }
                foreach ($activityvalue as $gradekey => $gradevalue) {                           
                    switch ($gradekey){
                        case 9:
                            $oneRecord->ninthGradeSum=$gradevalue;
                            $oneRecord->ninthGradeTotal=0;
                            break;
                        case 10:
                            $oneRecord->tenthGradeSum=$gradevalue;
                            $oneRecord->tenthGradeSum=0;
                            break;
                        case 11:
                            $oneRecord->eleventhGradeSum=$gradevalue;
                            $oneRecord->eleventhGradeTotal=0;
                            break;
                        case 12:
                            $oneRecord->twelfthGradeSum=$gradevalue;
                            $oneRecord->twelfthGradeTotal=0;
                            break;
                            
                    }
                }
                $oneRecord = $oneRecord ->to_std();
                array_push($resultInTamplateFormat, $oneRecord);           
            }
        }
    }
    return $resultInTamplateFormat;
    }
}