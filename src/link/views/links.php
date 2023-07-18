<section class="searchbar">
    <img src="<?= getRootPath() ?>svg/search.svg" alt="Supprimer">
    <input type="text" id="search-input" placeholder="Rechercher des liens...">
    <p id="search-stats">67 résultats en 7 ms</p>
</section>

<section class="b-darken">
    <form class="todo" method="post" action="<?= getRootPath() ?>link/manage/add_link">
        <?php set_csrf() ?>
        <input type="text" name="title" placeholder="Titre" maxlength="50" required
               value="<?= out($_POST['title'] ?? '') ?>">
        <textarea name="content" rows="4" placeholder="Description" maxlength="3000">
            <?= out($_POST['description'] ?? '') ?>
        </textarea>
        <div>
            <label for="expiration_date">Date d'expiration (facultatif) :</label>
            <input type="date" id="expiration_date" name="expiration_date"
                   value="<?= $_POST['expiration_date'] ?? '' ?>" min="<?= date_tomorrow() ?>">
        </div>
        <div>
            <input type="url" name="url" value="<?= out($_POST['url'] ?? '') ?>" required>
            <input class="fixed" type="submit" name="submit" value="Ajouter">
        </div>
    </form>
</section>

<?php
