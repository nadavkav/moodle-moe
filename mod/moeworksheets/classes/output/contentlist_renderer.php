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


use mod_moeworksheets\structure;
use mod_moeworksheets\form\delete_content;

defined('MOODLE_INTERNAL') || die();

/**
 *
 * @author avi
 *
 */
class contentlist_renderer extends \plugin_renderer_base{

    public function contentlist_page(\moeworksheets $moeworksheetsobj, structure $structure,
            \question_edit_contexts $contexts, \moodle_url $pageurl, array $pagevars) {
        global $DB;

        $context = new \stdClass();
        $context->cmid = $moeworksheetsobj->get_cmid();
        $additionalcontants = $DB->get_records('moeworksheets_additionalcont', array('moeworksheetsid' => $moeworksheetsobj->get_moeworksheets()->id));
        foreach ($additionalcontants as $additionalcontant) {
            $content =  new \stdClass();
            $content->id = $additionalcontant->id;
            $content->name = $additionalcontant->name;
            $context->contents[] = $content;
        }
        return $this->render_from_template('mod_moeworksheets/contentlist', $context);
    }

    public function delete_content_page(\moodle_url $pageurl, \moeworksheets $moeworksheetsobj) {
        $context = new \stdClass();
        $context->deleteform = $this->delete_content_form($pageurl, $moeworksheetsobj);
        return $this->render_from_template('mod_moeworksheets/deletepage', $context);
    }

    public function delete_content_form(\moodle_url $pageurl, $moeworksheetsobj) {
        global $DB;
        $deleteform = new delete_content($pageurl);

        if ($contentdata = $deleteform->get_data()) {
            $DB->delete_records('moeworksheets_questionconten', array('additionalcontentid' => $pageurl->get_param('id')));
            $DB->delete_records('moeworksheets_additionalcont', array('id' => $pageurl->get_param('id')));
            redirect(new \moodle_url('/mod/moeworksheets/additionalcontentlist.php', array('cmid' => $pageurl->get_param('cmid'))));
        }
        return $deleteform->render();
    }
}

