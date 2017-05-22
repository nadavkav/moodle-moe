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
use report_moereports\local\region;
use report_moereports\local\school;

/**
 *
 * @author avi
 *
 */
class activity_school {

    public function to_std($data):\stdClass {
        $obj = new \stdClass();
        $vars = get_object_vars($data);
        foreach ($vars as $key => $value) {
            $obj->{$key} = $value;
        }
        return $obj;
    }

    public function display_report(){
        global $DB;
        ini_set('memory_limit', '8192M');
        $schools = array();
        $allcourses = $DB->get_records('course', array('enablecompletion' => '1'));


            $regions = $DB->get_records_sql('select region from {moereports_reports} group by region');
            foreach ($regions as $name) {
                $region = new region($name->region);
                $schools=array_merge($schools, $region->get_schools());
            }


        foreach ($schools as $school) {
            $region = new region($school->get_region());
            $students = $school->get_students();
            foreach ($allcourses as $course) {
                $completion = new \completion_info($course);
                $activities=  $completion->get_activities();
                if(!empty($students)){
                    $studentcomplete = $completion->get_progress_all('u.id in ('. implode(',', array_keys($students)) . ')');
                    foreach ($studentcomplete as $user) {
                        $userinfo = get_complete_user_data('id', $user->id);
                        foreach ($activities as $activity) {
                            if (array_key_exists($activity->id,$user->progress)) {
                                $thisprogress=$user->progress[$activity->id];
                                $state=$thisprogress->completionstate;
                            } else {
                                $state=COMPLETION_INCOMPLETE;
                                if(!isset($data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$userinfo->profile['StudentKita']])){
                                    $data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$userinfo->profile['StudentKita']] = 0;
                                }
                            }
                            switch($state) {
                                case COMPLETION_COMPLETE :
                                case COMPLETION_COMPLETE_PASS :
                                    if(!isset($data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$userinfo->profile['StudentKita']])){
                                        $data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$userinfo->profile['StudentKita']] = 1;
                                    } else {
                                        $data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$userinfo->profile['StudentKita']]++;
                                    }
                                    break;
                                case COMPLETION_INCOMPLETE :
                                case COMPLETION_COMPLETE_FAIL :
                                    break;
                            }
                        }
                    }
                } else {
                    foreach ($activities as $activity){
                        foreach ($school->get_levels() as $level => $value){
                            $data[$region->get_name()][$school->get_symbol()][$course->category][$activity->id][$level] = 0;
                        }
                    }
                }
            }
        }

        $rows = new \stdClass();
          foreach ($schools as $school) {
            foreach ($allcourses as $course) {
                $completion = new \completion_info($course);
                $activities=  $completion->get_activities();
                $insinfo = get_fast_modinfo($course->id);
                    foreach ($activities as $key => $activity) {
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
                                $row->{'count' . $level} = $data[$row->region][$row->symbol][$course->category][$activity->id][$level];
                            } else {
                                $row->{'count' . $level} = 0;
                            }
                            if ($value != 0) {
                                $row->{'counterprcent' . $level} = round($row->{'count' . $level} / $value * 100, 2) . '%';
                            } else {
                                $row->{'counterprcent' . $level} = get_string('noinformation', 'report_moereports');
                            }
                        }
                        $row=$this->to_std($row);
                        $rows->results[] = $row;
                    }
            }
        }

        return $rows;
    }
}

