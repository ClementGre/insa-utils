<?php
global $db;
try{
    $db = new PDO('mysql:host=pdf4teachers.org;dbname=insa_utils','insa_utils', getenv("INSA_UTILS_DB_PWD"));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    echo "Erreur de connexion à la base de donnée.<br> Erreur : " . $e;
}
function getDB(){
    global $db;
    return $db;
}