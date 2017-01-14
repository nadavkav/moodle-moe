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
/**
 *
 * @author avi
 *
 */
class connectcontents_renderer extends \plugin_renderer_base {

    public function content_list_page(\quizsbs $quizsbsobj, $id = null) {
        global $DB;
        $context = new \stdClass();
        $context->content = array_values($DB->get_records('quizsbs_additional_content', array('quizsbsid' => $quizsbsobj->get_quizsbsid())));
        $this->page->requires->js_call_amd('mod_quizsbs/connect', 'init');
        $this->page->requires->strings_for_js(array(
            'approved',
            'changessuccessfulsave'
        ), 'mod_quizsbs');
        $context->cmid = $quizsbsobj->get_cmid();
        return $this->render_from_template('mod_quizsbs/connectcontent', $context);
    }

}

