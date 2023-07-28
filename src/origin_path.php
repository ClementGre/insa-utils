<?php

$GLOBALS["ROOT_PATH"] = getRootPath_intern();

function getRootPath(): string
{
    return $GLOBALS["ROOT_PATH"];
}

function getRootPath_intern(): string
{
    $_origin = str_replace("\\", "/", __DIR__); // Ends in the wanted value
    $origin_ = $_SERVER['PHP_SELF']; // Starts in the wanted value

    $intersect_array = array_intersect(explode('/', $_origin), explode('/', $origin_));
    $intersect_array = array_filter($intersect_array, fn($value) => !is_null($value) && $value !== '');

    return '/' . implode('/', $intersect_array) . '/';
}
