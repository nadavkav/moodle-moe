<?php
use report_moereports\form\users_stat_form;
use report_moereports\form\user_report_by_date;

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
require_once('../../config.php');

$url = new moodle_url('/report/moereports/users_report_by_date.php');
$PAGE->set_url($url);

// Make sure that the user has permissions to manage moe.
require_login();

$context = context_system::instance();

$PAGE->set_context($context);

require_capability('moodle/site:config', $context);

$PAGE->set_title(get_string('usersinfo', 'report_moereports'));
$PAGE->set_heading(get_string('usersinfo', 'report_moereports'));
$PAGE->set_pagelayout('admin');

$mform = new user_report_by_date();
$renderer = $PAGE->get_renderer('report_moereports','user_report_by_date');
if ($fromform = $mform->get_data()) {
	$timestart =  $fromform->timestart;
	$out = $renderer->display_report($timestart);
} else {
	$out = $renderer->display_report();
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('usersinfo', 'report_moereports'));
echo $out;
echo $OUTPUT->footer();