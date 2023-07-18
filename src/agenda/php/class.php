<?php
function leave_class($id, $class_id): void
{
    require_once __DIR__ . '/../db.php';
    $q = getDB()->prepare("UPDATE users SET class_id=NULL WHERE id=:id AND class_id=:class_id");
    $r = $q->execute([":id" => $id, ":class_id" => $class_id]);
    if ($r) {
        // check class is not empty
        $q = getDB()->prepare("SELECT 1 FROM users WHERE class_id=:class_id LIMIT 1");
        $q->execute([":class_id" => $class_id]);
        if ($q->rowCount() == 0) {
            // delete class
            $q = getDB()->prepare("DELETE FROM agenda_classes WHERE id=:class_id");
            $q->execute([":class_id" => $class_id]);
        }
    }
}

function get_class_name($class_id)
{
    $q = getDB()->prepare("SELECT name FROM agenda_classes WHERE id=:id LIMIT 1");
    $q->execute([":id" => $class_id]);
    $class = $q->fetch();
    if ($class == null) {
        return null;
    } else {
        return $class['name'];
    }
}

function is_class_active(mixed $id): bool
{
    $q = getDB()->prepare("SELECT COUNT(*) FROM agenda_todo WHERE class_id=:class_id AND duedate >= CURDATE() AND is_private=0");
    $q->execute([":class_id" => $id]);
    if ($r = $q->fetch()) {
        return $r[0] >= 2;
    } else {
        return false;
    }
}

function get_class_members_count($class_id): int
{
    $q = getDB()->prepare("SELECT COUNT(*) FROM users WHERE class_id=:class_id");
    $q->execute(['class_id' => $class_id]);
    $row = $q->fetch();
    return $row ? $row[0] : 0;
}

enum TodoStatus: string
{
    case TODO = "À faire";
    case IN_PROGRESS = "En cours";
    case DONE = "Fait";

    public static function fromName($name): TodoStatus
    {
        if (!$name) return TodoStatus::TODO;
        return match (strtolower($name)) {
            "in_progress" => TodoStatus::IN_PROGRESS,
            "done" => TodoStatus::DONE,
            default => TodoStatus::TODO,
        };
    }

    public function toCSSClass(): string
    {
        return match ($this) {
            TodoStatus::TODO => "todo",
            TodoStatus::IN_PROGRESS => "in-progress",
            TodoStatus::DONE => "done",
        };
    }
}


function duedate_to_str($duedate): string
{
    $current_date = DateTime::createFromFormat('Y-m-d', (new DateTime())->format('Y-m-d'));
    $duedate = DateTime::createFromFormat('Y-m-d', $duedate);

    if ($duedate >= $current_date) {
        $dist = get_day_distance($duedate, $current_date);

        if ($dist == 0) return "Aujourd'hui";

        if ($dist == 1) {
            return "Demain (" . format_date('EEEE', $duedate) . ')';
        }
        if ($dist == 2) {
            return "Après demain (" . format_date('EEEE', $duedate) . ')';
        }

        return format_date('EEEE d', $duedate) . " ($dist&nbsp;J)";
    } else {
        return format_date('EEEE d MMM', $duedate);
    }

}

function format_date($format, $date): string
{
    $fmt = new IntlDateFormatter('fr_FR');
    $fmt->setTimeZone(new DateTimeZone('Europe/Paris'));
    $fmt->setPattern($format);
    return ucfirst($fmt->format($date));
}

function get_day_distance($date1, $date2): int
{
    $diff = $date1->diff($date2);
    return $diff->days;
}

function current_date(): string
{
    return (new DateTime())->format('Y-m-d');
}

function date_in_a_week(): string
{
    $date = new DateTime();
    $date->add(new DateInterval('P7D'));
    return $date->format('Y-m-d');
}

function sort_classes_by_name($a, $b): int
{
    $as = explode(' ', strtolower($a['name']));
    $bs = explode(' ', strtolower($b['name']));
    if (count($as) < count($bs)) {
        $as = array_pad($as, count($bs), '');
    } else if (count($bs) < count($as)) {
        $bs = array_pad($bs, count($as), '');
    }

    for ($i = 0; $i < count($as); $i++) {
        if (is_numeric($as[$i]) && is_numeric($bs[$i])) {
            $cmp = $as[$i] <=> $bs[$i];
        } else {
            $cmp = strcmp($as[$i], $bs[$i]);
        }
        if ($cmp != 0) return $cmp;
    }
    return 0;
}

function print_classes_list($status)
{
    $links = $status['logged_in'] ?? false;
    ?>
    <div class="class-list">
        <?php
        $classes = getDB()->query('SELECT * FROM agenda_classes ORDER BY name')->fetchAll();

        uasort($classes, 'sort_classes_by_name');

        foreach ($classes as $class) {
            $members_count = get_class_members_count($class['id']);
            $is_active = is_class_active($class['id']);
            if($links){
                echo '<a class="class" href="' . getRootPath() . 'agenda/class/' . $class['id'] . '/join">';
            }else{
                echo '<div class="class">';
            }
                ?>
                    <p><?= out($class['name']) ?></p>
                    <div>
                        <p><?= $members_count == 1 ? ($members_count . ' membre') : ($members_count . ' membres') ?></p>
                        <p class="badged<?= $is_active ? '' : ' hidden' ?>">Active</p>
                    </div>
                <?php
            if($links){
                echo '</a>';
            }else{
                echo '</div>';
            }
        }
        ?>
    </div>
    <?php
}
