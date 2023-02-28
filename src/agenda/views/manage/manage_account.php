<?php

$status = get_user_status();
$_SESSION['errors'] = array();
$_SESSION['infos'] = array();

if (!$status['logged_in']) {
    header("HTTP/1.1 303 See Other");
    header('Location: ' . getRootPath() . 'agenda/');
    exit;
}

if (!is_csrf_valid()) {
    $_SESSION['errors'][] = 'Le formulaire a expiré. Veuillez réessayer.';
    exit();
}

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'disconnect':
            remove_cookie('id');
            remove_cookie('auth_token');
            header("HTTP/1.1 303 See Other");
            header('Location: ' . getRootPath() . 'agenda/');

            exit;
        case 'disconnect_all':
            $auth_token = randomToken(64);
            $q = getDB()->prepare("UPDATE users SET auth_token=:auth_token WHERE id=:id");
            $q->execute([
                'auth_token' => $auth_token,
                'id' => $status['id']
            ]);
            set_cookie('auth_token', $auth_token);
            $_SESSION['infos'][] = "Vous avez été déconnecté de tous vos autres appareils";

            break;
        case 'download_data':
            require_once __DIR__ . '/../../php/download_data.php';
            write_user_data_to_csv_output();

            exit;
        case 'delete_account':
            $q = getDB()->prepare("DELETE FROM users WHERE id=:id");
            $q->execute([":id" => $status['id']]);

            $q = getDB()->prepare("DELETE FROM status WHERE user_id=:id");
            $q->execute([":id" => $status['id']]);

            $q = getDB()->prepare("DELETE FROM todos WHERE creator_id=:id AND is_private=1");
            $q->execute([":id" => $status['id']]);

            $_SESSION['infos'] = array();
            $_SESSION['infos'][] = "Votre compte a bien été supprimé";
            remove_cookie('id');
            remove_cookie('auth_token');
            header("HTTP/1.1 303 See Other");
            header('Location: ' . getRootPath() . 'agenda/');

            break;
    }
}
header("HTTP/1.1 303 See Other");
header('Location: ' . getRootPath() . 'agenda/account');
