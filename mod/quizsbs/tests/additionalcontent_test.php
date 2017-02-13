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
        $additionalcontent = new additional_content();
        $additionalcontent->set_name('test content');
        $additionalcontent->set_quizsbsid(1);
        $additionalcontent->add_entry();
        unset($additionalcontent);
        $additionalcontent = new additional_content(1);
        $this->assertEquals('test content', $additionalcontent->get_name());
    }
}

