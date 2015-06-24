<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('help_readme', '<a onclick="window.open(\''.$CFG->wwwroot.'/theme/avalon/readme.html\',\'help\',\'height=640,width=560,resizable=yes\');" href="#">'.get_string('helpreadme','theme_avalon').'</a>','') );


    //Load theme config for default color settings
    // (1) We use it to display logo image preview, if images are set.
    // (2) Farther down the page, we use it to load subthemedefaults
    $theme_config = theme_config::load('avalon');

    // Use small logo
    $settings_usesmalllogo = false;
    $name = 'theme_avalon/usesmalllogo';
    $title = get_string('usesmalllogo','theme_avalon');
    $description = get_string('usesmalllogo_desc', 'theme_avalon');
    $setting = new admin_setting_configcheckbox($name, $title, $description, $settings_usesmalllogo,true, false );
    $settings->add($setting);

    $settings_href_target = array(
        'newwindow' => get_string('newwindow','theme_avalon'),
        'samewindow' => get_string('samewindow','theme_avalon'));

    $setting_logos = array('logocollege','logocollegertl','logocollegefooter','logocollegefooterrtl',
                           'topbanner','topbannerrtl','topbannerfar','topbannerfarrtl','topbanneronepixel');

    foreach ($setting_logos as $logoname) {
        // logo image setting (ltr)
        $name = 'theme_avalon/'.$logoname;
        $title = get_string($logoname,'theme_avalon');
        $description = get_string($logoname.'_desc', 'theme_avalon');
        $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL,60);
        $settings->add($setting);

        if (!empty($theme_config->settings->{$logoname})) {
            $settings->add(new admin_setting_heading($logoname.'_preview', $title.' Preview', '<img src="'.$theme_config->settings->{$logoname}.'">'));
        }

        if ($logoname == 'logocollege') {
            $name = 'theme_avalon/'.$logoname.'_url';
            $title = get_string($logoname.'_url','theme_avalon');
            $description = get_string($logoname.'_url_desc', 'theme_avalon');
            $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
            $settings->add($setting);

            // Link is open in the SameWindow or a NewWindow
            $name = 'theme_avalon/'.$logoname.'_urltarget';
            $title = get_string('urltarget','theme_avalon');
            $description = get_string('urltarget_desc', 'theme_avalon');
            $setting = new admin_setting_configselect($name, $title, $description,'', $settings_href_target );
            $settings->add($setting);

        }

    }

    // Header Style Setting
    $name = 'theme_avalon/headerstyle';
    $title = get_string('headerstyle', 'theme_avalon');
    $description = get_string('headerstyle_desc', 'theme_avalon');
    $setting = new admin_setting_configtextarea($name, $title , $description, '', PARAM_RAW);
    $settings->add($setting);

    // Override course format TopColl
    $name = 'theme_avalon/overridetopcoll';
    $title = get_string('overridetopcoll','theme_avalon');
    $description = get_string('overridetopcoll_desc', 'theme_avalon');
    $setting = new admin_setting_configcheckbox($name, $title, $description,'1');
    $settings->add($setting);

    $fontlist = array();
    foreach($theme_config->settings->fontlist as $key => $value){
        $fontlist[$key] = $value['name'];
    }

    // Select a special font for the blocks' header
    $name = 'theme_avalon/blockheaderfont';
    $title = get_string('blockheaderfont','theme_avalon');
    $description = get_string('blockheaderfont_desc', 'theme_avalon');
    $setting = new admin_setting_configselect($name, $title, $description, '', $fontlist);
    $settings->add($setting);

    // Select a special font for the page content
    $name = 'theme_avalon/contentfont';
    $title = get_string('contentfont','theme_avalon');
    $description = get_string('contentfont_desc', 'theme_avalon');
    $setting = new admin_setting_configselect($name, $title, $description, '', $fontlist);
    $settings->add($setting);

    // Communication blocks background color setting
	$name = 'theme_avalon/blockbgcolor_communication';
	$title = get_string('linkcolor','theme_avalon');
	$description = get_string('blockbgcolor_communication_desc', 'theme_avalon');
	$default = '#d1ebfb';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

    // Navigation blocks background color setting
    $name = 'theme_avalon/blockbgcolor_navigation';
    $title = get_string('linkcolor','theme_avalon');
    $description = get_string('blockbgcolor_navigation_desc', 'theme_avalon');
    $default = '#dae8b0';
    $previewconfig = NULL;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $settings->add($setting);

    // Information blocks background color setting
    $name = 'theme_avalon/blockbgcolor_information';
    $title = get_string('linkcolor','theme_avalon');
    $description = get_string('blockbgcolor_information_desc', 'theme_avalon');
    $default = '#eadd96';
    $previewconfig = NULL;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $settings->add($setting);

    // Personal blocks background color setting
    $name = 'theme_avalon/blockbgcolor_personal';
    $title = get_string('linkcolor','theme_avalon');
    $description = get_string('blockbgcolor_personal_desc', 'theme_avalon');
    $default = '#ead5b4';
    $previewconfig = NULL;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $settings->add($setting);

    $setting_links = array('email','help','rss','about', 'search','home','support');

    foreach ($setting_links as $service) {
        // link to email service setting
        $name = 'theme_avalon/service_'.$service;
        $title = get_string('service'.$service,'theme_avalon');
        $description = get_string('service'.$service.'_desc', 'theme_avalon');
        $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
        $settings->add($setting);

        // Link is open in the SameWindow or a NewWindow
        $name = 'theme_avalon/service_'.$service.'_urltarget';
        $title = get_string('urltarget','theme_avalon');
        $description = get_string('urltarget_desc', 'theme_avalon');
        $setting = new admin_setting_configselect($name, $title, $description,'', $settings_href_target );
        $settings->add($setting);
    }

    //Load theme config for default color settings
    //$theme_config = theme_config::load('avalon'); // nadav disabled this, and moved it to the begging of the file

    // subtheme selection setting
    $subthemes = array();
    foreach($theme_config->settings->subthemedefaults as $name => $value){
        $subthemes[$name] = $name.' ('.$theme_config->settings->subthemedefaults[$name]['parent'].')';
    }

    $name = 'theme_avalon/subtheme';
    $title = get_string('subtheme','theme_avalon');
    $description = get_string('subtheme_desc', 'theme_avalon');
    $setting = new admin_setting_configselect($name, $title, $description,key($theme_config->settings->subthemedefaults),$subthemes);
    $settings->add($setting);

    //subtheme reset to default colors JS
    $settings->add(new admin_setting_heading('reset_to_defaults', get_string('colors_heading', 'theme_avalon'), buildResetDefaultColorsJS($theme_config->settings->subthemedefaults)));

    //Add configurable color settings
    if (!empty($theme_config->settings->subtheme)){
        $selected_subtheme = $theme_config->settings->subtheme;
    }
    else{
        $selected_subtheme = 'sky';
    }

    foreach($theme_config->settings->subthemedefaults[$selected_subtheme]['colors'] as $color_number => $color_value){
        $name = 'theme_avalon/color'.$color_number;
        $title = get_string('color','theme_avalon').' '.$color_number;
        $description = get_string('color'.$color_number.'_desc', 'theme_avalon');
        $setting = new admin_setting_configtext($name, $title, $description, $color_value);
        $settings->add($setting);
    }

    // College system-wide Notice (above Toipcs) setting
    $name = 'theme_avalon/collegenotice';
    $title = get_string('collegenotice', 'theme_avalon');
    $description = get_string('collegenotice_desc', 'theme_avalon');
    $setting = new admin_setting_configtextarea($name, $title , $description, '', PARAM_RAW);
    $settings->add($setting);

    // College About setting
    $name = 'theme_avalon/collegeabout';
    $title = get_string('collegeabout','theme_avalon');
    $description = get_string('collegeabout_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_TEXT);
    $settings->add($setting);

    // College Footer (links) setting
    $name = 'theme_avalon/collegefooter';
    $title = get_string('collegefooter', 'theme_avalon');
    $description = get_string('collegefooter_desc', 'theme_avalon');
    $setting = new admin_setting_configtextarea($name, $title , $description, '', PARAM_RAW);
    $settings->add($setting);

    // Filtered courselist setting (custom plugin)
    //$categories = make_categories_options();
    if (file_exists($CFG->libdir. '/coursecatlib.php') AND $CFG->version >= 2013051402 ) {
        require_once($CFG->libdir. '/coursecatlib.php');
        $categories = coursecat::make_categories_list();
        $categories['-1'] = get_string('navshowallcourses','theme_avalon');
        //$frontpage = $ADMIN->locate('frontpage');
        $settings->add(new admin_setting_configselect('defaultcoursecategroy',get_string('defaultcoursecategroy', 'theme_avalon'),get_string('defaultcoursecategroydescription', 'theme_avalon'),'-1', $categories));
        $settings->add(new admin_setting_configcheckbox('showonlytopcategories', get_string('showonlytopcategories', 'theme_avalon'), get_string('showonlytopcategoriesdescription', 'theme_avalon'), 0));
        $settings->add(new admin_setting_configcheckbox('sortcoursesbylastaccess', get_string('sortcoursesbylastaccess', 'theme_avalon'), get_string('sortcoursesbylastaccessdescription', 'theme_avalon'), 0));
        //$ADMIN->add('frontpage', $temp);
    }

}