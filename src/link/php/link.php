<?php

function printLink()
{

}

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
        $client->index('links')->addDocuments([
            [
                'id' => getDB()->lastInsertId(),
                'author' => $author_name,
                'title' => $title,
                'description' => $description,
                'link' => $link,
                'likes' => 0,
                'dislikes' => 0
            ]
        ]);
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
