<?php
function getFooter($left, $right): string
{
    return '<div>
            <p>' . $left . '</p>
        </div>
        <div>
            <p>' . $right .'</p>
        </div>';
}