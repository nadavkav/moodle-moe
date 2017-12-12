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
 * @copyright  SysBind <service@sysbind.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_import_remote_course\local;
use block_import_remote_course\persistent;
defined('MOODLE_INTERNAL') || die();

class course_template extends persistent {
    const TABLE = 'import_remote_course_templat';

    /**
     * Return the definition of the properties of course template.
     *
     * @return array
     */
    protected static function define_properties() {
        return array(
            'tamplate_id' => array(
                'type' => PARAM_INT,
                'null' => NULL_NOT_ALLOWED,
            ),
            'course_id' => array(
                'type' => PARAM_INT,
                'null' => NULL_NOT_ALLOWED,
            ),
            'user_id' => array(
                'type' => PARAM_INT,
                'null' => NULL_NOT_ALLOWED,
            ),
        );
    }

    public function get_template_name() {
        global $DB;
        return $DB->get_field('import_remote_course_list', 'course_name', ['id' => $this->get('tamplate_id')]);
    }
}

