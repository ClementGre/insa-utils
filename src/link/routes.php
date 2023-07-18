<?php
// Libs
require_once __DIR__ . '/../../libs/router.php';
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../origin_path.php';
require_once __DIR__ . '/../../libs/utils.php';

// Template
require __DIR__ . '/../template/header.php';
require __DIR__ . '/../template/head.php';
require __DIR__ . '/../template/footer.php';
require_once __DIR__ . '/../template/matomo.php';

// Auth
require_once __DIR__ . '/../account/auth.php';


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
any(getRootPath() . 'link', __DIR__ . '/views/home.php');

// Class related pages

// Managers

// JS API





