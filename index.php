<?php

// Display all errors if the `error_reporting` parameter is set
if (!empty($_REQUEST['error_reporting'])) {
    ini_set("display_errors", 1);
    ini_set("track_errors", 1);
    ini_set("html_errors", 1);
    error_reporting(E_ALL);
}

require 'app/bootstrap.php';

// Run it
$app->run();
