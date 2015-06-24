<?php if(!empty($PAGE->theme->settings->logocollege_url)) { ?>
<a href="<?php echo $PAGE->theme->settings->logocollege_url; ?>" target="<?php echo ($PAGE->theme->settings->logocollege_urltarget=='samewindow')?'_self':'_blank'; ?>" >
    <?php } ?>
    <div class="collegelogo"></div>
    <?php if(!empty($PAGE->theme->settings->logocollege_url)) { ?>
</a>
<?php } ?>
