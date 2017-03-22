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
 * Administration settings definitions for the quizsbs module.
 *
 * @package   mod_quizsbs
 * @copyright 2010 Petr Skoda
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quizsbs/lib.php');

// First get a list of quizsbs reports with there own settings pages. If there none,
// we use a simpler overall menu structure.
$reports = core_component::get_plugin_list_with_file('quizsbs', 'settings.php', false);
$reportsbyname = array();
foreach ($reports as $report => $reportdir) {
    $strreportname = get_string($report . 'report', 'quizsbs_'.$report);
    $reportsbyname[$strreportname] = $report;
}
core_collator::ksort($reportsbyname);

// First get a list of quizsbs reports with there own settings pages. If there none,
// we use a simpler overall menu structure.
$rules = core_component::get_plugin_list_with_file('quizsbsaccess', 'settings.php', false);
$rulesbyname = array();
foreach ($rules as $rule => $ruledir) {
    $strrulename = get_string('pluginname', 'quizsbsaccess_' . $rule);
    $rulesbyname[$strrulename] = $rule;
}
core_collator::ksort($rulesbyname);

// Create the quizsbs settings page.
if (empty($reportsbyname) && empty($rulesbyname)) {
    $pagetitle = get_string('modulename', 'quizsbs');
} else {
    $pagetitle = get_string('generalsettings', 'admin');
}
$quizsbssettings = new admin_settingpage('modsettingquizsbs', $pagetitle, 'moodle/site:config');

if ($ADMIN->fulltree) {
    // Introductory explanation that all the settings are defaults for the add quizsbs form.
    $quizsbssettings->add(new admin_setting_heading('quizsbsintro', '', get_string('configintro', 'quizsbs')));

    // Time limit.
    $quizsbssettings->add(new admin_setting_configduration_with_advanced('quizsbs/timelimit',
            get_string('timelimit', 'quizsbs'), get_string('configtimelimitsec', 'quizsbs'),
            array('value' => '0', 'adv' => false), 60));

    // What to do with overdue attempts.
    $quizsbssettings->add(new mod_quizsbs_admin_setting_overduehandling('quizsbs/overduehandling',
            get_string('overduehandling', 'quizsbs'), get_string('overduehandling_desc', 'quizsbs'),
            array('value' => 'autosubmit', 'adv' => false), null));

    // Grace period time.
    $quizsbssettings->add(new admin_setting_configduration_with_advanced('quizsbs/graceperiod',
            get_string('graceperiod', 'quizsbs'), get_string('graceperiod_desc', 'quizsbs'),
            array('value' => '86400', 'adv' => false)));

    // Minimum grace period used behind the scenes.
    $quizsbssettings->add(new admin_setting_configduration('quizsbs/graceperiodmin',
            get_string('graceperiodmin', 'quizsbs'), get_string('graceperiodmin_desc', 'quizsbs'),
            60, 1));

    // Number of attempts.
    $options = array(get_string('unlimited'));
    for ($i = 1; $i <= quizsbs_MAX_ATTEMPT_OPTION; $i++) {
        $options[$i] = $i;
    }
    $quizsbssettings->add(new admin_setting_configselect_with_advanced('quizsbs/attempts',
            get_string('attemptsallowed', 'quizsbs'), get_string('configattemptsallowed', 'quizsbs'),
            array('value' => 0, 'adv' => false), $options));

    // Grading method.
    $quizsbssettings->add(new mod_quizsbs_admin_setting_grademethod('quizsbs/grademethod',
            get_string('grademethod', 'quizsbs'), get_string('configgrademethod', 'quizsbs'),
            array('value' => quizsbs_GRADEHIGHEST, 'adv' => false), null));

    // Maximum grade.
    $quizsbssettings->add(new admin_setting_configtext('quizsbs/maximumgrade',
            get_string('maximumgrade'), get_string('configmaximumgrade', 'quizsbs'), 10, PARAM_INT));

    // Questions per page.
    $perpage = array();
    $perpage[0] = get_string('never');
    $perpage[1] = get_string('aftereachquestion', 'quizsbs');
    for ($i = 2; $i <= quizsbs_MAX_QPP_OPTION; ++$i) {
        $perpage[$i] = get_string('afternquestions', 'quizsbs', $i);
    }
    $quizsbssettings->add(new admin_setting_configselect_with_advanced('quizsbs/questionsperpage',
            get_string('newpageevery', 'quizsbs'), get_string('confignewpageevery', 'quizsbs'),
            array('value' => 1, 'adv' => false), $perpage));

    // Navigation method.
    $quizsbssettings->add(new admin_setting_configselect_with_advanced('quizsbs/navmethod',
            get_string('navmethod', 'quizsbs'), get_string('confignavmethod', 'quizsbs'),
            array('value' => quizsbs_NAVMETHOD_FREE, 'adv' => true), quizsbs_get_navigation_options()));

    // Shuffle within questions.
    $quizsbssettings->add(new admin_setting_configcheckbox_with_advanced('quizsbs/shuffleanswers',
            get_string('shufflewithin', 'quizsbs'), get_string('configshufflewithin', 'quizsbs'),
            array('value' => 1, 'adv' => false)));

    // Preferred behaviour.
    $quizsbssettings->add(new admin_setting_question_behaviour('quizsbs/preferredbehaviour',
            get_string('howquestionsbehave', 'question'), get_string('howquestionsbehave_desc', 'quizsbs'),
            'deferredfeedback'));

    // Can redo completed questions.
    $quizsbssettings->add(new admin_setting_configselect_with_advanced('quizsbs/canredoquestions',
            get_string('canredoquestions', 'quizsbs'), get_string('canredoquestions_desc', 'quizsbs'),
            array('value' => 0, 'adv' => true),
            array(0 => get_string('no'), 1 => get_string('canredoquestionsyes', 'quizsbs'))));

    // Each attempt builds on last.
    $quizsbssettings->add(new admin_setting_configcheckbox_with_advanced('quizsbs/attemptonlast',
            get_string('eachattemptbuildsonthelast', 'quizsbs'),
            get_string('configeachattemptbuildsonthelast', 'quizsbs'),
            array('value' => 0, 'adv' => true)));

    // Review options.
    $quizsbssettings->add(new admin_setting_heading('reviewheading',
            get_string('reviewoptionsheading', 'quizsbs'), ''));
    foreach (mod_quizsbs_admin_review_setting::fields() as $field => $name) {
        $default = mod_quizsbs_admin_review_setting::all_on();
        $forceduring = null;
        if ($field == 'attempt') {
            $forceduring = true;
        } else if ($field == 'overallfeedback') {
            $default = $default ^ mod_quizsbs_admin_review_setting::DURING;
            $forceduring = false;
        }
        $quizsbssettings->add(new mod_quizsbs_admin_review_setting('quizsbs/review' . $field,
                $name, '', $default, $forceduring));
    }

    // Show the user's picture.
    $quizsbssettings->add(new mod_quizsbs_admin_setting_user_image('quizsbs/showuserpicture',
            get_string('showuserpicture', 'quizsbs'), get_string('configshowuserpicture', 'quizsbs'),
            array('value' => 0, 'adv' => false), null));

    // Decimal places for overall grades.
    $options = array();
    for ($i = 0; $i <= quizsbs_MAX_DECIMAL_OPTION; $i++) {
        $options[$i] = $i;
    }
    $quizsbssettings->add(new admin_setting_configselect_with_advanced('quizsbs/decimalpoints',
            get_string('decimalplaces', 'quizsbs'), get_string('configdecimalplaces', 'quizsbs'),
            array('value' => 2, 'adv' => false), $options));

    // Decimal places for question grades.
    $options = array(-1 => get_string('sameasoverall', 'quizsbs'));
    for ($i = 0; $i <= quizsbs_MAX_Q_DECIMAL_OPTION; $i++) {
        $options[$i] = $i;
    }
    $quizsbssettings->add(new admin_setting_configselect_with_advanced('quizsbs/questiondecimalpoints',
            get_string('decimalplacesquestion', 'quizsbs'),
            get_string('configdecimalplacesquestion', 'quizsbs'),
            array('value' => -1, 'adv' => true), $options));

    // Show blocks during quizsbs attempts.
    $quizsbssettings->add(new admin_setting_configcheckbox_with_advanced('quizsbs/showblocks',
            get_string('showblocks', 'quizsbs'), get_string('configshowblocks', 'quizsbs'),
            array('value' => 0, 'adv' => true)));

    // Password.
    $quizsbssettings->add(new admin_setting_configtext_with_advanced('quizsbs/password',
            get_string('requirepassword', 'quizsbs'), get_string('configrequirepassword', 'quizsbs'),
            array('value' => '', 'adv' => false), PARAM_TEXT));

    // IP restrictions.
    $quizsbssettings->add(new admin_setting_configtext_with_advanced('quizsbs/subnet',
            get_string('requiresubnet', 'quizsbs'), get_string('configrequiresubnet', 'quizsbs'),
            array('value' => '', 'adv' => true), PARAM_TEXT));

    // Enforced delay between attempts.
    $quizsbssettings->add(new admin_setting_configduration_with_advanced('quizsbs/delay1',
            get_string('delay1st2nd', 'quizsbs'), get_string('configdelay1st2nd', 'quizsbs'),
            array('value' => 0, 'adv' => true), 60));
    $quizsbssettings->add(new admin_setting_configduration_with_advanced('quizsbs/delay2',
            get_string('delaylater', 'quizsbs'), get_string('configdelaylater', 'quizsbs'),
            array('value' => 0, 'adv' => true), 60));

    // Browser security.
    $quizsbssettings->add(new mod_quizsbs_admin_setting_browsersecurity('quizsbs/browsersecurity',
            get_string('showinsecurepopup', 'quizsbs'), get_string('configpopup', 'quizsbs'),
            array('value' => '-', 'adv' => true), null));

    $quizsbssettings->add(new admin_setting_configtext('quizsbs/initialnumfeedbacks',
            get_string('initialnumfeedbacks', 'quizsbs'), get_string('initialnumfeedbacks_desc', 'quizsbs'),
            2, PARAM_INT, 5));

    // Allow user to specify if setting outcomes is an advanced setting.
    if (!empty($CFG->enableoutcomes)) {
        $quizsbssettings->add(new admin_setting_configcheckbox('quizsbs/outcomes_adv',
            get_string('outcomesadvanced', 'quizsbs'), get_string('configoutcomesadvanced', 'quizsbs'),
            '0'));
    }

    // Autosave frequency.
    $quizsbssettings->add(new admin_setting_configduration('quizsbs/autosaveperiod',
            get_string('autosaveperiod', 'quizsbs'), get_string('autosaveperiod_desc', 'quizsbs'), 60, 1));
}

// Now, depending on whether any reports have their own settings page, add
// the quizsbs setting page to the appropriate place in the tree.
if (empty($reportsbyname) && empty($rulesbyname)) {
    $ADMIN->add('modsettings', $quizsbssettings);
} else {
    $ADMIN->add('modsettings', new admin_category('modsettingsquizsbscat',
            get_string('modulename', 'quizsbs'), $module->is_enabled() === false));
    $ADMIN->add('modsettingsquizsbscat', $quizsbssettings);

    // Add settings pages for the quizsbs report subplugins.
    foreach ($reportsbyname as $strreportname => $report) {
        $reportname = $report;

        $settings = new admin_settingpage('modsettingsquizsbscat'.$reportname,
                $strreportname, 'moodle/site:config', $module->is_enabled() === false);
        if ($ADMIN->fulltree) {
            include($CFG->dirroot . "/mod/quizsbs/report/$reportname/settings.php");
        }
        if (!empty($settings)) {
            $ADMIN->add('modsettingsquizsbscat', $settings);
        }
    }

    // Add settings pages for the quizsbs access rule subplugins.
    foreach ($rulesbyname as $strrulename => $rule) {
        $settings = new admin_settingpage('modsettingsquizsbscat' . $rule,
                $strrulename, 'moodle/site:config', $module->is_enabled() === false);
        if ($ADMIN->fulltree) {
            include($CFG->dirroot . "/mod/quizsbs/accessrule/$rule/settings.php");
        }
        if (!empty($settings)) {
            $ADMIN->add('modsettingsquizsbscat', $settings);
        }
    }
}

$settings = null; // We do not want standard settings link.
