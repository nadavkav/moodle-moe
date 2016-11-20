<?php
// This file is part of Moodle - http://moodle.org/
//
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
    ),
    'moe_wiki_create_ver' => array(
        'classname'   => 'mod_moewiki_external',
        'methodname'  => 'create_version',
        'classpath'   => 'mod/moewiki/externallib.php',
        'description' => 'create new wiki version when add new annotation',
        'type'        => 'write',
        'ajax'        => true
    ),
    'moe_wiki_delete' => array(
        'classname'   => 'mod_moewiki_external',
        'methodname'  => 'delete',
        'classpath'   => 'mod/moewiki/externallib.php',
        'description' => 'Delete single annotation',
        'type'        => 'write',
        'ajax'        => 'true',
    ),
    'moe_wiki_update' => array(
        'classname'   => 'mod_moewiki_external',
        'methodname'  => 'update',
        'classpath'   => 'mod/moewiki/externallib.php',
        'description' => 'Update annotation',
        'type'        => 'write',
        'ajax'        => 'true',
    ),
    'moe_wiki_resolved' => array(
        'classname'   => 'mod_moewiki_external',
        'methodname'  => 'resolved',
        'classpath'   => 'mod/moewiki/externallib.php',
        'description' => 'Resolved annotation',
        'type'        => 'write',
        'ajax'        => 'true',
    ),
    'moe_wiki_reopen' => array(
        'classname'   => 'mod_moewiki_external',
        'methodname'  => 'reopen',
        'classpath'   => 'mod/moewiki/externallib.php',
        'description' => 'Reopen resolved annotation',
        'type'        => 'write',
        'ajax'        => 'true',
    ),
);

$services = array(
    'MOE_wiki annotaions' => array(
        'functions' => array(
            'moe_wiki_search',
            'moe_wiki_create',
            'moe_wiki_create_ver',
            'moe_wiki_delete',
            'moe_wiki_update',
            'moe_wiki_resolved',
            'moe_wiki_reopen',
        ),
        'restrictedusers' => 0,
        'enabled'=>1,
        'requiredcapability' => '',
    )
);