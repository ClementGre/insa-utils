<?php
require 'template/utils.php';
require 'template/head.php';
require 'template/header.php';
require 'template/footer.php';
require 'origin_path.php';
$title = "Utilitaires INSA - Menu principal";
$desc = "LE site permettant de te faciliter la vie à l'INSA, via divers services utiles.";
?>

<!DOCTYPE html>
<html lang="fr">
<?= getHead($title, $desc) ?>
<body>
<?= getSimpleHeader($title) ?>
<main>
        <?php
        $urls = array();
        $data = parse_ini_file("links-to-registered-urls.ini");

        foreach ($data as $name => $data) {
            array_push($urls, $data['url']);

            if(isset($data['hidden']) && $data['hidden']) continue;

            echo '<section>
          <a href="' . $data['url'] . '">
            <div class="link-div">';

            if(isset($data['icon'])) echo '<img src="' . $data['icon'] . '" alt="Logo du service ' . $name . '"/>';
            else echo '<img src="https://eu.ui-avatars.com/api/?background=random&name=' . $name . '" alt="Image contenant les deux premières lettres du nom du service ' . $name . '"/>';

            $desc = isset($data['desc']) ? '<h2>' . $data['desc'] . '</h2>' : '';

            echo '<div class="text">
                    <h1>' . $name . '</h1>
                    ' . $desc . '
                    <p>' . $data['url'] . '</p>
                  </div>
                </div>
              </a>
            </section>';
        }

        foreach (scandir('./') as $dir) {
            $url = $dir;

            if (in_array($url, $urls) || substr($dir, 0, 1) == '.' || endsWith($dir, '.php') || endsWith($dir, '.ini') || endsWith($dir, '.css') || endsWith($dir, '.html')) continue;

            echo '
            <section>
              <a href="' . $url . '">
                <div class="link-div">
                  <img src="https://eu.ui-avatars.com/api/?background=random&name=' . $dir . '" alt="Image contenant les deux premières lettres du nom du service ' . $dir . '"/>
                  <div class="text">
                    <h1>' . $dir . '</h1>
                    <p>' . $url . '</p>
                  </div>
                </div>
              </a>
            </section>';
        }

        ?>
</main>
<footer>
    <?= getFooter("", "Clément GRENNERAT") ?>
</footer>
</body>
</html>