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
 * Configuration settings for the moeworksheetsaccess_offlinemode plugin.
 *
 * @package   moeworksheetsaccess_offlinemode
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox_with_advanced('moeworksheetsaccess_offlinemode/defaultenabled',
            get_string('offlinemodeenabled', 'moeworksheetsaccess_offlinemode'),
            get_string('offlinemodeenabled_desc', 'moeworksheetsaccess_offlinemode'),
            array('value' => 0, 'adv' => true)));

    $settings->add(new admin_setting_configtextarea('moeworksheetsaccess_offlinemode/privatekey',
            get_string('privatekey', 'moeworksheetsaccess_offlinemode'),
            get_string('privatekey_desc', 'moeworksheetsaccess_offlinemode'), '', PARAM_RAW, 60, 14));

    $settings->add(new admin_setting_configtextarea('moeworksheetsaccess_offlinemode/publickey',
            get_string('publickey', 'moeworksheetsaccess_offlinemode'),
            get_string('publickey_desc', 'moeworksheetsaccess_offlinemode'), '', PARAM_RAW, 60, 5));
}
