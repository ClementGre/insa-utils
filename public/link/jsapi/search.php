<?php
header('Content-Type: application/json');
$json = json_decode(file_get_contents('php://input'), true);

$user_id = $json['user_id'];
$query = $json['query'];
$offset = $json['offset'];
$csrf_js = $json['csrf_js'];
$out = [];

if($_SESSION["csrf_js"] === $csrf_js){
    require_once __DIR__ . '/../../../libs/meilisearch_connect.php';
    global $client;

//    migrate_meilisearch();
//    synchronize_db_to_meili();
//    recalculate_link_likes();

    $limit = 20;

    $out['status'] = 'done';
    $result = $client->index('links')->search($query, [
        'filter' => 'expiration_date > ' . time() . ' OR expiration_date NOT EXISTS',
        "limit" => $limit,
        "offset" => $offset,
    ]);

    $q = getDB()->prepare('SELECT link_id, type FROM links_likes WHERE user_id = ?');
    $q->execute([$user_id]);
    $r = $q->fetchAll(PDO::FETCH_ASSOC);
    $liked = [];
    $disliked = [];
    foreach($r as $like){
        if ($like['type'] === 'like'){
            $liked[] = $like['link_id'];
        }else{
            $disliked[] = $like['link_id'];
        }
    }

    $out['hits_count'] = $result->getEstimatedTotalHits();
    $out['last_offset'] = $result->getEstimatedTotalHits() <= $offset + $limit;
    $out['processing_time_ms'] = $result->getProcessingTimeMs();
    $out['hits'] = array_map(function ($hit) use ($liked, $disliked) {
        $id = $hit['id'];

        $q = getDB()->prepare('SELECT id, author_id, expiration_date, title, description, link, likes, dislikes FROM links WHERE id = ?');
        $q->execute([$id]);
        $data = $q->fetch(PDO::FETCH_ASSOC);

        if($data !== false) {
            $q = getDB()->prepare('SELECT name from users WHERE id = ?');
            $q->execute([$data['author_id']]);
            $data['author_name'] = $data[0];

            $data['is_liked'] = in_array($id, $liked);
            $data['is_disliked'] = in_array($id, $disliked);
            return $data;
        }
        error_log("Link $id not found in database");
        return null;
    }, $result->getHits());


}else{
    $out['status'] = 'invalid_csrf';
    $out['error'] = 'Le formulaire a expiré. Veuillez réessayer.';
}

echo json_encode($out);
