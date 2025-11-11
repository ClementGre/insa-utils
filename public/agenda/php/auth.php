<?php

function get_user_agenda_status(): array
{
    $data = [
        'id' => $_COOKIE['id'] ?? null,
        'logged_in' => false,
        'banned' => false,
        'is_in_class' => false,
        'is_requesting_class' => false,
        'class_id' => null,
        'class_name' => null,
    ];
    if (isset($_COOKIE['id']) && isset($_COOKIE['auth_token'])) {
        $id = $_COOKIE['id'];
        $auth_token = $_COOKIE['auth_token'];

        $q = getDB()->prepare("SELECT auth_token, class_id, requested_class_id, status FROM users WHERE id=:id LIMIT 1");
        $q->execute([":id" => $id]);
        $user = $q->fetch();

        if($user && $auth_token == $user['auth_token']){
            $data['logged_in'] = true;
            if($user['status'] == 'banned'){
                $data['banned'] = true;
            }
            if($user['class_id'] != null){
                $q = getDB()->prepare("SELECT name FROM agenda_classes WHERE id=:id LIMIT 1");
                $q->execute([":id" => $user['class_id']]);
                $class = $q->fetch();
                $data['is_in_class'] = true;
                $data['class_id'] = $user['class_id'];
                $data['class_name'] = $class['name'];
            }
            else if($user['requested_class_id'] != null){
                $q = getDB()->prepare("SELECT name FROM agenda_classes WHERE id=:id LIMIT 1");
                $q->execute([":id" => $user['requested_class_id']]);
                $class = $q->fetch();
                $data['is_requesting_class'] = true;
                $data['class_id'] = $user['requested_class_id'];
                $data['class_name'] = $class['name'];
            }

        }
    }
    return $data;
}
