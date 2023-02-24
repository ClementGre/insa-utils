<?php
function printHeader($name, $subtitle = ''): void
{
    ?>
    <header>
        <div class="side left">
            <a class="back-link" href="<?= getRootPath() ?>">
                <div class="arrow">
                    <div class="branch1"></div>
                    <div class="branch2"></div>
                </div>
                <p>Menu</p>
            </a>
        </div>
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
        <div class="side right"></div>
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
