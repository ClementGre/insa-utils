<?php
require_once 'tracker.php';
function printHead($name, $title, $desc = '', $keywords = '', $iconPath = false, $manifestPath = false): void
{
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= getRootPath() ?>common.css" rel="stylesheet"/>
    <?php

    if($name != ''){
        ?>
        <title><?= $name ?> - <?= $title ?></title>
        <?php
    } else {
        ?>
        <title><?= $title ?></title>
        <?php
    }
    if ($iconPath){
        ?>
        <link rel="icon" href="<?= getRootPath() . $iconPath ?>" type="image/png">
        <?php
    }
    if ($desc != ''){
        ?>
        <meta name="description" content="<?= $desc ?>"/>
        <?php
    }
    if ($keywords != ''){
        ?>
        <meta name="keywords" content="<?= $keywords ?>"/>
        <?php
    }
    if ($manifestPath){
        ?>
        <link rel="manifest" href="<?= getRootPath() . $manifestPath ?>">
        <?php
    }
    echo getTrackerScript();
}