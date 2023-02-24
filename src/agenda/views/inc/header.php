<?php
$status = $status ?? array();
$logged_in = $status['logged_in'] ?? false;
?>
<header>
    <div class="side left">
        <?php
        if ($is_home ?? false) {
            ?>
            <a class="back-link" href="<?= getRootPath() ?>">
                <div class="arrow">
                    <div class="branch1"></div>
                    <div class="branch2"></div>
                </div>
                <p>Menu</p>
            </a>
            <?php
        } else {
            ?>
            <a class="back-link" href="<?= getRootPath() ?>agenda">
                <div class="arrow">
                    <div class="branch1"></div>
                    <div class="branch2"></div>
                </div>
                <p>Tâches</p>
            </a>
            <?php
        }
        ?>
    </div>
    <div class="title">
        <h1>INS'Agenda</h1>
        <h2><?= $title ?? 'Cahier de texte collaboratif' ?></h2>
    </div>
    <div class="side right">
        <?php
        if ($logged_in) {
            ?>
            <div class="dropdown" tabindex="0" aria-label="Autre">
                <div class="round round-1"></div>
                <div class="round round-2"></div>
                <div class="round round-3"></div>
                <div class="dropdown-content">
                    <?php
                    if ($status['is_in_class']) {
                        ?>
                        <a href="<?= getRootPath() ?>agenda/">Tâches à venir</a>
                        <a href="<?= getRootPath() ?>agenda/all">Toutes les tâches</a>
                        <a href="<?= getRootPath() ?>agenda/subjects">Gestion des matières</a>
                        <a href="<?= getRootPath() ?>agenda/requests">Demandes d'ajout</a>
                        <?php
                    } else if ($status['is_requesting_class']) {
                        ?>
                        <a href="<?= getRootPath() ?>agenda/">Tâches à venir</a>
                        <?php
                    } else {
                        ?>
                        <a href="<?= getRootPath() ?>agenda/">Accueil</a>
                        <?php
                    }
                    ?>
                    <a href="<?= getRootPath() ?>agenda/classes">Liste des classes</a>
                    <a href="<?= getRootPath() ?>agenda/account">Mon compte</a>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</header>