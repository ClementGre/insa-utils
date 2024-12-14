<?php
require '../template/head.php';
require '../template/header.php';
require '../template/footer.php';
require '../origin_path.php';
require '../../libs/router.php';
$name = "INS'Attente";
$title = "Temps d'attente des restaurants";
$desc = "Tiens toi au courant du temps d'attente du RI et de l'Olivier pour mieux t'organiser durant la pause.";

$bde = isset($_GET['bde']);
?>

<!DOCTYPE html>
<html lang="fr" class="<?= $bde ? 'bde' : '' ?>">
<head>
    <?php printHead($name, $title, $desc, '', 'attente/icons/icon-128.png', 'attente/attente.webmanifest') ?>
    <link href="<?= getRootPath() ?>attente/main.css" rel="stylesheet"/>
</head>
<body class="<?= $bde ? 'bde' : '' ?>">
<div class="csrf-container" data-csrf="<?= htmlspecialchars(gen_csrf_key('js')) ?>"></div>

<?php if (!$bde) printHeader($name, $title); ?>
<main class="<?= $bde ? 'bde' : '' ?>" id="app">
    <?php if (!$bde) { ?>
        <section id="rest-select">
            <toggle-group
                    label="Restaurant"
                    :buttons="['Olivier', 'RI:Déjeuner', 'RI:Diner']"
                    :disabled_indices="disabled_rest_indices"
                    v-model:selected_index="ui.selected_rest_index"
            ></toggle-group>
        </section>
    <?php } ?>

    <section id="restaurants"
             :set="restaurant = selected_restaurant">
        <template v-if="!ui.week_menu_available">
            <h2>Les données de la file d'attente n'ont pas pu être récupéré.</h2>
        </template>
        <template v-else-if="is_waitingTime_empty">
            <h2>Les données de la file d'attente sont vides.</h2>
        </template>
        <template v-else-if="restaurant">
            <div class="waiting-content">
                <div class="current-wait">
                    <h2>Temps d'attente actuel (en minutes)</h2> 
                    <span v-if="!restaurant.actualWaitingTime">Les données sur l'attente actuelle n'ont pas pu être récupérés.</span>
                    <p v-else-if = "restaurant.actualWaitingTime < 10">0{{ restaurant.actualWaitingTime }}</p>
                    <p v-else>{{ restaurant.actualWaitingTime }}</p>
                </div>
                <div class="wait-prediction">
                    <h2>Prédiction d'attente</h2>
                    <div v-if="prediction_is_not_null" class="chart">
                        <canvas id="histogramCanvas"></canvas>
                    </div>
                    <p v-else>Les prédictions sur le temps d'attente n'ont pas pu être récupérés.</p>
                </div>
                </div>
        </template>
        <template v-else>
            <h2>Ce menu n'est pas disponible pour la date sélectionnée.</h2>
        </template>
    </section>
</main>
<footer>
    <?= $bde ? getFooter('BdE INSA Lyon', "Powered by insa-utils.fr")
        : getFooter('<a href="https://menu-restaurants.insa-lyon.fr" target="_blank">Page officielle</a>', "Clément GRENNERAT") ?>
</footer>
<script src="<?= getRootPath() ?>template/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= getRootPath() ?>attente/main.js" type="module"></script>
</body>
</html>
