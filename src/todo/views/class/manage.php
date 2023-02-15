<?php

$status = get_user_status();
$_SESSION['errors'] = array();

if ($status['logged_in'] && $status['class_id'] == null) {
    header('Location: ' . getRootPath() . 'todo/classes');
    exit;
}

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
                    $_SESSION['errors'][] = 'Le formulaire a expiré. Veuillez réessayer.';
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
                    $_SESSION['errors'][] = 'Le formulaire a expiré. Veuillez réessayer.';
                }
            }
            break;
        case 'edit':
            if (is_csrf_valid('js')) {

            } else {
                $_SESSION['errors'][] = 'Le formulaire a expiré. Veuillez réessayer.';
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
                    $_SESSION['errors'][] = 'Le formulaire a expiré. Veuillez réessayer.';
                }
            }
            break;
    }
}


header("HTTP/1.1 303 See Other");
header('Location: ' . getRootPath() . 'todo/');
