<?php
$status = get_user_status();

if (!$status['logged_in']) {
    header('Location: ' . getRootPath() . 'agenda/');
    exit;
}

$id = $status['id'];
$infos = array();
$errors = array();

if(is_csrf_valid('disconnect')){
    remove_cookie('id');
    remove_cookie('auth_token');
    header('Location: ' . getRootPath() . 'agenda/');
    exit;
}
if(is_csrf_valid('disconnect_all')){
    // regen token
    $auth_token = randomToken(64);
    $q = getDB()->prepare("UPDATE users SET auth_token=:auth_token WHERE id=:id");
    $q->execute([
       'auth_token' => $auth_token,
       'id' => $status['id']
    ]);
    set_cookie('auth_token', $auth_token);
    $infos[] = "Vous avez été déconecté de tous vos autres appareils";
}

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

    <?php print_infos_html($infos); ?>

    <section class="b-darken">
        <h3>Se déconnecter</h3>

        <div class="form-container">
            <form action="" method="post">
                <?php set_csrf('disconnect') ?>
                <input type="submit" value="Se déconnecter">
            </form>
            <form action="" method="post">
                <?php set_csrf('disconnect_all') ?>
                <input type="submit" value="Se déconnecter de tous les autres appareils">
            </form>
        </div>
    </section>
    <section class="b-darken">
        <h3>Gestion de mes données</h3>

        <div class="form-container">
            <form action="" method="post">
                <?php set_csrf('download') ?>
                <input type="submit" value="Télécharger mes données">
            </form>
            <form action="" method="post">
                <?php set_csrf('delete') ?>
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