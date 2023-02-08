<?php
require_once __DIR__.'/router.php';
require_once __DIR__.'/../origin_path.php';
require_once __DIR__.'/db.php';
require_once __DIR__.'/utils.php';

any(getRootPath() . 'todo', '/views/home.php');
any(getRootPath() . 'todo/classes', '/views/home.php');
any(getRootPath() . 'todo/auth', '/views/auth.php');

any(getRootPath() . 'class/$class', 'views/class.php');

any(getRootPath() . 'class/$class/edit/$todo_id', 'views/class/edit.php');
any(getRootPath() . 'class/$class/all', 'views/class/all.php');
any(getRootPath() . 'class/$class/add', 'views/class/add.php');
any(getRootPath() . 'class/$class/join', 'views/class/join.php');
