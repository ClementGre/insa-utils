<?php
require '../template/head.php';
require '../template/header.php';
require '../template/footer.php';
require '../origin_path.php';
require '../../libs/router.php';
$name = "INS'Appétit";
$title = "Menu des restaurants";
$desc = "Tiens toi au courant du menu du RI et de l'Olivier sans VPN. Il est aussi possible de recevoir une notification avec le menu avant chaque repas.";

$bde = isset($_GET['bde']);
?>

<!DOCTYPE html>
<html lang="fr" class="<?= $bde ? 'bde' : '' ?>">
<head>
    <?php printHead($name, $title, $desc, '', 'menu/icons/icon-128.png', 'menu/menu.webmanifest') ?>
    <link href="<?= getRootPath() ?>menu/main.css" rel="stylesheet"/>
</head>
<body class="<?= $bde ? 'bde' : '' ?>">
<div class="csrf-container" data-csrf="<?= htmlspecialchars(gen_csrf_key('js')) ?>"></div>

<?php if (!$bde) printHeader($name, $title); ?>
<main class="<?= $bde ? 'bde' : '' ?>" id="app">
    <?php if (!$bde) { ?>
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
     <?php } ?>

     <section id="menu"
              :set="menu = selected_menu">
         <template v-if="!ui.week_menu_available">
             <h2>Le menu de la semaine sera disponible à 11h10.</h2>
         </template>
         <template v-else-if="is_menu_outdated">
             <h2>Le menu n'a pas pu être récupéré.</h2>
         </template>
         <template v-else-if="is_menu_empty">
             <h2>Le menu n'a pas encore été publié par les restaurants INSA.</h2>
         </template>
         <template v-else-if="menu">
             <div class="menu-content">
                 <div v-if="menu.plat && menu.plat.length != 0">
                     <h2>Plat</h2>
                     <div class="plat">
                         <p v-for="s in menu.plat" v-cloak v-html="get_dish_html(s)"></p>
                     </div>
                 </div>
                 <div v-if="(menu.garniture && menu.garniture.length != 0) || (menu.sauce && menu.sauce.length != 0)">
                     <h2>Accompagnements</h2>
                     <div class="garniture" v-if="menu.garniture && menu.garniture.length != 0">
                         <p v-for="s in menu.garniture" v-cloak v-html="get_dish_html(s)"></p>
                     </div>
                     <div class="sauce" v-if="menu.sauce && menu.sauce.length != 0">
                         <p v-for="s in menu.sauce" v-cloak v-html="get_dish_html(s, 'Sauce')"></p>
                     </div>
                 </div>
             </div>
             <div class="menu-content">
                 <div v-if="menu.entree && menu.entree.length != 0">
                     <h2>Entrée</h2>
                     <div class="entree">
                         <p v-for="s in menu.entree" v-cloak v-html="get_dish_html(s)"></p>
                     </div>
                 </div>
                 <div v-if="(menu.dessert && menu.dessert.length != 0) || (menu.fromage && menu.fromage.length != 0)">
                     <h2>Desserts</h2>
                     <div class="dessert" v-if="menu.dessert && menu.dessert.length != 0">
                         <p v-for="s in menu.dessert" v-cloak v-html="get_dish_html(s)"></p>
                     </div>
                     <div class="fromage" v-if="menu.fromage && menu.fromage.length != 0">
                         <p v-for="s in menu.fromage" v-cloak v-html="get_dish_html(s, 'Fromage')"></p>
                     </div>
                 </div>
             </div>
         </template>
         <template v-else>
             <h2>Ce menu n'est pas disponible pour la date sélectionnée.</h2>
         </template>
     </section>
     <?php if (!$bde) { ?>
         <section id="Notification" v-cloak>
             <button @click="enable_notifications()">
                 S'abonner aux notifications avant chaque repas
             </button>
         </section>
     <?php } ?>
</main>
<footer>
     <?= $bde ? getFooter('Menu des restaurants', "Powered by utils.bde-insa-lyon.fr")
         : getFooter('<a href="https://menu-restaurants.insa-lyon.fr" target="_blank">Page officielle</a>', "Clément GRENNERAT") ?>
</footer>
<script src="<?= getRootPath() ?>template/vue.global.prod.js"></script>
<script src="<?= getRootPath() ?>template/main.js"></script>
<script src="utils.js"></script>
<script src="toggle-group.js"></script>
<script src="main.js"></script>
</body>
</html>
