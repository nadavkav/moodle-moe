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
 * 'Wiki index' page. Displays an index of all pages in the wiki, in
 * various formats.
 *
 * @copyright &copy; 2007 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moewiki
 */

require_once(dirname(__FILE__) . '/../../config.php');
require($CFG->dirroot.'/mod/moewiki/basicpage.php');

raise_memory_limit(MEMORY_EXTRA);

$treemode = optional_param('type', '', PARAM_ALPHA) == 'tree';
$id = required_param('id', PARAM_INT); // Course Module ID

$url = new moodle_url('/mod/moewiki/wikiindex.php', array('id'=>$id));
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

// Do header
$moewikioutput->set_export_button('subwiki', $subwiki->id, $course->id, !empty($treemode) ? 1 : 0);
echo $moewikioutput->moewiki_print_start($moewiki, $cm, $course, $subwiki, get_string('index', 'moewiki'), $context, null, false);

// Print tabs for selecting index type
$tabrow = array();
$tabrow[] = new tabobject('alpha', 'wikiindex.php?'.$wikiparams,
    get_string('tab_index_alpha', 'moewiki'));
$tabrow[] = new tabobject('tree', 'wikiindex.php?'.$wikiparams.'&amp;type=tree',
    get_string('tab_index_tree', 'moewiki'));
$tabs = array();
$tabs[] = $tabrow;
print_tabs($tabs, $treemode ? 'tree' : 'alpha');
print '<div id="moewiki_belowtabs">';

global $orphans;
// Get actual index
$index = moewiki_get_subwiki_index($subwiki->id);

$orphans = false;
$func = 'moewiki_display_wikiindex_page_in_index';
if (count($index) == 0) {
    print '<p>'.get_string('startpagedoesnotexist', 'moewiki').'</p>';
} else if ($treemode) {
    moewiki_build_tree($index);
    // Print out in hierarchical form...
    print '<ul class="ouw_indextree">';
    print moewiki_tree_index($func, reset($index)->pageid, $index, $subwiki, $cm);
    print '</ul>';
    foreach ($index as $indexitem) {
        if (count($indexitem->linksfrom) == 0 && $indexitem->title !== '') {
            $orphans = true;
            break;
        }
    }
} else {
    // ...or standard alphabetical
    print '<ul class="ouw_index">';
    foreach ($index as $indexitem) {
        if (count($indexitem->linksfrom)!= 0 || $indexitem->title === '') {
            print '<li>' . moewiki_display_wikiindex_page_in_index($indexitem, $subwiki, $cm) . '</li>';
        } else {
            $orphans = true;
        }
    }
    print '</ul>';
}

if ($orphans) {
    print '<h2 class="ouw_orphans">'.get_string('orphanpages', 'moewiki').'</h2>';
    print '<ul class="ouw_index">';
    foreach ($index as $indexitem) {
        if (count($indexitem->linksfrom) == 0 && $indexitem->title !== '') {
            if ($treemode) {
                $orphanindex = moewiki_get_sub_tree_from_index($indexitem->pageid, $index);
                moewiki_build_tree($orphanindex);
                print moewiki_tree_index($func, $indexitem->pageid, $orphanindex, $subwiki, $cm);
            } else {
                print '<li>' . moewiki_display_wikiindex_page_in_index($indexitem, $subwiki, $cm) . '</li>';
            }
        }
    }
    print '</ul>';
}

$missing = moewiki_get_subwiki_missingpages($subwiki->id);
if (count($missing) > 0) {
    print '<div class="ouw_missingpages"><h2>'.get_string('missingpages', 'moewiki').'</h2>';
    print '<p>'.get_string(count($missing) > 1 ? 'advice_missingpages' : 'advice_missingpage', 'moewiki').'</p>';
    print '<ul>';
    $first = true;
    foreach ($missing as $title => $from) {
        print '<li>';
        if ($first) {
            $first = false;
        } else {
            print ' &#8226; ';
        }
        print '<a href="view.php?'.moewiki_display_wiki_parameters($title, $subwiki, $cm).'">'.
            htmlspecialchars($title).'</a> <span class="ouw_missingfrom">('.
            get_string(count($from) > 1 ? 'frompages' : 'frompage', 'moewiki',
                '<a href="view.php?'.moewiki_display_wiki_parameters($from[0], $subwiki, $cm).'">'.
                ($from[0] ? htmlspecialchars($from[0]) : get_string('startpage', 'moewiki')).'</a>)</span>');
        print '</li>';
    }
    print '</ul>';
    print '</div>';
}

$tree = 0;
if (!empty($treemode)) {
    $wikiparams.= '&amp;type=tree';
    $tree = 1;
}

if (count($index) != 0) {
    print '<div class="ouw_entirewiki"><h2>'.get_string('entirewiki', 'moewiki').'</h2>';
    print '<p>'.get_string('onepageview', 'moewiki').'</p><ul>';
    print '<li id="moewiki_down_html"><a href="entirewiki.php?'.$wikiparams.'&amp;format=html">'.
        get_string('format_html', 'moewiki').'</a></li>';

    // Are there any files in this wiki?
    $context = context_module::instance($cm->id);
    $result = $DB->get_records_sql("
SELECT
    f.id
FROM
    {moewiki_subwikis} sw
    JOIN {moewiki_pages} p ON p.subwikiid = sw.id
    JOIN {moewiki_versions} v ON v.pageid = p.id
    JOIN {files} f ON f.itemid = v.id
WHERE
    sw.id = ? AND f.contextid = ? AND f.component = 'mod_moewiki' AND f.filename NOT LIKE '.'
    AND f.filearea = 'attachment' AND v.id IN (SELECT MAX(v.id) from {moewiki_versions} v WHERE v.pageid = p.id)
    ", array($subwiki->id, $context->id), 0, 1);
    $anyfiles = count($result) > 0;
    $wikiparamsarray = array('subwikiid' => $subwiki->id, 'tree' => $tree);
    print $moewikioutput->render_export_all_li($subwiki, $anyfiles, $wikiparamsarray);

    if (has_capability('moodle/course:manageactivities', $context)) {
        $str = get_string('format_template', 'moewiki');
        $filesexist = false;
        if ($anyfiles) {
            // Images or attachment files found.
            $filesexist = true;
        }

        print '<li id="moewiki_down_template"><a href="entirewiki.php?' . $wikiparams . '&amp;format=template&amp;filesexist='
            .$filesexist.'">' . $str . '</a></li>';
    }
    print '</ul></div>';
}

$pageversion = moewiki_get_current_page($subwiki, $pagename);
echo $moewikioutput->get_bottom_buttons($subwiki, $cm, $context, $pageversion, false);

// Footer
moewiki_print_footer($course, $cm, $subwiki, $pagename);
