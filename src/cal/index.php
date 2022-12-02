<?php
require '../template/head.php';
require '../template/header.php';
require '../template/footer.php';
require '../origin_path.php';
$title = "Convertisseur de Calendrier ADE";
$desc = "Cette passerelle permet de convertir ton calendrier INSA en renommant les évènements pour les rendre plus lisibles. Entre le lien de ton calendrier ADE, puis ajoute sur ton hébergeur de calendrier préféré le lien d'abonnement iCal généré. À chaque actualisation, ce serveur convertira le calendrier ADE.";

$url = '';
if(isset($_GET['url'])) $url = urldecode($_GET['url']);

?>

<!DOCTYPE html>
<html lang="fr">
<?= getHead($title, $desc) ?>
<body>
<?= getHeader($title) ?>
<main class="">
    <section class="b-darken">
        <p class="info">
            <?= $desc ?>
            <br>
            <br>
            Récupère le lien de ton calendrier ici :
            <br>
            <a href="https://ade-outils.insa-lyon.fr/ADE-iCal@2022-2023">https://ade-outils.insa-lyon.fr/ADE-iCal@2022-2023</a>.
        </p>

        <form action="./" method="get">
            <label>
                URL de ton calendrier ADE (lien abonnement iCal) :
                <br>
                <input class="url-input" type="text" name="url" value="<?= $url ?>" placeholder="https://ade-outils.insa-lyon.fr/ADE-Cal:~jgarzer!2022-2023:459877899af69B3D">
                <br>
                <br>
                <input type="submit" value="Valider">
            </label>
        </form>

        <?php
        if(isset($_GET['url'])){
            $url = 'https://insa-utils.live/cal/get.php?url=' . $url;
            ?>
            <p>
                URL de ton calendrier convertis (nouveau abonnement iCal) :<br>
                <span><?php echo '<a href="' . $url . '">' . $url . '</a>'; ?></span>
            </p>
            <?php
        }
        ?>
    </section>
</main>
<footer>
    <?= getFooter("", "Clément GRENNERAT") ?>
</footer>
</body>
</html>