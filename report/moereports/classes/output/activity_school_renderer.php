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
use report_moereports\local\school;

/**
 *
 * @author avi
 *
 */
class activity_school_renderer extends \plugin_renderer_base
{
    public function display_report($context){
        global $DB, $USER;

        $schoollevelaccess = $DB->get_field('config', 'value', array('name' => 'schools_level_access'));
        $schoollevelaccess = explode(',', $schoollevelaccess);
        $schools = array();
        if (!isset($USER->profile['Yeshuyot']) || !isset($USER->profile['SimpleRole'])){
            return ;
        }
        $roles = explode(',', $USER->profile['SimpleRole']);
        $mosdot = explode(',', $USER->profile['Yeshuyot']);
        $rolesinschools = array();
        foreach ($roles as $key => $role) {
            $rolesinschools[$role][] = $mosdot[$key];
        }
        $allcourses = $DB->get_records('course', array('enablecompletion' => '1'));
        if(is_siteadmin($USER->id) || has_capability('report/moereport:viewall', $context)){
            $regions = $DB->get_records_sql('select region from {moereports_reports} group by region');
        } else {
            foreach ($rolesinschools as $role => $symboles) {
                if(in_array($role, $schoollevelaccess)){
                    foreach ($symboles as $symbole){
                        $schools[] = new school($symbole);
                    }
                }
            }
        }

        foreach ($schools as $school) {
            $region = new region($school->get_region());
            $students = $school->get_students();
            foreach ($allcourses as $course) {
                $category = $DB->get_field('course_categories', 'name', array('id' => $course->id));
                $completion = new \completion_info($course);
                foreach ($students as $student) {
                    $studentcomplete = $completion->get_completions($student);
                }
                $data[$region->get_name()][$school->get_symbol()][$school->get_name()][$category][] = 0;
            }



        }
        $data = new \stdClass();
        return $this->render_from_template('report_moereports/scool_level', $data);
    }
}

