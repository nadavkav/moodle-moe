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

defined('MOODLE_INTERNAL') || die();

class block_import_remote_course extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_import_remote_course');
    }

    function get_content() {
        global $COURSE, $CFG, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance) || !has_capability('block/import_remote_course:view', $this->page->context)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        if ($this->page->course->id == SITEID) {
            $this->context->text = "Only available in a course";
            return $this->content;
        }

        // user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);

        if (empty($currentcontext)) {
            return $this->content;
        }

        // Get course list from system settings.
        // todo: use system-level block settings
//        if (!empty($this->config->text)) {
//            $courselist = explode(',',$this->config->text);
//        }
        $remotecourselist = array();
        $tags = core_tag_tag::get_item_tags_array('core', 'course', $COURSE->id);
        foreach ($tags as $tag) {
            $select = $DB->sql_compare_text('course_tag') . " = '" . $tag ."'";
            $remotecourselist = array_merge($remotecourselist,$DB->get_records_select('import_remote_course_list', $select));
        }
        if (empty($remotecourselist)) {
            $this->content->text = get_string('noavailablecourses', 'block_import_remote_course');
            return $this->content;
        }

        $form = '<form id="restoreremotecourse" action="'.
            $CFG->wwwroot.'/blocks/import_remote_course/import_remote_course.php" type="get">';
        $form .= get_string('choosecourse').' <select id="choosecourse" name="remotecourseid">';
        foreach ($remotecourselist as $course) {
            $courseid   = $course->course_id;
            $coursename = $course->course_name;
            $form .= "<option value='$courseid'>$coursename</option>";
        }
        $form .= '</select>';
        $form .= '<input type="hidden" name="destcourseid" value="'.$COURSE->id.'">';
        $form .= '<input type="hidden" name="sessionid" value="'.session_id().'">';
        //$form .= '<input type="submit" value="'.get_string("restore").'" onsubmit="Y(\'div.inprogress\').removeClass(\'hide\')">';
        if(count(get_fast_modinfo($COURSE->id)->cms) <= 1){
            $form .= '<input id="restorebutton" type="button" value="'.get_string("restore", 'block_import_remote_course').'" ' .
                'onclick="Y.one(\'div.inprogress\').removeClass(\'hide\');document.forms[\'restoreremotecourse\'].submit();">';
        } else {
            $form .= '<input id="restorebutton" type="button" value="'.get_string("restore", 'block_import_remote_course').'" ' .
                'onclick="Y.one(\'form#restoreremotecourse\').append(\''."<br>" . get_string('courseisnotempty', 'block_import_remote_course') . '\');Y.one(\'input#restorebutton\').remove();">';
        }
        $form .= '</form>';
        $form .= '<a id="importsite" target="_blank" href="' . get_config('block_import_remote_course', 'testenv') . '" class="btn">' . get_string('trytemplates', 'block_import_remote_course') . '</a>';

        $form .= html_writer::start_div('inprogress hide');
            $form .= html_writer::start_div('notice');
            $form .= get_string('restoreinprogress', 'block_import_remote_course');
            $form .= html_writer::end_div();
            $form .= html_writer::start_div('notice');
            $form .= html_writer::img($CFG->wwwroot.'/blocks/import_remote_course/pix/download.gif',
                get_string('restoreinprogress', 'block_import_remote_course'));
            $form .= html_writer::end_div();
        $form .= html_writer::end_div();

        // If we have more then one (probably the "news forum") module in the course,
        // Display a warrening, and prevent restore.

        if(!($this->content instanceof  stdClass)) {
            $this->content = new stdClass();
        }
        if(isset($this->content->text)){
            $this->content->text .= $form;
        } else {
            $this->content->text = $form;
        }

        return $this->content;
    }

    // Block is available only on course pages.
    public function applicable_formats() {
        return array('all' => false,
                     'course-view' => true,
                     'category-view' => true);
    }

    // Block can appear only once, in a course.
    public function instance_allow_multiple() {
          return false;
    }

    // Block has course-level config.
    // todo: migrate to site level.
    function has_config() {
        return true;
    }
}
