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
use report_moereports\form\add_school_by_csv;
require_once($CFG->libdir.'/csvlib.class.php');

$url = new moodle_url('/reports/moereports/add_schools_by_csv.php');
global $DB;
$PAGE->set_url($url);

// Make sure that the user has permissions to manage moe.
require_login();

$context = context_system::instance();

$PAGE->set_context($context);

require_capability('moodle/site:config', $context);

$PAGE->set_title(get_string('reports', 'report_moereports'));
$PAGE->set_heading(get_string('reports', 'report_moereports'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reports', 'report_moereports'));

$mform = new add_school_by_csv();
echo $mform->render();
if ($formdata = $mform->get_data()) {
    $iid = csv_import_reader::get_new_iid('uploadschools');
    $cir = new csv_import_reader($iid, 'uploadschools');
    $content = $mform->get_file_content('userfile');
    $readcount = $cir->load_csv_content($content, 'utf-8', 'comma');
    $cir->init();
    $row = $cir->next();
    if (isset($row) && $row ) {
        $DB->delete_records('moereports_reports_classes');
        $update = true;
    }
    while ($row) {
        if ( $DB->record_exists('moereports_reports', ['symbol' => $row[1]])) {
            $updateid = $DB->get_field('moereports_reports', 'id', ['symbol' => $row[1]]);
            $dataobject = new stdClass();
            $dataobject->id = $updateid;
            $dataobject->name = $row[2];
            $dataobject->region = $row[4];
            $DB->update_record('moereports_reports', $dataobject);
        } else {
            $dataobject = new stdClass();
            $dataobject->symbol = $row[1];
            $dataobject->name = $row[2];
            $dataobject->region = $row[4];
            $DB->insert_record('moereports_reports', $dataobject);
        }
        $dataobject = new stdClass();
        $dataobject->symbol = $row[1];
        $dataobject->class = $row[5];
        $dataobject->studentsnumber = $row[6];
        $DB->insert_record('moereports_reports_classes', $dataobject);
        $row = $cir->next();
    }
    if($update){
        echo get_string('Succeeded','report_moereports');
    }
}




echo $OUTPUT->footer();