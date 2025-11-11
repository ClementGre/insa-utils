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

if (isset($_POST['id']) && isset($_POST['title']) && isset($_POST['description']) && isset($_POST['expiration_date']) && isset($_POST['url'])) {
    if (is_csrf_valid('js')) {

        // Check title
        if (mb_strlen($_POST['title'], "UTF-16BE") > 50) {
            $_SESSION['errors'][] = 'Le titre est trop long.';
            exit();
        }
        // Check description
        if (mb_strlen($_POST['description'], "UTF-16BE") > 1000) {
            $_SESSION['errors'][] = 'La description est trop longue.';
            exit();
        }
        // Check expiration date
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $_POST['expiration_date'])) {
            $_POST['expiration_date'] = null;
        }

        // Check url
        if (!filter_var($_POST['url'], FILTER_VALIDATE_URL)) {
            $_SESSION['errors'][] = 'Le lien n\'est pas valide.';
            exit();
        }
        if(mb_strlen($_POST['url'], "UTF-16BE") > 2048){
            $_SESSION['errors'][] = 'Le lien est trop long.';
            exit();
        }

        $r = editLink($status['id'], $_POST['id'], $_POST['expiration_date'], $_POST['title'], $_POST['description'], $_POST['url']);
        if ($r) {
            $_SESSION['infos'][] = 'Le lien a bien été modifié.';
        } else {
            $_SESSION['errors'][] = 'Une erreur est survenue lors de la modification du lien.';
        }

    } else {
        $_SESSION['errors'][] = 'Le formulaire a expiré. Veuillez réessayer.';
    }
}
