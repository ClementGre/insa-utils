<?php
require_once __DIR__.'/../../../template/matomo.php';
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= "INS'Agenda" . (isset($title) ? (' - ' . $title) : 'Cahier de texte collaboratif') ?></title>

<link rel="icon" href="<?= getRootPath() ?>agenda/icons/icon-256.png" type="image/png">
<meta name="description" content="Application de cahier de texte collaboratif pour ta classe. Crée une nouvelle classe ou rejoint-en une existante."/>
<meta name="keywords" content="Todo, Agenda, Planning, Devoirs, Tâches, Collaboration, Plusieurs, Classe, Groupe"/>

<link rel="manifest" href="<?= getRootPath() ?>agenda/agenda.webmanifest">

<link href="<?= getRootPath() ?>common.css" rel="stylesheet"/>
<link href="<?= getRootPath() ?>agenda/css/main.css" rel="stylesheet"/>

<?= getTrackerScript() ?>
