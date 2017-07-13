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
    protected $namespace;
    protected $namespaceid;
    protected $id;

    public function __construct($namespace, $namespaceid) {
        global $DB;
        $sql = 'select * from {notes} where ' . $DB->sql_compare_text('namespace') . ' = ? AND namespace_id = ? ';
        $rec = $DB->get_record_sql($sql, array("namespace"=>$namespace,"namespace_id"=>$namespaceid));
        if ($rec){
            $sql = 'select * from {notes_versions} where parent = ' . $rec->id . ' ORDER BY created_time DESC limit 1';
            $rec = $DB->get_record_sql($sql);
            $this->id = $rec->id;
            $this->note_content = $rec->content;

        } else {
            $rec = new \stdClass();
            $rec->namespace = $namespace;
            $rec->namespace_id = $namespaceid;
            $father = $DB->insert_record('notes', $rec);
            if ($father){
                $this->note_content = '';
                unset($rec);
                $rec = new \stdClass();
                $rec->parent = $father;
                $rec->content = '';
                $rec->created_time = time();
                $this->id = $DB->insert_record('notes_versions', $rec);
            }
        }
        $this->namespace = $namespace;
        $this->namespaceid = $namespaceid;
    }

    /**
     * @return the $mform
     */
    public function getnote() {
        $mform = new notes_form(null, array('content' => $this->note_content));
        return $mform->render();
    }

    /**
     * @return the namespace
     */
    public function getnamespace() {
        return $this->namespace;
    }

    /**
     * @return the namespace id
     */
    public function getnamespaceid() {
        return $this->namespaceid;
    }
    /**
     * @return the note content
     */
    public function getcontent() {
        return $this->note_content;
    }




}