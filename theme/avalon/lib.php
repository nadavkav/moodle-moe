<?php

/**
 * Makes our changes to the CSS
 *
 * @param string $css
 * @param theme_config $theme
 * @return string
 */
function avalon_process_css($css, $theme) {

    ////////// Blocks background colors

    // Set the communication blocks background color
    if (!empty($theme->settings->blockbgcolor_communication)) {
        $bgcolor = $theme->settings->blockbgcolor_communication;
    } else {
        $bgcolor = null;
    }
    $css = avalon_set_blockbgcolor_communication($css, $bgcolor);

    // Set the navigation blocks background color
    if (!empty($theme->settings->blockbgcolor_navigation)) {
        $bgcolor = $theme->settings->blockbgcolor_navigation;
    } else {
        $bgcolor = null;
    }
    $css = avalon_set_blockbgcolor_navigation($css, $bgcolor);

    // Set the information blocks background color
    if (!empty($theme->settings->blockbgcolor_information)) {
        $bgcolor = $theme->settings->blockbgcolor_information;
    } else {
        $bgcolor = null;
    }
    $css = avalon_set_blockbgcolor_information($css, $bgcolor);

    // Set the personal blocks background color
    if (!empty($theme->settings->blockbgcolor_personal)) {
        $bgcolor = $theme->settings->blockbgcolor_personal;
    } else {
        $bgcolor = null;
    }
    $css = avalon_set_blockbgcolor_personal($css, $bgcolor);


    ////////// Color presets

    // colors
    if (!empty($theme->settings->subtheme)){
        $subtheme = $theme->settings->subtheme;
    }
    else {
        $subtheme = 'sky';
    }

    $default_colors = $theme->settings->subthemedefaults[$subtheme]['colors'];
    foreach($default_colors  as $color_number => $default_setting)  {

        $colorsetting = 'color'.$color_number;

        if (!empty($theme->settings->$colorsetting)) {
            $color = $theme->settings->$colorsetting;
        } else {
            $color = null;
        }

        $css = avalon_set_color($css, $color_number,  $color , $default_setting );
    }


    ////////// Images (LOGO)

    // Set the logo image (ltr)
    if (!empty($theme->settings->logocollege)) {
        $logo = $theme->settings->logocollege;
    } else {
        $logo = null;
    }
    $css = avalon_set_logo($css, $logo);

    // Set the logo image (rtl)
    if (!empty($theme->settings->logocollegertl)) {
        $logo = $theme->settings->logocollegertl;
    } else {
        $logo = null;
    }
    $css = avalon_set_logortl($css, $logo);

    // Set the logo college footer image (ltr)
    if (!empty($theme->settings->logocollegefooter)) {
        $logocollegefooter = $theme->settings->logocollegefooter;
    } else {
        $logocollegefooter = null;
    }
    $css = avalon_set_logocollegefooter($css, $logocollegefooter);

    // Set the logo college footer image (rtl)
    if (!empty($theme->settings->logocollegefooterrtl)) {
        $logocollegefooter = $theme->settings->logocollegefooterrtl;
    } else {
        $logocollegefooter = null;
    }
    $css = avalon_set_logocollegefooterrtl($css, $logocollegefooter);

    // Set the background banner image (ltr)
    if (!empty($theme->settings->topbanner)) {
        $banner = $theme->settings->topbanner;
    } else {
        $banner = null;
    }
    $css = avalon_set_topbanner($css, $banner);

    // Set the background banner image (rtl)
    if (!empty($theme->settings->topbannerrtl)) {
        $banner = $theme->settings->topbannerrtl;
    } else {
        $banner = null;
    }
    $css = avalon_set_topbannerrtl($css, $banner);

    // Set the background banner image (ltr)
    if (!empty($theme->settings->topbannerfar)) {
        $banner = $theme->settings->topbannerfar;
    } else {
        $banner = null;
    }
    $css = avalon_set_topbannerfar($css, $banner);

    // Set the background banner image (rtl)
    if (!empty($theme->settings->topbannerfarrtl)) {
        $banner = $theme->settings->topbannerfarrtl;
    } else {
        $banner = null;
    }
    $css = avalon_set_topbannerfarrtl($css, $banner);

    // Set the one pixel background banner image
    if (!empty($theme->settings->topbanneronepixel)) {
        $banner = $theme->settings->topbanneronepixel;
    } else {
        $banner = null;
    }
    $css = avalon_set_topbanneronepixel($css, $banner);

    // Set Header Style
    if (!empty($theme->settings->headerstyle)) {
        $headerstyle = $theme->settings->headerstyle;
    } else {
        $headerstyle = null;
    }
    $css = avalon_set_headerstyle($css, $headerstyle);

    // Add support for special Block Header fonts, if some are selected
    if (!empty($theme->settings->blockheaderfont)) {
        $specialfont = $theme->settings->blockheaderfont;
    } else {
        $specialfont = null;
    }
    $css = avalon_set_blockheaderfont($css, $specialfont, $theme);

    // Add support for special Content fonts, if some are selected
    if (!empty($theme->settings->contentfont)) {
        $specialfont = $theme->settings->contentfont;
    } else {
        $specialfont = null;
    }
    $css = avalon_set_contentfont($css, $specialfont, $theme);

    //Process subtheme pix locations
    $css = avalon_pix_process($css, $subtheme);

    // Return the CSS
    return $css;
}



/**
 * Sets the link color variable in CSS
 *
 */
function avalon_set_blockbgcolor_communication($css, $bgcolor) {
    $tag = '[[setting:blockbgcolor_communication]]';
    $replacement = $bgcolor;
    if (is_null($replacement)) {
        $replacement = '#32529a';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_blockbgcolor_navigation($css, $bgcolor) {
    $tag = '[[setting:blockbgcolor_navigation]]';
    $replacement = $bgcolor;
    if (is_null($replacement)) {
        $replacement = '#32529a';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_blockbgcolor_information($css, $bgcolor) {
    $tag = '[[setting:blockbgcolor_information]]';
    $replacement = $bgcolor;
    if (is_null($replacement)) {
        $replacement = '#32529a';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_blockbgcolor_personal($css, $bgcolor) {
    $tag = '[[setting:blockbgcolor_personal]]';
    $replacement = $bgcolor;
    if (is_null($replacement)) {
        $replacement = '#32529a';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}


////////// Color presets


function avalon_set_color($css, $color_number, $color_setting, $default_color) {

    $tag = '[[setting:color'.$color_number.']]';
    $replacement = $color_setting;
    if (is_null($replacement)) {
        $replacement = $default_color;;
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}


////////// Images (LOGO)

function avalon_set_logo($css, $logo) {
	$tag = '[[setting:logocollege]]';
	$replacement = "#page-header .collegelogo { background-image:url($logo); } body.smalllogo #page-header .collegelogo { background-image:url($logo); } ";
	if (is_null($logo)) {
 		$replacement = '';
 	}
	$css = str_replace($tag, $replacement, $css);
	return $css;
}

function avalon_set_logortl($css, $logo) {
    $tag = '[[setting:logocollegertl]]';
    $replacement = ".dir-rtl #page-header .collegelogo { background-image:url($logo)} body.smalllogo.dir-rtl #page-header .collegelogo { background-image:url($logo); } ";
    if (is_null($logo)) {
        $replacement = '';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_logocollegefooter($css, $logocollegefooter) {
    $tag = '[[setting:logocollegefooter]]';
    $replacement = "#page-footer .bottomfar a.college { background:url($logocollegefooter) no-repeat; } ";
    if (is_null($logocollegefooter)) {
        $replacement = ' ';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_logocollegefooterrtl($css, $logocollegefooter) {
    $tag = '[[setting:logocollegefooterrtl]]';
    $replacement = ".dir-rtl #page-footer .bottomfar a.college { background:url($logocollegefooter) no-repeat; } ";
    if (is_null($logocollegefooter)) {
        $replacement = ' ';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_topbanner($css, $banner) {
    $tag = '[[setting:topbanner]]';
    $replacement = "#page-top { background-image:url($banner); } body.smalllogo #page-top { background-image:url($banner); } ";
    if (is_null($banner)) {
        $replacement = ' ';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_topbannerrtl($css, $banner) {
    $tag = '[[setting:topbannerrtl]]';
    $replacement = ".dir-rtl #page-top { background-image:url($banner); } body.smalllogo.dir-rtl #page-top { background-image:url($banner); } ";
    if (is_null($banner)) {
        $replacement = ' ';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_topbannerfar($css, $banner) {
    $tag = '[[setting:topbannerfar]]';
    $replacement = "#page-header { background:url($banner) no-repeat 100% 0; } ";
    if (is_null($banner)) {
        $replacement = ' ';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_topbannerfarrtl($css, $banner) {
    $tag = '[[setting:topbannerfarrtl]]';
    $replacement = ".dir-rtl #page-header { background:url($banner) no-repeat 0 0; } ";
    if (is_null($banner)) {
        $replacement = ' ';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_topbanneronepixel($css, $banner) {
    $tag = '[[setting:topbanneronepixel]]';
    $replacement = "#page { background:url($banner) repeat-x; } ";
    if (is_null($banner)) {
        $replacement = ' ';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_headerstyle($css, $headerstyle) {
    $tag = '[[setting:headerstyle]]';
    $replacement = "$headerstyle";
    if (is_null($headerstyle)) {
        $replacement = '';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_blockheaderfont($css, $specialfont, $theme) {

    $tag = '[[setting:import_blockheaderfont]]';

    $replacement = $theme->settings->fontlist[$specialfont]['url'];

    $css = str_replace($tag, $replacement, $css);

    $tag = '[[setting:blockheaderfont]]';
    $replacement = $theme->settings->fontlist[$specialfont]['name'];

    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_contentfont($css, $specialfont, $theme) {

    $tag = '[[setting:import_contentfont]]';

    $replacement = $theme->settings->fontlist[$specialfont]['url'];
    $css = str_replace($tag, $replacement, $css);

    $tag = '[[setting:contentfont]]';
    $replacement = $theme->settings->fontlist[$specialfont]['name'];

    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_pix_process($css, $subtheme) {
    global $OUTPUT;
    // now resolve all image locations
    if (preg_match_all('/\[\[avalon_pix:([a-z_]+\|)?([^\]]+)\]\]/', $css, $matches, PREG_SET_ORDER)) {
        $replaced = array();
        foreach ($matches as $match) {
            if (isset($replaced[$match[0]])) {
                continue;
            }
            $replaced[$match[0]] = true;
            $imagename = $match[2];
            $component = rtrim($match[1], '|');
            $imageurl = $OUTPUT->avalon_pix_url($imagename, $component, $subtheme)->out(false);
            // we do not need full url because the image.php is always in the same dir
            $imageurl = preg_replace('|^http.?://[^/]+|', '', $imageurl);
            $css = str_replace($match[0], $imageurl, $css);
        }
    }

    return $css;
}

/**
 * This function creates javascript code to reset to default subtheme colors
 * Used in admin settings (settings.php)
 */
function buildResetDefaultColorsJS($subthemedefaults){

    //Prepare color variable (Holds all subtheme's default colors
    $colors_var = 'var colors = { };';

    foreach ($subthemedefaults as $subtheme => $defaults){
        $colors_var  .=  'colors.'.$subtheme.'= { };';
        foreach ($defaults['colors'] as $color_number => $color_value){
            $colors_var .= 'colors.'.$subtheme.'['.$color_number.']='.'\''.$color_value.'\';';
        }
    }

    $resetDefaultColorsText = get_string('resetdefaultcolors', 'theme_avalon');


    //Return JS string with function to reset to subtheme's default colors
    //Make sure no whitespace exists! Otherwise Moodle functions strip out the html tags
    $js_str = <<<RESET_COLORS
<script type="text/javascript">
        $colors_var
        function resetDefaultColors(){
            var subtheme = document.getElementById('id_s_theme_avalon_subtheme').value;

            for (var i in colors[subtheme]){
                document.getElementById('id_s_theme_avalon_color' + i).value=colors[subtheme][i];
            }
        }
        var el = document.getElementById('id_s_theme_avalon_subtheme');
        if (el.addEventListener) {
            el.addEventListener("change", function(){
                resetDefaultColors(document.getElementById('adminsettings'));
            }, false);
        } else {
            el.attachEvent('onchange', function(){
                resetDefaultColors(document.getElementById('adminsettings'));
            });
        }
</script>
RESET_COLORS;

    return $js_str;
}


