<?php


$functions = array(
    'report_moereports_saveschools' => array(
        'classname' => 'report_moereports_external',
        'methodname' => 'saveschools',
        'classpath' => 'report/moereports/externallib.php',
        'description' => 'Saves',
        'type' => 'read',
        'ajax' => true,
    ),
    'report_moereports_classes' => array(
        'classname' => 'report_moereports_external',
        'methodname' => 'saveclasses',
        'classpath' => 'report/moereports/externallib.php',
        'description' => 'Saves',
        'type' => 'read',
        'ajax' => true,
    )
);

$services = array(
    'moereportservice' => array(
        'functions' => array(
            'report_moereports_saveschools',
            'report_moereports_classes',
        ),
        'requiredcapability' => '',
        'restrictedusers' =>0,
        'enabled'=>1,
    )
);