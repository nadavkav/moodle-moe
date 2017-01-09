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

namespace mod_quizsbs\local;

/**
 *
 * @author avi
 *
 */
class subject extends model {

    protected $name;
    protected $quizsbsid;
    /**
     */
    public function __construct(int $id = null, string $table = 'quizsbs_subject') {
        parent::__construct($id, $table);
        $this->load_from_db();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \mod_quizsbs\local\model::add_entry()
     *
     */
    public function add_entry() {
        global $DB;

        if ($this->get_id() && $DB->get_field($this->get_table(), 'id', array('id' => $this->get_id()))) {
            $DB->update_record($this->get_table(), $this->to_std());
        } else {
            $id = $DB->insert_record($this->get_table(), $this->to_std());
            if ($id) {
                $this->set_id($id);
            }
        }
    }

    /**
     * @return the $name
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * @return the $quizsbsid
     */
    public function get_quizsbsid() {
        return $this->quizsbsid;
    }

    /**
     * @param field_type $name
     */
    public function set_name($name) {
        $this->name = $name;
    }

    /**
     * @param field_type $quizsbsid
     */
    public function set_quizsbsid($quizsbsid) {
        $this->quizsbsid = $quizsbsid;
    }

    public function map_to_db($record) {
        parent::map_to_db($record);
    }

    /**
     *
     * @return NULL|\mod_quizsbs\local\additional_content[]
     */
    public function get_connected_contents() {
        global $DB;
        $additionalcontents = array();
        $contents = $DB->get_records('quizsbs_additional_content', array('subjectid' => $this->get_id()));
        foreach ($contents as $content) {
            $additionalcontents[$content->id] = new additional_content($content->id);
        }
        return (!empty($additionalcontents)) ? $additionalcontents : null;
    }

    /**
     *
     * @return NULL|\mod_quizsbs\local\additional_content[]
     */
    public function get_avilable_contents() {
        global $DB;
        $additionalcontents = array();
        $contents = $DB->get_records_select('quizsbs_additional_content', '(subjectid IS NULL OR subjectid = ?) AND quizsbsid = ?', array(
            $this->get_id(),
            $this->get_quizsbsid(),
        ));
        foreach ($contents as $content) {
            $additionalcontents[$content->id] = new additional_content($content->id);
        }
        return (!empty($additionalcontents)) ? $additionalcontents : null;
    }
}

