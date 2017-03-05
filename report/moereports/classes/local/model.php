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

namespace report_moereport\local;

/**
 *
 * @author avi
 *
 */
abstract class model {

    protected $table;
    protected $id;
    /**
     */
    public function __construct(int $id = null, string $table = '') {
        $this->id = $id;
        $this->set_table($table);
    }

    public function get_table():string {
        return $this->table;
    }

    public function get_id() {
        return (isset($this->id)) ? $this->id : null;
    }

    public function set_id($id) {
        $this->id = is_numeric($id) ? $id : null;
    }

    public function set_table($table):bool {
        global $DB;

        $dbman = $DB->get_manager();
        if ($dbman->table_exists($table)) {
            $this->table = $table;
            return true;
        }
        return false;
    }

    protected function map_to_db($record) {
        $vars = get_object_vars($this);
        foreach ($record as $key => $value) {
            if (key_exists($key, $vars)) {
                $this->{$key} = $value;
            }
        }
    }

    public function load_from_db() {
        global $DB;

        if (!empty($this->id)) {
            $vars = get_object_vars($this);
            $obj = $DB->get_record($this->get_table(), array('id' => $this->id));
            if ($obj){
                foreach ($obj as $key => $value) {
                    if (key_exists($key, $vars)) {
                        $this->{$key} = $value;
                    }
                }
            }
        }
    }

    public function to_std():\stdClass {
        $obj = new \stdClass();
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            $obj->{$key} = $value;
        }
        return $obj;
    }

    public abstract function add_entry();
}

