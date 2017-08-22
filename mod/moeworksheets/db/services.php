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
 * moeworksheets external functions and service definitions.
 *
 * @package    mod_moeworksheets
 * @category   external
 * @copyright  2016 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.1
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_moeworksheets_get_moeworksheetszes_by_courses' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'get_moeworksheetszes_by_courses',
        'description'   => 'Returns a list of moeworksheetszes in a provided list of courses,
                            if no list is provided all moeworksheetszes that the user can view will be returned.',
        'type'          => 'read',
        'capabilities'  => 'mod/moeworksheets:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_view_moeworksheets' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'view_moeworksheets',
        'description'   => 'Trigger the course module viewed event and update the module completion status.',
        'type'          => 'write',
        'capabilities'  => 'mod/moeworksheets:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_get_user_attempts' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'get_user_attempts',
        'description'   => 'Return a list of attempts for the given moeworksheets and user.',
        'type'          => 'read',
        'capabilities'  => 'mod/moeworksheets:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_get_user_best_grade' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'get_user_best_grade',
        'description'   => 'Get the best current grade for the given user on a moeworksheets.',
        'type'          => 'read',
        'capabilities'  => 'mod/moeworksheets:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_get_combined_review_options' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'get_combined_review_options',
        'description'   => 'Combines the review options from a number of different moeworksheets attempts.',
        'type'          => 'read',
        'capabilities'  => 'mod/moeworksheets:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_start_attempt' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'start_attempt',
        'description'   => 'Starts a new attempt at a moeworksheets.',
        'type'          => 'write',
        'capabilities'  => 'mod/moeworksheets:attempt',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_get_attempt_data' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'get_attempt_data',
        'description'   => 'Returns information for the given attempt page for a moeworksheets attempt in progress.',
        'type'          => 'read',
        'capabilities'  => 'mod/moeworksheets:attempt',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_get_attempt_summary' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'get_attempt_summary',
        'description'   => 'Returns a summary of a moeworksheets attempt before it is submitted.',
        'type'          => 'read',
        'capabilities'  => 'mod/moeworksheets:attempt',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_save_attempt' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'save_attempt',
        'description'   => 'Processes save requests during the moeworksheets.
                            This function is intended for the moeworksheets auto-save feature.',
        'type'          => 'write',
        'capabilities'  => 'mod/moeworksheets:attempt',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_process_attempt' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'process_attempt',
        'description'   => 'Process responses during an attempt at a moeworksheets and also deals with attempts finishing.',
        'type'          => 'write',
        'capabilities'  => 'mod/moeworksheets:attempt',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_get_attempt_review' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'get_attempt_review',
        'description'   => 'Returns review information for the given finished attempt, can be used by users or teachers.',
        'type'          => 'read',
        'capabilities'  => 'mod/moeworksheets:reviewmyattempts',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_view_attempt' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'view_attempt',
        'description'   => 'Trigger the attempt viewed event.',
        'type'          => 'write',
        'capabilities'  => 'mod/moeworksheets:attempt',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_view_attempt_summary' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'view_attempt_summary',
        'description'   => 'Trigger the attempt summary viewed event.',
        'type'          => 'write',
        'capabilities'  => 'mod/moeworksheets:attempt',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_view_attempt_review' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'view_attempt_review',
        'description'   => 'Trigger the attempt reviewed event.',
        'type'          => 'write',
        'capabilities'  => 'mod/moeworksheets:reviewmyattempts',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_get_moeworksheets_feedback_for_grade' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'get_moeworksheets_feedback_for_grade',
        'description'   => 'Get the feedback text that should be show to a student who got the given grade in the given moeworksheets.',
        'type'          => 'read',
        'capabilities'  => 'mod/moeworksheets:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_get_moeworksheets_access_information' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'get_moeworksheets_access_information',
        'description'   => 'Return access information for a given moeworksheets.',
        'type'          => 'read',
        'capabilities'  => 'mod/moeworksheets:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_get_attempt_access_information' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'get_attempt_access_information',
        'description'   => 'Return access information for a given attempt in a moeworksheets.',
        'type'          => 'read',
        'capabilities'  => 'mod/moeworksheets:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_get_moeworksheets_required_qtypes' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'get_moeworksheets_required_qtypes',
        'description'   => 'Return the potential question types that would be required for a given moeworksheets.',
        'type'          => 'read',
        'capabilities'  => 'mod/moeworksheets:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_moeworksheets_get_content_preview' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'get_content_preview',
        'classpath'     =>  'mod/moeworksheets/classes/external.php',
        'descritption'  => 'Return content preview',
        'type'          => 'read',
        'capabilities'  => 'mod/moeworksheets:manage',
        'ajax'          => true
    ),
    'mod_moeworksheets_add_content_to_subject' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'add_content_to_subject',
        'classpath'     => 'mod/moeworksheets/classes/external.php',
        'descritption'  => 'add content to subject',
        'type'          => 'write',
        'capabilities'  => 'mod/moeworksheets:manage',
        'ajax'          => true
    ),
    'mod_moeworksheets_get_question_preview' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'get_question_preview',
        'classpath'     => 'mod/moeworksheets/classes/external.php',
        'descritption'  => 'get question preivew',
        'type'          => 'read',
        'capabilities'  => 'mod/moeworksheets:manage',
        'ajax'          => true
    ),
    'mod_moeworksheets_add_subject_to_content' => array(
        'classname'     => 'mod_moeworksheets_external',
        'methodname'    => 'add_subject_to_content',
        'classpath'     => 'mod/moeworksheets/classes/external.php',
        'descritption'  => 'add subject to content',
        'type'          => 'write',
        'capabilities'  => 'mod/moeworksheets:manage',
        'ajax'          => true
    ),
);

$services = array(
    'moeworksheets' => array(
        'functions' => array(
            'mod_moeworksheets_get_content_preview',
            'mod_moeworksheets_add_content_to_subject',
            'mod_moeworksheets_get_question_preview',
            'mod_moeworksheets_add_subject_to_content',
        ),
        'requiredcapability' => '',
        'restrictedusers' =>0,
        'enabled'=>1,
    ),
);
