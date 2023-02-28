<?php
$status = get_user_status();

if (!$status['logged_in']) {
    header('Location: ' . getRootPath() . 'agenda/');
    exit;
}

$id = $status['id'];
$infos = array();
$errors = array();


$title = "Mon compte";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include __DIR__ . '/inc/head.php' ?>
</head>
<body>
<?php include __DIR__ . '/inc/header.php' ?>
<main>

    <?php
    print_infos_html($_SESSION['infos']);
    print_errors_html($_SESSION['errors']);
    $_SESSION['infos'] = array();
    $_SESSION['errors'] = array();
    gen_csrf_key()
    ?>

    <section class="b-darken">
        <h3>Se déconnecter</h3>

        <div class="form-container">
            <form action="<?= getRootPath() ?>agenda/manage/account" method="post">
                <?php set_csrf_without_regen(); ?>
                <input type="hidden" name="action" value="disconnect">
                <input type="submit" value="Se déconnecter">
            </form>
            <form action="<?= getRootPath() ?>agenda/manage/account" method="post">
                <?php set_csrf_without_regen() ?>
                <input type="hidden" name="action" value="disconnect_all">
                <input type="submit" value="Se déconnecter de tous les autres appareils">
            </form>
        </div>
    </section>
    <section class="b-darken">
        <h3>Gestion de mes données</h3>

        <div class="form-container">
            <form action="<?= getRootPath() ?>agenda/manage/account" method="post">
                <?php set_csrf_without_regen() ?>
                <input type="hidden" name="action" value="download_data">
                <input type="submit" value="Télécharger mes données">
            </form>
            <form action="<?= getRootPath() ?>agenda/manage/account" method="post">
                <?php set_csrf_without_regen() ?>
                <input type="hidden" name="action" value="delete_account">
                <input type="submit" value="Supprimer mon compte et mes données">
            </form>
        </div>
    </section>

    <style>
        .form-container{
            width: fit-content;
            margin: auto;
        }
        .form-container form{
            width: 100%;
        }
        .form-container form input{
            width: 100%;
            height: 35px;
            padding: 0 9px;
        }
    </style>
</main>
<footer>
    <?= getFooter('<a href="' . getRootPath() . 'agenda/">Tâches à venir</a>', "Clément GRENNERAT") ?>
</footer>
</body>
<script src="<?= getRootPath() ?>agenda/js/main.js"></script>
</html>