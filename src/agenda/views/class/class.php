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
    <div class="user-id-container" data-user-id="<?= htmlspecialchars($status['id']) ?>"></div>

<?php
print_session_messages();

$q = getDB()->prepare('SELECT COUNT(*) FROM users WHERE requested_class_id = :requested_class_id AND class_id IS NULL');
$q->execute(['requested_class_id' => $status['class_id']]);
$row = $q->fetch();
$count = $row ? $row[0] : 0;
?>



        <?php
        if ($count != 0) {
            ?>
            <div class="join-request">
                <a href="<?= getRootPath() ?>agenda/requests">
                    <?php
                    if ($count == 1)
                        echo "<p>$count personne souhaite rejoindre votre classe</p>";
                    else
                        echo "<p>$count personne souhaite rejoindre votre classe</p>";
                    ?>
                </a>
            </div>
            <?php
        }
        ?>


    <h3>À faire&#8239;:</h3>
    <ul class="todo-list">
        <?php
        if (count($to_do) == 0) {
            ?>
            <p class="no-todo">Aucune tâche pour le moment&#8239;!</p>
            <?php
        }else{
            print_todos($to_do, $subjects);
        }
        ?>
    </ul>

    <h3>Ajouter&#8239;:</h3>
    <div class="todo-list">
        <?php
        if (count($subjects) != 0) {
            ?>
            <form class="todo" method="post" action="<?= getRootPath() ?>agenda/manage/todo">
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
                        <option value="report" <?= ($_POST['type'] ?? '') === 'report' ? 'selected="selected"' : '' ?>>
                            Rendu
                        </option>
                        <option value="practice" <?= (!isset($_POST['type']) || $_POST['type'] === 'practice') ? 'selected="selected"' : '' ?>>
                            Exercice
                        </option>
                        <option value="reminder" <?= ($_POST['type'] ?? '') === 'reminder' ? 'selected="selected"' : '' ?>>
                            Pense bête
                        </option>
                    </select>
                </div>
                <div class="content">
                    <textarea name="content" rows="4"
                        placeholder="Titre&#10;Description" maxlength="3000" required><?= out($_POST['content'] ?? '') ?></textarea>
                </div>
                <div class="validate">
                    <input type="text" name="link" placeholder="Lien" value="<?= out($_POST['link'] ?? '') ?>" maxlength="2048">
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
            <?php
        } else {
            ?>
            <p class="no-todo">Pour ajouter une tâche, ajoutez d'abord des matières&#8239;:<br><a
                        href="<?= getRootPath() ?>agenda/subjects">Ajouter des matières</a></p>
            <?php
        }
        ?>
    </div>
<?php
if (count($done) != 0) {
    ?>
    <h3>Fait&#8239;:</h3>
    <ul class="todo-list">
        <?php
        print_todos($done, $subjects);
        ?>
    </ul>
    <?php
}
?>