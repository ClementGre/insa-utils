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
                <p>Solde restant&#8239;: {{ newSolde.toFixed(2) }}</p>
                <div class="centered" v-if="Math.abs(newSolde) <= 0.01">
                    <p>Waw, vous êtes un génie, votre solde est opti au maximum&nbsp;!</p>
                </div>
                <div class="centered" v-else-if="newSolde > 0">
                    <p>Vous pouvez</p>
                    <ul>
                        <li>doubler {{ Math.floor(newSolde / 6.00) }} fois</li>
                        <li>manger {{ Math.floor(newSolde / pricing.rep[regime]) }} repas de plus</li>
                        <li>manger {{ Math.floor(newSolde / pricing.dej[regime]) }} petits-déjeuners de plus</li>
                    </ul>
                </div>
                <div class="centered" v-else>
                    <p>Vous devez</p>
                    <ul>
                        <li>payer {{ Math.floor(-newSolde / pricing.rep[regime]) }} repas à l'unité
                            ({{(Math.floor(-newSolde / pricing.rep[regime]) * 5.30).toFixed(2)}}&nbsp;€)
                        </li>
                        <li>manger {{ Math.ceil(-newSolde / pricing.rep[regime]) }} repas de moins</li>
                        <li>ou manger {{ Math.ceil(-newSolde / pricing.dej[regime]) }} petits-déjeuners de moins.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="forms">
            <div class="form">
                <div>
                    <label for="solde_mois">Solde mois&#8239;:</label>
                    <input v-model="solde_mois_input" @blur="format_solde_mois" @input="update_solde_mois_input"
                           type="number" id="solde" name="solde_mois" min="-1000" max="1000" step="0.01">
                </div>
                <div>
                    <label for="solde">Solde actuel&#8239;:</label>
                    <input v-model="solde_input" @blur="format_solde" @input="update_solde_input" ref="solde_input"
                           type="number" id="solde" name="solde" min="-1000" max="1000" step="0.01">
                </div>
            </div>
            <div class="form">
                <div>
                    <label>Modifier solde&#8239;:</label>
                    <div class="soldebtns">
                        <button @click="solde_mois -= pricing.rep[regime]; format_soldes()">&#8209;1</button>
                        <button @click="solde_mois += pricing.rep[regime]; format_soldes()">+1</button>
                        <button @click="solde_mois -= 6.00; format_soldes()">&#8209;double</button>
                        <button @click="solde_mois += 6.00; format_soldes()">+double</button>
                    </div>
                </div>
                <div>
                    <label for="regime">Régime&thinsp;:</label>
                    <select v-model="regime" name="regime" id="regime" required>
                        <option value="0">7/7</option>
                        <option value="1">5/7 petit dej.</option>
                        <option value="2">5/7 simple</option>
                        <option value="3">Demi-pension</option>
                        <option value="4">À l'unité</option>
                    </select>
                </div>
            </div>
        </div>
        <calendar v-model:weeks="weeks" :allow-weekends="true"></calendar>
    </section>
</main>
<footer>
    <?= getFooter('<a href="/rest/data/Tarification_restaurants_2024-2025.pdf" target="_blank">Grille tarifaire</a>', "Clément GRENNERAT") ?>
</footer>
<script src="main.js" type="module"></script>
</body>
</html>
