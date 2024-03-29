<?php
session_start();
require 'template/head.php';
require 'template/header.php';
require 'template/footer.php';
require 'origin_path.php';
require '../libs/utils.php';

$title = "Utilitaires INSA";
$desc = "LE site permettant de te faciliter la vie à l'INSA, via divers services utiles.";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php printHead('', $title, $desc, '', 'icons/icon-128.png') ?>
    <link rel="manifest" href="<?= getRootPath() ?>insautils.webmanifest">
    <link href="<?= getRootPath() ?>main.css" rel="stylesheet"/>
</head>
<body>
<?php printSimpleHeader($title) ?>
<main>
        <?php
        print_session_messages();
        ?>

        <?php
        $data = parse_ini_file("links-to-registered-urls.ini");

        foreach ($data as $name => $data) {

            if(isset($data['hidden']) && $data['hidden']) continue;

            echo '
                <section>
                    <a href="' . $data['url'] . '">
                    <div class="link-div">';

                    if(isset($data['icon'])) echo '<img src="' . $data['icon'] . '" alt="Logo du service ' . $name . '"/>';
                    else echo '<img src="https://eu.ui-avatars.com/api/?background=random&name=' . $name . '" alt="Image contenant les deux premières lettres du nom du service ' . $name . '"/>';

                    $desc = isset($data['desc']) ? '<h2>' . $data['desc'] . '</h2>' : '';

                    echo '<div class="text">
                            <h1>' . $name . '</h1>
                            ' . $desc . '
                          </div>
                        </div>
                      </a>
                </section>
            ';
        }
        ?>
</main>
<footer>
    <?= getFooter("", "Clément GRENNERAT") ?>
</footer>
</body>
</html>
