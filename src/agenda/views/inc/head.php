<?php
require_once __DIR__.'/../../../template/matomo.php';
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= $title ?? '' ?></title>

<link rel="icon" href="<?= getRootPath() ?>agenda/icons/icon-256.png" type="image/png">
<meta name="description" content="<?= $desc ?? '' ?>"/>
<meta name="keywords" content="<?= $keywords ?? '' ?>"/>

<link rel="manifest" href="<?= getRootPath() ?>agenda/agenda.webmanifest">

<link href="<?= getRootPath() ?>common.css" rel="stylesheet"/>
<link href="<?= getRootPath() ?>agenda/css/main.css" rel="stylesheet"/>

<?= getTrackerScript() ?>
