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
 * List of wikis on course. (Not used in OU. I ripped it entirely off
 * from another module, deleting module-specific bits.)
 *
 * @copyright &copy; 2007 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moewiki
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/moewiki/lib.php');

// Support for OU shared activities system, if installed
$grabindex = $CFG->dirroot.'/course/format/sharedactv/grabindex.php';
if (file_exists($grabindex)) {
    require_once($grabindex);
}

$id = required_param('id', PARAM_INT);   // course
$url = new moodle_url('/mod/moewiki/index.php', array('id' => $id));
$PAGE->set_url($url);

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}

require_course_login($course->id, true);
$PAGE->set_pagelayout('incourse');
$context = context_course::instance($course->id);

// Get all required strings.
$strname = get_string('name');
$strsectionname = get_string('sectionname', 'format_' . $course->format);
$strdescription = get_string('description');
$strsectionname = get_string('sectionname', 'format_' . $course->format);
$strmoewikis = get_string('modulenameplural', 'moewiki');

// Print the header.
$PAGE->navbar->add($strmoewikis, "index.php?id=$course->id");
$PAGE->set_title($strmoewikis);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

if (!($moewikis = get_all_instances_in_course('moewiki', $course))) {
    notice("There are no wikis", "../../course/view.php?id=$course->id");
    die;
}

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_fast_modinfo($course)->get_section_info_all();
}

$table = new html_table();
if ($usesections) {
    $table->head = array($strsectionname, $strname, $strdescription);
} else {
    $table->head = array($strname, $strdescription);
}

foreach ($moewikis as $moewiki) {
    // Calculate the href.
    $linkcss = null;
    if (!$moewiki->visible) {
        $linkcss = array('class' => 'dimmed');
    }
    $link = html_writer::link(new moodle_url('/mod/moewiki/view.php', array('id' => $moewiki->coursemodule)), $moewiki->name, $linkcss);

    // Properly format the intro.
    $contextmodule = context_module::instance($moewiki->coursemodule);
    $moewiki->intro = file_rewrite_pluginfile_urls($moewiki->intro, 'pluginfile.php', $contextmodule->id,
            'mod_moewiki', 'intro', null);

    if ($usesections) {
        $table->data[] = array(get_section_name($course, $sections[$moewiki->section]), $link, $moewiki->intro);
    } else {
        $table->data[] = array($link, $moewiki->intro);
    }
}

echo html_writer::table($table);

// Log usage view.
$params = array(
    'context' => $context,
);

$event = \mod_moewiki\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

// Finish the page.
echo $OUTPUT->footer();
