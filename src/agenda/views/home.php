<?php

$errors = array();

if (isset($_POST['email'])) {
    if (is_csrf_valid()) {
        $errors[] = request_login($_POST['email']);
    } else {
        $errors[] = "Le formulaire a expiré, veuillez réessayer.";
    }
} else if (isset($_GET['id']) && isset($_GET['token'])) {
    $errors[] = try_token_login($_GET['id'], $_GET['token']);
}

$status = get_user_status();

if ($status['logged_in'] && $status['class_id'] == null) {
    header('Location: ' . getRootPath() . 'agenda/classes');
    exit;
}
if ($status['is_in_class']) {
    $title = "Tâches à venir";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    include __DIR__ . '/inc/head.php';
    if ($status['is_in_class']) echo '<link href="' . getRootPath() . 'agenda/css/todo.css" rel="stylesheet"/>';
    ?>
</head>
<body>
<?php include __DIR__ . '/inc/header.php' ?>
<main>

    <?php
    if ($status['is_in_class']) {
        require __DIR__ . '/inc/class.php';
    } else if ($status['is_requesting_class']) {
        ?>
        <section class="b-darken">
            <p>
                Vous avez demandé à rejoindre la classe <?= out($status['class_name']) ?>.<br>
                Veuillez attendre qu'un membre de la classe vous accepte.
            </p>
        </section>
        <?php
    } else {
        ?>

        <section class="b-darken">
            Cette application en ligne propose un cahier de texte collectif pour ta classe. Chaque membre peut ajouter
            des
            tâches à faire, puis tous les autres verront les devoirs à venir !
            <br>
            Tu peux rejoindre une classe existante en inscrivant ton email INSA et en cliquant sur le lien de
            vérification.
            Les membres de la classe pourront ensuite t'accepter et tu auras accès à la liste des tâches.
        </section>

        <section class="b-darken">
            <h3>Classes déjà présentes&#8239;:</h3>
            <div class="class-list">
                <?php
                $classes = getDB()->query('SELECT name FROM classes');
                $is_first = true;
                foreach ($classes as $class) {
                    echo '<p>' . out($class['name']) . '</p>';
                    $is_first = false;
                }
                ?>
            </div>
        </section>

        <section class="b-darken">
            <h3>Authentification</h3>
            <form action="<?= getRootPath() ?>agenda/" method="post">
                <?php set_csrf() ?>
                <label for="email">Email INSA&#8239;:</label><br/>
                <input type="text" name="email" id="email" required><span
                        style="font-size: 14px">@insa-lyon.fr</span><br/>
                <input type="submit" value="Envoyer l'email de vérification">
            </form>
            <?php print_errors_html($errors); ?>
        </section>
        <?php
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