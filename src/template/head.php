<?php
require_once 'matomo.php';
function getHead($title, $desc, $keywords = ''): string
{
    $current = dirname($_SERVER["PHP_SELF"]);
    if($current == '/') $current = '';

    return '<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
       
        <title>' . $title . '</title>
        <meta name="description" content="' . $desc . '"/>
        ' . ($keywords == '' ? '' : '<meta name="keywords" content="' . $keywords . '"/>') . '
          
        <link href="' . getRootPath() . 'common.css" rel="stylesheet"/>
        <link href="' . $current . '/main.css" rel="stylesheet"/>
        ' . getTrackerScript() . '
    </head>';
}