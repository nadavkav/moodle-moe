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
 * script for mor report - crete the data for a singale report
 * @subpackage cli
 * @copyright  2017 sysbind
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('CLI_SCRIPT', true);
require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/clilib.php');

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
        array(
                'report'    => false,
                'h'         => null,
                'help'      => null
        ),
        array(
                'h' => 'help'
        )
        );

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

if (!$options['report'] || $options['h'] || $options['help']) {
    $help =
    "this script create the data for a single moe report.

    use:
    # php /report/moereport/cli/create_report.php [--report=<report number>]

    Report number options:
    1. course_regin_level - info about each course category for every regain.
    2. course_scoole_level - info about each course category for every school.
    3. activity_regin_level - info about each activity in each course for every regain.
    4. activity_school_level - info about each activity in each course for every school.

    Parameters:
    --report     report number.";

    cli_error($help, 0);
}
global $DB;
if (!get_config('report_moereports', 'moereportsenable')) {
    cli_error('the MOE report not active (are you in Tdigital?)', 0);
}
ini_set('memory_limit', '8200000000000000000000000000');
require_once("/$CFG->libdir/completionlib.php");
switch ($options['report']) {
    case 1:
        // course_regin_level:
        require_once("$CFG->dirroot/report/moereports/classes/local/percoursereginlevel.php");
        $results = new percoursereginlevel();
        $results = $results->displayreportfortemplates();
        $DB->delete_records('moereports_courseregin');
        $DB->insert_records('moereports_courseregin', $results);
        break;
    case 2:
        // course_scoole_level:
        require_once("$CFG->dirroot/report/moereports/classes/local/percourseschoollevel.php");
        $results = new percourseschoollevel();
        $data = new \stdClass();
        $results = $results->displayreportfortemplates();
        $DB->delete_records('moereports_courseschool');
        $DB->insert_records('moereports_courseschool', $results);
        break;
    case 3:
        // activity_regin_level:
        require_once("$CFG->dirroot/report/moereports/classes/local/peractivityreginlevel.php");
        $results = new \peractivityreginlevel();
        $data = new \stdClass();
        $results = $results->displayreportfortemplates();
        $DB->delete_records('moereports_activityregin');
        $DB->insert_records('moereports_activityregin', $results);
        break;
    case 4:
        // activity_school_level:
        require_once("$CFG->dirroot/report/moereports/classes/local/activity_school.php");
        $rep = new \activity_school();
        $results = $rep->display_report();
        $DB->delete_records('moereports_acactivityschool');
        $DB->insert_records('moereports_acactivityschool', $results);
        break;
}
cli_writeln('finish successfully!');
exit();