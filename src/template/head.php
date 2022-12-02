<?php
require_once 'matomo.php';
function getHead($title, $desc, $keywords = ''): string
{
    return '<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
       
        <title>' . $title . '</title>
        <meta name="description" content="' . $desc . '"/>
        ' . ($keywords == '' ? '' : '<meta name="keywords" content="' . $keywords . '"/>') . '
          
        <link href="' . getRootPath() . 'common.css" rel="stylesheet"/>
        <link href="' . dirname($_SERVER["PHP_SELF"]) . '/main.css" rel="stylesheet"/>
        ' . getTrackerScript() . '
    </head>';
}