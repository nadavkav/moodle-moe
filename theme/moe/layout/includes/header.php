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

$login_link = theme_lambda_get_setting('login_link');
$login_custom_url = theme_lambda_get_setting('custom_login_link_url');
$login_custom_txt = theme_lambda_get_setting('custom_login_link_txt');
$shadow_effect = theme_lambda_get_setting('shadow_effect');
$auth_googleoauth2 = theme_lambda_get_setting('auth_googleoauth2');
$countdowntimer = theme_moe_get_setting('countdowntimer');

$haslogo = (!empty($PAGE->theme->settings->logo));
$hasheaderprofilepic = (empty($PAGE->theme->settings->headerprofilepic)) ? false : $PAGE->theme->settings->headerprofilepic;
$context=getdate();
if ($countdowntimer && isloggedin()) {
    $this->page->requires->js_call_amd('theme_moe/addclock', 'init', array($context['hours'],$context['minutes'],$context['seconds']));
}


$checkuseragent = '';
if (!empty($_SERVER['HTTP_USER_AGENT'])) {
    $checkuseragent = $_SERVER['HTTP_USER_AGENT'];
}
$username = get_string('username');
if (strpos($checkuseragent, 'MSIE 8')) {$username = str_replace("'", "&prime;", $username);}
?>

<?php if($PAGE->theme->settings->socials_position==1) { ?>
    	<div class="container-fluid socials-header">
    	<?php require_once(dirname(__FILE__).'/socials.php');?>
        </div>
<?php
} ?>
	<?php if($countdowntimer && isloggedin()) {?>
   		<div id="countdown" class="btn"></div>
   	<?php }?>
   <header id="page-header" class="clearfix">

    <div class="container-fluid">
    <div class="row-fluid">
    <!-- HEADER: LOGO AREA -->
        		<div id="lemidaDigit" class="hidden-phone span5">
                	<span>
                	<?php
					   echo get_string('lemidadigit','theme_moe');
					?>
					</span>
                </div>
                <div class="span4 hidden-phone">
              		<h1 id="title" style="line-height: 2em"><?php echo $SITE->fullname; ?></h1>
                </div>

            <?php if (!$haslogo) { ?>
            	<div class="span6">
              		<h1 id="title" style="line-height: 2em"><?php echo $SITE->fullname; ?></h1>
                </div>
            <?php } else { ?>
                <div class="span3 logo-header">
                	<a class="logo" href="<?php echo $CFG->wwwroot; ?>" title="<?php print_string('home'); ?>">
                    <?php
					echo html_writer::empty_tag('img', array('src'=>$PAGE->theme->setting_file_url('logo', 'logo'), 'class'=>'logo img-responsive', 'alt'=>'logo'));
					?>
                    </a>
                </div>
            <?php } ?>





            <?php
	function get_content () {
	global $USER, $CFG, $SESSION, $COURSE;
	$wwwroot = '';
	$signup = '';}

	if (empty($CFG->loginhttps)) {
		$wwwroot = $CFG->wwwroot;
	} else {
		$wwwroot = str_replace("http://", "https://", $CFG->wwwroot);
	}?>
    </div>
    </div>
</header>

<header role="banner" class="navbar">
    <nav role="navigation" class="navbar-inner">
        <div class="container-fluid">
            <a class="brand" href="<?php echo $CFG->wwwroot;?>"><?php echo $SITE->shortname; ?></a>
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <div class="nav-collapse collapse">
                <?php echo $OUTPUT->custom_menu(); ?>
                <ul class="nav pull-right">
                    <li><?php echo $OUTPUT->page_heading_menu(); ?></li>
                    <li><?php echo $OUTPUT->user_menu()?></li>
                </ul>

                <form id="search" action="<?php echo $CFG->wwwroot;?>/course/search.php" method="GET">
                <div class="nav-divider-left"></div>
					<input id="coursesearchbox" type="text" onFocus="if(this.value =='<?php echo get_string('searchcourses'); ?>' ) this.value=''" onBlur="if(this.value=='') this.value='<?php echo get_string('searchcourses'); ?>'" value="<?php echo get_string('searchcourses'); ?>" name="search">
					<input type="submit" value="">
				</form>

            </div>
        </div>
    </nav>
</header>

<?php if ($shadow_effect) { ?>
<div class="container-fluid"><img src="<?php echo $OUTPUT->pix_url('bg/lambda-shadow', 'theme'); ?>" class="lambda-shadow" alt=""></div>
<?php } ?>


