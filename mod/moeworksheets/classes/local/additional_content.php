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
class additional_content extends model{

    const HTML_CONTENT_TYPE = 0;
    const SCORM_CONTENT_TYPE = 1;
    const HTML_APPLICATION_TYPE = 2;


    protected $id;
    protected $name;
    protected $moeworksheetsid;
    protected $createdate;
    protected $modifieddate;
    protected $type;
    protected $subjectid;

    /**
     */
    public function __construct($id = null, $name = '', $moeworksheetsid = null, $createdate = null, $modifiedid = null,
                                    $type = additional_content::HTML_CONTENT_TYPE, $table = 'moeworksheets_additionalcont') {
        global $DB;
        $this->set_table($table);
        $this->set_id($id);
        $this->load_from_db();
        $this->name = (empty($this->name)) ? $name : $this->name;
        $this->moeworksheetsid = (empty($this->moeworksheetsid)) ? $moeworksheetsid : $this->moeworksheetsid;
        $this->createdate = (empty($this->createdate)) ? $createdate : $this->createdate;
        $this->modifieddate = (empty($this->modifieddate)) ? $modifiedid : $this->modifieddate;
        $this->type = (empty($this->type)) ? $type : $this->type;
    }

    /**
     *
     * @return the $id
     */
    public function get_id() {
        return $this->id;
    }

    /**
     *
     * @return the $name
     */
    public function get_name() {
        return $this->name;
    }

    /**
     *
     * @return the $moeworksheetsid
     */
    public function get_moeworksheetsid() {
        return $this->moeworksheetsid;
    }

    /**
     *
     * @return the $createdate
     */
    public function get_createdate() {
        return $this->createdate;
    }

    /**
     *
     * @return the $modifieddate
     */
    public function get_modifieddate() {
        return $this->modifieddate;
    }

    /**
     *
     * @return the $type
     */
    public function get_type() {
        return $this->type;
    }

    /**
     *
     * @param field_type $id
     */
    public function set_id($id) {
        $this->id = is_numeric($id) ? $id : null;
    }

    /**
     *
     * @param field_type $name
     */
    public function set_name($name) {
        $this->name = $name;
    }

    /**
     *
     * @param field_type $moeworksheetsid
     */
    public function set_moeworksheetsid($moeworksheetsid) {
        $this->moeworksheetsid = $moeworksheetsid;
    }

    /**
     *
     * @param field_type $createdate
     */
    public function set_createdate($createdate) {
        $this->createdate = $createdate;
    }

    /**
     *
     * @param int $modifieddate
     */
    public function set_modifieddate(int $modifieddate) {
        $this->modifieddate = $modifieddate;
    }

    /**
     *
     * @param int $type
     */
    public function set_type(int $type) {
        $this->type = $type;
    }

    /**
     * @return the $subjectid
     */
    public function get_subjectid()
    {
        return $this->subjectid;
    }

    /**
     * @param field_type $subjectid
     */
    public function set_subjectid($subjectid)
    {
        $this->subjectid = $subjectid;
    }

    /**
     *
     */
    public function add_entry() {
        global $DB;

        $additionalcontent = $DB->get_record('moeworksheets_additionalcont', array('id' => $this->id));
        if ($additionalcontent) {
            $this->set_modifieddate(time());
            $DB->update_record('moeworksheets_additionalcont', $this->to_std());
        } else {
            $this->set_createdate(time());
            $this->set_modifieddate($this->get_createdate());
            $id = $DB->insert_record('moeworksheets_additionalcont', $this->to_std());
            if ($id) {
                $this->set_id($id);
            }
        }
    }

    public function get_additional_content_record(int $id = null) {
        global $DB;

        if (!is_null($id)) {
            $record = $DB->get_record('moeworksheets_additionalcont', array('id' => $id));
        }
        if ($record) {
            $this->map_to_db($record);
        }
    }

    public function get_connected_contents() {
        global $DB;

        $contents = array();
        $dbcontents = $DB->get_records('moeworksheets_additionalcont', array(
            'additionalcontentid' => $this->get_id(),
        ));
        foreach ($dbcontents as $content) {
            $contents[$content->type] = new question_content($content->id);
        }
        return $contents;
    }
}

