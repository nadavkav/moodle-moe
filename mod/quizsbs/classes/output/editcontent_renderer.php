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

defined('MOODLE_INTERNAL') || die();

use mod_quizsbs\structure;
use mod_quizsbs\form\content_load;
use mod_quizsbs\local\additional_content;
use mod_quizsbs\local\question_content;

require_once("$CFG->libdir/formslib.php");
/**
 *
 * @author avi
 *
 */
class editcontent_renderer extends \plugin_renderer_base {

    public function editcontent_page(\quizsbs $quizsbsobj, structure $structure,
            \question_edit_contexts $contexts, \moodle_url $pageurl, array $pagevars) {
        global $DB;

        $context = new \stdClass();
        $context->content_form = $this->content_load_form($pageurl, $structure);
        return $this->render_from_template('mod_quizsbs/editcontent', $context);
    }

    /**
     * retuen html content for content load form
     *
     * @param unknown $pageurl
     * @param unknown $contexts
     * @param unknown $pagevars
     *
     * @return string
     */

    public function content_load_form($pageurl, $structure) {
        global $DB;

        $contentloadform = new content_load($pageurl, $structure);

        if ($contentdata = $contentloadform->get_data()) {
            $additionalcontent = new additional_content('quizsbs_additional_content');
            $additionalcontent->set_name($contentdata->additionalcontentname);
            $additionalcontent->set_type($contentdata->contenttype);
            $additionalcontent->set_quizsbsid($structure->get_quizsbsid());
            $additionalcontent->add_entry();
            if ($additionalcontent->get_id()) {
                $questioncontent = new question_content('quizsbs_question_content');
                switch ($additionalcontent->get_type()) {
                    case 2:
                        $questioncontent->set_type(question_content::CSS_CONTENT);
                        $questioncontent->set_content($contentdata->csseditor);
                        $questioncontent->set_additionalcontentid($additionalcontent->get_id());
                        $questioncontent->add_entry();
                        $questioncontent->set_type(question_content::JAVASCRIPT_CONTENT);
                        $questioncontent->set_content($contentdata->javascripteditor);
                        $questioncontent->set_additionalcontentid($additionalcontent->get_id());
                        $questioncontent->add_entry();
                    case 0:
                    default:
                        $questioncontent->set_type(question_content::HTML_CONTENT);
                        $questioncontent->set_content($contentdata->htmleditor['text']);
                        $questioncontent->set_additionalcontentid($additionalcontent->get_id());
                        $questioncontent->add_entry();
                        break;
                }
            }
            foreach ($contentdata->contentquestion as $questionid) {
                $DB->set_field('quizsbs_slots', 'additionalcontentid', $additionalcontent->get_id(), array('questionid' => $questionid));
            }
            redirect($pageurl);
        }
        return \html_writer::div($contentloadform->render(), 'contentloadformforpopup');
    }
}

