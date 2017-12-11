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
 * Block import_remote_course
 *
 * Display a list of courses to be imported from a remote Moodle system
 * Using a local/remote_backup_provider plugin (dependency)
 *
 * @package    block_import_remote_course
 * @copyright  SysBind <service@sysbind.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_import_remote_course\local;
use core_availability\info;
use block_import_remote_course\persistent;

defined('MOODLE_INTERNAL') || die();

class notification_helper extends persistent{

	const TABLE = 'import_remote_course_actdata';

	/**
    * Return the definition of the properties of notification helper.
    *
    * @return array
    */
	protected static function define_properties() {
        return array(
            'courseid' => array(
                'type' => PARAM_INT,
                'null' => NULL_NOT_ALLOWED,
            ),
            'linktoremoteact' => array(
                'type' => PARAM_URL,
                'null' => NULL_NOT_ALLOWED,
            ),
            'cm' => array(
                'type' => PARAM_INT,
                'null' => NULL_NOT_ALLOWED,
            ),
            'module' => array(
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
            ),
            'name' => array(
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
            ),
            'type' => array(
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
            ),
            'section' => array(
        			'type' => PARAM_TEXT,
        			'null' => NULL_ALLOWED,
        	),
        );
	}
}