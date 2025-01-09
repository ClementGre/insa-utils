<?php
require "../../vendor/autoload.php";

use ICal\ICal;
use Symfony\Component\Yaml\Yaml;


function removeFirstNLines(string $text, int $n): string
{
    $lines = explode(PHP_EOL, $text);
    $remainingLines = array_slice($lines, $n);
    return implode(PHP_EOL, $remainingLines);
}
function convertCalendar($url, $mode, $cleanDescription, $locationInSummary, $countInSummary, $types, $ctypes): void
{
    try {
        $ical = new ICal("ICal.ics", [
            "defaultSpan" => 2, // Default value
            "defaultTimeZone" => "UTC",
            "defaultWeekStart" => "MO", // Default value
            "disableCharacterReplacement" => false, // Default value
            "filterDaysAfter" => null, // Default value
            "filterDaysBefore" => null, // Default value
            "httpUserAgent" => null, // Default value
            "skipRecurrence" => false, // Default value
        ]);
        $ical->initUrl(
            $url,
            $username = null,
            $password = null,
            $userAgent = null
        );

        header("Content-type:text/text");
        header("Content-Disposition:attachment;filename=edt_insa.ics");

        echo "BEGIN:VCALENDAR\r\n";
        echo "METHOD:REQUEST\r\n";
        echo "PRODID:-//themsVPS/version 1.0\r\n";
        echo "VERSION:2.0\r\n";
        echo "CALSCALE:GREGORIAN\r\n";

        $config = Yaml::parseFile('cal-config.yml');
        foreach ($ical->events() as $i => $event) {
            editEventAndPrint($event, $mode, $cleanDescription, $locationInSummary, $countInSummary, $types, $ctypes, $config);
        }

        echo "END:VCALENDAR\r\n";
    } catch (\Exception $e) {
        die($e);
    }
}

function format_name_from_regex_result($obj, $name_name, $details): string
{
    $name = $obj[$name_name . '_name'];
    $name = str_replace('%DETAILS%', $details, $name);

    // Replace %MATCHED_***_***% with the corresponding matched regex group: from $obj['regex_***_matched'][***]
    $name = preg_replace_callback('/%MATCHED_([^%]+)_([0-9])%/', function ($matches) use ($obj) {
        $key = 'regex_' . strtolower($matches[1]) . '_matched';
        if (!isset($obj[$key])) {
            return '';
        }
        $index = min(count($obj[$key]) - 1, intval($matches[2]));
        return $obj[$key][$index];
    }, $name);
    // Replace %MATCHED_***% with the corresponding matched regex group: from $obj['regex_***_matched'][0]
    return preg_replace_callback('/%MATCHED_([^%]+)%/', function ($matches) use ($obj) {
        $key = 'regex_' . strtolower($matches[1]) . '_matched';
        if (!isset($obj[$key]) && count($obj[$key]) >= 1) {
            return '';
        }
        return $obj[$key][0];
    }, $name);
}

/**
 * Tries to match a regex configured in $object and named $regex_name with the text $text
 * If the regex is found, the matched text is stored in $object['regex_' . $regex_name . '_matched']
 * @param $object mixed object to work on
 * @param $regex_name string of the regex to match
 * @param $text string to match the regex with
 * @return bool true if the regex has matched or is invalid, false otherwise
 */
function match_regex_and_update(&$object, $regex_name, $text): bool
{
    if (!isset($object[$regex_name . '_regex'])) {
        $object['regex_' . $regex_name . '_matched'] = $text;
        return true;
    }
    if (preg_match('/' . $object[$regex_name . '_regex'] . '/', $text, $matched)) {
        $object['regex_' . $regex_name . '_matched'] = $matched;
        return true;
    }
    return false;
}

// Function to match a class group from the YAML config
function match_class($config, $group_info, $class_info, $subject, $details)
{
    foreach ($config['class_groups'] as $group) {
        if (!match_regex_and_update($group, 'group', $group_info)) {
            continue;
        }
        foreach ($group['classes'] as $class) {
            if (!match_regex_and_update($class, 'class', $class_info)) {
                continue;
            }
            if (!match_regex_and_update($sub, 'subject', $subject)) {
                continue;
            }
            if (!match_regex_and_update($class, 'details', $details)) {
                continue;
            }
            $class['regex_group_matched'] = $group['regex_group_matched'];
            return $class;
        }
    }
    return null;
}

// Function to match location from YAML config
function match_location($config, $location, $subject, $details)
{
    foreach ($config['locations'] as $loc) {
        if (!match_regex_and_update($loc, 'location', $location)) {
            continue;
        }
        if (!match_regex_and_update($loc, 'subject', $subject)) {
            continue;
        }
        if (!match_regex_and_update($loc, 'details', $details)) {
            continue;
        }
        return $loc;
    }
    return null;
}

// Function to match type from YAML config
function match_type($config, $type, $subject, $details)
{
    foreach ($config['types'] as $t) {
        if (!match_regex_and_update($t, 'type', $type)) {
            continue;
        }
        if (!match_regex_and_update($t, 'subject', $subject)) {
            continue;
        }
        if (!match_regex_and_update($t, 'details', $details)) {
            continue;
        }
        return $t;
    }
    return null;
}


/**
 * @param $class mixed YAML class object
 * @param $types array of types to accept
 * @return bool true if the class has one of the types in types, or if class has no type and * is in types, false otherwise
 */
function is_class_valid($class, $types): bool
{
    if (!isset($class['type'])) {
        return in_array('*', $types);
    }
    return in_array($class['type'], $types);
}
/**
 * @param $type string class type of the event to check
 * @param $types array of class types code to accept (TD, TP, soutien, or * to include all types)
 * @return bool true if the type is in types or if * is in types, false otherwise. If type is null, always return true.
 */
function is_type_valid($type, $types): bool
{
    if (!$type) {
        return true;
    }
    return in_array($type, $types) || in_array("*", $types);
}

/**
 * @param $event
 * @param $mode int 0 = full, 1 = short, 2 = default
 * @param $locationInSummary bool show the location in the summary after the subject
 * @param $countInSummary bool show the class number in the summary right after the type
 * @param $types mixed list of event type selectors to include: language, support, other... "*" to include events that has no type.
 * @param $ctypes mixed list of class type selectors to include: group, td, cm, tp, soutien "*" to include all types
 * @param $config
 * @return void
 */
function editEventAndPrint($event, $mode, $cleanDescription, $locationInSummary, $countInSummary, $types, $ctypes, $config): void
{
    $subject = ''; // Full name, between ] and \n(
    if (preg_match('/]\s(.*?)\n\(/', $event->description, $matches)) {
        $subject = $matches[1];
    }

    $classDetails = ''; // between \n( and )\n
    if (preg_match('/\n\((.*?)\)\n/', $event->description, $matches)) {
        $classDetails = $matches[1];
    }

    $explodedSummary = explode("::", $event->summary); // Exploding  FIMI:2:S1::MA-TF:TD::048 #011 into [FIMI:2:S1, MA-TF:TD, 048]

    $explodedFormationDetails = count($explodedSummary) >= 1 ? explode(":", $explodedSummary[0]) : []; // [FIMI, 2, S1]
    $explodedSubject = count($explodedSummary) >= 2 ? explode(":", $explodedSummary[1]) : []; // [MA-TF, TD]
    $explodedGroupAndCount = count($explodedSummary) >= 3 ? explode(" ", $explodedSummary[2]) : []; // [3IF3, #011]

    $group = count($explodedGroupAndCount) >= 1 ? $explodedGroupAndCount[0] : ""; // 3IF3, 221, ...
    $full_count = count($explodedGroupAndCount) >= 2 ? $explodedGroupAndCount[1] : ""; // #001, #011, ...
    $count = str_replace("#", "", $full_count); // 011, 003, ...
    $count = intval($count); // 11, 3, ...

    $department = count($explodedFormationDetails) >= 1 ? $explodedFormationDetails[0] : ""; // FIMI, IF, GI, ...
    $year = count($explodedFormationDetails) >= 2 ? $explodedFormationDetails[1] : ""; // 1, 2, 3, 4, 5
    $semester = count($explodedFormationDetails) >= 3 ? $explodedFormationDetails[2] : ""; // S1, S2

    $subjectTag = count($explodedSubject) >= 1 ? $explodedSubject[0] : ""; // MA-TF, ...
    $type = count($explodedSubject) >= 2 ? $explodedSubject[1] : null; // CM, TD, TP, EV => IE, EDT => Autre, PR => Projet

    // Information about the group used in the group regex: Department:Year:Semester:Group
    $group_info = count($explodedSummary) >= 1 ? $explodedSummary[0] . ':' . $group : ""; // FIMI:2:S2:221, FIMI:2:S2:048, IF:3:S1:3IF3, ...
    // Information about the class: SubjectTag:Type
    $class_info = count($explodedSummary) >= 2 ? $explodedSummary[1] : ""; // MA-TF:TD, MA-TF:CM, BDR:TD, EPS:EDT, ...

    // Match the class group, type and location, will be displayed as type class | location
    $matched_class = match_class($config, $group_info, $class_info, $subject, $classDetails);
    if (!is_class_valid($matched_class, $types)) {
        return;
    }
    $matched_type = match_type($config, $type, $subject, $classDetails);
    if(!is_type_valid($matched_type, $ctypes)) {
        return;
    }

    $matched_locations = $event->location ? array_map(function ($loc) use ($subject, $classDetails, $config) {
        return match_location($config, $loc, $subject, $classDetails);
    }, explode(",", $event->location)) : []; // Locations are comma-separated

    $event_name_name = $mode == 0 ? 'full' : ($mode == 1 ? 'short' : 'code');

    if ($matched_type) {
        $event_type_formatted = format_name_from_regex_result($matched_type, 'short', $classDetails);
    } else {
        $event_type_formatted = $type;
    }
    $event->summary = $event_type_formatted ?: "";
    if ($countInSummary && $event_type_formatted) $event->summary .= $count;
    $event->summary .= $event_type_formatted ? " " : "";

    if ($matched_class) {
        $event->summary .= format_name_from_regex_result($matched_class, $event_name_name, $classDetails);
    } else {
        $event->summary .= $subjectTag;
    }

    if ($event_name_name == 'code') $event_name_name = 'short'; // locations does not have code names
    $location = join(", ", array_map(function ($loc) use ($event_name_name, $classDetails) {
        return format_name_from_regex_result($loc, $event_name_name, $classDetails);
    }, array_filter($matched_locations, function ($loc) {
        return $loc != null;
    })));
    if ($location) {
        $event->location = $location;
        if ($locationInSummary) $event->summary .= " | " . $location;
    }

    if ($cleanDescription) {
        $event->description = removeFirstNLines($event->description, 3);
        if($classDetails) $event->description = $classDetails . '\n\n' . $event->description;
        else $event->description = '\n' . $event->description;
        $event->description = $subject . ' ' . $full_count . '\n' . $event->description;
    }

    printEvent($event);
}

function printEvent($event): void
{
    echo "BEGIN:VEVENT\r\n";
    echo getEventDataString($event);
    echo "END:VEVENT\r\n";
}

function getEventDataString($event): string
{
    $data = [
        "SUMMARY" => $event->summary,
        "DTSTART" => $event->dtstart,
        "DTEND" => $event->dtend,
        /*'DTSTART_TZ' => $event->dtstart_tz,
         'DTEND_TZ' => $event->dtend_tz,*/
        "DURATION" => $event->duration,
        "DTSTAMP" => $event->dtstamp,
        "UID" => $event->uid,
        "CREATED" => $event->created,
        "LAST-MODIFIED" => $event->last_modified,
        "DESCRIPTION" => $event->description,
        "LOCATION" => $event->location,
        "SEQUENCE" => $event->sequence,
        "STATUS" => $event->status,
        "TRANSP" => $event->transp,
        "ORGANISER" => $event->organizer,
        "ATTENDEE(S)" => $event->attendee,
    ];

    // Remove any blank values
    $data = array_filter($data);

    $output = "";
    foreach ($data as $key => $value) {
        $output .= sprintf("%s:%s\r\n", $key, str_replace("\n", "\\n", $value));
    }
    return $output;
}

if (isset($_GET["url"])) {
    if (!isset($_GET["types"])) {
        $types = ["*"];
    } else {
        $types = explode(",", $_GET["types"]);
    }
    if (!isset($_GET["ctypes"])) {
        $ctypes = ["*"];
    } else {
        $ctypes = explode(",", $_GET["ctypes"]);
    }

    $cleanDescription = isset($_GET["desc"]) && $_GET["desc"] != "false";
    $locationInSummary = isset($_GET["room"]) && $_GET["room"] != "false";
    $countInSummary = isset($_GET["count"]) && $_GET["count"] != "false";

    convertCalendar(urldecode($_GET["url"]), $_GET["mode"], $cleanDescription, $locationInSummary, $countInSummary, $types, $ctypes);
} else {
    header("Location: ./");
}
