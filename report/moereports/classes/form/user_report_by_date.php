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
 * @package    report_moereports
 * @copyright  2017 Sysbind
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_moereports\form;
require_once $CFG->libdir. '/formslib.php';
class user_report_by_date extends \moodleform {

    /**
     * (non-PHPdoc)
     *
     * @see moodleform::definition()
     *
     */
    protected function definition() {
    	global $DB;
        $mform =& $this->_form;
		
        $mform->addElement('date_time_selector', 'timestart', get_string('from'));
        $this->add_action_buttons(false, get_string('filter', 'report_moereports'));
        $mform->disable_form_change_checker();
        

    }
}

