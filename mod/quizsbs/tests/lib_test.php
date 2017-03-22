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
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quizsbs/lib.php');

/**
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class mod_quizsbs_lib_testcase extends advanced_testcase {
    public function test_quizsbs_has_grades() {
        $quizsbs = new stdClass();
        $quizsbs->grade = '100.0000';
        $quizsbs->sumgrades = '100.0000';
        $this->assertTrue(quizsbs_has_grades($quizsbs));
        $quizsbs->sumgrades = '0.0000';
        $this->assertFalse(quizsbs_has_grades($quizsbs));
        $quizsbs->grade = '0.0000';
        $this->assertFalse(quizsbs_has_grades($quizsbs));
        $quizsbs->sumgrades = '100.0000';
        $this->assertFalse(quizsbs_has_grades($quizsbs));
    }

    public function test_quizsbs_format_grade() {
        $quizsbs = new stdClass();
        $quizsbs->decimalpoints = 2;
        $this->assertEquals(quizsbs_format_grade($quizsbs, 0.12345678), format_float(0.12, 2));
        $this->assertEquals(quizsbs_format_grade($quizsbs, 0), format_float(0, 2));
        $this->assertEquals(quizsbs_format_grade($quizsbs, 1.000000000000), format_float(1, 2));
        $quizsbs->decimalpoints = 0;
        $this->assertEquals(quizsbs_format_grade($quizsbs, 0.12345678), '0');
    }

    public function test_quizsbs_get_grade_format() {
        $quizsbs = new stdClass();
        $quizsbs->decimalpoints = 2;
        $this->assertEquals(quizsbs_get_grade_format($quizsbs), 2);
        $this->assertEquals($quizsbs->questiondecimalpoints, -1);
        $quizsbs->questiondecimalpoints = 2;
        $this->assertEquals(quizsbs_get_grade_format($quizsbs), 2);
        $quizsbs->decimalpoints = 3;
        $quizsbs->questiondecimalpoints = -1;
        $this->assertEquals(quizsbs_get_grade_format($quizsbs), 3);
        $quizsbs->questiondecimalpoints = 4;
        $this->assertEquals(quizsbs_get_grade_format($quizsbs), 4);
    }

    public function test_quizsbs_format_question_grade() {
        $quizsbs = new stdClass();
        $quizsbs->decimalpoints = 2;
        $quizsbs->questiondecimalpoints = 2;
        $this->assertEquals(quizsbs_format_question_grade($quizsbs, 0.12345678), format_float(0.12, 2));
        $this->assertEquals(quizsbs_format_question_grade($quizsbs, 0), format_float(0, 2));
        $this->assertEquals(quizsbs_format_question_grade($quizsbs, 1.000000000000), format_float(1, 2));
        $quizsbs->decimalpoints = 3;
        $quizsbs->questiondecimalpoints = -1;
        $this->assertEquals(quizsbs_format_question_grade($quizsbs, 0.12345678), format_float(0.123, 3));
        $this->assertEquals(quizsbs_format_question_grade($quizsbs, 0), format_float(0, 3));
        $this->assertEquals(quizsbs_format_question_grade($quizsbs, 1.000000000000), format_float(1, 3));
        $quizsbs->questiondecimalpoints = 4;
        $this->assertEquals(quizsbs_format_question_grade($quizsbs, 0.12345678), format_float(0.1235, 4));
        $this->assertEquals(quizsbs_format_question_grade($quizsbs, 0), format_float(0, 4));
        $this->assertEquals(quizsbs_format_question_grade($quizsbs, 1.000000000000), format_float(1, 4));
    }

    /**
     * Test deleting a quizsbs instance.
     */
    public function test_quizsbs_delete_instance() {
        global $SITE, $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Setup a quizsbs with 1 standard and 1 random question.
        $quizsbsgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quizsbs');
        $quizsbs = $quizsbsgenerator->create_instance(array('course' => $SITE->id, 'questionsperpage' => 3, 'grade' => 100.0));

        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        $standardq = $questiongenerator->create_question('shortanswer', null, array('category' => $cat->id));

        quizsbs_add_quizsbs_question($standardq->id, $quizsbs);
        quizsbs_add_random_questions($quizsbs, 0, $cat->id, 1, false);

        // Get the random question.
        $randomq = $DB->get_record('question', array('qtype' => 'random'));

        quizsbs_delete_instance($quizsbs->id);

        // Check that the random question was deleted.
        $count = $DB->count_records('question', array('id' => $randomq->id));
        $this->assertEquals(0, $count);
        // Check that the standard question was not deleted.
        $count = $DB->count_records('question', array('id' => $standardq->id));
        $this->assertEquals(1, $count);

        // Check that all the slots were removed.
        $count = $DB->count_records('quizsbs_slots', array('quizsbsid' => $quizsbs->id));
        $this->assertEquals(0, $count);

        // Check that the quizsbs was removed.
        $count = $DB->count_records('quizsbs', array('id' => $quizsbs->id));
        $this->assertEquals(0, $count);
    }

    /**
     * Test checking the completion state of a quizsbs.
     */
    public function test_quizsbs_get_completion_state() {
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

        // Make a quizsbs with the outcome on.
        $quizsbsgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quizsbs');
        $data = array('course' => $course->id,
                      'outcome_'.$outcome->id => 1,
                      'grade' => 100.0,
                      'questionsperpage' => 0,
                      'sumgrades' => 1,
                      'completion' => COMPLETION_TRACKING_AUTOMATIC,
                      'completionpass' => 1);
        $quizsbs = $quizsbsgenerator->create_instance($data);
        $cm = get_coursemodule_from_id('quizsbs', $quizsbs->cmid);

        // Create a couple of questions.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');

        $cat = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('numerical', null, array('category' => $cat->id));
        quizsbs_add_quizsbs_question($question->id, $quizsbs);

        $quizsbsobj = quizsbs::create($quizsbs->id, $passstudent->id);

        // Set grade to pass.
        $item = grade_item::fetch(array('courseid' => $course->id, 'itemtype' => 'mod',
                                        'itemmodule' => 'quizsbs', 'iteminstance' => $quizsbs->id, 'outcomeid' => null));
        $item->gradepass = 80;
        $item->update();

        // Start the passing attempt.
        $quba = question_engine::make_questions_usage_by_activity('mod_quizsbs', $quizsbsobj->get_context());
        $quba->set_preferred_behaviour($quizsbsobj->get_quizsbs()->preferredbehaviour);

        $timenow = time();
        $attempt = quizsbs_create_attempt($quizsbsobj, 1, false, $timenow, false, $passstudent->id);
        quizsbs_start_new_attempt($quizsbsobj, $quba, $attempt, 1, $timenow);
        quizsbs_attempt_save_started($quizsbsobj, $quba, $attempt);

        // Process some responses from the student.
        $attemptobj = quizsbs_attempt::create($attempt->id);
        $tosubmit = array(1 => array('answer' => '3.14'));
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);

        // Finish the attempt.
        $attemptobj = quizsbs_attempt::create($attempt->id);
        $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());
        $attemptobj->process_finish($timenow, false);

        // Start the failing attempt.
        $quba = question_engine::make_questions_usage_by_activity('mod_quizsbs', $quizsbsobj->get_context());
        $quba->set_preferred_behaviour($quizsbsobj->get_quizsbs()->preferredbehaviour);

        $timenow = time();
        $attempt = quizsbs_create_attempt($quizsbsobj, 1, false, $timenow, false, $failstudent->id);
        quizsbs_start_new_attempt($quizsbsobj, $quba, $attempt, 1, $timenow);
        quizsbs_attempt_save_started($quizsbsobj, $quba, $attempt);

        // Process some responses from the student.
        $attemptobj = quizsbs_attempt::create($attempt->id);
        $tosubmit = array(1 => array('answer' => '0'));
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);

        // Finish the attempt.
        $attemptobj = quizsbs_attempt::create($attempt->id);
        $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());
        $attemptobj->process_finish($timenow, false);

        // Check the results.
        $this->assertTrue(quizsbs_get_completion_state($course, $cm, $passstudent->id, 'return'));
        $this->assertFalse(quizsbs_get_completion_state($course, $cm, $failstudent->id, 'return'));
    }

    public function test_quizsbs_get_user_attempts() {
        global $DB;
        $this->resetAfterTest();

        $dg = $this->getDataGenerator();
        $quizsbsgen = $dg->get_plugin_generator('mod_quizsbs');
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

        $quizsbs1 = $quizsbsgen->create_instance(['course' => $course->id, 'sumgrades' => 2]);
        $quizsbs2 = $quizsbsgen->create_instance(['course' => $course->id, 'sumgrades' => 2]);

        // Questions.
        $questgen = $dg->get_plugin_generator('core_question');
        $quizsbscat = $questgen->create_question_category();
        $question = $questgen->create_question('numerical', null, ['category' => $quizsbscat->id]);
        quizsbs_add_quizsbs_question($question->id, $quizsbs1);
        quizsbs_add_quizsbs_question($question->id, $quizsbs2);

        $quizsbsobj1a = quizsbs::create($quizsbs1->id, $u1->id);
        $quizsbsobj1b = quizsbs::create($quizsbs1->id, $u2->id);
        $quizsbsobj1c = quizsbs::create($quizsbs1->id, $u3->id);
        $quizsbsobj1d = quizsbs::create($quizsbs1->id, $u4->id);
        $quizsbsobj2a = quizsbs::create($quizsbs2->id, $u1->id);

        // Set attempts.
        $quba1a = question_engine::make_questions_usage_by_activity('mod_quizsbs', $quizsbsobj1a->get_context());
        $quba1a->set_preferred_behaviour($quizsbsobj1a->get_quizsbs()->preferredbehaviour);
        $quba1b = question_engine::make_questions_usage_by_activity('mod_quizsbs', $quizsbsobj1b->get_context());
        $quba1b->set_preferred_behaviour($quizsbsobj1b->get_quizsbs()->preferredbehaviour);
        $quba1c = question_engine::make_questions_usage_by_activity('mod_quizsbs', $quizsbsobj1c->get_context());
        $quba1c->set_preferred_behaviour($quizsbsobj1c->get_quizsbs()->preferredbehaviour);
        $quba1d = question_engine::make_questions_usage_by_activity('mod_quizsbs', $quizsbsobj1d->get_context());
        $quba1d->set_preferred_behaviour($quizsbsobj1d->get_quizsbs()->preferredbehaviour);
        $quba2a = question_engine::make_questions_usage_by_activity('mod_quizsbs', $quizsbsobj2a->get_context());
        $quba2a->set_preferred_behaviour($quizsbsobj2a->get_quizsbs()->preferredbehaviour);

        $timenow = time();

        // User 1 passes quizsbs 1.
        $attempt = quizsbs_create_attempt($quizsbsobj1a, 1, false, $timenow, false, $u1->id);
        quizsbs_start_new_attempt($quizsbsobj1a, $quba1a, $attempt, 1, $timenow);
        quizsbs_attempt_save_started($quizsbsobj1a, $quba1a, $attempt);
        $attemptobj = quizsbs_attempt::create($attempt->id);
        $attemptobj->process_submitted_actions($timenow, false, [1 => ['answer' => '3.14']]);
        $attemptobj->process_finish($timenow, false);

        // User 2 goes overdue in quizsbs 1.
        $attempt = quizsbs_create_attempt($quizsbsobj1b, 1, false, $timenow, false, $u2->id);
        quizsbs_start_new_attempt($quizsbsobj1b, $quba1b, $attempt, 1, $timenow);
        quizsbs_attempt_save_started($quizsbsobj1b, $quba1b, $attempt);
        $attemptobj = quizsbs_attempt::create($attempt->id);
        $attemptobj->process_going_overdue($timenow, true);

        // User 3 does not finish quizsbs 1.
        $attempt = quizsbs_create_attempt($quizsbsobj1c, 1, false, $timenow, false, $u3->id);
        quizsbs_start_new_attempt($quizsbsobj1c, $quba1c, $attempt, 1, $timenow);
        quizsbs_attempt_save_started($quizsbsobj1c, $quba1c, $attempt);

        // User 4 abandons the quizsbs 1.
        $attempt = quizsbs_create_attempt($quizsbsobj1d, 1, false, $timenow, false, $u4->id);
        quizsbs_start_new_attempt($quizsbsobj1d, $quba1d, $attempt, 1, $timenow);
        quizsbs_attempt_save_started($quizsbsobj1d, $quba1d, $attempt);
        $attemptobj = quizsbs_attempt::create($attempt->id);
        $attemptobj->process_abandon($timenow, true);

        // User 1 attempts the quizsbs three times (abandon, finish, in progress).
        $quba2a = question_engine::make_questions_usage_by_activity('mod_quizsbs', $quizsbsobj2a->get_context());
        $quba2a->set_preferred_behaviour($quizsbsobj2a->get_quizsbs()->preferredbehaviour);

        $attempt = quizsbs_create_attempt($quizsbsobj2a, 1, false, $timenow, false, $u1->id);
        quizsbs_start_new_attempt($quizsbsobj2a, $quba2a, $attempt, 1, $timenow);
        quizsbs_attempt_save_started($quizsbsobj2a, $quba2a, $attempt);
        $attemptobj = quizsbs_attempt::create($attempt->id);
        $attemptobj->process_abandon($timenow, true);

        $quba2a = question_engine::make_questions_usage_by_activity('mod_quizsbs', $quizsbsobj2a->get_context());
        $quba2a->set_preferred_behaviour($quizsbsobj2a->get_quizsbs()->preferredbehaviour);

        $attempt = quizsbs_create_attempt($quizsbsobj2a, 2, false, $timenow, false, $u1->id);
        quizsbs_start_new_attempt($quizsbsobj2a, $quba2a, $attempt, 2, $timenow);
        quizsbs_attempt_save_started($quizsbsobj2a, $quba2a, $attempt);
        $attemptobj = quizsbs_attempt::create($attempt->id);
        $attemptobj->process_finish($timenow, false);

        $quba2a = question_engine::make_questions_usage_by_activity('mod_quizsbs', $quizsbsobj2a->get_context());
        $quba2a->set_preferred_behaviour($quizsbsobj2a->get_quizsbs()->preferredbehaviour);

        $attempt = quizsbs_create_attempt($quizsbsobj2a, 3, false, $timenow, false, $u1->id);
        quizsbs_start_new_attempt($quizsbsobj2a, $quba2a, $attempt, 3, $timenow);
        quizsbs_attempt_save_started($quizsbsobj2a, $quba2a, $attempt);

        // Check for user 1.
        $attempts = quizsbs_get_user_attempts($quizsbs1->id, $u1->id, 'all');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::FINISHED, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($quizsbs1->id, $attempt->quizsbs);

        $attempts = quizsbs_get_user_attempts($quizsbs1->id, $u1->id, 'finished');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::FINISHED, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($quizsbs1->id, $attempt->quizsbs);

        $attempts = quizsbs_get_user_attempts($quizsbs1->id, $u1->id, 'unfinished');
        $this->assertCount(0, $attempts);

        // Check for user 2.
        $attempts = quizsbs_get_user_attempts($quizsbs1->id, $u2->id, 'all');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::OVERDUE, $attempt->state);
        $this->assertEquals($u2->id, $attempt->userid);
        $this->assertEquals($quizsbs1->id, $attempt->quizsbs);

        $attempts = quizsbs_get_user_attempts($quizsbs1->id, $u2->id, 'finished');
        $this->assertCount(0, $attempts);

        $attempts = quizsbs_get_user_attempts($quizsbs1->id, $u2->id, 'unfinished');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::OVERDUE, $attempt->state);
        $this->assertEquals($u2->id, $attempt->userid);
        $this->assertEquals($quizsbs1->id, $attempt->quizsbs);

        // Check for user 3.
        $attempts = quizsbs_get_user_attempts($quizsbs1->id, $u3->id, 'all');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::IN_PROGRESS, $attempt->state);
        $this->assertEquals($u3->id, $attempt->userid);
        $this->assertEquals($quizsbs1->id, $attempt->quizsbs);

        $attempts = quizsbs_get_user_attempts($quizsbs1->id, $u3->id, 'finished');
        $this->assertCount(0, $attempts);

        $attempts = quizsbs_get_user_attempts($quizsbs1->id, $u3->id, 'unfinished');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::IN_PROGRESS, $attempt->state);
        $this->assertEquals($u3->id, $attempt->userid);
        $this->assertEquals($quizsbs1->id, $attempt->quizsbs);

        // Check for user 4.
        $attempts = quizsbs_get_user_attempts($quizsbs1->id, $u4->id, 'all');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::ABANDONED, $attempt->state);
        $this->assertEquals($u4->id, $attempt->userid);
        $this->assertEquals($quizsbs1->id, $attempt->quizsbs);

        $attempts = quizsbs_get_user_attempts($quizsbs1->id, $u4->id, 'finished');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::ABANDONED, $attempt->state);
        $this->assertEquals($u4->id, $attempt->userid);
        $this->assertEquals($quizsbs1->id, $attempt->quizsbs);

        $attempts = quizsbs_get_user_attempts($quizsbs1->id, $u4->id, 'unfinished');
        $this->assertCount(0, $attempts);

        // Multiple attempts for user 1 in quizsbs 2.
        $attempts = quizsbs_get_user_attempts($quizsbs2->id, $u1->id, 'all');
        $this->assertCount(3, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::ABANDONED, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($quizsbs2->id, $attempt->quizsbs);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::FINISHED, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($quizsbs2->id, $attempt->quizsbs);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::IN_PROGRESS, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($quizsbs2->id, $attempt->quizsbs);

        $attempts = quizsbs_get_user_attempts($quizsbs2->id, $u1->id, 'finished');
        $this->assertCount(2, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::ABANDONED, $attempt->state);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::FINISHED, $attempt->state);

        $attempts = quizsbs_get_user_attempts($quizsbs2->id, $u1->id, 'unfinished');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);

        // Multiple quizsbs attempts fetched at once.
        $attempts = quizsbs_get_user_attempts([$quizsbs1->id, $quizsbs2->id], $u1->id, 'all');
        $this->assertCount(4, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::FINISHED, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($quizsbs1->id, $attempt->quizsbs);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::ABANDONED, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($quizsbs2->id, $attempt->quizsbs);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::FINISHED, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($quizsbs2->id, $attempt->quizsbs);
        $attempt = array_shift($attempts);
        $this->assertEquals(quizsbs_attempt::IN_PROGRESS, $attempt->state);
        $this->assertEquals($u1->id, $attempt->userid);
        $this->assertEquals($quizsbs2->id, $attempt->quizsbs);
    }

}
