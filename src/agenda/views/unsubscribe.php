<?php

$status = get_user_status();
$is_disabled = false;
$invalid_link = true;
$_SESSION['infos'] = $_SESSION['infos'] ?? array();
$_SESSION['errors'] = $_SESSION['errors'] ?? array();


if ($status['logged_in']) {
    $q = getDB()->prepare("SELECT status FROM users WHERE id=:id");
    $q->execute([":id" => $status['id']]);
    $user = $q->fetch();
    $is_disabled = $user['status'] == 'email_disabled';
}

if (isset($_GET['id']) && isset($_GET['token'])) {
    $q = getDB()->prepare("SELECT status, name, email_token FROM users WHERE id=:id");
    $q->execute([":id" => $_GET['id']]);

    $user = $q->fetch();
    if ($user != null) {
        $is_disabled = $user['status'] == 'email_disabled';

         if ($user['email_token'] == $_GET['token']) {
             if ($is_disabled) {
                 $_SESSION['infos'][] = "Vous avez déjà demandé à ne plus recevoir d'email d'INSA Utils.";
             }else{
                 disable_user_email($_GET['id'], $user['name']);
                 $_SESSION['infos'][] = "Vous ne recevrez plus d'email d'INSA Utils.<br>Un email de réactivation vous a été envoyé.";
                 $is_disabled = true;
             }
             $invalid_link = false;
        }
    }
}else if (isset($_GET['id']) && isset($_GET['resubscribe_token'])) {
    $q = getDB()->prepare("SELECT status, name, email_resubscribe_token FROM users WHERE id=:id");
    $q->execute([":id" => $_GET['id']]);

    $user = $q->fetch();
    if ($user != null) {
        $is_disabled = $user['status'] == 'email_disabled';

        if ($user['email_resubscribe_token'] == $_GET['resubscribe_token']) {
            if (!$is_disabled) {
                $_SESSION['infos'][] = "Vous avez déjà réactivé les emails.";
            }else{
                $q = getDB()->prepare("UPDATE users SET status='normal', email_resubscribe_token=null WHERE id=:id");
                $q->execute([":id" => $status['id']]);
                $user = $q->fetch();

                $_SESSION['infos'][] = "Vous avez bien réactivé les emails.";
                $is_disabled = false;
            }
            $invalid_link = false;
        }
    }
}

if (!$status['logged_in'] && $invalid_link) {
    $_SESSION['errors'][] = "Ce lien n'est pas valide.<br>Veuillez vous connecter pour accéder à cette page.";
}

$title = "Désabonnement des emails";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    include __DIR__ . '/inc/head.php';
    ?>
</head>
<body>
<?php include __DIR__ . '/inc/header.php' ?>
<main>

    <?php
    print_infos_html($_SESSION['infos']);
    print_errors_html($_SESSION['errors']);
    $_SESSION['infos'] = array();
    $_SESSION['errors'] = array();

    if(!$is_disabled || !$status['logged_in']){
        ?>
        <section class="b-darken">
            <h3>Ne plus recevoir d'emails</h3>
            <p>
                Sans emails, vous ne pourrez plus vous connecter sur de nouveaux appareils.
                <br>
                Un lien de réactivation vous sera envoyé par mail, conservez le pour ne pas perdre l'accès à votre compte.
                <br>
                Vous pourrez aussi réactiver les emails dans les paramètres de votre compte.
            </p>
        </section>
        <?php
    }
    if ($status['logged_in']) {
        if($is_disabled){
            ?>
            <section class="b-darken">
                <h3>Vous avez désactivé l'envoi d'emails</h3>
                <p>
                    Sans emails, vous ne pourrez plus vous connecter sur de nouveaux appareils.
                    <br>
                    Cliquez sur le bouton ci-dessous pour réactiver les emails.
                </p>
            </section>
            <section class="b-darken">
                <h3>Confirmer la réactivation des emails ?</h3>
                <form action="<?= getRootPath() ?>agenda/manage/disable_email" method="POST">
                    <?php set_csrf() ?>
                    <input type="hidden" name="action" value="enable">
                    <input type="submit" value="Confirmer">
                </form>
            </section>
            <?php
        }else {
            ?>
            <section class="b-darken">
                <h3>Confirmer la désactivation des emails ?</h3>
                <form action="<?= getRootPath() ?>agenda/manage/disable_email" method="POST">
                    <?php set_csrf() ?>
                    <input type="hidden" name="action" value="disable">
                    <input type="submit" value="Confirmer">
                </form>
            </section>
            <?php
        }
    }
    ?>
</main>
<footer>
    <?= getFooter('<a href="' . getRootPath() . 'agenda/classes">Liste des classes</a>', "Clément GRENNERAT") ?>
</footer>
</body>
<script src="<?= getRootPath() ?>agenda/js/main.js"></script>
<?php if ($status['is_in_class']) echo '<script src="' . getRootPath() . 'agenda/js/todo.js""></script>' ?>
</html>