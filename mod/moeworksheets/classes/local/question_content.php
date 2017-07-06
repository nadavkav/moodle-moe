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

namespace mod_moeworksheets\local;

require_once(__DIR__ . '/../../../../config.php');
/**
 *
 * @author avi
 *
 */
class question_content extends model{

    /**
     * Used for HTML content from Atto Editor
     * @var integer
     */
    const HTML_CONTENT = 0;

    /**
     * Used for scorm package content
     * @var integer
     */
    const APP_CONTENT = 1;

    /**
     * Used javascript content
     * @var integer
     */
    const JAVASCRIPT_CONTENT = 2;

    /**
     * Used for css content
     * @var integer
     */
    const CSS_CONTENT = 3;

    protected $id;
    protected $content;
    protected $type;
    protected $additionalcontentid;

    public function __construct($id = null, $table = 'moeworksheets_questionconten') {
        $this->set_id($id);
        $this->set_table($table);
        if(!is_null($this->get_id())) {
            $this->load_from_db();
        }
    }
    /**
     * @return the $id
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * @return the $content
     */
    public function get_content() {
        return $this->content;
    }

    /**
     * @return the $type
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * @return the $moeworksheetsid
     */
    public function get_additionalcontentid() {
        return $this->additionalcontentid;
    }

    /**
     * @param string $id
     */
    public function set_id($id) {
            $this->id = is_numeric($id) ? $id : null;
    }

    /**
     * @param string $content
     */
    public function set_content($content) {
        $this->content = $content;
    }

    /**
     * @param field_type $type
     */
    public function set_type($type) {
        $this->type = $type;
    }

    /**
     * @param string $moeworksheetsid
     */
    public function set_additionalcontentid($additionalcontentid) {
        $this->additionalcontentid = $additionalcontentid;
    }

    public function get_content_by_id($id = null) {
        global $DB;

        if (is_null($id)) {
            return false;
        }
        $content = $DB->get_record('moeworksheets_additionalcont', array('id' => $id));
        if ($content) {
            $this->id = $content->id;
            $this->content = $content->content;
            $this->type = $content->type;
            $this->additionalcontentid = $content->additionalcontentid;
            return $this;
        } else {
            return false;
        }
    }

    public function add_entry() {
        global $DB;

        $questioncontent = $DB->get_record($this->get_table(), array('id' => $this->get_id()));
        if ($questioncontent) {
            $DB->update_record($this->get_table(), $this->to_std());
        } else {
            $id = $DB->insert_record($this->get_table(), $this->to_std());
            if ($id) {
                $this->set_id($id);
            }
        }
    }

    public function get_question_content_record(int $id = null) {
        global $DB;

        if (!is_null($id)) {
            $record = $DB->get_record('moeworksheets_additionalcont', array('id' => $id));
        }
        if ($record) {
            $this->map_to_db($record);
        }
    }
}