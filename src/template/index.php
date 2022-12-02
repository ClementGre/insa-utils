<?php
require '../template/head.php';
require '../template/header.php';
require '../template/footer.php';
require '../origin_path.php';
$title = "Calculateur de points Restaurants INSA";
$desc = "Le nouveau calculateur";
?>

<!DOCTYPE html>
<html lang="fr">
<?= getHead($title, $desc) ?>
<body>
<?= getHeader($title) ?>
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