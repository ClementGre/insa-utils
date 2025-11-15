<?php
$status = get_user_status();

if (!$status['logged_in']) {
    header('Location: ' . getRootPath() . 'account/login?redirect=account/manage');
    exit;
}

$id = $status['id'];
$infos = array();
$errors = array();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Compte INSA Utils</title>

    <link rel="icon" href="<?= getRootPath() ?>icons/icon-256.png" type="image/png">

    <link href="<?= getRootPath() ?>common.css" rel="stylesheet"/>

    <?= getTrackerScript() ?>
</head>
<body>
<?php printHeader("INSA Utils", "Paramètre du compte", MenuType::Account); ?>
<main>

    <?php
    print_session_messages();
    gen_csrf_key()
    ?>

    <section class="b-darken">
        <h3>Se déconnecter</h3>

        <div class="form-container">
            <form action="<?= getRootPath() ?>account/manage/account" method="post">
                <?php set_csrf_without_regen(); ?>
                <input type="hidden" name="action" value="disconnect">
                <input type="submit" value="Se déconnecter">
            </form>
            <form action="<?= getRootPath() ?>account/manage/account" method="post">
                <?php set_csrf_without_regen() ?>
                <input type="hidden" name="action" value="disconnect_all">
                <input type="submit" value="Se déconnecter de tous les autres appareils">
            </form>
        </div>
    </section>
    <section class="b-darken">
        <h3>Gestion de mes données</h3>

        <div class="form-container">
            <form action="<?= getRootPath() ?>account/manage/account" method="post">
                <?php set_csrf_without_regen() ?>
                <input type="hidden" name="action" value="download_data">
                <input type="submit" value="Télécharger mes données">
            </form>
            <form action="<?= getRootPath() ?>account/manage/account"
                  onsubmit="return confirm('Votre compte et toutes vos données seront supprimées définitivement, mis à part les tâches publiques et liens partagés. Confirmer la suppression ?');"
                  method="post">
                <?php set_csrf_without_regen() ?>
                <input type="hidden" name="action" value="delete_account">
                <input type="submit" value="Supprimer mon compte et mes données">
            </form>
        </div>
    </section>

    <style>
        .form-container {
            width: fit-content;
            margin: auto;
        }

        .form-container form {
            width: 100%;
        }

        .form-container form input {
            width: 100%;
            height: 35px;
            padding: 0 9px;
        }
    </style>
</main>
<footer>
    <?= getFooter('<a href="' . getRootPath() . '">Menu</a>', "Clément GRENNERAT") ?>
</footer>
</body>
<script src="<?= getRootPath() ?>template/main.js"></script>
</html>
