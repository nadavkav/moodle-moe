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
 * Annotate page. Allows user to add and edit annotations.
 *
 * @copyright &copy; 2017 SysBind
 * @author avi@sysbind.co.il
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moewiki
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/lib/ajax/ajaxlib.php');
require_once($CFG->dirroot.'/mod/moewiki/basicpage.php');

$save = optional_param('submitbutton', '', PARAM_TEXT);
$cancel = optional_param('cancel', '', PARAM_TEXT);
$deleteorphaned = optional_param('deleteorphaned', 0, PARAM_BOOL);
$lockunlock = optional_param('lockediting', false, PARAM_BOOL);

if (!empty($_POST) && !confirm_sesskey()) {
    print_error('invalidrequest');
}

$url = new moodle_url('/local/notes/annotate.php', array('id' => $id));
$PAGE->set_url($url);

if ($id) {
    if (!$cm = get_coursemodule_from_id('notes', $id)) {
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

// Check permission
require_capability('mod/moewiki:annotate', $context);
if (!$subwiki->annotation) {
    $redirect = 'view.php?'.moewiki_display_wiki_parameters($pagename, $subwiki, $cm, MOEWIKI_PARAMS_URL);
    print_error('You do not have permission to annotate this wiki page', 'error', $redirect);
}

// Get the current page version, creating page if needed
$pageversion = moewiki_get_current_page($subwiki, $pagename, MOEWIKI_GETPAGE_ACCEPTNOVERSION);
$wikiformfields = moewiki_display_wiki_parameters($pagename, $subwiki, $cm, MOEWIKI_PARAMS_FORM);

// For everything except cancel we need to obtain a lock.
if (!$cancel) {
    if (!$pageversion) {
        print_error(get_string('startpagedoesnotexist', 'moewiki'));
    }
    // Get lock
    list($lockok, $lock) = moewiki_obtain_lock($moewiki, $pageversion->pageid);
}


$title = get_string('annotatingpage', 'moewiki');
$wikiname = format_string(htmlspecialchars($moewiki->name));
$name = $pagename;
if ($pagename) {
    $title = $wikiname.' - '.$title.' : '.$pagename;
} else {
    $title = $wikiname.' - '.$title.' : '.get_string('startpage', 'moewiki');
}

// Print header
echo $moewikioutput->moewiki_print_start($moewiki, $cm, $course, $subwiki, $pagename, $context,
    array(array('name' => get_string('annotatingpage', 'moewiki'), 'link' => null)),
    false, false, '', $title);

// Tabs
moewiki_print_tabs('annotate', $pagename, $subwiki, $cm, $context, $pageversion->versionid ? true : false, $pageversion->locked);

// prints the div that contains a message when js is disabled in the browser so cannot annotate.
print '<div id="moewiki_belowtabs_annotate_nojs"><p>'.get_string('jsnotenabled', 'moewiki').'</p>'.
        '<div class="moewiki_jsrequired"><p>'.get_string('jsajaxrequired', 'moewiki').'</p></div></div>';

// opens the annotate specific div for when js is enabled in the browser, user can annotate.
print '<div id="moewiki_belowtabs_annotate">';

moewiki_print_editlock($lock, $moewiki);

// Print the annotation that resolved
echo $moewikioutput->show_resolved_annotation($userid, $subwiki);
// close <div id="#moewiki_belowtabs_annotate">
print '</div>';
$PAGE->requires->js_call_amd('mod_notes/resolved', 'reopen',array(array(
    'pagename' => $pagename,
    'subwiki'  => $subwiki,
    'moduleid'       => $id,
)));
// Footer
moewiki_print_footer($course, $cm, $subwiki, $pagename);
