<?php

use JetBrains\PhpStorm\NoReturn;

function request_login($email): string
{
    require_once __DIR__ . '/../mailing/mailer.php';

    if(str_ends_with($email, '@insa-lyon.fr')) {
        $email = substr($email, 0, -strlen('@insa-lyon.fr'));
    }
    if (strlen($email) == 0) {
        return "Une adresse INSA ne peut pas faire 0 caractères (à ma connaissance).";
    }
    if (str_contains($email, '@')) {
        return "L'adresse email doit être de la forme \"prénom.nom\" (sans le @insa-lyon.fr).";
    }
    if(substr_count($email, '.') != 1) {
        return "L'adresse email doit être de la forme \"prénom.nom\".";
    }
    if (strlen($email) > 64) {
        return "Une adresse email INSA Lyon ne peut pas dépasser 75 caractères.";
    }
    if (str_contains($email, ' ')) {
        return "L'adresse email doit être de la forme \"prénom.nom\" (sans espace).";
    }

    $email_prefix = strtolower($email);
    $name = emailToName($email_prefix);
    $email_token = randomToken(32);
    $email_code = randomCode(4);

    // Fetch if user exists
    $q = getDB()->prepare("SELECT id, status FROM users WHERE name=:name LIMIT 1");
    $q->execute([":name" => $name]);
    if ($row = $q->fetch()) {
        $id = $row['id'];
        // User exists
        if ($row['status'] == 'email_disabled') {
            return "Vous avez choisi de ne plus recevoir d'emails de la part d'insa-utils.<br>Vous pouvez réactiver les emails en allant sur le mail de désabonnement.";
        }
        if ($row['status'] == 'banned') {
            return "Vous êtes bannis de insa-utils.";
        }

        $q = getDB()->prepare("UPDATE users SET email_token=:email_token, email_code=:email_code, email_date=NOW() WHERE id=:id");
        $q->execute([
            ":email_token" => $email_token,
            ":email_code" => $email_code,
            ":id" => $id
        ]);
    } else {
        // Create user
        $auth_token = randomToken(64);

        $q = getDB()->prepare("INSERT INTO users(name, email_code, email_token, email_date, auth_token) VALUES(:name, :email_code, :email_token, NOW(), :auth_token)");
        $q->execute([
            ":name" => $name,
            ":email_code" => $email_code,
            ":email_token" => $email_token,
            ":auth_token" => $auth_token
        ]);

        // Fetch id
        $q = getDB()->prepare("SELECT id FROM users WHERE name=:name LIMIT 1");
        $q->execute([":name" => $name]);
        $id = $q->fetch()['id'];
    }

    try {
        send_auth_mail($name, $email_prefix, $id, $email_token, $email_code);

        set_cookie('id', $id);
        header('Location: ' . getRootPath() . 'agenda/auth');
        exit;

    } catch (Exception $e) {
        return "Une erreur est survenue lors de l'envoi de l'email : " . $e->getMessage();
    }
}

function try_token_login($id, $email_token): string
{

    $q = getDB()->prepare("SELECT auth_token, email_token, email_date FROM users WHERE id=:id LIMIT 1");
    $q->execute([":id" => $id]);
    if ($row = $q->fetch()) {
        $auth_token = $row['auth_token'];
        $email_token_db = $row['email_token'];
        $email_date = $row['email_date'];

        if (!is_email_date_valid($email_date)) {
            return "Le lien de connexion a expiré. Veuillez réessayer.";
        }
        if ($email_token != $email_token_db) {
            return "Le lien de connexion est invalide. Veuillez réessayer.";
        }
        do_login_user($id, $auth_token);
    }

    return "Utilisateur non existant, veuillez réessayer de vous authentifier.";
}

function try_code_login($id, $email_code): string
{

    $q = getDB()->prepare("SELECT auth_token, email_code, email_code_trials, email_date FROM users WHERE id=:id LIMIT 1");
    $q->execute([":id" => $id]);
    if ($row = $q->fetch()) {
        $auth_token = $row['auth_token'];
        $email_code_db = $row['email_code'];
        $email_code_trials = $row['email_code_trials'];
        $email_date = $row['email_date'];

        if (!is_email_date_valid($email_date)) {
            return "Le lien de connexion a expiré. Veuillez essayer à nouveau.";
        }
        if ($email_code_trials >= 5) {
            return "Vous avez échoué un trop grand nombre de fois.<br>Veuillez vous connecter via le lien présent dans le mail.";
        }
        if ($email_code != $email_code_db) {
            $q = getDB()->prepare("UPDATE users SET email_code_trials=email_code_trials+1 WHERE id=:id");
            $q->execute([":id" => $id]);
            if ($email_code_trials == 4) {
                return "Code incorrect.<br/>Vous avez échoué un trop grand nombre de fois. Veuillez vous connecter via le lien présent dans le mail.";
            }
            return "Code incorrect. Veuillez essayer à nouveau.";
        }
        do_login_user($id, $auth_token);
    }
    return "Utilisateur non existant, veuillez réessayer de vous authentifier.";
}

#[NoReturn] function do_login_user($id, $auth_token): void
{
    $q = getDB()->prepare("UPDATE users SET email_code_trials=0, email_token=null, email_code=null, email_date=null WHERE id=:id");
    $q->execute([":id" => $id]);

    set_cookie('id', $id);
    set_cookie('auth_token', $auth_token);
    header('Location: ' . getRootPath() . 'agenda/');
    exit;
}

function is_email_date_valid($email_date): bool
{
    return timestampDiffMn($email_date) < 15;
}

function is_logged_in(): bool
{
    if (isset($_COOKIE['id']) && isset($_COOKIE['auth_token'])) {
        $id = $_COOKIE['id'];
        $auth_token = $_COOKIE['auth_token'];

        $q = getDB()->prepare("SELECT auth_token FROM users WHERE id=:id LIMIT 1");
        $q->execute([":id" => $id]);
        $auth_token_db = $q->fetch()['auth_token'];

        return $auth_token == $auth_token_db;
    }
    return false;
}
function get_user_status(): array
{
    $data = [
        'id' => $_COOKIE['id'] ?? null,
        'logged_in' => false,
        'banned' => false,
        'is_in_class' => false,
        'is_requesting_class' => false,
        'class_id' => null,
        'class_name' => null,
    ];
    if (isset($_COOKIE['id']) && isset($_COOKIE['auth_token'])) {
        $id = $_COOKIE['id'];
        $auth_token = $_COOKIE['auth_token'];

        $q = getDB()->prepare("SELECT auth_token, class_id, requested_class_id, status FROM users WHERE id=:id LIMIT 1");
        $q->execute([":id" => $id]);
        $user = $q->fetch();

        if($auth_token == $user['auth_token']){
            $data['logged_in'] = true;
            if($user['status'] == 'banned'){
                $data['banned'] = true;
            }
            if($user['class_id'] != null){
                $q = getDB()->prepare("SELECT name FROM classes WHERE id=:id LIMIT 1");
                $q->execute([":id" => $user['class_id']]);
                $class = $q->fetch();
                $data['is_in_class'] = true;
                $data['class_id'] = $user['class_id'];
                $data['class_name'] = $class['name'];
            }
            else if($user['requested_class_id'] != null){
                $q = getDB()->prepare("SELECT name FROM classes WHERE id=:id LIMIT 1");
                $q->execute([":id" => $user['requested_class_id']]);
                $class = $q->fetch();
                $data['is_requesting_class'] = true;
                $data['class_id'] = $user['requested_class_id'];
                $data['class_name'] = $class['name'];
            }

        }
    }
    return $data;
}