<?php

defined('MOODLE_INTERNAL') || die;

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function report_moereports_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $USER,$DB;

    if (isguestuser() or !isloggedin()) {
        return;
    }

    if (\core\session\manager::is_loggedinas() or $USER->id != $user->id) {
        // No peeking at somebody else's sessions!
        return;
    }

    
    if (!$USER->profile['IsStudent']){
       $school_level_access=$DB->get_field('config', 'value', array('name'=>'schools_level_access'));
       $regin_level_access=$DB->get_field('config', 'value', array('name'=>'regin_level_access'));
       
       if (strpos($school_level_access, $USER->profile['COMPLEXORGROLES']) !== false) {
           $node = new core_user\output\myprofile\node('reports', 'per_activity_school_level',get_string('per_activity_school_level', 'report_moereports'), null, new moodle_url('/report/moereports/activity_scoole_level.php'));
           $tree->add_node($node);
           $node = new core_user\output\myprofile\node('reports', 'per_course_scool_level',get_string('per_course_scool_level', 'report_moereports'), null, new moodle_url('/report/moereports/course_scoole_level.php'));
           $tree->add_node($node);
           
           if (strpos($regin_level_access, $USER->profile['COMPLEXORGROLES']) !== false){
               $node = new core_user\output\myprofile\node('reports', 'per_activity_regin_level',get_string('per_activity_regin_level', 'report_moereports'), null, new moodle_url('/report/moereports/activity_regin_level.php'));
               $tree->add_node($node);
               $node = new core_user\output\myprofile\node('reports', 'per_course_regin_level',get_string('per_course_regin_level', 'report_moereports'), null, new moodle_url('/report/moereports/course_regin_level.php'));
               $tree->add_node($node);
               
           }       
       }
       
    }
    return true;
}


