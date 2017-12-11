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

use block_import_remote_course\form\clone_form;
use block_import_remote_course\local\notification_helper;
use block_import_remote_course\local\course_template;
/**
 * Block import_remote_course
 *
 * Display a list of courses to be imported from a remote Moodle system
 * Using a local/remote_backup_provider plugin (dependency)
 *
 * @package    block_import_remote_course
 * @copyright  Nadav Kavalerchik <nadavkav@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_import_remote_course extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_import_remote_course');
    }

    function get_content() {
        global $COURSE, $CFG, $DB, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance) || !has_capability('block/import_remote_course:view', $this->page->context)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        if ($this->page->course->id == SITEID) {
            $this->context->text = "Only available in a course";
            return $this->content;
        }

        // user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);

        if (empty($currentcontext)) {
            return $this->content;
        }

		$coursetemplate = course_template::get_record(['course_id' => $COURSE->id]);
		if (!$coursetemplate) {
	        $remotecourselist = array();
	        $tags = core_tag_tag::get_item_tags_array('core', 'course', $COURSE->id);
	        foreach ($tags as $tag) {
	            $select = $DB->sql_compare_text('course_tag') . " = '" . $tag ."'";
	            $remotecourselist = array_merge($remotecourselist,$DB->get_records_select('import_remote_course_list', $select));
	        }
	        if (empty($remotecourselist)) {
	            $this->content->text = get_string('noavailablecourses', 'block_import_remote_course');
	            return $this->content;
	        }

	        $form = '<form id="restoreremotecourse" action="'.
	            $CFG->wwwroot.'/blocks/import_remote_course/import_remote_course.php" type="get">';
	        $form .= get_string('choosecourse').' <select id="choosecourse" name="remotecourseid">';
	        $form .= "<option value='' selected disabled>" . get_string('selectoption', 'block_import_remote_course') . "</option>";
	        foreach ($remotecourselist as $course) {
	            $courseid   = $course->course_id;
	            $coursename = $course->course_name;
	            $form .= "<option value='$courseid'>$coursename</option>";
	        }
	        $form .= '</select>';
	        $form .= '<input type="hidden" name="destcourseid" value="'.$COURSE->id.'">';
	        $form .= '<input type="hidden" name="sessionid" value="'.session_id().'">';
	        //$form .= '<input type="submit" value="'.get_string("restore").'" onsubmit="Y(\'div.inprogress\').removeClass(\'hide\')">';
	        if(count(get_fast_modinfo($COURSE->id)->cms) <= 1){
	            $form .= '<input id="restorebutton" type="button" value="'.get_string("restore", 'block_import_remote_course').'" ' .
	                'onclick="Y.one(\'div.inprogress\').removeClass(\'hide\');document.forms[\'restoreremotecourse\'].submit();">';
	        } else {
	            $form .= '<input id="restorebutton" type="button" value="'.get_string("restore", 'block_import_remote_course').'" ' .
	                'onclick="Y.one(\'form#restoreremotecourse\').append(\''."<br>" . get_string('courseisnotempty', 'block_import_remote_course') . '\');Y.one(\'input#restorebutton\').remove();">';
	        }
	        $form .= '</form>';
	        $form .= '<a id="importsite" target="_blank" href="' . get_config('block_import_remote_course', 'testenv') . '" class="btn">' . get_string('trytemplates', 'block_import_remote_course') . '</a>';

	        $form .= html_writer::start_div('inprogress hide');
	            $form .= html_writer::start_div('notice');
	            $form .= get_string('restoreinprogress', 'block_import_remote_course');
	            $form .= html_writer::end_div();
	            $form .= html_writer::start_div('notice');
	            $form .= html_writer::img($CFG->wwwroot.'/blocks/import_remote_course/pix/download.gif',
	                get_string('restoreinprogress', 'block_import_remote_course'));
	            $form .= html_writer::end_div();
	        $form .= html_writer::end_div();

	        // If we have more then one (probably the "news forum") module in the course,
	        // Display a warrening, and prevent restore.

	        if(!($this->content instanceof  stdClass)) {
	            $this->content = new stdClass();
	        }
	        if(isset($this->content->text)){
	            $this->content->text .= $form;
	        } else {
	            $this->content->text = $form;
	        }

	        return $this->content;
    } else {
    	global $PAGE, $DB, $COURSE, $USER, $PAGE;
    	$renderer = $PAGE->get_renderer('core');
    	$context = new stdClass();
    	$notifications = notification_helper::get_records(['courseid' => $COURSE->id]);

    	$context->tamplatename = get_string('blockeheader','block_import_remote_course', $coursetemplate->get_template_name());
    	$context->newitems = get_string('newact','block_import_remote_course', notification_helper::count_records_select("type = ? and courseid= ? ", array('new', $COURSE->id)));
    	$context->updatesstr = get_string('updateact','block_import_remote_course', notification_helper::count_records_select("type = ? and courseid= ? ", array('update', $COURSE->id)));
    	$context->deleteitemsstr = get_string('deleteitems','block_import_remote_course', notification_helper::count_records_select("type = ? and courseid= ? ", array('delete', $COURSE->id)));
    	$context->tetstenvurl = get_config('block_import_remote_course', 'testenv');

    	$modnames = get_module_types_names();
    	$modules = get_module_metadata($COURSE, $modnames, 0);
    	$mods = notification_helper::get_records_select("type = ? and courseid= ? ", array('new', $COURSE->id), 'section');
    	$newactivities = [];
    	if ($mods) {
	    	foreach ($mods as $mod) {
	    	    $activity 			  = new stdClass();
	    		$activity->url        = get_config('local_remote_backup_provider', 'remotesite') . '/mod/' . $mod->get('module') . '/view.php?id=' . $mod->get('cm');
	    		$activity->time_added = date('d-m-Y H:i:s', $mod->get('timecreated'));
	    		$localmod		      = $modules[$mod->get('module')];
	    		$activity->iconsrc 	  = $localmod->icon;
	    		$activity->type 	  = $localmod->title;
	    		$activity->name 	  = $mod->get('name');
	    		$activity->cmid 	  = $mod->get('cm');
	    		$activity->section 	  = $mod->get('section');
                $newactivities[]	  = $activity;
	    	}
		    $newactivitieslist = [];
		    $i = 0;
		    foreach ($newactivities as $neact) {
		    	if (isset($newactivitieslist[$i]['sectionname']) && $newactivitieslist[$i]['sectionname'] != $neact->section) {
		    		$i++;
		    	}
		    	$newactivitieslist[$i]['sectionname'] = $neact->section;
		    	$newactivitieslist[$i]['child'][] = $neact;
		    }
    	}
        $mods = notification_helper::get_records_select("type = ? and courseid= ? ", array('update', $COURSE->id), 'section');
    	$updateactivities = [];
    	if ($mods) {
	    	foreach ($mods as $mod) {
	    	    $activity = new stdClass();
	    		$activity->url = get_config('local_remote_backup_provider', 'remotesite') . '/mod/' . $mod->get('module') . '/view.php?id=' . $mod->get('cm');
	    		$activity->time_added = date('d-m-Y H:i:s', $mod->get('timecreated'));
	    		$localmod = $modules[$mod->get('module')];
	    		$activity->iconsrc = $localmod->icon;
	    		$activity->type = $localmod->title;
	    		$activity->name = $mod->get('name');
	    		$activity->cmid = $mod->get('cm');
                $updateactivities[] = $activity;
	    	}
    	}
    	$updateactivitieslist = [];
    	$i = 0;
    	foreach ($updateactivities as $upcat) {
    		if (isset($updateactivitieslist[$i]['sectionname']) && $updateactivitieslist[$i]['sectionname'] != $upcat->section) {
    			$i++;
    		}
    		$updateactivitieslist[$i]['sectionname'] = $upcat->section;
    		$updateactivitieslist[$i]['child'][] = $upcat;
    	}
    	/*
    	$mods = notification_helper::get_records_select("type = ? and courseid= ? ", array('delete', $COURSE->id));
    	$deleteactivities = [];
    	if ($mods) {
    		foreach ($mods as $mod) {
    			$activity = new stdClass();
    			$localmod = $modules[$mod->get('module')];
    			$activity->iconsrc = $localmod->icon;
    			$activity->type = $localmod->title;
    			$activity->name = $mod->get('name');
    			$activity->cmid = $mod->get('cm');
    			$deleteactivities[] = $activity;
    		}
    	}
    	$deletectivitieslist = [];
    	$i = 0;
    	foreach ($deleteactivities as $delcat) {
    		if (isset($deletectivitieslist[$i]['sectionname']) && $deletectivitieslist[$i]['sectionname'] != $delcat->section) {
    			$i++;
    		}
    		$deletectivitieslist[$i]['sectionname'] = $delcat->section;
    		$deletectivitieslist[$i]['child'][] = $delcat;
    	}
    	$context->deletedmods = array_values($deleteactivities);
    	$context->deletedmodsbutton = count($deleteactivities) > 0 ? true : false;

    	*/
    	
    	//mods display
    	$context->newmods = array_values($newactivitieslist);
    	$context->updatemods = array_values($updateactivitieslist);   	
    	$context->newmodsbutton = count($newactivities) > 0 ? true : false;
    	$context->updatemodsbutton = count($updateactivities) > 0 ? true : false;
    	
    	$this->content = new stdClass();
    	$modlist = $renderer->render_from_template('block_import_remote_course/modlist', $context);
    	$this->content->text = $renderer->render_from_template('block_import_remote_course/block_notification_view', $context);

    	$PAGE->requires->js_call_amd('block_import_remote_course/import_helper', 'init', [
    	    'courseid' => $COURSE->id,
    	]);
    	$context = context_course::instance($COURSE->id);
    	if (has_capability('block/import_remote_course:clon', $context)){
    		$importform = new clone_form($CFG->wwwroot . '/blocks/import_remote_course/clone.php');
    		$importform->set_data(['course' => $COURSE->id]);
    		$this->content->footer = $importform->render();
    	}
    	return $this->content;
    }
}


    // Block is available only on course pages.
    public function applicable_formats() {
        return array('all' => false,
                     'course-view' => true,
                     'category-view' => true);
    }

    // Block can appear only once, in a course.
    public function instance_allow_multiple() {
          return false;
    }

    // Block has course-level config.
    // todo: migrate to site level.
    function has_config() {
        return true;
    }
}
