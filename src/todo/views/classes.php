<?php

if (!is_logged_in()){
    header('Location: ' . getRootPath() . 'todo/');
    exit;
}
$id = $_COOKIE['id'];

$errors = array();

// Create class
if (isset($_POST['class_name'])){
    if (is_csrf_valid()){
        $q = getDB()->prepare("SELECT id FROM classes WHERE name=:name LIMIT 1");
        $q->execute([":name" => $_POST['class_name']]);
        if ($q->fetch() != null) {
            $errors[] = "Une classe avec ce nom existe déjà. Vous devriez peut-être la rejoindre.";
        }else{
            // Create class
            $q = getDB()->prepare("INSERT INTO classes (name) VALUES (:name)");
            $q->execute([":name" => $_POST['class_name']]);
            $class_id = getDB()->lastInsertId();

            // Fetch user current class
            $q = getDB()->prepare("SELECT class_id FROM users WHERE id=:id LIMIT 1");
            $q->execute([":id" => $id]);
            $row = $q->fetch();

            // Leave current class
            if ($row['class_id'] != null){
                leave_class($id, $row['class_id']);
            }

            // Update user class
            $q = getDB()->prepare("UPDATE users SET class_id=:class_id, requested_class_id=null WHERE id=:id");
            $q->execute([":class_id" => $class_id, ":id" => $id]);
            header('Location: ' . getRootPath() . 'todo/');
            exit;
        }
    }else{
        $errors[] = "Le formulaire a expiré, veuillez réessayer.";
    }
}

require '../template/head.php';
require '../template/header.php';
require '../template/footer.php';
require_once '../origin_path.php';
$title = "Liste des classes | Todo list de classe";
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
        <h3>Rejoindre une classe</h3>

        <?php
        $classes = getDB()->query('SELECT * FROM classes');

        foreach ($classes as $class) {
            echo '<a href="' . getRootPath() . 'todo/class/' . $class['id']. '/join">' . $class['name'] . '</a>&nbsp;&nbsp;';
        }
        ?>

    </section>
    <section class="b-darken">
        <h3>Créer une classe</h3>
        <form action="<?= getRootPath() ?>todo/classes" method="post">
            <?php set_csrf() ?>
            <label for="class_name">Nom de la classe&#8239;:</label><br/>
            <input type="text" name="class_name" id="class_name" minlength="2" maxlength="16" placeholder="FIMI Groupe 6"
                   value="<?= $_POST['class_name'] ?? '' ?>" required><br/>
            <input type="submit" value="Créer">
        </form>
        <?php print_errors_html($errors); ?>
    </section>
</main>
<footer>
    <?= getFooter('<a href="' . getRootPath() . 'todo/classes">Liste des classes</a>', "Clément GRENNERAT") ?>
</footer>
</body>
</html>