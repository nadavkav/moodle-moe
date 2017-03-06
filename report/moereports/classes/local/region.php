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
class region
{
    protected $name;
    protected $schools;

    public function __construct($name) {
        $this->schools = array();
        if (!empty($name)) {
            global $DB;
            $this->set_name($name);
            $schools = $DB->get_records('moereports_reports', array('region' => $name), '', 'symbol, name');
        }
    }

    public function set_name($name) {
        global $DB;

        $this->name = $DB->get_field('moereports_reports', 'region', array('region' => $name), IGNORE_MULTIPLE);
    }

    public function set_schools($schools) {
        if (is_array($schools)) {
            $this->schools = $schools;
        } else {
            throw new \coding_exception('Schools must be array');
        }
    }

    public function get_name() {
        return $this->name;
    }

    public function get_schools() {
        return $this->schools;
    }

    public static function get_name_by_scool_symbol($symbol) {
        global $DB;
        return $DB->get_field('moereports_reports', 'region', array("symbol" => $symbol));
    }

}

