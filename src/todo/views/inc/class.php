<?php
$status = $status ?? null;
$to_do = array();
$done = array();

$q = getDB()->prepare("SELECT * FROM todos WHERE class_id=:class_id AND duedate >= :min_date AND (creator_id = :creator_id OR is_private = 0) ORDER BY duedate");
$q->execute([
    'class_id' => $status['class_id'],
    'min_date' => date('Y-m-d', time()), // 30 days ago
    'creator_id' => $status['id']
]);

while ($todo = $q->fetch()) {
    $q2 = getDB()->prepare("SELECT status FROM status WHERE todo_id=:todo_id AND user_id=:user_id LIMIT 1");
    $q2->execute([
        'todo_id' => $todo['id'],
        'user_id' => $status['id']
    ]);

    $split = preg_split("/\r\n|\n|\r/", $todo['content']);
    $todo['title'] = $split[0];
    $todo['description'] = implode('<br>', array_slice($split, 1));

    $row = $q2->fetch();
    $todo['status'] = $row ? TodoStatus::fromName($row['status']) : TodoStatus::TODO;

    // Fetch user from creator_id
    $q3 = getDB()->prepare("SELECT name FROM users WHERE id=:id LIMIT 1");
    $q3->execute([":id" => $todo['creator_id']]);
    $user = $q3->fetch();
    if ($user) $todo['creator'] = $user['name'];
    else $todo['creator'] = "Utilisateur inconnu";

    // Fetch user from last_editor_id
    if (isset($todo['last_editor_id'])) {
        $q3 = getDB()->prepare("SELECT name FROM users WHERE id=:id LIMIT 1");
        $q3->execute([":id" => $todo['last_editor_id']]);
        $user = $q3->fetch();
        if ($user) $todo['last_editor'] = $user['name'];
        else $todo['last_editor'] = "Utilisateur inconnu";
    }

    if ($todo['status'] == TodoStatus::DONE) {
        $done[] = $todo;
    } else {
        $to_do[] = $todo;
    }
}

function sort_todos($a, $b): int
{
    if ($a['duedate'] === $b['duedate']) {
        /* order :
         * - status: todo
         *   - type: report
         *   - type: practice
         * - status: in_progress
         *   - type: report
         *   - type: practice
         * - type: reminder
         */

        if ($a['type'] === 'reminder') {
            return 1;
        }
        if ($b['type'] === 'reminder') {
            return -1;
        }

        if ($a['status'] === $b['status']) {
            if ($a['type'] === $b['type']) {
                return 0;
            }
            // type either report or practice
            if ($a['type'] === 'report') {
                return -1;
            }
            return 1;
        }
        // status either todo or in_progress
        if ($a['status'] === TodoStatus::TODO) {
            return 1;
        }
        return -1;

    }
    return ($a['duedate'] < $b['duedate']) ? -1 : 1;
}

uasort($to_do, 'sort_todos');

function print_todos(array $todos, $subjects): bool
{
    $has_printed = false;
    foreach ($todos as $todo) {
        $has_printed = true;
        $subject_name = '???';
        $subject_color = 'black';
        foreach ($subjects as $subject) {
            if ($subject['id'] == $todo['subject_id']) {
                $subject_name = $subject['name'];
                $subject_color = SubjectColor::fromString($subject['color'])->value;
                break;
            }
        }
        ?>
        <div class="todo <?= $todo['is_private'] === 1 ? 'private' : '' ?>" data-todo-id="<?= $todo['id'] ?>">
            <div class="heading">
                <div class="subject">
                    <p style="background-color: <?= $subject_color ?>;"><?= out($subject_name) ?></p>
                </div>
                <div class="duedate">
                    <p><?= duedate_to_str($todo['duedate']) ?></p>
                </div>
                <div class="status <?= $todo['status']->toCSSClass() ?> <?= $todo['type'] ?>">
                    <p data-todo-id="<?= $todo['id'] ?>"><?= $todo['type'] == 'reminder' ? 'Pense bête' : ($todo['status']->value . '<img src="' . getRootPath() . 'todo/svg/pointer.svg"/>') ?></p>
                </div>
            </div>
            <div class="content">
                <div class="side">
                    <a href="<?= out($todo['link']) ?>" target="_blank"
                       class="img-button link <?= $todo['link'] ? '' : 'disabled' ?>">
                        <img alt="Lien associé" src="<?= getRootPath() ?>todo/svg/link.svg"/>
                    </a>
                    <div class="img-button edit dropdown">
                        <div class="round round-1"></div>
                        <div class="round round-2"></div>
                        <div class="round round-3"></div>
                        <div class="dropdown-content">
                            <?php
                            if ($todo['is_private'] === 1) {
                                ?>
                                <a class="make-public-todo" data-todo-id="<?= $todo['id'] ?>">Rendre publique</a>
                                <?php
                            } else {
                                ?>
                                <p>Créé par <?= $todo['creator'] ?></p>
                                <?php
                                if (isset($todo['last_editor'])) {
                                    ?>
                                    <p>Modifié en dernier par <?= $todo['last_editor'] ?></p>
                                    <?php
                                }
                            }
                            ?>
                            <a class="edit-todo" data-todo-id="<?= $todo['id'] ?>"
                               data-subject-id="<?= $todo['subject_id'] ?>" data-duedate="<?= out($todo['duedate']) ?>"
                               data-type="<?= $todo['type'] ?>" data-content="<?= out($todo['content']) ?>"
                               data-link="<?= out($todo['link']) ?>">Modifier</a>
                            <a class="delete-todo" data-todo-id="<?= $todo['id'] ?>">Supprimer</a>
                        </div>
                    </div>
                </div>
                <div class="description">
                    <p class="first-line"><?= out($todo['title']) ?></p>
                    <p><?= out($todo['description']) ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    return $has_printed;
}

require_once __DIR__ . '/../../php/subjects.php';
$q = getDB()->prepare("SELECT * FROM subjects WHERE class_id = :class_id ORDER BY type, name");
$q->execute([
    'class_id' => $status['class_id']
]);
$subjects = $q->fetchAll();

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