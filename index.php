<?php
require_once 'maui_jim.php';

// Main program
$spider = new MauiJimmSpider();
$spider->startRequests();
$spider->saveDataToFile();
?>

