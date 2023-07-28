<?php
enum MenuType
{
    case Account;
    case Link;
    case None;
}
function printHeader($name, $subtitle = '', $menu_type = MenuType::None, $status = []): void
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
        <div class="side right">
            <?php
                if($menu_type == MenuType::Account){
                    printAccountSideRight();
                }else if($menu_type == MenuType::Link){
                    printLinkSideRight($status);
                }
            ?>
        </div>
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

function printAccountSideRight(): void
{
    ?>
    <div class="dropdown" tabindex="0" aria-label="Autre">
        <div class="round round-1"></div>
        <div class="round round-2"></div>
        <div class="round round-3"></div>
        <div class="dropdown-content">
            <a href="<?= getRootPath() ?>agenda/">INS'Agenda</a>
            <a href="<?= getRootPath() ?>link/">Link'INSA</a>
            <a href="<?= getRootPath() ?>account/manage">Mon compte</a>
        </div>
    </div>
    <?php
}
function printLinkSideRight($status): void
{
    ?>
    <div class="dropdown" tabindex="0" aria-label="Autre">
        <div class="round round-1"></div>
        <div class="round round-2"></div>
        <div class="round round-3"></div>
        <div class="dropdown-content">
            <a href="<?= getRootPath() ?>link/">Liens</a>
            <a href="<?= getRootPath() ?>link/?q=<?= $status['name'] ?>">Mes liens</a>
            <a href="<?= getRootPath() ?>account/manage">Mon compte</a>
        </div>
    </div>
    <?php
}
