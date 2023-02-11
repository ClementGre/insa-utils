<?php

$errors = array();

if(!isset($_COOKIE['id'])){
    header('Location: ' . getRootPath() . 'todo/');
    exit;
}
$id = $_COOKIE['id'];

if(isset($_POST['email_code'])){
    if(is_csrf_valid() ){
        $errors[] = try_code_login($id, $_POST['email_code']);
    }else{
        $errors[] = "Le formulaire a expiré, veuillez réessayer.";
    }
}

if (is_logged_in()){
    header('Location: ' . getRootPath() . 'todo/');
    exit;
}

require '../template/head.php';
require '../template/header.php';
require '../template/footer.php';
require_once '../origin_path.php';
$title = "Authentification | Todo list de classe";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?= getHead($title) ?>
    <link rel="stylesheet" href="main.css">
</head>
<body>
<?= getHeader($title) ?>
<main class="">
    <section class="b-darken">
        <h3>Authentification</h3>

        <p>
            Un email de vérification vous a été envoyé.
            <br>
            Cliquez sur le lien dans l'email, ou entrez le code ci-dessous.
        </p>

        <form action="<?= getRootPath() ?>todo/auth" method="post">
            <?php set_csrf() ?>
            <label for="email_code">Code&#8239;:</label><br/>
            <input type="text" name="email_code" id="email_code" pattern="[0-9]{4}" required><br/>
            <input type="submit" value="Valider">
        </form>

        <?php print_errors_html($errors); ?>

    </section>
</main>
<footer>
    <?= getFooter('<a href="'.getRootPath().'todo/classes">Liste des classes</a>', "Clément GRENNERAT") ?>
</footer>
</body>
</html>