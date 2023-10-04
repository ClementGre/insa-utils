<?php
require '../template/head.php';
require '../template/header.php';
require '../template/footer.php';
require '../origin_path.php';
$name = "INS'Appétit";
$title = "Menu des restaurants";
$desc = "Tiens toi au courant du menu du RI et de l'Olivier sans VPN. Il est aussi possible de recevoir une notification avec le menu avant chaque repas.";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php printHead($name, $title, $desc, '', /*'menu/icons/icon-128.png', 'menu/menu.webmanifest'*/) ?>
    <link href="<?= getRootPath() ?>menu/main.css" rel="stylesheet"/>
</head>
<body>
<?php printHeader($name, $title); ?>
<main class="" id="app">
    <section id="day-select">
        <toggle-group
                label="Jour de la semaine"
                :buttons="get_day_buttons_names()"
                v-model:selected_index="ui.selected_day_index"
        ></toggle-group>
    </section>
    <section id="rest-select">
        <toggle-group
                label="Restaurant"
                :buttons="['Olivier', 'RI:Déjeuner', 'RI:Diner']"
                :disabled_indices="disabled_rest_indices"
                v-model:selected_index="ui.selected_rest_index"
        ></toggle-group>
    </section>
    <section id="menu" v-if="data?.days?.[ui.selected_day_index]?.[time_id]?.[rest_id]" :set="menu = data?.days?.[ui.selected_day_index]?.[time_id]?.[rest_id]" v-cloak>
        <b>Plat :</b>
        <div class="plat">
            <p v-for="s in menu.plat">{{s}}</p>
        </div>
        <b>Garniture :</b>
        <div class="garniture">
            <p v-for="s in menu.garniture">{{s}}</p>
        </div>
        <b>Sauce :</b>
        <div class="sauce">
            <p v-for="s in menu.sauce">{{s}}</p>
        </div>
        <b>Entrée :</b>
        <div class="entree">
            <p v-for="s in menu.entree">{{s}}</p>
        </div>
        <b>Fromage :</b>
        <div class="fromage">
            <p v-for="s in menu.fromage">{{s}}</p>
        </div>
        <b>Déssert :</b>
        <div class="dessert">
            <p v-for="s in menu.dessert">{{s}}</p>
        </div>
    </section>
    <section id="Notification" v-cloak>
        <button>
            S'abonner aux notifications avant chaque repas
        </button>
    </section>
</main>
<footer>
    <?= getFooter('<a href="https://menu-restaurants.insa-lyon.fr" target="_blank">Page officielle</a>', "Clément GRENNERAT") ?>
</footer>
<script src="main.js" type="module"></script>
</body>
</html>
