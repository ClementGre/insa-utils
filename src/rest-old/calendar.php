<?php

function getMonthLastDay($date){
    $mon = $date['mon'];
    $year = $date['year'];

    if(checkdate($mon, 31, $year))
        return 31;
    elseif(checkdate($mon, 30, $year))
        return 30;
    elseif(checkdate($mon, 29, $year))
        return 29;
    elseif(checkdate($mon, 28, $year))
        return 28;
    return 27;

}

function printCalendar($empty = false, $dejZeroDays = array(), $dejOneDays = array(), $dejTwoDays = array(), $oneDays = array(), $twoDays = array()){
    echo "<table border='0'>";
    // Get the current date
    $date = getdate();

    // Get the value of day, month, year
    $mday = $date['mday'];
    $mon = $date['mon'];
    $wday = $date['wday'];
    $month = $date['month'];
    $year = $date['year'];

    $dayCount = $wday;
    $day = $mday;
    while($day > 0) {
        $days[$day--] = $dayCount--;
        if($dayCount < 0)
            $dayCount = 6;
    }

    $dayCount = $wday;
    $day = $mday;



    $lastDay = getMonthLastDay($date);
    while($day <= $lastDay){
        $days[$day++] = $dayCount++;
        if($dayCount > 6)
            $dayCount = 0;
    }
    // Days to highlight
    $day_to_highlight = array(8, 9, 10, 11, 12, 22,23,24,25,26);
    $mois = array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
        "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");


    echo("<tr>");

    echo("<th colspan='7' align='center'>" . $mois[$mon-1] . " $year</th>");
    echo("</tr>");
    echo("<tr>");
    echo("<td class='weekdayheader'>Lun</td>");
    echo("<td class='weekdayheader'>Mar</td>");
    echo("<td class='weekdayheader'>Mer</td>");
    echo("<td class='weekdayheader'>Jeu</td>");
    echo("<td class='weekdayheader'>Ven</td>");
    echo("<td class='red weekdayheader'>Sam</td>");
    echo("<td class='red weekdayheader'>Dim</td>");
    echo("</tr>");

    $startDay = 1;
    $d = $days[1];

    echo("<tr>");
    while($startDay < $d) {
        echo("<td></td>");
        $startDay++;
    }

    for($d = 1 ; $d <= $lastDay ; $d++) {

        if($empty){
            if($d < $mday)
                echo("<td class='red'><p>$d</p></td>");
            else if($d == $mday)
                echo("<td class='bg-current'><p>$d</p></td>");
            else if($startDay == 6 || $startDay == 7)
                echo("<td class='bg-weekend'><p>$d</p></td>");
            else
                echo("<td class='bg-weekday'><p>$d</p></td>");
        }else{
            if(in_array($d, $dejZeroDays))
                $bg = "bg-dej-zero";
            else if(in_array($d, $dejOneDays))
                $bg = "bg-dej-one";
            else if(in_array($d, $dejTwoDays))
                $bg = "bg-dej-two";
            else if(in_array($d, $oneDays))
                $bg = "bg-one";
            else if(in_array($d, $twoDays))
                $bg = "bg-two";
            else
                $bg = "bg-zero";

            if($d < $mday)
                echo("<td class='red'><p>$d</p></td>");
            else
                echo("<td class='$bg'><p>$d</p></td>");
        }

        $startDay++;
        if($startDay > 7 && $d < $lastDay){
            $startDay = 1;
            echo("</tr>");
            echo("<tr>");
        }
    }
    echo("</tr></table>");
}