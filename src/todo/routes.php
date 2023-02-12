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

any(getRootPath() . 'todo', 'views/home.php');
any(getRootPath() . 'todo/classes', 'views/classes.php');
any(getRootPath() . 'todo/auth', 'views/auth.php');
any(getRootPath() . 'todo/account', 'views/account.php');

any(getRootPath() . 'todo/subjects', 'views/class/subjects.php');

any(getRootPath() . 'todo/class/$class/edit/$todo_id', 'views/class/edit.php');
any(getRootPath() . 'todo/class/$class/all', 'views/class/all.php');
any(getRootPath() . 'todo/class/$class/join', 'views/class/join.php');
