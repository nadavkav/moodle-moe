<div id="page-footer"<?php if (empty($PAGE->theme->settings->collegefooter)) echo ' class="clpsd"' ?>>
    <div class="bottomnear">
        <?php if ($USER->id > 1) echo $OUTPUT->user_picture($USER, array('popup'=>'off')) ?>
        <?php echo $OUTPUT->login_info(); ?>
        <div class="collegename"><?php echo $PAGE->theme->settings->collegeabout ?></div>
        <div class="collegelinks">
            <?php echo $OUTPUT->footer_custom_menu();?>
        </div>
        <a class="moodle" href="http://moodle.org/" target="_blank"></a>
        <a class="mofet" href="http://www.mofet.macam.ac.il/" target="_blank"></a>
    </div>
    <div class="bottomfar">
        <a class="college" <?php if (!empty($PAGE->theme->settings->service_home)) echo "href=\"{$PAGE->theme->settings->service_home}\""; ?> target="_blank"></a>
        <!--
         Removing the following MOFET Institute logo from the bottom of will violate the CC-BY-NC 3.0 license
        under which this theme ("Avalon") is distributed.
        -->
        <a class="mofet" href="http://www.mofet.macam.ac.il/" target="_blank"></a>
        <!--
         It is also encouraged to leave the Moodle logo with the link to Moodle.org website
         To make sure users that are using this Application or Service can have clear access
         To its Open Source nature, privileges and benefits.
        -->
        <a class="moodle" href="http://moodle.org/" target="_blank"></a>
    </div>
</div>
