<?php
function randomToken($length) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $input_length = strlen($chars);
    $random_string = '';
    for($i = 0; $i < $length; $i++) {
        $random_string .= $chars[mt_rand(0, $input_length - 1)];
    }
    return $random_string;
}
function randomCode($length) {
    $chars = '0123456789';
    $input_length = strlen($chars);
    $random_string = '';
    for($i = 0; $i < $length; $i++) {
        $random_string .= $chars[mt_rand(0, $input_length - 1)];
    }
    return $random_string;
}
// $email does not include @insa-lyon.fr
function emailToName($email){
    return ucwords(strtolower(str_replace('.', ' ', $email)));
}

// $email does not include @insa-lyon.fr
function nameToEmail($name){
    return strtolower(str_replace(' ', '.', $name));
}