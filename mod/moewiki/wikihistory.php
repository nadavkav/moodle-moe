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
 * 'Wiki changes' page. Displays a list of recent changes to the wiki. You
 * can choose to view all changes or only new pages.
 *
 * @copyright &copy; 2007 SysBind
 * @author avi@sysbind.co.il
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moewiki
 *//** */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/moewiki/basicpage.php');
require_once($CFG->dirroot.'/mod/moewiki/locallib.php');

$id = required_param('id', PARAM_INT); // Course Module ID
$newpages = optional_param('type', '', PARAM_ALPHA) == 'pages';
$from = optional_param('from', '', PARAM_INT);

$url = new moodle_url('/mod/moewiki/wikihistory.php', array('id' => $id));
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

// Get basic wiki parameters
$wikiparams = moewiki_display_wiki_parameters('', $subwiki, $cm);
$tabparams = $newpages ? $wikiparams.'&amp;type=pages' : $wikiparams;

// Get changes
if ($newpages) {
    $changes = moewiki_get_subwiki_recentpages($subwiki->id, $from, MOEWIKI_PAGESIZE+1);
} else {
    $changes = moewiki_get_subwiki_recentchanges($subwiki->id, $from, MOEWIKI_PAGESIZE+1);
}

// Check to see whether any change has been overwritten by being imported.
$overwritten = false;
foreach ($changes as $change) {
    if (!empty($change->importversionid)) {
        $overwritten = true;
        break;
    }
}

// Do header
$atomurl = $CFG->wwwroot.'/mod/moewiki/feed-wikihistory.php?'.$wikiparams.
    ($newpages?'&amp;type=pages' : '').'&amp;magic='.$subwiki->magic;
$rssurl = $CFG->wwwroot.'/mod/moewiki/feed-wikihistory.php?'.$wikiparams.
    ($newpages?'&amp;type=pages' : '').'&amp;magic='.$subwiki->magic.'&amp;format=rss';
$meta = '<link rel="alternate" type="application/atom+xml" title="Atom feed" '.
    'href="'.$atomurl.'" />';

// bug #3542
$wikiname = format_string(htmlspecialchars($moewiki->name));
$title = $wikiname.' - '.get_string('wikirecentchanges', 'moewiki');

echo $moewikioutput->moewiki_print_start($moewiki, $cm, $course, $subwiki,
    $from > 0
        ? get_string('wikirecentchanges_from', 'moewiki', (int)($from/MOEWIKI_PAGESIZE) + 1)
        : get_string('wikirecentchanges', 'moewiki'),
    $context, null, false, false, $meta, $title);

// Print tabs for selecting all changes/new pages
$tabrow = array();
$tabrow[] = new tabobject('changes', 'wikihistory.php?'.$wikiparams,
    get_string('tab_index_changes', 'moewiki'));
$tabrow[] = new tabobject('pages', 'wikihistory.php?'.$wikiparams.'&amp;type=pages',
    get_string('tab_index_pages', 'moewiki'));
$tabs = array();
$tabs[] = $tabrow;
print_tabs($tabs, $newpages ? 'pages' : 'changes');
print '<div id="moewiki_belowtabs">';

if ($newpages) {
    $pagetabname = get_string('tab_index_pages', 'moewiki');
} else {
    $pagetabname = get_string('tab_index_changes', 'moewiki');
}
print get_accesshide($pagetabname, 'h1');

// On first page, show information
if (!$from) {
    print get_string('advice_wikirecentchanges_'
        .($newpages ? 'pages' : 'changes'
        .(!empty($CFG->moewikienablecurrentpagehighlight) ? '' : '_nohighlight')), 'moewiki').'</p>';
}

$strdate = get_string('date');
$strtime = get_string('time');
$strpage = get_string('page', 'moewiki');
$strperson = get_string('changedby', 'moewiki');
$strview = get_string('view');

$strimport = '';
if ($overwritten) {
    $strimport = get_string('importedfrom', 'moewiki');
}

print "
<table class='generaltable'>
<thead>
<tr><th scope='col'>$strdate</th><th scope='col'>$strtime</th><th scope='col'>$strpage</th>".
($newpages?'':'<th><span class="accesshide">'.$strview.'</span></th>');
if ($moewiki->enablewordcount) {
    print "<th scope='col'>".get_string('words', 'moewiki')."</th>";
}
if ($overwritten) {
    print '<th scope="col">' . $strimport . '</th>';
}
print "
  <th scope='col'>$strperson</th></tr></thead><tbody>
";

$strchanges = get_string('changes', 'moewiki');
$strview = get_string('view');
$lastdate = '';
$count = 0;
foreach ($changes as $change) {
    $count++;
    if ($count > MOEWIKI_PAGESIZE) {
        break;
    }

    $pageparams = moewiki_display_wiki_parameters($change->title, $subwiki, $cm);

    $date = userdate($change->timecreated, get_string('strftimedate'));
    if ($date == $lastdate) {
        $date = '';
    } else {
        $lastdate = $date;
    }
    $time = moewiki_recent_span($change->timecreated).userdate($change->timecreated, get_string('strftimetime')).'</span>';

    $page = $change->title ? htmlspecialchars($change->title) : get_string('startpage', 'moewiki');
    if (!empty($change->previousversionid)) {
        $changelink = " <small>(<a href='diff.php?$pageparams&amp;v2={$change->versionid}&amp;v1={$change->previousversionid}'>$strchanges</a>)</small>";
    } else {
        $changelink = ' <small>('.get_string('newpage', 'moewiki').')</small>';
    }

    $current = '';
    if ($change->versionid == $change->currentversionid || $newpages) {
        $viewlink = "view.php?$pageparams";
        if (!$newpages && !empty($CFG->moewikienablecurrentpagehighlight)) {
            $current =' class="current"';
        }
    } else {
        $viewlink = "viewold.php?$pageparams&amp;version={$change->versionid}";
    }

    $change->id = $change->userid;
    if ($change->id) {
        $userlink = moewiki_display_user($change, $course->id);
    } else {
        $userlink = '';
    }

    if ($newpages) {
        $actions = '';
        $page = "<a href='$viewlink'>$page</a>";
    } else {
        $actions = "<td class='actions'><a href='$viewlink'>$strview</a>$changelink</td>";
    }

    // see bug #3611
    if (!empty($current) && !empty($CFG->moewikienablecurrentpagehighlight)) {
        // current page so add accessibility stuff
        $accessiblityhide = '<span class="accesshide">'.get_string('currentversionof', 'moewiki').'</span>';
        $dummy = $page;
        $page = $accessiblityhide.$dummy;
    }

    print "
<tr$current>
  <td class='ouw_leftcol'>$date</td><td>$time</td><td>$page</td>
  $actions";
    if ($moewiki->enablewordcount) {
        if (isset($change->previouswordcount)) {
            $wordcountchanges = moewiki_wordcount_difference($change->wordcount,
                    $change->previouswordcount, true);
        } else {
            // first page
            $wordcountchanges = moewiki_wordcount_difference($change->wordcount, 0, false);
        }
        print "<td>$wordcountchanges</td>";
    }
    if ($overwritten) {
        if (!empty($change->importversionid)) {
            $selectedmoewiki = moewiki_get_wiki_details($change->importversionid);
            print '<td>';
            if ($selectedmoewiki->courseshortname) {
                print $selectedmoewiki->courseshortname. '<br/>';
            }
            print $selectedmoewiki->name;
            if ($selectedmoewiki->group) {
                print '<br/>';
                print '[[' .$selectedmoewiki->group. ']]';
            } else if ($selectedmoewiki->user) {
                print '<br/>';
                print '[[' .$selectedmoewiki->user. ']]';
            }
            print '</td>';
        } else {
            print '<td></td>';
        }
    }
    print "
  <td class='ouw_rightcol'>$userlink</td>
</tr>";
}

print '</tbody></table>';

if (empty($changes)) {
    echo get_string('nowikipages', 'moewiki');
}

if ($count > MOEWIKI_PAGESIZE || $from > 0) {
    print '<div class="ouw_paging"><div class="ouw_paging_prev">&nbsp;';
    if ($from > 0) {
        $jump = $from - MOEWIKI_PAGESIZE;
        if ($jump < 0) {
            $jump = 0;
        }
        print link_arrow_left(get_string('previous', 'moewiki'),
            'wikihistory.php?'.$tabparams. ($jump > 0 ? '&amp;from='.$jump : ''));
    }
    print '</div><div class="ouw_paging_next">';
    if ($count > MOEWIKI_PAGESIZE) {
        $jump = $from + MOEWIKI_PAGESIZE;
        print link_arrow_right(get_string('next', 'moewiki'),
            'wikihistory.php?'.$tabparams. ($jump > 0 ? '&amp;from='.$jump : ''));
    }
    print '&nbsp;</div></div>';
}

echo $moewikioutput->moewiki_get_feeds($atomurl, $rssurl);

$pageversion = moewiki_get_current_page($subwiki, $pagename);
echo $moewikioutput->get_bottom_buttons($subwiki, $cm, $context, $pageversion, false);

// Footer
moewiki_print_footer($course, $cm, $subwiki);
