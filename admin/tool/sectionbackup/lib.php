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

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the section backup
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function tool_sectionbackup_extend_navigation_course($navigation, $course, $context) {
    global $DB;
    if (has_capability('moodle/backup:backupsection', $context)) {
        $name = get_string('header', 'tool_sectionbackup');
        $head = $navigation->add($name, null, navigation_node::TYPE_COURSE, null, null, new pix_icon('i/report', ''));  
        $sections = get_fast_modinfo($course->id)->get_sections();
        foreach ($sections as $sectionkey => $sectionvalue) {
            $name = get_section_name($course->id, $sectionkey);
            $sectionid = $DB->get_field('course_sections', 'id', ['course' => $course->id, 'section' => $sectionkey]);
            $url = new moodle_url('/backup/backup.php', ['id' => $course->id, 'section' => $sectionid]);
            $head->add($name, $url, navigation_node::TYPE_RESOURCE, null, null, null);
            
        }
        
    }
}