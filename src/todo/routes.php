<?php
require_once __DIR__.'/router.php';
require_once __DIR__.'/../origin_path.php';

get(getRootPath() . 'todo', '/views/home.php');
get(getRootPath() . 'todo/classes', '/views/home.php');

any(getRootPath() . 'todo/auth', '/views/auth.php');

get(getRootPath() . 'class/$class', 'views/class.php');

get(getRootPath() . 'class/$class/edit/$todo_id', 'views/class/edit.php');
get(getRootPath() . 'class/$class/all', 'views/class/all.php');
get(getRootPath() . 'class/$class/add', 'views/class/add.php');
get(getRootPath() . 'class/$class/join', 'views/class/join.php');
