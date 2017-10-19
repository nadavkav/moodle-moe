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
 * script for MOE users extra fields update
 * @subpackage cli
 * @copyright  2017 sysbind
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/clilib.php');

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
		array(
				'file'    => false,
				'h' => null,
				'help'=> null
		),
		array(
				'h' => 'help'
		)
		);

if ($unrecognized) {
	$unrecognized = implode("\n  ", $unrecognized);
	cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

if (!$options['file'] || $options['h'] || $options['help']) {
	$help =
	"this script update extra fields for MOE users.

	use:
    # php user_extra_filds_csv_update.php [--Parameters=<Parameters>]

	Parameters:
    --file     path to CSV file.";
	cli_error($help, 0);
}

$file = fopen($options['file'], 'r');
if (! $file) {
	cli_error("can not open file or file not exist!\n exiting...",0);
}
global $DB;
$total = count(file($options['file'], FILE_SKIP_EMPTY_LINES));
$success = 0;
$fails = 0;
$outpresenteg = 0;
while (($line = fgetcsv($file)) !== FALSE) {
	if ($line[3] == '' || $line[4] == '' || $line[5] == '' || $line[0] == 'SUG_ZEHUT'){
		$fails++;
		continue;
	}
	$username = $line[0] . $line[1];
	$user = $DB->get_record('user', array('username' => $username));
	if (!$user) {
		cli_writeln("user $username not found. skipping..");
		$fails++;
		continue;
	}
	$sql = "select * from {user_info_data} where userid = :userid AND fieldid in (14,17,20)";		
	$userextrafilds = $DB->get_records_sql($sql, array('userid' => $user->id));
	if (!$userextrafilds) {
		cli_writeln("user $username not have extra fields. skipping..");
		$fails++;
		continue;
	}
	foreach ($userextrafilds as $fild) {
		switch ($fild->fieldid) {
			case 14:
				$fild->data = $line[3];
			break;
			case 17:
				$fild->data = $line[4];
			break;
			case 20:
				$fild->data = $line[5];
			break;			
		}
		$DB->update_record('user_info_data', $fild);
	}
	$success++;
	$presentege = round((($success + $fails)/$total) * 100);
	if ($presentege != $outpresenteg){
		cli_writeln("$presentege % finished");
		$outpresenteg = $presentege;
	}
}
fclose($file);
cli_writeln('update complete successfully');
cli_writeln(--$total .' users in CSV');
cli_writeln(--$fails . " users skipped");
cli_writeln($success ." users complete successfully");
exit(0);