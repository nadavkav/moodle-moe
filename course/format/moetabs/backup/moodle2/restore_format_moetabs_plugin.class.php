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
 * moetabs Information
 *
 * @package    course/format
 * @subpackage Moetabs
 * @version    See the value of '$plugin->version' in below.
 * @copyright  &copy; 2012 G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/moetabs/lib.php');

/**
 * Restore plugin class that provides the necessary information
 * needed to restore one moetabs format course.
 */
class restore_format_moetabs_plugin extends restore_local_plugin {

    /**
     * Returns the paths to be handled by the plugin at course level.
     */
    protected function define_course_plugin_structure() {

        $paths = array();

        // Add own format stuff.
        $elename = 'moetabs'; // This defines the postfix of 'process_*' below.
        /*
         * This is defines the nested tag within 'plugin_format_moetabs_course' to allow '/course/plugin_format_moetabs_course' in
         * the path therefore as a path structure representing the levels in section.xml in the backup file.
         */
        $elepath = $this->get_pathfor('/');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.
    }

    /**
     * Process the 'plugin_format_moetabs_course' element within the 'course' element in the 'course.xml' file in the '/course'
     * folder of the zipped backup 'mbz' file.
     */
    public function process_moetabs($data) {
        global $DB;

        $data = (object) $data;

        /* We only process this information if the course we are restoring to
          has 'moetabs' format (target format can change depending of restore options). */
        $format = $DB->get_field('course', 'format', array('id' => $this->task->get_courseid()));
        if ($format != 'moetabs') {
            return;
        }

        $data->courseid = $this->task->get_courseid();


    }

    protected function after_execute_structure() {
        $this->add_related_files('format_moetabs', 'headingimage', null);
    }

    /**
     * Returns the paths to be handled by the plugin at section level
     */
    protected function define_section_plugin_structure() {

        $paths = array();



        return $paths; // And we return the interesting paths.
    }

    /**
     * Process the 'plugin_format_moetabs_section' element within the 'section' element in the 'section.xml' file in the
     * '/sections/section_sectionid' folder of the zipped backup 'mbz' file.
     * Discovered that the files are contained in the course repository with the new section number, so we just need to alter to
     * the new value if any. * This was undertaken by performing a restore and using the url
     * 'http://localhost/moodle23/pluginfile.php/94/course/section/162/mc_fs.png' where I had an image called 'mc_fs.png' in
     * section 1 which was id 129 but now 162 as the debug code told me.  '94' is just the context id.  The url was originally
     * created in '_make_block_icon_topics' of lib.php of the format.
     * Still need courseid in the 'format_moetabs_icon' table as it is used in discovering what records to remove when deleting a
     * course, see lib.php 'format_moetabs_delete_course'.
     */
    public function process_motabssection($data) {
        global $DB;

        $data = (object) $data;

        /* We only process this information if the course we are restoring to
           has 'moetabs' format (target format can change depending of restore options). */
        $format = $DB->get_field('course', 'format', array('id' => $this->task->get_courseid()));
        if ($format != 'moetabs') {
            return;
        }

        $data->courseid = $this->task->get_courseid();
        $data->sectionid = $this->task->get_sectionid();


        // No need to annotate anything here.
    }

}
