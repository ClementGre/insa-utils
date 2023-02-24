<?php
require_once __DIR__.'/router.php';

require __DIR__.'/../template/footer.php';

require_once __DIR__.'/../origin_path.php';

require_once __DIR__.'/db.php';
require_once __DIR__.'/utils.php';
require_once __DIR__.'/php/auth.php';
require_once __DIR__.'/php/class.php';

setlocale(LC_ALL, 'fr_FR');
date_default_timezone_set("Europe/Paris");

// Not authenticated pages
any(getRootPath() . 'agenda', 'views/home.php');
any(getRootPath() . 'agenda/auth', 'views/auth.php');
any(getRootPath() . 'agenda/account', 'views/account/account.php');
any(getRootPath() . 'agenda/classes', 'views/classes.php');
any(getRootPath() . 'agenda/class/$class/join', 'views/class/join.php');

// Class related pages
any(getRootPath() . 'agenda/all', 'views/class/all.php');
any(getRootPath() . 'agenda/requests', 'views/class/requests.php');
any(getRootPath() . 'agenda/subjects', 'views/class/subjects.php');

// Managers
any(getRootPath() . 'agenda/manage/todo', 'views/class/manage/manage_todo.php');
any(getRootPath() . 'agenda/manage/subjects', 'views/class/manage/manage_subjects.php');
any(getRootPath() . 'agenda/manage/requests', 'views/class/manage/manage_requests.php');

// JS API
any(getRootPath() . 'agenda/jsapi/status', 'views/class/jsapi/jsapi_status.php');
any(getRootPath() . 'agenda/jsapi/checkcsrf', 'views/class/jsapi/jsapi_checkcsrf.php');






