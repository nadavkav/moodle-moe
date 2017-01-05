<?php

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('localplugins', new admin_category('local_moereports',
    get_string('pluginname', 'local_moereports')));

$settings = new admin_settingpage('', get_string('add', 'local_moereports')); 
/*  if ($ADMIN->fulltree) {  */
    $ADMIN->add('local_moereports',new admin_externalpage('reports', get_string('reports', 'local_moereports'),
    $CFG->wwwroot.'/local/moereports/view.php'));
/*  }  */

 $ADMIN->add('local_moereports', $settings); 


$settings = null;