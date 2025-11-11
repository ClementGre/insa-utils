<?php
// Libs
require_once __DIR__ . '/../../libs/router.php';
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../origin_path.php';
require_once __DIR__ . '/../../libs/utils.php';

// Template
require __DIR__ . '/../template/footer.php';
require __DIR__ . '/../template/header.php';
require_once __DIR__.'/../template/tracker.php';

// Auth
require_once __DIR__ . '/auth.php';

// Configuration
setlocale(LC_ALL, 'fr_FR');
date_default_timezone_set("Europe/Paris");

// Routes
any(getRootPath() . 'account/login', __DIR__.'/views/login.php');
any(getRootPath() . 'account/auth', __DIR__.'/views/auth_confirm.php');
any(getRootPath() . 'account/unsubscribe', __DIR__.'/views/unsubscribe.php');
any(getRootPath() . 'account/manage', __DIR__.'/views/account.php');

// Managers
any(getRootPath() . 'account/manage/disable_email', __DIR__.'/views/manage/manage_disable_email.php');
any(getRootPath() . 'account/manage/account', __DIR__.'/views/manage/manage_account.php');

// JSAPI
any(getRootPath() . 'account/jsapi/checkcsrf', __DIR__ . '/views/jsapi/jsapi_checkcsrf.php');







