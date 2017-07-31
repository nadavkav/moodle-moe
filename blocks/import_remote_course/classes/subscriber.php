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

class subscriber {



    /**
    * Subscribe remote server.
    *
    * @return bool
    */
    public static function  subscribe (){
        global $CFG, $DB;

        //parameters for the remote function
        $name          = str_replace(' ', '',$DB->get_field('course', 'fullname', array('id' => 1)));
        $url           = $CFG->wwwroot;
        $local_user    = get_config('block_import_remote_course', 'localusername');
        $local_token   = get_config('block_import_remote_course', 'wstoken');;

        //parameters for the webservice url
        $prefixurl      = '/webservice/rest/server.php?wstoken=';
        $postfixurl     = '&wsfunction=local_remote_backup_provider_subscribe&moodlewsrestformat=json';
        $remotesite     = get_config('local_remote_backup_provider', 'remotesite');

        //parameters for the webservice
        $token          = get_config('local_remote_backup_provider', 'wstoken');
        $remoteusername = get_config('local_remote_backup_provider', 'remoteusername');
        $skipcertverify = (get_config('local_remote_backup_provider', 'selfsignssl')) ? true : false;

        if ($skipcertverify){
            $options['curlopt_ssl_verifypeer'] = false;
            $options['curlopt_ssl_verifyhost'] = false;
        }
        // build the curl.
        $url_to_send = $remotesite . $prefixurl . $token . $postfixurl;
        $params = array('username'=>$remoteusername, 'name'=>$name, 'url'=>$url, 'user'=>$local_user, 'token'=>$local_token );

        $curl = new curl;
        $resp = json_decode($curl->post($url_to_send, $params, $options));

        //insert the courses to DB
        $DB->delete_records('import_remote_course_list');
        $DB->insert_records('import_remote_course_list', $resp);

         return $resp;

    }

    /**
     * Subscribe remote server.
     *
     * @return bool|int true or new id
     */
    public static function unsubscrib (){
        $name = $DB->get_field('course', 'fullname', array('id' => 1));
        $url = $CFG->wwwroot;

        //TO_DO add the web server actions

    }

    public static function update($type, $course_id, $course_tag, $course_name) {
        global $DB;
        $table = 'import_remote_course_list';
        switch ($type) {
            case 'c':
                $dataobject = new stdClass();
                $dataobject->course_id   = $course_id;
                $dataobject->course_tag  = $course_tag;
                $dataobject->course_name = $course_name;
                $DB->insert_record($table, $dataobject);
                break;
            case 'd':
                $DB->delete_records($table, array(
                    'course_id'   => $course_id,
                    'course_tag'  => $course_tag,
                    'course_name' => $course_name
                ));
                break;
            case 'u':
                $id = $DB->get_field($table, 'id', array('course_id' => $course_id,));
                $dataobject = new stdClass();
                $dataobject->id          = $id;
                $dataobject->course_id   = $course_id;
                $dataobject->course_tag  = $course_tag;
                $dataobject->course_name = $course_name;
                break;
        }
        return true;

    }


}