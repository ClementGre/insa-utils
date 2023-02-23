<?php
$status = get_user_status();

if (!$status['logged_in'] || !$status['is_in_class']) {
    header('Location: ' . getRootPath() . 'agenda/');
    exit;
}

$title = "Toutes les tâches";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    include __DIR__ . '/../inc/head.php';
    echo '<link href="' . getRootPath() . 'agenda/css/todo.css" rel="stylesheet"/>';
    ?>
</head>
<body>
<?php include __DIR__ . '/../inc/header.php' ?>
<main class="">

    <?php
    require_once __DIR__ . '/../../php/todos.php';
    require_once __DIR__ . '/../../php/subjects.php';

    $todos = array();

    $q = getDB()->prepare("SELECT * FROM todos WHERE class_id=:class_id AND (is_private = 0 OR creator_id = :creator_id) ORDER BY duedate DESC");
    $q->execute([
        'class_id' => $status['class_id'],
        'creator_id' => $status['id']
    ]);

    while ($todo = $q->fetch()) {
        dress_todo($todo, $status['id']);
        $todos[] = $todo;
    }

    uasort($todos, 'sort_todos_asc');
    $subjects = get_class_subjects($status['class_id']);
    ?>

    <div class="root-path-container" data-root-path="<?= htmlspecialchars(getRootPath()) ?>"></div>
    <div class="subjects-container" data-subjects="<?= htmlspecialchars(json_encode($subjects)) ?>"></div>
    <div class="csrf-container" data-csrf="<?= htmlspecialchars(gen_csrf_key('js')) ?>"></div>
    <div class="user-id-container" data-user-id="<?= htmlspecialchars($status['id']) ?>"></div>

    <?php
    if (isset($_SESSION['errors'])) {
        print_errors_html($_SESSION['errors']);
        $_SESSION['errors'] = array();
    }
    ?>

    <h3>Toutes les tâches&#8239;:</h3>
    <ul class="todo-list">
        <?php
        if (count($todos) == 0) {
            ?>
            <p class="no-todo">Aucune tâche n'a été ajouté pour cette classe !</p>
            <?php
        }else{
            print_todos($todos, $subjects);
        }
        ?>
    </ul>

</main>
<footer>
    <?= getFooter('<a href="' . getRootPath() . 'agenda/">Tâches à venir</a>', "Clément GRENNERAT") ?>
</footer>
</body>
<script src="<?= getRootPath() ?>agenda/js/main.js"></script>
<?php if ($status['is_in_class']) echo '<script src="' . getRootPath() . 'agenda/js/todo.js""></script>' ?>
</html>