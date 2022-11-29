<?php
require "../../vendor/autoload.php";

use ICal\ICal;

function convertCalendar($url){
    try {
        $ical = new ICal('ICal.ics', array(
            'defaultSpan'                 => 2,     // Default value
            'defaultTimeZone'             => 'UTC',
            'defaultWeekStart'            => 'MO',  // Default value
            'disableCharacterReplacement' => false, // Default value
            'filterDaysAfter'             => null,  // Default value
            'filterDaysBefore'            => null,  // Default value
            'httpUserAgent'               => null,  // Default value
            'skipRecurrence'              => false, // Default value
        ));
        $ical->initUrl($url, $username = null, $password = null, $userAgent = null);

        header("Content-type:text/calendar");
        header("Content-Disposition:attachment;filename=edt_insa.ics");

        echo "BEGIN:VCALENDAR\r\n";
        echo "METHOD:REQUEST\r\n";
        echo "PRODID:-//themsVPS/version 1.0\r\n";
        echo "VERSION:2.0\r\n";
        echo "CALSCALE:GREGORIAN\r\n";

        foreach($ical->events() as $i => $event){
            editEventAndPrint($event);
        }

        echo "END:VCALENDAR\r\n";

    } catch (\Exception $e) {
        die($e);
    }
}

function editEventAndPrint($event){
    $line1 = explode("\n()", $event->description)[0];
    $subject = explode("] ", $line1)[1]; // Full name

    $explodedSummary = explode(":", $event->summary);

    $tag = count($explodedSummary) >= 1 ? $explodedSummary[0] : ""; // PC-S1-PH-AMP
    $type = count($explodedSummary) >= 2 ? $explodedSummary[1] : null; // CM, TD, TP

    if(str_starts_with($tag, "ANG-1FC-")){ // Anglais
        $tag = "PC-S1-ANG";
    }
    if(str_starts_with($tag, "EPS-1-MA14-")){ // Sport
        $tag = "PC-S1-EPS";
        $type = null;
    }

    $subjectTag = str_replace("PC-S2-", "", str_replace("PC-S1-", "", $tag)); // PH-AMP, MA-AP, SOL-TF

    $location = $event->location == null ? null :
        str_replace("Amphithéâtre", "Amphi", explode(" - ", $event->location)[1]); // Room letter & number only

    if($tag === "PC-S1-SOU-EDT" // Soutien
        || $tag === "PC-S13-LV-EDT" // Langues *2
        || $tag === "PC-S13-EPS-EDT"){ // Sport *2
        return;
    }

    $event->summary = ($type == null ? "" : $type . " ") . $subjectTag . ($location == null ? "" : " - " . $location);
    $event->description = "\n" . $event->description;
    $event->location = $location;

    printEvent($event);
}

function printEvent($event){
    echo "BEGIN:VEVENT\r\n";
    echo getEventDataString($event);
    echo "END:VEVENT\r\n";
}

function getEventDataString($event){
    $data = array(
        'SUMMARY'       => $event->summary,
        'DTSTART'       => $event->dtstart,
        'DTEND'         => $event->dtend,
        'DTSTART_TZ'    => $event->dtstart_tz,
        'DTEND_TZ'      => $event->dtend_tz,
        'DURATION'      => $event->duration,
        'DTSTAMP'       => $event->dtstamp,
        'UID'           => $event->uid,
        'CREATED'       => $event->created,
        'LAST-MODIFIED' => $event->last_modified,
        'DESCRIPTION'   => $event->description,
        'LOCATION'      => $event->location,
        'SEQUENCE'      => $event->sequence,
        'STATUS'        => $event->status,
        'TRANSP'        => $event->transp,
        'ORGANISER'     => $event->organizer,
        'ATTENDEE(S)'   => $event->attendee,
    );

    // Remove any blank values
    $data = array_filter($data);
    $output = '';

    foreach($data as $key => $value) {
        $output .= sprintf("%s:%s\r\n", $key, str_replace("\n", "\\n", $value));
    }

    return $output;
}


if(isset($_GET['url'])) {
    convertCalendar(urldecode($_GET['url']));
}else{
    header("Location: ./");
}
