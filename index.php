<?php
/**
 * Script to export Southampton University timetable information
 * as ICAL.
 *
 * Relies on the CampusM mySouthampton APP API.
 */
require_once('campusm.php');
require_once('calendar.php');
require_once('conf.php');

// Ensure the user authenticates
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Authorization required';
    exit;
}

$end = new DateTime();
$end->add($INTERVAL);

// Get the timetable
$timetable = getTimeTable($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], new DateTime(), $end);

// Send the ICAL representation to the client
$timetable->generateDownload();

