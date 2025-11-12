<?php

// Should be called with the get parameter "token=<MENU_PUSH_TOKEN>"

if(isset($_GET['token']) && $_GET['token'] === getenv('MENU_PUSH_TOKEN')){
    // Read request json content and store it inside data/menu.json
    file_put_contents(dirname(__FILE__) . '/data/menu.json', file_get_contents('php://input'));
    // Copy data/menu.json to /data/menu.json to keep it persistent in case of server restart.
    copy(dirname(__FILE__) . '/data/menu.json', '/data/menu.json');
    // Return a 200 OK response
    http_response_code(200);
} else {
    http_response_code(403);
    echo "Forbidden";
}
