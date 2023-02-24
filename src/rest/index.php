<?php
require '../template/head.php';
require '../template/header.php';
require '../template/footer.php';
require '../origin_path.php';
$name = "Rest'INSA";
$title = "Calculateur de points RI";
$desc = "Déterminer si tu auras assez de solde pour finir le mois n'a jamais été aussi simple. Optimise au maximum ton solde RI grâce à ce calculateur hors norme.";
$keywords = 'insa, calculer, restaurant, ri, solde, olivier, doubler';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php printHead($name, $title, $desc, $keywords, 'rest/icons/icon-128.png', 'rest/rest.webmanifest') ?>
    <link href="<?= getRootPath() ?>rest/main.css" rel="stylesheet"/>
</head>
<body>
<?php printHeader($name, $title); ?>
<main class="">
    <section id="app">
        <div class="output" v-cloak>
            <div>
                <p>Solde restant&#8239;: {{ twoDecimals(newSolde) }}</p>
                <div class="centered" v-if="newSolde >= 0">
                    <p>Vous pouvez</p>
                    <ul>
                        <li>doubler {{ Math.floor(newSolde / 5.83) }} fois</li>
                        <li>ou manger {{ Math.floor(newSolde / pricing.rep[regime]) }} repas de plus</li>
                        <li>ou manger {{ Math.floor(newSolde / pricing.dej[regime]) }} petits-déjeuners de plus.</li>
                    </ul>
                </div>
                <div class="centered" v-else>
                    <p>Vous devez</p>
                    <ul>
                        <li>manger {{ Math.ceil(-newSolde / pricing.rep[regime]) }} repas de moins</li>
                        <li>ou manger {{ Math.ceil(-newSolde / pricing.dej[regime]) }} petits-déjeuners de moins.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="form">
            <div>
                <label for="solde">Solde&#8239;:</label>
                <input v-model="solde" type="number" id="solde" name="solde" min="-1000" max="1000" step="0.01">
            </div>
            <div>
                <label>Modifier solde&#8239;:</label>
                <div class="soldebtns">
                    <button @click="solde -= pricing.rep[regime]">&#8209;1</button>
                    <button @click="solde += pricing.rep[regime]">+1</button>
                    <button @click="solde -= 5.83">&#8209;double</button>
                    <button @click="solde += 5.83">+double</button>
                </div>
            </div>
            <div>
                <label for="regime">Régime&thinsp;:</label>
                <select v-model="regime" name="regime" id="regime" required>
                    <option value="0">7/7</option>
                    <option value="1">5/7</option>
                    <option value="2">5/7 Liberté</option>
                    <option value="3">15</option>
                    <option value="4">À l'unité</option>
                </select>
            </div>
        </div>
        <calendar v-model:weeks="weeks" :allowed-weekdays="regime != 1"></calendar>
    </section>
</main>
<footer>
    <?= getFooter('<a href="https://restaurants.insa-lyon.fr/sites/restaurants.insa-lyon.fr/files/u130/2022_2023itarifs_restaurants-.pdf" target="_blank">Grille tarifaire</a>', "Clément GRENNERAT") ?>
</footer>
<script src="main.js" type="module"></script>
</body>
</html>