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
 * Configuration for Moodle's standard theme.
 *
 * This theme is the default theme within Moodle 2.0, it builds upon the base theme
 * adding only CSS to create the simple look and feel Moodlers have come to recognise.
 *
 * For full information about creating Moodle themes, see:
 *  http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   moodlecore
 * @copyright 2010 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$THEME->name = 'avalon';
$THEME->parents = array('base');
$THEME->sheets = array(
    'pagelayout',   /** Must come first: Page layout **/
    'core',         /** Must come second: General styles **/
    'admin',
    'blocks',
    'calendar',
    /*'course',*/
    'course_format_flexsections',
    'course_format_grid',
    'course_format_grid_rtl',
    // moonstone is only available on M24 (see later IF settings->overridetopcoll )
    //'course_format_moonstone',
    //'course_format_moonstone_rtl',
    'course_format_topcoll_moonstone',
    'course_format_topcoll_moonstone_rtl',
    'course_format_fntabs',
    'user',
    'grade',
    'message',
    'modules',
    'question',
    'css3',      /** Sets up CSS 3 + browser specific styles **/
    'special',           // Experimental CSS code [nadavkav]
    'autohide',          // hide all edit icons, when teacher is in edit mode
	  //'fontface',          // @font-face support (related on specially installed webfonts that start with "font_*"
    //'font_shmulikclm',   // Specially generated file for culmus.sourceforge.net project font:ShmulikCLM ( using : fontsquirrel.com)
    //'font_webfontkit',
    'rtl',
    'renderer'
);

//$THEME->sheets[] = 'course_format_topcoll_moonstone';
//$THEME->sheets[] = 'course_format_topcoll_moonstone_rtl';

if(isset($CFG->avalon_mode) && $CFG->avalon_mode == 'moe')
{
    $THEME->sheets[] = 'mode_moe';
}
if(isset($CFG->avalon_mode) && $CFG->avalon_mode == 'moe-talk')
{
    $THEME->sheets[] = 'mode_moe-talk';
}

$THEME->layouts = array(
    // Most backwards compatible layout without the blocks - this is the layout used by default
    'base' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
    ),
    // Standard layout with blocks, this is recommended for most pages with general information
    'standard' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
    ),
    // Main course page
    'course' => array(
        //'file' => 'course.php', // special layout that support new region blocks above content "contenthead"
        'file' => 'general.php',
        //'regions' => array('side-pre','contenthead', 'side-post'),
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu'=>true),
    ),
    'coursecategory' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
    ),
    // part of course, typical for modules - default page layout if $cm specified in require_login()
    'incourse' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
    ),
    // The site home page.
    'frontpage' => array(
        'file' => 'frontpage.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu'=>true),
    ),
    // Server administration scripts.
    'admin' => array(
        'file' => 'general.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // My dashboard page
    'mydashboard' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu'=>true),
    ),
    // My public page
    'mypublic' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
    ),
    'login' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('langmenu'=>true),
    ),

    // Pages that appear in pop-up windows - no navigation, no blocks, no header.
    'popup' => array(
        'file' => 'embedded.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true, 'nologininfo'=>true),
    ),
    // No blocks and minimal footer - used for legacy frame layouts only!
    'frametop' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter'=>true),
    ),
    // Embeded pages, like iframe/object embeded in moodleform - it needs as much space as possible
    'embedded' => array(
        'file' => 'embedded.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true),
    ),
    // Used during upgrade and install, and for the 'This site is undergoing maintenance' message.
    // This must not have any blocks, and it is good idea if it does not have links to
    // other places - for example there should not be a home link in the footer...
    'maintenance' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('noblocks'=>true, 'nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true),
    ),
    // Should display the content and basic headers only.
    'print' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('noblocks'=>true, 'nofooter'=>true, 'nonavbar'=>false, 'nocustommenu'=>true),
    ),
    // The pagelayout used when a redirection is occuring.
    'redirect' => array(
        'file' => 'embedded.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true),
    ),
    // The pagelayout used for reports
    'report' => array(
        'file' => 'general.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
);

$THEME->settings->subthemedefaults = array(
    'sky'=> array('parent' => 'Avalon', 'colors' => array(1 => '#12789b', 2 => '#caf1fe', 3=> '#90cce0', 4 => '#edfbff', 5 => '#00ade6', 6 => '#008cb8', 7 => '#004a61', 8 => '#51300d', 9 => '#ead5b4', 20 => '#ffe222', 21 => '#aa00ee', 22 => '#12789b'))
    ,'sienna'=> array('parent' => 'Avalon Sienna', 'colors' => array(1 => '#9a5d3c', 2 => '#fac79a', 3=> '#e4b38d', 4 => '#fdf3e4', 5 => '#eb4200', 6 => '#cd7c50', 7 => '#774225', 8 => '#4a577b', 9 => '#c5d4f5', 20 => '#ffe222', 21 => '#d500aa', 22 => '#9a5d3c'))
    ,'summer'=> array('parent' => 'Avalon Summer', 'colors' => array(1 => '#639922', 2 => '#d0ebb0', 3=> '#a6cc76', 4 => '#f3fee6', 5 => '#00a00a', 6 => '#88c333', 7 => '#659e21', 8 => '#d18a00', 9 => '#ffe680', 20 => '#fbf000', 21 => '#c88d00', 22 => '#639922'))
    ,'sandy'=> array('parent' => 'Avalon Sienna', 'colors' => array(1 => '#db6f03', 2 => '#ffcf96', 3=> '#f2b061', 4 => '#fef6e5', 5 => '#f05500', 6 => '#eb9251', 7 => '#a6580a', 8 => '#a22316', 9 => '#ffada5', 20 => '#9cff60', 21 => '#9b00db', 22 => '#db6f03'))
    ,'seacoral'=> array('parent' => 'Avalon', 'colors' => array(1 => '#2a89cd', 2 => '#bbe5ff', 3=> '#86ccf7', 4 => '#ecf7ff', 5 => '#240fff', 6 => '#4fafe9', 7 => '#1c78ba', 8 => '#cd631c', 9 => '#ffbe85', 20 => '#fbf000', 21 => '#ad00d8', 22 => '#2a89cd'))
    ,'navy'=> array('parent' => 'Avalon', 'colors' => array(1 => '#2e5193', 2 => '#99c2ff', 3=> '#7caaee', 4 => '#dbedff', 5 => '#12789b', 6 => '#3075f7', 7 => '#12377d', 8 => '#387613', 9 => '#86d25a', 20 => '#fff21b', 21 => '#ad00d8', 22 => '#2e5193'))
    ,'seabreeze'=> array('parent' => 'Avalon', 'colors' => array(1 => '#12789b', 2 => '#b8e6f9', 3=> '#93d3ed', 4 => '#e0f7fe', 5 => '#006cfe', 6 => '#46b6ce', 7 => '#025e7e', 8 => '#007fc8', 9 => '#88d4ff', 20 => '#ff4200', 21 => '#ad00d8', 22 => '#12789b'))
    ,'seamist'=> array('parent' => 'Avalon', 'colors' => array(1 => '#508a9a', 2 => '#c8e6f0', 3=> '#c8e6f0', 4 => '#f6f6f6', 5 => '#1b3e5f', 6 => '#6aaac0', 7 => '#31525a', 8 => '#508a9a', 9 => '#abd1da', 20 => '#ed0000', 21 => '#af4bd8', 22 => '#001e40'))
    ,'smoke'=> array('parent' => 'Avalon Smoke', 'colors' => array(1 => '#3a3a3a', 2 => '#ccd0d2', 3=> '#c4c4c4', 4 => '#f8f8f8', 5 => '#188fcf', 6 => '#777777', 7 => '#333333', 8 => '#075782', 9 => '#339fda', 20 => '#fff21b', 21 => '#cc4bec', 22 => '#002a40'))
    ,'lime'=> array('parent' => 'Avalon Smoke', 'colors' => array(1 => '#3a3a3a', 2 => '#ccd0d2', 3=> '#c4c4c4', 4 => '#f8f8f8', 5 => '#2d9c33', 6 => '#777777', 7 => '#333333', 8 => '#4d5c4e', 9 => '#bbd4bd', 20 => '#e4ff01', 21 => '#a11fde', 22 => '#004704'))
);

$THEME->settings->fontlist = array('default'=>array('name'=>'Default font', 'url'=>' '),
                                    'alef'=> array('name' => 'Alef font', 'url' => '@import url(http://fonts.googleapis.com/css?family=Alef:400,700); '),
                                    'noto'=>array('name' => 'Noto Sans font', 'url' => '@import url(http://fonts.googleapis.com/earlyaccess/notosansgeorgian.css);' ));

// Renderers...
$THEME->csspostprocess = 'avalon_process_css';
$THEME->rendererfactory = 'theme_overridden_renderer_factory';

$THEME->javascripts_footer = array('blocks','usermenu');

// What block regions should be swapped when in RTL mode
$THEME->settings->rtlswapblocks = array('side-pre'=>'side-post','side-post'=>'side-pre');
