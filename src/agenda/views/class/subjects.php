<?php
$errors = array();
$status = get_user_status();

if (!$status['is_in_class']) {
    header('Location: ' . getRootPath() . 'agenda/');
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

$title = "Matières | Todo list de classe";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    include __DIR__ . '/../inc/head.php';
    ?>
    <link href="<?= getRootPath() ?>agenda/css/subjects.css" rel="stylesheet"/>
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
                <form method="post" action="<?= getRootPath() ?>agenda/subjects/">
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
                            <img src="<?= getRootPath() ?>agenda/svg/delete.svg" alt="Supprimer">
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
        <form method="post" action="<?= getRootPath() ?>agenda/subjects/">
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
                <?php
                foreach (SubjectColor::cases() as $color) {
                    ?>
                    <input type="radio" name="color" value="<?= strtolower($color->name) ?>"
                           style="background-color: <?= $color->value ?>;" required>
                    <?php
                }
                ?>
            </div>
        </form>
    </section>

</main>
<footer>
    <?= getFooter('<a href="' . getRootPath() . 'agenda/">Tâches à venir</a>', "Clément GRENNERAT") ?>
</footer>
</body>
<script src="<?= getRootPath() ?>agenda/js/main.js"></script>
</html>