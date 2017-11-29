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
 * @package    local_remote_backup_provider
 * @copyright  2017 SysBind LTD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(
	array(
			'eventname'   => 'core\event\course_deleted',
			'callback'    => 'block_import_remote_course\observer::course_delete',
			'internal'  => false, // This means that we get events only after transaction commit.
			'priority'  => 1000,
	),
    array(
        'eventname'   => 'core\event\user_enrolment_created',
        'callback'    => 'block_import_remote_course\observer::enrol_user_check',
        'internal'  => false, // This means that we get events only after transaction commit.
        'priority'  => 1000,
    ),
	array(
			'eventname'   => 'core\event\user_enrolment_deleted',
			'callback'    => 'block_import_remote_course\observer::enrol_user_remove',
			'internal'  => false, // This means that we get events only after transaction commit.
			'priority'  => 1000,
	),
);