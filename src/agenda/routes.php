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

any(getRootPath() . 'agenda', 'views/home.php');
any(getRootPath() . 'agenda/all', 'views/class/all.php');
any(getRootPath() . 'agenda/requests', 'views/class/requests.php');
any(getRootPath() . 'agenda/manage', 'views/class/manage.php');
any(getRootPath() . 'agenda/statusapi', 'views/class/statusapi.php');
any(getRootPath() . 'agenda/subjects', 'views/class/subjects.php');

any(getRootPath() . 'agenda/classes', 'views/classes.php');
any(getRootPath() . 'agenda/class/$class/join', 'views/class/join.php');

any(getRootPath() . 'agenda/auth', 'views/auth.php');
any(getRootPath() . 'agenda/account', 'views/account.php');

