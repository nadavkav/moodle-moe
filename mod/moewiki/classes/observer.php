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

namespace mod_moewiki;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/moewiki/locallib.php');

class observer {
    
    /**
     * 
     * @param \core\event\user_enrolment_created $eventname
     */
    public static function userenrolled (\core\event\user_enrolment_created $event){
        global $DB,$COURSE;
        
        $data = $event->get_data();
        $modinfo = get_fast_modinfo($data['courseid']);
        $moewikiinstances = $modinfo->get_instances_of('moewiki');
        foreach ($moewikiinstances as $moewiki) {
            list($cm, $context, $module, $mowikidata, $cw) = can_update_moduleinfo($moewiki);
            $moewiki = $DB->get_record('moewiki', array('id' => $mowikidata->id));
            $subwiki = moewiki_create_subwiki($moewiki, $moewiki->id, $COURSE, $data['relateduserid']);
            if($texttemplate = $mowikidata->template_text) {             
                $pageversion = moewiki_get_current_page($subwiki, '', MOEWIKI_GETPAGE_CREATE);
                //put the template text in the students main page
                moewiki_save_new_version($COURSE, $cm, $moewiki, $subwiki, '', $texttemplate);
            }
        }
    }
    
    public static function userdeleted ($eventanme){
        global $DB;
    }
}