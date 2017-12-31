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


require_once(dirname(__FILE__) . '/../../config.php');

$context = context_system::instance();
require_login();
require_capability('moodle/site:config', $context);
global $PAGE;
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/remote_backup_provider/failednotifications.php'));
navigation_node::override_active_url(new moodle_url('/local/remote_backup_provider/failednotifications.php'), true);

$PAGE->set_pagelayout('standard');

$PAGE->set_title(get_string('failednotifications', 'local_remote_backup_provider'));
$PAGE->set_heading(get_string('failednotifications', 'local_remote_backup_provider'));
echo $OUTPUT->header();
$render = $PAGE->get_renderer('local_remote_backup_provider', 'fails_meneger');
$PAGE->requires->js_call_amd('local_remote_backup_provider/fail_meneger_helper', 'init');

echo $render->showall();
echo $OUTPUT->footer();




