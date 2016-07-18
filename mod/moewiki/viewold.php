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
 * 'View old' page. Displays old versions of wiki pages.
 *
 * @copyright &copy; 2007 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moewiki
 */

require_once(dirname(__FILE__) . '/../../config.php');
require($CFG->dirroot.'/mod/moewiki/basicpage.php');

$id = required_param('id', PARAM_INT);
$versionid = required_param('version', PARAM_INT);

$url = new moodle_url('/mod/moewiki/viewold.php', array('id' => $id, 'version' => $versionid));
$PAGE->set_url($url);

if ($id) {
    if (!$cm = get_coursemodule_from_id('moewiki', $id)) {
        print_error('invalidcoursemodule');
    }

    // Checking course instance
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

    if (!$moewiki = $DB->get_record('moewiki', array('id' => $cm->instance))) {
        print_error('invalidcoursemodule');
    }

    $PAGE->set_cm($cm);
}
$context = context_module::instance($cm->id);
$PAGE->set_pagelayout('incourse');
require_course_login($course, true, $cm);
$moewikioutput = $PAGE->get_renderer('mod_moewiki');

// Get the current page version
$pageversion = moewiki_get_page_version($subwiki, $pagename, $versionid);
if (!$pageversion) {
    print_error('Unknown page version');
}

// Check permission - Allow anyone with delete page capability to view a deleted page version
$candelete = has_capability('mod/moewiki:deletepage', $context);
if (!empty($pageversion->deletedat) && !$candelete) {
    print_error('viewdeletedversionerrorcapability', 'moewiki');
}

// Get previous and next versions
$prevnext = moewiki_get_prevnext_version_details($pageversion);

// Get basic wiki parameters
$wikiparams = moewiki_display_wiki_parameters($pagename, $subwiki, $cm);

$tabhistparams = moewiki_shared_url_params($pagename, $subwiki, $cm);
echo $moewikioutput->moewiki_print_start($moewiki, $cm, $course, $subwiki, $pagename, $context,
    array(
        array('name' => get_string('tab_history', 'moewiki'), 'link' => new moodle_url('/mod/moewiki/history.php', $tabhistparams)),
        array('name' => get_string('oldversion', 'moewiki'), 'link' => null)
    ), true, true);

// Information box
if ($prevnext->prev) {
    $date = moewiki_nice_date($prevnext->prev->timecreated);
    $prev = link_arrow_left(get_string('previousversion', 'moewiki', $date), "viewold.php?$wikiparams&amp;version={$prevnext->prev->versionid}");
} else {
    $prev = '';
}
if ($prevnext->next) {
    if ($prevnext->next->versionid == $pageversion->currentversionid) {
        $date = get_string('currentversion', 'moewiki');
        $next = link_arrow_right(get_string('nextversion', 'moewiki', $date), "view.php?$wikiparams");
    } else {
        $date = moewiki_nice_date($prevnext->next->timecreated);
        $next = link_arrow_right(get_string('nextversion', 'moewiki', $date), "viewold.php?$wikiparams&amp;version={$prevnext->next->versionid}");
    }
} else {
    $next = '';
}
$date = userdate($pageversion->timecreated);
$pageversion->id = $pageversion->userid; // To make it look like a user object
$name = moewiki_display_user($pageversion, $course->id);
$savedby = get_string('savedby', 'moewiki', $name);

$stradvice = get_string('advice_viewold', 'moewiki');
if (!empty($pageversion->deletedat)) {
    $stradvice = get_string('advice_viewdeleted', 'moewiki');
}

print "
<div class='ouw_oldversion'>
  <h1>$date <span class='ouw_person'>($savedby)</span></h1>
  <p>".$stradvice."</p>
  <div class='ouw_prev'>$prev</div>
  <div class='ouw_next'>$next</div>
  <div class='clearer'></div>
</div>";

// Print page content
$data = $moewikioutput->moewiki_print_page($subwiki, $cm, $pageversion, false, 'viewold', $moewiki->enablewordcount);
print($data[0]);

// Add a div to be closed in print_footer, so ensuring div id=osep-pagewrapper (white background)
// is not closed early.
// Note no bottom button here.
echo '<div>';

// Footer
moewiki_print_footer($course, $cm, $subwiki, $pagename);
