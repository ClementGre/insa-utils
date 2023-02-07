<?php

/* When email form submitted -> redirect to next point
 * $_POST['email']
 * After email is submitted -> ask mail code
 * $_POST['id'] $_POST['email_token']
 * When flowing mail link
 * $_GET['id'] $_GET['email_token'] $_GET['email_code']
*/

$errors = array();

if(isset($_POST['email'])){
    require_once __DIR__.'/../utils.php';
    require_once __DIR__.'/../db.php';
    $email = $_POST['email'];
    $name = emailToName($email);
    $email_token = randomToken(32);
    $email_code = randomCode(4);

    // Fetch if user exists
    $q = $db->prepare("SELECT id, status FROM users WHERE name=:name LIMIT 1");
    $q->execute([":name" => $name]);
    if($row = $q->fetch()) {
        $id = $row['id'];
        // User exists
        if($row['status'] == 'email_disabled'){
            $errors[] = "Vous avez choisi de ne plus recevoir d'emails de la part d'insa-utils. Vous pouvez réactiver les emails en allant sur le mail de désabonnement.";
        }else if($row['status'] == 'banned'){
            $errors[] = "Vous êtes bannis de insa-utils.";
        }else{
            $q = $db->prepare("UPDATE users SET email_token=:email_token, email_code=:email_code, email_date=NOW() WHERE id=:id");
            $q->execute([
                ":email_token" => $email_token,
                ":email_code" => $email_code,
                ":id" => $id
            ]);
        }
    }else{
        // Create user
        $auth_token = randomToken(64);

        $q = $db->prepare("INSERT INTO users(name, email_code, email_token, email_date, auth_token) VALUES(:name, :email_code, :email_token, NOW(), :auth_token)");
        $q->execute([
            ":name" => $name,
            ":email_code" => $email_code,
            ":email_token" => $email_token,
            ":auth_token" => $auth_token
        ]);

        // Fetch id
        $q = $db->prepare("SELECT id FROM users WHERE name=:name LIMIT 1");
        $q->execute([":name" => $name]);
        $id = $q->fetch()['id'];
    }

    try {
        mail($email . "@insa-lyon.fr", "Authentification sur insa-utils",
            "Bien le bonjour,\nVeuillez suivre ce lien pour vous authentifier sur insa-utils :' .
                    '\n https://insa-utils.live/todo/auth?id=" . $id . "&email_token=" . $email_token . "&email_code=" . $email_code . "\n\n" .
            "Autrement, entrez le code suivant sur la page :" .
            $email_code .
            "\n\nCordialement,\nL'équipe d'insa-utils"
        );
    }catch (Exception $e){
        $errors[] = "Une erreur est survenue lors de l'envoi du mail : " . $e->getMessage();
    }
}

require '../template/head.php';
require '../template/header.php';
require '../template/footer.php';
require_once '../origin_path.php';
$title = "Authentification | Todo list de classe";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?= getHead($title) ?>
    <link rel="stylesheet" href="main.css">
</head>
<body>
<?= getHeader($title) ?>
<main class="">
    <section class="b-darken">
        <?= implode(" | ", $errors) ?>
    </section>

    <!--<section class="b-darken">
        <h3>Authentification</h3>
        <form action="<?php /*= getRootPath() */?>todo/auth" method="post">
            <label for="email">Email INSA&#8239;:</label><br/>
            <input type="text" name="email" id="email" required><span style="font-size: 14px">@insa-lyon.fr</span><br/>
            <input type="submit" value="Envoyer l'email de vérification">
        </form>
    </section>-->
</main>
<footer>
    <?= getFooter('<a href="'.getRootPath().'todo/classes">Liste des classes</a>', "Clément GRENNERAT") ?>
</footer>
</body>
</html>