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
    'notes_search' => array(
        'classname'   => 'local_notes_external',
        'methodname'  => 'search',
        'classpath'   => 'local/notes/externallib.php',
        'description' => 'Get all annotaioin for note',
        'type'        => 'read',
        'ajax'        => true
    ),
    'notes_create' => array(
        'classname'   => 'local_notes_external',
        'methodname'  => 'create',
        'classpath'   => 'local/notes/externallib.php',
        'description' => 'Save anotaion to the DB',
        'type'        => 'write',
        'ajax'        => true
    ),
    'notes_create_ver' => array(
        'classname'   => 'local_notes_external',
        'methodname'  => 'create_version',
        'classpath'   => 'local/notes/externallib.php',
        'description' => 'create new note version when add new annotation',
        'type'        => 'write',
        'ajax'        => true
    ),
    'notes_delete' => array(
        'classname'   => 'local_notes_external',
        'methodname'  => 'delete',
        'classpath'   => 'local/notes/externallib.php',
        'description' => 'Delete single annotation',
        'type'        => 'write',
        'ajax'        => 'true',
    ),
    'notes_update' => array(
        'classname'   => 'local_notes_external',
        'methodname'  => 'update',
        'classpath'   => 'local/notes/externallib.php',
        'description' => 'Update annotation',
        'type'        => 'write',
        'ajax'        => 'true',
    ),
    'notes_resolved' => array(
        'classname'   => 'local_notes_external',
        'methodname'  => 'resolved',
        'classpath'   => 'local/notes/externallib.php',
        'description' => 'Resolved annotation',
        'type'        => 'write',
        'ajax'        => 'true',
    ),
    'notes_reopen' => array(
        'classname'   => 'local_notes_external',
        'methodname'  => 'reopen',
        'classpath'   => 'local/notes/externallib.php',
        'description' => 'Reopen resolved annotation',
        'type'        => 'write',
        'ajax'        => 'true',
    ),
    'insert_notes' => array(
        'classname'   => 'local_notes_external',
        'methodname'  => 'insert_notes',
        'classpath'   => 'local/notes/externallib.php',
        'description' => 'insert note new version',
        'type'        => 'write',
        'ajax'        => 'true',
    ),
);

$services = array(
    'notes annotaions' => array(
        'functions' => array(
            'notes_search',
            'notes_create',
            'notes_create_ver',
            'notes_delete',
            'notes_update',
            'notes_resolved',
            'notes_reopen',
            'insert_notes'
        ),
        'restrictedusers' => 0,
        'enabled'=>1,
        'requiredcapability' => '',
    )
);