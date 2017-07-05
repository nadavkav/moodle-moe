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
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/moeworksheets/lib.php');

/**
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class mod_moeworksheets_lib_testcase extends advanced_testcase {
    public function test_moeworksheets_has_grades() {
        $moeworksheets = new stdClass();
        $moeworksheets->grade = '100.0000';
        $moeworksheets->sumgrades = '100.0000';
        $this->assertTrue(moeworksheets_has_grades($moeworksheets));
        $moeworksheets->sumgrades = '0.0000';
        $this->assertFalse(moeworksheets_has_grades($moeworksheets));
        $moeworksheets->grade = '0.0000';
        $this->assertFalse(moeworksheets_has_grades($moeworksheets));
        $moeworksheets->sumgrades = '100.0000';
        $this->assertFalse(moeworksheets_has_grades($moeworksheets));
    }

    public function test_moeworksheets_format_grade() {
        $moeworksheets = new stdClass();
        $moeworksheets->decimalpoints = 2;
        $this->assertEquals(moeworksheets_format_grade($moeworksheets, 0.12345678), format_float(0.12, 2));
        $this->assertEquals(moeworksheets_format_grade($moeworksheets, 0), format_float(0, 2));
        $this->assertEquals(moeworksheets_format_grade($moeworksheets, 1.000000000000), format_float(1, 2));
        $moeworksheets->decimalpoints = 0;
        $this->assertEquals(moeworksheets_format_grade($moeworksheets, 0.12345678), '0');
    }

    public function test_moeworksheets_get_grade_format() {
        $moeworksheets = new stdClass();
        $moeworksheets->decimalpoints = 2;
        $this->assertEquals(moeworksheets_get_grade_format($moeworksheets), 2);
        $this->assertEquals($moeworksheets->questiondecimalpoints, -1);
        $moeworksheets->questiondecimalpoints = 2;
        $this->assertEquals(moeworksheets_get_grade_format($moeworksheets), 2);
        $moeworksheets->decimalpoints = 3;
        $moeworksheets->questiondecimalpoints = -1;
        $this->assertEquals(moeworksheets_get_grade_format($moeworksheets), 3);
        $moeworksheets->questiondecimalpoints = 4;
        $this->assertEquals(moeworksheets_get_grade_format($moeworksheets), 4);
    }

    public function test_moeworksheets_format_question_grade() {
        $moeworksheets = new stdClass();
        $moeworksheets->decimalpoints = 2;
        $moeworksheets->questiondecimalpoints = 2;
        $this->assertEquals(moeworksheets_format_question_grade($moeworksheets, 0.12345678), format_float(0.12, 2));
        $this->assertEquals(moeworksheets_format_question_grade($moeworksheets, 0), format_float(0, 2));
        $this->assertEquals(moeworksheets_format_question_grade($moeworksheets, 1.000000000000), format_float(1, 2));
        $moeworksheets->decimalpoints = 3;
        $moeworksheets->questiondecimalpoints = -1;
        $this->assertEquals(moeworksheets_format_question_grade($moeworksheets, 0.12345678), format_float(0.123, 3));
        $this->assertEquals(moeworksheets_format_question_grade($moeworksheets, 0), format_float(0, 3));
        $this->assertEquals(moeworksheets_format_question_grade($moeworksheets, 1.000000000000), format_float(1, 3));
        $moeworksheets->questiondecimalpoints = 4;
        $this->assertEquals(moeworksheets_format_question_grade($moeworksheets, 0.12345678), format_float(0.1235, 4));
        $this->assertEquals(moeworksheets_format_question_grade($moeworksheets, 0), format_float(0, 4));
        $this->assertEquals(moeworksheets_format_question_grade($moeworksheets, 1.000000000000), format_float(1, 4));
    }

    /**
     * Test deleting a moeworksheets instance.
     */
    public function test_moeworksheets_delete_instance() {
        global $SITE, $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Setup a moeworksheets with 1 standard and 1 random question.
        $moeworksheetsgenerator = $this->getDataGenerator()->get_plugin_generator('mod_moeworksheets');
        $moeworksheets = $moeworksheetsgenerator->create_instance(array('course' => $SITE->id, 'questionsperpage' => 3, 'grade' => 100.0));

        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        $standardq = $questiongenerator->create_question('shortanswer', null, array('category' => $cat->id));

        moeworksheets_add_moeworksheets_question($standardq->id, $moeworksheets);
        moeworksheets_add_random_questions($moeworksheets, 0, $cat->id, 1, false);

        // Get the random question.
        $randomq = $DB->get_record('question', array('qtype' => 'random'));

        moeworksheets_delete_instance($moeworksheets->id);

        // Check that the random question was deleted.
        $count = $DB->count_records('question', array('id' => $randomq->id));
        $this->assertEquals(0, $count);
        // Check that the standard question was not deleted.
        $count = $DB->count_records('question', array('id' => $standardq->id));
        $this->assertEquals(1, $count);

        // Check that all the slots were removed.
        $count = $DB->count_records('moeworksheets_slots', array('moeworksheetsid' => $moeworksheets->id));
        $this->assertEquals(0, $count);

        // Check that the moeworksheets was removed.
        $count = $DB->count_records('moeworksheets', array('id' => $moeworksheets->id));
        $this->assertEquals(0, $count);
    }

    /**
     * Test checking the completion state of a moeworksheets.
     */
    public function test_moeworksheets_get_completion_state() {
        global $CFG, $DB;
        $this->resetAfterTest(true);

        // Enable completion before creating modules, otherwise the completion data is not written in DB.
        $CFG->enablecompletion = true;

        // Create a course and student.
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => true));
        $passstudent = $this->getDataGenerator()->create_user();
        $failstudent = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->assertNotEmpty($studentrole);

        // Enrol students.
        $this->assertTrue($this->getDataGenerator()->enrol_user($passstudent->id, $course->id, $studentrole->id));
        $this->assertTrue($this->getDataGenerator()->enrol_user($failstudent->id, $course->id, $studentrole->id));

        // Make a scale and an outcome.
        $scale = $this->getDataGenerator()->create_scale();
        $data = array('courseid' => $course->id,
                      'fullname' => 'Team work',
                      'shortname' => 'Team work',
                      'scaleid' => $scale->id);
        $outcome = $this->getDataGenerator()->create_grade_outcome($data);

        // Make a moeworksheets with the outcome on.
        $moeworksheetsgenerator = $this->getDataGenerator()->get_plugin_generator('mod_moeworksheets');
        $data = array('course' => $course->id,
                      'outcome_'.$outcome->id => 1,
                      'grade' => 100.0,
                      'questionsperpage' => 0,
                      'sumgrades' => 1,
                      'completion' => COMPLETION_TRACKING_AUTOMATIC,
                      'completionpass' => 1);
        $moeworksheets = $moeworksheetsgenerator->create_instance($data);
        $cm = get_coursemodule_from_id('moeworksheets', $moeworksheets->cmid);

        // Create a couple of questions.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');

        $cat = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('numerical', null, array('category' => $cat->id));
        moeworksheets_add_moeworksheets_question($question->id, $moeworksheets);

        $moeworksheetsobj = moeworksheets::create($moeworksheets->id, $passstudent->id);

        // Set grade to pass.
        $item = grade_item::fetch(array('courseid' => $course->id, 'itemtype' => 'mod',
                                        'itemmodule' => 'moeworksheets', 'iteminstance' => $moeworksheets->id, 'outcomeid' => null));
        $item->gradepass = 80;
        $item->update();

        // Start the passing attempt.
        $quba = question_engine::make_questions_usage_by_activity('mod_moeworksheets', $moeworksheetsobj->get_context());
        $quba->set_preferred_behaviour($moeworksheetsobj->get_moeworksheets()->preferredbehaviour);

        $timenow = time();
        $attempt = moeworksheets_create_attempt($moeworksheetsobj, 1, false, $timenow, false, $passstudent->id);
        moeworksheets_start_new_attempt($moeworksheetsobj, $quba, $attempt, 1, $timenow);
        moeworksheets_attempt_save_started($moeworksheetsobj, $quba, $attempt);

        // Process some responses from the student.
        $attemptobj = moeworksheets_attempt::create($attempt->id);
        $tosubmit = array(1 => array('answer' => '3.14'));
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);

        // Finish the attempt.
        $attemptobj = moeworksheets_attempt::create($attempt->id);
        $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());
        $attemptobj->process_finish($timenow, false);

        // Start the failing attempt.
        $quba = question_engine::make_questions_usage_by_activity('mod_moeworksheets', $moeworksheetsobj->get_context());
        $quba->set_preferred_behaviour($moeworksheetsobj->get_moeworksheets()->preferredbehaviour);

        $timenow = time();
        $attempt = moeworksheets_create_attempt($moeworksheetsobj, 1, false, $timenow, false, $failstudent->id);
        moeworksheets_start_new_attempt($moeworksheetsobj, $quba, $attempt, 1, $timenow);
        moeworksheets_attempt_save_started($moeworksheetsobj, $quba, $attempt);

        // Process some responses from the student.
        $attemptobj = moeworksheets_attempt::create($attempt->id);
        $tosubmit = array(1 => array('answer' => '0'));
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);

        // Finish the attempt.
        $attemptobj = moeworksheets_attempt::create($attempt->id);
        $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());
        $attemptobj->process_finish($timenow, false);

        // Check the results.
        $this->assertTrue(moeworksheets_get_completion_state($course, $cm, $passstudent->id, 'return'));
        $this->assertFalse(moeworksheets_get_completion_state($course, $cm, $failstudent->id, 'return'));
    }

    public function test_moeworksheets_get_user_attempts() {
        global $DB;
        $this->resetAfterTest();

        $dg = $this->getDataGenerator();
        $moeworksheetsgen = $dg->get_plugin_generator('mod_moeworksheets');
        $course = $dg->create_course();
        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $u4 = $dg->create_user();
        $role = $DB->get_record('role', ['shortname' => 'student']);

        $dg->enrol_user($u1->id, $course->id, $role->id);
        $dg->enrol_user($u2->id, $course->id, $role->id);
        $dg->enrol_user($u3->id, $course->id, $role->id);
        $dg->enrol_user($u4->id, $course->id, $role->id);

        $moeworksheets1 = $moeworksheetsgen->create_instance(['course' => $course->id, 'sumgrades' => 2]);
        $moeworksheets2 = $moeworksheetsgen->create_instance(['course' => $course->id, 'sumgrades' => 2]);

        // Questions.
        $questgen = $dg->get_plugin_generator('core_question');
        $moeworksheetscat = $questgen->create_question_category();
        $question = $questgen->create_question('numerical', null, ['category' => $moeworksheetscat->id]);
        moeworksheets_add_moeworksheets_question($question->id, $moeworksheets1);
        moeworksheets_add_moeworksheets_question($question->id, $moeworksheets2);

        $moeworksheetsobj1a = moeworksheets::create($moeworksheets1->id, $u1->id);
        $moeworksheetsobj1b = moeworksheets::create($moeworksheets1->id, $u2->id);
        $moeworksheetsobj1c = moeworksheets::create($moeworksheets1->id, $u3->id);
        $moeworksheetsobj1d = moeworksheets::create($moeworksheets1->id, $u4->id);
        $moeworksheetsobj2a = moeworksheets::create($moeworksheets2->id, $u1->id);

        // Set attempts.
        $quba1a = question_engine::make_questions_usage_by_activity('mod_moeworksheets', $moeworksheetsobj1a->get_context());
        $quba1a->set_preferred_behaviour($moeworksheetsobj1a->get_moeworksheets()->preferredbehaviour);
        $quba1b = question_engine::make_questions_usage_by_activity('mod_moeworksheets', $moeworksheetsobj1b->get_context());
        $quba1b->set_preferred_behaviour($moeworksheetsobj1b->get_moeworksheets()->preferredbehaviour);
        $quba1c = question_engine::make_questions_usage_by_activity('mod_moeworksheets', $moeworksheetsobj1c->get_context());
        $quba1c->set_preferred_behaviour($moeworksheetsobj1c->get_moeworksheets()->preferredbehaviour);
        $quba1d = question_engine::make_questions_usage_by_activity('mod_moeworksheets', $moeworksheetsobj1d->get_context());
        $quba1d->set_preferred_behaviour($moeworksheetsobj1d->get_moeworksheets()->preferredbehaviour);
        $quba2a = question_engine::make_questions_usage_by_activity('mod_moeworksheets', $moeworksheetsobj2a->get_context());
        $quba2a->set_preferred_behaviour($moeworksheetsobj2a->get_moeworksheets()->preferredbehaviour);

        $timenow = time();

        // User 1 passes moeworksheets 1.
        $attempt = moeworksheets_create_attempt($moeworksheetsobj1a, 1, false, $timenow, false, $u1->id);
        moeworksheets_start_new_attempt($moeworksheetsobj1a, $quba1a, $attempt, 1, $timenow);
        moeworksheets_attempt_save_started($moeworksheetsobj1a, $quba1a, $attempt);
        $attemptobj = moeworksheets_attempt::create($attempt->id);
        $attemptobj->process_submitted_actions($timenow, false, [1 => ['answer' => '3.14']]);
        $attemptobj->process_finish($timenow, false);

        // User 2 goes overdue in moeworksheets 1.
        $attempt = moeworksheets_create_attempt($moeworksheetsobj1b, 1, false, $timenow, false, $u2->id);
        moeworksheets_start_new_attempt($moeworksheetsobj1b, $quba1b, $attempt, 1, $timenow);
        moeworksheets_attempt_save_started($moeworksheetsobj1b, $quba1b, $attempt);
        $attemptobj = moeworksheets_attempt::create($attempt->id);
        $attemptobj->process_going_overdue($timenow, true);

        // User 3 does not finish moeworksheets 1.
        $attempt = moeworksheets_create_attempt($moeworksheetsobj1c, 1, false, $timenow, false, $u3->id);
        moeworksheets_start_new_attempt($moeworksheetsobj1c, $quba1c, $attempt, 1, $timenow);
        moeworksheets_attempt_save_started($moeworksheetsobj1c, $quba1c, $attempt);

        // User 4 abandons the moeworksheets 1.
        $attempt = moeworksheets_create_attempt($moeworksheetsobj1d, 1, false, $timenow, false, $u4->id);
        moeworksheets_start_new_attempt($moeworksheetsobj1d, $quba1d, $attempt, 1, $timenow);
        moeworksheets_attempt_save_started($moeworksheetsobj1d, $quba1d, $attempt);
        $attemptobj = moeworksheets_attempt::create($attempt->id);
        $attemptobj->process_abandon($timenow, true);

        // User 1 attempts the moeworksheets three times (abandon, finish, in progress).
        $quba2a = question_engine::make_questions_usage_by_activity('mod_moeworksheets', $moeworksheetsobj2a->get_context());
        $quba2a->set_preferred_behaviour($moeworksheetsobj2a->get_moeworksheets()->preferredbehaviour);

        $attempt = moeworksheets_create_attempt($moeworksheetsobj2a, 1, false, $timenow, false, $u1->id);
        moeworksheets_start_new_attempt($moeworksheetsobj2a, $quba2a, $attempt, 1, $timenow);
        moeworksheets_attempt_save_started($moeworksheetsobj2a, $quba2a, $attempt);
        $attemptobj = moeworksheets_attempt::create($attempt->id);
        $attemptobj->process_abandon($timenow, true);

        $quba2a = question_engine::make_questions_usage_by_activity('mod_moeworksheets', $moeworksheetsobj2a->get_context());
        $quba2a->set_preferred_behaviour($moeworksheetsobj2a->get_moeworksheets()->preferredbehaviour);

        $attempt = moeworksheets_create_attempt($moeworksheetsobj2a, 2, false, $timenow, false, $u1->id);
        moeworksheets_start_new_attempt($moeworksheetsobj2a, $quba2a, $attempt, 2, $timenow);
        moeworksheets_attempt_save_started($moeworksheetsobj2a, $quba2a, $attempt);
        $attemptobj = moeworksheets_attempt::create($attempt->id);
        $attemptobj->process_finish($timenow, false);

        $quba2a = question_engine::make_questions_usage_by_activity('mod_moeworksheets', $moeworksheetsobj2a->get_context());
        $quba2a->set_preferred_behaviour($moeworksheetsobj2a->get_moeworksheets()->preferredbehaviour);

        $attempt = moeworksheets_create_attempt($moeworksheetsobj2a, 3, false, $timenow, false, $u1->id);
        moeworksheets_start_new_attempt($moeworksheetsobj2a, $quba2a, $attempt, 3, $timenow);
        moeworksheets_attempt_save_started($moeworksheetsobj2a, $quba2a, $attempt);

        // Check for user 1.
        $attempts = moeworksheets_get_user_attempts($moeworksheets1->id, $u1->id, 'all');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::FINISHED, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($moeworksheets1->id, $attempt->moeworksheets);

        $attempts = moeworksheets_get_user_attempts($moeworksheets1->id, $u1->id, 'finished');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::FINISHED, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($moeworksheets1->id, $attempt->moeworksheets);

        $attempts = moeworksheets_get_user_attempts($moeworksheets1->id, $u1->id, 'unfinished');
        $this->assertCount(0, $attempts);

        // Check for user 2.
        $attempts = moeworksheets_get_user_attempts($moeworksheets1->id, $u2->id, 'all');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::OVERDUE, $attempt->state);
        $this->assertEquals($u2->id, $attempt->userid);
        $this->assertEquals($moeworksheets1->id, $attempt->moeworksheets);

        $attempts = moeworksheets_get_user_attempts($moeworksheets1->id, $u2->id, 'finished');
        $this->assertCount(0, $attempts);

        $attempts = moeworksheets_get_user_attempts($moeworksheets1->id, $u2->id, 'unfinished');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::OVERDUE, $attempt->state);
        $this->assertEquals($u2->id, $attempt->userid);
        $this->assertEquals($moeworksheets1->id, $attempt->moeworksheets);

        // Check for user 3.
        $attempts = moeworksheets_get_user_attempts($moeworksheets1->id, $u3->id, 'all');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::IN_PROGRESS, $attempt->state);
        $this->assertEquals($u3->id, $attempt->userid);
        $this->assertEquals($moeworksheets1->id, $attempt->moeworksheets);

        $attempts = moeworksheets_get_user_attempts($moeworksheets1->id, $u3->id, 'finished');
        $this->assertCount(0, $attempts);

        $attempts = moeworksheets_get_user_attempts($moeworksheets1->id, $u3->id, 'unfinished');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::IN_PROGRESS, $attempt->state);
        $this->assertEquals($u3->id, $attempt->userid);
        $this->assertEquals($moeworksheets1->id, $attempt->moeworksheets);

        // Check for user 4.
        $attempts = moeworksheets_get_user_attempts($moeworksheets1->id, $u4->id, 'all');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::ABANDONED, $attempt->state);
        $this->assertEquals($u4->id, $attempt->userid);
        $this->assertEquals($moeworksheets1->id, $attempt->moeworksheets);

        $attempts = moeworksheets_get_user_attempts($moeworksheets1->id, $u4->id, 'finished');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::ABANDONED, $attempt->state);
        $this->assertEquals($u4->id, $attempt->userid);
        $this->assertEquals($moeworksheets1->id, $attempt->moeworksheets);

        $attempts = moeworksheets_get_user_attempts($moeworksheets1->id, $u4->id, 'unfinished');
        $this->assertCount(0, $attempts);

        // Multiple attempts for user 1 in moeworksheets 2.
        $attempts = moeworksheets_get_user_attempts($moeworksheets2->id, $u1->id, 'all');
        $this->assertCount(3, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::ABANDONED, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($moeworksheets2->id, $attempt->moeworksheets);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::FINISHED, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($moeworksheets2->id, $attempt->moeworksheets);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::IN_PROGRESS, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($moeworksheets2->id, $attempt->moeworksheets);

        $attempts = moeworksheets_get_user_attempts($moeworksheets2->id, $u1->id, 'finished');
        $this->assertCount(2, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::ABANDONED, $attempt->state);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::FINISHED, $attempt->state);

        $attempts = moeworksheets_get_user_attempts($moeworksheets2->id, $u1->id, 'unfinished');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);

        // Multiple moeworksheets attempts fetched at once.
        $attempts = moeworksheets_get_user_attempts([$moeworksheets1->id, $moeworksheets2->id], $u1->id, 'all');
        $this->assertCount(4, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::FINISHED, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($moeworksheets1->id, $attempt->moeworksheets);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::ABANDONED, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($moeworksheets2->id, $attempt->moeworksheets);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::FINISHED, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($moeworksheets2->id, $attempt->moeworksheets);
        $attempt = array_shift($attempts);
        $this->assertEquals(moeworksheets_attempt::IN_PROGRESS, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($moeworksheets2->id, $attempt->moeworksheets);
    }

}
