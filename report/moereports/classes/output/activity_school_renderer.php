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
namespace report_moereports\output;

use report_moereports\local\region;

/**
 *
 * @author avi
 *
 */
class activity_school_renderer extends \plugin_renderer_base
{
    public function display_report($context){
        global $DB, $USER;

        $allcourses = $DB->get_records('course', array('enablecompletion' => '1'));
        if(is_siteadmin($USER->id) || has_capability('report/moereport:viewall', $context)){
            $regions = $DB->get_records_sql('select region from {moereports_reports} group by region');
        } else {
            if(isset($USER->profile['Yeshuyot'])){
                $regions = $DB->get_records_sql('select region from {moereports_reports} where symbol in (?) group by region',
                    array($USER->profile['Yeshuyot']));
            }
        }

        foreach ($regions as $region) {
           $region = new region($region);
           $data[$region->get_name()] = array();
        }
        $data = new stdClass();
        return $this->render_from_template('report_moereports/scool_level', $data);
    }
}

