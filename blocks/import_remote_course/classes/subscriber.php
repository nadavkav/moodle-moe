<?php
use tool_log\plugininfo\logstore;

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
            case 'n':
            	$sql = "UPDATE {import_remote_course_notific}
                        SET no_of_notification = no_of_notification + 1
                        where tamplate_id=:tamplate_id";
                $DB->execute($sql, array('tamplate_id' => course_id));
            	break;
            case 'na':
                $data = [];
                $templateid = $DB->get_field('import_remote_course_list', 'id', array(
                    'course_id' => $course_id
                ));
                $coursewithtamplayte = $DB->get_records('import_remote_course_templat',array(
                    'tamplate_id' => $templateid
                ));
                $dataobject = new stdClass();
            	$dataobject->link_to_remote_act = $link_to_remote_act;
            	$dataobject->cm       		    = (int)$cm;
            	$dataobject->module                = $mod;
            	$dataobject->name 		        = $name;
            	$dataobject->time_added 	    = time();
            	$dataobject->type               = 'new';
                foreach ($coursewithtamplayte as $course) {
                    $dataobject->courseid        = (int)$course->course_id;
                    $data[] = clone $dataobject;
                }
                if (!empty($data)) {
                    $DB->insert_records('import_remote_course_actdata', $data);
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
    	return $DB->delete_records('import_remote_course_templat', ['course_id' => $courseid]);
    }

    /**
     * sign user to notification on course.
     *
     * @param int $user -  user id.
     * @param int $course -  course id
     * @return bool true | false
     */
    public function sign_user($user, $course, $template_id){
    	global $DB;
    	$dataobject = new stdClass();
    	$dataobject->course_id 					   = $course;
    	$dataobject->teacher_id 				   = $user;
    	$dataobject->tamplate_id 				   = $template_id;
    	$dataobject->no_of_notification            = 0;
    	$dataobject->time_last_notification        = now();
    	$dataobject->time_last_reset_notifications = now();
    	$dataobject->time_last_reset_act 		   = now();
    	$DB->insert_record('import_remote_course_notific', $dataobject);
    }

    /**
     * sign user to notification on course.
     *
     * @param int $user -  user id.
     * @param int $course -  course id
     * @return bool true | false
     */
    public function un_sign_user($user, $course){
    	global $DB;
    	$DB->delete_records('import_remote_course_notific', ['teacher_id' => $user, 'course_id' => $course]);
    }

    /**
     * reset notification on change log count for specific user in a course.
     *
     * @param int $user -  user id.
     * @param int $course -  local course id
     * @return bool true
     */
    public function reset_notification($user, $course){
    	global $DB;
    	$dataobject = $DB->get_record('import_remote_course_notific', ["teacher_id" => $user, "course_id" => $course]);
    	if (!$dataobject) {
    		return false;
    	}
    	$dataobject->no_of_notification = 0;
    	$dataobject->time_last_reset_notifications = now();
    	return $DB->update_record('import_remote_course_notific', $dataobject);
    }

    /**
     * reset notification on new activity's count for specific user in a course.
     *
     * @param int $user -  user id.
     * @param int $course - local course id
     * @return bool true
     */
    public function reset_activitys($user, $course){
    	global $DB;
    	$dataobject = $DB->get_record('import_remote_course_notific', ["teacher_id" => $user, "course_id" => $course]);
    	if (!$dataobject) {
    		return false;
    	}
    	$dataobject->time_last_reset_act = now();
    	return $DB->update_record('import_remote_course_notific', $dataobject);
    }

    /**
     * get a single activity from remote server and restore it to the  course
     *
     * @param int $cm - remote mod cm id.
     * @param int $course -  local course id
     * @return bool true|false
     */
    public function get_remote_activity ($cm, $course) {
    	$course = $DB->get_record('course', array('id' => $course), '*', MUST_EXIST);

    	if ($CFG->debug) {
    		debugging("(1) Security checks...<br>");
    	}

    	// Security
    	if (!isloggedin()) {
    		require_login($course);
    	}

    	// Check permissions.
    	$context = context_course::instance($course->id);
    	require_capability('local/remote_backup_provider:access', $context);
    	require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
    	// Get local_remote_backup_provider system-level config settings.
    	$token      = get_config('local_remote_backup_provider', 'wstoken');
    	$remotesite = get_config('local_remote_backup_provider', 'remotesite');
    	$skipcertverify = (get_config('local_remote_backup_provider', 'selfsignssl')) ? true : false;
    	if (empty($token) || empty($remotesite)) {
    		print_error('pluginnotconfigured', 'local_remote_backup_provider', $returnurl);
    	}

    	if ($CFG->debug) {
    		debugging("(2) Generate or get last automated remote backup via Web Service...<br>");
    	}

    	// Get import_remote_course system-level config settings.
    	$remoteusername = get_config('local_remote_backup_provider', 'remoteusername');
    	if (empty($remoteusername)) {
    		print_error('missingremoteusername', 'block_import_remote_course', $returnurl);
    	}
    	// Generate the backup file.
    	$fs = get_file_storage();
    	$url = $remotesite . '/webservice/rest/server.php?wstoken=' . $token .
    	'&wsfunction=local_remote_backup_provider_get_activity_backup_by_id&moodlewsrestformat=json';
    	$options = [];
    	if ($skipcertverify){
    		$options['curlopt_ssl_verifypeer'] = false;
    		$options['curlopt_ssl_verifyhost'] = false;
    	}
    	$params = array('id' => $cm, 'username' => $remoteusername);
    	$curl = new curl();
    	$resp = json_decode($curl->post($url, $params, $options));

    	if ($CFG->debug) {
    		debugging("(3) Starting download & redirecting to restore process...<br>");
    	}

    	$timestamp = time();
    	$filerecord = array(
    			'contextid' => $context->id,
    			'component' => 'local_remote_backup_provider',
    			'filearea'  => 'backup',
    			'itemid'    => $timestamp,
    			'filepath'  => '/',
    			'filename'  => 'foo.mbz',
    			'timecreated' => $timestamp,
    			'timemodified' => $timestamp
    	);
    	$downloadedbackupfile = $fs->create_file_from_url($filerecord, $resp->url . '?token=' . $token, array('skipcertverify' => $skipcertverify), true);

    	if ($CFG->debug) {
    		debugging("(4) Start quick restore into (merge) current course...<br>");
    	}

    	// Extract to a temp folder.
    	$filepath = md5(time() . '-' . $context->id . '-'. $user . '-'. random_string(20));
    	$fb = get_file_packer('application/vnd.moodle.backup');
    	$extracttopath = $CFG->tempdir . '/backup/' . $filepath . '/';
    	$extractedbackup = $fb->extract_to_pathname($downloadedbackupfile, $extracttopath);

    	// Prepare for restore
    	$rc = new restore_controller($filepath, $course, \backup::INTERACTIVE_NO,
    			\backup::MODE_IMPORT, $user, \backup::EXISTING_ADDING);

    	if ($rc->execute_precheck()) {
    		$rc->execute_plan();
    	} else {
    		echo get_string('errorwhilerestoringthecourse', 'tool_uploadcourse');
    	}
    	$rc->destroy();
    	unset($rc);
    	return true;
    }
}