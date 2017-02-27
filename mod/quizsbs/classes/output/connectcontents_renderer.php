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

use mod_quizsbs\form\connectcontent;
use mod_quizsbs\local\additional_content;

defined('MOODLE_INTERNAL') || die();
/**
 *
 * @author avi
 *
 */
class connectcontents_renderer extends \plugin_renderer_base {

    public function content_list_page(\quizsbs $quizsbsobj, $page) {
        global $DB, $PAGE;
        $context = new \stdClass();
        $customdata = new \stdClass();
        $customdata->contents = $DB->get_records_select('quizsbs_additional_content', 'quizsbsid = ? and (subjectid is null or subjectid = ?)',
                                        array(
                                            $quizsbsobj->get_quizsbsid(),
                                            $page,
                                        ));
        $connectcontentform = new connectcontent($PAGE->url, $customdata);
        $data = new \stdClass();
        $data->contents = $DB->get_field('quizsbs_additional_content', 'id', array(
            'quizsbsid' => $quizsbsobj->get_quizsbsid(),
            'subjectid' => $page,
        ));
        $connectcontentform->set_data($data);
        if($connectcontentform->is_submitted()) {
            $data = $connectcontentform->get_data();
            if(!empty($data)){
                if($data->contents != 0){
                    $additionalcontent = new additional_content($data->contents);
                    $oldadditionalcontent = $DB->get_record('quizsbs_additional_content', array(
                        'subjectid' => $page,
                        'quizsbsid' => $quizsbsobj->get_quizsbsid(),
                    ));
                    $oldadditionalcontent = new additional_content($oldadditionalcontent->id);
                    $oldadditionalcontent->set_subjectid(null);
                    $oldadditionalcontent->add_entry();
                    $additionalcontent->set_subjectid($page);
                } else {
                    $additionalcontentid = $DB->get_field('quizsbs_additional_content', 'id', array(
                        'subjectid' => $page,
                        'quizsbsid' => $quizsbsobj->get_quizsbsid(),
                    ));
                    $additionalcontent = new additional_content($additionalcontentid);
                    $additionalcontent->set_subjectid(null);
                }
                $additionalcontent->add_entry();
                redirect(new \moodle_url('/mod/quizsbs/edit.php', array(
                    'cmid' => $PAGE->url->get_param('cmid'),
                )), get_string('pagesuccessfulsave', 'quizsbs'), null, \core\output\notification::NOTIFY_SUCCESS);
            }
            redirect(new \moodle_url('/mod/quizsbs/edit.php', array(
                'cmid' => $PAGE->url->get_param('cmid'),
            )));
        }
        $context->content = array_values($DB->get_records('quizsbs_additional_content', array('quizsbsid' => $quizsbsobj->get_quizsbsid())));
        $context->form = $connectcontentform->render();
        $this->page->requires->js_call_amd('mod_quizsbs/connect', 'init');
        $this->page->requires->strings_for_js(array(
            'approved',
            'changessuccessfulsave'
        ), 'mod_quizsbs');
        $context->cmid = $quizsbsobj->get_cmid();
        $context->pagetitile = get_string('connectcontent', 'quizsbs', $page);
        return $this->render_from_template('mod_quizsbs/connectcontent', $context);
    }

}

