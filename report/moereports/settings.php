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

defined('MOODLE_INTERNAL') || die;

    $ADMIN->add('reports', new admin_category('report_moereports', get_string('pluginname', 'report_moereports')));
    $ADMIN->add('report_moereports', new admin_externalpage('schools info', get_string('schools_info', 'report_moereports'),
    $CFG->wwwroot.'/report/moereports/view.php'));
    $ADMIN->add('report_moereports', new admin_externalpage('classes info', get_string('classesinfo', 'report_moereports'),
    $CFG->wwwroot.'/report/moereports/classes_report.php'));
    $ADMIN->add('report_moereports', new admin_externalpage('users info', get_string('usersinfo', 'report_moereports'),
        $CFG->wwwroot.'/report/moereports/users_report.php'));

    $settings = new admin_settingpage('viewpermission', get_string('moeviewpermission', 'report_moereports'));

    $settings->add( new admin_setting_configcheckbox(
    		
    		// This is the reference you will use to your configuration
    		'report_moereports/moereportsenable',
    		
    		// This is the friendly title for the config, which will be displayed
    		get_string('moereportsenable', 'report_moereports'),
    		
    		// This is helper text for this config field
    		get_string('moereportsenablehelper', 'report_moereports'),
    		
    		// This is the default value
    		0
    		
    		) );
    
    
    // Add a setting field to the settings for this page
    $settings->add( new admin_setting_configtext(

        // This is the reference you will use to your configuration
        'schools_level_access',

        // This is the friendly title for the config, which will be displayed
        get_string('schools_level_report', 'report_moereports'),

        // This is helper text for this config field
        get_string('reportviewhelper', 'report_moereports'),

        // This is the default value
        '',

        // This is the type of Parameter this config is
        PARAM_TEXT

        ) );

    $settings->add( new admin_setting_configtext(

        // This is the reference you will use to your configuration
        'regin_level_access',

        // This is the friendly title for the config, which will be displayed
        get_string('regin_level_report', 'report_moereports'),

        // This is helper text for this config field
        get_string('reportviewhelper', 'report_moereports'),

        // This is the default value
        '',

        // This is the type of Parameter this config is
        PARAM_TEXT

        ) );
    
    

    
    