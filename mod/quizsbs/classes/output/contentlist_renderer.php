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


use mod_quizsbs\structure;
use mod_quizsbs\form\delete_content;

defined('MOODLE_INTERNAL') || die();

/**
 *
 * @author avi
 *
 */
class contentlist_renderer extends \plugin_renderer_base{

    public function contentlist_page(\quizsbs $quizsbsobj, structure $structure,
            \question_edit_contexts $contexts, \moodle_url $pageurl, array $pagevars) {
        global $DB;

        $context = new \stdClass();
        $context->cmid = $quizsbsobj->get_cmid();
        $additionalcontants = $DB->get_records('quizsbs_additional_content', array('quizsbsid' => $quizsbsobj->get_quizsbs()->id));
        foreach ($additionalcontants as $additionalcontant) {
            $content =  new \stdClass();
            $content->id = $additionalcontant->id;
            $content->name = $additionalcontant->name;
            $context->contents[] = $content;
        }
        return $this->render_from_template('mod_quizsbs/contentlist', $context);
    }

    public function delete_content_page(\moodle_url $pageurl, \quizsbs $quizsbsobj) {
        $context = new \stdClass();
        $context->deleteform = $this->delete_content_form($pageurl, $quizsbsobj);
        return $this->render_from_template('mod_quizsbs/deletepage', $context);
    }

    public function delete_content_form(\moodle_url $pageurl, $quizsbsobj) {
        global $DB;
        $deleteform = new delete_content($pageurl);

        if ($contentdata = $deleteform->get_data()) {
            $DB->delete_records('quizsbs_question_content', array('additionalcontentid' => $pageurl->get_param('additionalcontentid')));
            $DB->delete_records('quizsbs_additional_content', array('id' => $pageurl->get_param('additionalcontentid')));
            redirect(new \moodle_url('/mod/quizsbs/additionalcontentlist.php', array('cmid' => $pageurl->get_param('cmid'))));
        }
        return $deleteform->render();
    }
}

