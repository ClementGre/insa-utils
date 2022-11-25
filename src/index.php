<?php
function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if (!$length) return true;
    return substr($haystack, -$length) === $needle;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>themsVPS Services Menu</title>
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
<div class="index">
    <?php
    $urls = array();
    $data = parse_ini_file("links-to-registered-urls.ini");

    foreach ($data as $name => $data) {
        array_push($urls, $data['url']);
        echo '
          <a href="' . $data['url'] . '">
            <div class="linkdiv">';

        if (isset($data['icon'])) echo '<img src="' . $data['icon'] . '"></img>';
        else echo '<img src="https://eu.ui-avatars.com/api/?background=random&name=' . $name . '"></img>';

        echo '<div class="text">
                <h1>' . $name . '</h1>
                <p>' . $data['url'] . '</p>
              </div>
            </div>
          </a>';
    }

    foreach (scandir('./') as $dir) {
        $url = $dir;

        if (in_array($url, $urls) || substr($dir, 0, 1) == '.' || endsWith($dir, '.php') || endsWith($dir, '.ini') || endsWith($dir, '.css') || endsWith($dir, '.html')) continue;

        echo '
          <a href="' . $url . '">
            <div class="linkdiv">
              <img src="https://eu.ui-avatars.com/api/?background=random&name=' . $dir . '"></img>
              <div class="text">
                <h1>' . $dir . '</h1>
                <p>' . $url . '</p>
              </div>
            </div>
          </a>';
    }

    ?>

</div>
</body>
</html>
