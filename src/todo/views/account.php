<?php
$status = get_user_status();

if (!$status['logged_in']) {
    header('Location: ' . getRootPath() . 'todo/');
    exit;
}

if(is_csrf_valid('disconnect')){
    remove_cookie('id');
    remove_cookie('auth_token');
    header('Location: ' . getRootPath() . 'todo/');
    exit;
}

$id = $status['id'];
$errors = array();

$title = "Mon compte | Todo list de classe";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include __DIR__ . '/inc/head.php' ?>
</head>
<body>
<?php include __DIR__ . '/inc/header.php' ?>
<main class="">
    <section class="b-darken">
        <h3>Se déconnecter</h3>

        <form action="" method="post">
            <?php set_csrf('disconnect') ?>
            <input type="submit" value="Se déconnecter">
        </form>
        <form action="" method="post">
            <?php set_csrf('disconnect_all') ?>
            <input type="submit" value="Se déconnecter de tous les appareils">
        </form>
    </section>
    <section class="b-darken">
        <h3>Gestion de mes données</h3>

        <form action="" method="post">
            <?php set_csrf('download') ?>
            <input type="submit" value="Télécharger mes données">
        </form>
        <form action="" method="post">
            <?php set_csrf('delete') ?>
            <input type="submit" value="Supprimer mon compte et mes données">
        </form>
    </section>
</main>
<footer>
    <?= getFooter('<a href="' . getRootPath() . 'todo/classes">Liste des classes</a>', "Clément GRENNERAT") ?>
</footer>
</body>
<script src="<?= getRootPath() ?>todo/js/main.js"></script>
</html>