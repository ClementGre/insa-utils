<?php

header('Content-Type: application/json');
$json = json_decode(file_get_contents('php://input'), true);
$csrf_name = $json['csrf_name'];
$csrf_value = $json['csrf_value'];

$out = [];

if($_SESSION["csrf_$csrf_name"] === $csrf_value){
    $out['status'] = 'success';
    $out['message'] = 'CSRF token is valid';
}else{
    $out['status'] = 'error';
    $out['message'] = 'CSRF token is invalid';
}

echo json_encode($out);
