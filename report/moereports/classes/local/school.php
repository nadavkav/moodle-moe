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
namespace report_moereports\local;

defined('MOODLE_INTERNAL') || die();


/**
 *
 * @author avi
 *
 */
class school extends model
{

    protected $symbol;
    protected $region;
    protected $name;
    protected $levels;
    protected $isstudentfieldid;
    protected $studentmosadid;


    /**
     */
    public function __construct(int $symbol = null, int $id = null, string $table = 'moereports_reports') {
        global $DB;

        parent::__construct($id, $table);
        $this->isstudentfieldid = $DB->get_field('user_info_field', 'id', array('shortname' => 'IsStudent'));
        $this->studentmosadid = $DB->get_field('user_info_field', 'id', array('shortname' => 'StudentMosad'));
        $this->levels = array(
            '8' => 0,
            '9' => 0,
            '10' => 0,
        );
        if (!empty($symbol)) {
            $this->set_symbol($symbol);
        }
        if (!empty($this->get_symbol())) {
            $this->load_from_db();
            $this->update_levels_from_db();
        }
    }

    /**
     * @return the $symbol
     */
    public function get_symbol() {
        return $this->symbol;
    }

    /**
     * @return the $region
     */
    public function get_region() {
        return $this->region;
    }

    /**
     * @return the $name
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * @param int $symbol
     */
    public function set_symbol(int $symbol) {
        $this->symbol = is_numeric($symbol) ? $symbol : null;
    }

    /**
     * @param string $region
     */
    public function set_region(string $region) {
        $this->region = $region;
    }

    public function get_levels() {
        return $this->levels;
    }

    /**
     * @param field_type $name
     */
    public function set_name($name) {
        $this->name = $name;
    }

    public function update_levels_from_db() {
        global $DB;
        $levels = $DB->get_records('moereports_reports_classes', array('symbol' => $this->get_symbol()));
        foreach ($levels as $level) {
            $this->levels[$level->class] = $level->studentsnumber;
        }
    }

    public function load_from_db() {
        global $DB;

        if (!empty($this->symbol)) {
            $vars = get_object_vars($this);
            $obj = $DB->get_record($this->get_table(), array('symbol' => $this->symbol));
            if ($obj) {
                foreach ($obj as $key => $value) {
                    if (key_exists($key, $vars)) {
                        $this->{$key} = $value;
                    }
                }
            }
        }
    }

    public function get_students() {
        global $DB;
        $users = $DB->get_records_sql("select u.id as id from {user} u join {user_info_data}
        uid on u.id=uid.userid where uid.fieldid=:studentmosad and uid.data=:mosad and u.id in
        (select u.id from {user} u join {user_info_data} uid on u.id=uid.userid where uid.fieldid=:isstudent and uid.data='Yes')",
            array(
                'isstudent' => $this->isstudentfieldid,
                'studentmosad' => $this->studentmosadid,
                'mosad' => $this->get_symbol(),
            ));
        return $users;
    }
    /**
     * (non-PHPdoc)
     *
     * @see \report_moereports\local\model::add_entry()
     *
     */
    public function add_entry() {
    }
}

