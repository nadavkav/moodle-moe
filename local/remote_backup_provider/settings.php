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
 * @copyright  2015 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_remote_backup_provider', get_string('pluginname', 'local_remote_backup_provider'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext('local_remote_backup_provider/remoteusername',
        get_string('remoteusername_label', 'local_remote_backup_provider'),
        get_string('remoteusername_desc', 'local_remote_backup_provider'),
        ''));

    $adminsetting = new admin_setting_configtext('remotesite', get_string('remotesite', 'local_remote_backup_provider'),
        get_string('remotesite_desc', 'local_remote_backup_provider'), '');
    $adminsetting->plugin = 'local_remote_backup_provider';
    $settings->add($adminsetting);

    $adminsetting = new admin_setting_configtext('wstoken', get_string('wstoken', 'local_remote_backup_provider'),
        get_string('wstoken_desc', 'local_remote_backup_provider'), '');
    $adminsetting->plugin = 'local_remote_backup_provider';
    $settings->add($adminsetting);

    $settings->add(new admin_setting_configcheckbox('local_remote_backup_provider/selfsignssl',
        get_string('selfsignssl_label', 'local_remote_backup_provider'),
        get_string('selfsignssl_desc', 'local_remote_backup_provider'), true));

    $ADMIN->add('localplugins', new admin_externalpage('local_remote_backup_provider/listsubs',
                                          get_string('listofsubs', 'local_remote_backup_provider'),
                                          new moodle_url('/local/remote_backup_provider/subscribermanager.php')));
    $ADMIN->add('localplugins', new admin_externalpage('local_remote_backup_provider/failednotifications',
            get_string('failednotifications', 'local_remote_backup_provider'),
            new moodle_url('/local/remote_backup_provider/failednotifications.php')));

}
