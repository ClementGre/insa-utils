<?php
$status = $status ?? null;

require_once __DIR__ . '/../../php/todos.php';
require_once __DIR__ . '/../../php/subjects.php';

$to_do = array();
$done = array();

$q = getDB()->prepare("SELECT * FROM todos WHERE class_id=:class_id AND duedate >= :min_date AND (is_private = 0 OR creator_id = :creator_id) ORDER BY duedate");
$q->execute([
    'class_id' => $status['class_id'],
    'min_date' => date('Y-m-d', time()), // 30 days ago
    'creator_id' => $status['id']
]);

while ($todo = $q->fetch()) {
    dress_todo($todo, $status['id']);
    if ($todo['status'] == TodoStatus::DONE) {
        $done[] = $todo;
    } else {
        $to_do[] = $todo;
    }
}

uasort($to_do, 'sort_todos_desc');
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

<h3>À faire :</h3>
<div class="todo-list">
    <?php
    if(!print_todos($to_do, $subjects)){
        ?>
        <p class="no-todo">Aucune tâche pour le moment !</p>
        <?php
    }
    ?>
</div>

<h3>Ajouter :</h3>
<div class="todo-list">
    <form class="todo" method="post" action="<?= getRootPath() ?>todo/manage">
        <?php set_csrf() ?>
        <input type="hidden" name="action" value="add"/>
        <div class="heading">
            <select id="subject" name="subject_id" required onchange="onSubjectComboChange(event);">
                <?php
                foreach ($subjects as $subject) {
                    ?>
                    <option value="<?= $subject['id'] ?>" <?= (($_POST['subject_id'] ?? '') === $subject['id']) ? 'selected="selected"' : '' ?>><?= out($subject['name']) ?></option>
                    <?php
                }
                ?>
                <option value="manage">Gérer les matières</option>
            </select>
            <input type="date" id="duedate" name="duedate"
                   value="<?= $_POST['duedate'] ?? date_in_a_week() ?>" min="<?= current_date() ?>"
                   max="<?= year() . '-06-30' ?>" required>
            <select class="fixed" id="type" name="type" required>
                <option value="report" <?= ($_POST['type'] ?? '') === 'report' ? 'selected="selected"' : '' ?>>Rendu
                </option>
                <option value="practice" <?= (!isset($_POST['type']) || $_POST['type'] === 'practice') ? 'selected="selected"' : '' ?>>
                    Exercice
                </option>
                <option value="reminder" <?= ($_POST['type'] ?? '') === 'reminder' ? 'selected="selected"' : '' ?>>Pense
                    bête
                </option>
            </select>
        </div>
        <div class="content">
            <textarea name="content" rows="4"
                      placeholder="Titre&#10;Description"><?= out($_POST['content'] ?? '') ?></textarea>
        </div>
        <div class="validate">
            <input type="text" name="link" placeholder="Lien" value="<?= out($_POST['link'] ?? '') ?>">
            <select class="fixed" name="visibility" required>
                <option value="public" <?= (!isset($_POST['visibility']) || $_POST['visibility'] === 'public') ? 'selected="selected"' : '' ?>>
                    Publique
                </option>
                <option value="private" <?= (($_POST['visibility'] ?? '') === 'private') ? 'selected="selected"' : '' ?>>
                    Privé
                </option>
            </select>
            <input class="fixed" type="submit" name="submit" value="Ajouter">
        </div>
    </form>
</div>
<?php
if (count($done) > 0) {
    ?>
    <h3>Fait :</h3>
    <div class="todo-list">
        <?php
        print_todos($done, $subjects);
        ?>
    </div>
    <?php
}
?>