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

use mod_moeworksheets\form\connectcontent;
use mod_moeworksheets\local\additional_content;

defined('MOODLE_INTERNAL') || die();
/**
 *
 * @author avi
 *
 */
class connectcontents_renderer extends \plugin_renderer_base {

    public function content_list_page(\moeworksheets $moeworksheetsobj, $page) {
        global $DB, $PAGE;
        $context = new \stdClass();
        $customdata = new \stdClass();
        $customdata->contents = $DB->get_records_select('moeworksheets_additionalcont', 'moeworksheetsid = ? and (subjectid is null or subjectid = ?)',
                                        array(
                                            $moeworksheetsobj->get_moeworksheetsid(),
                                            $page,
                                        ));
        $connectcontentform = new connectcontent($PAGE->url, $customdata);
        $data = new \stdClass();
        $data->contents = $DB->get_field('moeworksheets_additionalcont', 'id', array(
            'moeworksheetsid' => $moeworksheetsobj->get_moeworksheetsid(),
            'subjectid' => $page,
        ));
        $firstcontentflag = $data->contents;
        $connectcontentform->set_data($data);
        if($connectcontentform->is_submitted()) {
            $data = $connectcontentform->get_data();
            if(!empty($data)){
                if($data->contents != 0){
                    $additionalcontent = new additional_content($data->contents);
                    $oldadditionalcontent = $DB->get_record('moeworksheets_additionalcont', array(
                        'subjectid' => $page,
                        'moeworksheetsid' => $moeworksheetsobj->get_moeworksheetsid(),
                    ));
                    if (is_bool($firstcontentflag)){
                        $oldadditionalcontent = new additional_content();
                    } else {
                        $oldadditionalcontent = new additional_content($oldadditionalcontent->id);
                    }

                    $oldadditionalcontent->set_subjectid(null);
                    $oldadditionalcontent->add_entry();
                    $additionalcontent->set_subjectid($page);
                } else {
                    $additionalcontentid = $DB->get_field('moeworksheets_additionalcont', 'id', array(
                        'subjectid' => $page,
                        'moeworksheetsid' => $moeworksheetsobj->get_moeworksheetsid(),
                    ));
                    $additionalcontent = new additional_content($additionalcontentid);
                    $additionalcontent->set_subjectid(null);
                }
                $additionalcontent->add_entry();
                redirect(new \moodle_url('/mod/moeworksheets/edit.php', array(
                    'cmid' => $PAGE->url->get_param('cmid'),
                )), get_string('pagesuccessfulsave', 'moeworksheets'), null, \core\output\notification::NOTIFY_SUCCESS);
            }
            redirect(new \moodle_url('/mod/moeworksheets/edit.php', array(
                'cmid' => $PAGE->url->get_param('cmid'),
            )));
        }
        $context->content = array_values($DB->get_records('moeworksheets_additionalcont', array('moeworksheetsid' => $moeworksheetsobj->get_moeworksheetsid())));
        $context->form = $connectcontentform->render();
        $this->page->requires->js_call_amd('mod_moeworksheets/connect', 'init');
        $this->page->requires->strings_for_js(array(
            'approved',
            'changessuccessfulsave'
        ), 'mod_moeworksheets');
        $context->cmid = $moeworksheetsobj->get_cmid();
        $context->pagetitile = get_string('connectcontent', 'moeworksheets', $page);
        return $this->render_from_template('mod_moeworksheets/connectcontent', $context);
    }

}

