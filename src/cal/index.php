<?php
require "../../vendor/autoload.php";
require '../template/head.php';
require '../template/header.php';
require '../template/footer.php';
require '../origin_path.php';
use Symfony\Component\Yaml\Yaml;

$name = "Cal'INSA";
$title = "Convertisseur de Calendrier ADE";
$desc = "Cette passerelle permet de convertir ton calendrier INSA en renommant les évènements pour les rendre plus lisibles. Entre le lien de ton calendrier ADE, puis ajoute le lien d'abonnement iCal généré à ton calendrier. À chaque actualisation, ce serveur convertira le calendrier ADE.";

$year = date('Y');
if(date('m') < 9) $year--;
$fetch_url = 'https://ade-outils.insa-lyon.fr/ADE-iCal@' . $year . '-' . $year+1;
$sample_url = 'https://ade-outils.insa-lyon.fr/ADE-Cal:~jgarzer!' . $year . '-' . $year+1 . ':459877899af69B3D';

$url = '';
if(isset($_GET['url'])) $url = urldecode($_GET['url']);
$mode = '';
if(isset($_GET['mode'])) $mode = urldecode($_GET['mode']);
$room = '';
if(isset($_GET['room'])) $room = urldecode($_GET['room']);
$count = '';
if(isset($_GET['count'])) $count = urldecode($_GET['count']);

$types = Yaml::parseFile('cal-config.yml')['event_type'];
$selected_types = [];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php printHead($name, $title, $desc, '', 'cal/icons/icon-128.png') ?>
    <link href="<?= getRootPath() ?>cal/main.css" rel="stylesheet"/>
</head>
<body>
<?php printHeader($name, $title) ?>
<main>
    <section class="b-darken">
        <p class="info">
            <?= $desc ?>
            <br>
            <br>
            Récupère le lien de ton calendrier ici&#8239;:
            <br>
            <a href="<?= $fetch_url ?>"><?= $fetch_url ?></a>.
        </p>

        <form action="./" method="get">
            <p>URL de ton calendrier ADE (lien abonnement iCal)&#8239;:</p>
            <input class="url-input" type="text" name="url" value="<?= $url ?>" placeholder="<?= $sample_url ?>">

            <p>Mode d'affichage des évènements&#8239;:</p>
            <select name="mode">
                <option value="0" <?= $mode == 0 ? 'selected' : '' ?>>Nom complet (Mathématiques, Thermodynamique...)</option>
                <option value="1" <?= $mode == 1 ? 'selected' : '' ?>>Nom court (Maths, Thermo...)</option>
                <option value="2" <?= $mode == 2 ? 'selected' : '' ?>>Nom acronyme (MA, TH...)</option>
            </select>

            <p>Types d'évènements à afficher&#8239;:</p>
            <div class="types">
                <?php
                foreach ($types as $type) {
                    if (empty($_GET) && $type['default']) {
                        $selected_types[] = $type['code'];
                    }else if(isset($_GET['type_' . $type['code']]) && $_GET['type_' . $type['code']]){
                        $selected_types[] = $type['code'];
                    }
                    ?>
                    <div>
                        <input id="type_<?= $type['code'] ?>" type="checkbox" name="type_<?= $type['code'] ?>"
                               value="true" <?= in_array($type['code'], $selected_types) ? 'checked' : '' ?>>
                        <label for="type_<?= $type['code'] ?>"><?= $type['full_name'] ?></label>
                    </div>
                    <?php
                }
                ?>
            </div>

            <p>Options&#8239;:</p>
            <div>
                <input id="room-checkbox" type="checkbox" name="room" value="true" <?= $room ? 'checked' : '' ?>>
                <label for="room-checkbox">Afficher le nom de la salle dans le nom des évènements.</label>
            </div>
            <div>
                <input id="count-checkbox" type="checkbox" name="count" value="true" <?= $count ? 'checked' : '' ?>>
                <label for="count-checkbox">Afficher le numéro du cours dans le nom.</label>
            </div>
            <br>

            <div class="submit">
                <input type="submit" value="Valider">
            </div>
        </form>

        <?php
        if(isset($_GET['url'])){
            $mode = $mode ? '&mode=true' : '';
            $room = $room ? '&room=true' : '';
            $count = $count ? '&count=true' : '';
            $url = 'https://insa-utils.fr/cal/get.php?url=' . $url . $mode . $room . $count . "&types=" . implode(',', $selected_types);
            ?>
            <p>
                URL de ton calendrier convertis (nouvel abonnement iCal) :<br>
                <span style="word-break: break-all"><?php echo '<a href="' . $url . '">' . $url . '</a>'; ?></span>
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
