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
namespace report_moereports\task;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/modinfolib.php');
require_once("$CFG->dirroot/report/moereports/classes/local/peractivityreginlevel.php");
require_once("$CFG->dirroot/report/moereports/classes/local/percoursereginlevel.php");
require_once("$CFG->dirroot/report/moereports/classes/local/percourseschoollevel.php");
require_once("$CFG->dirroot/report/moereports/classes/local/activity_school.php");


use core\task\scheduled_task;

/**
 *
 * @author Meir
 *
 */
class report_data_generate extends \core\task\scheduled_task {
    /**
     * {@inheritDoc}
     * @see \core\task\scheduled_task::get_name()
     */
    public function get_name() {
        return get_string('cron_name', 'report_moereports');

    }

    /**
     * {@inheritDoc}
     * @see \core\task\task_base::execute()
     */
    public function execute() {
        global $DB;

        // course_regin_level:
        $results = new \percoursereginlevel();
        $data = new \stdClass();
        $data->results = $results->displayreportfortemplates();
        $DB->delete_records('moereports_courseregin');
        $DB->insert_records('moereports_courseregin', $data->results);

        // course_scoole_level:
        $results = new \percourseschoollevel();
        $data = new \stdClass();
        $data->results = $results->displayreportfortemplates();
        $DB->delete_records('moereports_courseschool');
        $DB->insert_records('moereports_courseschool', $data->results);

        // activity_regin_level:
        $results = new \peractivityreginlevel();
        $data = new \stdClass();
        $data->results = $results->displayreportfortemplates();
        $DB->delete_records('moereports_activityregin');
        $DB->insert_records('moereports_activityregin', $data->results);

        // activity_school_level:
        $rep = new \activity_school();
        $results = $rep->display_report();
        $DB->delete_records('moereports_acactivityschool');
        $DB->insert_records('moereports_acactivityschool', $results->results);

    }
}