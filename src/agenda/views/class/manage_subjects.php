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
        case 'edit_subject':
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
        case 'delete_subject':

            break;
        case 'add_subject':

            break;

    }
}
header('Location: ' . getRootPath() . 'agenda/subjects');