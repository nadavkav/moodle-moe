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
 * Strings for component 'format_moetabs', language 'en'
 */
$string['zerosectionbtn'] = 'ארגז כלים, חומרי רקע ועזר עבור המורה';

$string['moetabspagesectionone'] = '<h1><span class="multilang" lang="he">מרחב 1</span><span class="multilang" lang="en">Section 1</span></h1>';
$string['moetabspagesectiontwo'] = '<h1><span class="multilang" lang="he">מרחב 2</span><span class="multilang" lang="en">Section 2</span></h1>';
$string['moetabspagesectionthree'] = '<h1><span class="multilang" lang="he">מרחב 3</span><span class="multilang" lang="en">Section 3</span></h1>';

$string['currentsection'] = 'This topic';
$string['sectionname'] = 'Topic';
$string['pluginname'] = 'Moetabs format';
$string['page-course-view-topics'] = 'Any course main page in onetopic format';
$string['page-course-view-topics-x'] = 'Any course page in onetopic format';
$string['hidefromothers'] = 'Hide topic';
$string['showfromothers'] = 'Show topic';
$string['hidetabsbar'] = 'Hide tabs bar';
$string['hidetabsbar_help'] = 'Hide tabs bar in the course page. The navigation is with the sections navbar.';

$string['movesectionto'] = 'Move current topic';
$string['movesectionto_help'] = 'Move current topic to left/right of selected topic';

$string['utilities'] = 'Tabs edition utilities';
$string['disableajax'] = 'Asynchronous edit actions';
$string['disable'] = 'Disable';
$string['enable'] = 'Enable';
$string['disableajax_help'] = 'Use this action in order to move resources between topic tabs. It only disables the asynchronous actions in current session, it is not permanently.';

$string['subtopictoright'] = 'Move to right as subtopic';

$string['duplicatesection'] = 'Duplicate current topic';
$string['duplicatesection_help'] = 'Used to duplicate the resources of current topic in a new topic';
$string['duplicate'] = 'Duplicate';
$string['duplicating'] = 'Duplicating';
$string['creating_section'] = 'Creating new topic';
$string['rebuild_course_cache'] = 'Rebuild course cache';
$string['duplicate_confirm'] = 'Are you sure you want to duplicate the current topic? The task can take a while depending on the amount of resources.';
$string['cantcreatesection'] = 'Error creating a new topic';
$string['progress_counter'] = 'Duplicating activities ({$a->current}/{$a->size})';
$string['progress_full'] = 'Duplicating topic';
$string['error_nosectioninfo'] = 'The indicated topic have not information';

$string['level'] = 'Level';
$string['index'] = 'Index';
$string['asprincipal'] = 'Normal, as a first level tab';
$string['asbrother'] = 'Same level that the previous tab';
$string['aschild'] = 'Child of previous tab';
$string['level_help'] = 'Change the tab level.';
$string['fontcolor'] = 'Font color';
$string['fontcolor_help'] = 'Used to change the tab font color. The value can be a color in a CSS valid representation, for example: <ul><li>Hexadecimal: #ffffff</li><li>RGB: rgb(0,255,0)</li><li>Name: green</li></ul>';
$string['bgcolor'] = 'Background color';
$string['bgcolor_help'] = 'Used to change the tab background color. The value can be a color in a CSS valid representation, for example: <ul><li>Hexadecimal: #ffffff</li><li>RGB: rgb(0,255,0)</li><li>Name: green</li></ul>';
$string['cssstyles'] = 'CSS properties';
$string['cssstyles_help'] = 'Used to change CSS properties of the tab. Use a standard value to the attribute <em>style</em> in a html tag. Example: <br /><strong>font-weight: bold; font-size: 16px;</strong>';
$string['firsttabtext'] = 'Text of the first tab in sublevel';
$string['firsttabtext_help'] = 'If this tab has sublevels, this will be the text of the first tab';

$string['coursedisplay'] = 'Visualization mode of section 0';
$string['coursedisplay_help'] = 'This define as display the section 0: as a first tab or as section before the tabs bar.';
$string['coursedisplay_single'] = 'As tab';
$string['coursedisplay_multi'] = 'Before the tabs';

$string['templatetopic'] = 'Use topic summary as template';
$string['templatetopic_help'] = 'This option is used in order to use the summary topic as a template. If it is used as template, you can include the resources in the content, not only as tradicional moodle\'s lists. <br />In order to include a resource, write the resource name between double brackets, for example: [[News forum]]. This functionality is similar to activity name filter, however, it is different because the user can chose if included the resource icon and decide than activities are be included.';
$string['templetetopic_not'] = 'No, display as default';
$string['templetetopic_single'] = 'Yes, use the summary as template';
$string['templetetopic_list'] = 'Yes, use the summary as template, list the resources that are not referenced';
$string['templatetopic_icons'] = 'Show icon in resource links in summary';
$string['templatetopic_icons_help'] = 'This option defines if the icons are displayed in the summary when it is a template.';

$string['utilities'] = 'Tabs edition utilities';
$string['display_summary'] = 'move out of grid';
$string['display_summary_alt'] = 'Move this section out of the grid';
$string['editimage'] = 'Change image';
$string['editimage_alt'] = 'Set or change image';
$string['formatgrid'] = 'Grid format'; // Name to display for format.
$string['general_information'] = 'General Information'; // No longer used kept for legacy versions.
$string['hidden_topic'] = 'This section has been hidden';
$string['hide_summary'] = 'move section into grid';
$string['hide_summary_alt'] = 'Move this section into the grid';
$string['namegrid'] = 'Grid view';
$string['title'] = 'Section title';
$string['topic'] = 'Section';
$string['topic0'] = 'General';
$string['topicoutline'] = 'Section'; // No longer used kept for legacy versions.

// Moodle 2.0 Enhancement - Moodle Tracker MDL-15252, MDL-21693 & MDL-22056 - http://docs.moodle.org/en/Development:Languages.
$string['sectionname'] = 'Section';
$string['pluginname'] = 'Moetabs format';
$string['section0name'] = 'General';

// WAI-ARIA - http://www.w3.org/TR/wai-aria/roles.
$string['gridimagecontainer'] = 'Grid images';
$string['closeshadebox'] = 'Close shade box';
$string['previoussection'] = 'Previous section';
$string['nextsection'] = 'Next section';
$string['shadeboxcontent'] = 'Shade box content';

// MDL-26105.
$string['page-course-view-grid'] = 'Any course main page in the grid format';
$string['page-course-view-grid-x'] = 'Any course page in the grid format';

// Moodle 2.3 Enhancement.
$string['hidefromothers'] = 'Hide section'; // No longer used kept for legacy versions.
$string['showfromothers'] = 'Show section'; // No longer used kept for legacy versions.
$string['currentsection'] = 'This section'; // No longer used kept for legacy versions.
$string['markedthissection'] = 'This section is highlighted as the current section';
$string['markthissection'] = 'Highlight this section as the current section';
// Moodle 3.0 Enhancement.
$string['editsection'] = 'Edit section';
$string['deletesection'] = 'Delete section';

// Moodle 2.4 Course format refactoring - MDL-35218.
$string['numbersections'] = 'Number of sections';

// Exception messages.
$string['imagecannotbeused'] = 'Image cannot be used, must be a PNG, JPG or GIF and the GD PHP extension must be installed.';
$string['cannotfinduploadedimage'] = 'Cannot find the uploaded original image.  Please report error details and the information contained in the php.log file to developer.  Refresh the page and upload a fresh copy of the image.';
$string['cannotconvertuploadedimagetodisplayedimage'] = 'Cannot convert uploaded image to displayed image.  Please report error details and the information contained in the php.log file to developer.';
$string['cannotgetimagesforcourse'] = 'Cannot get images for course.  Please report error details to developer.';

// CONTRIB-4099 Image container size change improvement.
$string['off'] = 'Off';
$string['on'] = 'On';
$string['scale'] = 'Scale';
$string['crop'] = 'Crop';
$string['imagefile'] = 'Upload an image';
$string['imagefile_help'] = 'Upload an image of type PNG, JPG or GIF.';
$string['deleteimage'] = 'Delete image';
$string['deleteimage_help'] = "Delete the image for the section being edited.  If you've uploaded an image then it will not replace the deleted image.";
$string['gfreset'] = 'Grid reset options';
$string['gfreset_help'] = 'Reset to Grid defaults.';
$string['defaultimagecontainerwidth'] = 'Default width of the image container';
$string['defaultimagecontainerwidth_desc'] = 'The default width of the image container.';
$string['defaultimagecontainerratio'] = 'Default ratio of the image container relative to the width';
$string['defaultimagecontainerratio_desc'] = 'The default ratio of the image container relative to the width.';
$string['defaultimageresizemethod'] = 'Default image resize method';
$string['defaultimageresizemethod_desc'] = 'The default method of resizing the image to fit the container.';
$string['defaultbordercolour'] = 'Default image container border colour';
$string['defaultbordercolour_desc'] = 'The default image container border colour.';
$string['defaultborderradius'] = 'Default border radius';
$string['defaultborderradius_desc'] = 'The default border radius on / off.';
$string['defaultborderwidth'] = 'Default border width';
$string['defaultborderwidth_desc'] = 'The default border width.';
$string['defaultimagecontainerbackgroundcolour'] = 'Default image container background colour';
$string['defaultimagecontainerbackgroundcolour_desc'] = 'The default image container background colour.';
$string['defaultcurrentselectedsectioncolour'] = 'Default current selected section colour';
$string['defaultcurrentselectedsectioncolour_desc'] = 'The default current selected section colour.';
$string['defaultcurrentselectedimagecontainercolour'] = 'Default current selected image container colour';
$string['defaultcurrentselectedimagecontainercolour_desc'] = 'The default current selected image container colour.';

$string['defaultcoursedisplay'] = 'Course display default';
$string['defaultcoursedisplay_desc'] = "Either show all the sections on a single page or section zero and the chosen section on page.";

$string['defaultfitsectioncontainertowindow'] = 'Fit section container to window by default';
$string['defaultfitsectioncontainertowindow_desc'] = 'The default setting for \'Fit section container to window\'.';

$string['defaultnewactivity'] = 'Show new activity notification image default';
$string['defaultnewactivity_desc'] = "Show the new activity notification image when a new activity or resource are added to a section default.";

$string['setimagecontainerwidth'] = 'Set the image container width';
$string['setimagecontainerwidth_help'] = 'Set the image container width to one of: 128, 192, 210, 256, 320, 384, 448, 512, 576, 640, 704 or 768';
$string['setimagecontainerratio'] = 'Set the image container ratio relative to the width';
$string['setimagecontainerratio_help'] = 'Set the image container ratio to one of: 3-2, 3-1, 3-3, 2-3, 1-3, 4-3 or 3-4.';
$string['setimageresizemethod'] = 'Set the image resize method';
$string['setimageresizemethod_help'] = "Set the image resize method to: 'Scale' or 'Crop' when resizing the image to fit the container.";
$string['setbordercolour'] = 'Set the border colour';
$string['setbordercolour_help'] = 'Set the border colour in hexidecimal RGB.';
$string['setborderradius'] = 'Set the border radius on / off';
$string['setborderradius_help'] = 'Set the border radius on or off.';
$string['setborderwidth'] = 'Set the border width';
$string['setborderwidth_help'] = 'Set the border width between 1 and 10.';
$string['setimagecontainerbackgroundcolour'] = 'Set the image container background colour';
$string['setimagecontainerbackgroundcolour_help'] = 'Set the image container background colour in hexidecimal RGB.';
$string['setcurrentselectedsectioncolour'] = 'Set the current selected section colour';
$string['setcurrentselectedsectioncolour_help'] = 'Set the current selected section colour in hexidecimal RGB.';
$string['setcurrentselectedimagecontainercolour'] = 'Set the current selected image container colour';
$string['setcurrentselectedimagecontainercolour_help'] = 'Set the current selected image container colour in hexidecimal RGB.';

$string['setnewactivity'] = 'Show new activity notification image';
$string['setnewactivity_help'] = "Show the new activity notification image when a new activity or resource are added to a section.";

$string['setfitsectioncontainertowindow'] = 'Fit the section popup to the window';
$string['setfitsectioncontainertowindow_help'] = 'If enabled, the popup box with the contents of the section will fit to the size of the window and will scroll inside if necessary.  If disabled, the entire page will scroll instead.';

$string['colourrule'] = "Please enter a valid RGB colour, six hexadecimal digits.";

// Reset.
$string['resetgrp'] = 'Reset:';
$string['resetallgrp'] = 'Reset all:';
$string['resetimagecontainersize'] = 'Image container size';
$string['resetimagecontainersize_help'] = 'Resets the image container size to the default value so it will be the same as a course the first time it is in the Grid format.';
$string['resetallimagecontainersize'] = 'Image container sizes';
$string['resetallimagecontainersize_help'] = 'Resets the image container sizes to the default value for all courses so it will be the same as a course the first time it is in the Grid format.';
$string['resetimageresizemethod'] = 'Image resize method';
$string['resetimageresizemethod_help'] = 'Resets the image resize method to the default value so it will be the same as a course the first time it is in the Grid format.';
$string['resetallimageresizemethod'] = 'Image resize methods';
$string['resetallimageresizemethod_help'] = 'Resets the image resize methods to the default value for all courses so it will be the same as a course the first time it is in the Grid format.';
$string['resetimagecontainerstyle'] = 'Image container style';
$string['resetimagecontainerstyle_help'] = 'Resets the image container style to the default value so it will be the same as a course the first time it is in the Grid format.';
$string['resetallimagecontainerstyle'] = 'Image container styles';
$string['resetallimagecontainerstyle_help'] = 'Resets the image container styles to the default value for all courses so it will be the same as a course the first time it is in the Grid format.';
$string['resetnewactivity'] = 'New activity';
$string['resetnewactivity_help'] = 'Resets the new activity notification image to the default value so it will be the same as a course the first time it is in the Grid format.';
$string['resetallnewactivity'] = 'New activities';
$string['resetallnewactivity_help'] = 'Resets the new activity notification images to the default value for all courses so it will be the same as a course the first time it is in the Grid format.';
$string['resetfitpopup'] = 'Fit section popup to the window';
$string['resetfitpopup_help'] = 'Resets the \'Fit section popup to the window\' to the default value so it will be the same as a course the first time it is in the Grid format.';
$string['resetallfitpopup'] = 'Fit section popups to the window';
$string['resetallfitpopup_help'] = 'Resets the \'Fit section popup to the window\' to the default value for all courses so it will be the same as a course the first time it is in the Grid format.';

// Capabilities.
$string['grid:changeimagecontainersize'] = 'Change or reset the image container size';
$string['grid:changeimageresizemethod'] = 'Change or reset the image resize method';
$string['grid:changeimagecontainerstyle'] = 'Change or reset the image container style';
