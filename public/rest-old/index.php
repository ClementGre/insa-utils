<?php
require '../template/head.php';
require '../template/header.php';
require '../template/footer.php';
require '../origin_path.php';
$title = "RestINSA v1.0";
$desc = "Ancienne version du calculateur automatique de solde. Celui-ci est moins configurable, mais plus simple à utiliser. Attention aux jours fériés et aux vacances !";
?>

<!DOCTYPE html>
<html lang="fr">
<head><?= getHead($title, $desc) ?></head>
<body>
<?php printHeader($title) ?>
<main>
    <?php
    require 'calendar.php';

    /*
     * solde : nombre de points >= 0
     * regime : 1 = 7/7 | 2 = 5/7 | 3 = 5/7 liberté | 4 = 15
     * dej : nombre de dej par semaine (0-7)
     * repas : nombre de repas par semaine
     * we : nombre de weekends à exclure (vendredi soir inclus)
     * ajd : nombre de repas restants aujourd'hui.
     */

    $h = getdate()['hours'];
    $ajd_default = ($h < 14) ? 2 : (($h < 21) ? 1 : 0);

    $solde = isset($_GET['solde']) ? floatval($_GET['solde']) : "";
    $regime = isset($_GET['regime']) ? intval($_GET['regime']) : 3;
    $dej = isset($_GET['dej']) ? intval($_GET['dej']) : 0;
    $repas = isset($_GET['repas']) ? intval($_GET['repas']) : 13;
    $we = isset($_GET['we']) ? intval($_GET['we']) : 0;
    $ajd = isset($_GET['ajd']) ? intval($_GET['ajd']) : $ajd_default;

    if(isset($_GET['solde']) && isset($_GET['regime']) && isset($_GET['dej']) && isset($_GET['repas']) && isset($_GET['we']) && isset($_GET['ajd'])){

        $priceDej = [1 => 1.87, 2 => 2.14, 3 => 2.25, 4 => 2.25][$regime];
        $priceRep = [1 => 3.74, 2 => 4.23, 3 => 4.47, 4 => 4.59][$regime];

        $date = getdate();
        $mday = $date['mday'];
        $mon = $date['mon'];
        $wday = $date['wday'];
        $month = $date['month'];
        $year = $date['year'];

        $dejZeroDays = array();
        $dejOneDays = array();
        $dejTwoDays = array();
        $oneDays = array();
        $twoDays = array();
        $newSolde = $solde;
        $weCount = $we;
        $output = "";

        $dayCount = $wday == 0 ? 7 : $wday - 1;
        $day = $mday;
        $lastDay = getMonthLastDay($date);
        // Map [mday => wday] from today to the end of the month
        $days = [];
        while($day <= $lastDay){
            $days[$day++] = $dayCount++;
            if($dayCount > 6)
                $dayCount = 0;
        }


        $weekCount = 0;
        $repThisWeekCount = 0;
        $dejThisWeekCount = 0;
        $repThisWeek = 0;
        $dejThisWeek = 0;
        foreach($days as $day => $weekday) {
            if($day == $mday || $weekday == 0){

                if($repThisWeekCount != 0 || $dejThisWeekCount != 0){
                    $output .= "S" . $weekCount . " : " . $repThisWeekCount . " rep, " . $dejThisWeekCount . " dej. ("
                        . ($repThisWeekCount*$priceRep + $dejThisWeekCount*$priceDej) . " pts.)<br>";
                    $repThisWeekCount = 0;
                    $dejThisWeekCount = 0;
                }
                $repThisWeek = $repas;
                $dejThisWeek = $dej;
                if($weCount > 0 && $weekday < 5){
                    $repThisWeek = max($repThisWeek-4, 0);
                    $weCount--;
                }
                $weekCount++;
            }

            if($day == $mday){ // Remove already eaten meals for days of this week but before now.
                $eatenDej = $ajd >= 3 ? $weekday : $weekday+1;
                $dejThisWeek = max($dejThisWeek-$eatenDej, 0);

                $eatenRep = $ajd >= 2 ? $weekday*2 : ($ajd == 1 ? $weekday*2 +1 : $weekday*2 + 2);
                $repThisWeek = max($repThisWeek-$eatenRep, 0);
            }

            $dejTdy = false;
            if($dejThisWeek >= 1 && ($day != $mday || $ajd >= 3)){
                $dejTdy = true;
                $dejThisWeek--;
                $dejThisWeekCount++;
                $newSolde -= $priceDej;
            }

            if($repThisWeek >= 2 && ($day != $mday || $ajd >= 2) && $weekday != 5) {
                $repThisWeek -= 2;
                $repThisWeekCount += 2;
                $newSolde -= 2*$priceRep;
                if($dejTdy) $dejTwoDays[] = $day;
                else $twoDays[] = $day;
            }else if($repThisWeek >= 1 && ($day != $mday || $ajd >= 1)) {
                $repThisWeek--;
                $repThisWeekCount++;
                $newSolde -= $priceRep;
                if($dejTdy) $dejOneDays[] = $day;
                else $oneDays[] = $day;
            }else{
                if($dejTdy) $dejZeroDays[] = $day;
            }
        }
        if($repThisWeekCount != 0 || $dejThisWeekCount != 0){
            $output .= "S" . $weekCount . " : " . $repThisWeekCount . " rep, " . $dejThisWeekCount . " dej. ("
                . ($repThisWeekCount*$priceRep + $dejThisWeekCount*$priceDej) . " pts.)<br>";
        }

        ?>
        <section class="outputsection">
            <div class="infos">
                <p style="margin: 10px 0;">Nouveau solde : <?= $newSolde ?></p>
                <p style="font-size: 14px; text-align: left; margin: 0 0 20px 0;"><?= $output ?></p>

                <table>
                    <tr>
                        <td class='bg-dej-zero'></td>
                        <td class='' style="width: 80px">Petit dej</td>
                    </tr>
                    <tr>
                        <td class='bg-one'></td>
                        <td class=''>Un repas</td>
                    </tr>
                    <tr>
                        <td class='bg-two'></td>
                        <td class=''>Deux repas</td>
                    </tr>
                </table>

            </div>
            <div class="calendar">
                <?php printCalendar(false, $dejZeroDays, $dejOneDays, $dejTwoDays, $oneDays, $twoDays); ?>
            </div>
        </section>
        <?php
    }

    ?>

    <section class="inputsection">
        <form method="get">
            <label for="solde">Solde&thinsp;:</label>
            <input value="<?= $solde ?>" type="number" id="solde" name="solde" min="0" max="1000" required><br>

            <label for="regime">Régime&thinsp;:</label>
            <select name="regime" id="regime" required>
                <option value="1" <?= $regime == 1 ? 'selected' : '' ?>>7/7</option>
                <option value="2" <?= $regime == 2 ? 'selected' : '' ?>>5/7</option>
                <option value="3" <?= $regime == 3 ? 'selected' : '' ?>>5/7 Liberté</option>
                <option value="4" <?= $regime == 4 ? 'selected' : '' ?>>15</option>
            </select><br>

            <label for="repas">Repas restants aujourd'hui&nbsp;:</label>
            <input value="<?= $ajd ?>" type="number" id="ajd" name="ajd" min="0" max="3" required>

            <label for="dej">Nombre de petit déjeuné par semaine&nbsp;:</label>
            <input value="<?= $dej ?>" type="number" id="dej" name="dej" min="0" max="7" required><br>

            <label for="repas">Nombre de repas par semaine&nbsp;:</label>
            <input value="<?= $repas ?>" type="number" id="repas" name="repas" min="0" max="13" required>

            <label for="we">Nombre de weekends à exclure<br>(Compte 4 repas)&nbsp;:</label>
            <input value="<?= $we ?>" type="number" id="we" name="we" min="0" max="13" required>
            <br><br>
            <input type="submit" width="150" value="Calculer">
        </form>
        <div class="calendar">
            <?php printCalendar(true); ?>
        </div>
    </section>
</main>
<footer>
    <?= getFooter("", "Clément GRENNERAT") ?>
</footer>
</body>
</html>
