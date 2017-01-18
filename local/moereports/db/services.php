<?php


$functions = array(
    'local_moereports_saveschools' => array(
        'classname' => 'local_moereports_external',
        'methodname' => 'saveschools',
        'classpath' => 'local/moereports/externallib.php',
        'description' => 'Saves',
        'type' => 'read',
        'ajax' => true,
    ),
    'local_moereports_classes' => array(
        'classname' => 'local_moereports_external',
        'methodname' => 'saveclasses',
        'classpath' => 'local/moereports/externallib.php',
        'description' => 'Saves',
        'type' => 'read',
        'ajax' => true,
    )
);

$services = array(
    'moereportservice' => array(
        'functions' => array(
            'local_moereports_saveschools',
            'local_moereports_classes',
        ),
        'requiredcapability' => '',
        'restrictedusers' =>0,
        'enabled'=>1,
    )
);