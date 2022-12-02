<?php

$GLOBALS["ROOT_PATH"] = getRootPath_intern();

function getRootPath(): string
{
    return $GLOBALS["ROOT_PATH"];
}

function getRootPath_intern(): string
{
    $_origin = __DIR__ . "/"; // Ends in the wanted value
    $origin_ = $_SERVER['PHP_SELF']; // Starts in the wanted value

    $intersect_array = array_intersect(explode('/', $_origin), explode('/', $origin_));

    return implode('/', $intersect_array);
}