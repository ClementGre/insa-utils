<?php
$errors = array();
$status = get_user_status();

if (!$status['is_in_class']) {
    header('Location: ' . getRootPath() . 'todo/');
    exit;
}

require_once __DIR__ . '/../../php/subjects.php';
if (isset($_POST['name']) && isset($_POST['color']) && isset($_POST['type'])) {
    if (is_csrf_valid()) {
        if (isset($_POST['id'])) {
            if(isset($_POST['action']) && $_POST['action'] == 'Supprimer') {
                $errors = array_merge($errors, delete_subject($_POST['id'], $status['class_id']));
            }else{
                $errors = array_merge($errors, update_subject($_POST['id'], $_POST['name'], $_POST['color'], $_POST['type'], $status['class_id']));

            }
        } else {
            $errors = array_merge($errors, create_subject($_POST['name'], $_POST['color'], $_POST['type'], $status['class_id']));
        }
    } else {
        $errors[] = "Le formulaire a expiré, veuillez réessayer.";
    }
}

$title = "Matières - " . $status['class_name'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    include __DIR__ . '/../inc/head.php';
    ?>
    <link href="<?= getRootPath() ?>todo/css/subjects.css" rel="stylesheet"/>
</head>
<body>
<?php include __DIR__ . '/../inc/header.php' ?>

<main>

    <?php
    print_errors_html($errors);
    ?>

    <h3>Matières</h3>
    <?php
    $q = getDB()->prepare('SELECT * FROM subjects WHERE class_id = :class_id ORDER BY type, name');
    $q->execute(['class_id' => $status['class_id']]);
    if ($q->rowCount() == 0) {
        echo '<section class="b-darken"><p>Aucune matière n\'a été ajoutée.</p></section>';
    } else {
        $subjects = $q->fetchAll();
        foreach ($subjects as $subject) {
            ?>
            <section class="b-darken">
                <form method="post" action="<?= getRootPath() ?>todo/subjects/">
                    <?php set_csrf(); ?>
                    <input type="hidden" name="id" value="<?= $subject['id'] ?>">
                    <div class="heading">
                        <input type="text" name="name" placeholder="Matière" value="<?= out($subject['name']) ?>" required>
                        <select id="type" name="type" required>
                            <option value="main" <?= $subject['type'] == 'main' ? 'selected="selected"' : '' ?>>
                                Principales
                            </option>
                            <option value="others" <?= $subject['type'] == 'others' ? 'selected="selected"' : '' ?>>
                                Autres
                            </option>
                            <option value="humas" <?= $subject['type'] == 'humas' ? 'selected="selected"' : '' ?>>
                                Humanités
                            </option>
                        </select>
                        <input type="submit" name="action" value="Modifier">
                        <button type="submit" name="action" value="Supprimer">
                            <img src="<?= getRootPath() ?>todo/svg/delete.svg" alt="Supprimer">
                        </button>
                    </div>
                    <div class="color">
                        <?php
                        foreach (SubjectColor::cases() as $color) {
                            ?>
                            <input type="radio" name="color" value="<?= strtolower($color->name) ?>"
                                   style="background-color: <?= $color->value ?>;"
                                <?= $subject['color'] === strtolower($color->name) ? 'checked="checked"' : '' ?> required>
                            <?php
                        }
                        ?>
                    </div>
                </form>
            </section>
            <?php
        }
    }
    ?>

    <h3>Ajouter une matière</h3>
    <section class="b-darken">
        <form method="post" action="<?= getRootPath() ?>todo/subjects/">
            <?php set_csrf(); ?>
            <div class="heading">
                <input type="text" name="name" placeholder="Matière" required>
                <select id="type" name="type" required>
                    <option value="main" selected="selected">Principales</option>
                    <option value="others">Autres</option>
                    <option value="humas">Humanités</option>
                </select>
                <input type="submit" value="Ajouter">
            </div>
            <div class="color">
                <input type="radio" name="color" id="color-red" value="red" style="background-color: red;" required>
                <input type="radio" name="color" id="color-red" value="orange" style="background-color: orange;"
                       required>
                <input type="radio" name="color" id="color-red" value="yellow" style="background-color: yellow;"
                       required>
                <input type="radio" name="color" id="color-red" value="green" style="background-color: green" required>
                <input type="radio" name="color" id="color-red" value="blue" style="background-color: blue" required>
                <input type="radio" name="color" id="color-red" value="maroon" style="background-color: maroon;"
                       required>
                <input type="radio" name="color" id="color-red" value="gray" style="background-color: gray" required>
                <input type="radio" name="color" id="color-red" value="lightgray" style="background-color: lightgray;"
                       required>
                <input type="radio" name="color" id="color-red" value="pink" style="background-color: pink;" required>
                <input type="radio" name="color" id="color-red" value="purple" style="background-color: purple;"
                       required>
            </div>
        </form>
    </section>

</main>
<footer>
    <?= getFooter('<a href="' . getRootPath() . 'todo/classes">Liste des classes</a>', "Clément GRENNERAT") ?>
</footer>
</body>
<script src="<?= getRootPath() ?>todo/js/main.js"></script>
</html>