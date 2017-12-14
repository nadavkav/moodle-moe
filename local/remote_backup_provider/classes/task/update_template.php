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

namespace local_remote_backup_provider\task;

use core\task\scheduled_task;
use local_remote_backup_provider\publisher;
use local_remote_backup_provider\observer;
use local_remote_backup_provider\fail;
defined('MOODLE_INTERNAL') || die();

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
            if ($DB->get_record('course_modules', array('id' => $cm->id))) {
                self::delete_activity_backup ($cm->id);
            }
            $sectionsublevel = null;
            $modinfo = get_fast_modinfo($cm->course);
            $sectionnumber = $DB->get_field('course_sections', 'section', ['id' => $cm->section]);
            $section = get_section_name($cm->course, $sectionnumber);
            $courseformat = course_get_format($cm->course)->get_format();
            
            if ($courseformat == 'moetopcoll' || $courseformat == 'moetabs') {
                $mod = $modinfo->get_cm($cm->id);
                $sectioninfo = $modinfo->get_section_info($mod->sectionnum);
                $sequence = $sectioninfo->sequence;
                $sequence = explode(',', $sequence);
                foreach ($sequence as $step) {
                    if ($step == $cm->id) {
                        break;
                    }
                    $modparent = $modinfo->get_cm($step);
                    if ($modparent->modname == 'label' && strpos($modparent->content, 'moetopcalllabel')) {
                        $sectionsublevel = $modparent->name;
                    }
                }
            }
            $instance = $DB->get_record($cm->name, ['id' => $cm->instance]);
            $params = array(
                'type' => 'ua',
                'course_id'          => $cm->course,
                'link_to_remote_act' => $CFG->wwwroot . '/mod/' . $cm->name . '/view.php?id=' . $cm->id,
                'cm'                  => $cm->id,
                'mod'                  => $cm->name,
                'name'                  => $instance->name,
                'section'             => $section,
                'sectionsublevel'    => $sectionsublevel
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
            $DB->delete_records('tag_instance', ['id' => $cm->taginstance]);
        }
    }

    private function delete_activity_backup ($cmid) {
        $fs = get_file_storage();
        $context = \context_module::instance ($cmid);

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
        return;
    }
}

