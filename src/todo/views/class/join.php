<?php

$status = get_user_status();

if (!$status['logged_in']) {
    header('Location: ' . getRootPath() . 'todo/');
    exit;
}

// Get class passed in URL
$class_id = $class ?? null;
$q = getDB()->prepare("SELECT name FROM classes WHERE id=:id LIMIT 1");
$q->execute([":id" => $class_id]);
$row = $q->fetch();
if($row == null){
    header('Location: ' . getRootPath() . 'todo/classes');
    exit;
}
$class_name = $row['name'];

// Update user requested_class if not already in class or if confined via form csrf token
if(!$status['is_in_class'] || is_csrf_valid()){
    if ($status['is_in_class']){
        leave_class($status['id'], $status['class_id']);
    }
    $q = getDB()->prepare("UPDATE users SET requested_class_id=:class_id WHERE id=:id");
    $q->execute([":class_id" => $class_id, ":id" => $status['id']]);
    header('Location: ' . getRootPath() . 'todo/');
    exit;
}

$errors = array();

require '../template/head.php';
require '../template/header.php';
require '../template/footer.php';
require_once '../origin_path.php';
$title = "Rejoindre une classe | Todo list de classe";
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
        <h3>Rejoindre la classe <?= $class_name ?></h3>

        <p>Vous êtes déjà dans une classe.<br>Êtes-vous sûr de vouloir la quitter pour rejoindre <?= $class_name ?></p>
        <form action="" method="post">
            <?php set_csrf() ?>
            <input type="submit" value="Rejoindre <?= $class_name ?>">
        </form>

    </section>
</main>
<footer>
    <?= getFooter('<a href="' . getRootPath() . 'todo/classes">Liste des classes</a>', "Clément GRENNERAT") ?>
</footer>
</body>
</html>