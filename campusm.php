<?php
/**
 * Implementation of the CampusM timetable API
 */
require_once('calendar.php');
require_once('conf.php');

/**
 * The base url of the CampusM API
 */
$CAMPUSM_BASE_URL = "https://campusm.soton.ac.uk";

/**
 * The relative url of the CampusM timetable service
 */
$CAMPUSM_TIMETABLE_URL = "/campusm/services/CampusMUniversityService/retrieveCalendar";

/**
 * Date format used by CampusM.
 * Seems to be ISO 8601
 */
$CAMPUSM_DATE_FORMAT = "c";

/**
 * URI for the CAMPUSM ns1 namespace
 */
$CAMPUSM_XML = 'http://campusm.gw.com/campusm';

/**
 * Headers set for a request.
 * These are the exact headers used by the Android App
 */
$CAMPUSM_HEADERS = array(
    'Content-Type: application/xml',
    'Cache-Control: no-cache',
    'Pragma: no-cache',
    'Connection: close',
    'Accept-Encoding: gzip',
    'User-Agent: Apache-HttpClient/UNAVAILABLE (java 1.4)'
);

/**
 * Fetch the timetable, parse it, and return a Calendar object.
 */
function getTimeTable($username, $password, DateTime $start, DateTime $end) {
    $raw = fetchTimeTable($username, $password, $start, $end);

    return XMLTimeTableToICAL($raw);
}

/**
 * Convert an XML timetable to ICAL
 */
function XMLTimeTableToICAL($raw) {
    global $CAMPUSM_XML;

    $cal = new Calendar();

    // This is a bit of a mess
    $parsed = new SimpleXMLElement($raw);
    // Extract the children of the children with the correct namespace
    $entries = $parsed->children($CAMPUSM_XML)->children($CAMPUSM_XML);

    foreach ($entries->calitem as $calitem) {
        $cal->addEvent(new CalendarEvent($calitem->desc1, $calitem->locCode, new DateTime($calitem->start), new DateTime($calitem->end), $calitem->desc2));
    }

    return $cal;
}

/**
 * Fetch the timeable as RAW XML.
 */
function fetchTimeTable($username, $password, DateTime $start, DateTime $end) {
    global $CAMPUSM_USER, $CAMPUSM_PWD, $CAMPUSM_BASE_URL, $CAMPUSM_TIMETABLE_URL, $CAMPUSM_DATE_FORMAT, $CAMPUSM_HEADERS;

    $request = '<?xml version="1.0" encoding="UTF-8"?>' . chr(0x0A) .
               '<retrieveCalendar xmlns="http://campusm.gw.com/campusm">' .
                   "<username>{$username}</username>" .
                   "<password>{$password}</password>" .
                   '<calType>course_timetable</calType>' .
                   "<start>{$start->format($CAMPUSM_DATE_FORMAT)}</start>" .
                   "<end>{$end->format($CAMPUSM_DATE_FORMAT)}</end>" .
                   '</retrieveCalendar>';

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $CAMPUSM_BASE_URL . $CAMPUSM_TIMETABLE_URL,
        CURLOPT_HTTPHEADER => $CAMPUSM_HEADERS,
        CURLOPT_RETURNTRANSFER => True,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $request,
        CURLOPT_USERPWD => $CAMPUSM_USER . ":" . $CAMPUSM_PWD,

        // UnComment to enable proxy
        /*CURLOPT_PROXY => "127.0.0.1",
        CURLOPT_PROXYPORT => "8443",
        CURLOPT_SSL_VERIFYPEER => False*/
    ));

    $result = curl_exec($curl);

    return $result;
}
