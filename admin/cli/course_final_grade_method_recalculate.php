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
 * script for recalculate courses final score method in category
 * @subpackage cli
 * @copyright  2018 meir@sysbind.co.il
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/clilib.php');
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/lib.php';
// Now get cli options.
list($options, $unrecognized) = cli_get_params(
		array(
		        'category' => false,
				'h'        => null,
				'help'     => null
		),
		array(
				'h' => 'help'
		)
		);

if ($unrecognized) {
	$unrecognized = implode("\n  ", $unrecognized);
	cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

if (!$options['category'] || $options['h'] || $options['help']) {
	$help =
	"script for recalculate courses final score method in category.

	use:
    # php course_final_grade_method_recalculate.php --Parameter=<Parameter>

	Parameters:
    --method     final course score method to change to.
    --category   category id where the course final score method need to change (all courses in category will change!)";
	    
	cli_error($help, 0);
}

global $DB;
$total = 0;
$success = 0;
$settings = array(
    'aggregateonlygraded' => '1',
    'aggregateoutcomes' => '0',
    'aggregation' => "10",
    'droplow' => 0,
    'fullname' => "",
    'gpr_plugin' => "tree",
    'gpr_type' => "edit",
    'grade_item_decimals' => "-1",
    'grade_item_display' => "0",
    'grade_item_grademax' => "100",
    'grade_item_grademin' => "0",
    'grade_item_gradepass' => "0.00",
    'grade_item_gradetype' => "1",
    'grade_item_hiddenuntil' => 0,
    'grade_item_idnumber' => "",
    'grade_item_iteminfo' => "",
    'grade_item_itemname' => "",
    'grade_item_locktime' => 0,
);

$catcontex = context_coursecat::instance($options['category']);
$select = "contextlevel = ? AND path like ?";
$courses = $DB->get_records_select('context', $select, [CONTEXT_COURSE, $catcontex->path . '/%']);
$total = count($courses);
cli_writeln("found $total courses in category tree");

foreach ($courses as $course) {
    $course = $course->instanceid;
    cli_writeln("course $course:");
    $courseid             = $course;
    $data                 = $settings;
    $data['courseid']     = $course;
    $data['gpr_courseid'] = $course;
    $grade_categorys      = $DB->get_records('grade_categories', ['courseid' => $course, 'parent' => NULL]);
    cli_writeln("found " .count($grade_categorys)." grades category");
    foreach ($grade_categorys as $grade_category) {
        $data['id'] = $grade_category->id;
        $data = (object)$data;
        cli_writeln("grades category $grade_category->id:");
        
        $grade_category = grade_category::fetch(array('id'=>$grade_category->id, 'courseid'=>$course));
        $grade_category->apply_forced_settings();
        $category = $grade_category->get_record_data();
        // set parent
        $category->parentcategory = $grade_category->parent;
        $grade_item = $grade_category->load_grade_item();
        // nomalize coef values if needed
        $parent_category = $grade_category->get_parent_category();
        
        foreach ($grade_item->get_record_data() as $key => $value) {
            $category->{"grade_item_$key"} = $value;
        }
        
        $decimalpoints = $grade_item->get_decimals();
        
        $category->grade_item_grademax   = format_float($category->grade_item_grademax, $decimalpoints);
        $category->grade_item_grademin   = format_float($category->grade_item_grademin, $decimalpoints);
        $category->grade_item_gradepass  = format_float($category->grade_item_gradepass, $decimalpoints);
        $category->grade_item_multfactor = format_float($category->grade_item_multfactor, 4);
        $category->grade_item_plusfactor = format_float($category->grade_item_plusfactor, 4);
        $category->grade_item_aggregationcoef2 = format_float($category->grade_item_aggregationcoef2 * 100.0, 4);
        
        if (!$parent_category) {
            // keep as is
        } else if ($parent_category->aggregation == GRADE_AGGREGATE_SUM or $parent_category->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2) {
            $category->grade_item_aggregationcoef = $category->grade_item_aggregationcoef == 0 ? 0 : 1;
        } else {
            $category->grade_item_aggregationcoef = format_float($category->grade_item_aggregationcoef, 4);
        }
        // Check to see if the gradebook is frozen. This allows grades to not be altered at all until a user verifies that they
        // wish to update the grades.
        $gradebookcalculationsfreeze = get_config('core', 'gradebook_calculations_freeze_' . $courseid);
        // Stick with the original code if the grade book is frozen.
        if ($gradebookcalculationsfreeze && (int)$gradebookcalculationsfreeze <= 20150627) {
            if ($category->aggregation == GRADE_AGGREGATE_SUM) {
                // Input fields for grademin and grademax are disabled for the "Natural" category,
                // this means they will be ignored if user does not change aggregation method.
                // But if user does change aggregation method the default values should be used.
                $category->grademax = 100;
                $category->grade_item_grademax = 100;
                $category->grademin = 0;
                $category->grade_item_grademin = 0;
            }
        } else {
            if ($category->aggregation == GRADE_AGGREGATE_SUM && !$grade_item->is_calculated()) {
                // Input fields for grademin and grademax are disabled for the "Natural" category,
                // this means they will be ignored if user does not change aggregation method.
                // But if user does change aggregation method the default values should be used.
                // This does not apply to calculated category totals.
                $category->grademax = 100;
                $category->grade_item_grademax = 100;
                $category->grademin = 0;
                $category->grade_item_grademin = 0;
            }
        }
        //now implement the specific settings   
        // If no fullname is entered for a course category, put ? in the DB
        if (!isset($data->fullname) || $data->fullname == '') {
            $data->fullname = '?';
        }
        
        if (!isset($data->aggregateonlygraded)) {
            $data->aggregateonlygraded = 0;
        }
        if (!isset($data->aggregateoutcomes)) {
            $data->aggregateoutcomes = 0;
        }
        grade_category::set_properties($grade_category, $data);
        
        /// CATEGORY
        if (empty($grade_category->id)) {
            $grade_category->insert();
            
        } else {
            $grade_category->update();
        }
        
        /// GRADE ITEM
        // grade item data saved with prefix "grade_item_"
        $itemdata = new stdClass();
        foreach ($data as $k => $v) {
            if (preg_match('/grade_item_(.*)/', $k, $matches)) {
                $itemdata->{$matches[1]} = $v;
            }
        }
        
        if (!isset($itemdata->aggregationcoef)) {
            $itemdata->aggregationcoef = 0;
        }
        
        if (!isset($itemdata->gradepass) || $itemdata->gradepass == '') {
            $itemdata->gradepass = 0;
        }
        
        if (!isset($itemdata->grademax) || $itemdata->grademax == '') {
            $itemdata->grademax = 0;
        }
        
        if (!isset($itemdata->grademin) || $itemdata->grademin == '') {
            $itemdata->grademin = 0;
        }
        
        $hidden      = empty($itemdata->hidden) ? 0: $itemdata->hidden;
        $hiddenuntil = empty($itemdata->hiddenuntil) ? 0: $itemdata->hiddenuntil;
        unset($itemdata->hidden);
        unset($itemdata->hiddenuntil);
        
        $locked   = empty($itemdata->locked) ? 0: $itemdata->locked;
        $locktime = empty($itemdata->locktime) ? 0: $itemdata->locktime;
        unset($itemdata->locked);
        unset($itemdata->locktime);
        
        $convert = array('grademax', 'grademin', 'gradepass', 'multfactor', 'plusfactor', 'aggregationcoef', 'aggregationcoef2');
        foreach ($convert as $param) {
            if (property_exists($itemdata, $param)) {
                $itemdata->$param = unformat_float($itemdata->$param);
            }
        }
        if (isset($itemdata->aggregationcoef2)) {
            $itemdata->aggregationcoef2 = $itemdata->aggregationcoef2 / 100.0;
        }
        
        // When creating a new category, a number of grade item fields are filled out automatically, and are required.
        // If the user leaves these fields empty during creation of a category, we let the default values take effect
        // Otherwise, we let the user-entered grade item values take effect
        $grade_item = $grade_category->load_grade_item();
        $grade_item_copy = fullclone($grade_item);
        grade_item::set_properties($grade_item, $itemdata);
        
        if (empty($grade_item->id)) {
            $grade_item->id = $grade_item_copy->id;
        }
        if (empty($grade_item->grademax) && $grade_item->grademax != '0') {
            $grade_item->grademax = $grade_item_copy->grademax;
        }
        if (empty($grade_item->grademin) && $grade_item->grademin != '0') {
            $grade_item->grademin = $grade_item_copy->grademin;
        }
        if (empty($grade_item->gradepass) && $grade_item->gradepass != '0') {
            $grade_item->gradepass = $grade_item_copy->gradepass;
        }
        if (empty($grade_item->aggregationcoef) && $grade_item->aggregationcoef != '0') {
            $grade_item->aggregationcoef = $grade_item_copy->aggregationcoef;
        }
        
        // Handle null decimals value - must be done before update!
        if (!property_exists($itemdata, 'decimals') or $itemdata->decimals < 0) {
            $grade_item->decimals = null;
        }
        
        // Change weightoverride flag. Check if the value is set, because it is not when the checkbox is not ticked.
        $itemdata->weightoverride = isset($itemdata->weightoverride) ? $itemdata->weightoverride : 0;
        if ($grade_item->weightoverride != $itemdata->weightoverride && $grade_category->aggregation == GRADE_AGGREGATE_SUM) {
            // If we are using natural weight and the weight has been un-overriden, force parent category to recalculate weights.
            $grade_category->force_regrading();
        }
        $grade_item->weightoverride = $itemdata->weightoverride;
        
        $grade_item->outcomeid = null;
        
        if (!empty($data->grade_item_rescalegrades) && $data->grade_item_rescalegrades == 'yes') {
            $grade_item->rescale_grades_keep_percentage($grade_item_copy->grademin, $grade_item_copy->grademax, $grade_item->grademin,
                $grade_item->grademax, 'gradebook');
        }
        
        // update hiding flag
        if ($hiddenuntil) {
            $grade_item->set_hidden($hiddenuntil, false);
        } else {
            $grade_item->set_hidden($hidden, false);
        }
        
        $grade_item->set_locktime($locktime); // locktime first - it might be removed when unlocking
        $grade_item->set_locked($locked, false, true);
        
        $grade_item->update(); // We don't need to insert it, it's already created when the category is created
        
        // set parent if needed
        if (isset($data->parentcategory)) {
            $grade_category->set_parent($data->parentcategory, 'gradebook');
        }
        unset($data);
        cli_writeln('Finish!');
        $success++;
        cli_writeln("-------------------------------------------------------");
    }
}
cli_writeln("finish convert successfully!");
cli_writeln("total courses process: $total");
cli_writeln("total courses processed successfully: $success");
exit(0);



