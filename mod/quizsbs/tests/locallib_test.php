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
 * Unit tests for (some of) mod/quizsbs/locallib.php.
 *
 * @package    mod_quizsbs
 * @category   test
 * @copyright  2008 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quizsbs/locallib.php');


/**
 * Unit tests for (some of) mod/quizsbs/locallib.php.
 *
 * @copyright  2008 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_quizsbs_locallib_testcase extends advanced_testcase {

    public function test_quizsbs_rescale_grade() {
        $quizsbs = new stdClass();
        $quizsbs->decimalpoints = 2;
        $quizsbs->questiondecimalpoints = 3;
        $quizsbs->grade = 10;
        $quizsbs->sumgrades = 10;
        $this->assertEquals(quizsbs_rescale_grade(0.12345678, $quizsbs, false), 0.12345678);
        $this->assertEquals(quizsbs_rescale_grade(0.12345678, $quizsbs, true), format_float(0.12, 2));
        $this->assertEquals(quizsbs_rescale_grade(0.12345678, $quizsbs, 'question'),
            format_float(0.123, 3));
        $quizsbs->sumgrades = 5;
        $this->assertEquals(quizsbs_rescale_grade(0.12345678, $quizsbs, false), 0.24691356);
        $this->assertEquals(quizsbs_rescale_grade(0.12345678, $quizsbs, true), format_float(0.25, 2));
        $this->assertEquals(quizsbs_rescale_grade(0.12345678, $quizsbs, 'question'),
            format_float(0.247, 3));
    }

    public function test_quizsbs_attempt_state_in_progress() {
        $attempt = new stdClass();
        $attempt->state = quizsbs_attempt::IN_PROGRESS;
        $attempt->timefinish = 0;

        $quizsbs = new stdClass();
        $quizsbs->timeclose = 0;

        $this->assertEquals(mod_quizsbs_display_options::DURING, quizsbs_attempt_state($quizsbs, $attempt));
    }

    public function test_quizsbs_attempt_state_recently_submitted() {
        $attempt = new stdClass();
        $attempt->state = quizsbs_attempt::FINISHED;
        $attempt->timefinish = time() - 10;

        $quizsbs = new stdClass();
        $quizsbs->timeclose = 0;

        $this->assertEquals(mod_quizsbs_display_options::IMMEDIATELY_AFTER, quizsbs_attempt_state($quizsbs, $attempt));
    }

    public function test_quizsbs_attempt_state_sumitted_quizsbs_never_closes() {
        $attempt = new stdClass();
        $attempt->state = quizsbs_attempt::FINISHED;
        $attempt->timefinish = time() - 7200;

        $quizsbs = new stdClass();
        $quizsbs->timeclose = 0;

        $this->assertEquals(mod_quizsbs_display_options::LATER_WHILE_OPEN, quizsbs_attempt_state($quizsbs, $attempt));
    }

    public function test_quizsbs_attempt_state_sumitted_quizsbs_closes_later() {
        $attempt = new stdClass();
        $attempt->state = quizsbs_attempt::FINISHED;
        $attempt->timefinish = time() - 7200;

        $quizsbs = new stdClass();
        $quizsbs->timeclose = time() + 3600;

        $this->assertEquals(mod_quizsbs_display_options::LATER_WHILE_OPEN, quizsbs_attempt_state($quizsbs, $attempt));
    }

    public function test_quizsbs_attempt_state_sumitted_quizsbs_closed() {
        $attempt = new stdClass();
        $attempt->state = quizsbs_attempt::FINISHED;
        $attempt->timefinish = time() - 7200;

        $quizsbs = new stdClass();
        $quizsbs->timeclose = time() - 3600;

        $this->assertEquals(mod_quizsbs_display_options::AFTER_CLOSE, quizsbs_attempt_state($quizsbs, $attempt));
    }

    public function test_quizsbs_attempt_state_never_sumitted_quizsbs_never_closes() {
        $attempt = new stdClass();
        $attempt->state = quizsbs_attempt::ABANDONED;
        $attempt->timefinish = 1000; // A very long time ago!

        $quizsbs = new stdClass();
        $quizsbs->timeclose = 0;

        $this->assertEquals(mod_quizsbs_display_options::LATER_WHILE_OPEN, quizsbs_attempt_state($quizsbs, $attempt));
    }

    public function test_quizsbs_attempt_state_never_sumitted_quizsbs_closes_later() {
        $attempt = new stdClass();
        $attempt->state = quizsbs_attempt::ABANDONED;
        $attempt->timefinish = time() - 7200;

        $quizsbs = new stdClass();
        $quizsbs->timeclose = time() + 3600;

        $this->assertEquals(mod_quizsbs_display_options::LATER_WHILE_OPEN, quizsbs_attempt_state($quizsbs, $attempt));
    }

    public function test_quizsbs_attempt_state_never_sumitted_quizsbs_closed() {
        $attempt = new stdClass();
        $attempt->state = quizsbs_attempt::ABANDONED;
        $attempt->timefinish = time() - 7200;

        $quizsbs = new stdClass();
        $quizsbs->timeclose = time() - 3600;

        $this->assertEquals(mod_quizsbs_display_options::AFTER_CLOSE, quizsbs_attempt_state($quizsbs, $attempt));
    }

    public function test_quizsbs_question_tostring() {
        $question = new stdClass();
        $question->qtype = 'multichoice';
        $question->name = 'The question name';
        $question->questiontext = '<p>What sort of <b>inequality</b> is x &lt; y<img alt="?" src="..."></p>';
        $question->questiontextformat = FORMAT_HTML;

        $summary = quizsbs_question_tostring($question);
        $this->assertEquals('<span class="questionname">The question name</span> ' .
                '<span class="questiontext">What sort of INEQUALITY is x &lt; y[?]' . "\n" . '</span>', $summary);
    }

    /**
     * Test quizsbs_view
     * @return void
     */
    public function test_quizsbs_view() {
        global $CFG;

        $CFG->enablecompletion = 1;
        $this->resetAfterTest();

        $this->setAdminUser();
        // Setup test data.
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $quizsbs = $this->getDataGenerator()->create_module('quizsbs', array('course' => $course->id),
                                                            array('completion' => 2, 'completionview' => 1));
        $context = context_module::instance($quizsbs->cmid);
        $cm = get_coursemodule_from_instance('quizsbs', $quizsbs->id);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();

        quizsbs_view($quizsbs, $course, $cm, $context);

        $events = $sink->get_events();
        // 2 additional events thanks to completion.
        $this->assertCount(3, $events);
        $event = array_shift($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_quizsbs\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/quizsbs/view.php', array('id' => $cm->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());
        // Check completion status.
        $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);
    }
}
