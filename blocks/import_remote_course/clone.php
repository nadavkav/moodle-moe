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
 *
 * @package    block_import_remote_course
 * @copyright  Sysbind <service@sysbind.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_import_remote_course;
use block_import_remote_course\local\course_template;

require_once(dirname(__FILE__) . '/../../config.php');

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_login();
$context = \context_course::instance($_POST['course']);
require_capability('block/import_remote_course:clon', $context);

$mform = new \block_import_remote_course\form\clone_form();
 if ($fromform = $mform->get_data()) {
 	global $USER, $DB;
 	$cat = $fromform->cat;
 	$originalcourse = $fromform->course;
 	
 	//check if the dest course have record in the course - tamplte table
 	$original_course_tamplate = $DB->get_record('import_remote_course_templat', ['course_id' => $originalcourse]);
 	$original_course_tamplate = course_template::get_record(['course_id' => $originalcourse]);
 	
 	if (! $original_course_tamplate) {
 		throw new \Exception(get_string('coursenotfound','block_import_remote_course'));
 	}
 		
 	//***********************backup******************************
 	$course = $DB->get_record('course', array('id' => $originalcourse));
    $backupsettings = array (
 		'users' => 0,
 		'role_assignments' => 0,
 		'activities' => 1,
 		'blocks' => 1,
 		'filters' => 0,
 		'comments' => 0,
 		'userscompletion' => 0,
 		'logs' => 0,
 		'grade_histories' => 0,
 		'calendarevents' => 0
 		);	
 		$bc = new \backup_controller(\backup::TYPE_1COURSE, $course->id, \backup::FORMAT_MOODLE,
 				\backup::INTERACTIVE_NO, \backup::MODE_GENERAL, $USER->id);
 		
 		foreach ($backupsettings as $name => $value) {
 			$bc->get_plan()->get_setting($name)->set_value($value);
 		}
 		
 		// Set the default filename.
 		$format = $bc->get_format();
 		$type = $bc->get_type();
 		$id = $bc->get_id();
 		
 		$filename = \backup_plan_dbops::get_default_backup_filename($format, $type, $id, false, null);
 		$bc->get_plan()->get_setting('filename')->set_value($filename);
 		 		
 		// Execution.
 		$bc->execute_plan();
 		$results = $bc->get_results();
 		$file = $results['backup_destination'];
 		
 		// Extract to a temp folder.
 		$context = \context_course::instance($course->id);
 		$filepath = md5(time() . '-' . $context->id . '-'. $USER->id . '-'. random_string(20));
 		$fb = get_file_packer('application/vnd.moodle.backup');
 		$extracttopath = $CFG->tempdir . '/backup/' . $filepath . '/';
 		
 		//*****************************restore**************************************
 		$fullname = $course->fullname;
 		$shortname = $course->shortname;
 		$extractedbackup = $fb->extract_to_pathname($file, $extracttopath);

 		list($fullname, $shortname) = \restore_dbops::calculate_course_names(0, $fullname, $shortname);
 		$newcourseid = \restore_dbops::create_new_course($fullname, $shortname, $cat);
 		$rc = new \restore_controller($filepath, $newcourseid, \backup::INTERACTIVE_NO,
 				\backup::MODE_GENERAL, $USER->id, \backup::TARGET_NEW_COURSE);
 		
 		foreach ($backupsettings as $name => $value) {
 			$rc->get_plan()->get_setting($name)->set_value($value);
 		}
 		// Check if the format conversion must happen first.
 		
 		if ($rc->get_status() == \backup::STATUS_REQUIRE_CONV) {
 			$rc->convert();
 		}
 		if ($rc->execute_precheck()) {
 			// Start restore (import).
 			$rc->execute_plan();
 			//echo get_string('courserestored', 'tool_uploadcourse');
 		} else {
 			echo get_string('errorwhilerestoringthecourse', 'tool_uploadcourse');
 		}
 		$rc->destroy();
 		unset($rc); 	
 		
 		//insert the new course to the course - template pivo	
 		$dataobject = new stdClass();
 		$dataobject->course_id = $newcourseid;
 		$dataobject->tamplate_id = $original_course_tamplate->get('tamplate_id');
 		$dataobject->user_id = $USER->id;
 		$coursetemplate = new course_template(0, $dataobject);
 		$coursetemplate->create();
 		
 		$DB->insert_record('import_remote_course_templat', $original_course_tamplate);
 		 		
 		redirect(new \moodle_url('/course/view.php', ['id' => $originalcourse]), get_string('successclone', 'block_import_remote_course'));
 }




