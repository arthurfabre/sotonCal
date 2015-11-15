<?php
/**
 * ICAL Generation library.
 * Modified from https://gist.github.com/pamelafox-coursera/5359246
 */

/**
 * Class representing Events in a Calendar.
 */
class CalendarEvent {
    
    /**
     * The event start date
     * @var DateTime
     */
    private $start;
    
    /**
     * The event end date
     * @var DateTime
     */
    private $end;
    
    /**
     * 
     * The event title
     * @var string
     */
    private $summary;
    
    /**
     * The event description
     * @var string
     */
    private $description;
    
    /**
     * The event location
     * @var string
     */
    private $location;

    /**
     * Create a Calendar Event
     */ 
    public function __construct($summary, $location, DateTime $start, DateTime $end, $description = "") {
        $this->start = $start;
        $this->end = $end;
        $this->summary = $summary;
        $this->description = $description;
        $this->location = $location;
    }

    /**
     * Get the UID representing this Event.
     * UIDs are persistent.
     */
    private function getUID() {
        // Hostname should ensure the IDs are unique to us
        return md5($this->description . $this->location . $this->summary . $this->formatDate($this->start) . $this->formatDate($this->end)) .'@'. gethostname();
    }

    /**
     * Get the start time set for the even
     * @return string
     */
    private function formatDate($date) {   
        return $date->format("Ymd\THis\Z");
    }

    /** 
     * Escape commas, semi-colons, backslashes.
     * http://stackoverflow.com/questions/1590368/should-a-colon-character-be-escaped-in-text-values-in-icalendar-rfc2445
     */
    private function formatValue($str) {
        return addcslashes($str, ",\\;");
    }

    /**
     * Generate an ICAL String representation of this event.
     */
    public function generateIcal() {
        $content = '';
        $content = "BEGIN:VEVENT\r\n"
                 . "UID:{$this->getUID()}\r\n"
                 . "DTSTART:{$this->formatDate($this->start)}\r\n"
                 . "DTEND:{$this->formatDate($this->end)}\r\n"
                 . "DTSTAMP:{$this->formatDate($this->start)}\r\n"
                 . "DESCRIPTION:{$this->formatValue($this->description)}\r\n"
                 . "LAST-MODIFIED:{$this->formatDate($this->start)}\r\n"
                 . "LOCATION:{$this->location}\r\n"
                 . "SUMMARY:{$this->formatValue($this->summary)}\r\n"
                 . "SEQUENCE:0\r\n"
                 . "STATUS:CONFIRMED\r\n"
                 . "TRANSP:OPAQUE\r\n"
                 . "END:VEVENT\r\n";
        return $content;
    }
}

class Calendar {

    /**
     * List of events in the calendar.
     */
    protected $events;

    /**
     * Create a new calendar
     */
    public function __construct($events = array()) {
        $this->events = $events;
    }

    /**
     * Add an event to the calendar
     */
    public function addEvent(CalendarEvent $event) {
        array_push($this->events, $event);
    }

    /**
     * Send the Calendar to the client 
     */
    public function generateDownload() {
        $generated = $this->generateIcal();
        header('Cache-Control: no-store, no-cache, must-revalidate' ); //force revaidation
        header('Cache-Control: post-check=0, pre-check=0', false );
        header('Pragma: no-cache' ); 
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: inline; filename="calendar.ics"');
        header("Content-Description: File Transfer");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . strlen($generated));
        print $generated;
    }

    /**
     * The function generates the actual content of the ICS
     * file and returns it.
     * 
     * @return string|bool
     */
    public function generateIcal() {
        $content = "BEGIN:VCALENDAR\r\n"
                 . "VERSION:2.0\r\n"
                 . "PRODID:-//University of Southampton//NONSGML ICALTimetable//EN\r\n";

        foreach($this->events as $event) {
            $content .= $event->generateIcal();
        }
	    $content .= "END:VCALENDAR";
        return $content;
	}
}
