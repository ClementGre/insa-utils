<?php
$status = get_user_agenda_status();

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

    $q = getDB()->prepare("SELECT * FROM agenda_todo WHERE class_id=:class_id AND (is_private = 0 OR creator_id = :creator_id) ORDER BY duedate DESC");
    $q->execute([
        'class_id' => $status['class_id'],
        'creator_id' => $status['id']
    ]);

    $class_members_count = get_class_members_count($status['class_id']);
    while ($todo = $q->fetch()) {
        dress_todo($todo, $class_members_count, $status['id']);
        $todos[] = $todo;
    }

    uasort($todos, 'sort_todos_asc');
    $all_subjects = get_all_class_subjects($status['class_id']);
    $subjects = extract_non_deleted_subjects($all_subjects);
    ?>

    <div class="root-path-container" data-root-path="<?= htmlspecialchars(getRootPath()) ?>"></div>
    <div class="subjects-container" data-subjects="<?= htmlspecialchars(json_encode($subjects)) ?>"></div>
    <div class="csrf-container" data-csrf="<?= htmlspecialchars(gen_csrf_key('js')) ?>"></div>
    <div class="user-id-container" data-user-id="<?= htmlspecialchars($status['id']) ?>"></div>
    <div class="page-name-container" data-page-name="all"></div>

    <?php
    print_session_messages();
    ?>

    <h3>Toutes les tâches&#8239;:</h3>
    <ul class="todo-list">
        <?php
        if (count($todos) == 0) {
            ?>
            <p class="no-todo">Aucune tâche n'a été ajouté pour cette classe !</p>
            <?php
        }else{
            print_todos($todos, $all_subjects);
        }
        ?>
    </ul>

</main>
<footer>
    <?= getFooter('<a href="' . getRootPath() . 'agenda/">Tâches à venir</a>', "Clément GRENNERAT") ?>
</footer>
</body>
<script src="<?= getRootPath() ?>account/js/main.js"></script>
<?php if ($status['is_in_class']) echo '<script src="' . getRootPath() . 'agenda/js/todo.js""></script>' ?>
</html>
