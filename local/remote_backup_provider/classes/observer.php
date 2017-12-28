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

namespace local_remote_backup_provider;

use local_remote_backup_provider\publisher;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer.
 *
 * @package local_remote_backup_provider
 * @copyright 2017 SysBind LTD
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /**
     * Event observer.
     * send update to all subscribers about the course change
     */
    public static function send_update(\core\event\base $event) {
        global $DB;

        $pub = new publisher();
        $type = $event->crud;
        // Url strings parts.
        $prefixurl = '/webservice/rest/server.php?wstoken=';
        $postfixurl = '&wsfunction=block_import_remote_course_update&moodlewsrestformat=json';
        $localevent = $event->get_data();
        $options = array();

        // If delete don't need course data.
        if ($type == "d") {
            $params = array(
                'type' => $type,
                'course_id' => $localevent['objectid'],
                'course_tag' => 'stab',
                'course_name' => 'stab',
                'change_log_link' => 'stab'
            );
        } else {
            // Course info to update.
            $localcourse = $DB->get_record('course', array(
                'id' => $localevent['courseid']
            ));

            // Check that the course have category with tag.
            $sql = 'select CA.idnumber from {course} C inner join
                    {course_categories} CA on C.category = CA.id where C.id=:id';
            $tag = $DB->get_field_sql($sql, array(
                'id' => $localcourse->id
            ));
            if ($tag == false) {
                $params = array(
                        'type' => 'd',
                        'course_id' => $localevent['objectid'],
                        'course_tag' => 'stab',
                        'course_name' => 'stab',
                        'change_log_link' => 'stab'
                );
            } else {
                $params = array(
                    'type' => $type,
                    'course_id' => $localcourse->id,
                    'course_tag' => $tag,
                    'course_name' => $localcourse->fullname,
                    'change_log_link' => 'stab'
                );
            }
        }
 
        $skipcertverify = (get_config('local_remote_backup_provider', 'selfsignssl')) ? true : false;
        if ($skipcertverify) {
            $options['curlopt_ssl_verifypeer'] = false;
            $options['curlopt_ssl_verifyhost'] = false;
        }
        
        //in case of new section moodle don't supply event. we using this event and change it to work like send_mod_notification.
        $noofsectioninconfig = $DB->get_field('course_format_options', 'value', ['name' => 'numsections', 'courseid' => $localevent['objectid']]);
        $noofsectioninconfig = $noofsectioninconfig +1;
        $sections = $DB->get_records('course_sections', ['course' => $localevent['objectid']], 'section');
       
        if (count($sections) > 0 && $noofsectioninconfig != count($sections) && $event->crud == 'c' ) {
            $sectionstosand = array_slice($sections, $noofsectioninconfig);
            foreach ($sectionstosand as $section) {
                $name = get_section_name($localevent['objectid'], $section->section);
                $params = array(
                        'type' => 'ns',
                        'course_id'          => $localevent['courseid'],
                        'link_to_remote_act' => 'stub',
                        'cm'                 => $section->id,
                        'mod'                => 'stub',
                        'name'               => $name,
                        'section'            => 'stub',
                );
                
                foreach ($pub->get_all_subscribers() as $sub) {
                    // Subscriber info.
                    $token = $sub->remote_token;
                    $remotesite = $sub->base_url;
                    $localparams = $params;
                    $localparams['username'] = $sub->remote_user;
                    
                    $url = $remotesite . $prefixurl . $token . $postfixurl;
                    
                    $curl = new \curl();
                    $resp = json_decode($curl->post($url, $localparams, $options));
                    
                    if (! isset($resp->result) || $resp->result != true) {
                        $fail = new fail(null, $url, $localparams, $options, 'send_section_update');
                        $fail->save();
                    }
                }
                
                
            }    
        } else {
        
            foreach ($pub->get_all_subscribers() as $sub) {
                // Subscriber info.
                $token = $sub->remote_token;
                $remotesite = $sub->base_url;
                $localparams = $params;
                $localparams['username'] = $sub->remote_user;
    
                $url = $remotesite . $prefixurl . $token . $postfixurl;
    
                $curl = new \curl();
                $resp = json_decode($curl->post($url, $localparams, $options));
    
                if (! isset($resp->result) || $resp->result != true) {
                    $fail = new fail(null, $url, $localparams, $options, 'send_update');
                    $fail->save();
                }
            }
        }
    }

    /**
     * Event observer.
     * send update to all subscribers about the course cate change
     */
    public static function send_cat_update(\core\event\base $event) {
        global $DB;

        $pub = new publisher();
        $type = 'cu';
        // Url strings parts.
        $prefixurl = '/webservice/rest/server.php?wstoken=';
        $postfixurl = '&wsfunction=block_import_remote_course_update&moodlewsrestformat=json';
        $options = array();

        $skipcertverify = (get_config('local_remote_backup_provider', 'selfsignssl')) ? true : false;
        if ($skipcertverify) {
            $options['curlopt_ssl_verifypeer'] = false;
            $options['curlopt_ssl_verifyhost'] = false;
        }

        $localdata = $event->get_data();
        // Get all cources in cat.
        $corcestoupdate = $DB->get_records('course', array(
            'category' => $localdata['objectid']
        ));
        foreach ($corcestoupdate as $localcourse) {
            $sql = 'select CA.idnumber from {course} C inner join {course_categories}
                    CA on C.category = CA.id where C.id=:id';
            $tag = $DB->get_field_sql($sql, array(
                'id' => $localcourse->id
            ));
            $params = array(
                'type' => $type,
                'course_id' => $localcourse->id,
                'course_tag' => $tag,
                'course_name' => $localcourse->fullname,
                'change_log_link' => 'stab'
            );

            foreach ($pub->get_all_subscribers() as $sub) {
                // Subscriber info.
                $token = $sub->remote_token;
                $remotesite = $sub->base_url;
                $localparams = $params;
                $localparams['username'] = $sub->remote_user;

                $url = $remotesite . $prefixurl . $token . $postfixurl;

                $curl = new \curl();
                $resp = json_decode($curl->post($url, $localparams, $options));

                if (! isset($resp->result) || $resp->result != true) {
                    $fail = new fail(null, $url, $localparams, $options, 'send_cat_update');
                    $fail->save();
                }
            }
        }
    }
    /**
     * Event notification_observer.
     * send update to all subscribers about the course cate change
     */
    public static function send_mod_notification(\core\event\base $event) {
        global $DB, $CFG;

        $instance = new observer();
        $localevent = $event->get_data();
        $courseformat = course_get_format($localevent['courseid'])->get_format();
        if (! $instance->parent_have_idnumber($localevent['courseid'])) {
            return;
        }

        if ($courseformat == 'moetopcoll' && $localevent['other']['modulename'] == 'label') {
            if ($localevent['other']['name'] == 'למידה' || $localevent['other']['name'] == 'תומכי למידה' || $localevent['other']['name'] == 'ארגז כלים') {
                return;
            }
        }

        // Get section name.
        if ($localevent['crud'] == 'd') {
            $section = 'stub';
        } else {
            $modinfo = get_fast_modinfo($localevent['courseid']);
            $mod = $modinfo->get_cm($localevent['contextinstanceid']);
            $section = get_section_name($localevent['courseid'], $mod->sectionnum);
        }
        $pub = new publisher();
        $prefixurl = $instance->get_prefixurl();
        $suffixurl = $instance->get_suffixurl();
        $options = array();

        $skipcertverify = (get_config('local_remote_backup_provider', 'selfsignssl')) ? true : false;
        if ($skipcertverify) {
            $options['CURLOPT_SSL_VERIFYPEER'] = false;
            $options['CURLOPT_SSL_VERIFYHOST'] = false;
        }
        $type = $event->crud;
        switch ($type) {
            case "d":
                $params = array(
                        'type' => 'da',
                        'course_id'          => $localevent['courseid'],
                        'link_to_remote_act' => 'stub',
                        'cm'                 => $localevent['objectid'],
                        'mod'                => $localevent['other']['modulename'],
                        'name'               => 'stub',
                        'section'            => $section,
                );
            break;
            case "c":
                $params = array(
                        'type' => 'na',
                        'course_id'          => $localevent['courseid'],
                        'link_to_remote_act' => $CFG->wwwroot . '/mod/' . $localevent['other']['modulename'] . '/view.php?id=' . $localevent['objectid'],
                        'cm'                 => $localevent['objectid'],
                        'mod'                => $localevent['other']['modulename'],
                        'name'               => $localevent['other']['name'],
                        'section'            => $section,
                );
            break;
        }
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
    }

    /**
     * Event notification_observer.
     * send update to all subscribers about post in the chenge log
     */
    public static function send_notification(\core\event\base $event) {
        global $DB, $CFG;
        $instance = new observer();
        $localevent = $event->get_data();
        if (! $instance->parent_have_idnumber($localevent['courseid']) || ! $instance->is_news_forum($localevent['other']['forumid'])) {
            return;
        }

        $pub = new publisher();
        $prefixurl = $instance->get_prefixurl();
        $suffixurl = $instance->get_suffixurl();
        $options = array();

        $skipcertverify = (get_config('local_remote_backup_provider', 'selfsignssl')) ? true : false;
        if ($skipcertverify) {
            $options['curlopt_ssl_verifypeer'] = false;
            $options['curlopt_ssl_verifyhost'] = false;
        }

        $params = array(
                'type'         => 'n',
                'course_id' => $localevent['courseid'],
        );

        foreach ($pub->get_all_subscribers() as $sub) {
            // Subscriber info.
            $token = $sub->remote_token;
            $remotesite = $sub->base_url;
            $localparams = $params;
            $localparams['username'] = $sub->remote_user;

            $url = $remotesite . $prefixurl . $token . $postfixurl;

            $curl = new \curl();
            $resp = json_decode($curl->post($url, $localparams, $options));

            if (! isset($resp->result) || $resp->result != true) {
                $fail = new fail(null, $url, $localparams, $options, 'send_notification');
                $fail->save();
            }
        }

    }

    /**
     * Event notification_observer.
     * send update to all subscribers about new section created
     */
    public function send_section_notification (\core\event\base $event) {
        global $DB, $CFG;
        
        $instance = new observer();
        $localevent = $event->get_data();
        if (! $instance->parent_have_idnumber($localevent['courseid'])) {
            return;
        }

        $pub = new publisher();
        $prefixurl = $instance->get_prefixurl();
        $suffixurl = $instance->get_suffixurl();
        $options = array();
        
        $skipcertverify = (get_config('local_remote_backup_provider', 'selfsignssl')) ? true : false;
        if ($skipcertverify) {
            $options['CURLOPT_SSL_VERIFYPEER'] = false;
            $options['CURLOPT_SSL_VERIFYHOST'] = false;
        }
        $type = $event->crud;
        switch ($type) {
            case "c":
                $params = array(
                    //ADD PARAMS
                );
                break;
        }
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
    }
    
    
    /**
     * get prefix url for the web service.
     *
     * @return String start of web service url.
     */
    protected function  get_prefixurl () {
        return '/webservice/rest/server.php?wstoken=';
    }

    /**
     * get suffix url for the web service.
     *
     * @return String end of web service url.
     */
    protected function  get_suffixurl () {
        return '&wsfunction=block_import_remote_course_update&moodlewsrestformat=json';
    }

    /**
     * check if parent have idnumber to know if we need to notify the subscribers.
     *
     * @return bool .
     */
    protected function parent_have_idnumber($courseid) {
        global $DB;

        $sql = 'select CA.idnumber from {course} C inner join
                    {course_categories} CA on C.category = CA.id where C.id=:id';
         $res = $DB->get_field_sql($sql, array(
                'id' => $courseid
         ));
         if ($res) {
             return true;
         }
            return false;
    }

    /**
     * check if the forum is news forum.
     *
     * @return boolean
     */
    protected function is_news_forum($formid) {
        global $DB;
        return $DB->get_field('forum', 'type', ['id' => $formid]) == 'news' ? true : false;

    }
}
