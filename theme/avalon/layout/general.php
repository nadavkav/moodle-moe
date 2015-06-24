<?php

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));
$haslogininfo = (empty($PAGE->layout_options['nologininfo']));

$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']));// && !empty($custommenu));

$courseheader = $coursecontentheader = $coursecontentfooter = $coursefooter = '';
if (empty($PAGE->layout_options['nocourseheaderfooter'])) {
    $courseheader = $OUTPUT->course_header();
    $coursecontentheader = $OUTPUT->course_content_header();
    if (empty($PAGE->layout_options['nocoursefooter'])) {
        $coursecontentfooter = $OUTPUT->course_content_footer();
        $coursefooter = $OUTPUT->course_footer();
    }
}

$bodyclasses = array();
if ($showsidepre && !$showsidepost) {
    if (!right_to_left()) {
        $bodyclasses[] = 'side-pre-only';
    }else{
        $bodyclasses[] = 'side-post-only';
    }
} else if ($showsidepost && !$showsidepre) {
    if (!right_to_left()) {
        $bodyclasses[] = 'side-post-only';
    }else{
        $bodyclasses[] = 'side-pre-only';
    }
} else if (!$showsidepost && !$showsidepre) {
    $bodyclasses[] = 'content-only';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}

$showbacktocourse = array('page-group-index','page-enrol-users','page-admin-roles-check','page-question-edit','page-grade-edit-tree-index','page-enrol-instances');

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>

<?php include("header.inc") ?>

<div id="page">
    <div id="page-top">
        <?php if ($hasheading || $hasnavbar) { ?>
        <div id="page-header">
            <?php include("services.inc") ?>
            <?php include("collegelogo.inc") ?>
            <?php if ($hasheading) { ?>
            <h1 class="headermain">
                <span title="<?php echo $PAGE->heading ?>">
                <?php echo $PAGE->heading ?>
                </span>
            </h1>

            <div class="headermenu">
                <?php if ($USER->id > 1) echo $OUTPUT->user_picture($USER, array('popup'=>'off')) ?>
                <?php
                if ($haslogininfo) { echo $OUTPUT->login_info(); }
                ?>
            </div>
            <?php } ?>
            <div class="teachername"><?php if ($COURSE->id > 1) $OUTPUT->render_course_teachers() ?></div>
        <?php if ($hascustommenu) { ?>
            <div id="custommenu">
                <?php echo $custommenu; ?>
                <div class="navbutton"> <?php echo $PAGE->button; ?></div>
            </div>
        <?php } ?>
        <?php if ($hasnavbar) { ?>
            <div class="navbar clearfix">
                <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
                <div class="info"><?php echo page_doc_link(get_string('moodledocslink')) ?></div>
            </div>
        <?php } ?>
        </div>
        <?php } ?>

        <div id="page-middle">
            <div id="page-content">
                <?php
                if(trim($PAGE->theme->settings->collegenotice) !== '')
                {
                    echo '<div class="collegenotice"><div class="heading">'.get_string('importantnotice','theme_avalon').'</div><div class="content">';
                    echo clean_text(trim($PAGE->theme->settings->collegenotice));
                    echo '</div></div>';
                }
                ?>
                <div id="region-main-box">
                    <div id="region-post-box">

                        <div id="region-main-wrap">
                            <div id="region-main">
                                <div class="region-content">
                                    <?php
                                            if (!$COURSE->visible) echo "<div id='coursevisibility'>".get_string('notactive','theme_avalon')."</div>";
                                    ?>
                                    <?php if ($hasheading and false)  {// disable duplicate heading ?>
                                        <div title="<?php echo $PAGE->heading ?>" class="heading"><?php echo $PAGE->heading ?></div>
                                    <?php } ?>
                                    <?php echo $coursecontentheader; ?>
                                    <?php echo $OUTPUT->main_content() ?>
                                    <?php echo $coursecontentfooter; ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($hassidepre OR (right_to_left() AND $hassidepost)) { ?>
                        <div id="region-pre" class="block-region">
                            <div class="region-content">
                                <?php
                                if (!right_to_left()) {
                                    if ( $PAGE->context->get_level_name() == get_string('activitymodule') ) {
                                        echo "<a id='backtocourse_top' href='$CFG->wwwroot/course/view.php?id=$COURSE->id'>".get_string('backtocourse','theme_avalon')."</a><div style='clear:both;height:10px'></div>";
                                    }
                                    echo $OUTPUT->blocks_for_region('side-pre');
                                } elseif ($hassidepost) {
                                    echo $OUTPUT->blocks_for_region('side-post');
                                } ?>

                            </div>
                        </div>
                        <?php } ?>

                        <?php if ($hassidepost OR (right_to_left() AND $hassidepre)) { ?>
                        <div id="region-post" class="block-region">
                            <div class="region-content">
                                <?php
                                if (!right_to_left()) {
                                    echo $OUTPUT->blocks_for_region('side-post');
                                } elseif ($hassidepre) {
                                    if ( $PAGE->context->get_level_name() == get_string('activitymodule') || in_array($PAGE->bodyid,$showbacktocourse) ) {
                                        echo "<a id='backtocourse_top' href='$CFG->wwwroot/course/view.php?id=$COURSE->id'>".get_string('backtocourse','theme_avalon')."</a><div style='clear:both;height:10px'></div>";
                                    }
                                    echo $OUTPUT->blocks_for_region('side-pre');

                                } ?>
                            </div>
                        </div>
                        <?php } ?>

                    </div>
                </div>
            </div>

            <?php if ($hasnavbar) { ?>
            <div class="navbar clearfix">
                <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
                <?php
                if ( $PAGE->context->get_level_name() == get_string('activitymodule') || in_array($PAGE->bodyid,$showbacktocourse) ) {
                    echo " <a id='backtocourse_bot' href='$CFG->wwwroot/course/view.php?id=$COURSE->id'>".get_string('backtocourse','theme_avalon')."</a>";
                } ?>
            </div>
            <?php } ?>



        </div>

    </div>

    <?php if ($hasfooter) { ?>
        <?php include("footer.inc") ?>
    <?php } ?>

</div>
<?php echo $OUTPUT->standard_footer_html(); // display performance and developer debugging info, if enabled ?>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>