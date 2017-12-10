<?php
use tool_log\plugininfo\logstore;
use block_import_remote_course\local\notification_helper;

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
        $local_token   = get_config('block_import_remote_course', 'wstoken');

        //parameters for the webservice url
        $prefixurl      = '/webservice/rest/server.php?wstoken=';
        $postfixurl     = '&wsfunction=local_remote_backup_provider_subscribe&moodlewsrestformat=json';
        $remotesite     = get_config('local_remote_backup_provider', 'remotesite');

        //parameters for the webservice
        $token          = get_config('local_remote_backup_provider', 'wstoken');
        $remoteusername = get_config('local_remote_backup_provider', 'remoteusername');
        $skipcertverify = (get_config('local_remote_backup_provider', 'selfsignssl')) ? true : false;

        if ($skipcertverify){
            $options['CURLOPT_SSL_VERIFYPEER'] = false;
            $options['CURLOPT_SSL_VERIFYHOST'] = false;
        }
        // build the curl.
        $url_to_send = $remotesite . $prefixurl . $token . $postfixurl;
        $params = array('username'=>$remoteusername, 'name'=>$name, 'url'=>$url, 'user'=>$local_user, 'token'=>$local_token );

        $curl = new curl();
        $resp = json_decode($curl->post($url_to_send, $params, $options));

        //insert the courses to DB
        $DB->delete_records('import_remote_course_list');
        if (!empty($resp)){
            foreach ($resp as $remotecourse){
                $remotecourse->course_tag = trim($remotecourse->course_tag);
            }
            $DB->insert_records('import_remote_course_list', $resp);
        }

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

    public static function update($type, $course_id, $course_tag = null, $course_name = null,
    		$link_to_remote_act = null, $cm = null, $mod = null, $name = null) {
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
            	if ($DB->get_field($table, 'id', array('course_id' => $course_id,))) {
                	$DB->delete_records($table, array('course_id' => $course_id));
            	}
                break;
            case 'u':
                $id = $DB->get_field($table, 'id', array('course_id' => $course_id,));
                if ($id) {
                	//update existing course
                	$dataobject = new stdClass();
                	$dataobject->id          = $id;
                	$dataobject->course_id   = $course_id;
                	$dataobject->course_tag  = $course_tag;
                	$dataobject->course_name = $course_name;
                	$DB->update_record($table, $dataobject);
                } else {
                	//creating new course in case of moving course to cat with tag
                	$dataobject = new stdClass();
                	$dataobject->course_id   = $course_id;
                	$dataobject->course_tag  = $course_tag;
                	$dataobject->course_name = $course_name;
                	$DB->insert_record($table, $dataobject);
                }
                break;
            case 'cu':
                //course tag remove - delete course
                if ($course_tag == ''){
                    if ($DB->get_record($table, array('course_id' => $course_id))){
                        $DB->delete_records($table, array('course_id' => $course_id));
                    }
                } else {
                    //have course tag - update as need
                    $id = $DB->get_field($table, 'id', array('course_id' => $course_id,));
                    //a new tag to existing course
                    if($id){
                        $dataobject = new stdClass();
                        $dataobject->id          = $id;
                        $dataobject->course_id   = $course_id;
                        $dataobject->course_tag  = $course_tag;
                        $dataobject->course_name = $course_name;
                        $DB->update_record($table, $dataobject);
                    } else{
                        //a new course
                        $dataobject = new stdClass();
                        $dataobject->course_id   = $course_id;
                        $dataobject->course_tag  = $course_tag;
                        $dataobject->course_name = $course_name;
                        $DB->insert_record($table, $dataobject);
                    }
                }
                break;
            case 'na':
                $data = [];
                $templateid = $DB->get_field('import_remote_course_list', 'id', array(
                    'course_id' => $course_id
                ));
                $coursewithtamplayte = $DB->get_records('import_remote_course_templat',array(
                    'tamplate_id' => $templateid
                ));                
                if (notification_helper::get_record(['cm' => $cm])) {
                	return;
                }
                $dataobject = new stdClass();
            	$dataobject->linktoremoteact = $link_to_remote_act;
            	$dataobject->cm       		 = (int)$cm;
            	$dataobject->module          = $mod;
            	$dataobject->name 		     = $name;
            	$dataobject->type            = 'new';
                foreach ($coursewithtamplayte as $course) {
                    $dataobject->courseid        = (int)$course->course_id;
                    $notification = new notification_helper(0, $dataobject);
                    $notification->create();
                }
            	break;
            case 'ua':
                $data = [];
                $templateid = $DB->get_field('import_remote_course_list', 'id', array(
                    'course_id' => $course_id
                ));
                $coursewithtamplayte = $DB->get_records('import_remote_course_templat',array(
                    'tamplate_id' => $templateid
                ));
                self::delete_activity_backup($cm);
                $dataobject = new stdClass();
            	$dataobject->linktoremoteact = $link_to_remote_act;
            	$dataobject->cm       		 = (int)$cm;
            	$dataobject->module          = $mod;
            	$dataobject->name 		     = $name;
            	$dataobject->type            = 'update';
                foreach ($coursewithtamplayte as $course) {
                    $dataobject->courseid  = (int)$course->course_id;
                    $notification = new notification_helper(0, $dataobject);
                    $notification->create();
                }
                break;
            default:
                return array('result' => false);
        }
        return array('result' => true);

    }

    /**
     * remove a course from the course - template table.
     *
     * @param int $courseid -  course id.
     * @return bool true.
     * @throws dml_exception A DML specific exception is thrown for any errors.
     * */
    public function delete_course(int $courseid) {
    	global $DB;
    	$DB->delete_records('import_remote_course_templat', ['course_id' => $courseid]);
    	$DB->delete_records('import_remote_course_actdata', ['courseid' => $courseid]);
    	return true;
    }
    
    private function delete_activity_backup ($cmid) {
    	$fs = get_file_storage();
    	$context = \context_module::instance ($cmid);
    	
    	// Prepare file record object
    	$fileinfo = array(
    			'component' => 'blocks_import_remote_course',
    			'filearea'  => 'backup',     // usually = table name
    			'itemid'    => $cmid,               // usually = ID of row in table
    			'contextid' => $context->id, // ID of context
    			'filepath'  => '/',           // any path beginning and ending in /
    			'filename'  => $cmid . '.mbz'); // any filename
    	
    	// Get file
    	$file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
    			$fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
    	
    	// Delete it if it exists
    	if ($file) {
    		$file->delete();
    	}
    	return ;
    }
    
    /**
     * remove all actdata entry for specific course
     *
     * @param int $courseid -  course id.
     * @param int $type -  type of actdata - new or updates
     * @return bool true.
     * @throws dml_exception A DML specific exception is thrown for any errors.
     * */
    public static function delete_all_act_course($courseid, $type) {
    	global $DB;
    	$sql = "courseid = :courseid AND " . $DB->sql_compare_text('type') . "= :type";
    	return  $DB->delete_records_select('import_remote_course_actdata',$sql, ['courseid' => $courseid, 'type' => $type]);
    }

}