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

use local_remote_backup_provider\output\subscribers_renderer;

require_once(dirname(__FILE__) . '/../../config.php');

$unsubscribe = optional_param('unsubscribe', null, PARAM_INT);
$unsubscribeconf = optional_param('unsubscribeconf', null, PARAM_INT);

$context = context_system::instance();
require_login();
require_capability('moodle/site:config', $context);
$PAGE->set_context($context);
$PAGE->set_url('/local/remote_backup_provider/subscribermanager.php');
$PAGE->set_pagelayout('standard');

$PAGE->set_title(get_string('subscribermanager', 'local_remote_backup_provider'));
$PAGE->set_heading(get_string('subscribermanager', 'local_remote_backup_provider'));
echo $OUTPUT->header();
$out = new subscribers_renderer($PAGE, null);

if (isset($unsubscribe)) {
    echo $out->unsubscribe($unsubscribe, $unsubscribeconf);
} else {
    echo $out->subs_list();
}
echo $OUTPUT->footer();




