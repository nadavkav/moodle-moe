<?php
use local_remote_backup_provider\publisher;

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
defined('MOODLE_INTERNAL') || die();

/**
 * Event observer.
 * send update to all subscribers about the course change
 *
 * @package    local_remote_backup_provider
 * @copyright  2017 SysBind LTD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_remote_backup_provider_observer {


    /**
     * notify to all subsribers about the chenges in course.
     *
     * @param \core\event\base $event
     */
    public static function send_update(\core\event\base $event) {
        global $DB;

        $pub = new publisher();
        $type       = $event->crud;

        //url strings parts
        $prefixurl  = '/webservice/rest/server.php?wstoken=';
        $postfixurl = '&wsfunction=block_import_remote_course_update&moodlewsrestformat=json';
        $local_event = $event->get_data();
        //course info to update
        $local_course = $DB->get_record('course', array('id' => $local_event['courseid']));

        //check that the course have category with tag
        $sql = 'select * from {course} as C inner join {course_categories} as CA on C.category = CA.id where C.id=:id';
        if($DB->get_record_sql($sql, array('id' => $local_course->id)) == false){
            return ;
        }

        $params       = array(
            'type'        => $type,
            'course_id'   => $local_course->id,
            'course_tag'  => $local_course->idnumber,
            'course_name' => $local_course->fullname
        );
        $skipcertverify = (get_config('local_remote_backup_provider', 'selfsignssl')) ? true : false;
        if ($skipcertverify){
            $options['curlopt_ssl_verifypeer'] = false;
            $options['curlopt_ssl_verifyhost'] = false;
        }
        foreach ($pub->get_all_subscribers() as $sub){
            //subscriber info
            $token        = $sub->remote_token;
            $remotesite   = $sub->base_url;

            $local_params = $params;
            $local_params ['username'] =  $sub->remote_user;

            $url = $remotesite . $prefixurl . $token . $postfixurl;

            $curl = new curl();
            $resp = json_decode($curl->post($url, $local_params, $options));
        }
    }

}
