<?php
$status = get_user_status();

if (!$status['logged_in'] || !$status['is_in_class']) {
    header('Location: ' . getRootPath() . 'agenda/');
    exit;
}

$title = "Demandes d'ajout";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    include __DIR__ . '/../inc/head.php';
    ?>
    <link href="<?= getRootPath() ?>agenda/css/requests.css" rel="stylesheet"/>
</head>
<body>
<?php include __DIR__ . '/../inc/header.php' ?>
<main>

    <?php
    if (isset($_SESSION['errors'])) {
        print_errors_html($_SESSION['errors']);
        $_SESSION['errors'] = array();
    }
    if (isset($_SESSION['infos'])) {
        print_infos_html($_SESSION['infos']);
        $_SESSION['infos'] = array();
    }
    ?>

    <h3>Utilisateurs souhaitant rejoindre votre classe :</h3>
    <?php
    $q = getDB()->prepare('SELECT * FROM users WHERE requested_class_id = :requested_class_id AND class_id IS NULL');
    $q->execute(['requested_class_id' => $status['class_id']]);
    if ($q->rowCount() == 0) {
        echo '<section class="b-darken"><p>Aucune demande d\'ajout n\'a été faite.</p></section>';
    } else {
        $users = $q->fetchAll();
        gen_csrf_key();
        foreach ($users as $user) {
            ?>
            <form class="user" method="post" action="<?= getRootPath() ?>agenda/manage/requests">
                <?php set_csrf_without_regen(); ?>
                <input type="hidden" name="id" value="<?= $user['id'] ?>"/>
                <p class="user-name">
                    <?= $user['name'] ?>
                </p>
                <div class="buttons">
                    <button type="submit" name="action" value="accept_user">Accepter</button>
                    <button type="submit" name="action" value="reject_user">Refuser</button>
                </div>
            </form>
            <?php
        }
    }
    ?>
    <h3>Membre de la classe :</h3>
    <?php
    $q = getDB()->prepare('SELECT * FROM users WHERE class_id = :class_id');
    $q->execute(['class_id' => $status['class_id']]);
    $users = $q->fetchAll();
    foreach ($users as $user) {
        ?>
        <div class="user">
            <p class="user-name">
                <?= $user['name'] ?>
            </p>
        </div>
        <?php
    }
    ?>

</main>
<footer>
    <?= getFooter('<a href="' . getRootPath() . 'agenda/">Tâches à venir</a>', "Clément GRENNERAT") ?>
</footer>
</body>
<script src="<?= getRootPath() ?>agenda/js/main.js"></script>
</html>