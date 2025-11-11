<?php

$redirect_url = "";
if(isset($_GET['redirect'])){
    $redirect_url = urldecode($_GET['redirect']);
}

$errors = array();

if (isset($_POST['email'])) {
    if (is_csrf_valid()) {
        $errors[] = request_login($_POST['email'], $redirect_url);
    } else {
        $errors[] = "Le formulaire a expiré, veuillez réessayer.";
    }
} else if (isset($_GET['id']) && isset($_GET['token'])) {
    $errors[] = try_token_login($_GET['id'], $_GET['token'], $redirect_url);
}

$status = get_user_status();
if ($status['logged_in'] && $status['class_id'] == null) {
    header('Location: ' . getRootPath() . $redirect_url);
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Authentification INSA Utils</title>

    <link rel="icon" href="<?= getRootPath() ?>icons/icon-256.png" type="image/png">
    <meta name="description" content="Connexion à INSA Utils"/>

    <link href="<?= getRootPath() ?>common.css" rel="stylesheet"/>

    <?= getTrackerScript() ?>
</head>
<body>
<header>
    <div class="side left">
        <a class="back-link" href="<?= getRootPath() . $redirect_url ?>">
            <div class="arrow">
                <div class="branch1"></div>
                <div class="branch2"></div>
            </div>
            <p>Retour</p>
        </a>
    </div>
    <div class="title">
        <h1>Authentification</h1>
        <h2>Connexion à INSA Utils</h2>
    </div>
    <div class="side right">
    </div>
</header>
<main>

    <section class="b-darken">
        <h3>Authentification</h3>
        <form action="?redirect=<?= urlencode($redirect_url) ?>" method="post">
            <?php set_csrf() ?>
            <label for="email">Email INSA&#8239;:</label><br/>
            <input type="text" name="email" id="email" required><span
                    style="font-size: 14px">@insa-lyon.fr</span><br/>
            <input type="submit" value="Envoyer l'email de vérification">
        </form>
        <?php print_messages($errors, true); ?>
    </section>

</main>
<footer>
    <?= getFooter('<a href="' . getRootPath() . '">Menu</a>', "Clément GRENNERAT") ?>
</footer>
</body>
<script src="<?= getRootPath() ?>template/main.js"></script>
</html>
