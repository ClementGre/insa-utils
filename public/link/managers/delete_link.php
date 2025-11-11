<?php

header("HTTP/1.1 303 See Other");

$status = get_user_status();
$_SESSION['errors'] = array();
$_SESSION['infos'] = array();

if (!$status['logged_in']) {
    header('Location: ' . getRootPath() . 'link/');
    exit;
}
if (isset($_POST['r'])) {
    header('Location: ' . getRootPath() . 'link/' . $_POST['r']);
} else {
    header('Location: ' . getRootPath() . 'link/');
}

if (isset($_POST['id'])) {
    if (is_csrf_valid('js')) {

        $r = deleteLink($_POST['id'], $status['id']);
        if ($r) {
            $_SESSION['infos'][] = 'Le lien a bien été supprimé.';
        } else {
            $_SESSION['errors'][] = 'Une erreur est survenue lors de la suppression du lien.';
        }

    } else {
        $_SESSION['errors'][] = 'Le formulaire a expiré. Veuillez réessayer.';
    }
}
