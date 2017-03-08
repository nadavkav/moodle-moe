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
 * Parent theme: Bootstrapbase by Bas Brands
 * Built on: Essential by Julian Ridden
 *
 * @package   theme_moe
 * @copyright 2014 redPIthemes
 *
 */

$settings = null;

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('themes', new admin_category('theme_moe', 'Theme-moe'));


    // "settings general" settingpage
    $temp = new admin_settingpage('theme_moe_general',  get_string('settings_general', 'theme_moe'));

    // Logo file setting.
    $name = 'theme_moe/logo';
    $title = get_string('logo', 'theme_moe');
    $description = get_string('logodesc', 'theme_moe');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);




    // Logo resolution.
    $name = 'theme_moe/logo_res';
    $title = get_string('logo_res', 'theme_moe');
    $description = get_string('logo_res_desc', 'theme_moe');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // add/remove downlclock
    $name = 'theme_moe/countdowntimer';
    $title = get_string('countdowntimer', 'theme_moe');
    $description = get_string('countdowntimerdesc', 'theme_moe');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Set time to start the countdown
    $name = 'theme_moe/countdowntimertime';
    $title = get_string('countdowntimertime', 'theme_moe');
    $description = get_string('countdowntimertimedesc', 'theme_moe');
    $setting = new admin_setting_configtime('theme_moe/hourtostart', 'minuttostart', $title, $description, array(
        'h' => 00,
        'm' => 00,
    ));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);


    // Set countdown duration
    $name = 'theme_moe/countdowntimerdoration';
    $title = get_string('countdowntimerdoration', 'theme_moe');
    $description = get_string('countdowntimerdorationdesc', 'theme_moe');
    $setting = new admin_setting_configtime('theme_moe/hourstocountdown', 'minutestocountdown', $title, $description, array(
        'h' => 00,
        'm' => 00,
    ));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Fixed or Variable Width.
    $name = 'theme_moe/pagewidth';
    $title = get_string('pagewidth', 'theme_moe');
    $description = get_string('pagewidthdesc', 'theme_moe');
    $default = 1600;
    $choices = array(
        1600 => get_string('boxed_wide', 'theme_moe'),
        1000 => get_string('boxed_narrow', 'theme_moe'),
        90 => get_string('boxed_variable', 'theme_moe'),
        100 => get_string('full_wide', 'theme_moe')
    );
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Custom or standard block layout.
    $name = 'theme_moe/layout';
    $title = get_string('layout', 'theme_moe');
    $description = get_string('layoutdesc', 'theme_moe');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Footnote setting.
    $name = 'theme_moe/footnote';
    $title = get_string('footnote', 'theme_moe');
    $description = get_string('footnotedesc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Custom CSS file.
    $name = 'theme_moe/customcss';
    $title = get_string('customcss', 'theme_moe');
    $description = get_string('customcssdesc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    $ADMIN->add('theme_moe', $temp);

    // "settings background" settingpage
	$temp = new admin_settingpage('theme_moe_background',  get_string('settings_background', 'theme_moe'));

	// list with provides backgrounds
    $name = 'theme_moe/list_bg';
    $title = get_string('list_bg', 'theme_moe');
    $description = get_string('list_bg_desc', 'theme_moe');
    $default = '0';
    $choices = array(
		'0' => 'Country Road',
		'1' => 'Bokeh Background',
		'2' => 'Blurred Background I',
		'3' => 'Blurred Background II',
		'4' => 'Blurred Background III',
		'5' => 'Cream Pixels (Pattern)',
		'6' => 'MochaGrunge (Pattern)',
		'7' => 'Skulls (Pattern)',
		'8' => 'SOS (Pattern)',
		'9' => 'Squairy Light (Pattern)',
		'10' => 'Subtle White Feathers (Pattern)',
		'11' => 'Tweed (Pattern)',
		'12' => 'Wet Snow (Pattern)');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// upload background image.
    $name = 'theme_moe/pagebackground';
    $title = get_string('pagebackground', 'theme_moe');
    $description = get_string('pagebackgrounddesc', 'theme_moe');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'pagebackground');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// bg repeat.
	$name = 'theme_moe/page_bg_repeat';
    $title = get_string('page_bg_repeat', 'theme_moe');
    $description = get_string('page_bg_repeat_desc', 'theme_moe');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    $ADMIN->add('theme_moe', $temp);

	// "settings colors" settingpage
	$temp = new admin_settingpage('theme_moe_colors',  get_string('settings_colors', 'theme_moe'));

    // Main theme color setting.
    $name = 'theme_moe/maincolor';
    $title = get_string('maincolor', 'theme_moe');
    $description = get_string('maincolordesc', 'theme_moe');
    $default = '#f9bf3b';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Main theme Hover color setting.
    $name = 'theme_moe/mainhovercolor';
    $title = get_string('mainhovercolor', 'theme_moe');
    $description = get_string('mainhovercolordesc', 'theme_moe');
    $default = '#E8B60F';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Link color setting.
    $name = 'theme_moe/linkcolor';
    $title = get_string('linkcolor', 'theme_moe');
    $description = get_string('linkcolordesc', 'theme_moe');
    $default = '#EBA600';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Default Button color setting.
    $name = 'theme_moe/def_buttoncolor';
    $title = get_string('def_buttoncolor', 'theme_moe');
    $description = get_string('def_buttoncolordesc', 'theme_moe');
    $default = '#8ec63f';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Default Button Hover color setting.
    $name = 'theme_moe/def_buttonhovercolor';
    $title = get_string('def_buttonhovercolor', 'theme_moe');
    $description = get_string('def_buttonhovercolordesc', 'theme_moe');
    $default = '#77ae29';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Menu 1. Level color setting.
    $name = 'theme_moe/menufirstlevelcolor';
    $title = get_string('menufirstlevelcolor', 'theme_moe');
    $description = get_string('menufirstlevelcolordesc', 'theme_moe');
    $default = '#3A454b';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Menu 1. Level Links color setting.
    $name = 'theme_moe/menufirstlevel_linkcolor';
    $title = get_string('menufirstlevel_linkcolor', 'theme_moe');
    $description = get_string('menufirstlevel_linkcolordesc', 'theme_moe');
    $default = '#ffffff';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Menu 2. Level color setting.
    $name = 'theme_moe/menusecondlevelcolor';
    $title = get_string('menusecondlevelcolor', 'theme_moe');
    $description = get_string('menusecondlevelcolordesc', 'theme_moe');
    $default = '#f4f4f4';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Menu 2. Level Links color.
    $name = 'theme_moe/menusecondlevel_linkcolor';
    $title = get_string('menusecondlevel_linkcolor', 'theme_moe');
    $description = get_string('menusecondlevel_linkcolordesc', 'theme_moe');
    $default = '#444444';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Footer color setting.
    $name = 'theme_moe/footercolor';
    $title = get_string('footercolor', 'theme_moe');
    $description = get_string('footercolordesc', 'theme_moe');
    $default = '#323A45';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Footer Headings color setting.
    $name = 'theme_moe/footerheadingcolor';
    $title = get_string('footerheadingcolor', 'theme_moe');
    $description = get_string('footerheadingcolordesc', 'theme_moe');
    $default = '#f2f2f2';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Footer Text color setting.
    $name = 'theme_moe/footertextcolor';
    $title = get_string('footertextcolor', 'theme_moe');
    $description = get_string('footertextcolordesc', 'theme_moe');
    $default = '#bdc3c7';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Copyright color setting.
    $name = 'theme_moe/copyrightcolor';
    $title = get_string('copyrightcolor', 'theme_moe');
    $description = get_string('copyrightcolordesc', 'theme_moe');
    $default = '#292F38';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Copyright color setting.
    $name = 'theme_moe/copyright_textcolor';
    $title = get_string('copyright_textcolor', 'theme_moe');
    $description = get_string('copyright_textcolordesc', 'theme_moe');
    $default = '#bdc3c2';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	$ADMIN->add('theme_moe', $temp);

	// "settings socials" settingpage
	$temp = new admin_settingpage('theme_moe_socials',  get_string('settings_socials', 'theme_moe'));
	$temp->add(new admin_setting_heading('theme_moe_socials', get_string('socialsheadingsub', 'theme_moe'),
            format_text(get_string('socialsdesc' , 'theme_moe'), FORMAT_MARKDOWN)));

    // Website url setting.
    $name = 'theme_moe/website';
    $title = get_string('website', 'theme_moe');
    $description = get_string('websitedesc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Mail setting.
    $name = 'theme_moe/socials_mail';
    $title = get_string('socials_mail', 'theme_moe');
    $description = get_string('socials_mail_desc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Facebook url setting.
    $name = 'theme_moe/facebook';
    $title = get_string('facebook', 'theme_moe');
    $description = get_string('facebookdesc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Flickr url setting.
    $name = 'theme_moe/flickr';
    $title = get_string('flickr', 'theme_moe');
    $description = get_string('flickrdesc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Twitter url setting.
    $name = 'theme_moe/twitter';
    $title = get_string('twitter', 'theme_moe');
    $description = get_string('twitterdesc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Google+ url setting.
    $name = 'theme_moe/googleplus';
    $title = get_string('googleplus', 'theme_moe');
    $description = get_string('googleplusdesc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Pinterest url setting.
    $name = 'theme_moe/pinterest';
    $title = get_string('pinterest', 'theme_moe');
    $description = get_string('pinterestdesc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Instagram url setting.
    $name = 'theme_moe/instagram';
    $title = get_string('instagram', 'theme_moe');
    $description = get_string('instagramdesc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // YouTube url setting.
    $name = 'theme_moe/youtube';
    $title = get_string('youtube', 'theme_moe');
    $description = get_string('youtubedesc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// social icons color setting.
    $name = 'theme_moe/socials_color';
    $title = get_string('socials_color', 'theme_moe');
    $description = get_string('socials_color_desc', 'theme_moe');
    $default = '#a9a9a9';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// social icons position
    $name = 'theme_moe/socials_position';
    $title = get_string('socials_position', 'theme_moe');
    $description = get_string('socials_position_desc', 'theme_moe');
    $default = '0';
    $choices = array(
		'0' => 'footer',
		'1' => 'header');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	$ADMIN->add('theme_moe', $temp);

	// "settings fonts" settingpage
	$temp = new admin_settingpage('theme_moe_fonts',  get_string('settings_fonts', 'theme_moe'));

	$name = 'theme_moe/font_body';
    $title = get_string('fontselect_body' , 'theme_moe');
    $description = get_string('fontselectdesc_body', 'theme_moe');
    $default = '1';
    $choices = array(
    	'1' => 'Open Sans',
		'2' => 'Arimo',
		'3' => 'Arvo',
		'4' => 'Bree Serif',
		'5' => 'Cabin',
		'6' => 'Cantata One',
		'7' => 'Crimson Text',
		'8' => 'Droid Sans',
		'9' => 'Droid Serif',
		'10' => 'Gudea',
		'11' => 'Imprima',
		'12' => 'Lekton',
		'13' => 'Nixie One',
		'14' => 'Montserrat',
		'15' => 'Playfair Display',
		'16' => 'Pontano Sans',
		'17' => 'PT Sans',
    	'18' => 'Raleway',
		'19' => 'Ubuntu',
    	'20' => 'Vollkorn');

    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    $name = 'theme_moe/font_heading';
    $title = get_string('fontselect_heading' , 'theme_moe');
    $description = get_string('fontselectdesc_heading', 'theme_moe');
    $default = '1';
    $choices = array(
		'1' => 'Open Sans',
		'2' => 'Abril Fatface',
		'3' => 'Arimo',
		'4' => 'Arvo',
		'5' => 'Bevan',
		'6' => 'Bree Serif',
		'7' => 'Cabin',
		'8' => 'Cantata One',
		'9' => 'Crimson Text',
		'10' => 'Droid Sans',
		'11' => 'Droid Serif',
		'12' => 'Gudea',
		'13' => 'Imprima',
		'14' => 'Josefin Sans',
		'15' => 'Lekton',
		'16' => 'Lobster',
		'17' => 'Nixie One',
		'18' => 'Montserrat',
		'19' => 'Pacifico',
		'20' => 'Playfair Display',
		'21' => 'Pontano Sans',
		'22' => 'PT Sans',
    	'23' => 'Raleway',
		'24' => 'Sansita One',
		'25' => 'Ubuntu',
    	'26' => 'Vollkorn');

    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	$ADMIN->add('theme_moe', $temp);

	// "settings slider" settingpage
	$temp = new admin_settingpage('theme_moe_slider',  get_string('settings_slider', 'theme_moe'));
    $temp->add(new admin_setting_heading('theme_moe_slider', get_string('slideshowheadingsub', 'theme_moe'),
            format_text(get_string('slideshowdesc' , 'theme_moe'), FORMAT_MARKDOWN)));

    /*
     * Slide 1
     */
	 $temp->add(new admin_setting_heading('theme_moe_slider_slide1', get_string('slideshow_slide1', 'theme_moe'), NULL));

    // Image.
    $name = 'theme_moe/slide1image';
    $title = get_string('slideimage', 'theme_moe');
    $description = get_string('slideimagedesc', 'theme_moe');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Title.
    $name = 'theme_moe/slide1';
    $title = get_string('slidetitle', 'theme_moe');
    $description = get_string('slidetitledesc', 'theme_moe');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_moe/slide1caption';
    $title = get_string('slidecaption', 'theme_moe');
    $description = get_string('slidecaptiondesc', 'theme_moe');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// URL.
	$name = 'theme_moe/slide1_url';
    $title = get_string('slide_url', 'theme_moe');
    $description = get_string('slide_url_desc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 2
     */
	 $temp->add(new admin_setting_heading('theme_moe_slider_slide2', get_string('slideshow_slide2', 'theme_moe'), NULL));

    // Image.
    $name = 'theme_moe/slide2image';
    $title = get_string('slideimage', 'theme_moe');
    $description = get_string('slideimagedesc', 'theme_moe');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Title.
    $name = 'theme_moe/slide2';
    $title = get_string('slidetitle', 'theme_moe');
    $description = get_string('slidetitledesc', 'theme_moe');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_moe/slide2caption';
    $title = get_string('slidecaption', 'theme_moe');
    $description = get_string('slidecaptiondesc', 'theme_moe');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// URL.
	$name = 'theme_moe/slide2_url';
    $title = get_string('slide_url', 'theme_moe');
    $description = get_string('slide_url_desc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 3
     */
	 $temp->add(new admin_setting_heading('theme_moe_slider_slide3', get_string('slideshow_slide3', 'theme_moe'), NULL));

    // Image.
    $name = 'theme_moe/slide3image';
    $title = get_string('slideimage', 'theme_moe');
    $description = get_string('slideimagedesc', 'theme_moe');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Title.
    $name = 'theme_moe/slide3';
    $title = get_string('slidetitle', 'theme_moe');
    $description = get_string('slidetitledesc', 'theme_moe');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_moe/slide3caption';
    $title = get_string('slidecaption', 'theme_moe');
    $description = get_string('slidecaptiondesc', 'theme_moe');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// URL.
	$name = 'theme_moe/slide3_url';
    $title = get_string('slide_url', 'theme_moe');
    $description = get_string('slide_url_desc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 4
     */
	 $temp->add(new admin_setting_heading('theme_moe_slider_slide4', get_string('slideshow_slide4', 'theme_moe'), NULL));

    // Image.
    $name = 'theme_moe/slide4image';
    $title = get_string('slideimage', 'theme_moe');
    $description = get_string('slideimagedesc', 'theme_moe');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide4image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Title.
    $name = 'theme_moe/slide4';
    $title = get_string('slidetitle', 'theme_moe');
    $description = get_string('slidetitledesc', 'theme_moe');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_moe/slide4caption';
    $title = get_string('slidecaption', 'theme_moe');
    $description = get_string('slidecaptiondesc', 'theme_moe');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// URL.
	$name = 'theme_moe/slide4_url';
    $title = get_string('slide_url', 'theme_moe');
    $description = get_string('slide_url_desc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 5
     */
	 $temp->add(new admin_setting_heading('theme_moe_slider_slide5', get_string('slideshow_slide5', 'theme_moe'), NULL));

    // Image.
    $name = 'theme_moe/slide5image';
    $title = get_string('slideimage', 'theme_moe');
    $description = get_string('slideimagedesc', 'theme_moe');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide5image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Title.
    $name = 'theme_moe/slide5';
    $title = get_string('slidetitle', 'theme_moe');
    $description = get_string('slidetitledesc', 'theme_moe');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_moe/slide5caption';
    $title = get_string('slidecaption', 'theme_moe');
    $description = get_string('slidecaptiondesc', 'theme_moe');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// URL.
	$name = 'theme_moe/slide5_url';
    $title = get_string('slide_url', 'theme_moe');
    $description = get_string('slide_url_desc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	/*
     * Options
     */
	 $temp->add(new admin_setting_heading('theme_moe_slider_options', get_string('slideshow_options', 'theme_moe'), NULL));

    // Slideshow Pattern
    $name = 'theme_moe/slideshowpattern';
    $title = get_string('slideshowpattern', 'theme_moe');
    $description = get_string('slideshowpatterndesc', 'theme_moe');
    $default = '0';
    $choices = array(
		'0' => 'none',
		'1' => 'pattern1',
		'2' => 'pattern2',
		'3' => 'pattern3',
		'4' => 'pattern4');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Slidshow AutoAdvance
	$name = 'theme_moe/slideshow_advance';
    $title = get_string('slideshow_advance', 'theme_moe');
    $description = get_string('slideshow_advance_desc', 'theme_moe');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 1);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Slidshow Navigation
	$name = 'theme_moe/slideshow_nav';
    $title = get_string('slideshow_nav', 'theme_moe');
    $description = get_string('slideshow_nav_desc', 'theme_moe');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 1);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Slideshow Loader
    $name = 'theme_moe/slideshow_loader';
    $title = get_string('slideshow_loader', 'theme_moe');
    $description = get_string('slideshow_loader_desc', 'theme_moe');
    $default = '0';
    $choices = array(
		'0' => 'bar',
		'1' => 'pie',
		'2' => 'none');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Slideshow Image FX

	$name = 'theme_moe/slideshow_imgfx';
	$title = get_string('slideshow_imgfx', 'theme_moe');
	$description = get_string('slideshow_imgfx_desc', 'theme_moe');
	$setting = new admin_setting_configtext($name, $title, $description, 'random', PARAM_URL);
	$temp->add($setting);

	// Slideshow Text FX
	$name = 'theme_moe/slideshow_txtfx';
	$title = get_string('slideshow_txtfx', 'theme_moe');
	$description = get_string('slideshow_txtfx_desc', 'theme_moe');
	$setting = new admin_setting_configtext($name, $title, $description, 'moveFromLeft', PARAM_URL);
	$temp->add($setting);

	$ADMIN->add('theme_moe', $temp);

	// "frontpage carousel" settingpage
    $temp = new admin_settingpage('theme_moe_carousel', get_string('settings_carousel', 'theme_moe'));
    $temp->add(new admin_setting_heading('theme_moe_carousel', get_string('carouselheadingsub', 'theme_moe'),
            format_text(get_string('carouseldesc' , 'theme_moe'), FORMAT_MARKDOWN)));

    // Position
    $name = 'theme_moe/carousel_position';
    $title = get_string('carousel_position', 'theme_moe');
    $description = get_string('carousel_positiondesc', 'theme_moe');
	$default = '1';
    $choices = array(
		'0' => 'top',
		'1' => 'bottom');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Heading
    $name = 'theme_moe/carousel_h';
    $title = get_string('carousel_h', 'theme_moe');
    $description = get_string('carousel_h_desc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default, PARAM_TEXT);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Heading Style
    $name = 'theme_moe/carousel_hi';
    $title = get_string('carousel_hi', 'theme_moe');
    $description = get_string('carousel_hi_desc', 'theme_moe');
	$default = '3';
    $choices = array(
		'1' => 'Heading h1',
		'2' => 'Heading h2',
		'3' => 'Heading h3',
		'4' => 'Heading h4',
		'5' => 'Heading h5',
		'6' => 'Heading h6');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Additional HTML
	$name = 'theme_moe/carousel_add_html';
    $title = get_string('carousel_add_html', 'theme_moe');
    $description = get_string('carousel_add_html_desc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Number of slides.
    $name = 'theme_moe/carousel_slides';
    $title = get_string('carousel_slides', 'theme_moe');
    $description = get_string('carousel_slides_desc', 'theme_moe');
    $default = 4;
    $choices = array(
        1 => '1',
        2 => '2',
        3 => '3',
        4 => '4',
        5 => '5',
        6 => '6',
        7 => '7',
        8 => '8',
        9 => '9',
        10 => '10',
        11 => '11',
        12 => '12',
        13 => '13',
        14 => '14',
        15 => '15',
        16 => '16'
    );
    $temp->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    $numberofslides = get_config('theme_moe', 'carousel_slides');
    for ($i = 1; $i <= $numberofslides; $i++) {
		// Image.
        $name = 'theme_moe/carousel_image_'.$i;
        $title = get_string('carousel_image', 'theme_moe');
        $description = get_string('carousel_imagedesc', 'theme_moe');
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'carousel_image_'.$i);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);

        // Caption Heading.
        $name = 'theme_moe/carousel_heading_'.$i;
        $title = get_string('carousel_heading', 'theme_moe');
        $description = get_string('carousel_heading_desc', 'theme_moe');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);

        // Caption text.
        $name = 'theme_moe/carousel_caption_'.$i;
        $title = get_string('carousel_caption', 'theme_moe');
        $description = get_string('carousel_caption_desc', 'theme_moe');
        $default = '';
        $setting = new admin_setting_configtextarea($name, $title, $description, $default, PARAM_TEXT);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);

        // URL.
        $name = 'theme_moe/carousel_url_'.$i;
        $title = get_string('carousel_url', 'theme_moe');
        $description = get_string('carousel_urldesc', 'theme_moe');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);

		// Button Text.
        $name = 'theme_moe/carousel_btntext_'.$i;
        $title = get_string('carousel_btntext', 'theme_moe');
        $description = get_string('carousel_btntextdesc', 'theme_moe');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_TEXT);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);

        // Color
        $name = 'theme_moe/carousel_color_'.$i;
        $title = get_string('carousel_color', 'theme_moe');
        $description = get_string('carousel_colordesc', 'theme_moe');
		$default = '0';
    	$choices = array(
			'0' => 'green',
			'1' => 'purple',
			'2' => 'orange',
			'3' => 'lightblue',
			'4' => 'yellow',
			'5' => 'turquoise');
    	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);
    }
    $ADMIN->add('theme_moe', $temp);

	// "settings login and navigations" settingpage
	$temp = new admin_settingpage('theme_moe_login',  get_string('settings_login', 'theme_moe'));

	// Additional Login Link
    $name = 'theme_moe/login_link';
    $title = get_string('login_link', 'theme_moe');
    $description = get_string('login_link_desc', 'theme_moe');
    $default = 2;
    $choices = array(0 => get_string('none'), 1 => get_string('startsignup'), 2 => get_string('forgotten'), 3 => get_string('moodle_login_page', 'theme_moe'));
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Custom Login Link URL.
    $name = 'theme_moe/custom_login_link_url';
    $title = get_string('custom_login_link_url', 'theme_moe');
    $description = get_string('custom_login_link_url_desc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// Custom Login Link Text.
    $name = 'theme_moe/custom_login_link_txt';
    $title = get_string('custom_login_link_txt', 'theme_moe');
    $description = get_string('custom_login_link_txt_desc', 'theme_moe');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_TEXT);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// customized login page.
	$name = 'theme_moe/auth_googleoauth2';
    $title = get_string('auth_googleoauth2', 'theme_moe');
    $description = get_string('auth_googleoauth2_desc', 'theme_moe');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// customized login page.
	$name = 'theme_moe/custom_login';
    $title = get_string('custom_login', 'theme_moe');
    $description = get_string('custom_login_desc', 'theme_moe');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 1);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// hide breadcrumd for guest users
	$name = 'theme_moe/hide_breadcrumb';
    $title = get_string('hide_breadcrumb', 'theme_moe');
    $description = get_string('hide_breadcrumb_desc', 'theme_moe');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 1);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	// custom menu with shadow effect
	$name = 'theme_moe/shadow_effect';
    $title = get_string('shadow_effect', 'theme_moe');
    $description = get_string('shadow_effect_desc', 'theme_moe');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Show MyCourses dropdown in custommenu.
    $name = 'theme_moe/mycourses_dropdown';
    $title = get_string('mycourses_dropdown', 'theme_moe');
    $description = get_string('mycourses_dropdown_desc', 'theme_moe');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

	$ADMIN->add('theme_moe', $temp);