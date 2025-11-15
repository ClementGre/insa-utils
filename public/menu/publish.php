<?php

// Should be called with the get parameter "token=<MENU_PUSH_TOKEN>"

if(isset($_GET['token']) && $_GET['token'] === getenv('MENU_PUSH_TOKEN')){
    // Create a new directory/file if it doesn't exist yet
    if(!is_dir('/data/menu-data')){
        mkdir('/data/menu-data', 0755, true);
    }
    // Read request json content and store it inside data/menu.json
    file_put_contents('/data/menu-data/menu.json', file_get_contents('php://input'));
    // Return a 200 OK response
    http_response_code(200);
} else {
    http_response_code(403);
    echo "Forbidden";
}
