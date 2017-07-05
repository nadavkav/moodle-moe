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
 * Unit tests for (some of) mod/moeworksheets/locallib.php.
 *
 * @package    mod_moeworksheets
 * @category   test
 * @copyright  2008 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/moeworksheets/locallib.php');


/**
 * Unit tests for (some of) mod/moeworksheets/locallib.php.
 *
 * @copyright  2008 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_moeworksheets_locallib_testcase extends advanced_testcase {

    public function test_moeworksheets_rescale_grade() {
        $moeworksheets = new stdClass();
        $moeworksheets->decimalpoints = 2;
        $moeworksheets->questiondecimalpoints = 3;
        $moeworksheets->grade = 10;
        $moeworksheets->sumgrades = 10;
        $this->assertEquals(moeworksheets_rescale_grade(0.12345678, $moeworksheets, false), 0.12345678);
        $this->assertEquals(moeworksheets_rescale_grade(0.12345678, $moeworksheets, true), format_float(0.12, 2));
        $this->assertEquals(moeworksheets_rescale_grade(0.12345678, $moeworksheets, 'question'),
            format_float(0.123, 3));
        $moeworksheets->sumgrades = 5;
        $this->assertEquals(moeworksheets_rescale_grade(0.12345678, $moeworksheets, false), 0.24691356);
        $this->assertEquals(moeworksheets_rescale_grade(0.12345678, $moeworksheets, true), format_float(0.25, 2));
        $this->assertEquals(moeworksheets_rescale_grade(0.12345678, $moeworksheets, 'question'),
            format_float(0.247, 3));
    }

    public function test_moeworksheets_attempt_state_in_progress() {
        $attempt = new stdClass();
        $attempt->state = moeworksheets_attempt::IN_PROGRESS;
        $attempt->timefinish = 0;

        $moeworksheets = new stdClass();
        $moeworksheets->timeclose = 0;

        $this->assertEquals(mod_moeworksheets_display_options::DURING, moeworksheets_attempt_state($moeworksheets, $attempt));
    }

    public function test_moeworksheets_attempt_state_recently_submitted() {
        $attempt = new stdClass();
        $attempt->state = moeworksheets_attempt::FINISHED;
        $attempt->timefinish = time() - 10;

        $moeworksheets = new stdClass();
        $moeworksheets->timeclose = 0;

        $this->assertEquals(mod_moeworksheets_display_options::IMMEDIATELY_AFTER, moeworksheets_attempt_state($moeworksheets, $attempt));
    }

    public function test_moeworksheets_attempt_state_sumitted_moeworksheets_never_closes() {
        $attempt = new stdClass();
        $attempt->state = moeworksheets_attempt::FINISHED;
        $attempt->timefinish = time() - 7200;

        $moeworksheets = new stdClass();
        $moeworksheets->timeclose = 0;

        $this->assertEquals(mod_moeworksheets_display_options::LATER_WHILE_OPEN, moeworksheets_attempt_state($moeworksheets, $attempt));
    }

    public function test_moeworksheets_attempt_state_sumitted_moeworksheets_closes_later() {
        $attempt = new stdClass();
        $attempt->state = moeworksheets_attempt::FINISHED;
        $attempt->timefinish = time() - 7200;

        $moeworksheets = new stdClass();
        $moeworksheets->timeclose = time() + 3600;

        $this->assertEquals(mod_moeworksheets_display_options::LATER_WHILE_OPEN, moeworksheets_attempt_state($moeworksheets, $attempt));
    }

    public function test_moeworksheets_attempt_state_sumitted_moeworksheets_closed() {
        $attempt = new stdClass();
        $attempt->state = moeworksheets_attempt::FINISHED;
        $attempt->timefinish = time() - 7200;

        $moeworksheets = new stdClass();
        $moeworksheets->timeclose = time() - 3600;

        $this->assertEquals(mod_moeworksheets_display_options::AFTER_CLOSE, moeworksheets_attempt_state($moeworksheets, $attempt));
    }

    public function test_moeworksheets_attempt_state_never_sumitted_moeworksheets_never_closes() {
        $attempt = new stdClass();
        $attempt->state = moeworksheets_attempt::ABANDONED;
        $attempt->timefinish = 1000; // A very long time ago!

        $moeworksheets = new stdClass();
        $moeworksheets->timeclose = 0;

        $this->assertEquals(mod_moeworksheets_display_options::LATER_WHILE_OPEN, moeworksheets_attempt_state($moeworksheets, $attempt));
    }

    public function test_moeworksheets_attempt_state_never_sumitted_moeworksheets_closes_later() {
        $attempt = new stdClass();
        $attempt->state = moeworksheets_attempt::ABANDONED;
        $attempt->timefinish = time() - 7200;

        $moeworksheets = new stdClass();
        $moeworksheets->timeclose = time() + 3600;

        $this->assertEquals(mod_moeworksheets_display_options::LATER_WHILE_OPEN, moeworksheets_attempt_state($moeworksheets, $attempt));
    }

    public function test_moeworksheets_attempt_state_never_sumitted_moeworksheets_closed() {
        $attempt = new stdClass();
        $attempt->state = moeworksheets_attempt::ABANDONED;
        $attempt->timefinish = time() - 7200;

        $moeworksheets = new stdClass();
        $moeworksheets->timeclose = time() - 3600;

        $this->assertEquals(mod_moeworksheets_display_options::AFTER_CLOSE, moeworksheets_attempt_state($moeworksheets, $attempt));
    }

    public function test_moeworksheets_question_tostring() {
        $question = new stdClass();
        $question->qtype = 'multichoice';
        $question->name = 'The question name';
        $question->questiontext = '<p>What sort of <b>inequality</b> is x &lt; y<img alt="?" src="..."></p>';
        $question->questiontextformat = FORMAT_HTML;

        $summary = moeworksheets_question_tostring($question);
        $this->assertEquals('<span class="questionname">The question name</span> ' .
                '<span class="questiontext">What sort of INEQUALITY is x &lt; y[?]' . "\n" . '</span>', $summary);
    }

    /**
     * Test moeworksheets_view
     * @return void
     */
    public function test_moeworksheets_view() {
        global $CFG;

        $CFG->enablecompletion = 1;
        $this->resetAfterTest();

        $this->setAdminUser();
        // Setup test data.
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $moeworksheets = $this->getDataGenerator()->create_module('moeworksheets', array('course' => $course->id),
                                                            array('completion' => 2, 'completionview' => 1));
        $context = context_module::instance($moeworksheets->cmid);
        $cm = get_coursemodule_from_instance('moeworksheets', $moeworksheets->id);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();

        moeworksheets_view($moeworksheets, $course, $cm, $context);

        $events = $sink->get_events();
        // 2 additional events thanks to completion.
        $this->assertCount(3, $events);
        $event = array_shift($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_moeworksheets\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/moeworksheets/view.php', array('id' => $cm->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());
        // Check completion status.
        $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);
    }
}
