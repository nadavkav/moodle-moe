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

    /**
     *
     * @param \quizsbs $quizsbsobj
     * @param structure $structure
     * @param \question_edit_contexts $contexts
     * @param \moodle_url $pageurl
     * @param array $pagevars
     * @return string|boolean
     */
    public function editcontent_page(\quizsbs $quizsbsobj, structure $structure,
            \question_edit_contexts $contexts, \moodle_url $pageurl, array $pagevars, additional_content $additionalcontent) {

        $context = new \stdClass();
        $context->content_form = $this->content_load_form($pageurl, $structure, $additionalcontent);
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

    public function content_load_form($pageurl, $structure, additional_content $additaional) {
        global $DB, $CFG, $USER;

        $contentloadform = new content_load($pageurl, array(
            'structure' => $structure,
            'additional' => $additaional,
        ));
        $questioncontenthtml = new question_content();
        $questioncontentcss = new question_content();
        $questioncontentjavascript = new question_content();
        $additaionalcontent = new \stdClass();
        $additaionalcontent->id = $additaional->get_id();
        $additaionalcontent->additionalcontentname = $additaional->get_name();
        $additaionalcontent->contenttype = $additaional->get_type();
        $additaionalcontent->createdate = $additaional->get_createdate();
        if (! is_null($additaional->get_id())) {
            $questioncontents = $DB->get_records('quizsbs_question_content', array(
                'additionalcontentid' => $additaional->get_id()
            ));
            foreach ($questioncontents as $questioncontent) {
                switch ($questioncontent->type) {
                    case question_content::HTML_CONTENT:
                        $additaionalcontent->htmleditor['text'] = $questioncontent->content;
                        $questioncontenthtml->set_id($questioncontent->id);
                        break;
                    case question_content::CSS_CONTENT:
                        $additaionalcontent->csseditor = $questioncontent->content;
                        $questioncontentcss->set_id($questioncontent->id);
                        break;
                    case question_content::JAVASCRIPT_CONTENT:
                    default:
                        $additaionalcontent->javascripteditor = $questioncontent->content;
                        $questioncontentjavascript->set_id($questioncontent->id);
                    break;
                }
            }
            $contentloadform->set_data($additaionalcontent);
        }
        if ($contentdata = $contentloadform->get_data()) {
            $additionalcontent = new additional_content($contentdata->id);
            $additionalcontent->set_name($contentdata->additionalcontentname);
            $additionalcontent->set_type($contentdata->contenttype);
            if (!$additionalcontent->get_createdate()) {
                $additionalcontent->set_createdate($contentdata->createdate);
            }
            $additionalcontent->set_quizsbsid($structure->get_quizsbsid());
            $additionalcontent->add_entry();
            if ($additionalcontent->get_id()) {
                switch ($additionalcontent->get_type()) {
                    case 2:
                        $questioncontentcss->set_id($DB->get_field('quizsbs_question_content', 'id', array(
                            'additionalcontentid' => $additionalcontent->get_id(),
                            'type' => question_content::CSS_CONTENT,
                        )));
                        $questioncontentcss->load_from_db();
                        $questioncontentcss->set_type(question_content::CSS_CONTENT);
                        $questioncontentcss->set_content($contentdata->csseditor);
                        $questioncontentcss->set_additionalcontentid($additionalcontent->get_id());
                        $questioncontentcss->add_entry();
                        $questioncontentjavascript->set_id($DB->get_field('quizsbs_question_content', 'id', array(
                            'additionalcontentid' => $additionalcontent->get_id(),
                            'type' => question_content::JAVASCRIPT_CONTENT,
                        )));
                        $questioncontentjavascript->load_from_db();
                        $questioncontentjavascript->set_type(question_content::JAVASCRIPT_CONTENT);
                        $questioncontentjavascript->set_content($contentdata->javascripteditor);
                        $questioncontentjavascript->set_additionalcontentid($additionalcontent->get_id());
                        $questioncontentjavascript->add_entry();
                    case 0:
                    default:
                        $questioncontenthtml->set_id($DB->get_field('quizsbs_question_content', 'id', array(
                            'additionalcontentid' => $additionalcontent->get_id(),
                            'type' => question_content::HTML_CONTENT,
                        )));
                        $questioncontenthtml->load_from_db();
                        $questioncontenthtml->set_type(question_content::HTML_CONTENT);
                        $questioncontenthtml->set_content($contentdata->htmleditor['text']);
                        $questioncontenthtml->set_additionalcontentid($additionalcontent->get_id());
                        $questioncontenthtml->add_entry();
                        break;
                }
            }              
            if($contentdata->savenshow == 0) {
                 redirect(new \moodle_url('/mod/quizsbs/additionalcontentlist.php', array(
                        'cmid' => $pageurl->get_param('cmid'),
                        )), get_string('subjectsuccessfulsave', 'quizsbs'), 
                        null, \core\output\notification::NOTIFY_SUCCESS);
            } else {
                $modid = $DB->get_field('modules', 'id', array("name" => "quizsbs"));
                $cmid = $DB->get_field('course_modules', 'id', array('module' => $modid, 'instance' => $additionalcontent->get_quizsbsid()));
                $this->page->requires->js_call_amd('mod_quizsbs/contentpreview', 'init', array($cmid, $CFG->wwwroot, $additaionalcontent->id, $USER->sesskey));
                return \html_writer::div($contentloadform->render(), 'contentloadformforpopup');
            }
        }
        $this->page->requires->js_call_amd('mod_quizsbs/contentpreview', 'init', array(null, null, null, null));
        return \html_writer::div($contentloadform->render(), 'contentloadformforpopup');
    }
}

