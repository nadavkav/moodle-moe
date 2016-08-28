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
 * Lock editing page. Allows user to lock or unlock the editing of a wiki page
 *
 * @copyright &copy; 2009 SysBind
 * @author avi@sysbind.co.il
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moewiki
 */

require_once(dirname(__FILE__) . '/../../config.php');
require($CFG->dirroot.'/mod/moewiki/basicpage.php');

$id = required_param('id', PARAM_INT);           // Course Module ID that defines wiki

// check we are using the annotation system
$action = required_param('ouw_lock', PARAM_RAW);
$pageid = required_param('ouw_pageid', PARAM_INT);

// Get the current page version, creating page if needed
$pageversion = moewiki_get_current_page($subwiki, $pagename, MOEWIKI_GETPAGE_ACCEPTNOVERSION);
$wikiformfields = moewiki_display_wiki_parameters($pagename, $subwiki, $cm, MOEWIKI_PARAMS_FORM);
$sectionfields = '';

// get the context and check user has the required capability
require_capability('mod/moewiki:lock', $context);
$moewikioutput = $PAGE->get_renderer('mod_moewiki');

// Get an editing lock
list($lockok, $lock) = moewiki_obtain_lock($moewiki, $pageversion->pageid);

// Handle case where page is locked by someone else
if (!$lockok) {
    // Print header etc
    echo $moewikioutput->moewiki_print_start($moewiki, $cm, $course, $subwiki, $pagename, $context);

    $details = new StdClass;
    $lockholder = $DB->get_record('user', array('id' => $lock->userid));
    $details->name = fullname($lockholder);
    $details->lockedat = moewiki_nice_date($lock->lockedat);
    $details->seenat = moewiki_nice_date($lock->seenat);
    $pagelockedtitle = get_string('pagelockedtitle', 'moewiki');
    $pagelockedtimeout = '';
    if ($lock->seenat > time()) {
        // When the 'seen at' value is greater than current time, that means
        // their lock has been automatically confirmed in advance because they
        // don't have JavaScript support.
        $details->nojs = moewiki_nice_date($lock->seenat + MOEWIKI_LOCK_PERSISTENCE);
        $pagelockeddetails = get_string('pagelockeddetailsnojs', 'moewiki', $details);
    } else {
        $pagelockeddetails = get_string('pagelockeddetails', 'moewiki', $details);
        if ($lock->expiresat) {
            $pagelockedtimeout = get_string('pagelockedtimeout', 'moewiki', userdate($lock->expiresat));
        }
    }
    $canoverride = has_capability('mod/moewiki:overridelock', $context);
    $pagelockedoverride = $canoverride ? '<p>'.get_string('pagelockedoverride', 'moewiki').'</p>' : '';
    $overridelock = get_string('overridelock', 'moewiki');
    $overridebutton = $canoverride ? "
<form class='moewiki_overridelock' action='override.php' method='post'>
  <input type='hidden' name='redirpage' value='view'>
  $wikiformfields
  <input type='submit' value='$overridelock' />
</form>
" : '';
    $cancel = get_string('cancel');
    $tryagain = get_string('tryagain', 'moewiki');
    print "
<div id='moewiki_lockinfo'>
  <h2>$pagelockedtitle</h2>
  <p>$pagelockeddetails $pagelockedtimeout</p>
  $pagelockedoverride
  <div class='moewiki_lockinfobuttons'>
    <form action='edit.php' method='get'>
      $wikiformfields
      $sectionfields
      <input type='submit' value='$tryagain' />
    </form>
    <form action='view.php' method='get'>
      $wikiformfields
      <input type='submit' value='$cancel' />
    </form>
    $overridebutton
  </div>
</div>";
    print_footer($course);
    exit;
}

// The page is now locked to us!
// To have got this far everything checks out so lock or unlock the page as requested
if ($action == get_string('lockpage', 'moewiki')) {
    moewiki_lock_editing($pageid, true);
    $eventtype = 'lock';
} else if ($action == get_string('unlockpage', 'moewiki')) {
    moewiki_lock_editing($pageid, false);
    $eventtype = 'unlock';
}

// all done - release the editing lock...
moewiki_release_lock($pageversion->pageid);

// add to moodle log...
$url = 'view.php';
$url .= (strpos($url, '?')===false ? '?' : '&').'id='.$cm->id;
if ($subwiki->groupid) {
    $url .= '&group='.$subwiki->groupid;
}
if ($subwiki->userid) {
    $url .= '&user='.$subwiki->userid;
}
$info = '';
if ($pagename) {
    $url .= '&page='.urlencode($pagename);
    $info = $pagename;
}

// Add to event log.

// Log usage view.
$params = array(
        'context' => $context,
        'objectid' => $pageid,
        'other' => array('info' => $info, 'logurl' => $url)
);

$event = null;
if ($eventtype == 'lock') {
    $event = \mod_moewiki\event\page_lock::create($params);
} else {
    $event = \mod_moewiki\event\page_unlock::create($params);
}
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('moewiki', $moewiki);
$event->trigger();

// redirect back to the view page.
redirect($url);
