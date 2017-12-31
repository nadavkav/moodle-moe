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
use block_import_remote_course\local\notification_helper;
defined('MOODLE_INTERNAL') || die();

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
            'username'           => new external_value(PARAM_RAW),
            'type'               => new external_value(PARAM_ALPHANUMEXT),
            'course_id'          => new external_value(PARAM_INT),
            'course_tag'         => new external_value(PARAM_TEXT, '', VALUE_DEFAULT, null),
            'course_name'        => new external_value(PARAM_TEXT, '', VALUE_DEFAULT, null),
            'change_log_link'    => new external_value(PARAM_TEXT, '', VALUE_DEFAULT, null),
            'link_to_remote_act' => new external_value(PARAM_URL, '', VALUE_DEFAULT, null),
            'cm'                 => new external_value(PARAM_INT, '', VALUE_DEFAULT, null),
            'mod'                => new external_value(PARAM_TEXT, '', VALUE_DEFAULT, null),
            'name'               => new external_value(PARAM_TEXT, '', VALUE_DEFAULT, null),
            'section'            => new external_value(PARAM_TEXT, '', VALUE_DEFAULT, null),
        ));
    }
    
    /**
     * update to the server
     *
     * @return boolean.
     */
    public static function update($username, $type, $course_id, $course_tag = null, $course_name = null, $change_log_link =null, $link_to_remote_act = null, $cm = null, $mod = null, $name = null, $section = null) {
        return subscriber::update($type, $course_id, $course_tag, $course_name, $change_log_link, $link_to_remote_act, $cm, $mod, $name, $section);
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

        // Check if activity already exist.
        $fs = get_file_storage();

        // Prepare file record object
        $fileinfo = array(
                'component' => 'blocks_import_remote_course',
                'filearea'  => 'backup',
                'itemid'    => $cmid,
                'contextid' => $context->id,
                'filepath'  => '/',
                'filename'  => $cmid . 'mbz');

        // Get file
        $downloadedbackupfile = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

        $destcourseid = $params['courseid'];
        // Read contents
        if (! $downloadedbackupfile) {
            // file doesn't exist - do something
            // Get local_remote_backup_provider system-level config settings.
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
            if ($skipcertverify) {
                $options['curlopt_ssl_verifypeer'] = false;
                $options['curlopt_ssl_verifyhost'] = false;
            }
            $curlparams = array('id' => $params['cmid'], 'username' => $remoteusername);
            $curl = new curl();
            $resp = json_decode($curl->post($url, $curlparams, $options));
            $timestamp = time();
            $filerecord = array(
                'contextid'    => $context->id,
                'component'    => 'blocks_import_remote_course',
                'filearea'     => 'backup',
                'itemid'       => $cmid,
                'filepath'     => '/',
                 'filename'    => $cmid . 'mbz',
                'timecreated'  => $timestamp,
                'timemodified' => $timestamp
            );
            $downloadedbackupfile = $fs->create_file_from_url($filerecord, $resp->url . '?token=' . $token, array('skipcertverify' => $skipcertverify), true);
        }

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
        } else {
            $result->status = 'fail';
            return $result;
        }
        $backupinfo = $rc->get_info();
        $rc->destroy();

        unset($rc); // File logging is a mess, we can only try to rely on gc to close handles.
        $newactivity = reset($backupinfo->activities);
        $cm = end(get_coursemodules_in_course($newactivity->modulename, $destcourseid));
        $section = $DB->get_record('course_sections', array('course' => $destcourseid, 'section' => $sectionid));

        moveto_module($cm, $section, null);
        // Log course - template.
        $notification = notification_helper::get_record(['cm' => $params['cmid'], 'courseid' => $destcourseid]);
        if (!empty($notification) && !empty($notification->get('id'))) {
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

    /**
     * Returns description of subscribe method parameters
     *
     * @return external_function_parameters
     */
    public static function delete_act_parameters() {
        return new external_function_parameters(array(
                'type'                => new external_value(PARAM_ALPHANUMEXT),
                'course_id'          => new external_value(PARAM_INT),
        ));
    }

    /**
     * update to the server
     *
     * @return boolean.
     */
    public static function delete_act($type, $course_id) {
        $params = self::validate_parameters(self::delete_act_parameters(), ['type' => $type,'course_id' => $course_id]);
        return ['result' => subscriber::delete_all_act_course($params['course_id'], $params['type'])];
    }

    /**
     * Returns description of subscribe method result value
     *
     * @return external_description
     */
    public static function delete_act_returns() {
        return new external_function_parameters(array( 'result' => (new external_value(PARAM_BOOL))));
    }
    
    public static function import_section_parameters() {
        return new external_function_parameters(array(
                'cmid' => new external_value(PARAM_INT, 'context id on remote site', true, null, false),
                'courseid' => new external_value(PARAM_INT, 'course id to restore to', true, null, false),
        ));
    }
    
    public static function import_section($cmid, $courseid) {
        global $CFG, $DB, $USER;
        
        $result = new stdClass();
        $params = self::validate_parameters(self::import_section_parameters(), array(
                'cmid' => $cmid,
                'courseid' => $courseid,
        ));
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        
        // Check if activity already exist.
        $fs = get_file_storage();
        
        // Prepare file record object
        $fileinfo = array(
                'component' => 'blocks_import_remote_course',
                'filearea'  => 'backup',
                'itemid'    => $cmid,
                'contextid' => $context->id,
                'filepath'  => '/section/',
                'filename'  => $cmid . 'mbz');
        
        // Get file
        $downloadedbackupfile = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
        
        $destcourseid = $params['courseid'];
        // Read contents
        if (! $downloadedbackupfile) {
            // Get local_remote_backup_provider system-level config settings.
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
            '&wsfunction=local_remote_backup_provider_get_section_backup_by_id&moodlewsrestformat=json';
            $options = [];
            if ($skipcertverify) {
                $options['curlopt_ssl_verifypeer'] = false;
                $options['curlopt_ssl_verifyhost'] = false;
            }
            $curlparams = array('id' => $params['cmid'], 'username' => $remoteusername);
            $curl = new curl();
            $resp = json_decode($curl->post($url, $curlparams, $options));
            $timestamp = time();
            $filerecord = array(
                    'contextid'    => $context->id,
                    'component'    => 'blocks_import_remote_course',
                    'filearea'     => 'backup',
                    'itemid'       => $cmid,
                    'filepath'     => '/section/',
                    'filename'    => $cmid . 'mbz',
                    'timecreated'  => $timestamp,
                    'timemodified' => $timestamp
            );
            $downloadedbackupfile = $fs->create_file_from_url($filerecord, $resp->url . '?token=' . $token, array('skipcertverify' => $skipcertverify), true);
        }
        
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
        } else {
            $result->status = 'fail';
            return $result;
        }
        $backupinfo = $rc->get_info();
        $rc->destroy();
        
        unset($rc); // File logging is a mess, we can only try to rely on gc to close handles.
        
        // Log course - template.
        $notification = notification_helper::get_record(['cm' => $params['cmid'], 'courseid' => $destcourseid]);
        if (!empty($notification) && !empty($notification->get('id'))) {
            $notification->delete();
        }
        // Finished? ... show updated course.
        $result->status = 'success';
        
        // Update no' of section
        require_once($CFG->dirroot.'/course/lib.php');
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $courseformatoptions = course_get_format($course)->get_format_options();
        $courseformatoptions['numsections']++;
        update_course((object)array('id' => $courseid, 'numsections' => $courseformatoptions['numsections']));
        
        return $result;
    }
    
    /**
     * Returns description of subscribe method result value
     *
     * @return external_description
     */
    public static function import_section_returns() {
        return new external_single_structure(array(
                'status' => new external_value(PARAM_ALPHA, 'restore status'),
        ));
    }    
}
