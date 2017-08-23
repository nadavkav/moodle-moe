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

    public function content_load_form($pageurl, structure $structure, additional_content $additaional) {
        global $DB, $CFG, $USER;


        $context = \context_module::instance($structure->get_cmid());
        $questioncontenthtml = new question_content();
        $questioncontentcss = new question_content();
        $questioncontentjavascript = new question_content();
        $questioncontentapp = new question_content();
        $forminitialdata = new \stdClass();
        $forminitialdata->id = $additaional->get_id();
        $forminitialdata->additionalcontentname = $additaional->get_name();
        $forminitialdata->contenttype = $additaional->get_type();
        $forminitialdata->createdate = $additaional->get_createdate();
        $htmleditoroption = array(
            'subdirs' => file_area_contains_subdirs($context, 'mod_quizsbs', 'content', null),
            'maxbytes' => 0,
            'maxfiles' => 99,
            'changeformat' => 0,
            'context' => $context,
            'noclean' => 0,
            'trusttext' => 0,
            'enable_filemanagement' => true
        );
        $appoption = array(
            'subdirs'  => true,
            'maxbytes' => 0,
            'maxfiles' => -1,
            'mainfile'       => true,
            'accepted_types' => '*',
        );
        if (!empty($additaional->get_id())) {
            $questioncontents = $DB->get_records('quizsbs_question_content', array(
                'additionalcontentid' => $additaional->get_id()
            ));
            foreach ($questioncontents as $questioncontent) {
                switch ($questioncontent->type) {
                    case question_content::HTML_CONTENT:
                        $forminitialdata->html = $questioncontent->content;
                        $forminitialdata->htmlformat = FORMAT_HTML;
                        $htmleditoroption['subdirs'] = file_area_contains_subdirs($context, 'mod_quizsbs', 'content', $questioncontent->id);
                        $forminitialdata = file_prepare_standard_editor($forminitialdata, 'html', $htmleditoroption, $context, 'mod_quizsbs', 'content', $questioncontent->id);
                        $questioncontenthtml->set_id($questioncontent->id);
                        break;
                    case question_content::CSS_CONTENT:
                        $forminitialdata->csseditor = $questioncontent->content;
                        $questioncontentcss->set_id($questioncontent->id);
                        break;
                    case question_content::APP_CONTENT:
                        $draftitemid = file_get_submitted_draft_itemid('app');
                        file_prepare_draft_area($draftitemid, $context->id, 'mod_quizsbs', 'app', $additaional->get_id(), $appoption);
                        $questioncontentapp->set_id($questioncontent->id);
                        $forminitialdata->app = $draftitemid;
                    case question_content::JAVASCRIPT_CONTENT:
                    default:
                        $forminitialdata->javascripteditor = $questioncontent->content;
                        $questioncontentjavascript->set_id($questioncontent->id);
                    break;
                }
            }
        }
        $contentloadform = new content_load($pageurl, array(
                'structure' => $structure,
                'additional' => $additaional,
                'htmleditoroption' => $htmleditoroption,
                'appoption' => $appoption,
            ));
        if(!empty($additaional->get_id())){
            $contentloadform->set_data($forminitialdata);
        }
        if ($contentloadform->is_cancelled()){
            redirect(new \moodle_url('/mod/quizsbs/additionalcontentlist.php', array(
                'cmid' => $pageurl->get_param('cmid'),
            )));
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
            file_save_draft_area_files($contentdata->app, $context->id, 'mod_quizsbs', 'app', $additionalcontent->get_id(), $appoption);
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'mod_quizsbs', 'app', $additionalcontent->get_id(),
                'sortorder DESC, id ASC', false);
            $file = reset($files);
            unset($files);
            if ($file) {
                $filename = $file->get_filename();
                $url = \moodle_url::make_file_url('/pluginfile.php', '/' .$file->get_contextid() . '/mod_quizsbs/app/' .
                    $file->get_itemid() . $file->get_filepath() . $filename);
            }
            if ($additionalcontent->get_id()) {
                switch ($additionalcontent->get_type()) {
                    case 1:
                    case 2:
                        if (!empty($file)){
                            $questioncontentapp->set_content($url->out_as_local_url());
                            $questioncontentapp->set_additionalcontentid($additionalcontent->get_id());
                            $questioncontentapp->set_type(question_content::APP_CONTENT);
                            $questioncontentapp->add_entry();
                            break;
                        }
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
                        $questioncontenthtml->set_id($DB->get_field('quizsbs_question_content', 'id', array(
                        'additionalcontentid' => $additionalcontent->get_id(),
                        'type' => question_content::HTML_CONTENT,
                        )));
                        $questioncontenthtml->load_from_db();
                        $questioncontenthtml->set_type(question_content::HTML_CONTENT);
                        $questioncontenthtml->set_content($contentdata->html_editor['text']);
                        $questioncontenthtml->set_additionalcontentid($additionalcontent->get_id());
                        $questioncontenthtml->add_entry();
                        $contentdata = file_postupdate_standard_editor($contentdata, 'html', $htmleditoroption, $context, 'mod_quizsbs', 'content', $questioncontenthtml->get_id());
                        $questioncontenthtml->set_content($contentdata->html);
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
                $this->page->requires->js_call_amd('mod_quizsbs/contentpreview', 'init', array($cmid, $CFG->wwwroot, $forminitialdata->id, $USER->sesskey));
                return \html_writer::div($contentloadform->render(), 'contentloadformforpopup');
            }
        }
        $this->page->requires->js_call_amd('mod_quizsbs/contentpreview', 'init', array(null, null, null, null));
        return \html_writer::div($contentloadform->render(), 'contentloadformforpopup');
    }
}

