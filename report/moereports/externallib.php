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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
class report_moereports_external extends external_api {


    public static function saveclasses_parameters() {

        return new external_function_parameters(
            array('classes' => new external_multiple_structure(
                new external_single_structure(array (
                    new external_value(PARAM_NUMBER, ' ID', false, null, true),
                    new external_value(PARAM_NUMBER, 'School\'s symbol'),
                    new external_value(PARAM_NUMBER, 'School\'s class'),
                    new external_value(PARAM_NUMBER, 'number of students in class'),
                ), 'School\'s fields'),
                'the classes to be saved', VALUE_REQUIRED),
                'del' => new external_multiple_structure(
                    new external_value(PARAM_NUMBER, 'entry Id  to delete'),
                    'the entry be deleted', VALUE_OPTIONAL))
            );
    }

    public static function saveschools_parameters() {

        return new external_function_parameters(
            array('schools' => new external_multiple_structure(
                new external_single_structure(array (
                    new external_value(PARAM_NUMBER, 'School\'s ID', false, null, true),
                    new external_value(PARAM_NUMBER, 'School\'s symbol'),
                    new external_value(PARAM_TEXT, 'School\'s region'),
                    new external_value(PARAM_TEXT, 'School\'s Name'),
                ), 'School\'s fields'),
                'the schools to be saved', VALUE_REQUIRED),
                'del' => new external_multiple_structure(
                    new external_value(PARAM_NUMBER, 'Id of the school to delete'),
                    'the schools to be deleted', VALUE_OPTIONAL))
            );
    }




    public static function saveschools($schools, $deleted) {
        global $DB;
        $records = [];

        for ($i = 0; $i < count($schools); $i++) {
            $tmp = new stdClass();
            $tmp->id = $schools[$i][0];
            $tmp->symbol = $schools[$i][1];
            $tmp->region = $schools[$i][2];
            $tmp->name = $schools[$i][3];

            $records[] = $tmp;
        }

        foreach ($deleted as $record) {
            $DB->delete_records('moereports_reports', ["id" => $record]);
        }

        $return->inserted = 0;
        $return->existed = 0;
        foreach ($records as $linenumber => $record) {
            $rec = null;
            if (empty($record->id)) {
                if (empty($record->name) || empty($record->region)) {
                    $return->message = "missingschoolfield";
                }
                if ($return->message) {
                    $validrecords = false;
                    break;
                }
            }
            if (! $return->message) {
                $symbol = $DB->get_field('moereports_reports', 'symbol', array('symbol' => $record->symbol));
                if ($symbol) {
                    $rec = $DB->get_record('moereports_reports', array('symbol' => $record->symbol));
                }
                if ($rec) {
                    $rec->symbol = $record->symbol;
                    $rec->name = $record->name;
                    $rec->region = $record->region;
                    $DB->update_record('moereports_reports', $rec);
                    $return->existed ++;
                    continue;
                }
                $school = new stdClass();
                $school->symbol = $record->symbol;
                $school->region = $record->region;
                $school->name = $record->name;

                $DB->insert_record('moereports_reports', $school);
                $return->inserted ++;
            }
        }
        $reports = $DB->get_records('moereports_reports');
        $schools = [];

        foreach ($reports as $key => $report) {
            // Set the fields.
            $tmp = [];
            $tmp[] = $report->id;
            $tmp[] = $report->symbol;
            $tmp[] = $report->region;
            $tmp[] = $report->name;

            // Add the current group to the groups array.
            $schools[] = $tmp;
        }
        return $schools;
    }
    public static function saveclasses($classes, $deleted) {
        global $DB;
        $records = [];

        for ($i = 0; $i < count($classes); $i++) {
            $tmp = new stdClass();
            $tmp->id = $classes[$i][0];
            $tmp->symbol = $classes[$i][1];
            $tmp->class = $classes[$i][2];
            $tmp->studentsnumber = $classes[$i][3];

            $records[] = $tmp;
        }

        foreach ($deleted as $record) {
            $DB->delete_records('moereports_reports_classes', ["id" => $record]);
        }

        $return->inserted = 0;
        $return->existed = 0;
        foreach ($records as $linenumber => $record) {
            $rec = null;
            if (empty($record->id)) {
                if (empty($record->class) || empty($record->studentsnumber) || empty($record->symbol)) {
                    $return->message = "missingschoolfield";
                }
                if ($return->message) {
                    $validrecords = false;
                    break;
                }
            }
            if (! $return->message) {

                if (!empty($record->id)) {
                    $rec = $DB->get_record('moereports_reports_classes', array('id' => $record->id));
                }

                if ($rec) {
                    $rec->symbol = $record->symbol;
                    $rec->class = $record->class;
                    $rec->studentsnumber = $record->studentsnumber;

                    $DB->update_record('moereports_reports_classes', $rec);

                    $return->existed ++;
                    continue;
                }

                $class = new stdClass();
                $class->class = $record->class;
                $class->studentsnumber = $record->studentsnumber;
                $class->id = $record->id;
                $class->symbol = $record->symbol;

                $DB->insert_record('moereports_reports_classes', $class);
                $return->inserted ++;
            }
        }
        $reports = $DB->get_records('moereports_reports_classes');
        $schools = [];

        foreach ($reports as $key => $report) {
            // Set the fields.
            $tmp = [];
            $tmp[] = $report->id;
            $tmp[] = $report->symbol;
            $tmp[] = $report->class;
            $tmp[] = $report->studentsnumber;

            // Add the current group to the groups array.
            $classes[] = $tmp;
        }
        // Trigger a report viewed event.
        $context = context_system::instance();
        global $USER;
        $event = \report_moereports\event\classes_report_viewed::create(array('context' => $context, 'relateduserid' => $USER->id, 'other' => array('date' => time())));
        $event->trigger();
        return $classes;
    }
    public static function saveschools_returns() {
        return new external_multiple_structure(
            new external_single_structure(array (
                new external_value(PARAM_NUMBER, 'School\'s ID'),
                new external_value(PARAM_NUMBER, 'School\'s symbol'),
                new external_value(PARAM_TEXT, 'School\'s region'),
                new external_value(PARAM_TEXT, 'School\'s name'),
            ), 'region\'s fields'),
            'the saveschools to be saved', VALUE_REQUIRED);
    }

    public static function saveclasses_returns() {
        return new external_multiple_structure(
            new external_single_structure(array (
                    new external_value(PARAM_NUMBER, 'School\'s ID'),
                    new external_value(PARAM_NUMBER, 'School\'s symbol'),
                    new external_value(PARAM_NUMBER, 'School\'s class'),
                    new external_value(PARAM_NUMBER, 'number of students in class'),
            ), ' fields'),
            'the classes to be saved', VALUE_REQUIRED);
    }


}