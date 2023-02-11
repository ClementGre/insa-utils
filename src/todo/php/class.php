<?php
function leave_class($id, $class_id): void
{
    require_once __DIR__.'/../db.php';
    $q = getDB()->prepare("UPDATE users SET class_id=NULL WHERE id=:id AND class_id=:class_id");
    $r = $q->execute([":id" => $id, ":class_id" => $class_id]);
    if($r) {
        // check class is not empty
        $q = getDB()->prepare("SELECT 1 FROM users WHERE class_id=:class_id LIMIT 1");
        $q->execute([":class_id" => $class_id]);
        if ($q->rowCount() == 0) {
            // delete class
            $q = getDB()->prepare("DELETE FROM classes WHERE id=:class_id");
            $q->execute([":class_id" => $class_id]);
        }
    }
}
function get_class_name($class_id){
    $q = getDB()->prepare("SELECT name FROM classes WHERE id=:id LIMIT 1");
    $q->execute([":id" => $class_id]);
    $class = $q->fetch();
    if ($class == null) {
        return null;
    } else {
        return $class['name'];
    }
}