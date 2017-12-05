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

namespace block_import_remote_course;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer.
 *
 * @package block_import_remote_course
 * @copyright 2017 SysBind LTD
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {	
	/**
	 * Event observer.
	 * on course delete remve course from course-tamplate table
	 */
	public static function course_delete(\core\event\base $event) {
		global $DB;
		$localdata = $event->get_data();
		if ( !$tamplate = self::is_tag_course($localdata['courseid'])) {
			return ;
		}
		$subinstance = new \subscriber();
		return  $subinstance->delete_course($localdata['courseid']);
	}
	
	/**
	 * chek if a given course is.
	 *
	 * @param int $courseid -  course id.
	 * @return mixed std table row | false if none found;
	 */
	public static function is_tag_course(int $courseid) {
		global $DB;
		return $DB->get_record('import_remote_course_templat', ['course_id' => $courseid]);
	}
	
    
}
