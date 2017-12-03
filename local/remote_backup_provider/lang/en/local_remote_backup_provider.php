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

$string['import'] = 'Import from remote';
$string['pluginname'] = 'Remote backup provider';
$string['pluginnotconfigured'] = 'The plugin is not configured';
$string['remotesite'] = 'Remote site';
$string['remotesite_desc'] = 'The fully-qualified domain of the remote site';
$string['wstoken'] = 'Web service token';
$string['wstoken_desc'] = 'Add the web service token from the remote site.';
$string['remote_backup_provider:access'] = 'Access';
$string['selfsignssl_label'] = 'allow self sign certification';
$string['selfsignssl_desc'] = 'If in the remote site the ssl is self sign';

$string['remoteusername_label'] = 'Remote Username';
$string['remoteusername_desc'] = 'Username used to backup the original courses on the remote system.';
$string['listofsubs'] = 'list of remote servers';
$string['subscribermanager'] = 'subscribers management';

$string['subscriber_name'] = 'Subscriber name';
$string['base_url'] = 'Subscriber url';
$string['remote_user'] = 'user name in remote sys';
$string['remote_token'] = 'token for remote user';
$string['remove_remote'] = 'remove subscriber';
$string['confirm'] = "are you sure you want to unsubscribe the remote server?";
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['cronjub'] = "Send fails update to remote servers";
$string['cronjub_desc'] = "in case the remote server not respond with OK we want to send him the update again";
$string['failednotifications'] = "fails notification";
$string['id'] = 'ID';
$string['name'] = 'Site';
$string['timecreate'] = 'Time create';
$string['last_time_try'] = 'Time from last sand try';
$string['resend'] = 'Re sand the notification';
$string['type'] = 'Type';

$string['failmesege'] = 'failed to send notification please try again later';
$string['successmesege'] = 'notification sand successfully';
$string['updatetemplate'] = 'update template';
