<?php
$errors = array();

$redirect_url = "";
if(isset($_GET['redirect'])){
    $redirect_url = urldecode($_GET['redirect']);
}

if(!isset($_COOKIE['id'])){
    header('Location: ' . getRootPath() . $redirect_url);
    exit;
}
$id = $_COOKIE['id'];

if(isset($_POST['email_code'])){
    if(is_csrf_valid() ){
        $errors[] = try_code_login($id, $_POST['email_code'], $redirect_url);
    }else{
        $errors[] = "Le formulaire a expiré, veuillez réessayer.";
    }
}

if (is_logged_in()){
    header('Location: ' . getRootPath() . $redirect_url);
    exit;
}

$title = "Authentification";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Authentification INSA Utils</title>

    <link rel="icon" href="<?= getRootPath() ?>icons/icon-256.png" type="image/png">
    <meta name="description" content="Confirmation de l'identité"/>

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
        <h2>Confirmation de l'identité</h2>
    </div>
    <div class="side right">
    </div>
</header>
<main class="">
    <section class="b-darken">
        <h3>Confirmation de l'identité</h3>

        <p>
            Un email de vérification vous a été envoyé.
            <br>
            Cliquez sur le lien dans l'email, ou entrez le code ci-dessous.
        </p>

        <form action="?redirect=<?= $redirect_url ?>" method="post">
            <?php set_csrf() ?>
            <label for="email_code">Code&#8239;:</label><br/>
            <input type="text" name="email_code" id="email_code" pattern="[0-9]{4}" required><br/>
            <input type="submit" value="Valider">
        </form>

        <?php print_messages($errors, true); ?>

    </section>
</main>
<footer>
    <?= getFooter('<a href="' . getRootPath() . '">Menu</a>', "Clément GRENNERAT") ?>
</footer>
</body>
<script src="<?= getRootPath() ?>account/js/main.js"></script>
</html>
