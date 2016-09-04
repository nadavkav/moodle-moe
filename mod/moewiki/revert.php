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
 * Confirms reverting to previous version
 * when confirmed, reverts to previous version then redirects back to that page.
 * @copyright &copy; 2008 SysBind
 * @author avi@sysbind.co.il
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moewiki
 */

require_once(dirname(__FILE__) . '/../../config.php');
require($CFG->dirroot.'/mod/moewiki/basicpage.php');

$id = required_param('id', PARAM_INT);
$versionid = required_param('version', PARAM_INT);
$confirmed = optional_param('confirm', null, PARAM_TEXT);
$cancelled = optional_param('cancel', null, PARAM_TEXT);

$url = new moodle_url('/mod/moewiki/view.php', array('id' => $id, 'page' => $pagename));
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

// Get the page version to be reverted back to (must not be deleted page version)
$pageversion = moewiki_get_page_version($subwiki, $pagename, $versionid);
if (!$pageversion || !empty($pageversion->deletedat)) {
    print_error('reverterrorversion', 'moewiki');
}

// Check for cancel
if (isset($cancelled)) {
    redirect('history.php?'.moewiki_display_wiki_parameters($pagename, $subwiki, $cm, MOEWIKI_PARAMS_URL));
    exit;
}

// Check permission - Allow anyone with edit capability to revert to a previous version
$canrevert = has_capability('mod/moewiki:edit', $context);
if (!$canrevert) {
    print_error('reverterrorcapability', 'moewiki');
}

// Check if reverting to previous version has been confirmed
if ($confirmed) {

    // Lock something - but maybe this should be the current version
    list($lockok, $lock) = moewiki_obtain_lock($moewiki, $pageversion->pageid);

    // Revert to previous version
    moewiki_save_new_version($course, $cm, $moewiki, $subwiki, $pagename, $pageversion->xhtml, -1, -1, -1, null, null, $pageversion->versionid);

    // Unlock whatever we locked
    moewiki_release_lock($pageversion->pageid);

    // Redirect to view what is now the current version
    redirect('view.php?'.moewiki_display_wiki_parameters($pagename, $subwiki, $cm, MOEWIKI_PARAMS_URL));
    exit;

} else {
    // Display confirm form
    $nav = get_string('revertversion', 'moewiki');
    echo $moewikioutput->moewiki_print_start($moewiki, $cm, $course, $subwiki, $pagename, $context, array(array('name' => $nav, 'link' => null)), true, true);

    $date = moewiki_nice_date($pageversion->timecreated);
    print get_string('revertversionconfirm', 'moewiki', $date);
    print '<form action="revert.php" method="post">';
    print moewiki_display_wiki_parameters($pagename, $subwiki, $cm, MOEWIKI_PARAMS_FORM);
    print
        '<input type="hidden" name="version" value="'.$versionid.'" />'.
        '<input type="submit" name="confirm" value="'.get_string('revertversion', 'moewiki').'"/> '.
        '<input type="submit" name="cancel" value="'.get_string('cancel').'"/>';
    print '</form>';

    // Footer
    moewiki_print_footer($course, $cm, $subwiki, $pagename);
}
