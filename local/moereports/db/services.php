<?php


$functions = array(
    'local_moereports_saveschools' => array(
        'classname' => 'local_moereports_external',
        'methodname' => 'saveschools',
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
        ),
        'requiredcapability' => '',
        'restrictedusers' =>0,
        'enabled'=>1,
    )
);