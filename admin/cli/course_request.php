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

define('CLI_SCRIPT', true);
require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/clilib.php');
cli_writeln('starting rule assign script...');

$users = $DB->get_records_sql("select * from {user} where id in (select uid.userid from {user_info_data} uid INNER JOIN {user_info_field} uif 
    ON uid.fieldid = uif.id where uif.shortname ='IsStudent' and uid.data = 'NO' and uid.userid in (select userid from {user_info_data} uid INNER JOIN 
    {user_info_field} uif ON uid.fieldid = uif.id where uif.shortname = 'SimpleRole' and uid.data like '%667%'))");
cli_writeln("found " . count($users) . " users to update");

$roleid = $DB->get_field('role', 'id', ['shortname'=>'course_request']);
cli_writeln('the rule ID is: ' . $roleid);

$contextid = $DB->get_field('context', 'id', ['contextlevel' => CONTEXT_SYSTEM, 'depth' => 1]);
cli_writeln('the context ID is: ' . $contextid);

$totalsuccess = 0;
$totalskipd = 0;
foreach ($users as $user){
    $addrole = ['roleid' => $roleid, 'contextid' => $contextid,'userid' => $user->id ,'timemodified' => time(), 'modifierid' => '2'];
    $exist = $DB->get_records('role_assignments', ['userid' => $user->id, 'roleid' => $roleid]);
    if (empty($exist)){
        cli_writeln("adding user with ID  $user->id");
        $DB->insert_record('role_assignments', $addrole);
        $totalsuccess++;
    } else {
        cli_writeln("user with ID $user->id already enroll. skips!");
        $totalskipd++;
    }
}

cli_writeln('script finish successfully');
cli_writeln('-------------------------------------');
cli_writeln('total user processed:' . count($users));
cli_writeln('total users enrolled:' . $totalsuccess);
cli_writeln('total users skipped:' . $totalskipd);


