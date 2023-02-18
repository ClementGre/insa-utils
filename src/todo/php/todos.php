<?php
function sort_todos_desc($a, $b): int
{
    return sort_todos($b, $a, false);
}
function sort_todos_asc($a, $b): int
{
    return sort_todos($b, $a, true);
}
function sort_todos($a, $b, $asc): int
{
    if ($a['duedate'] === $b['duedate']) {
        /* order :
         * - status: done
         *   - type: report
         *   - type: practice
         * - status: in_progress
         *   - type: report
         *   - type: practice
         * - status: todo
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
        // status values : todo, in_progress, done
        if ($a['status'] === TodoStatus::DONE) {
            return -1;
        }
        if ($b['status'] === TodoStatus::DONE) {
            return 1;
        }
        // status values : todo, in_progress (different)
        if ($a['status'] === TodoStatus::IN_PROGRESS) {
            return -1;
        }
        return 1;

    }
    return (($a['duedate'] > $b['duedate']) xor $asc) ? -1 : 1;
}

function dress_todo(&$todo, $user_id){
    $q2 = getDB()->prepare("SELECT status FROM status WHERE todo_id=:todo_id AND user_id=:user_id LIMIT 1");
    $q2->execute([
        'todo_id' => $todo['id'],
        'user_id' => $user_id
    ]);

    $split = preg_split("/\r\n|\n|\r/", out($todo['content']));
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
}

function print_todos(array $todos, $subjects): bool
{
    $has_printed = false;
    foreach ($todos as $todo) {
        $has_printed = true;
        $subject_name = 'Matière supprimée';
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
                <div class="side <?= empty($todo['description']) ? 'inline' : '' ?>">
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
                    <p class="first-line"><?= $todo['title'] ?></p>
                    <p><?= $todo['description'] ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    return $has_printed;
}