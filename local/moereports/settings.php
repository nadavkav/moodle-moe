<?php

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('localplugins', new admin_category('local_moereports',get_string('pluginname', 'local_moereports')));


$settings = new admin_settingpage('', get_string('add', 'local_moereports')); 

    $ADMIN->add('local_moereports',new admin_externalpage('reports', get_string('reports', 'local_moereports'),
    $CFG->wwwroot.'/local/moereports/view.php'));
    $ADMIN->add('local_moereports',new admin_externalpage('classes reports', get_string('classesreports', 'local_moereports'),
        $CFG->wwwroot.'/local/moereports/classes_report.php'));
    
    //function local_{moereports}_extend_settings_navigation(settings_navigation $nav, context $context)    
 $ADMIN->add('local_moereports', $settings); 


$settings = null;