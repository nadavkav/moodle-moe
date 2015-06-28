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
 * Language strings
 *
 * @package    local
 * @subpackage ltiprovider
 * @copyright  2011 Juan Leyva <juanleyvadelgado@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'מתן גישה חיצונית לרכיביי הקורס';
$string['providetool'] = 'מתן גישה חיצונית לרכיבים מקומיים';

$string['remotesystem'] = 'מערכת מרוחקת';
$string['userdefaultvalues'] = 'ערכי ברירת-מחדל למשתמש';
$string['remoteencoding'] = 'קידוד מערכת מרוחקת';
$string['secret'] = 'קוד סודי';
$string['toolsettings'] = 'הגדרות הכלי';
$string['courseroleinstructor'] = 'תפקיד המורה המתחבר ממערכת מרוחקת בקורס';
$string['courserolelearner'] = 'תפקיד התלמיד המתחבר ממערכת מרוחקת בקורס';
$string['activityroleinstructor'] = 'תפקיד המורה המתחבר ממערכת מרוחקת בפעילות';
$string['activityrolelearner'] = 'תפקיד התלמיד המתחבר ממערכת מרוחקת בפעילות';

$string['tooldisabled'] = 'גישה לכלי אינה מאופשרת';
$string['tooltobeprovide'] = 'הפריט שאליו נרצה לתת גישה';
$string['delconfirm'] = 'האם אתה בטוח שברצונך למחוק את הכלי ?';
$string['deletetool'] = 'מחק את הכלי';
$string['toolsprovided'] = 'רשימת של ספקי גישה';
$string['name'] = 'שם';
$string['url'] = 'כתובת המשאב המרוחק';
$string['layoutandcss'] = 'פריסה ועיצוב CSS';
$string['hidepageheader'] = 'הסתר את הכותרת העליונה';
$string['hidepagefooter'] = 'הסתר את הכותרת התחתונה';
$string['hideleftblocks'] = 'הסתר את המשבצות בצד שמאל';
$string['hiderightblocks'] = 'הסתר את המשבצות בצד ימין';
$string['customcss'] = 'CSS מותאם אישית';
$string['sendgrades'] = 'שלח ציונים בחזרה';
$string['forcenavigation'] = 'הכרח ניווט קורס או פעילות';

$string['enrolperiod'] = 'משך הרישום';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid (in seconds). If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user enrols themselves from the remote system. If disabled, the enrolment duration will be unlimited.';
$string['enrolstartdate'] = 'תאריך תחילת רישום';
$string['enrolstartdate_help'] = 'If enabled, users can access from this date onward only.';
$string['enrolenddate'] = 'תאריך סיום רישום';
$string['enrolenddate_help'] = 'If enabled, users can access until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';

$string['maxenrolled'] = 'מספר משתמשים מירבי';
$string['maxenrolled_help'] = 'Specifies the maximum number of users that can access from the remote system. 0 means no limit.';
$string['maxenrolledreached'] = 'Maximum number of users allowed to access was already reached.';

$string['courseroleinstructor'] = 'תפקיד בקורס עבור מורה';
$string['courserolelearner'] = 'תפקיד בקורס עבור תלמיד';
$string['activityroleinstructor'] = 'תפקיד בפעילות עבור מורה';
$string['activityrolelearner'] = 'תפקיד בפעילות עבור תלמיד';

$string['tooldisabled'] = 'הגישה לרכיב לא זמינה';
$string['tooltobeprovide'] = 'הרכיב אותו נשתף';
$string['delconfirm'] = 'Are you sure you want to delete this tool?';
$string['deletetool'] = 'Delete a tool';
$string['toolsprovided'] = 'רשימת כלים זמינים';
$string['name'] = 'שם הרכיב';
$string['url'] = 'כתובת האינטרנט הציבורית';
$string['layoutandcss'] = 'עיצוב תצוגה';
$string['hidepageheader'] = 'הסתרת כותרת העמוד';
$string['hidepagefooter'] = 'הסתרת כותרת התחתית';
$string['hideleftblocks'] = 'הסתרת משבצת צד שמאל';
$string['hiderightblocks'] = 'הסתרת משבצות צד ימין';
$string['customcss'] = 'Custom CSS';
$string['sendgrades'] = 'שליחת ציונים בחזרה';
$string['forcenavigation'] = 'הכרחת פעולת ניווט ברכיב';

$string['invalidcredentials'] = 'Invalid credentials';
$string['allowframembedding'] = 'In order to avoid problems embedding this site, please enable the allowframembedding setting in Admin -> Security -> HTTP security';
$string['newpopupnotice'] = 'The tool will be opened in a new Window. Please, check that popups for this site are enabled in your browser. You can use the link displayed bellow for opening the tool.';
$string['opentool'] = 'Open tool in a new window';

$string['enrolmentnotstarted'] = 'The enrolment period has not started';
$string['enrolmentfinished'] = 'The enrolment period has finished';
$string['ltiprovider:manage'] = 'Manage tools (provide)';
$string['ltiprovider:view'] = 'View tools provided';

$string['globalsharedsecret'] = 'Global Shared Secret';
$string['defaultauthmethod'] = 'Default auth method';
$string['defaultauthmethodhelp'] = 'This is the auth method assigned a new users created by the plugin';
$string['delegate'] = 'Delegate';
$string['userprofileupdate'] = 'עדכון פרופיל משתמש';
$string['userprofileupdatehelp'] = 'Never for not update the user profile on every remote access, Delegate to be configured at tool level';
$string['rolesallowedcreateresources'] = 'Roles allowed to create resources (from the remote site)';
$string['rolesallowedcreatecontexts'] = 'Roles allowed to create contexts (from the remote site)';
$string['cantdeterminecontext'] = 'Can\' determine the context, it seems that there are more than one tool provided for this context_id';

$string['invalidtplcourse'] = 'Invalid course template id';
$string['missingrequiredtool'] = 'For duplicating a resource, you must point the request to an existing resource type course';
$string['invalidtypetool'] = 'For duplicating a resource, you must point the request to a resource type course';
$string['invalidresourcecopyid'] = 'Invalid resource to be copied identifier';

$string['coursebeingrestored'] = 'This course is being restored, it can take some minutes to finish';

$string['membershipsettings'] = 'הגדרות שרותי רישום משתמשים';
$string['enablememberssync'] = 'אפשרו סינכרון משתמשים';
$string['syncperiod'] = 'סינכרון כל...';
$string['syncmode'] = 'מצב סינכרון';
$string['enrolandunenrol'] = 'רישום משתמשים חדשים והסרת משתמשים חסרים';
$string['enrolnew'] = 'רישום משתמשים חדשים';
$string['unenrolmissing'] = 'הסרת רישום משתמשים';

$string['idnumberformat'] = 'Idnumber format for new created courses';
$string['shortnameformat'] = 'Shortname format for new created courses';
$string['fullnameformat'] = 'Fullname format for new created courses';
$string['genericformathelp'] = 'For remotely new create courses you can select the remote parameters for creating the name';

$string['duplicatecourseswithoutusers'] = 'Duplicate courses without users';
$string['duplicatecourseswithoutusershelp'] = 'When creating a new course, do not import the users from the template course';


