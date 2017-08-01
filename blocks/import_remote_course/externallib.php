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

require_once ($CFG->dirroot . '/lib/externallib.php');

class block_import_remote_course_external extends external_api {

    /**
     * Returns description of subscribe method parameters
     *
     * @return external_function_parameters
     */
    public static function update_parameters() {
        return new external_function_parameters(array(
            'type'       => new external_value(PARAM_ALPHANUMEXT),
            'course_id'  => new external_value(PARAM_INT),
            'course_tag' => new external_value(PARAM_TEXT),
            'course_name'=> new external_value(PARAM_TEXT),
            'username' => new external_value(PARAM_RAW)
        ));
    }

    /**
     * update to the server
     *
     * @return boolean.
     */
    public static function update($type, $course_id, $course_tag, $course_name, $username) {

        $subscribedata = self::validate_parameters(self::update_parameters(), array(
            'type'        => $type,
            'course_id'   => $course_id,
            'course_tag'  => $course_tag,
            'course_name' => $course_name,
            'username' => $username
            ));
        return subscriber::update($type, $course_id, $course_tag, $course_name);
    }

    /**
     * Returns description of subscribe method result value
     *
     * @return external_description
     */
    public static function update_returns() {
        return new external_function_parameters( array (new external_value(PARAM_BOOL)));
    }
}
