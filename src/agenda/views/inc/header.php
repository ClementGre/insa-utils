<?php
$status = $status ?? array();
$logged_in = $status['logged_in'] ?? false;
$has_class = isset($status['class_id']) && $status['class_id'] != null; // requesting or not
?>
<header>
    <a class="back-link" href="<?= getRootPath() ?>"><p>← Menu</p></a>
    <div class="title">
        <h1>INS'Agenda</h1>
        <h2><?= $title ?? 'Cahier de texte collaboratif' ?></h2>
    </div>

    <?php
    if ($logged_in) {
        ?>
        <div class="dropdown" tabindex="0" aria-label="Autre">
            <div class="round round-1"></div>
            <div class="round round-2"></div>
            <div class="round round-3"></div>
            <div class="dropdown-content">
                <?php
                if ($has_class) {
                    ?>
                    <a href="<?= getRootPath() ?>agenda/">Tâches à venir</a>
                    <a href="<?= getRootPath() ?>agenda/all">Toutes les tâches</a>
                    <a href="<?= getRootPath() ?>agenda/subjects">Gestion des matières</a>
                    <a href="<?= getRootPath() ?>agenda/requests">Demandes d'ajout</a>
                    <a href="<?= getRootPath() ?>agenda/classes">Liste des classes</a>
                    <a href="<?= getRootPath() ?>agenda/account">Mon compte</a>
                    <?php
                } else {
                    ?>
                    <a href="<?= getRootPath() ?>agenda/classes">Liste des classes</a>
                    <a href="<?= getRootPath() ?>agenda/account">Mon compte</a>
                    <?php
                }
                ?>

            </div>
        </div>
        <?php
    } else {
        ?>
        <div></div>
        <?php
    }
    ?>
</header>