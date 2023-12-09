<?php
// Libs
require_once __DIR__ . '/../../libs/router.php';
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../origin_path.php';
require_once __DIR__ . '/../../libs/utils.php';

// Template
require __DIR__ . '/../template/footer.php';
require_once __DIR__ . '/../template/matomo.php';

// Auth
require_once __DIR__ . '/../account/auth.php';

// Agenda
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/class.php';

// Configuration
setlocale(LC_ALL, 'fr_FR');
date_default_timezone_set("Europe/Paris");

// Need to remove old cookies that were set on agenda path
setcookie('id', '', [
    'expires' => 0,
    'path' => getRootPath() . 'agenda/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);
setcookie('auth_token', '', [
    'expires' => 0,
    'path' => getRootPath() . 'agenda/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);


// Not authenticated pages
any(getRootPath() . 'agenda', __DIR__ . '/views/home.php');

any(getRootPath() . 'agenda/classes', __DIR__ . '/views/classes.php');
any(getRootPath() . 'agenda/class/$class/join', __DIR__ . '/views/class/join.php');

// Class related pages
any(getRootPath() . 'agenda/all', __DIR__ . '/views/class/all.php');
any(getRootPath() . 'agenda/requests', __DIR__ . '/views/class/requests.php');
any(getRootPath() . 'agenda/subjects', __DIR__ . '/views/class/subjects.php');

// Managers
any(getRootPath() . 'agenda/manage/todo', __DIR__ . '/views/manage/manage_todo.php');
any(getRootPath() . 'agenda/manage/subjects', __DIR__ . '/views/manage/manage_subjects.php');
any(getRootPath() . 'agenda/manage/requests', __DIR__ . '/views/manage/manage_requests.php');

// JS API
any(getRootPath() . 'agenda/jsapi/status', __DIR__ . '/views/jsapi/jsapi_status.php');





