<?php
namespace quizsbs\tests;

use mod_quizsbs\local\additional_content;

/**
 *
 * @author avi
 *
 */
class mod_quizsbs_additionalcontent_testcase extends \advanced_testcase {
    public function test_adding() {
        $this->resetAfterTest(true);
        $additionalcontent = new additional_content();
        $additionalcontent->set_name('test content');
        $additionalcontent->set_quizsbsid(1);
        $additionalcontent->add_entry();
        $id = $additionalcontent->get_id();
        unset($additionalcontent);
        $additionalcontent = new additional_content($id);
        $this->assertEquals('test content', $additionalcontent->get_name());
        $this->assertEquals(1, $additionalcontent->get_quizsbsid());
    }
}

