<?php

$errors = array();

if (isset($_POST['email'])) {
    if (is_csrf_valid()) {
        $errors[] = request_login($_POST['email'], 'link');
    } else {
        $errors[] = "Le formulaire a expiré, veuillez réessayer.";
    }
}

$status = get_user_status();
$logged_in = $status['logged_in'] ?? false;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    printHead("Link'INSA", "Partage collaboratif de liens",
        "Link'INSA permet aux Insaliens de partager tous types de liens : ressources éducatives, associatives ou techniques !",
        '', 'icons/icon-128.png', 'link/link.webmanifest');
    ?>
    <link href="<?= getRootPath() ?>link/css/main.css" rel="stylesheet"/>
</head>
<body>
<?php printHeader("Link'INSA", "Partage collaboratif de liens",
    $logged_in ? MenuType::Link : MenuType::None); ?>
<main>

    <?php print_session_messages(); ?>


    <?php
    if ($logged_in) {
        include __DIR__ . '/links.php';
    } else {
        ?>
        <section class="b-darken">
            Link'INSA permet aux Insaliens de partager tous types de liens : ressources éducatives, associatives ou
            techniques !
            <br>
            Pour consulter les liens et ajouter des liens, il est nécessaire de se connecter en inscrivant ton email
            INSA et en cliquant sur le lien de
            vérification envoyé par email.
        </section>

        <section class="b-darken">
            <h3>Authentification</h3>
            <form action="<?= getRootPath() ?>link/" method="post">
                <?php set_csrf() ?>
                <label for="email">Email INSA&#8239;:</label><br/>
                <input type="text" name="email" id="email" required><span
                        style="font-size: 14px">@insa-lyon.fr</span><br/>
                <input type="submit" value="Envoyer l'email de vérification">
            </form>
            <?php print_messages($errors, true); ?>
        </section>
        <?php
    }
    ?>

</main>
<footer>
    <?= getFooter('', "Clément GRENNERAT") ?>
</footer>
</body>
<script src="<?= getRootPath() ?>template/main.js"></script>
<?php
if ($logged_in) {
    ?>
    <script src="<?= getRootPath() ?>link/main.js"></script>
    <?php
}
?>
</html>
