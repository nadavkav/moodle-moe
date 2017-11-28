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
 * This script migrate MDBIU old env to new instanace
 *
 * @copyright 2017 Meir Ifrach
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true); // This prevents reading of existing caches.

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/clilib.php');
cli_writeln('starting courses process...');
global $DB;
$totalnewadd = 0;
$totaskip = 0;
$courses = $DB->get_records('course');
foreach ($courses as $course) {
	cli_writeln("check course $course->fullname ");
	if ($DB->get_field('import_remote_course_templat', 'id', ['course_id' => $course->id])) {
		cli_writeln("already exist. skipping");
		$totaskip++;
		continue;
	}
	$tags = core_tag_tag::get_item_tags_array('core', 'course', $course->id, 1);
	foreach ($tags as $tag) {
		$tag_parts = explode('_', $tag);
		if (count($tag_parts) == 2) {
			cli_writeln("found matching regex, tag: $tag_parts[0]. template: $tag_parts[1]");
			$dbobj = new stdClass();
			$select = $DB->sql_compare_text('course_name') . ' = "' . $DB->sql_compare_text($tag_parts[1]) . '" AND ' . $DB->sql_compare_text('course_tag') . ' = "' . $DB->sql_compare_text($tag_parts[0]) . '"';
			$tamplate_id = $DB->get_field_select('import_remote_course_list', 'id', $select);				
			$dbobj->tamplate_id = $tamplate_id;
			$dbobj->course_id   = $course->id;
			$dbobj->user_id     = 2;
			$dbobj->time_added  = time();
			
			$newid = $DB->insert_record('import_remote_course_templat', $dbobj);
			cli_writeln("new id in course-template table: $newid");
			$totalnewadd++;
		}
	}	
	cli_writeln('+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++');
}
$total = count($courses);
cli_writeln("total courses checks : $total");
cli_writeln("total new course-template added: $totalnewadd");
cli_writeln("total skipped courses: $totaskip");

// Close connection
exit();
