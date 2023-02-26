<?php

header("HTTP/1.1 303 See Other");
header('Location: ' . getRootPath() . 'agenda/unsubscribe/');

$status = get_user_status();
$_SESSION['errors'] = array();
$_SESSION['infos'] = array();

if (!$status['logged_in']) {
    header('Location: ' . getRootPath() . 'agenda/');
    exit;
}

if (!is_csrf_valid()){
    $_SESSION['errors'][] = 'Le formulaire a expiré. Veuillez réessayer.';
    exit();
}

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'enable':
            $q = getDB()->prepare("UPDATE users SET status='normal' WHERE id=:id");
            $q->execute([":id" => $status['id']]);
            $user = $q->fetch();

            $_SESSION['infos'] = "Vous avez bien réactivé les emails.";

            break;
        case 'disable':

            $q = getDB()->prepare("SELECT status, name FROM users WHERE id=:id");
            $q->execute([":id" => $status['id']]);
            $user = $q->fetch();

            disable_user_email($status['id'], $user['name']);
            $_SESSION['infos'] = "Vous ne recevrez plus d'email d'INSA Utils.<br>Un email de réactivation vous a été envoyé.";

            break;

    }
}
