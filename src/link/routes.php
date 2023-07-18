<?php
// Libs
require_once __DIR__ . '/../../libs/router.php';
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../origin_path.php';
require_once __DIR__ . '/../../libs/utils.php';

// Auth
require_once __DIR__ . '/../account/auth.php';

// Link'INSA
require_once __DIR__ . '/../link/php/link.php';

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

// Managers
any(getRootPath() . 'link/manage/add_link', __DIR__ . '/managers/add_link.php');
any(getRootPath() . 'link/manage/edit_link', __DIR__ . '/managers/edit_link.php');
any(getRootPath() . 'link/manage/delete_link', __DIR__ . '/managers/delete_link.php');

// JS API
any(getRootPath() . 'link/jsapi/like', __DIR__ . '/jsapi/like.php');
any(getRootPath() . 'link/jsapi/search', __DIR__ . '/jsapi/search.php');

// Template
require __DIR__ . '/../template/header.php';
require __DIR__ . '/../template/head.php';
require __DIR__ . '/../template/footer.php';
require_once __DIR__ . '/../template/matomo.php';

// Pages

any(getRootPath() . 'link', __DIR__ . '/views/home.php');








