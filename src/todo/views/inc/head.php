<?php
require_once __DIR__.'/../../../template/matomo.php';
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= $title ?? '' ?></title>

<link rel="icon" href="<?= getRootPath() ?>todo/icon.jpg" type="image/png">
<meta name="description" content="<?= $desc ?? '' ?>"/>
<meta name="keywords" content="<?= $keywords ?? '' ?>"/>

<link rel="manifest" href="<?= getRootPath() ?>todo/todo.webmanifest">

<link href="<?= getRootPath() ?>common.css" rel="stylesheet"/>
<link href="<?= getRootPath() ?>todo/css/main.css" rel="stylesheet"/>

<?= getTrackerScript() ?>
