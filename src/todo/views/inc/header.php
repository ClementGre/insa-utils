<?php
$status = $status ?? array();
$logged_in = $status['logged_in'] ?? false;
$has_class = isset($status['class_id']) && $status['class_id'] != null; // requesting or not
?>
<header>
    <a class="back-link" href="<?= getRootPath() ?>"><p>← Menu</p></a>
    <h1><?= $title ?? '' ?></h1>
    <?php
    if ($logged_in) {
        ?>
        <div class="dropdown">
            <div class="round round-1"></div>
            <div class="round round-2"></div>
            <div class="round round-3"></div>
            <div class="dropdown-content">
                <?php
                if ($has_class) {
                    ?>
                    <a href="<?= getRootPath() ?>todo/">Ma classe</a>
                    <a href="<?= getRootPath() ?>todo/account">Mon compte</a>
                    <a href="<?= getRootPath() ?>todo/classes">Liste des classes</a>
                    <?php
                } else {
                    ?>
                    <a href="<?= getRootPath() ?>todo/classes">Liste des classes</a>
                    <a href="<?= getRootPath() ?>todo/account">Mon compte</a>
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