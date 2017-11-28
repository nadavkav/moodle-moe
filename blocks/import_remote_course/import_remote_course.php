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
/**
 * Block import_remote_course
 *
 * Display a list of courses to be imported from a remote Moodle system
 * Using a local/remote_backup_provider plugin (dependency)
 *
 * @package    block_import_remote_course
 * @copyright  Nadav Kavalerchik <nadavkav@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

$destcourseid = optional_param('destcourseid', 0, PARAM_INT);
$remote = optional_param('remotecourseid', 0, PARAM_INT);
$sessiontoken = optional_param('sessionid', false, PARAM_ALPHANUM);

//todo: use $COURSE?
$course = $DB->get_record('course', array('id' => $destcourseid), '*', MUST_EXIST);

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

$PAGE->set_url('/blocks/import_remote_course/import_remote_course.php', array('destcourseid' => $destcourseid));
$PAGE->set_pagelayout('report');
$returnurl = new moodle_url('/course/view.php', array('id' => $destcourseid));

if (empty($remote)) {
    redirect($returnurl, get_string('missingremote'));
}
// More security.
if (empty($sessiontoken) || $sessiontoken != session_id()) {
    redirect($returnurl, get_string('missingsessionid'));
}

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
    '&wsfunction=local_remote_backup_provider_get_manual_course_backup_by_id&moodlewsrestformat=json';
$options = [];
if ($skipcertverify){
    $options['curlopt_ssl_verifypeer'] = false;
    $options['curlopt_ssl_verifyhost'] = false;
}
$params = array('id' => $remote, 'username' => $remoteusername);
//print_r($params);
$curl = new curl();
// todo: Use HTTPS?
$resp = json_decode($curl->post($url, $params, $options));

//echo "<br>Link to file:<br>";
//print_object($resp->url);
//echo $resp->url . '?token=' . $token;
//die;

if ($CFG->debug) {
    debugging("(3) Starting download & redirecting to restore process...<br>");
}

// Import the backup file.
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
//print_r($filerecord);
//die;
$downloadedbackupfile = $fs->create_file_from_url($filerecord, $resp->url . '?token=' . $token, array('skipcertverify' => $skipcertverify), true);

// Used to redirect to a detailed restore process
//$restoreurl = new moodle_url(
//    '/backup/restore.php',
//    array(
//        'contextid'    => $context->id,
//        'pathnamehash' => $storedfile->get_pathnamehash(),
//        'contenthash'  => $storedfile->get_contenthash()
//    )
//);
//redirect($restoreurl);

if ($CFG->debug) {
    debugging("(4) Start quick restore into (merge) current course...<br>");
}

// Extract to a temp folder.
$filepath = md5(time() . '-' . $context->id . '-'. $USER->id . '-'. random_string(20));
$fb = get_file_packer('application/vnd.moodle.backup');
$extracttopath = $CFG->tempdir . '/backup/' . $filepath . '/';
$extractedbackup = $fb->extract_to_pathname($downloadedbackupfile, $extracttopath);

// Prepare for restore
$rc = new restore_controller($filepath, $destcourseid, backup::INTERACTIVE_NO,
    backup::MODE_IMPORT, $USER->id, backup::TARGET_CURRENT_DELETING);
// Check if the format conversion must happen first.
if ($rc->get_status() == backup::STATUS_REQUIRE_CONV) {
    $rc->convert();
}
if ($rc->execute_precheck()) {
    // Start restore (import).
    $rc->execute_plan();
    //echo get_string('courserestored', 'tool_uploadcourse');
} else {
    echo get_string('errorwhilerestoringthecourse', 'tool_uploadcourse');
}
$backupinfo = $rc->get_info();
$course = $DB->get_record('course', array('id' => $destcourseid), '*', MUST_EXIST);
$course->format = $backupinfo->original_course_format;
$DB->update_record('course', $course);
$rc->destroy();
unset($rc); // File logging is a mess, we can only try to rely on gc to close handles.

//log course - template
$template_id = $DB->get_field('import_remote_course_list', 'id', ['course_id' => $remote]);
$dataobject = new stdClass();
$dataobject->course_id = $destcourseid;
$dataobject->tamplate_id = $template_id;
$dataobject->user_id = $USER->id;
$dataobject->time_added = time();
$DB->insert_record('import_remote_course_templat', $dataobject);
// Finished? ... show updated course.
redirect($returnurl);
