<?php
function endsWith($haystack, $needle): bool
{
    $length = strlen($needle);
    if (!$length) return true;
    return substr($haystack, -$length) === $needle;
}