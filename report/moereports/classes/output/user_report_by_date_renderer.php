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

use report_moereports\form\user_report_by_date;

/**
 *
 * @author Meir
 *
 */
class user_report_by_date_renderer extends \plugin_renderer_base {

    public function display_report($time = null) { 
    	global $DB;
        $data = new \stdClass();
        $data->form = new user_report_by_date();
        $data->form = $data->form->render();
        $options = ' where timecreated >' . $time;
        if ($time) {
        	$sqlstudents = "SELECT COUNT(*) as count FROM {user_info_data} uid join mdl_user u on uid.userid=u.id WHERE uid.fieldid = 11 and uid.data ='Yes' and u.deleted=0 AND u.id in (select DISTINCT userid FROM {logstore_standard_log} $options)";
        	$sqlteachers = "SELECT COUNT(*) as count FROM {user_info_data} uid join mdl_user u on uid.userid=u.id WHERE uid.fieldid = 11 and uid.data ='No' and u.deleted=0 AND u.id in (select DISTINCT userid FROM {logstore_standard_log} $options)";
        	
        } else {
        	$sqlstudents = "SELECT COUNT(*) as count FROM {user_info_data} uid join mdl_user u on uid.userid=u.id WHERE uid.fieldid = 11 and uid.data ='Yes' and u.deleted=0 AND u.id in (select DISTINCT userid FROM {logstore_standard_log})";
        	$sqlteachers = "SELECT COUNT(*) as count FROM {user_info_data} uid join mdl_user u on uid.userid=u.id WHERE uid.fieldid = 11 and uid.data ='No' and u.deleted=0 AND u.id in (select DISTINCT userid FROM {logstore_standard_log})";
        	
        }
        $data->students = $DB->get_field_sql($sqlstudents);
        $data->teachers = $DB->get_field_sql($sqlteachers);
        return $this->render_from_template('report_moereports/users_report_by_date', $data);
    }
}

