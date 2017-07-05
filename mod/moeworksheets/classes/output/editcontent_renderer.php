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

defined('MOODLE_INTERNAL') || die();

use mod_moeworksheets\structure;
use mod_moeworksheets\form\content_load;
use mod_moeworksheets\local\additional_content;
use mod_moeworksheets\local\question_content;

require_once("$CFG->libdir/formslib.php");
/**
 *
 * @author avi
 *
 */
class editcontent_renderer extends \plugin_renderer_base {


    /**
     *
     * @param \moeworksheets $moeworksheetsobj
     * @param structure $structure
     * @param \question_edit_contexts $contexts
     * @param \moodle_url $pageurl
     * @param array $pagevars
     * @return string|boolean
     */
    public function editcontent_page(\moeworksheets $moeworksheetsobj, structure $structure,
            \question_edit_contexts $contexts, \moodle_url $pageurl, array $pagevars, additional_content $additionalcontent) {

        $context = new \stdClass();
        $context->content_form = $this->content_load_form($pageurl, $structure, $additionalcontent);
        return $this->render_from_template('mod_moeworksheets/editcontent', $context);
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
        $additaionalcontent = new \stdClass();
        $additaionalcontent->id = $additaional->get_id();
        $additaionalcontent->additionalcontentname = $additaional->get_name();
        $additaionalcontent->contenttype = $additaional->get_type();
        $additaionalcontent->createdate = $additaional->get_createdate();
        $htmleditoroption = array(
            'subdirs' => file_area_contains_subdirs($context, 'mod_moeworksheets', 'content', null),
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
        if (! is_null($additaional->get_id())) {
            $questioncontents = $DB->get_records('moeworksheets_additionalcont', array(
                'additionalcontentid' => $additaional->get_id()
            ));
            foreach ($questioncontents as $questioncontent) {
                switch ($questioncontent->type) {
                    case question_content::HTML_CONTENT:
                        $additaionalcontent->html = $questioncontent->content;
                        $additaionalcontent->htmlformat = FORMAT_HTML;
                        $htmleditoroption['subdirs'] = file_area_contains_subdirs($context, 'mod_moeworksheets', 'content', $questioncontent->id);
                        $additaionalcontent = file_prepare_standard_editor($additaionalcontent, 'html', $htmleditoroption, $context, 'mod_moeworksheets', 'content', $questioncontent->id);
                        $questioncontenthtml->set_id($questioncontent->id);
                        break;
                    case question_content::CSS_CONTENT:
                        $additaionalcontent->csseditor = $questioncontent->content;
                        $questioncontentcss->set_id($questioncontent->id);
                        break;
                    case question_content::APP_CONTENT:
                        $draftitemid = file_get_submitted_draft_itemid('app');
                        file_prepare_draft_area($draftitemid, $context->id, 'mod_moeworksheets', 'app', $additaional->get_id(), $appoption);
                        $questioncontentapp->set_id($questioncontent->id);
                        $additaionalcontent->app = $draftitemid;
                    case question_content::JAVASCRIPT_CONTENT:
                    default:
                        $additaionalcontent->javascripteditor = $questioncontent->content;
                        $questioncontentjavascript->set_id($questioncontent->id);
                    break;
                }
            }
            $contentloadform = new content_load($pageurl, array(
                'structure' => $structure,
                'additional' => $additaional,
                'htmleditoroption' => $htmleditoroption,
                'appoption' => $appoption,
            ));
            $contentloadform->set_data($additaionalcontent);
        }
        if(!isset($contentloadform) || !($contentloadform instanceof content_load)) {
            $contentloadform = new content_load($pageurl, array(
                'structure' => $structure,
                'additional' => $additaional,
                'htmleditoroption' => $htmleditoroption,
                'appoption' => $appoption,
            ));
        }
        if ($contentloadform->is_cancelled()){
            redirect(new \moodle_url('/mod/moeworksheets/additionalcontentlist.php', array(
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
            $additionalcontent->set_moeworksheetsid($structure->get_moeworksheetsid());
            $additionalcontent->add_entry();
            file_save_draft_area_files($contentdata->app, $context->id, 'mod_moeworksheets', 'app', $additionalcontent->get_id(), $appoption);
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'mod_moeworksheets', 'app', $additionalcontent->get_id(),
                'sortorder DESC, id ASC', false);
            $file = reset($files);
            unset($files);
            if ($file) {
                $filename = $file->get_filename();
                $url = \moodle_url::make_file_url('/pluginfile.php', '/' .$file->get_contextid() . '/mod_moeworksheets/app/' .
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
                        $questioncontentcss->set_id($DB->get_field('moeworksheets_additionalcont', 'id', array(
                            'additionalcontentid' => $additionalcontent->get_id(),
                            'type' => question_content::CSS_CONTENT,
                        )));
                        $questioncontentcss->load_from_db();
                        $questioncontentcss->set_type(question_content::CSS_CONTENT);
                        $questioncontentcss->set_content($contentdata->csseditor);
                        $questioncontentcss->set_additionalcontentid($additionalcontent->get_id());
                        $questioncontentcss->add_entry();
                        $questioncontentjavascript->set_id($DB->get_field('moeworksheets_additionalcont', 'id', array(
                            'additionalcontentid' => $additionalcontent->get_id(),
                            'type' => question_content::JAVASCRIPT_CONTENT,
                        )));
                        $questioncontentjavascript->load_from_db();
                        $questioncontentjavascript->set_type(question_content::JAVASCRIPT_CONTENT);
                        $questioncontentjavascript->set_content($contentdata->javascripteditor);
                        $questioncontentjavascript->set_additionalcontentid($additionalcontent->get_id());
                        $questioncontentjavascript->add_entry();
                    case 0:
                        $questioncontenthtml->set_id($DB->get_field('moeworksheets_additionalcont', 'id', array(
                        'additionalcontentid' => $additionalcontent->get_id(),
                        'type' => question_content::HTML_CONTENT,
                        )));
                        $questioncontenthtml->load_from_db();
                        $questioncontenthtml->set_type(question_content::HTML_CONTENT);
                        $contentdata = file_postupdate_standard_editor($contentdata, 'html', $htmleditoroption, $context, 'mod_moeworksheets', 'content', $questioncontenthtml->get_id());
                        $questioncontenthtml->set_content($contentdata->html);
                        $questioncontenthtml->set_additionalcontentid($additionalcontent->get_id());
                        $questioncontenthtml->add_entry();
                        break;
                }
            }
            if($contentdata->savenshow == 0) {
                 redirect(new \moodle_url('/mod/moeworksheets/additionalcontentlist.php', array(
                        'cmid' => $pageurl->get_param('cmid'),
                        )), get_string('subjectsuccessfulsave', 'moeworksheets'),
                        null, \core\output\notification::NOTIFY_SUCCESS);
            } else {
                $modid = $DB->get_field('modules', 'id', array("name" => "moeworksheets"));
                $cmid = $DB->get_field('course_modules', 'id', array('module' => $modid, 'instance' => $additionalcontent->get_moeworksheetsid()));
                $this->page->requires->js_call_amd('mod_moeworksheets/contentpreview', 'init', array($cmid, $CFG->wwwroot, $additaionalcontent->id, $USER->sesskey));
                return \html_writer::div($contentloadform->render(), 'contentloadformforpopup');
            }
        }
        $this->page->requires->js_call_amd('mod_moeworksheets/contentpreview', 'init', array(null, null, null, null));
        return \html_writer::div($contentloadform->render(), 'contentloadformforpopup');
    }
}

