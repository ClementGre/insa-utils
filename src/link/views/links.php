<?php
$status = $status ?? false;
?>
<div class="user-id-container" data-user-id="<?= htmlspecialchars($status['id']) ?>"></div>
<div class="page-name-container" data-page-name="all"></div>
<div class="root-path-container" data-root-path="<?= htmlspecialchars(getRootPath()) ?>"></div>
<div class="csrf-container" data-csrf="<?= htmlspecialchars(gen_csrf_key('js')) ?>"></div>

<section class="searchbar">
    <img src="<?= getRootPath() ?>svg/search.svg" alt="Supprimer">
    <input type="text" id="search-input" placeholder="Rechercher des liens...">
    <p id="search-stats"></p>
</section>

<section id="results-section">
    <p class="no-results">Aucun résultat</p>
</section>

<h3>Publier un lien :</h3>

<section class="b-darken">
    <form class="add-link" method="post" action="<?= getRootPath() ?>link/manage/add_link">
        <?php set_csrf() ?>
        <div class="header">
            <input type="text" name="title" placeholder="Titre" maxlength="50" required
                   value="<?= out($_POST['title'] ?? '') ?>">
            <textarea name="description" rows="4" placeholder="Description" maxlength="1000"><?= out($_POST['description'] ?? '') ?></textarea>
        </div>
        <div class="horizontal">
            <label for="expiration_date">Date d'expiration (facultatif)</label>
            <input type="date" id="expiration_date" name="expiration_date"
                   value="<?= $_POST['expiration_date'] ?? '' ?>" min="<?= date_tomorrow() ?>">
        </div>
        <div class="horizontal">
            <input type="url" name="url" value="<?= out($_POST['url'] ?? '') ?>" placeholder="Lien (URL)" maxlength="2048" required>
            <input class="fixed" type="submit" name="submit" value="Ajouter">
        </div>
    </form>
</section>

<?php
