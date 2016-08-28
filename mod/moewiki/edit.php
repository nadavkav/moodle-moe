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
 * Edit page. Allows user to edit and/or preview wiki pages.
 *
 * @copyright &copy; 2007 SysBind
 * @author avi@sysbind.co.il
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moewiki
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/lib/ajax/ajaxlib.php');
require_once($CFG->dirroot.'/mod/moewiki/basicpage.php');
require_once($CFG->dirroot.'/mod/moewiki/edit_form.php');

if (file_exists($CFG->dirroot.'/local/mobile/ou_lib.php')) {
    require_once($CFG->dirroot.'/local/mobile/ou_lib.php');
}

$action = optional_param('editoption', '', PARAM_TEXT);

// for creating pages and sections
$frompage = optional_param('frompage', null, PARAM_TEXT);
$newsection = optional_param('newsection', null, PARAM_TEXT);

// for creating/editing sections
$section = optional_param('section', null, PARAM_RAW);

$urlparams = array();
$urlparams['id'] = $cm->id;
if (!empty($pagename)) {
    $urlparams['page'] = $pagename;
}
if (!empty($newsection)) {
    $urlparams['newsection'] = $newsection;
}
if (!empty($section)) {
    $urlparams['section'] = $section;
}

// sort out if the action was save or cancel
$save = $action === get_string('savechanges') ? true : false;
$cancel = $action === get_string('cancel') ? true : false;

if (!$cm = get_coursemodule_from_id('moewiki', $id)) {
    print_error('invalidcoursemodule');
}

// Checking course instance
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

if (!$moewiki = $DB->get_record('moewiki', array('id' => $cm->instance))) {
    print_error('invalidcoursemodule');
}

$PAGE->set_cm($cm);

// When creating a new page, do some checks
$addpage = false;
if (!is_null($frompage)) {
    $urlparams['frompage'] = $frompage;
    $returnurl = new moodle_url('/mod/moewiki/view.php',
            moewiki_display_wiki_parameters($frompage, $subwiki, $cm, MOEWIKI_PARAMS_ARRAY));
    if (trim($pagename) === '') {
        print_error('emptypagetitle', 'moewiki', $returnurl);
    }
    // Strip whitespace from new page name from form (editor does this for other links).
    $pagename = preg_replace('/\s+/', ' ', $pagename);

    $addpage = true;
}

$returnurl = new moodle_url('/mod/moewiki/view.php',
        moewiki_display_wiki_parameters($pagename, $subwiki, $cm, MOEWIKI_PARAMS_ARRAY));

// When creating a section, do checks
$addsection = false;
if (!is_null($newsection)) {
    if (trim($newsection) === '') {
        print_error('emptysectiontitle', 'moewiki', $returnurl);
    }
    $addsection = true;
}

$context = context_module::instance($cm->id);
$PAGE->set_pagelayout('incourse');
require_course_login($course, true, $cm);

$moewikioutput = $PAGE->get_renderer('mod_moewiki');

require_capability('mod/moewiki:edit', $context);

$url = new moodle_url('/mod/moewiki/edit.php', $urlparams);
$PAGE->set_url($url);

// Check permission
if (!$subwiki->canedit) {
    print_error('You do not have permission to edit this wiki');
}

$useattachments = !$addsection && !$section;

// create the new mform
// customdata indicates whether attachments are used (no for sections)
$mform = new mod_moewiki_edit_page_form('edit.php', (object)array(
        'attachments' => $useattachments, 'startpage' => $pagename === '',
        'addpage' => $addpage, 'addsection' => $addsection));

// get form content if save/preview
$content = null;
$formdata = null;
if ($formdata = $mform->get_data()) {
    if ($content = $formdata->content['text']) {
        // Check if they used the plaintext editor, if so fixup linefeeds
        if ((isset($formdata->content['format'])) && ($formdata->content['format'] != FORMAT_HTML)) {
            $content = moewiki_plain_to_xhtml($content);
        }
        $content = moewiki_format_xhtml_a_bit($content); // Tidy up HTML
    }
}

// new content for section
if ($newsection) {
    $new = new StdClass;
    $new->name = moewiki_display_user($USER, $course->id);
    $new->date = userdate(time());
    $sectionheader = html_writer::tag('h3', s($newsection)) .
            html_writer::tag('p', '(' . get_string('createdbyon', 'moewiki', $new) . ')');
}

// if cancel redirect before going too far
if ($cancel) {
    // Get pageid to unlock.
    $pageversion = moewiki_get_current_page($subwiki, $pagename);
    if (!empty($pageversion->pageid)) {
        moewiki_release_lock($pageversion->pageid);
    }
    redirect($returnurl);
    exit;
}

// Get the current page version, creating page if needed
$pageversion = moewiki_get_current_page($subwiki, $pagename, MOEWIKI_GETPAGE_CREATE);
if ($addpage && !is_null($pageversion->xhtml)) {
    print_error('duplicatepagetitle', 'moewiki', $returnurl);
}
if ($pageversion->locked === '1') {
    print_error('thispageislocked', 'moewiki', 'view.php?id='.$cm->id);
}

// Need list of known sections on current version
$knownsections = moewiki_find_sections($pageversion->xhtml);

// Get section, make sure the name is valid
if (!preg_match('/^[0-9]+_[0-9]+$/', $section)) {
    $section = null;
}
if ($section) {
    if (!array_key_exists($section, $knownsections)) {
        print_error("Unknown section $section");
    }
    $sectiontitle = $knownsections[$section];
    $sectiondetails = moewiki_get_section_details($pageversion->xhtml, $section);
}


// Get lock
if (!$cancel) {
    list($lockok, $lock) = moewiki_obtain_lock($moewiki, $pageversion->pageid);
}

if ($save) {
    if (!$newsection ) {
        // Check we started editing the right version
        $startversionid = required_param('startversionid', PARAM_INT);
        $versionok = $startversionid == $pageversion->versionid;
    } else {
        $versionok = true;
    }

    // If we either don't have a lock or are editing the wrong version...
    if (!$versionok || !$lockok) {
        $savefailtitle = get_string('savefailtitle', 'moewiki');
        $specificmessage = get_string(!$versionok ? 'savefaildesynch' : 'savefaillocked', 'moewiki');
        $returntoview = get_string('returntoview', 'moewiki');
        $savefailcontent = get_string('savefailcontent', 'moewiki');
        $actualcontent = moewiki_convert_content($content, $subwiki, $cm, null, $pageversion->xhtmlformat);

        // we are either returning to an existing page or a "new" one that ws
        // simultaneously created by someone else at the same time
        $returnpage = $addpage ? $frompage : $pagename;

        moewiki_release_lock($pageversion->pageid);
        echo $OUTPUT->header();

        $pagefield = '';
        if ($returnpage !== '') {
            $pagefield = html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => 'page', 'value' => $returnpage));
        }
        print '<div id="moewiki_savefail">'
            .'<h2>'.$savefailtitle.'</h2>'
            .'<p>'.$specificmessage.'</p>'
            .'<form action="view.php" method="get">'
            .'<input type="hidden" name="id" value="'.$cm->id.'" />'
            . $pagefield
            .'<input type="submit" value="'.$returntoview.'" />'
            .'</form>'
            .'<p>'.$savefailcontent.'</p>'
            .'<div class="moewiki_savefailcontent">'.$actualcontent.'</div>'
            .'</div><div>';

        moewiki_print_footer($course, $cm, $subwiki, $pagename);
        exit;
    }

    $event = null;
    if ($section) {
        moewiki_save_new_version_section($course, $cm, $moewiki, $subwiki, $pagename, $pageversion->xhtml, $formdata->content['text'], $sectiondetails, $formdata);
    } else {
        if ($addpage) {
            moewiki_create_new_page($course, $cm, $moewiki, $subwiki, $frompage, $pagename, $content, $formdata);
        } else {
            if ($addsection) {
                moewiki_create_new_section($course, $cm, $moewiki, $subwiki, $pagename, $formdata->content['text'], $sectionheader, $formdata);
            } else {
                // Normal save
                moewiki_save_new_version($course, $cm, $moewiki, $subwiki, $pagename, $content, -1, -1, -1, null, $formdata);
            }
        }
    }

    // Update completion state
    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) && ($moewiki->completionedits || $moewiki->completionpages)) {
        $completion->update_state($cm, COMPLETION_COMPLETE);
    }

    // Release lock, log and redirect.
    moewiki_release_lock($pageversion->pageid);

    // Log.
    $info = '';
    if ($pagename) {
        $info = $pagename;
    }

    // Log usage edit.
    $params = array(
            'context' => $context,
            'objectid' => $pageversion->pageid,
            'other' => array('info' => $info, 'logurl' => $url->out_as_local_url())
    );

    if ($addpage) {
        $event = \mod_moewiki\event\page_created::create($params);
    } else {
        $event = \mod_moewiki\event\page_updated::create($params);
    }
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('moewiki', $moewiki);
    $event->trigger();

    // Redirect.
    redirect($returnurl);
    exit;
}

if ($section) {
    $sectionfields = "<input type='hidden' name='section' value='$section' />";
} else {
    $sectionfields = '';
}

// Handle case where page is locked by someone else
if (!$lockok) {
    echo $OUTPUT->header();

    $lockholder = $DB->get_record('user', array('id' => $lock->userid));
    $canoverride = has_capability('mod/moewiki:overridelock', $context);
    $pagelockedtimeout = null;

    $cancel = get_string('cancel');
    $tryagain = get_string('tryagain', 'moewiki');
    $pagelockedtitle = get_string('pagelockedtitle', 'moewiki');
    $overridelock = get_string('overridelock', 'moewiki');

    $details = new StdClass;
    $details->name = fullname($lockholder);
    $details->lockedat = moewiki_nice_date($lock->lockedat);
    $details->seenat = moewiki_nice_date($lock->seenat);
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

    $pagelockedoverride = $canoverride ? '<p>'.get_string('pagelockedoverride', 'moewiki').'</p>' : '';

    print "<div id='moewiki_lockinfo'>
        <h2>$pagelockedtitle</h2>
        <p>$pagelockeddetails $pagelockedtimeout</p>
        $pagelockedoverride
        <div class='moewiki_lockinfobuttons'>";

    if ($pagename === '') {
        $pageinputs = '';
    } else {
        $pageinputs = html_writer::empty_tag('input', array('type' => 'hidden',
                        'name' => 'page', 'value' => $pagename));
    }
    if ($addpage) {
        $pageinputs .= html_writer::empty_tag('input', array('type' => 'hidden',
                        'name' => 'frompage', 'value' => $frompage));
    }

    $newsectioninput = '';
    if ($addsection) {
        $newsectioninput = html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => 'newsection', 'value' => $newsection));
    }

    print "<form action='edit.php' method='get'>
            <input type='hidden' name='id' value='$cm->id' />
            $pageinputs
            $newsectioninput
            $sectionfields
            <input type='submit' value='$tryagain' />
        </form>";

    print "<form action='view.php' method='get'>
            <input type='hidden' name='id' value='$cm->id' />
            $pageinputs
            $newsectioninput
            $sectionfields
            <input type='submit' value='$cancel' />
        </form>";

    print $canoverride
        ? "<form class='moewiki_overridelock' action='override.php' method='post'>
        <input type='hidden' name='id' value='$cm->id' />
        $pageinputs
        $newsectioninput
        <input type='submit' value='$overridelock' /></form>"
        : '';

    print "</div></div><div>";

    moewiki_print_footer($course, $cm, $subwiki, $pagename);
    exit;
}

// The page is now locked to us! Go ahead and print edit form

// get title of the page
$title = get_string('editingpage', 'moewiki');
$wikiname = format_string(htmlspecialchars($moewiki->name));
$name = '';
if ($pagename) {
    $title .= ': ' . $pagename;
} else {
    if ($addsection) {
        $sectiontitle = $newsection;
        $name = htmlspecialchars($newsection);
        $title = get_string('editingsection', 'moewiki', $name);
        $section = true;
    } else {
        if (!$section) {
            $name = get_string('startpage', 'moewiki');
        } else {
            $name = htmlspecialchars($sectiontitle);
            $title = get_string('editingsection', 'moewiki', $name);
        }
    }
}
$title = $wikiname.' - '.$title;

echo $moewikioutput->moewiki_print_start($moewiki, $cm, $course, $subwiki, $pagename, $context,
    array(array('name' =>
        $section
            ? get_string('editingsection', 'moewiki', htmlspecialchars($sectiontitle))
            : get_string('editingpage', 'moewiki'), 'link' => null)
        ), false, false, '', $title);

if ($newsection) {
    $section = false;
}

// Tabs
moewiki_print_tabs('edit', $pagename, $subwiki, $cm, $context, $pageversion->versionid ? true : false);

// setup the edit locking
moewiki_print_editlock($lock, $moewiki);

// Calculate initial text for editor
if ($section) {
    $existing = $sectiondetails->content;
} else if ($newsection) {
    $existing = $sectionheader;
} else if ($pageversion) {
    $existing = $pageversion->xhtml;
} else {
    $existing = '';
}

// print the preview box
if ($content) {
    echo $moewikioutput->moewiki_print_preview($content, $pagename, $subwiki, $cm, $pageversion->xhtmlformat);
    $existing = $content;
}

// Get the annotations and add prepare them for editing
$annotations = moewiki_get_annotations($pageversion);
moewiki_highlight_existing_annotations($existing, $annotations, 'edit');

print get_string('advice_edit', 'moewiki', $OUTPUT->help_icon('createlinkedwiki', 'moewiki'));
if ($moewiki->timeout) {
    $countdowntext = get_string('countdowntext', 'moewiki', $moewiki->timeout/60);
    print "<script type='text/javascript'>
                document.write('<p><div id=\"ouw_countdown\"></div>$countdowntext<span id=\"ouw_countdownurgent\"></span></p>');
        </script>";
}

// Set up basic form data
$data = new StdClass;
$data->id = $cm->id;
$data->startversionid = $pageversion->versionid;
$data->page = $pagename;
$data->frompage = $frompage;
$data->newsection = $newsection;
$data->section = $section;
$data->user = $subwiki->userid;

// Prepare form file manager attachments
if ($useattachments) {
    $attachmentsdraftid = file_get_submitted_draft_itemid('attachments');
    file_prepare_draft_area($attachmentsdraftid, $context->id, 'mod_moewiki',
            'attachment', empty($pageversion->versionid) ? null : $pageversion->versionid);
    $data->attachments = $attachmentsdraftid;
}

// Prepare form editor attachments
$contentdraftid = file_get_submitted_draft_itemid('content');
$currenttext = file_prepare_draft_area($contentdraftid, $context->id, 'mod_moewiki', 'content',
        empty($pageversion->versionid) ? null : $pageversion->versionid,
        array('subdirs' => false), empty($existing) ? '' : $existing);

$data->content = array('text' => $currenttext,
       'format' => empty($pageversion->xhtmlformat)
           ? editors_get_preferred_format() : $pageversion->xhtmlformat,
       'itemid' => $contentdraftid);

$mform->set_data($data);

$mform->display();

echo $moewikioutput->get_bottom_buttons($subwiki, $cm, $context, $pageversion, false);

$stringlist = array(
        array('savefailnetwork', 'moewiki'),
        array('savefailtitle', 'moewiki'),
);
$jsmodule = array(
        'name' => 'mod_moewiki_edit',
        'fullpath' => '/mod/moewiki/module.js',
        'requires' => array('base', 'event', 'io', 'node', 'anim', 'moodle-core-notification-alert', 'button'),
        'strings'  => $stringlist
);
$PAGE->requires->js_init_call('M.mod_moewiki_edit.init', null, true, $jsmodule);

// Footer
moewiki_print_footer($course, $cm, $subwiki, $pagename);
