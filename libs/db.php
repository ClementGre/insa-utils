<?php
global $db;
try{
    $db = new PDO('mysql:host=' . getenv('DATABASE_SERVER') . ':' . getenv('DATABASE_PORT') . ';dbname=' . getenv("DATABASE_NAME"), getenv("DATABASE_USER"), getenv("DATABASE_PASSWORD"));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    echo "Erreur de connexion à la base de donnée.<br> Erreur : " . $e;
}
function getDB(){
    global $db;
    return $db;
}
