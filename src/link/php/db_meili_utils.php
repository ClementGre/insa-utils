<?php

function addLink($author_id, $author_name, $expiration_date, $title, $description, $link): bool
{
    $q = getDB()->prepare('INSERT INTO links (author_id, expiration_date, title, description, link) VALUES (:author_id, :expiration_date, :title, :description, :link)');
    $r = $q->execute([
        ':author_id' => $author_id,
        ':expiration_date' => $expiration_date,
        ':title' => $title,
        ':description' => $description,
        ':link' => $link
    ]);
    if ($r) {
        require_once __DIR__ . '/../php/meilisearch_connect.php';
        global $client;

        $document = [
            'id' => getDB()->lastInsertId(),
            'title' => $title,
            'description' => $description,
            'link' => $link,
            'author_name' => $author_name,
            'likes' => 0,
            'dislikes' => 0,
        ];
        if ($expiration_date && $expiration_date_epoch = strtotime($expiration_date)) {
            $document['expiration_date'] = $expiration_date_epoch;
        }
        $client->index('links')->addDocuments([$document]);
    }
    return $r;
}

function deleteLink()
{

}

function editLink()
{

}

function update_link_likes($link_id, $likes, $dislikes): bool
{

    $q = getDB()->prepare('UPDATE links SET likes = :likes, dislikes = :dislikes WHERE id = :link_id');
    $r = $q->execute([
        ':likes' => $likes,
        ':dislikes' => $dislikes,
        ':link_id' => $link_id
    ]);
    if ($r) {
        require_once __DIR__ . '/../php/meilisearch_connect.php';
        global $client;
        $client->index('links')->updateDocuments([
            [
                'id' => $link_id,
                'likes' => $likes,
                'dislikes' => $dislikes,
            ]
        ]);
    }
    return $r;
}
function recalculate_link_likes(): void
{
    $q = getDB()->prepare("SELECT id, author_id FROM links");
    $q->execute();
    $links = $q->fetchAll(PDO::FETCH_ASSOC);
    foreach($links as $link){
        $q = getDB()->prepare('SELECT COUNT(*) FROM links_likes WHERE link_id = :id AND type = \'like\' AND user_id != :author_id');
        $q->execute([':id' => $link['id'], ':author_id' => $link['author_id']]);
        $likes = $q->fetch()[0];

        $q = getDB()->prepare('SELECT COUNT(*) FROM links_likes WHERE link_id = :id AND type = \'dislike\' AND user_id != :author_id');
        $q->execute([':id' => $link['id'], ':author_id' => $link['author_id']]);
        $dislikes = $q->fetch()[0];

        update_link_likes($link['id'], $likes, $dislikes);
    }
}

function synchronize_db_to_meili(): void
{

    $q = getDB()->prepare('SELECT * FROM links');
    $q->execute();
    require_once __DIR__ . '/../php/meilisearch_connect.php';
    global $client;

    $documents = array_map(function ($row) {
        $q = getDB()->prepare('SELECT name from users WHERE id = ?');
        $q->execute([$row['author_id']]);
        $name = $q->fetch()[0];
        $document = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'link' => $row['link'],
            'author_name' => $name,
            'likes' => $row['likes'],
            'dislikes' => $row['dislikes'],
        ];
        if ($row['expiration_date'] && $expiration_date_epoch = strtotime($row['expiration_date'])) {
            $document['expiration_date'] = $expiration_date_epoch;
        }
        return $document;
    }, $q->fetchAll());

    $client->index('links')->deleteAllDocuments();
    $client->index('links')->addDocuments($documents);
}

function migrate_meilisearch(): void
{
    require_once __DIR__ . '/../php/meilisearch_connect.php';
    global $client;

    $client->index('links')->resetSettings();

    // Results format
    $client->index('links')->updateDisplayedAttributes([
        'id'
    ]);

    // Search order
    $client->index('links')->updateSearchableAttributes([
        'title',
        'description',
        'link',
        'author_name'
    ]);

    $client->index('links')->updateRankingRules([
        "words",
        "typo",
        "proximity",
        "attribute",
        "sort",
        "likes:desc",
        "dislikes:asc",
        "exactness",
    ]);

    $client->index('links')->updateDistinctAttribute('link');

    // Filter

    $client->index('links')->updateFilterableAttributes([
        'expiration_date'
    ]);

}
