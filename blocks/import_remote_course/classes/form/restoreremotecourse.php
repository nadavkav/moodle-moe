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
 * @package    block_import_remote_course
 * @copyright  2017 SysBind
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_import_remote_course\form;
require_once $CFG->libdir. '/formslib.php';

defined('MOODLE_INTERNAL') || die();

class restoreremotecourse extends \moodleform {
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $mform->
        $mform->addElement('text', 'restoreremotecourse', get_string('restoreremotecourse'));
        $mform->setType('restoreremotecourse', PARAM_NOTAGS);
        $mform->addRule('restoreremotecourse', '', 'minlength', 4);
        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_INT);
        $this->add_action_buttons(false, get_string('restoreremotecourse'));
    }
}
