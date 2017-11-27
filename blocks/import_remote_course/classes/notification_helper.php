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
 * @copyright  Nadav Kavalerchik <nadavkav@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_import_remote_course;
use core_availability\info;

defined('MOODLE_INTERNAL') || die();

class notification_helper {
	
	const TABLE = 'import_remote_course_notific';
	
	
	protected $id;
	protected $course_id;
	protected $teacher_id;
	protected $tamplate_id;
	protected $no_of_notification;
	protected $time_last_notification;
	protected $time_last_reset_notifications;
	protected $time_last_reset_act;
	
	/**
	 * @param int $notification_id -  id from import_remote_course_notific table.
	 */
	public function __construct($id) {
		global $DB;
		$dbobj = $DB->get_record(self::TABLE, ['id' => $id]);
		foreach ($dbobj as $key => $value) {
			$this->$key = $value;
		}
	}
	/**
	 * return full template info 
	 *
	 * @return mixed fieldset object - the tamplte info | false if not found
	 */
	public function get_template_info(){
		global $DB;
		$notification_info = $DB->get_record(self::TABLE, ['id' => $this->id]);
		if (!$notification_info) {
			return false;
		}
		return $DB->get_record('import_remote_course_list', ['id' => $this->tamplate_id]);
	}
	
	/**
	 * get number of new activity's 
	 *
	 * @return mixed fieldset object - the tamplte info | false if not found
	 */
	public function get_new_act_number() {
		global $DB;
		$last = $this->time_last_reset_act;
		return (count($DB->get_records_select('import_remote_course_actdata', "time_added < $last")));
	}
	
}