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
defined('MOODLE_INTERNAL') || die;

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function report_moereports_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $USER, $DB;
    $usercontext = context_user::instance($user->id);

    if (isguestuser() or !isloggedin()) {
        return;
    }

    if ($USER->id != $user->id) {
        // No peeking at somebody else's sessions!
        return;
    }

    if ($USER->profile['IsStudent'] == 'No' || is_siteadmin()|| has_capability('report/moereport:viewall', $usercontext)) {
        $schoollevelaccess = $DB->get_field('config', 'value', array('name' => 'schools_level_access'));
        $reginlevelaccess = $DB->get_field('config', 'value', array('name' => 'regin_level_access'));

        if(isset($USER->profile['SimpleRole'])){
            $userrule = explode(",", $USER->profile['SimpleRole']);
            $reginpermitrules = explode(",", $reginlevelaccess);
            $schoolpermitrules = explode(",", $schoollevelaccess);
        }
        $schoollevelaccess = array_intersect($userrule,$schoolpermitrules);
        $reginlevelaccess = array_intersect($userrule,$reginpermitrules);
        if (count($schoollevelaccess)>0 ||  is_siteadmin()|| has_capability('report/moereport:viewall', $usercontext)) {
            $node = new core_user\output\myprofile\node('reports', 'per_activity_school_level', get_string('per_activity_school_level', 'report_moereports'),
                null, new moodle_url('/report/moereports/activity_school_level.php'));
            $tree->add_node($node);
            $node = new core_user\output\myprofile\node('reports', 'per_course_scool_level', get_string('per_course_scool_level', 'report_moereports'),
                null, new moodle_url('/report/moereports/course_scoole_level.php'));
            $tree->add_node($node);
        }
        if (count($reginlevelaccess)>0|| is_siteadmin()|| has_capability('report/moereport:viewall', $usercontext)) {
               $node = new core_user\output\myprofile\node('reports', 'per_activity_regin_level', get_string('per_activity_regin_level', 'report_moereports'),
                   null, new moodle_url('/report/moereports/activity_regin_level.php'));
               $tree->add_node($node);
               $node = new core_user\output\myprofile\node('reports', 'per_course_regin_level', get_string('per_course_regin_level', 'report_moereports'),
                   null, new moodle_url('/report/moereports/course_regin_level.php'));
               $tree->add_node($node);
        }
    }
    return true;
}




