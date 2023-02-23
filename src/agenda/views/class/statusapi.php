<?php
header('Content-Type: application/json');
$json = json_decode(file_get_contents('php://input'), true);
$todo_id = $json['todo_id'];
$user_id = $json['user_id'];
$csrf_js = $json['csrf_js'];

$output = [];

if ($_SESSION["csrf_js"] === $csrf_js) {
    $q = getDB()->prepare('SELECT status FROM status WHERE user_id=:user_id AND todo_id=:todo_id');
    $r = $q->execute([
        ':user_id' => $user_id,
        ':todo_id' => $todo_id
    ]);
    $status = 'todo';
    if($row = $q->fetch()){
        $status = $row['status'];

        $q = getDB()->prepare('DELETE FROM status WHERE user_id=:user_id AND todo_id=:todo_id');
        $r = $q->execute([
            ':user_id' => $user_id,
            ':todo_id' => $todo_id
        ]);
    }

    $map = [
        'todo' => 'in_progress',
        'in_progress' => 'done',
        'done' => 'todo'
    ];
    $new_status = $map[$status];

    $q = getDB()->prepare('INSERT INTO status (user_id, todo_id, status) VALUES (:user_id, :todo_id, :status)');
    $r = $q->execute([
        ':user_id' => $user_id,
        ':todo_id' => $todo_id,
        ':status' => $new_status
    ]);

    $out['status'] = 'done';
} else {
    $out['status'] = 'error';
    $out['error'] = 'Le formulaire a expiré. Veuillez réessayer.';
}

echo json_encode($out);

