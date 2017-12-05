<?php
namespace local_remote_backup_provider\task;

use core\task\scheduled_task;
use local_remote_backup_provider\publisher;
use local_remote_backup_provider\observer;
use local_remote_backup_provider\fail;

class update_template extends scheduled_task {

    public function get_name() {
        return get_string('updatetemplate', 'local_remote_backup_provider');
    }

    public function execute() {
        global $DB, $CFG;

        require_once($CFG->libdir . '/filelib.php');
        $sql = "SELECT cm.*, m.name, ti.id as taginstance FROM (SELECT * FROM {course_modules} WHERE course IN (SELECT id FROM {course}
            WHERE category IN (SELECT id FROM {course_categories} WHERE idnumber<>''))) cm
            INNER JOIN {tag_instance} ti ON ti.itemid=cm.id INNER JOIN {tag} t on t.id=ti.tagid
            INNER JOIN {modules} m on m.id=cm.module WHERE t.name='update'";
        $coursesmodule = $DB->get_records_sql($sql);
        $instance = new observer();
        $pub = new publisher();
        $prefixurl = '/webservice/rest/server.php?wstoken=';
    	$suffixurl = '&wsfunction=block_import_remote_course_update&moodlewsrestformat=json';
    	$options = array();
        $skipcertverify = (get_config('local_remote_backup_provider', 'selfsignssl')) ? true : false;
    	if ($skipcertverify) {
    		$options['CURLOPT_SSL_VERIFYPEER'] = false;
    		$options['CURLOPT_SSL_VERIFYHOST'] = false;
    	}
    	foreach ($coursesmodule as $cm) {
    		self::delete_activity_backup ($cm->id);
    	    $instance = $DB->get_record($cm->name, ['id' => $cm->instance]);
    	    $params = array(
    			'type' => 'ua',
    			'course_id' 		 => $cm->course,
    			'link_to_remote_act' => $CFG->wwwroot . '/mod/' . $cm->name . '/view.php?id=' . $cm->id,
    			'cm' 				 => $cm->id,
    			'mod' 				 => $cm->name,
    			'name' 				 => $instance->name,
    	    );
    	    foreach ($pub->get_all_subscribers() as $sub) {
                // Subscriber info.
                $token = $sub->remote_token;
                $remotesite = $sub->base_url;
                $localparams = $params;
                $localparams['username'] = $sub->remote_user;

                $url = $remotesite . $prefixurl . $token . $suffixurl;

                $curl = new \curl();
                $resp = json_decode($curl->post($url, $localparams, $options));

                if (! isset($resp->result) || $resp->result != true) {
                    $fail = new fail(null, $url, $localparams, $options, 'send_mod_notification');
                    $fail->save();
                }
    	    }
    	    unset($curl);
    	    $DB->delete_records('tag_instance', ['id' => $cm->taginstnace]);
    	}
    }
    
    private function delete_activity_backup ($cmid) {
    	$fs = get_file_storage();
    	$context = \context_module::instance ( $cmid );
    	
    	// Prepare file record object
    	$fileinfo = array(
    			'component' => 'local_remote_backup_provider',
    			'filearea' => 'backup',     // usually = table name
    			'itemid' => $cmid,               // usually = ID of row in table
    			'contextid' => $context->id, // ID of context
    			'filepath' => '/',           // any path beginning and ending in /
    			'filename' => $cmid . '.mbz'); // any filename
    	
    	// Get file
    	$file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
    			$fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
    	
    	// Delete it if it exists
    	if ($file) {
    		$file->delete();
    	}
    	return ;
    }
}

