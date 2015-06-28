<?php

//require("../../../config.php");
//$showtab = optional_param('showtab', 0, PARAM_INT); // tab index, to show
$showtab = $_GET['showtab'];
echo "var opts = {initialTab:$showtab};";
