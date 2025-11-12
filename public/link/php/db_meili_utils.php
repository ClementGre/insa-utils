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
        require_once __DIR__ . '/../../../libs/meilisearch_connect.php';
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

function deleteLink($id, $author_id): bool
{
    // The link is deleted only if the author is the one who deletes it

    $q = getDB()->prepare('DELETE FROM links WHERE id = :id AND author_id = :author_id');
    $r = $q->execute([
        ':id' => $id,
        ':author_id' => $author_id
    ]);
    if ($r && $q->rowCount() > 0){
        require_once __DIR__ . '/../../../libs/meilisearch_connect.php';
        global $client;
        $client->index('links')->deleteDocument($id);
    }
    return $r && $q->rowCount() > 0;
}

function editLink($author_id, $id, $expiration_date, $title, $description, $link): bool
{
    // Resets the likes and dislikes counters if title, description or link are modified
    $q = getDB()->prepare('SELECT title, description, link, likes, dislikes FROM links WHERE id = :id');
    $q->execute([':id' => $id]);
    $old_link = $q->fetch();
    $likes = $old_link['likes'];
    $dislikes = $old_link['dislikes'];
    $reset_counters = false;
    if($old_link['title'] !== $title || $old_link['description'] !== $description || $old_link['link'] !== $link){
        $reset_counters = true;
        $likes = 0;
        $dislikes = 0;
    }

    // Link will be edited only if the author is the one who edits it
    $q = getDB()->prepare('UPDATE links SET expiration_date = :expiration_date, title = :title, description = :description,
                 link = :link, likes = :likes, dislikes = :dislikes WHERE id = :id AND author_id = :author_id');
    $r = $q->execute([
        ':expiration_date' => $expiration_date,
        ':title' => $title,
        ':description' => $description,
        ':link' => $link,
        ':id' => $id,
        ':author_id' => $author_id,
        ':likes' => $likes,
        ':dislikes' => $dislikes
    ]);
    if($r && $q->rowCount() > 0){
        if($reset_counters){
            $q = getDB()->prepare('DELETE FROM links_likes WHERE link_id = :id');
            $q->execute([':id' => $id]);
        }

        require_once __DIR__ . '/../../../libs/meilisearch_connect.php';
        global $client;
        $client->index('links')->updateDocuments([[
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'link' => $link,
            'likes' => $likes,
            'dislikes' => $dislikes
        ]]);
        return true;
    }
    return false;
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
        require_once __DIR__ . '/../../../libs/meilisearch_connect.php';
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
