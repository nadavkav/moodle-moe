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
/**
 * @package    block_import_remote_course
 * @copyright  2015 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$functions = array(

    'block_import_remote_course_update' => array(
        'classname' => 'block_import_remote_course_external',
        'methodname' => 'update',
        'classpath' => 'blocks/import_remote_course/externallib.php',
        'description' => 'get updates from the server.',
        'type' => 'write',
    ),
    'block_import_remote_course_activity' => array(
        'classname' => 'block_import_remote_course_external',
        'methodname' => 'import_activity',
        'classpath' => 'blocks/import_remote_course/externallib.php',
        'description' => 'import_activity',
        'type' => 'write',
        'ajax' => true
    ),
    'block_import_remote_course_delete_act' => array(
            'classname' => 'block_import_remote_course_external',
            'methodname' => 'delete_act',
            'classpath' => 'blocks/import_remote_course/externallib.php',
            'description' => 'delete activity from course',
            'type' => 'write',
            'ajax' => true
    ),
);
