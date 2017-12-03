<?php
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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
/**
 *
 * @package blocks_import_remote_course
 * @copyright 2015 Lafayette College ITS
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/lib/externallib.php');
require_once($CFG->dirroot . '/blocks/import_remote_course/classes/subscriber.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
class block_import_remote_course_external extends external_api {

    /**
     * Returns description of subscribe method parameters
     *
     * @return external_function_parameters
     */
    public static function update_parameters() {
        return new external_function_parameters(array(
        	'username'			=> new external_value(PARAM_RAW),
            'type'    		    => new external_value(PARAM_ALPHANUMEXT),
            'course_id'  		=> new external_value(PARAM_INT),
        	'course_tag'	    => new external_value(PARAM_TEXT, '',VALUE_DEFAULT, null),
        	'course_name'	    => new external_value(PARAM_TEXT, '',VALUE_DEFAULT, null),
        	'link_to_remote_act'=> new external_value(PARAM_URL, '',VALUE_DEFAULT, null),
        	'cm'  				=> new external_value(PARAM_INT,'',VALUE_DEFAULT, null),
        	'mod'				=> new external_value(PARAM_TEXT, '',VALUE_DEFAULT, null),
        	'name'				=> new external_value(PARAM_TEXT, '',VALUE_DEFAULT, null),
        ));
    }

    /**
     * update to the server
     *
     * @return boolean.
     */
    public static function update($username, $type, $course_id, $course_tag = null, $course_name = null, $link_to_remote_act = null, $cm = null, $mod = null, $name = null) {
        return subscriber::update($type, $course_id, $course_tag, $course_name, $link_to_remote_act, $cm, $mod, $name);
    }

    /**
     * Returns description of subscribe method result value
     *
     * @return external_description
     */
    public static function update_returns() {
        return new external_function_parameters(array( 'result' =>  (new external_value(PARAM_BOOL))));
    }

    public static function import_activity_parameters() {
        return new external_function_parameters(array(
            'cmid' => new external_value(PARAM_INT, 'context id on remote site', true, null, false),
            'courseid' => new external_value(PARAM_INT, 'course id to restore to', true, null, false),
            'sectionid' => new external_value(PARAM_INT, 'section id number', true, null, false),
        ));
    }

    public static function import_activity($cmid, $courseid, $sectionid) {
        global $CFG, $DB, $USER;

        $result = new stdClass();
        $params = self::validate_parameters(self::import_activity_parameters(), array(
            'cmid' => $cmid,
            'courseid' => $courseid,
            'sectionid' => $sectionid,
        ));
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        // Get local_remote_backup_provider system-level config settings.
        $destcourseid = $params['courseid'];
        $token      = get_config('local_remote_backup_provider', 'wstoken');
        $remotesite = get_config('local_remote_backup_provider', 'remotesite');
        $remoteusername = get_config('local_remote_backup_provider', 'remoteusername');
        $skipcertverify = (get_config('local_remote_backup_provider', 'selfsignssl')) ? true : false;
        if (empty($token) || empty($remotesite)) {
            $result->status = 'fail';
            return $result;
        }
        $fs = get_file_storage();
        $url = $remotesite . '/webservice/rest/server.php?wstoken=' . $token .
            '&wsfunction=local_remote_backup_provider_get_activity_backup_by_id&moodlewsrestformat=json';
        $options = [];
        if ($skipcertverify){
            $options['curlopt_ssl_verifypeer'] = false;
            $options['curlopt_ssl_verifyhost'] = false;
        }
        $curlparams = array('id' => $params['cmid'], 'username' => $remoteusername);
        //print_r($params);
        $curl = new curl();
        // todo: Use HTTPS?
        $resp = json_decode($curl->post($url, $curlparams, $options));

        $timestamp = time();
        $filerecord = array(
            'contextid' => $context->id,
            'component' => 'local_remote_backup_provider',
            'filearea'  => 'backup',
            'itemid'    => $timestamp,
            'filepath'  => '/',
            'filename'  => 'foo',
            'timecreated' => $timestamp,
            'timemodified' => $timestamp
        );
        $downloadedbackupfile = $fs->create_file_from_url($filerecord, $resp->url . '?token=' . $token, array('skipcertverify' => $skipcertverify), true);
        $filepath = md5(time() . '-' . $context->id . '-' . $USER->id . '-' . random_string(20));
        $fb = get_file_packer('application/vnd.moodle.backup');
        $extracttopath = $CFG->tempdir . '/backup/' . $filepath . '/';
        $extractedbackup = $fb->extract_to_pathname($downloadedbackupfile, $extracttopath);

        // Prepare for restore
        $rc = new restore_controller($filepath, $destcourseid, backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id, backup::TARGET_CURRENT_ADDING);
        // Check if the format conversion must happen first.
        if ($rc->get_status() == backup::STATUS_REQUIRE_CONV) {
            $rc->convert();
        }
        if ($rc->execute_precheck()) {
            // Start restore (import).
            $rc->execute_plan();
            // echo get_string('courserestored', 'tool_uploadcourse');
        } else {
            $result->status = 'fail';
            return $result;
        }
        $backupinfo = $rc->get_info();
        $rc->destroy();

        unset($rc); // File logging is a mess, we can only try to rely on gc to close handles.
        $newactivity = reset($backupinfo->activities);
        $cm = end(get_coursemodules_in_course($newactivity->modulename, $destcourseid));
        $section = $DB->get_record('course_sections', array('course'=>$destcourseid, 'section'=>$sectionid));

        moveto_module($cm, $section, null);
        // log course - template
        $notification = notification_helper::get_record(['cm' => $params['cmid'], 'courseid' => $destcourseid]);
        if(!empty($notification) && !empty($notification->get('id'))) {
            $notification->delete();
        }
        // Finished? ... show updated course.
        $result->status = 'success';
        return $result;
    }

    public static function import_activity_returns() {
        return new external_single_structure(array(
            'status' => new external_value(PARAM_ALPHA, 'restore status'),
        ));
    }
}
