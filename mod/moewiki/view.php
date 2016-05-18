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
 * View page. Displays wiki pages.
 *
 * @copyright &copy; 2007 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moewiki
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/lib/ajax/ajaxlib.php');

require($CFG->dirroot.'/mod/moewiki/basicpage.php');

$url = new moodle_url('/mod/moewiki/view.php', array('id' => $id, 'page' => $pagename));
$PAGE->set_url($url);
$PAGE->set_cm($cm);

$context = context_module::instance($cm->id);
$PAGE->set_pagelayout('incourse');
require_course_login($course, true, $cm);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$moewikioutput = $PAGE->get_renderer('mod_moewiki');

// Get the current page version
$pageversion = moewiki_get_current_page($subwiki, $pagename);

if ($pageversion) {
    $moewikioutput->set_export_button('page', $pageversion->pageid, $course->id);
}
echo $moewikioutput->moewiki_print_start($moewiki, $cm, $course, $subwiki, $pagename, $context);

// Check consistency in setting subwikis and group mode
$courselink = new moodle_url('/course/view.php?id=', array('id' =>  $cm->course));
if (($cm->groupmode == 0) && isset($subwiki->groupid)) {
    print_error("Sub-wikis is set to 'One wiki per group'.
        Please change Group mode to 'Separate groups' or 'Visible groups'.", 'error', $courselink);
}
if (($cm->groupmode > 0) && !isset($subwiki->groupid)) {
    print_error("Sub-wikis is NOT set to 'One wiki per group'.
        Please change Group mode to 'No groups'.", 'error', $courselink);
}

$locked = ($pageversion) ? $pageversion->locked : false;

moewiki_print_tabs('view', $pagename, $subwiki, $cm, $context, $pageversion ? true : false, $locked);

if (($pagename === '' || $pagename === null) && strlen(preg_replace('/\s|<br\s*\/?>|<p>|<\/p>/',
        '', $moewiki->intro)) > 0) {
    echo $moewikioutput->moewiki_get_intro($moewiki->intro, $context->id);
}

if ($pageversion) {
    // Print warning if page is large (more than 75KB)
    if (strlen($pageversion->xhtml) > 75 * 1024) {
        print '<div class="moewiki-sizewarning"><img src="' . $OUTPUT->pix_url('warning', 'moewiki') .
                '" alt="" />' . get_string('sizewarning', 'moewiki') .
                '</div>';
    }
    // Print page content
    $hideannotations = get_user_preferences(MOEWIKI_PREF_HIDEANNOTATIONS, 0);
    $data = $moewikioutput->moewiki_print_page($subwiki, $cm, $pageversion, true, 'view',
            $moewiki->enablewordcount, (bool)$hideannotations);
    echo $data[0];
    echo $moewikioutput->moewiki_get_addnew($subwiki, $cm, $pageversion, $context, $id, $pagename);
    echo $moewikioutput->get_bottom_buttons($subwiki, $cm, $context, $pageversion, true);
} else {
    // Page does not exist
    print '<p>'.get_string($pagename ? 'pagedoesnotexist' : 'startpagedoesnotexist', 'moewiki').'</p>';
    if ($subwiki->canedit) {
        print '<p>'.get_string('wouldyouliketocreate', 'moewiki').'</p>';
        print "<form method='get' action='edit.php'>";
        print moewiki_display_wiki_parameters($pagename, $subwiki, $cm, MOEWIKI_PARAMS_FORM);
        print "<input type='submit' value='".get_string('createpage', 'moewiki')."' /></form>";
    }
}

if ($timelocked = moewiki_timelocked($subwiki, $moewiki, $context)) {
    print '<div class="ouw_timelocked">'.$timelocked.'</div>';
}

// init JS module
$stringlist[] = array('typeinsectionname', 'moewiki');
$stringlist[] = array('typeinpagename', 'moewiki');
$stringlist[] = array('collapseannotation', 'moewiki');
$stringlist[] = array('expandannotation', 'moewiki');
$jsmodule = array('name'     => 'mod_moewiki_view',
                  'fullpath' => '/mod/moewiki/module.js',
                  'requires' => array('base', 'event', 'io', 'node', 'anim', 'panel'),
                  'strings'  => $stringlist
                 );
$PAGE->requires->js_init_call('M.mod_moewiki_view.init', array(), true, $jsmodule);
$userid = ($userid == 0) ? $USER->id : $userid;
$PAGE->requires->js_call_amd('mod_moewiki/annotation', 'merkannotaion',array(array(
    'wikiid'   => $id,
    'userid'   => $USER->id,
    'userpage' => $userid,
)));
$PAGE->requires->strings_for_js(array('annotation'),'mod_moewiki');
// Footer
moewiki_print_footer($course, $cm, $subwiki, $pagename);
