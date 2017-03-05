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

use report_moereport\local\model;

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

    /**
     */
    public function __construct(int $id = null, string $table = 'moereports_reports') {
        parent::__construct($id, $table);
        if (!empty($this->get_id())) {
            $this->load_from_db();
        }
        $this->levels = array(
            '8' => 0,
            '9' => 0,
            '10' => 0,
            '11' => 0,
            '12' => 0,
        );
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

    /**
     * (non-PHPdoc)
     *
     * @see \report_moereport\local\model::add_entry()
     *
     */
    public function add_entry() {}
}

