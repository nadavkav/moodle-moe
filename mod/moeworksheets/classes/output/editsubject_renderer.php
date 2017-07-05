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

namespace mod_moeworksheets\output;

use mod_moeworksheets\form\editsubject;
use mod_moeworksheets\local\subject;
use mod_moeworksheets\form\delete_content;
use core\notification;

defined('MOODLE_INTERNAL') || die();
/**
 *
 * @author avi
 *
 */
class editsubject_renderer extends \plugin_renderer_base {

     public function editsubject_page(\moeworksheets $moeworksheetsobj,\moodle_url $url = null, $subjectid = null) {
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
            $subject->set_moeworksheetsid($moeworksheetsobj->get_moeworksheets()->id);
            $subject->add_entry();
            redirect(new \moodle_url('/mod/moeworksheets/editsubject.php', array(
                'cmid' => $url->get_param('cmid'),
                'action' => 'view',
            )), get_string('subjectsuccessfulsave', 'moeworksheets'), null, \core\output\notification::NOTIFY_SUCCESS);
        }
        $context->subjectfrom = $subjectform->render();
        $context->id = $url->get_param('id');
        $context->cmid = $url->get_param('cmid');
        return $this->render_from_template('mod_moeworksheets/editsubject', $context);
     }

     public function listsubject_page(\moeworksheets $moeworksheetsobj, $url = null, $subjectid = null) {
         global $DB;
         $context = new \stdClass();
         $context->cmid = $moeworksheetsobj->get_cmid();
         $subjects = $DB->get_records('moeworksheets_subject', array('moeworksheetsid' => $moeworksheetsobj->get_moeworksheets()->id));
         foreach ($subjects as $subject) {
             $csubject = new \stdClass();
             $csubject->id = $subject->id;
             $csubject->name = $subject->name;
             $context->subjects[] = $csubject;
         }
         return $this->render_from_template('mod_moeworksheets/subjectlist', $context);
     }

     public function deletesubject_page(\moeworksheets $moeworksheetsobj, \moodle_url $pageurl) {
         global $DB;
         $deleteform = new delete_content($pageurl);
         if ($contentdata = $deleteform->get_data()) {
             $DB->delete_records('moeworksheets_subject', array('id' => $pageurl->get_param('id')));
             $additionalcontents = $DB->get_records('moeworksheets_additionalcont', array('subjectid' => $pageurl->get_param('id')));
             foreach ($additionalcontents as $additionalcontent) {
                 $DB->set_field('moeworksheets_additionalcont', 'subjectid', null);
             }
             redirect(new \moodle_url('/mod/moeworksheets/editsubject.php',array('cmid' => $pageurl->get_param('cmid'))));
         }
         $context = new \stdClass();
         $context->deleteform = $deleteform->render();
         return $this->render_from_template('mod_moeworksheets/deletepage', $context);
     }

     public function connecttosubject_page(\moeworksheets $moeworksheets, \moodle_url $url, int $id = null) {
        global $DB;
        $context = new \stdClass();
        $subject = new subject($id);
        $additionalcontent = $subject->get_avilable_contents();
        $context->content = array();
        $context->subjectname = $subject->get_name();
        $context->subjectid = $subject->get_id();
        $this->page->requires->js_call_amd('mod_moeworksheets/loadcontent', 'init');
        $this->page->requires->strings_for_js(array(
            'changessuccessfulsave'
        ), 'mod_moeworksheets');
        foreach ($additionalcontent as $key => $content) {
            $context->content[] = $content->to_std();
            end($context->content)->subjectid = ($subject->get_id() == $content->get_subjectid()) ? $subject->get_id() : false;
        }
        return $this->render_from_template('mod_moeworksheets/connecttosubject', $context);
     }
}

