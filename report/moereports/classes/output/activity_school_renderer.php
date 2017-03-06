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
    public function display_report($context, $region){
        global $DB, $USER;
        ini_set('memory_limit', '8192M');

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
            if(empty($region)){
                 $regions = new \stdClass();
                 $regions->name = array();
                 $regionsnames = $DB->get_records_sql('select region from {moereports_reports} group by region');
                 foreach ($regionsnames as $name) {
                     $regions->name[] = $name->region;
                 }
                 return $this->render_from_template('report_moereports/regionslist', $regions);
            }else {
                $region = new region($region);
                $schools = $region->get_schools();
            }
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
                $category = $DB->get_field('course_categories', 'name', array('id' => $course->category));
                $completion = new \completion_info($course);
                if(!empty($students)){
                    $studentcomplete = $completion->get_progress_all('u.id in ('. implode(',', array_keys($students)) . ')');
                    foreach ($studentcomplete as $comp) {
                        $userinfo = get_complete_user_data('id', $comp->id);
                        foreach ($comp->progress as $progrs) {
                            $activity = $DB->get_record_sql("SELECT cm.*, md.name as modname
                               FROM {course_modules} cm,
                                    {modules} md
                               WHERE cm.id = ? AND
                                     md.id = cm.module", array($progrs->coursemoduleid));
                            $activity = $DB->get_record($activity->modname, array('id' => $activity->instance));
                            if(!isset($data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$userinfo->profile['StudentKita']])){
                                $data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$userinfo->profile['StudentKita']] = 0;
                            } else {
                                $data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$userinfo->profile['StudentKita']]++;
                            }
                        }
                    }
                }
            }
        }
        $rows = new \stdClass();
        foreach ($schools as $school) {
            foreach ($allcourses as $course) {
                $insinfo = get_fast_modinfo($course->id);
                foreach ($insinfo->instances as $cactivity) {
                    foreach ($cactivity as $key => $activity) {
                        if($activity->completion == 0){
                            continue;
                        }
                        $row = new \stdClass();
                        $category = $DB->get_field('course_categories', 'name', array(
                            'id' => $course->category
                        ));
                        $row->region = $school->get_region();
                        $row->symbol = $school->get_symbol();
                        $row->name = $school->get_name();
                        $row->category = $category;
                        $row->activity = $activity->name;
                        foreach ($school->get_levels() as $level => $value) {
                            if (isset($data[$row->region][$row->symbol][$course->category][$key][$level])) {
                                $row->{'count' . $level} = $data[$row->region][$row->symbol][$course->category][$key][$level];
                            } else {
                                $row->{'count' . $level} = 0;
                            }
                            if ($value != 0) {
                                $row->{'counterprcent' . $level} = round($row->{'count' . $level} / $value * 100, 2) . '%';
                            } else {
                                $row->{'counterprcent' . $level} = get_string('noinformation', 'report_moereports');
                            }
                        }
                        $rows->results[] = $row;
                    }
                }
            }
        }
        return $this->render_from_template('report_moereports/scool_level', $rows);
    }
}

