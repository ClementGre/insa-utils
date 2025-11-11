<?php
require '../template/head.php';
require '../template/header.php';
require '../template/footer.php';
require '../origin_path.php';
$title = "RestINSA";
$desc = "Le nouveau calculateur";
?>

<!DOCTYPE html>
<html lang="fr">
<head><?= getHead($title, $desc) ?></head>
<body>
<?php printHeader($title) ?>
<main class="">
    <section class="b-darken">
        Calculateur
    </section>
</main>
<footer>
    <?= getFooter("", "Clément GRENNERAT") ?>
</footer>
</body>
</html>