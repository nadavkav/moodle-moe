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

namespace mod_quizsbs\output;

use mod_quizsbs\form\editsubject;
use mod_quizsbs\local\subject;
use mod_quizsbs\form\delete_content;
use core\notification;

defined('MOODLE_INTERNAL') || die();
/**
 *
 * @author avi
 *
 */
class editsubject_renderer extends \plugin_renderer_base {

     public function editsubject_page(\quizsbs $quizsbsobj,\moodle_url $url = null, $subjectid = null) {
        $context = new \stdClass();
        $subjectform = new editsubject($url);
        if($subjectid){
            $customdata = new \stdClass();
            $subject = new subject($subjectid);
            $customdata->subjectname = $subject->get_name();
            $subjectform->set_data($customdata);
        } else {
            $subject = new subject();
        }
        $data = $subjectform->get_data();
        if(!is_null($data)) {
            $subject->set_name($data->subjectname);
            $subject->set_quizsbsid($quizsbsobj->get_quizsbs()->id);
            $subject->add_entry();
            redirect(new \moodle_url('/mod/quizsbs/editsubject.php', array(
                'cmid' => $url->get_param('cmid'),
                'action' => 'view',
            )), get_string('subjectsuccessfulsave', 'quizsbs'), null, \core\output\notification::NOTIFY_SUCCESS);
        }
        $context->subjectfrom = $subjectform->render();
        $context->id = $url->get_param('id');
        $context->cmid = $url->get_param('cmid');
        return $this->render_from_template('mod_quizsbs/editsubject', $context);
     }

     public function listsubject_page(\quizsbs $quizsbsobj, $url = null, $subjectid = null) {
         global $DB;
         $context = new \stdClass();
         $context->cmid = $quizsbsobj->get_cmid();
         $subjects = $DB->get_records('quizsbs_subject', array('quizsbsid' => $quizsbsobj->get_quizsbs()->id));
         foreach ($subjects as $subject) {
             $csubject = new \stdClass();
             $csubject->id = $subject->id;
             $csubject->name = $subject->name;
             $context->subjects[] = $csubject;
         }
         return $this->render_from_template('mod_quizsbs/subjectlist', $context);
     }

     public function deletesubject_page(\quizsbs $quizsbsobj, \moodle_url $pageurl) {
         global $DB;
         $deleteform = new delete_content($pageurl);
         if ($contentdata = $deleteform->get_data()) {
             $DB->delete_records('quizsbs_subject', array('id' => $pageurl->get_param('id')));
             $additionalcontents = $DB->get_records('quizsbs_additional_content', array('subjectid' => $pageurl->get_param('id')));
             foreach ($additionalcontents as $additionalcontent) {
                 $DB->set_field('quizsbs_additional_content', 'subjectid', null);
             }
             redirect(new \moodle_url('/mod/quizsbs/editsubject.php',array('cmid' => $pageurl->get_param('cmid'))));
         }
         $context = new \stdClass();
         $context->deleteform = $deleteform->render();
         return $this->render_from_template('mod_quizsbs/deletepage', $context);
     }

     public function connecttosubject_page(\quizsbs $quizsbs, \moodle_url $url, int $id = null) {
        global $DB;
        $context = new \stdClass();
        $subject = new subject($id);
        $additionalcontent = $subject->get_avilable_contents();
        $context->content = array();
        $context->subjectname = $subject->get_name();
        $context->subjectid = $subject->get_id();
        $this->page->requires->js_call_amd('mod_quizsbs/loadcontent', 'init');
        $this->page->requires->strings_for_js(array(
            'changessuccessfulsave'
        ), 'mod_quizsbs');
        foreach ($additionalcontent as $key => $content) {
            $context->content[] = $content->to_std();
            end($context->content)->subjectid = ($subject->get_id() == $content->get_subjectid()) ? $subject->get_id() : false;
        }
        return $this->render_from_template('mod_quizsbs/connecttosubject', $context);
     }
}

