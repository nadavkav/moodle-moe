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
 * Administration settings definitions for the moeworksheets module.
 *
 * @package   mod_moeworksheets
 * @copyright 2010 sysBind
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/moeworksheets/lib.php');

// First get a list of moeworksheets reports with there own settings pages. If there none,
// we use a simpler overall menu structure.
$reports = core_component::get_plugin_list_with_file('moeworksheets', 'settings.php', false);
$reportsbyname = array();
foreach ($reports as $report => $reportdir) {
    $strreportname = get_string($report . 'report', 'moeworksheets_'.$report);
    $reportsbyname[$strreportname] = $report;
}
core_collator::ksort($reportsbyname);

// First get a list of moeworksheets reports with there own settings pages. If there none,
// we use a simpler overall menu structure.
$rules = core_component::get_plugin_list_with_file('moeworksheetsaccess', 'settings.php', false);
$rulesbyname = array();
foreach ($rules as $rule => $ruledir) {
    $strrulename = get_string('pluginname', 'moeworksheetsaccess_' . $rule);
    $rulesbyname[$strrulename] = $rule;
}
core_collator::ksort($rulesbyname);

// Create the moeworksheets settings page.
if (empty($reportsbyname) && empty($rulesbyname)) {
    $pagetitle = get_string('modulename', 'moeworksheets');
} else {
    $pagetitle = get_string('generalsettings', 'admin');
}
$moeworksheetssettings = new admin_settingpage('modsettingmoeworksheets', $pagetitle, 'moodle/site:config');

if ($ADMIN->fulltree) {
    // Introductory explanation that all the settings are defaults for the add moeworksheets form.
    $moeworksheetssettings->add(new admin_setting_heading('moeworksheetsintro', '', get_string('configintro', 'moeworksheets')));

    // Time limit.
    $moeworksheetssettings->add(new admin_setting_configduration_with_advanced('moeworksheets/timelimit',
            get_string('timelimit', 'moeworksheets'), get_string('configtimelimitsec', 'moeworksheets'),
            array('value' => '0', 'adv' => false), 60));

    // What to do with overdue attempts.
    $moeworksheetssettings->add(new mod_moeworksheets_admin_setting_overduehandling('moeworksheets/overduehandling',
            get_string('overduehandling', 'moeworksheets'), get_string('overduehandling_desc', 'moeworksheets'),
            array('value' => 'autosubmit', 'adv' => false), null));

    // Grace period time.
    $moeworksheetssettings->add(new admin_setting_configduration_with_advanced('moeworksheets/graceperiod',
            get_string('graceperiod', 'moeworksheets'), get_string('graceperiod_desc', 'moeworksheets'),
            array('value' => '86400', 'adv' => false)));

    // Minimum grace period used behind the scenes.
    $moeworksheetssettings->add(new admin_setting_configduration('moeworksheets/graceperiodmin',
            get_string('graceperiodmin', 'moeworksheets'), get_string('graceperiodmin_desc', 'moeworksheets'),
            60, 1));

    // Number of attempts.
    $options = array(get_string('unlimited'));
    for ($i = 1; $i <= moeworksheets_MAX_ATTEMPT_OPTION; $i++) {
        $options[$i] = $i;
    }
    $moeworksheetssettings->add(new admin_setting_configselect_with_advanced('moeworksheets/attempts',
            get_string('attemptsallowed', 'moeworksheets'), get_string('configattemptsallowed', 'moeworksheets'),
            array('value' => 0, 'adv' => false), $options));

    // Grading method.
    $moeworksheetssettings->add(new mod_moeworksheets_admin_setting_grademethod('moeworksheets/grademethod',
            get_string('grademethod', 'moeworksheets'), get_string('configgrademethod', 'moeworksheets'),
            array('value' => moeworksheets_GRADEHIGHEST, 'adv' => false), null));

    // Maximum grade.
    $moeworksheetssettings->add(new admin_setting_configtext('moeworksheets/maximumgrade',
            get_string('maximumgrade'), get_string('configmaximumgrade', 'moeworksheets'), 10, PARAM_INT));

    // Questions per page.
    $perpage = array();
    $perpage[0] = get_string('never');
    $perpage[1] = get_string('aftereachquestion', 'moeworksheets');
    for ($i = 2; $i <= moeworksheets_MAX_QPP_OPTION; ++$i) {
        $perpage[$i] = get_string('afternquestions', 'moeworksheets', $i);
    }
    $moeworksheetssettings->add(new admin_setting_configselect_with_advanced('moeworksheets/questionsperpage',
            get_string('newpageevery', 'moeworksheets'), get_string('confignewpageevery', 'moeworksheets'),
            array('value' => 1, 'adv' => false), $perpage));

    // Navigation method.
    $moeworksheetssettings->add(new admin_setting_configselect_with_advanced('moeworksheets/navmethod',
            get_string('navmethod', 'moeworksheets'), get_string('confignavmethod', 'moeworksheets'),
            array('value' => moeworksheets_NAVMETHOD_FREE, 'adv' => true), moeworksheets_get_navigation_options()));

    // Shuffle within questions.
    $moeworksheetssettings->add(new admin_setting_configcheckbox_with_advanced('moeworksheets/shuffleanswers',
            get_string('shufflewithin', 'moeworksheets'), get_string('configshufflewithin', 'moeworksheets'),
            array('value' => 1, 'adv' => false)));

    // Preferred behaviour.
    $moeworksheetssettings->add(new admin_setting_question_behaviour('moeworksheets/preferredbehaviour',
            get_string('howquestionsbehave', 'question'), get_string('howquestionsbehave_desc', 'moeworksheets'),
            'deferredfeedback'));

    // Can redo completed questions.
    $moeworksheetssettings->add(new admin_setting_configselect_with_advanced('moeworksheets/canredoquestions',
            get_string('canredoquestions', 'moeworksheets'), get_string('canredoquestions_desc', 'moeworksheets'),
            array('value' => 0, 'adv' => true),
            array(0 => get_string('no'), 1 => get_string('canredoquestionsyes', 'moeworksheets'))));

    // Each attempt builds on last.
    $moeworksheetssettings->add(new admin_setting_configcheckbox_with_advanced('moeworksheets/attemptonlast',
            get_string('eachattemptbuildsonthelast', 'moeworksheets'),
            get_string('configeachattemptbuildsonthelast', 'moeworksheets'),
            array('value' => 0, 'adv' => true)));

    // Review options.
    $moeworksheetssettings->add(new admin_setting_heading('reviewheading',
            get_string('reviewoptionsheading', 'moeworksheets'), ''));
    foreach (mod_moeworksheets_admin_review_setting::fields() as $field => $name) {
        $default = mod_moeworksheets_admin_review_setting::all_on();
        $forceduring = null;
        if ($field == 'attempt') {
            $forceduring = true;
        } else if ($field == 'overallfeedback') {
            $default = $default ^ mod_moeworksheets_admin_review_setting::DURING;
            $forceduring = false;
        }
        $moeworksheetssettings->add(new mod_moeworksheets_admin_review_setting('moeworksheets/review' . $field,
                $name, '', $default, $forceduring));
    }

    // Show the user's picture.
    $moeworksheetssettings->add(new mod_moeworksheets_admin_setting_user_image('moeworksheets/showuserpicture',
            get_string('showuserpicture', 'moeworksheets'), get_string('configshowuserpicture', 'moeworksheets'),
            array('value' => 0, 'adv' => false), null));

    // Decimal places for overall grades.
    $options = array();
    for ($i = 0; $i <= moeworksheets_MAX_DECIMAL_OPTION; $i++) {
        $options[$i] = $i;
    }
    $moeworksheetssettings->add(new admin_setting_configselect_with_advanced('moeworksheets/decimalpoints',
            get_string('decimalplaces', 'moeworksheets'), get_string('configdecimalplaces', 'moeworksheets'),
            array('value' => 2, 'adv' => false), $options));

    // Decimal places for question grades.
    $options = array(-1 => get_string('sameasoverall', 'moeworksheets'));
    for ($i = 0; $i <= moeworksheets_MAX_Q_DECIMAL_OPTION; $i++) {
        $options[$i] = $i;
    }
    $moeworksheetssettings->add(new admin_setting_configselect_with_advanced('moeworksheets/questiondecimalpoints',
            get_string('decimalplacesquestion', 'moeworksheets'),
            get_string('configdecimalplacesquestion', 'moeworksheets'),
            array('value' => -1, 'adv' => true), $options));

    // Show blocks during moeworksheets attempts.
    $moeworksheetssettings->add(new admin_setting_configcheckbox_with_advanced('moeworksheets/showblocks',
            get_string('showblocks', 'moeworksheets'), get_string('configshowblocks', 'moeworksheets'),
            array('value' => 0, 'adv' => true)));

    // Password.
    $moeworksheetssettings->add(new admin_setting_configtext_with_advanced('moeworksheets/password',
            get_string('requirepassword', 'moeworksheets'), get_string('configrequirepassword', 'moeworksheets'),
            array('value' => '', 'adv' => false), PARAM_TEXT));

    // IP restrictions.
    $moeworksheetssettings->add(new admin_setting_configtext_with_advanced('moeworksheets/subnet',
            get_string('requiresubnet', 'moeworksheets'), get_string('configrequiresubnet', 'moeworksheets'),
            array('value' => '', 'adv' => true), PARAM_TEXT));

    // Enforced delay between attempts.
    $moeworksheetssettings->add(new admin_setting_configduration_with_advanced('moeworksheets/delay1',
            get_string('delay1st2nd', 'moeworksheets'), get_string('configdelay1st2nd', 'moeworksheets'),
            array('value' => 0, 'adv' => true), 60));
    $moeworksheetssettings->add(new admin_setting_configduration_with_advanced('moeworksheets/delay2',
            get_string('delaylater', 'moeworksheets'), get_string('configdelaylater', 'moeworksheets'),
            array('value' => 0, 'adv' => true), 60));

    // Browser security.
    $moeworksheetssettings->add(new mod_moeworksheets_admin_setting_browsersecurity('moeworksheets/browsersecurity',
            get_string('showinsecurepopup', 'moeworksheets'), get_string('configpopup', 'moeworksheets'),
            array('value' => '-', 'adv' => true), null));

    $moeworksheetssettings->add(new admin_setting_configtext('moeworksheets/initialnumfeedbacks',
            get_string('initialnumfeedbacks', 'moeworksheets'), get_string('initialnumfeedbacks_desc', 'moeworksheets'),
            2, PARAM_INT, 5));

    // Allow user to specify if setting outcomes is an advanced setting.
    if (!empty($CFG->enableoutcomes)) {
        $moeworksheetssettings->add(new admin_setting_configcheckbox('moeworksheets/outcomes_adv',
            get_string('outcomesadvanced', 'moeworksheets'), get_string('configoutcomesadvanced', 'moeworksheets'),
            '0'));
    }

    // Autosave frequency.
    $moeworksheetssettings->add(new admin_setting_configduration('moeworksheets/autosaveperiod',
            get_string('autosaveperiod', 'moeworksheets'), get_string('autosaveperiod_desc', 'moeworksheets'), 60, 1));
}

// Now, depending on whether any reports have their own settings page, add
// the moeworksheets setting page to the appropriate place in the tree.
if (empty($reportsbyname) && empty($rulesbyname)) {
    $ADMIN->add('modsettings', $moeworksheetssettings);
} else {
    $ADMIN->add('modsettings', new admin_category('modsettingsmoeworksheetscat',
            get_string('modulename', 'moeworksheets'), $module->is_enabled() === false));
    $ADMIN->add('modsettingsmoeworksheetscat', $moeworksheetssettings);

    // Add settings pages for the moeworksheets report subplugins.
    foreach ($reportsbyname as $strreportname => $report) {
        $reportname = $report;

        $settings = new admin_settingpage('modsettingsmoeworksheetscat'.$reportname,
                $strreportname, 'moodle/site:config', $module->is_enabled() === false);
        if ($ADMIN->fulltree) {
            include($CFG->dirroot . "/mod/moeworksheets/report/$reportname/settings.php");
        }
        if (!empty($settings)) {
            $ADMIN->add('modsettingsmoeworksheetscat', $settings);
        }
    }

    // Add settings pages for the moeworksheets access rule subplugins.
    foreach ($rulesbyname as $strrulename => $rule) {
        $settings = new admin_settingpage('modsettingsmoeworksheetscat' . $rule,
                $strrulename, 'moodle/site:config', $module->is_enabled() === false);
        if ($ADMIN->fulltree) {
            include($CFG->dirroot . "/mod/moeworksheets/accessrule/$rule/settings.php");
        }
        if (!empty($settings)) {
            $ADMIN->add('modsettingsmoeworksheetscat', $settings);
        }
    }
}

$settings = null; // We do not want standard settings link.
