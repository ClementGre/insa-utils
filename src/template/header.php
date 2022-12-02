<?php
function getHeader($title){
    return '<header>
            <a href="' . getRootPath() . '"><p>← Menu</p></a>
            <h1>' . $title . '</h1>
            <div></div>
        </header>';
}
function getSimpleHeader($title){
    return '<header style="justify-content: center;">
            <h1>' . $title . '</h1>
        </header>';
}
