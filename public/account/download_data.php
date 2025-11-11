<?php

function write_user_data_to_csv_output()
{

    require_once '../agenda/php/auth.php';
    $status = get_user_agenda_status();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=UserData.csv');
    $output = fopen('php://output', 'w');

    function print_table($output, $name, $q): void
    {
        fputcsv($output, array($name));
        $i = 0;
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            if ($i == 0) fputcsv($output, array_keys($data));
            $i++;
            fputcsv($output, array_values($data));
        }
        fputcsv($output, array());
    }

    // User Data
    $q = getDB()->prepare("SELECT id, name, email_date, status, class_id, requested_class_id FROM users WHERE id=:id LIMIT 1");
    $q->execute([":id" => $status['id']]);
    print_table($output, 'Information utilisateur :', $q);

    // User created todos
    $q = getDB()->prepare("SELECT * FROM agenda_todo WHERE creator_id=:id OR last_editor_id=:id");
    $q->execute([":id" => $status['id']]);
    print_table($output, 'Tâches créés ou modifiés :', $q);

    // User totos status
    $q = getDB()->prepare("SELECT todo_id, status FROM agenda_status WHERE user_id=:id");
    $q->execute([":id" => $status['id']]);
    print_table($output, 'Status des tâches :', $q);

    // Class subjects
    $q = getDB()->prepare("SELECT * FROM agenda_subjects WHERE class_id=:id");
    $q->execute([":id" => $status['class_id']]);
    print_table($output, 'Matières de la classe courante :', $q);
}

