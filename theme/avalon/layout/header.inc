<body
    id="<?php p($PAGE->bodyid) ?>"
    class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>
    <?php if (empty($PAGE->theme->settings->collegefooter)) echo 'clpsd' ?>
    <?php echo ($PAGE->theme->settings->usesmalllogo ? 'smalllogo' : '') ?>
    <?php if ($CFG->debugdisplay==1) echo 'debugdisplay' ?>
    <?php if (!$COURSE->visible) echo " notactive " ?>
    "
    >
<?php echo $OUTPUT->standard_top_of_body_html() ?>
