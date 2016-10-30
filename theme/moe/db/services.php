<?php
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
/**
 * Web service local plugin template external functions and service definitions.
 *
 * @package    courseformatlm
 * @copyright  2016 Sysbind
 */
// We defined the web service functions to install.
$functions = array(
    'update_clock' => array(
        'classname'   => 'ajax_external',
        'methodname'  => 'update_clock_ajax',
        'classpath'   => 'theme/moe/externallib.php',
        'description' => 'give correct time',
        'type'        => 'write',
        'ajax'        => true
    ),
    
    
    /*'lm_update_course_title' => array(
        'classname'   => 'format_lm_external',
        'methodname'  => 'update_course_title',
        'classpath'   => 'course/format/lm/externallib.php',
        'description' => 'update course title',
        'type'        => 'write',
        'ajax'        => true
    ),
    
    
    'lm_coursesummary_ajax' => array(
        'classname'   => 'format_lm_external',
        'methodname'  => 'coursesummary_ajax',
        'classpath'   => 'course/format/lm/externallib.php',
        'description' => 'update course title',
        'type'        => 'read',
        'ajax'        => true
    ),
    
    'lm_update_section_ajax'=> array(
        'classname'   => 'format_lm_external',
        'methodname'  => 'update_section_ajax',
        'classpath'   => 'course/format/lm/externallib.php',
        'description' => 'update section ajax',
        'type'        => 'write',
        'ajax'        => true
        
        
    ),
    'lm_get_tasklist' => array(
        'classname'   => 'format_lm_external',
        'methodname'  => 'addtask',
        'classpath'   => 'course/format/lm/externallib.php',
        'description' => 'return the list of tasks',
        'type'        => 'read',
        'ajax'        => true
    ),
    'lm_cm_studinfo' => array(
        'classname'   => 'format_lm_external',
        'methodname'  => 'cm_studinfo',
        'classpath'   => 'course/format/lm/externallib.php',
        'description' => 'return all course modules information for students',
        'type'        => 'read',
        'ajax'        => true
    ),*/
 

);

$services = array(
   

   
   
   /* 'studentinfo' => array(
        'functions' => array ('lm_cm_studinfo'),
        'restrictedusers' =>0,
        'enabled'=>1,
        'requiredcapability' => ''
    ),*/
    
    'updateclock' => array(
        'functions' => array ('update_clock'), 
        'restrictedusers' =>0,
        'enabled'=>1,
        'requiredcapability' => ''
    )   
);
