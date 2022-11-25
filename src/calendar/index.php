<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Modificateur de calendrier INSA Caldav</title>
    <!--    description : -->
    <meta name="description"
          content="Cette passerelle te permets de traiter ton calendrier INSA afin de renommer les évènements pour les rendre plus lisibles. Entre le lien de ton calendrier ade, puis ajoute sur zimbra ou autre le lien qui t'es donné ici."/>
    <!--    <meta name="keywords" content="insa, calculer, restaurant, ri, solde, olivier, doubler" />-->
    <!--    <link rel="icon" href="icon.png" />-->

    <meta charset="utf-8">
    <link href="../common.css" rel="stylesheet"/>
    <link href="main.css" rel="stylesheet"/>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Matomo -->
    <script>
        var _paq = window._paq = window._paq || [];
        /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function(){
            var u = "//html.pdf4teachers.org/matomo/";
            _paq.push(['setTrackerUrl', u + 'matomo.php']);
            _paq.push(['setSiteId', '3']);
            var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
            g.async = true;
            g.src = u + 'matomo.js';
            s.parentNode.insertBefore(g, s);
        })();
    </script>
    <!-- End Matomo Code -->
</head>
<body>

<header>
    <h1>Modificateur de calendrier INSA Caldav</h1>
</header>

<?php
$url = '';
if(isset($_GET['url'])){
    $url = urldecode($_GET['url']);
}
?>

<main>
    <div>
        <p class="info">
            Cette passerelle te permets de traiter ton calendrier INSA afin de renommer les évènements pour les rendre plus
            lisibles. Entre le lien de ton calendrier ade, puis ajoute sur zimbra ou autre le lien qui t'es donné ici.
            <br>
            Récupère le lien de ton calendrier ici :
            <br>
            <a href="https://ade-outils.insa-lyon.fr/ADE-iCal@2022-2023">https://ade-outils.insa-lyon.fr/ADE-iCal@2022-2023</a>.
        </p>

        <form action="./" method="get">
            <label>
                URL de votre calendrier ADE :
                <br>
                <input class="url-input" type="text" name="url" value="<?= $url ?>" placeholder="https://ade-outils.insa-lyon.fr/ADE-Cal:~jgarzer!2022-2023:459877899af69B3D">
                <br>
                <input type="submit" value="Valider">
            </label>
        </form>

        <?php
            if(isset($_GET['url'])){
                $url = 'https://insa-utils.live/calendar/get.php?url=' . $url;
            ?>
            <p>
                URL de votre calendrier convertis :<br>
                <span><?php echo '<a href="' . $url . '">' . $url . '</a>'; ?></span>
            </p>
            <?php
            }
        ?>
    </div>
</main>

<footer>
    <div>
        <p>
        </p>
    </div>
    <div>
        <p>
            Clément GRENNERAT
        </p>
    </div>
</footer>


</body>
</html>