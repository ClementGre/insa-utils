<?php
function printHeader($name, $subtitle = ''): void
{
    ?>
    <header>
        <a class="back-link" href="<?= getRootPath() ?>"><p>←&#8239;Menu</p></a>
        <div class="title">
            <h1><?= $name ?></h1>
            <?php
            if ($subtitle != null) {
                ?>
                <h2><?= $subtitle ?></h2>
                <?php
            }
            ?>
        </div>
        <div class="spacing"></div>
    </header>
    <?php
}

function printSimpleHeader($name, $subtitle = ''): void
{
    ?>
    <header style="justify-content: center;">
        <div class="title">
            <h1><?= $name ?></h1>
            <?php
            if ($subtitle != null) {
                ?>
                <h2><?= $subtitle ?></h2>
                <?php
            }
            ?>
        </div>
    </header>
    <?php
}
