<?php
global $db;
try{
    $db = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=insa-utils','insa-utils', getenv("DB_PASSWORD"));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    echo "Erreur de connexion à la base de donnée.<br> Erreur : " . $e;
}
function getDB(){
    global $db;
    return $db;
}