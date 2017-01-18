<?php
require_once ('../../config.php');
require_once($CFG->libdir.'/completionlib.php');


global $DB;
$count=0;
$courses = $DB->get_records('course');
foreach ($courses as $course){
    $completion = new completion_info($course);
    if ($course->enablecompletion == COMPLETION_DISABLED) {
        continue ;
    }
    $activities = $completion->get_activities();
    foreach ($activities as $act){ 
        var_dump($act);
    }
}
