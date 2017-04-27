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
 * Quiz results block installation.
 *
 * @package    block_quiz_results
 * @copyright  2015 Dan Poltawski <dan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('config.php');
require_once($CFG->libdir.'/blocklib.php');

$CFG->defaultblocks_moetopcoll=  'participants,activity_modules,search_forums,course_list:news_items,calendar_upcoming,recent_activity,links_for_moetopcoll';

// function links_for_moetopcoll_install() {
//     $courses = get_courses();//can be feed categoryid to just effect one category
//     foreach($courses as $course) {
//         $context = context_course::instance($course->id);
//         blocks_delete_all_for_context($context->id);
//         blocks_add_default_course_blocks($course);
//     }
// }

