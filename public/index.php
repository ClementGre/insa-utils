<?php
session_start();
require 'template/head.php';
require 'template/header.php';
require 'template/footer.php';
require 'origin_path.php';
require '../libs/utils.php';

$title = "INSA Utils";
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
        // If date is < than summer 2026
        if (date('Y-m-d') < '2026-06-01') {
            echo '
            <section id="special-section">
                <div class="link-div">
                    <span class="corner corner-tl">🚀</span>
                    <span class="corner corner-tr">🎉</span>
                    <span class="corner corner-bl">🌟</span>
                    <span class="corner corner-br">💫</span>
                    <div class="text">
                        <h2><code>insa-utils.fr</code> devient<br><code>utils.bde-insa-lyon.fr</code></h2>
                        <p class="highlight-msg">
                            Pour garantir la pérennité du service, INSA Utils a été repris par le BdE INSA Lyon.
                        </p>
                    </div>
                </div>
            </section>
            <style>
                
            </style>
            ';
        }
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
