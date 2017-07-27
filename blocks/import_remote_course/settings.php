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
 * Newblock block caps.
 *
 * @package    block_import_remote_course
 * @copyright  Nadav Kavalerchik <nadavkav@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('configheader',
        get_string('config_header', 'block_import_remote_course'),
        get_string('config_desc', 'block_import_remote_course')));

    $settings->add(new admin_setting_configtext('block_import_remote_course/localusername',
        get_string('localusername_label', 'block_import_remote_course'),
        get_string('localusername_desc', 'block_import_remote_course'),
        ''));
    $adminsetting = new admin_setting_configtext('block_import_remote_course/wstoken',
        get_string('wstoken', 'block_import_remote_course'),
        get_string('wstoken_desc', 'block_import_remote_course'), '');
    $adminsetting->plugin = 'block_import_remote_course';
    $settings->add($adminsetting);

    $settings->add(new admin_setting_configtext('block_import_remote_course/testenv',
        get_string('testenv_label', 'block_import_remote_course'),
        get_string('testenv_desc', 'block_import_remote_course'),
        ''));
}