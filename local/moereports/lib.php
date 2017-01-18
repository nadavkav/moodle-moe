<?php


function local_moereports_extend_navigation($moereportsnode){
    global $CFG, $PAGE, $USER;
  
    $previewnode = $PAGE->navigation->add(get_string('pluginname', 'local_moereports'), navigation_node::TYPE_CONTAINER);
    $thingnode = $previewnode->add( get_string('reports', 'local_moereports'), new moodle_url( $CFG->wwwroot.'/local/moereports/view.php'));
    $thingnode = $previewnode->add(get_string('add', 'local_moereports'));
}

