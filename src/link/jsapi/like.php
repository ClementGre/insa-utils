<?php
header('Content-Type: application/json');
$json = json_decode(file_get_contents('php://input'), true);

$user_id = $json['user_id'];
$csrf_js = $json['csrf_js'];
$link_id = $json['link_id'];
$type = $json['type'];
$out = [];

if($_SESSION["csrf_js"] === $csrf_js){

    // Fetching current state

    $liked = false;
    $disliked = false;
    $q = getDB()->prepare('SELECT type FROM links_likes WHERE user_id = :user_id AND link_id = :link_id');
    $q->execute([':user_id' => $user_id, ':link_id' => $link_id]);
    $r = $q->fetch(PDO::FETCH_ASSOC);
    if ($q->rowCount() > 0){
        if ($r['type'] === 'like'){
            $liked = true;
        }else{
            $disliked = true;
        }
    }

    $q = getDB()->prepare('SELECT likes, dislikes FROM links WHERE id = :link_id');
    $q->execute([':link_id' => $link_id]);
    $r = $q->fetch(PDO::FETCH_ASSOC);
    $likes = $r['likes'];
    $dislikes = $r['dislikes'];

    // Updating likes/dislikes

    if($type === 0){
        $q = getDB()->prepare('DELETE FROM links_likes WHERE user_id = :user_id AND link_id = :link_id');
    }else if($type === 1){
        if($liked || $disliked){
            $q = getDB()->prepare('UPDATE links_likes SET type = \'like\' WHERE user_id = :user_id AND link_id = :link_id');
        }else{
            $q = getDB()->prepare('INSERT INTO links_likes (user_id, link_id, type) VALUES (:user_id, :link_id, \'like\')');
        }
    }else if($type === -1){
        if($liked || $disliked){
            $q = getDB()->prepare('UPDATE links_likes SET type = \'dislike\' WHERE user_id = :user_id AND link_id = :link_id');
        }else{
            $q = getDB()->prepare('INSERT INTO links_likes (user_id, link_id, type) VALUES (:user_id, :link_id, \'dislike\')');
        }
    }
    $q->execute([':user_id' => $user_id, ':link_id' => $link_id]);

    // Updating counter

    if(!$liked && $type === 1) {
        $likes++;
    }else if($liked && $type !== 1) {
        $likes--;
    }
    if(!$disliked && $type === -1) {
        $dislikes++;
    }else if($disliked && $type !== -1) {
        $dislikes--;
    }

    if(update_link_likes($link_id, $likes, $dislikes)){
        $out['status'] = 'done';
    }else{
        $out['status'] = 'error';
        $out['error'] = 'An error occurred updating the like/dislike counters.';
    }


}else{
    $out['status'] = 'invalid_csrf';
    $out['error'] = 'Le formulaire a expiré. Veuillez réessayer.';
}

echo json_encode($out);
