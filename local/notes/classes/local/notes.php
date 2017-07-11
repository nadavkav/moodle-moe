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
namespace local_notes\local;
use local_notes\local\notes_form;

defined('MOODLE_INTERNAL') || die();

class notes {

    protected $note_content;
    protected $id;

    public function __construct($namespace, $id) {
        global $DB;
        $sql = 'select * from {notes} where ' . $DB->sql_compare_text('namespace') . ' = ? AND namespace_id = ?';
        $rec = $DB->get_record_sql($sql, array("namespace"=>$namespace,"namespace_id"=>$id));
        if ($rec){
            $note_content = $DB->get_field('notes_versions','content', array("parent"=>$rec->id));
            $this->id = $rec->id;
        } else {
            $rec = new \stdClass();
            $rec->namespace = $namespace;
            $rec->namespace_id = $id;
            $rec = $DB->insert_record('notes', $rec);
            if ($rec){
                $this->id = $rec;
            }
        }

    }

    /**
     * @return the $mform
     */
    public function getMform()
    {
        $mform = new notes_form();
        if (isset($this->note_content)){
            $mform->set_data(array('content' => $this->note_content));
        }
        return $mform;
    }




}