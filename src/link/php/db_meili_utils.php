<?php

function addLink($author_id, $expiration_date, $title, $description, $link): bool
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

function likeLink()
{

}

function unlikeLink()
{

}

function synchronize_db_to_meili(): void
{

    $q = getDB()->prepare('SELECT * FROM links');
    $q->execute();
    require_once __DIR__ . '/../php/meilisearch_connect.php';
    global $client;

    $documents = array_map(function ($row) {
        $document = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'link' => $row['link'],
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
        'title'
    ]);

    // Search order
    $client->index('links')->updateSearchableAttributes([
        'title',
        'description',
        'link'
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
