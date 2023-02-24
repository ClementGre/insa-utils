<?php

header("HTTP/1.1 303 See Other");
header('Location: ' . getRootPath() . 'agenda/requests/');

$status = get_user_status();
$_SESSION['errors'] = array();
$_SESSION['infos'] = array();

if (!$status['is_in_class']) {
    header('Location: ' . getRootPath() . 'agenda/');
    exit;
}

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
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
            }
            break;

    }
}
