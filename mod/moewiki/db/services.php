<?php

$functions = array(
    'moe_wiki_search' => array(
        'classname'   => 'mod_moewiki_external',
        'methodname'  => 'search',
        'classpath'   => 'mod/moewiki/externallib.php',
        'description' => 'Get all annotaioin for wiki page',
        'type'        => 'read',
        'ajax'        => true
    ), 
    'moe_wiki_create' => array(
        'classname'   => 'mod_moewiki_external',
        'methodname'  => 'create',
        'classpath'   => 'mod/moewiki/externallib.php',
        'description' => 'Save anotaion to the DB',
        'type'        => 'write',
        'ajax'        => true
    )
);

$services = array(
    'MOE_wiki annotaions' => array(
        'functions' => array('moe_wiki_search','moe_wiki_create'),
        'restrictedusers' => 0,
        'enabled'=>1,
        'requiredcapability' => '',
    )
);