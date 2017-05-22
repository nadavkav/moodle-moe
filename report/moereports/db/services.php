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
defined('MOODLE_INTERNAL') || die();

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
        'restrictedusers' => 0,
        'enabled' => 1,
    )
);