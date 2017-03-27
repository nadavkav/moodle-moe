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

class block_links_for_moetopcoll extends block_base {
    public function init() {
        $this->title = get_string('links_for_moetopcoll', 'block_links_for_moetopcoll');
    }

    public function get_content() {
        global  $PAGE, $COURSE, $USER;
        $context ="";
        $renderer = $PAGE->get_renderer('core');
        if ($this->content !== null) {
            return $this->content;
        }
        $isstudent = !has_capability('block/links_for_moetopcoll:canSeeLinks', $this->context) ? true : false;
        if (!$isstudent) {
            $this->content         = new stdClass;
            $this->content->text   = $renderer->render_from_template('block_links_for_moetopcoll/main', $context);
            return $this->content;
        }
        $this->content = new stdClass;
        $this->content->text = "";
        $this->content->footer  = "";
        return $this->content;
    }
    public function instance_allow_multiple() {
        return true;
    }
}