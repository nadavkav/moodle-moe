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
require_once('../../config.php');

$url = new moodle_url('/report/moereports/users_report.php');
$PAGE->set_url($url);

// Make sure that the user has permissions to manage moe.
require_login();

$context = context_system::instance();

$PAGE->set_context($context);

require_capability('moodle/site:config', $context);

$PAGE->set_title(get_string('usersinfo', 'report_moereports'));
$PAGE->set_heading(get_string('usersinfo', 'report_moereports'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('usersinfo', 'report_moereports'));

global $DB;
$isstudentfieldid = $DB->get_field('user_info_field', 'id', array('shortname' => 'IsStudent'));
if (!$isstudentfieldid) {
    throw new coding_exception('IsStudent filed is missing');
}
$sumusers = $DB->get_record_sql("SELECT COUNT(*) as count FROM {user} WHERE NOT(username = 'guest') and deleted=0");
$sumstudents = $DB->get_record_sql("SELECT COUNT(*) as count FROM {user_info_data} uid join {user} u on uid.userid=u.id WHERE uid.fieldid = ? and uid.data ='Yes' and u.deleted=0",
    array($isstudentfieldid));
$sumstuff = $sumusers->count - $sumstudents->count;
echo '<div>'.get_string('numofusers', 'report_moereports') . ' ' . $sumusers->count . '</div>
    <table style="width:50%">
    <tr>
    <th>'.get_string('teachingstuff', 'report_moereports') . '</th>
    <th>'.get_string('students', 'report_moereports') . '</th>
  </tr>
   <tr>
    <th>' . $sumstuff .'</th>
    <th>' . $sumstudents->count . '</th>
  </tr>
        </table>';

echo $OUTPUT->footer();