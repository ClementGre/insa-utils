<?php
$status = $status ?? null;
$to_do = array();
$done = array();

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            if (isset($_POST['subject_id']) && isset($_POST['duedate']) && isset($_POST['type']) && $_POST['content'] && isset($_POST['link']) && isset($_POST['visibility'])) {
                if (is_csrf_valid()) {
                    $q = getDB()->prepare('INSERT INTO todos (class_id, creator_id, is_private, subject_id, type, duedate, content, link) VALUES (:class_id, :creator_id, :is_private, :subject_id, :type, :duedate, :content, :link)');
                    $r = $q->execute([
                        ':class_id' => $status['class_id'],
                        ':creator_id' => $status['id'],
                        ':is_private' => $_POST['visibility'] === 'private' ? 1 : 0,
                        ':subject_id' => $_POST['subject_id'],
                        ':type' => $_POST['type'],
                        ':duedate' => $_POST['duedate'],
                        ':content' => $_POST['content'],
                        ':link' => $_POST['link']
                    ]);
                } else {
                    $errors[] = 'Le formulaire a expiré. Veuillez réessayer.';
                }
            }
            break;
        case 'delete':
            if (isset($_POST['id'])) {
                if (is_csrf_valid('js')) {
                    $q = getDB()->prepare('DELETE FROM todos WHERE id=:id AND class_id=:class_id');
                    $r = $q->execute([
                        ':id' => $_POST['id'],
                        ':class_id' => $status['class_id']
                    ]);
                } else {
                    $errors[] = 'Le formulaire a expiré. Veuillez réessayer.';
                }
            }
            break;
        case 'edit':
            if (is_csrf_valid('js')) {

            } else {
                $errors[] = 'Le formulaire a expiré. Veuillez réessayer.';
            }
            break;
        case 'make_public':
            if (isset($_POST['id'])) {
                if (is_csrf_valid('js')) {
                    $q = getDB()->prepare('UPDATE todos SET is_private=0 WHERE id=:id AND class_id=:class_id AND creator_id=:creator_id');
                    $r = $q->execute([
                        ':id' => $_POST['id'],
                        ':class_id' => $status['class_id'],
                        ':creator_id' => $status['id']
                    ]);
                } else {
                    $errors[] = 'Le formulaire a expiré. Veuillez réessayer.';
                }
            }
            break;
    }
}


$q = getDB()->prepare("SELECT * FROM todos WHERE class_id=:class_id AND duedate >= :min_date AND (creator_id = :creator_id OR is_private = 0) ORDER BY duedate DESC");
$q->execute([
    'class_id' => $status['class_id'],
    'min_date' => date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 30), // 30 days ago
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
function print_todo(array $todos, $subjects): void
{
    foreach ($todos as $todo) {
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
        <div class="todo" data-todo-id="<?= $todo['id'] ?>">
            <div class="heading">
                <div class="subject">
                    <p style="background-color: <?= $subject_color ?>;"><?= out($subject_name) ?></p>
                </div>
                <div class="duedate">
                    <p><?= duedate_to_str($todo['duedate']) ?></p>
                </div>
                <div class="status <?= $todo['status']->toCSSClass() ?> <?= $todo['type'] ?>">
                    <p><?= $todo['type'] == 'reminder' ? 'Pense bête' : $todo['status']->value ?></p>
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
                                    <br><p>Modifié en dernier par <?= $todo['last_editor'] ?></p>
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

<?php print_errors_html($errors) ?>

<h3>À faire :</h3>
<div class="todo-list">
    <?php
    print_todo($to_do, $subjects);
    ?>
</div>

<h3>Ajouter :</h3>
<div class="todo-list">
    <form class="todo" method="post" action="<?= getRootPath() ?>todo/">
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
<h3>Fait :</h3>
<div class="todo-list">
    <?php
    print_todo($done, $subjects);
    ?>
</div>