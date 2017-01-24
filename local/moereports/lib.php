<?php


function local_moereports_extend_navigation($moereportsnode){
    global $CFG, $PAGE, $USER;
  
    $previewnode = $PAGE->navigation->add(get_string('pluginname', 'local_moereports'), navigation_node::TYPE_CONTAINER);
    $thingnode = $previewnode->add( get_string('per_activity_school_level', 'local_moereports'), new moodle_url( $CFG->wwwroot.'/local/moereports/activity_scoole_level.php'));   
    $thingnode = $previewnode->add( get_string('per_course_scool_level', 'local_moereports'), new moodle_url( $CFG->wwwroot.'/local/moereports/course_scoole_level.php'));   
    $thingnode = $previewnode->add( get_string('per_activity_regin_level', 'local_moereports'), new moodle_url( $CFG->wwwroot.'/local/moereports/activity_regin_level.php'));
    $thingnode = $previewnode->add( get_string('per_course_regin_level', 'local_moereports'), new moodle_url( $CFG->wwwroot.'/local/moereports/course_regin_level.php'));
    
}

