<?php
require '../../libs/db.php';
header('Content-Type: application/json');
$json = json_decode(file_get_contents('php://input'), true);


$csrf_js = $json['csrf_js'];
$out = [];

session_start();
if($_SESSION["csrf_js"] !== $csrf_js){
    $out['status'] = 'invalid_csrf';
    $out['error'] = 'Le formulaire a expiré. Veuillez réessayer.';
    echo json_encode($out);
    exit();
}

$form_data = $json['form_data'];
$subscription = $json['subscription'];

// insert into db

$q = getDB()->prepare('
INSERT INTO menu_subscriptions
    (ri_lunch, ri_dinner, ri_weekend, olivier, endpoint, key_p256dh, key_auth)
    VALUES (:ri_lunch, :ri_dinner, :ri_weekend, :olivier, :endpoint, :key_p256dh, :key_auth)
    ON DUPLICATE KEY UPDATE
                         ri_lunch = :ri_lunch,
                         ri_dinner = :ri_dinner,
                         ri_weekend = :ri_weekend,
                         lunch_time = :lunch_time,
                        dinner_time = :dinner_time,
                         olivier = :olivier,
                         endpoint = :endpoint;');

$r = $q->execute([
    ':ri_lunch' => $form_data['ri_lunch'],
    ':ri_dinner' => $form_data['ri_dinner'],
    ':ri_weekend' => $form_data['ri_weekend'],
    ':lunch_time' => $form_data['lunch_time'],
    ':dinner_time' => $form_data['dinner_time'],
    ':olivier' => $form_data['olivier'],
    ':endpoint' => $subscription['endpoint'],
    ':key_p256dh' => $subscription['keys']['p256dh'],
    ':key_auth' => $subscription['keys']['auth'],
]);

if($r){
    $out['status'] = 'done';
}else{
    $out['status'] = 'error';
    $out['error'] = 'invalid_csrf';
}
echo json_encode($out);

