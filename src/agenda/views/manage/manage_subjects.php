<?php


$status = get_user_status();
$_SESSION['errors'] = array();
$_SESSION['infos'] = array();

if (!$status['is_in_class']) {
    header("HTTP/1.1 303 See Other");
    header('Location: ' . getRootPath() . 'agenda/');
    exit;
}

if (!is_csrf_valid()) {
    $_SESSION['errors'][] = 'Le formulaire a expiré. Veuillez réessayer.';
    exit();
}

header("HTTP/1.1 303 See Other");

if (isset($_POST['r'])){
    header('Location: ' . getRootPath() . 'agenda/' . $_POST['r']);
} else {
    header('Location: ' . getRootPath() . 'agenda/subjects');
}

require_once __DIR__ . '/../../php/subjects.php';
$errors = array();

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'Ajouter':
            if (isset($_POST['name']) && isset($_POST['color']) && isset($_POST['type'])) {
                $errors = array_merge($errors, create_subject($_POST['name'], $_POST['color'], $_POST['type'], $status['class_id']));
            }
            break;
        case 'Modifier':
            if (isset($_POST['name']) && isset($_POST['color']) && isset($_POST['type']) && isset($_POST['id'])) {
                $errors = array_merge($errors, update_subject($_POST['id'], $_POST['name'], $_POST['color'], $_POST['type'], $status['class_id']));
            }
            break;
        case 'Supprimer':
            if (isset($_POST['name']) && isset($_POST['color']) && isset($_POST['type']) && isset($_POST['id'])) {
                $errors = array_merge($errors, delete_subject($_POST['id'], $status['class_id']));
            }
            break;
        case 'load_template':
            if (isset($_POST['name'])) {
                load_subjects_templates($status['class_id'], $_POST['name']);
            }
            break;
    }
}