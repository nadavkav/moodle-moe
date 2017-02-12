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
require_once('../../report/moereports/classes/local/peractivityschoollevel.php');

require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/modinfolib.php');

global $OUTPUT;

$url = new moodle_url('/report/moereports/activity_scoole_level.php');
$PAGE->set_url($url);

// Make sure that the user has permissions to manage moe.
require_login();

$context = context_system::instance();
$PAGE->set_context($context);

require_capability('moodle/site:config', $context);

$PAGE->set_title(get_string('per_activity_school_level', 'report_moereports'));
$PAGE->set_heading(get_string('per_activity_school_level', 'report_moereports'));
$PAGE->set_pagelayout('standard');

$results = new peractivityschoollevel();
$data = new stdClass();
$data->results = $results->displayreportfortemplates();

$renderer = $PAGE->get_renderer('core');

$resulttable = $OUTPUT->render_from_template('report_moereports/scool_level', $data);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('per_activity_school_level', 'report_moereports'));
echo $resulttable;
echo $OUTPUT->footer();

