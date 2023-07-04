<?php

$GLOBALS["ROOT_PATH"] = getRootPath_intern();

function getRootPath(): string
{
    return $GLOBALS["ROOT_PATH"] . 'src/';
}

function getRootPath_intern(): string
{
    $_origin = __DIR__ . "origin_path.php/"; // Ends in the wanted value
    $origin_ = $_SERVER['PHP_SELF']; // Starts in the wanted value

    $intersect_array = array_intersect(explode('/', $_origin), explode('/', $origin_));

    return implode('/', $intersect_array);
}
