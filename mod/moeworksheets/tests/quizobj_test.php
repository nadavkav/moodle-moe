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
 * Unit tests for the moeworksheets class.
 *
 * @package   mod_moeworksheets
 * @copyright 2008 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/moeworksheets/locallib.php');


/**
 * Unit tests for the moeworksheets class
 *
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_moeworksheets_class_testcase extends basic_testcase {
    public function test_cannot_review_message() {
        $moeworksheets = new stdClass();
        $moeworksheets->reviewattempt = 0x10010;
        $moeworksheets->timeclose = 0;
        $moeworksheets->attempts = 0;

        $cm = new stdClass();
        $cm->id = 123;

        $moeworksheetsobj = new moeworksheets($moeworksheets, $cm, new stdClass(), false);

        $this->assertEquals('',
            $moeworksheetsobj->cannot_review_message(mod_moeworksheets_display_options::DURING));
        $this->assertEquals('',
            $moeworksheetsobj->cannot_review_message(mod_moeworksheets_display_options::IMMEDIATELY_AFTER));
        $this->assertEquals(get_string('noreview', 'moeworksheets'),
            $moeworksheetsobj->cannot_review_message(mod_moeworksheets_display_options::LATER_WHILE_OPEN));
        $this->assertEquals(get_string('noreview', 'moeworksheets'),
            $moeworksheetsobj->cannot_review_message(mod_moeworksheets_display_options::AFTER_CLOSE));

        $closetime = time() + 10000;
        $moeworksheets->timeclose = $closetime;
        $moeworksheetsobj = new moeworksheets($moeworksheets, $cm, new stdClass(), false);

        $this->assertEquals(get_string('noreviewuntil', 'moeworksheets', userdate($closetime)),
            $moeworksheetsobj->cannot_review_message(mod_moeworksheets_display_options::LATER_WHILE_OPEN));
    }
}
