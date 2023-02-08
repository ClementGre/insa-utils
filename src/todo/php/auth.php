<?php
function request_login($email): array
{
    require_once __DIR__ . '/../mailing/mailer.php';

    $errors = array();
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
            $errors[] = "Vous avez choisi de ne plus recevoir d'emails de la part d'insa-utils. Vous pouvez réactiver les emails en allant sur le mail de désabonnement.";
        } else if ($row['status'] == 'banned') {
            $errors[] = "Vous êtes bannis de insa-utils.";
        } else {

            $q = getDB()->prepare("UPDATE users SET email_token=:email_token, email_code=:email_code, email_date=NOW() WHERE id=:id");
            $q->execute([
                ":email_token" => $email_token,
                ":email_code" => $email_code,
                ":id" => $id
            ]);
        }
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

        setcookie('id', $id, time() + 60 * 60 * 24 * 365 * 5, '/');
        setcookie('email_token', $email_token, time() + 60 * 60 * 24 * 365 * 5, '/');
        header('Location: ' . getRootPath() . 'todo/auth');
        exit;

    } catch (Exception $e) {
        $errors[] = "Une erreur est survenue lors de l'envoi de l'email : " . $e->getMessage();
    }
    return $errors;
}

function try_login($id, $email_token, $email_code, $code_messages): array
{

    $q = getDB()->prepare("SELECT email_token, email_code, auth_token, login_trials, email_date, auth_banned_date FROM users WHERE id=:id LIMIT 1");
    $q->execute([":id" => $id]);
    if($row = $q->fetch()){
        $auth_token = $row['auth_token'];
        $email_token_db = $row['email_token'];
        $email_code_db = $row['email_code'];
        $login_trials = $row['login_trials'];
        $email_date = $row['email_date'];
        $auth_banned_date = $row['auth_banned_date'];

        $errors = array();
        $increment_trials = true;
        if ($login_trials >= 5) {
            $errors[] = "Vous avez fait trop d'essais de connexion. Veuillez réessayer dans 1 jour.";
            $q = getDB()->prepare("UPDATE users SET auth_banned_date=NOW(), login_trials=0 WHERE id=:id");
            $q->execute([":id" => $id]);
            $increment_trials = false;

        } else if (is_login_banned($auth_banned_date)) {
            $errors[] = "Vous avez fait trop d'essais de connexion. Veuillez réessayer dans 1 jour.";
            $increment_trials = false;

        } else if ($email_token != $email_token_db) {
            $errors[] = $code_messages ? "La tentative de connexion a expiré. Veuillez réessayer" : "Le lien de connexion est invalide.";

        } else if ($email_code != $email_code_db) {
            if ($code_messages) {
                if ($login_trials == 4) {
                    $errors[] = "Code incorrect. Vous avez fait trop d'essais de connexion. Veuillez réessayer dans 1 jour.";
                    $q = getDB()->prepare("UPDATE users SET auth_banned_date=NOW(), login_trials=0 WHERE id=:id");
                    $q->execute([":id" => $id]);
                    $increment_trials = false;
                } else {
                    $errors[] = "Code incorrect " . (4 - $login_trials) . " tentatives restantes.";
                }
            } else {
                $errors[] = "Le lien de connexion est invalide.";
            }
        } else if (!is_email_date_valid($email_date)) {
            $errors[] = "Le lien de connexion a expiré. Veuillez réessayer.";

        } else {
            // Login success
            $q = getDB()->prepare("UPDATE users SET login_trials=0, email_token=null, email_code=null WHERE id=:id");
            $q->execute([":id" => $id]);

            setcookie('email_token', null, -1, '/');
            setcookie('id', $id, time() + 60 * 60 * 24 * 365 * 5, '/');
            setcookie('auth_token', $auth_token, time() + 60 * 60 * 24 * 365 * 5, '/');
            header('Location: ' . getRootPath() . 'todo/');
            exit;
        }

        if ($increment_trials){
            $q = getDB()->prepare("UPDATE users SET login_trials=login_trials+1 WHERE id=:id");
            $q->execute([":id" => $id]);
        }
    }else{
        $errors[] = "Utilisateur non existant, veuillez réessayer de vous authentifier.";
    }

    return $errors;
}

function is_login_banned($auth_banned_date): bool
{
    return timestampDiffMn($auth_banned_date) < 60 * 24;
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