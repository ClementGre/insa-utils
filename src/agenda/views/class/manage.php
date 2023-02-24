<?php

header("HTTP/1.1 303 See Other");

$status = get_user_status();
$_SESSION['errors'] = array();
$_SESSION['infos'] = array();

if (!$status['is_in_class']) {
    header('Location: ' . getRootPath() . 'agenda/');
    exit;
}

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            if (isset($_POST['subject_id']) && isset($_POST['duedate']) && isset($_POST['type']) && $_POST['content'] && isset($_POST['link']) && isset($_POST['visibility'])) {
                if (is_csrf_valid()) {

                    if(strlen($_POST['link']) > 0){
                        if(!filter_var($_POST['link'], FILTER_VALIDATE_URL)){
                            $_SESSION['errors'][] = 'Le lien n\'est pas valide.';
                            break;
                        }
                    }
                    if(mb_strlen($_POST['link'], "UTF-16BE") > 2048){
                        $_SESSION['errors'][] = 'Le lien est trop long.';
                        break;
                    }
                    if(mb_strlen($_POST['content'], "UTF-16BE") > 3000) {
                        $_SESSION['errors'][] = 'Le contenu est trop long.';
                        break;
                    }


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
                if (isset($_POST['id']) && isset($_POST['subject_id']) && isset($_POST['duedate']) && isset($_POST['type']) && $_POST['content'] && isset($_POST['link'])) {
                    $q = getDB()->prepare('UPDATE todos SET subject_id=:subject_id, type=:type, duedate=:duedate, content=:content, link=:link, last_editor_id=:user_id WHERE id=:id AND class_id=:class_id');
                    $r = $q->execute([
                        ':id' => $_POST['id'],
                        ':class_id' => $status['class_id'],
                        ':subject_id' => $_POST['subject_id'],
                        ':type' => $_POST['type'],
                        ':duedate' => $_POST['duedate'],
                        ':content' => $_POST['content'],
                        ':link' => $_POST['link'],
                        ':user_id' => $status['id']
                    ]);
                }
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
        case 'accept_user':
            if (isset($_POST['id'])) {
                if (is_csrf_valid()) {
                    $q = getDB()->prepare('UPDATE users SET class_id=requested_class_id, requested_class_id=NULL WHERE id=:id');
                    $r = $q->execute([
                        ':id' => $_POST['id']
                    ]);
                    $_SESSION['infos'][] = 'La demande a bien été acceptée.';
                } else {
                    $_SESSION['errors'][] = 'Le formulaire a expiré. Veuillez réessayer.';
                }
                header('Location: ' . getRootPath() . 'agenda/requests');
                exit();
            }
            break;
        case 'reject_user':
            if (isset($_POST['id'])) {
                if (is_csrf_valid()) {
                    $q = getDB()->prepare('UPDATE users SET requested_class_id=NULL WHERE id=:id');
                    $r = $q->execute([
                        ':id' => $_POST['id']
                    ]);
                    $_SESSION['infos'][] = 'La demande a bien été rejetée.';
                } else {
                    $_SESSION['errors'][] = 'Le formulaire a expiré. Veuillez réessayer.';
                }
                header('Location: ' . getRootPath() . 'agenda/requests');
                exit();
            }
            break;

    }
}
header('Location: ' . getRootPath() . 'agenda/');
