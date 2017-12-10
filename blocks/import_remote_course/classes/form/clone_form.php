<?php
namespace block_import_remote_course\form;
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
 * @package    block_import_remote_course
 * @copyright  2017 Sysbind
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once $CFG->libdir. '/formslib.php';
require_once $CFG->dirroot. '/user/editlib.php';
class clone_form extends \moodleform {

    /**
     * (non-PHPdoc)
     *
     * @see moodleform::definition()
     *
     */
    protected function definition() {
    	global $DB;
        $mform =& $this->_form;
      
        
        $cats = $DB->get_records('course_categories');
        $attributes = array();
        $attributes[] = get_string('selectoption', 'block_import_remote_course');
        foreach ($cats as $catid => $catval) {
        	$paths = explode('/', $catval->path);
        	foreach ($paths as $pathkey => $patval) {
        		$paths[$pathkey] = $DB->get_field('course_categories', 'name', ['id' => $patval]);
        	}
        	$attributes[$catid] = ltrim(implode('/', $paths), '/');       	
        }
        
        $mform->addElement('select', 'cat', get_string('cat', 'block_import_remote_course'), $attributes);
        $mform->addElement('hidden', 'course', '');
        $mform->setType('cat', PARAM_INT);
        $mform->setType('course', PARAM_INT);
        
        $this->add_action_buttons(false, get_string('clone', 'block_import_remote_course'));
    }
}

